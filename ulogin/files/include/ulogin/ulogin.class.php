<?php
/**
 * Copyright (C) 2015 Visman (mio.visman@yandex.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

class uLoginClass
{
	private $_error = false;
	private $db = NULL;
	private $pun_config = NULL;


	public function __construct($db, $pun_config)
	{
		$this->db = $db;
		$this->pun_config = $pun_config;
	}
		
		
	private function _Request($url, $timeout = 10, $max_redirects = 10)
	{
		$allow_url_fopen = strtolower(ini_get('allow_url_fopen'));
		$ua = 'Mozilla/5.0 (Windows NT 6.4; rv:38.0) Gecko/20100101 Firefox/38.0';

		// 1
		if (extension_loaded('curl'))
		{
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_NOBODY, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_USERAGENT, $ua);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$content = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			curl_close($ch);

			if ($content !== false)
			{
				// переадресация
				if (($http_code == '301' || $http_code == '302') && $max_redirects > 0)
				{
					if (preg_match('%Location:\s(http[^\n\r]+)%i', $content, $matches))
						return $this->_Request($matches['1'], $timeout, $max_redirects - 1);
				}

				// отдаем ответ
				else if ($http_code == '200')
				{
					$content_start = strpos($content, "\r\n\r\n");
					if ($content_start !== false)
						return substr($content, $content_start + 4);
				}

				// прерываем выполнение
				else
					return false;
			}
		}

		// 2
		else if (function_exists('file_get_contents') && in_array($allow_url_fopen, array('on', 'true', '1')))
		{
			$stream_context = stream_context_create(
				array(
					'http' => array(
						'method'		=> 'GET',
						'user_agent'	=> $ua,
						'max_redirects'	=> $max_redirects + 1,	// PHP >=5.1.0 only
						'timeout'		=> $timeout	// PHP >=5.2.1 only
					)
				)
			);

			return file_get_contents($url, false, $stream_context);
		}

		return false;
	}


	// проверка ответа на наличие ошибок
	private function _CheckError($profile)
	{
		if (!is_array($profile))
		{
			$this->_error = ulogin_lang('Error ntr'); // ошибка: Формат ответа нарушен.
			return false;
		}

		if (isset($profile['error']))
		{
			$strpos = strpos($profile['error'], 'host is not');
			if ($strpos)
			{
				$this->_error = ulogin_lang('Error host'); // ошибка: адрес хоста не совпадает с оригиналом
				return false;
			}
			switch ($profile['error'])
			{
				case 'token expired':
					$this->_error = ulogin_lang('Error token exp'); // ошибка: время жизни токена истекло
					return false;
					break;
				case 'invalid token':
					$this->_error = ulogin_lang('Error inval token'); // ошибка: неверный токен
					return false;
					break;
				default:
					$this->_error = pun_htmlspecialchars($profile['error']).'.'; // ошибка
					return false;
			}
		}

		if (empty($profile['identity']))
		{
			$this->_error = ulogin_lang('Error no ident'); // ошибка: В возвращаемых данных отсутствует переменная "identity"
			return false;
		}
		
		if (!in_array($profile['network'], explode(',', $this->pun_config['o_ulogin_net'])))
		{
			$this->_error = ulogin_lang('Error network'); // ошибка: данная соцсеть запрещена
			return false;
		}

		return true;
	}

	
	// отдаем текст ошибки
	public function GetError()
	{
	  return $this->_error === false ? '' : $this->_error;
	}


	// получение данных юзера от uLogin со всеми проверками
	public function GetUserFromToken($token = false)
	{
		if (!$token)
		{
			$this->_error = ulogin_lang('No token'); // ошибка: Токен отсутвтует
			return false;
		}

		$response = $this->_Request('http://ulogin.ru/token.php?token='.$token.'&host='.$_SERVER['HTTP_HOST']); // получаем ответ от uLogin
		
		if (!$response)
		{
			$this->_error = ulogin_lang('Error utrd'); // ошибка: Невозможно получить данные
			return false;
		}
		
		$profile = json_decode($response, true); // преобразуем ответ в массив
		
		if ($this->_CheckError($profile)) return $profile; // проверка на ошибки
		
		return false;
	}
	
	
	// получение email из пришедшего профиля
	public function GetEmail($profile)
	{
		if (!empty($profile['email']) && !empty($profile['verified_email']) && $profile['verified_email'] == 1)
		  return $profile['email'];
		  
		return '';
	}


	// получение id юзера на основе identity
	public function GetUserIdByIdentity($identity)
	{
		$result = $this->db->query('SELECT user_id FROM '.$this->db->prefix.'ulogin WHERE identity=\''.$this->db->escape($identity).'\'') or error('Unable to fetch ulogin info', __FILE__, __LINE__, $this->db->error());

		if ($this->db->num_rows($result))
			return $this->db->result($result);
			
		return false;
	}

	
	// получение id юзера на основе email
	public function GetUserIdByEmail($email)
	{
		$result = $this->db->query('SELECT id FROM '.$this->db->prefix.'users WHERE email=\''.$this->db->escape($email).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $this->db->error());

		if ($this->db->num_rows($result))
			return $this->db->result($result);

		return false;
	}


	// получение списка id юзеров на основе email (нереальный случай 1 email у нескольких юзеров)
	public function GetUserIdsByEmail($email)
	{
		$result = $this->db->query('SELECT DISTINCT user_id FROM '.$this->db->prefix.'ulogin WHERE email=\''.$this->db->escape($email).'\'') or error('Unable to fetch ulogin info', __FILE__, __LINE__, $this->db->error());

		$user_ids = array();
		for ($i = 0; $cur_id = $this->db->result($result, $i); $i++)
			$user_ids[] = $cur_id;

		return $user_ids;
	}


	// проверяем наличие пользователя с таким id
	public function IsUserById($id)
	{
		$result = $this->db->query('SELECT id FROM '.$this->db->prefix.'users WHERE id='.(int)$id) or error('Unable to fetch user info', __FILE__, __LINE__, $this->db->error());

		if ($this->db->num_rows($result))
			return true;

		return false;
	}


	// удаляем все записи для юзер с id из таблице ulogin
	public function DeleteUserById($id)
	{
		$this->db->query('DELETE FROM '.$this->db->prefix.'ulogin WHERE user_id='.(int)$id) or error('Unable to delete user from ulogin', __FILE__, __LINE__, $db->error());
	}


	// сохранение нового профиля с проверкой дубля
	public function SaveProfile($user_id, $profile)
	{
		$email = $this->GetEmail($profile);
		$uid = isset($profile['uid']) ? $profile['uid'] : '';

		if (!empty($email))
			$this->db->query('UPDATE '.$this->db->prefix.'ulogin SET identity=\''.$this->db->escape($profile['identity']).'\', uid=\''.$this->db->escape($uid).'\' WHERE user_id='.(int)$user_id.' AND network=\''.$this->db->escape($profile['network']).'\' AND email=\''.$this->db->escape($email).'\'') or error('Unable to update ulogin', __FILE__, __LINE__, $this->db->error());

		if (empty($email) || !$this->db->affected_rows())
			$this->db->query('INSERT INTO '.$this->db->prefix.'ulogin (user_id, identity, network, email, uid) VALUES('.(int)$user_id.', \''.$this->db->escape($profile['identity']).'\', \''.$this->db->escape($profile['network']).'\', \''.$this->db->escape($email).'\', \''.$this->db->escape($uid).'\')') or error('Unable to insert ulogin', __FILE__, __LINE__, $this->db->error());
	}


	// запускаем имеющегося юзера на форум
	public function LoginUser($user_id)
	{
		$result = $this->db->query('SELECT u.*, g.* FROM '.$this->db->prefix.'users AS u INNER JOIN '.$this->db->prefix.'groups AS g ON u.group_id=g.g_id WHERE u.id='.(int)$user_id) or error('Unable to fetch user information', __FILE__, __LINE__, $this->db->error());
		$cur_user = $this->db->fetch_assoc($result);

		// перезаписываем ip админа и модератора, если это моя сборка
		if (isset($this->pun_config['o_check_ip']) && $this->pun_config['o_check_ip'] == '1')
		{
			if ($cur_user['g_id'] == PUN_ADMIN || $cur_user['g_moderator'] == '1')
				$this->db->query('UPDATE '.$this->db->prefix.'users SET registration_ip=\''.$this->db->escape(get_remote_address()).'\' WHERE id='.$user_id) or error('Unable to update user IP', __FILE__, __LINE__, $this->db->error());
		}

		// Remove this users guest entry from the online list
		$this->db->query('DELETE FROM '.$this->db->prefix.'online WHERE ident=\''.$this->db->escape(get_remote_address()).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $this->db->error());

		// ставим куки на 2 недели
		pun_setcookie($cur_user['id'], $cur_user['password'], time() + 1209600);

		// Reset tracked topics
		set_tracked_topics(null);
	}


	// регистрируем нового юзера на форуме
	public function RegistrationUser($profile, $username, $pun_user)
	{
		$now = time();
		$password = random_pass(12);
		// массив полей для регистрации
		$arr = array(
			'username'				=> $username,
			'password'				=> pun_hash($password),
			'group_id'				=> $this->pun_config['o_default_user_group'],
			'email_setting'		=> $this->pun_config['o_default_email_setting'],
			'dst'							=> $this->pun_config['o_default_dst'],
			'language'				=> $pun_user['language'],
			'style'						=> $this->pun_config['o_default_style'],
			'registration_ip'	=> get_remote_address(),
			'last_visit'			=> $now,
			'email'						=> $this->GetEmail($profile),
			'realname'				=> '',
		);
		// числовые поля
		$arr_numb = array('group_id', 'email_setting', 'dst', 'registered', 'last_visit', 'gender');

		// если есть пол, заполняем
		if (isset($pun_user['gender']) && !empty($profile['sex']))
		  $arr['gender'] = ($profile['sex'] == 1) ? 2 : 1;
		  
		// заполняем реальное имя
		if (!empty($profile['first_name']))
		  $arr['realname'].= $profile['first_name'];
		if (!empty($profile['last_name']))
		  $arr['realname'].= ' '.$profile['last_name'];
	  $arr['realname'] = pun_trim($arr['realname']);

		$fields = 'registered';
		$value = $now;

		foreach ($arr as $key => $val)
		{
			if (in_array($key, $arr_numb)) {
				$fields.= ', '.$key;
				$value.= ', '.preg_replace('%[^0-9\.-]%', '', str_replace(',', '.', $val));
			} else if (!empty($val)) {
				$fields.= ', '.$key;
				$value.= ', \''.$this->db->escape($val).'\'';
			}
		}

		// добавляем нового юзера в базу
		$this->db->query('INSERT INTO '.$this->db->prefix.'users ('.$fields.') VALUES('.$value.')') or error('Unable to create user', __FILE__, __LINE__, $this->db->error());
		$new_uid = $this->db->insert_id();
		
		$this->SaveProfile($new_uid, $profile);

		// ставим куки на 2 недели
		pun_setcookie($new_uid, $arr['password'], $now + 1209600);

		// Remove this users guest entry from the online list
		$this->db->query('DELETE FROM '.$this->db->prefix.'online WHERE ident=\''.$this->db->escape(get_remote_address()).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $this->db->error());

		// Regenerate the users info cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PUN_ROOT.'include/cache.php';

		generate_users_info_cache();

		// загружаем аватарку
    $this->SetAvatar($new_uid, $profile);

		// отправляем письмо с паролем
		if (is_valid_email($arr['email']))
		{
			// Load the "welcome" template
			$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/welcome.tpl'));

			// The first row contains the subject
			$first_crlf = strpos($mail_tpl, "\n");
			$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
			$mail_message = trim(substr($mail_tpl, $first_crlf));

			$mail_subject = str_replace('<board_title>', $this->pun_config['o_board_title'], $mail_subject);
			$mail_message = str_replace('<base_url>', get_base_url().'/', $mail_message);
			$mail_message = str_replace('<username>', $username, $mail_message);
			$mail_message = str_replace('<password>', $password, $mail_message);
			$mail_message = str_replace('<login_url>', get_base_url(), $mail_message);
			$mail_message = str_replace('<board_mailer>', $this->pun_config['o_board_title'], $mail_message);

			pun_mail($arr['email'], $mail_subject, $mail_message);
		}

		// If the mailing list isn't empty, we may need to send out some alerts
		if ($this->pun_config['o_mailing_list'] != '')
		{
			if ($this->pun_config['o_regs_report'] == '1')
			{
				// Load the "new user" template
				$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/new_user.tpl'));

				// The first row contains the subject
				$first_crlf = strpos($mail_tpl, "\n");
				$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
				$mail_message = trim(substr($mail_tpl, $first_crlf));

				$mail_message = str_replace('<username>', $username, $mail_message);
				$mail_message = str_replace('<base_url>', get_base_url().'/', $mail_message);
				$mail_message = str_replace('<profile_url>', get_base_url().'/profile.php?id='.$new_uid, $mail_message);
				$mail_message = str_replace('<board_mailer>', $this->pun_config['o_board_title'], $mail_message);

				pun_mail($this->pun_config['o_mailing_list'], $mail_subject, $mail_message);
			}
		}

		return $new_uid;
	}


	// генерация логина/username для формы регистрации
	public function GenUserNames($profile)
	{
		$usernames = array();
		
		if (!empty($profile['first_name']))
		{
			if (!empty($profile['last_name']))
			{
				$usernames[] = $profile['first_name'].' '.$profile['last_name'];
				$usernames[] = $profile['last_name'].' '.$profile['first_name'];
				$usernames[] = $profile['last_name'];
			}
			$usernames[] = $profile['first_name'];
			$usernames[] = $profile['first_name'].mt_rand(1, 10000);
		}

		if (!empty($profile['nickname']))
		{
			$usernames[] = $profile['nickname'];
			$usernames[] = $profile['nickname'].mt_rand(1, 10000);
		}

		if (!empty($profile['email']) && preg_match('%^(.+)\@%u', $profile['email'], $nickname))
			$usernames[] = $nickname[1];

		$result = $this->db->query('SELECT id FROM '.$this->db->prefix.'users ORDER BY id DESC LIMIT 1') or error('Unable to fetch users info', __FILE__, __LINE__, $this->db->error());
		$usernames[] = 'User_'.($this->db->result($result) + 1);

		$usernames = array_map('ulogin_check_username', $usernames);

		return $usernames;
	}


	// точка входа для загрузки аватарки
	public function SetAvatar($id, $profile)
	{
		if ($id < 2)
			return false;

		if ($this->pun_config['o_avatars'] * $this->pun_config['o_avatars_width'] * $this->pun_config['o_avatars_height'] == 0)
			return false;

		if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor'))
			return false;

		$url = empty($profile['photo_big']) ? (empty($profile['photo']) ? '' : $profile['photo']) : $profile['photo_big'];
		
		if (empty($url) || strpos($url, 'ulogin.ru/') !== false)
		  return false;

		$temp = $this->_Request($url);
		if ($temp === false)
			return false;

		$filename = PUN_ROOT.$this->pun_config['o_avatars_dir'].'/'.md5(time().random_pass(10)).'.tmp';
		if (file_put_contents($filename, $temp) === false)
			return false;

		$this->_ImgResize($filename, $this->pun_config['o_avatars_dir'], $id, $this->pun_config['o_avatars_width'], $this->pun_config['o_avatars_height']);
		
		@unlink($file);
	}


	// ресайз изображения
	private function _ImgResize($file, $dir, $name, $width = 0, $height = 0, $quality = 80)
	{
		$dir = PUN_ROOT.$dir.'/';

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

		if (!isset($image) || empty($image))
			return false;

		if (!function_exists($icfunc))
		{
			$ext = '.jpg';
			$icfunc = 'imagejpeg';
			if (!function_exists($icfunc))
				return false;
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
			@imagepng($idest, $dir.$name.$ext, $quality);
		}
		else if ($ext == '.jpg')
			@imagejpeg($idest, $dir.$name.$ext, $quality);
		else
			@$icfunc($idest, $dir.$name.$ext);

		imagedestroy($image);
		imagedestroy($idest);

		@chmod($dir.$name.$ext, 0644);
	}
}
