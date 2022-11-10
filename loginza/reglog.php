<?php

/**
 * Copyright (C) 2011 Visman (visman@inbox.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';

if ($pun_user['g_read_board'] == '0')
	message($lang_common['No view']);
	
if (!isset($pun_config['o_loginza_set']))
	message($lang_common['No permission']);

// Load language file
if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/reglog.php'))
	require PUN_ROOT.'lang/'.$pun_user['language'].'/reglog.php';
else
	require PUN_ROOT.'lang/English/reglog.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/login.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/register.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/prof_reg.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/profile.php';
require PUN_ROOT.'include/loginza/LoginzaAPI.class.php';
require PUN_ROOT.'include/loginza/LoginzaUserProfile.class.php';
require PUN_ROOT.'include/email.php';

$hash_ip = pun_hash(get_remote_address());

if (file_exists(PUN_ROOT.'include/header.php'))
	$prefhf = 'include/';
else
	$prefhf = '';

// обработка гостя
if ($pun_user['is_guest'])
{
	if (isset($_GET['action']) && $_GET['action'] == 'endreg' && isset($_POST['form_sent']))
	{
		// регистрация новых юзеров запрещена
		if ($pun_config['o_regs_allow'] == '0')
			message($lang_register['No new regs']);

		$dataUser = isset($_POST['dataUser']) ? $_POST['dataUser'] : '';
		$dataHash = isset($_POST['dataHash']) ? $_POST['dataHash'] : '';

		if ($dataHash != pun_hash($dataUser.$hash_ip))
			message($lang_common['Bad request']);
			
		if ($_POST['form_sent'] != pun_hash($_SERVER['SCRIPT_FILENAME'].$hash_ip))
			message($lang_common['Bad request']);

		$username = isset($_POST['req_user']) ? pun_trim($_POST['req_user']) : '';

		$errors  = array();
		check_username($username);

		// окончание регистрации
		if (empty($errors))
		{
			$dUser = unserialize($dataUser);
			
			$now = time();
			$password = random_pass(10);
			$dUser['username'] = $username;
			$dUser['password'] = pun_hash($password);

			$set_fi = 'group_id, email_setting, dst, language, style, registered, registration_ip, last_visit';
			$set_va = $pun_config['o_default_user_group'].', '.$pun_config['o_default_email_setting'].', '.$pun_config['o_default_dst'].', \''.$db->escape($pun_user['language']).'\', \''.$db->escape($pun_config['o_default_style']).'\', '.$now.', \''.$db->escape(get_remote_address()).'\', '.$now;

			$arr_numb = array('timezone', 'gender');
			$arr_add  = array('username', 'password', 'email', 'realname', 'url', 'jabber', 'icq', 'location', 'timezone');
			foreach ($dUser as $key => $dat)
			{
				if (isset($pun_user[$key]) || in_array($key, $arr_add))
				{
					if (in_array($key, $arr_numb)) {
						$set_fi.= ', '.$key;
						$set_va.= ', '.preg_replace('%[^0-9\.-]%', '', str_replace(',', '.', $dat));
					} else if (!empty($dat)) {
						$set_fi.= ', '.$key;
						$set_va.= ', \''.$db->escape($dat).'\'';
					}
				}
			}

			// Add the user
			$db->query('INSERT INTO '.$db->prefix.'users ('.$set_fi.') VALUES('.$set_va.')') or error('Unable to create user', __FILE__, __LINE__, $db->error());
			$new_uid = $db->insert_id();

			$db->query('INSERT INTO '.$db->prefix.'reglog (user_id, identity, provider, email, uid) VALUES('.$new_uid.', \''.$db->escape($dUser['identity']).'\', \''.$db->escape($dUser['provider']).'\', \''.$db->escape($dUser['email']).'\', \''.$db->escape($dUser['uid']).'\')') or error('Unable to create reglog', __FILE__, __LINE__, $db->error());

			// ставим куки на 1 год
			pun_setcookie($new_uid, $dUser['password'], $now + 31536000);

			// Remove this users guest entry from the online list
			$db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape(get_remote_address()).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());

			// MOD кэш пользователей - Visman
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PUN_ROOT.'include/cache.php';
			generate_users_info_cache();

      $LgzAPI = new LoginzaAPI();
      $LgzAPI->setAvatar($new_uid, $dUser['photo_url'], $pun_config);
      
			// отправляем письмо с паролем
			if (is_valid_email($dUser['email']))
			{
				// Load the "welcome" template
				$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/welcome.tpl'));

				// The first row contains the subject
				$first_crlf = strpos($mail_tpl, "\n");
				$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
				$mail_message = trim(substr($mail_tpl, $first_crlf));

				$mail_subject = str_replace('<board_title>', $pun_config['o_board_title'], $mail_subject);
				$mail_message = str_replace('<base_url>', get_base_url().'/', $mail_message);
				$mail_message = str_replace('<username>', $username, $mail_message);
				$mail_message = str_replace('<password>', $password, $mail_message);
				$mail_message = str_replace('<login_url>', get_base_url(), $mail_message);
				$mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'], $mail_message);

				pun_mail($dUser['email'], $mail_subject, $mail_message);
			}
			
			// If the mailing list isn't empty, we may need to send out some alerts
			if ($pun_config['o_mailing_list'] != '')
			{
				if ($pun_config['o_regs_report'] == '1')
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
					$mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'], $mail_message);

					pun_mail($pun_config['o_mailing_list'], $mail_subject, $mail_message);
				}
			}

//			$pun_config['o_redirect_delay'] = 30;
			redirect('profile.php?id='.$new_uid, $lang_register['Reg complete']);
		}
	}
	else if (isset($_POST['token']))
	{
		$LgzAPI = new LoginzaAPI();
  
		// запрос профиля авторизованного пользователя
		$profile = $LgzAPI->getAuthInfo($_POST['token']);

		if (!is_object($profile))
			message($lang_rl['Error loginza'].$lang_rl['LoginzaAPI ntr']);

		if (!isset($profile->identity))
			message($lang_rl['Error loginza'].pun_htmlspecialchars($profile->error_message));
		
		$result = $db->query('SELECT user_id FROM '.$db->prefix.'reglog WHERE identity=\''.$db->escape($profile->identity).'\'') or error('Unable to fetch reglog info', __FILE__, __LINE__, $db->error());

		// Такой identity есть, значит запускаем юзера на форум
		if ($db->num_rows($result))
		{
			$user_id = $db->result($result);

			$result = $db->query('SELECT u.*, g.* FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id WHERE u.id='.$user_id) or error('Unable to fetch user information', __FILE__, __LINE__, $db->error());
			$cur_user = $db->fetch_assoc($result);

			// перезаписываем ip админа и модератора, если это моя сборка
			if (isset($pun_config['o_check_ip']) && $pun_config['o_check_ip'] == '1')
			{
				if ($cur_user['g_id'] == PUN_ADMIN || $cur_user['g_moderator'] == '1')
					$db->query('UPDATE '.$db->prefix.'users SET registration_ip=\''.$db->escape(get_remote_address()).'\' WHERE id='.$user_id) or error('Unable to update user IP', __FILE__, __LINE__, $db->error());
			}

			// Remove this users guest entry from the online list
			$db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape(get_remote_address()).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());

			// ставим куки на 1 год
			$expire = time() + 31536000;
			pun_setcookie($cur_user['id'], $cur_user['password'], $expire);
		
			// Reset tracked topics
			set_tracked_topics(null);

			$redirect = (isset($_GET['redirect_url']) ? pun_trim($_GET['redirect_url']) : 'index.php');
			redirect(htmlspecialchars($redirect), $lang_login['Login redirect']);
		}
	
		// объект генерации полей профиля
		$LgzPrf = new LoginzaUserProfile($profile);

		// проверяем не дублируется ли этот email или он забанен
		$email = $LgzPrf->genEmail();

		if (is_banned_email($email))
			message($lang_rl['Ban email'].htmlspecialchars($email));

		$result = $db->query('SELECT id FROM '.$db->prefix.'users WHERE email=\''.$db->escape($email).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
			message($lang_rl['Duble email'].htmlspecialchars($email));

		$result = $db->query('SELECT user_id FROM '.$db->prefix.'reglog WHERE email=\''.$db->escape($email).'\'') or error('Unable to fetch reglog info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
			message($lang_rl['Duble email'].htmlspecialchars($email));
		
		// регистрация новых юзеров запрещена
		if ($pun_config['o_regs_allow'] == '0')
			message($lang_register['No new regs']);
		
		// Check that someone from this IP didn't register a user within the last hour (DoS prevention)
		$result = $db->query('SELECT 1 FROM '.$db->prefix.'users WHERE registration_ip=\''.get_remote_address().'\' AND registered>'.(time() - 3600)) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
			message($lang_register['Registration flood']);

		// ************ генерация данных *******************************************
		// получение имени
		$usernames = $LgzPrf->genUsername();
		foreach ($usernames as $username)
		{
			$errors  = array();
			check_username($username);
			if (empty($errors))
				break;
		}
		if (!empty($errors) || empty($usernames))
		{
			$result = $db->query('SELECT id FROM '.$db->prefix.'users ORDER BY id DESC LIMIT 1') or error('Unable to fetch users info', __FILE__, __LINE__, $db->error());
			$username = 'User_'.($db->result($result) + 1);
		}

		$dataUser = serialize($LgzPrf->genDataAll($pun_config));
		$dataHash = pun_hash($dataUser.$hash_ip);
		$errors  = array();
	}
	else
		message($lang_rl['No token']);

	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_register['Register']);
	$required_fields = array('req_user' => $lang_common['Username']);
	$focus_element = array('register', 'req_user');
	define('PUN_ACTIVE_PAGE', 'register');
	require PUN_ROOT.$prefhf.'header.php';

	// If there are errors, we display them
	if (!empty($errors))
	{

?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_register['Registration errors'] ?></span></h2>
	<div class="box">
		<div class="inbox error-info">
			<p><?php echo $lang_register['Registration errors info'] ?></p>
			<ul class="error-list">
<?php

		foreach ($errors as $cur_error)
			echo "\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
?>
			</ul>
		</div>
	</div>
</div>

<?php

	}

?>
<div id="regform" class="blockform">
	<h2><span><?php echo $lang_register['Register'] ?></span></h2>
	<div class="box">
		<form id="register" method="post" action="reglog.php?action=endreg" onsubmit="this.register.disabled=true;if(process_form(this)){return true;}else{this.register.disabled=false;return false;}">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_register['Username legend'] ?></legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="<?php echo pun_htmlspecialchars(pun_hash($_SERVER['SCRIPT_FILENAME'].$hash_ip)) ?>" />
						<input type="hidden" name="dataUser" value="<?php echo pun_htmlspecialchars($dataUser) ?>" />
						<input type="hidden" name="dataHash" value="<?php echo pun_htmlspecialchars($dataHash) ?>" />
						<label class="required"><strong><?php echo $lang_common['Username'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br /><input type="text" name="req_user" value="<?php echo pun_htmlspecialchars($username) ?>" size="25" maxlength="25" /><br /></label>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="register" value="<?php echo $lang_register['Register'] ?>" /></p>
		</form>
	</div>
</div>
<?php

	require PUN_ROOT.$prefhf.'footer.php';
}

// обработка пользователя
else
{
	$provs = explode(',', $pun_config['o_loginza_prov']);
	$LgzAPI = new LoginzaAPI();

	if (isset($_POST['delete']) && isset($_POST['form_sent']))
	{
		if ($_POST['form_sent'] != pun_hash($pun_user['username'].$hash_ip))
			message($lang_common['Bad request']);

		$ar_del = array();
		$del_acc = isset($_POST['del_acc']) ? $_POST['del_acc'] : array();
		foreach ($provs as $prov)
		{
			if (isset($del_acc[$prov]) && $del_acc[$prov] == '1')
				$ar_del[] = '\''.$prov.'\'';
		}
		if (!empty($ar_del))
			$db->query('DELETE FROM '.$db->prefix.'reglog WHERE user_id='.$pun_user['id'].' AND provider IN ('.implode(',', $ar_del).')') or error('Unable to delete reglog info', __FILE__, __LINE__, $db->error());

		redirect('reglog.php', $lang_rl['Delete redirect']);
	}
	else if (isset($_POST['token']))
	{
		// запрос профиля авторизованного пользователя
		$profile = $LgzAPI->getAuthInfo($_POST['token']);

		if (!is_object($profile))
			message($lang_rl['Error loginza'].$lang_rl['LoginzaAPI ntr']);

		if (!isset($profile->identity))
			message($lang_rl['Error loginza'].pun_htmlspecialchars($profile->error_message));

		$result = $db->query('SELECT user_id FROM '.$db->prefix.'reglog WHERE identity=\''.$db->escape($profile->identity).'\'') or error('Unable to fetch reglog info', __FILE__, __LINE__, $db->error());

		// Такой identity есть
		if ($db->num_rows($result))
		{
			if ($pun_user['id'] != $db->result($result))
			{
				$pun_config['o_redirect_delay'] = 10;
				redirect('reglog.php', $lang_rl['Duble identity']);
			}
		}
		// Такого identity нет
		else
		{
			// объект генерации полей профиля
			$LgzPrf = new LoginzaUserProfile($profile);

			// проверяем не дублируется ли этот email или он забанен
			$email = $LgzPrf->genEmail();

			if (is_banned_email($email))
				message($lang_rl['Ban email'].htmlspecialchars($email));

			$result = $db->query('SELECT id FROM '.$db->prefix.'users WHERE id!='.$pun_user['id'].' AND email=\''.$db->escape($email).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
			if ($db->num_rows($result))
				message($lang_rl['Duble email2'].htmlspecialchars($email));

			$result = $db->query('SELECT user_id FROM '.$db->prefix.'reglog WHERE user_id!='.$pun_user['id'].' AND email=\''.$db->escape($email).'\'') or error('Unable to fetch reglog info', __FILE__, __LINE__, $db->error());
			if ($db->num_rows($result))
				message($lang_rl['Duble email2'].htmlspecialchars($email));

			$dUser = $LgzPrf->genDataAll($pun_config);

			$db->query('DELETE FROM '.$db->prefix.'reglog WHERE user_id='.$pun_user['id'].' AND provider=\''.$db->escape($dUser['provider']).'\'') or error('Unable to delete reglog info', __FILE__, __LINE__, $db->error());

			$db->query('INSERT INTO '.$db->prefix.'reglog (user_id, identity, provider, email, uid) VALUES('.$pun_user['id'].', \''.$db->escape($dUser['identity']).'\', \''.$db->escape($dUser['provider']).'\', \''.$db->escape($dUser['email']).'\', \''.$db->escape($dUser['uid']).'\')') or error('Unable to create reglog', __FILE__, __LINE__, $db->error());

			if (is_valid_email($dUser['email']) && !is_valid_email($pun_user['email']))
				$db->query('UPDATE '.$db->prefix.'users SET email=\''.$db->escape($dUser['email']).'\' WHERE id='.$pun_user['id']) or error('Unable to update email address', __FILE__, __LINE__, $db->error());

			redirect('reglog.php', $lang_rl['Setacc redirect']);
		}
	}

	$page_head['rl_css'] = '<style type="text/css">.pun .blocktable .provh {text-align: center; width: 105px;} .pun .blocktable .statush {text-align: center; width: 100%} .pun .blocktable .tdel {text-align: center; width: 80px} .pun .blocktable .prov {background-image: url("img/loginza/provider_bg.png"); background-position: center center; background-repeat: no-repeat; height: 48px; text-align: center; width: 105px;} .pun .blocktable .status {width: 100%} .providers {background-image: url("img/loginza/providers_sprite.png"); background-position: 0 0; background-repeat: no-repeat; display: inline-block; height: 25px; width: 90px;} .google {background-position: 0 0;} .yandex {background-position: 0 -25px;} .mailruapi {background-position: 0 -50px;} .vkontakte {background-position: 0 -75px;} .facebook {background-position: 0 -100px;} .twitter {background-position: 0 -125px;} .loginza {background-position: 0 -150px;} .myopenid {background-position: 0 -175px;} .webmoney {background-position: 0 -200px;} .rambler {background-position: 0 -225px;} .flickr {background-position: 0 -250px;} .lastfm {background-position: 0 -275px;} .openid {background-position: 0 -300px;} .mailru {background-position: 0 -375px;} .verisign {background-position: 0 -325px;} .aol {background-position: 0 -350px;} .steam {background-position: 0 -400px;} .block2col .block .inbox {padding: 0;} </style>';

	$result = $db->query('SELECT provider, identity FROM '.$db->prefix.'reglog WHERE user_id='.$pun_user['id']) or error('Unable to fetch reglog info', __FILE__, __LINE__, $db->error());
	$ar_iden = array();
	while ($cur = $db->fetch_assoc($result))
		$ar_iden[$cur['provider']] = $cur['identity'];

	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_rl['Management']);
	define('PUN_ACTIVE_PAGE', 'profile');
	require PUN_ROOT.$prefhf.'header.php';

	$id = $pun_user['id'];
	generate_profile_menu('loginza');

?>
	<div class="block">
		<h2><span><?php echo pun_htmlspecialchars($pun_user['username']).' - '.$lang_rl['Section loginza'] ?></span></h2>
		<div id="accounts" class="blocktable">
			<div class="box">
				<div class="inbox">
					<form id="form_accounts" method="post" action="reglog.php">
						<input type="hidden" name="form_sent" value="<?php echo pun_htmlspecialchars(pun_hash($pun_user['username'].$hash_ip)) ?>" />
						<table cellspacing="0">
							<thead>
								<tr>
									<th class="provh"><?php echo $lang_rl['Provh'] ?></th>
									<th class="statush"><?php echo $lang_rl['Account'] ?></th>
									<th class="tdel"><input type="submit" name="delete" value="<?php echo $lang_rl['Delete'] ?>"></th>
								</tr>
							</thead>
							<tbody>
<?php

		foreach ($provs as $prov)
		{
			$urlLgz = $LgzAPI->getWidgetUrl(get_base_url(true).'/reglog.php', $prov, $lang_rl['lang']);

			if (isset($ar_iden[$prov]))
			{
				$status = pun_htmlspecialchars($ar_iden[$prov]);
				$disbl = '';
			}
			else
			{
				$status = '&nbsp;';
				$disbl = '" disabled="disabled';
			}

?>
								<tr>
									<td class="prov"><a href="<?php echo $urlLgz ?>" class="loginza"><span title="<?php echo $prov ?>" class="providers <?php echo $prov ?>"></span></a></td>
									<td class="status"><?php echo $status ?></td>
									<td class="tdel"><input type="checkbox" name="<?php echo 'del_acc['.$prov.']'.$disbl ?>" value="1"></td>
								</tr>
<?php

		}

?>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

	if (strpos($pun_config['o_loginza_set'], 'java') !== false)
	{
		if (isset($page_js))
			$page_js['f']['loginza'] = 'http://s1.loginza.ru/js/widget.js';
		else
			echo '<script src="http://s1.loginza.ru/js/widget.js" type="text/javascript"></script>'."\n";
	}

	require PUN_ROOT.$prefhf.'footer.php';
}