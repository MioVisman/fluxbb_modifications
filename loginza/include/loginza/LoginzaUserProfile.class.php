<?php
/**
 * @link http://loginza.ru/api-overview
 * @author Sergey Arsenichev, PRO-Technologies Ltd.
 * @version 1.0
 *
 * @модификация для FluxBB - Visman (visman@inbox.ru)
 * @version 1.0.2
 */
if (!defined('PUN'))
	exit;

function checkUsername ($username)
{
	$username = preg_replace('/[@=\'\.-]+/u', '_', $username);
	$username = preg_replace('/[^\p{L}\p{N} _]/u', '', $username);
	$username = pun_trim(utf8_substr($username, 0, 25));
	
	return $username;
}

class LoginzaUserProfile {
	/**
	 * Профиль
	 *
	 * @var unknown_type
	 */
	private $profile;
	
	function __construct($profile)
	{
		$this->profile = $profile;
	}

	// получение массива данных
	public function genDataAll ($pun_config)
	{
		return array(
			'email'			=> $this->genEmail(),
			'realname'	=> $this->genRealname(),
			'url'				=> $this->genUserSite(),
			'jabber'		=> (empty($this->profile->im->jabber) ? '' : pun_trim($this->profile->im->jabber)),
			'icq'				=> (empty($this->profile->im->icq) ? '' : pun_trim($this->profile->im->icq)),
			'location'	=> '',
			'timezone'	=> (isset($this->profile->timezone) ? ((float) $this->profile->timezone) : $pun_config['o_default_timezone']),
			'gender'		=> $this->genGender(),
			'provider'	=> $this->genProvider(),
			'identity'	=> $this->profile->identity,
			'uid'				=> (empty($this->profile->uid) ? '' : $this->profile->uid),
			'photo_url'	=> (empty($this->profile->photo) ? '' : $this->profile->photo),
			);
	}

	// получаем массив для логина/username
	public function genUsername ()
	{
		$username = array();
		if (!empty($this->profile->nickname))
			$username[] = $this->profile->nickname;

		$username[] = $this->genRealname();

		if (!empty($this->profile->email) && preg_match('/^(.+)\@/i', $this->profile->email, $nickname))
			$username[] = $nickname[1];

		// шаблоны по которым выцепляем ник из identity
		$patterns = array(
			'([^\.]+)\.ya\.ru',
			'([^\.]+)\.loginza\.ru',
			'openid\.mail\.ru\/[^\/]+\/([^\/?]+)',
			'my\.mail\.ru\/[^\/]+\/([^\/?]+)',
			'openid\.yandex\.ru\/([^\/?]+)',
			'([^\.]+)\.myopenid\.com',
			'id\.rambler\.ru\/users\/([^\/?]+)',
		);
		foreach ($patterns as $pattern) {
			if (preg_match('/^https?\:\/\/'.$pattern.'/i', $this->profile->identity, $result))
				$username[] = $result[1];
		}

		$username = array_map('checkUsername', $username);

		return $username;
	}

	// генерация email
	public function genEmail ()
	{
		if (isset($this->profile->email))
			return $this->profile->email;

		// шаблоны по которым выцепляем email из identity
		$patterns = array(
			'openid\.mail\.ru\/([^\/]+)\/([^\/?]+)',
			'my\.mail\.ru\/([^\/]+)\/([^\/?]+)',
			'openid\.(yandex)\.ru\/([^\/?]+)',
			'id\.(rambler)\.ru\/users\/([^\/?]+)',
		);
		foreach ($patterns as $pattern) {
			if (preg_match('/^https?\:\/\/'.$pattern.'/i', $this->profile->identity, $result))
				return $result[2].'@'.$result[1].'.ru';
		}

		// email от балды
		$result = preg_replace('/https?\:\/\/(www\.)?/i', '', $this->profile->identity);
		$result = pun_trim(preg_replace('/[^\w\d]+/u', '.', $result), '.');
		return $result.'@localhost';
	}

	// генерация реального имени
	private function genRealname ()
	{
		$name = '';
		if (isset($this->profile->name->full_name))
			$name = pun_trim($this->profile->name->full_name);
		else {
			if (isset($this->profile->name->first_name))
				$name.= pun_trim($this->profile->name->first_name);
			if (isset($this->profile->name->last_name))
				$name.= ' '.pun_trim($this->profile->name->last_name);
		}
		$name = preg_replace('/[\s]+/u', ' ', $name);

		return pun_trim(utf8_substr($name, 0, 40));;
	}

	// генерация пола юзера
	private function genGender ()
	{
		if (isset($this->profile->gender))
		{
			if ($this->profile->gender == 'M')
				return 1; // м
			if ($this->profile->gender == 'F')
				return 2; // ж
		}
		return 0; // не указан
	}

	// генерация сайта юзера
	private function genUserSite ()
	{
		if (!empty($this->profile->web->blog))
			$url = $this->profile->web->blog;
		elseif (!empty($this->profile->web->default))
			$url = $this->profile->web->default;
		else
			$url = $this->profile->identity;

		if (utf8_strlen($url) > 100)
			return '';

		// шаблоны по которым обнуляем сайт
		$patterns = array(
			'google\.com\/accounts\/.*\/id\?id',
			'https\:\/\/me\.yahoo\.com\/',
		);
		foreach ($patterns as $pattern) {
			if (preg_match('/'.$pattern.'/i', $url))
				return '';
		}

		return $url;
	}

	// генерация провайдера
	private function genProvider ()
	{
		if (!empty($this->profile->provider))
			$provider = $this->profile->provider;
		else
			$provider = $this->profile->identity;

		$arr = parse_url($provider);
		
		$providers = array(
			'openid.yandex.ru'	=> 'yandex',
			'openid.mail.ru'		=> 'mailru',
			'yahooapis.com'			=> 'flickr',
			'facebook.com'			=> 'facebook',
			'vkontakte.ru'			=> 'vkontakte',
			'webmoney.com'			=> 'webmoney',	// no test
			'wmkeeper.com'			=> 'webmoney',	// no test
			'me.yahoo.com'			=> 'flickr',
			'myopenid.com'			=> 'myopenid',	// no test
			'twitter.com'				=> 'twitter',
			'webmoney.ru'				=> 'webmoney',	// no test
			'rambler.ru'				=> 'rambler',
			'google.com'				=> 'google',
			'my.mail.ru'				=> 'mailruapi',
			'loginza.ru'				=> 'loginza',
			'google.ru'					=> 'google',
			'lastfm.ru'					=> 'lastfm',
			'mail.ru'						=> 'mailruapi',
			'last.fm'						=> 'lastfm',
			'aol.com'						=> 'aol',
			'vk.com'						=> 'vkontakte',
			'ya.ru'							=> 'yandex',
			'verisign'					=> 'verisign',	// no test
			'rambler'						=> 'rambler',
			'yandex'						=> 'yandex',
			'steam'							=> 'steam',			// no test
// TODO
			);

		foreach ($providers as $key => $dat) {
			if (strstr($arr['host'], $key) !== false)
				return $dat;
		}

		return 'openid'; // $arr['host'];
	}
}

?>