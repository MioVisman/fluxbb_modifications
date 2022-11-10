<?php
/**
 * Требуется PHP 5 (Visman - а может уже и нет ;) ), а так же CURL или разрешение работы c ресурсами http:// для file_get_contents.
 * 
 * @link http://loginza.ru/api-overview
 * @author Sergey Arsenichev, PRO-Technologies Ltd.
 * @version 1.0
 *
 * @модификация для FluxBB - Visman (visman@inbox.ru)
 * @version 1.0.2
 */
if (!defined('PUN'))
	exit;

class LoginzaAPI {
	/**
	 * Версия класса
	 *
	 */
	const VERSION = 'FluxBB-1.0.2';
	/**
	 * URL для взаимодействия с API loginza
	 *
	 */
	const API_URL = 'http://loginza.ru/api/%method%';
	/**
	 * URL виджета Loginza
	 *
	 */
	const WIDGET_URL = 'https://loginza.ru/api/widget';

	private $data;
	/**
	 * Получить информацию профиля авторизованного пользователя
	 *
	 * @param string $token Токен ключ авторизованного пользователя
	 * @return mixed
	 */
	public function getAuthInfo ($token) {
		return $this->apiRequert('authinfo', array('token='.$token));
	}

	public function getData () {
		return $this->data;
	}

	/**
	 * Получает адрес ссылки виджета Loginza
	 *
	 * @param string $return_url Ссылка возврата, куда будет возвращен пользователя после авторизации
	 * @param string $provider Провайдер по умолчанию из списка: google, yandex, mailru, vkontakte, facebook, twitter, loginza, myopenid, webmoney, rambler, mailruapi:, flickr, verisign, aol
	 * @param string $overlay Тип встраивания виджета: true, wp_plugin, loginza
	 * @return string
	 */
	public function getWidgetUrl ($return_url=null, $provider=null, $lang='en', $overlay='') {
		$params = array();
		
		if ($lang) {
			switch ($lang)
			{
				case 'ru':
					$params[] = 'lang=ru';
					break;
				case 'uk':
					$params[] = 'lang=uk';
					break;
				default:
					$params[] = 'lang=en';
			}
		}

		if ($overlay) {
			$params[] = 'overlay='.$overlay;
		}

		if ($provider) {
			if (strstr($provider, ',') === false)
				$params[] = 'provider='.$provider;
			else
				$params[] = 'providers_set='.$provider;
		}

		if (!$return_url) {
			$params[] = 'token_url='.urlencode($this->currentUrl());
		} else {
			$params[] = 'token_url='.urlencode($return_url);
		}

		return self::WIDGET_URL.'?'.implode('&', $params);
	}
	
	// копирование аватара из адреса
	public function setAvatar($id, $url, $pun_config) {
		if ($id < 2 || empty($url)) { return false; }
			
		if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) { return false; }

		$file = $this->downloadAvatar($url, $pun_config['o_avatars_dir']);
		if ($file === false) { return false; }
		
