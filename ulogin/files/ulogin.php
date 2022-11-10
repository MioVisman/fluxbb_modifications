<?php
/**
 * Copyright (C) 2015-21 Visman (mio.visman@yandex.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';

if ($pun_user['g_read_board'] == '0')
	message($lang_common['No view'], false, '403 Forbidden');

if (!isset($pun_config['o_ulogin_set']))
	message($lang_common['No permission'], false, '403 Forbidden');

require PUN_ROOT.'lang/'.$pun_user['language'].'/login.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/register.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/prof_reg.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/profile.php';
require PUN_ROOT.'include/ulogin/functions.php';
require PUN_ROOT.'include/ulogin/ulogin.class.php';
require PUN_ROOT.'include/email.php';

$hash_ip = pun_hash(get_remote_address());

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

		if ($dataHash != pun_hash($pun_config['o_ulogin_set'].pun_hash($dataUser.$hash_ip)))
			message($lang_common['Bad request']);

		$username = isset($_POST['req_user']) ? pun_trim($_POST['req_user']) : '';

		$errors  = array();
		check_username($username);

		// окончание регистрации
		if (empty($errors))
		{
			$profile = unserialize(base64_decode($dataUser));

			$uLogin = new uLoginClass($db, $pun_config);
			$new_uid = $uLogin->RegistrationUser($profile, $username, $pun_user);

			redirect('profile.php?id='.$new_uid, $lang_register['Reg complete']);
		}
	}
	else if (isset($_POST['token']))
	{
		// проверка токена безопасности
		if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] != pun_hash(get_remote_address().pun_hash($pun_config['o_ulogin_set'])))
			message($lang_common['Bad request']);

		$redirect_url = pun_htmlspecialchars(validate_redirect(isset($_GET['redirect_url']) ? pun_trim($_GET['redirect_url']) : 'index.php', 'index.php'));
		if (preg_match('%viewtopic\.php\?pid=(\d+)$%', $redirect_url, $matches))
			$redirect_url .= '#p'.$matches[1];

		$uLogin = new uLoginClass($db, $pun_config);

		// запрос профиля пользователя от uLogin
		$profile = $uLogin->GetUserFromToken($_POST['token']);

		if ($profile === false)
			message(ulogin_lang('Error ulogin').$uLogin->GetError()); // ошибка

		$user_id = $uLogin->GetUserIdByIdentity($profile['identity']);

		if ($user_id !== false) // есть юзер с таким идентификатором
		{
		  if ($user_id > 1 && $uLogin->IsUserById($user_id)) // и он существует в таблице users
		  {
				$uLogin->LoginUser($user_id); // запускаем юзера на форум

				redirect($redirect_url, $lang_login['Login redirect']);
			}
			else // или нет его в users :(
				$uLogin->DeleteUserById($user_id);
		}

		$email = $uLogin->GetEmail($profile);

		if (!empty($email))
		{
			$user_id = $uLogin->GetUserIdByEmail($email);
			$user_ids = $uLogin->GetUserIdsByEmail($email);

			if (count($user_ids) > 1)
				message(ulogin_lang('Duble email2').pun_htmlspecialchars($email)); // ошибка: у нескольких юзеров одинаковый email

			if ($user_id !== false && count($user_ids) == 1 && $user_id != $user_ids[0])
				message(ulogin_lang('Duble email2').pun_htmlspecialchars($email)); // ошибка: у нескольких юзеров одинаковый email

			if ($user_id !== false || count($user_ids) == 1) // есть юзер с таким email
			{
				$user_id = ($user_id !== false) ? $user_id : $user_ids[0];

				if ($user_id > 1 && $uLogin->IsUserById($user_id))
				{
					$uLogin->SaveProfile($user_id, $profile); // сохраняем новый профиль юзера
					$uLogin->LoginUser($user_id); // запускаем юзера на форум

					redirect($redirect_url, $lang_login['Login redirect']);
				}
				else
					$uLogin->DeleteUserById($user_id);
			}
		}

		// регистрация новых юзеров запрещена
		if ($pun_config['o_regs_allow'] == '0')
			message($lang_register['No new regs']);

		// Check that someone from this IP didn't register a user within the last hour (DoS prevention)
		$result = $db->query('SELECT 1 FROM '.$db->prefix.'users WHERE registration_ip=\''.get_remote_address().'\' AND registered>'.(time() - 3600)) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

		if ($db->result($result))
			message($lang_register['Registration flood']);

		// проверка на запрет данного email
		if (!empty($profile['email']) && is_banned_email($profile['email']))
			message(ulogin_lang('Ban email').pun_htmlspecialchars($profile['email']));

		// получение вариантов имени для ручного ввода
		$usernames = $uLogin->GenUserNames($profile);
		foreach ($usernames as $username)
		{
			$errors  = array();
			check_username($username);
			if (empty($errors))
				break;
		}

		$dataUser = base64_encode(serialize($profile));
		$dataHash = pun_hash($pun_config['o_ulogin_set'].pun_hash($dataUser.$hash_ip));
		$errors  = array();
	}
	else
		message(ulogin_lang('No token'));


	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_register['Register']);
	$required_fields = array('req_user' => $lang_common['Username']);
	$focus_element = array('register', 'req_user');
	define('PUN_ACTIVE_PAGE', 'register');
	require PUN_ROOT.'header.php';

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
		<form id="register" method="post" action="ulogin.php?action=endreg" onsubmit="this.register.disabled=true;if(process_form(this)){return true;}else{this.register.disabled=false;return false;}">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_register['Username legend'] ?></legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
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

	require PUN_ROOT.'footer.php';
}


// обработка пользователя
else
{
	// удаление записи по конретному identity
	if (isset($_GET['delete']) && isset($_GET['csrf_token']))
	{
		if ($_GET['csrf_token'] != pun_hash($pun_user['id'].$_GET['delete'].$hash_ip))
			message($lang_common['Bad request']);

		$db->query('DELETE FROM '.$db->prefix.'ulogin WHERE user_id='.$pun_user['id'].' AND identity=\''.$db->escape($_GET['delete']).'\'') or error('Unable to delete ulogin info', __FILE__, __LINE__, $db->error());

		redirect('ulogin.php', ulogin_lang('Delete redirect'));
	}
	// добавление привязки нового аккаунта соцсети для текущего юзера
	else if (isset($_POST['token']))
	{
		// проверка токена безопасности
		if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] != pun_hash(get_remote_address().pun_hash($pun_config['o_ulogin_set'])))
			message($lang_common['Bad request']);

		$uLogin = new uLoginClass($db, $pun_config);

		// запрос профиля пользователя от uLogin
		$profile = $uLogin->GetUserFromToken($_POST['token']);

		if ($profile === false)
			message(ulogin_lang('Error ulogin').$uLogin->GetError()); // ошибка

		$user_id = $uLogin->GetUserIdByIdentity($profile['identity']);

		if ($user_id !== false) // есть юзер с таким идентификатором
		{
			if ($pun_user['id'] != $user_id)
			{
				$pun_config['o_redirect_delay'] = 10;
				redirect('ulogin.php', ulogin_lang('Duble identity'));
			}
		}
		else
		{
			$email = $uLogin->GetEmail($profile);

			if (!empty($email))
			{
				if (is_banned_email($email))
					message(ulogin_lang('Ban email').htmlspecialchars($email)); // email забанен

				$user_id = $uLogin->GetUserIdByEmail($email);
				$user_ids = $uLogin->GetUserIdsByEmail($email);

				if (count($user_ids) > 1)
					message(ulogin_lang('Duble email2').pun_htmlspecialchars($email)); // ошибка: у нескольких юзеров одинаковый email

				if ($user_id !== false && count($user_ids) == 1 && $user_id != $user_ids[0])
					message(ulogin_lang('Duble email2').pun_htmlspecialchars($email)); // ошибка: у нескольких юзеров одинаковый email

				if ($user_id !== false || count($user_ids) == 1) // есть юзер с таким email
				{
					$user_id = ($user_id !== false) ? $user_id : $user_ids[0];

					if ($user_id != $pun_user['id'])
						message(ulogin_lang('Duble email').pun_htmlspecialchars($email)); // ошибка: у другого юзера такой email
				}

				// сохраняем валидный email в таблицу users
				if (is_valid_email($email) && !is_valid_email($pun_user['email']))
					$db->query('UPDATE '.$db->prefix.'users SET email=\''.$db->escape($email).'\' WHERE id='.$pun_user['id']) or error('Unable to update email address', __FILE__, __LINE__, $db->error());
			}

			$uLogin->SaveProfile($pun_user['id'], $profile); // сохраняем новый профиль юзера

			redirect('ulogin.php', ulogin_lang('Setacc redirect'));
		}
	}

	// отображаем страницу аккаунтов в профиле юзера
	if (!isset($page_head))
		$page_head = array();
	$page_head['ulogin_css'] = '<style type="text/css">.pun .ulogin-line {margin-top: 8px; line-height: 17px; font-weight: bold;} .ulogin-img-sm {height: 16px; width: 16px; background-image: url("img/ulogin/small.png"); margin: 0 10px 0 0; float: left;}';

	$arr_nets = array(
		'vkontakte'			=> -19,
		'odnoklassniki'		=> -42,
		'mailru'			=> -65,
		'facebook'			=> -88,
		'twitter'			=> -111,
		'google'			=> -134,
		'yandex'			=> -157,
		'livejournal'		=> -180,
		'openid'			=> -203,
		'lastfm'			=> -272,
		'linkedin'			=> -295,
		'liveid'			=> -318,
		'soundcloud'		=> -341,
		'steam'				=> -364,
		'flickr'			=> -249,
		'uid'				=> -387,
		'youtube'			=> -433,
		'webmoney'			=> -410,
		'foursquare'		=> -456,
		'tumblr'			=> -479,
		'googleplus'		=> -502,
		'dudu'				=> -525,
		'vimeo'				=> -548,
		'instagram'			=> -571,
		'wargaming'			=> -594,
	);

	foreach ($arr_nets as $key => $val)
		$page_head['ulogin_css'].= ' .'.$key.' {background-position: 0 '.$val.'px;}';

	$page_head['ulogin_css'].= '</style>';

	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), ulogin_lang('Management'));
	define('PUN_ACTIVE_PAGE', 'profile');
	require PUN_ROOT.'header.php';

	$id = $pun_user['id'];
	generate_profile_menu('ulogin');

?>
	<div class="blockform">
		<h2><span><?php echo pun_htmlspecialchars($pun_user['username']).' - '.ulogin_lang('Section ulogin') ?></span></h2>
		<div class="box">
			<div class="inform">
				<fieldset>
					<legend><?php echo ulogin_lang('Management') ?></legend>
					<div class="infldset">
<?php

	$result = $db->query('SELECT network, identity FROM '.$db->prefix.'ulogin WHERE user_id='.$pun_user['id']) or error('Unable to fetch ulogin info', __FILE__, __LINE__, $db->error());
	$i = 0;

	while ($cur = $db->fetch_assoc($result))
	{
		echo "\t\t\t\t\t\t".'<div class="ulogin-line"><img class="ulogin-img-sm '.pun_htmlspecialchars($cur['network']).'" alt="'.pun_htmlspecialchars($cur['network']).'" src="img/ulogin/blank.gif">'.pun_htmlspecialchars($cur['identity']).'&#160;<a title="'.ulogin_lang('Delete').'" href="ulogin.php?csrf_token='.pun_htmlspecialchars(pun_hash($pun_user['id'].$cur['identity'].$hash_ip)).'&amp;delete='.urlencode($cur['identity']).'"><span>&#160;X&#160;</span></a></div>'."\n";
		++$i;
	}

	if ($i < 1)
		echo "\t\t\t\t\t\t<p><span>".ulogin_lang('No accounts')."</span></p>\n";

?>
					</div>
				</fieldset>
<?php

	ulogin_set_reglog('ulogin.php', ulogin_lang('Add new account'));

?>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}