		$this->img_resize($file, $pun_config['o_avatars_dir'], $id, $pun_config['o_avatars_width'], $pun_config['o_avatars_height']);
		@unlink($file);
	}
	
	private function img_resize ($file, $dir, $name, $width = 0, $height = 0, $quality = 80) {

		if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) { return false; }

		$dir.='/';
		
		list($w, $h, $type,) = @getimagesize($file);
		if ($type == IMAGETYPE_GIF)
		{
			$ext = '.gif';
			$image = @imagecreatefromgif($file);
			$icfunc = 'imagegif';
		}
		else if ($type == IMAGETYPE_JPEG)
		{
			$ext = '.jpg';
			$image = @imagecreatefromjpeg($file);
			$icfunc = 'imagejpeg';
		}
		else if ($type == IMAGETYPE_PNG)
		{
			$ext = '.png';
			$image = @imagecreatefrompng($file);
			$icfunc = 'imagepng';
		}
		else
			return false;

		if (!isset($image) || empty($image)) { return false; }

		if (!function_exists($icfunc))
		{
			$ext = '.jpg';
			$icfunc = 'imagejpeg';
			if (!function_exists($icfunc)) { return false; }
		}

		$xr = ($w == 0) ? 1 : $width / $w;
		$yr = ($h == 0) ? 1 : $height / $h;
		$r = min($xr, $yr, 1);
		$width = round($w * $r);
		$height = round($h * $r);

		$idest = imagecreatetruecolor($width, $height);
		imagefill($idest, 0, 0, 0x7FFFFFFF);
		imagecolortransparent($idest, 0x7FFFFFFF);
		if ($ext == '.gif')
		{
			$palette = imagecolorstotal($image);
			imagetruecolortopalette($idest, true, $palette);
		}
		imagecopyresampled($idest, $image, 0, 0, 0, 0, $width, $height, $w, $h);
		imagesavealpha($idest, true);

		if ($ext == '.png' && version_compare(PHP_VERSION, '5.1.2', '>='))
		{
			$quality = floor((100 - $quality) / 11);
			@imagepng($idest, PUN_ROOT.$dir.$name.$ext, $quality);
		}
		else if ($ext == '.jpg')
			@imagejpeg($idest, PUN_ROOT.$dir.$name.$ext, $quality);
		else
			@$icfunc($idest, PUN_ROOT.$dir.$name.$ext);

		imagedestroy($image);
		imagedestroy($idest);

		@chmod(PUN_ROOT.$dir.$name.$ext, 0644);
	}

	private function downloadAvatar($url, $dir) {
	
		$tempfile = PUN_ROOT.$dir.'/'.md5(time().random_pass(10)).'.tmp';

		$f_remote = @fopen($url, 'rb');
		if (!$f_remote) { return false; }

		$f_local = @fopen($tempfile, 'wb');
		if (!$f_local)
		{
			@fclose($f_remote);
			return false;
		}

		while (!feof($f_remote)) {
			$buff = fread($f_remote, 1024);
			fwrite($f_local, $buff);
		}

		@fclose($f_remote);
		@fclose($f_local);
		@chmod($tempfile, 0644);

		return $tempfile;
	}

	/**
	 * Возвращает ссылку на текущую страницу
	 *
	 * @return string
	 */
	private function currentUrl () {
		$url = array();
		// проверка https
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {
			$url['sheme'] = "https";
			$url['port'] = '443';
		} else {
			$url['sheme'] = 'http';
			$url['port'] = '80';
		}
		// хост
		$url['host'] = $_SERVER['HTTP_HOST'];
		// если не стандартный порт
		if (strpos($url['host'], ':') === false && $_SERVER['SERVER_PORT'] != $url['port']) {
			$url['host'] .= ':'.$_SERVER['SERVER_PORT'];
		}
		// строка запроса
		if (isset($_SERVER['REQUEST_URI'])) {
			$url['request'] = $_SERVER['REQUEST_URI'];
		} else {
			$url['request'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
			$query = $_SERVER['QUERY_STRING'];
			if (isset($query)) {
			  $url['request'] .= '?'.$query;
			}
		}
		
		return $url['sheme'].'://'.$url['host'].$url['request'];
	}
	
	/**
	 * Делает запрос на API loginza
	 *
	 * @param string $method
	 * @param array $params
	 * @return string
	 */
	private function apiRequert($method, $params) {
		// url запрос
		$url = str_replace('%method%', $method, self::API_URL).'?'.implode('&', $params);
		
		if ( function_exists('curl_init') ) {
			$curl = curl_init($url);
			$user_agent = 'LoginzaAPI-'.self::VERSION.'/php'.phpversion();
			
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$raw_data = curl_exec($curl);
			curl_close($curl);
			$responce = $raw_data;
		} else {
			$responce = file_get_contents($url);
		}

		$this->data = $responce;
		// обработка JSON ответа API
		return $this->decodeJSON($responce);
	}
	
	/**
	 * Парсим JSON данные
	 *
	 * @param string $data
	 * @return object
	 */
	private function decodeJSON ($data) {
		if (function_exists('json_decode'))
			return json_decode ($data);

		// загружаем библиотеку работы с JSON если она необходима
		if (!class_exists('Services_JSON'))
			include PUN_ROOT.'include/loginza/JSON.php';

		$json = new Services_JSON();	
		return $json->decode($data);
	}
}

?>