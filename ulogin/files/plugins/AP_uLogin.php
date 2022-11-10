<?php
/**
 * Copyright (C) 2015 Visman (mio.visman@yandex.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', '1.0.0');
define('PLUGIN_REVISION', 1);
define('PLUGIN_NAME', 'uLogin');
define('PLUGIN_URL', pun_htmlspecialchars('admin_loader.php?plugin='.$_GET['plugin']));
define('PLUGIN_NET', 'dudu,facebook,foursquare,flickr,google,googleplus,instagram,lastfm,linkedin,liveid,livejournal,mailru,odnoklassniki,openid,soundcloud,steam,tumblr,twitter,uid,vimeo,vkontakte,wargaming,webmoney,yandex,youtube');
$tabindex = 1;

// Load language file
if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/ulogin.php'))
	require PUN_ROOT.'lang/'.$pun_user['language'].'/ulogin.php';
else
	require PUN_ROOT.'lang/English/ulogin.php';

$arr_files = array(
	'register.php',
	'login.php',
	'admin_users.php',
	'profile.php',
	'include/functions.php',
	'include/email.php',
	'header.php',
);
$arr_search = array(
	'				<fieldset>'."\n".'					<legend><'.'?php echo $lang_register[\'Username legend\'] ?'.'></legend>',
	'				<fieldset>'."\n".'					<legend><'.'?php echo $lang_login[\'Login legend\'] ?'.'></legend>',
	'		$db->query(\'DELETE FROM \'.$db->prefix.\'users WHERE id IN (\'.implode(\',\', $user_ids).\')\') or error(\'Unable to delete users\', __FILE__, __LINE__, $db->error());',
	'		$db->query(\'DELETE FROM \'.$db->prefix.\'users WHERE id=\'.$id) or error(\'Unable to delete user\', __FILE__, __LINE__, $db->error());',
	'					<li<'.'?php if ($page == \'privacy\') echo \' class="isactive"\'; ?'.'>><a href="profile.php?section=privacy&amp;id=<'.'?php echo $id ?'.'>"><'.'?php echo $lang_profile[\'Section privacy\'] ?'.'></a></li>',
	'function pun_mail($to, $subject, $message, $reply_to_email = \'\', $reply_to_name = \'\')'."\n".'{'."\n".'	global $pun_config, $lang_common;',
	'if (is_array($page_statusinfo))',
);
$arr_new = array(
	'<'.'?php'."\n\n".'if (!function_exists(\'ulogin_set_reglog\')) // MOD uLogin'."\n\t".'include PUN_ROOT.\'include/ulogin/functions.php\';'."\n".'ulogin_set_reglog(\'\');'."\n\n".'?'.'>'."\n".'%search%',
	'<'.'?php'."\n\n".'if (!function_exists(\'ulogin_set_reglog\')) // MOD uLogin'."\n\t".'include PUN_ROOT.\'include/ulogin/functions.php\';'."\n".'ulogin_set_reglog(empty($redirect_url) ? \'\' : $redirect_url);'."\n\n".'?'.'>'."\n".'%search%',
	'%search%'."\n\t\t".'$db->query(\'DELETE FROM \'.$db->prefix.\'ulogin WHERE user_id IN (\'.implode(\',\', $user_ids).\')\') or error(\'Unable to delete users from ulogin\', __FILE__, __LINE__, $db->error()); // MOD uLogin',
	'%search%'."\n\t\t".'$db->query(\'DELETE FROM \'.$db->prefix.\'ulogin WHERE user_id=\'.$id) or error(\'Unable to delete user from ulogin\', __FILE__, __LINE__, $db->error()); // MOD uLogin',
	'%search%'."\n".'<'.'?php if ($pun_user[\'id\'] == $id): ?'.'>					<li<'.'?php if ($page == \'ulogin\') echo \' class="isactive"\'; ?'.'>><a href="ulogin.php">uLogin</a></li>'."\n".'<'.'?php endif; // MOD uLogin ?'.'>',
	'%search%'."\n\n\t".'if (!is_valid_email($to)) // MOD uLogin'."\n\t\t".'return;',
	'if ($pun_user[\'is_guest\']) // MOD uLogin'."\n{\n\t".'if (!function_exists(\'ulogin_set_header\'))'."\n\t\t".'include PUN_ROOT.\'include/ulogin/functions.php\';'."\n\t".'ulogin_set_header($page_statusinfo);'."\n}\n".'%search%',
);
?><?php
// установка изменений в файлы
function InstallModInFiles ()
{
	global $arr_files, $arr_search, $arr_new, $lang_ulogin;
	
	$max = count($arr_files);
	$errors = array();

	for ($i=0; $i < $max; $i++)
	{
		$file_content = file_get_contents(PUN_ROOT.$arr_files[$i]);
		if ($file_content === false)
		{
			$errors[] = $arr_files[$i].$lang_ulogin['Error open file'];
			continue;
		}
		$search = str_replace('%search%', $arr_search[$i], $arr_new[$i]);
		if (strpos($file_content, $search) !== false)
		{
			continue;
		}
		if (strpos($file_content, $arr_search[$i]) === false)
		{
			$errors[] = $arr_files[$i].$lang_ulogin['Error search'];
			continue;
		}
		$file_content = str_replace($arr_search[$i], $search, $file_content);
		$fp = fopen(PUN_ROOT.$arr_files[$i], 'wb');
		if ($fp === false)
		{
			$errors[] = $arr_files[$i].$lang_ulogin['Error save file'];
			continue;
		}
		fwrite ($fp, $file_content);
		fclose ($fp);
	}
	
	return $errors;
}

// удаление изменений в файлы
function DeleteModInFiles ()
{
	global $arr_files, $arr_search, $arr_new, $lang_ulogin;

	$max = count($arr_files);
	$errors = array();

	for ($i=0; $i < $max; $i++)
	{
		$file_content = file_get_contents(PUN_ROOT.$arr_files[$i]);
		if ($file_content === false)
		{
			$errors[] = $arr_files[$i].$lang_ulogin['Error open file'];
			continue;
		}
		$search = str_replace('%search%', '', $arr_new[$i]);
		if (strpos($file_content, $search) === false)
		{
			$errors[] = $arr_files[$i].$lang_ulogin['Error delete'];
			continue;
		}
		$file_content = str_replace($search, '', $file_content);
		$fp = fopen(PUN_ROOT.$arr_files[$i], 'wb');
		if ($fp === false)
		{
			$errors[] = $arr_files[$i].$lang_ulogin['Error save file'];
			continue;
		}
		fwrite ($fp, $file_content);
		fclose ($fp);
	}

	return $errors;
}

// Установка плагина/мода
if (isset($_POST['installation']))
{
	$allow_url_fopen = strtolower(@ini_get('allow_url_fopen'));

	if (!extension_loaded('curl') && (!in_array($allow_url_fopen, array('on', 'true', '1')) || !function_exists('file_get_contents')))
		message($lang_ulogin['Error curl']);

	$schema = array(
		'FIELDS'		=> array(
			'user_id'		=> array(
				'datatype'		=> 'INT(10) UNSIGNED',
				'allow_null'	=> false,
				'default'		=> '0'
			),
			'identity'		=> array(
				'datatype'		=> 'VARCHAR(250)',
				'allow_null'	=> true
			),
			'network'		=> array(
				'datatype'		=> 'VARCHAR(250)',
				'allow_null'	=> true
			),
			'email'		=> array(
				'datatype'		=> 'VARCHAR(80)',
				'allow_null'	=> true
			),
			'uid'		=> array(
				'datatype'		=> 'VARCHAR(250)',
				'allow_null'	=> true
			),
		),
		'INDEXES'		=> array(
			'user_id_idx'		=> array('user_id'),
			'identity_idx'	=> array('identity'),
			'email_idx'			=> array('email')
		)
	);

	$db->create_table('ulogin', $schema) or error('Unable to create ulogin table', __FILE__, __LINE__, $db->error());

	$db->query('DELETE FROM '.$db->prefix.'config WHERE conf_name LIKE \'o_ulogin_%\'') or error('Unable to remove config entries', __FILE__, __LINE__, $db->error());;
	$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES(\'o_ulogin_set\', \''.$db->escape(random_pass(24)).'\')') or error('Unable to insert into table config.', __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES(\'o_ulogin_net\', \''.$db->escape(PLUGIN_NET).'\')') or error('Unable to insert into table config.', __FILE__, __LINE__, $db->error());

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();
	
	$err = InstallModInFiles();
	if (empty($err))
		redirect(PLUGIN_URL, $lang_ulogin['Red installation']);

	$pun_config['o_redirect_delay'] = 30;
	redirect(PLUGIN_URL, implode('<br />', $err));
}

// Обновления параметров
else if (isset($_POST['update']))
{
	if (isset($_POST['prov']))
	{
		$provs = explode(',', PLUGIN_NET);
		$p = explode(',', str_replace(' ', '', $_POST['prov']));
		$y = array();
		foreach ($p as $x)
		{
			if (in_array($x, $provs))
				$y[] = $x;
		}
		if (empty($y))
			$prov = PLUGIN_NET;
		else
			$prov = implode(',', $y);
	}
	else
		$prov = PLUGIN_NET;

	$db->query('DELETE FROM '.$db->prefix.'config WHERE conf_name LIKE \'o_ulogin_%\'') or error('Unable to remove config entries', __FILE__, __LINE__, $db->error());;
	$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES(\'o_ulogin_set\', \''.$db->escape(random_pass(24)).'\')') or error('Unable to insert into table config.', __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES(\'o_ulogin_net\', \''.$db->escape($prov).'\')') or error('Unable to insert into table config.', __FILE__, __LINE__, $db->error());

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();

	redirect(PLUGIN_URL, $lang_ulogin['Reg update']);
}

// Удаление мода (таблицу ulogin через плагин не удалить! :P)
else if (isset($_POST['delete']))
{
	$db->query('DELETE FROM '.$db->prefix.'config WHERE conf_name LIKE \'o_ulogin_%\'') or error('Unable to remove config entries', __FILE__, __LINE__, $db->error());;

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();
	
	$err = DeleteModInFiles();
	if (empty($err))
		redirect(PLUGIN_URL, $lang_ulogin['Red delete']);

	$pun_config['o_redirect_delay'] = 30;
	redirect(PLUGIN_URL, implode('<br />', $err));
}

// Display the admin navigation menu
generate_admin_menu($plugin);

?>
	<div id="ulogin" class="plugin blockform">
		<h2><span><?php echo PLUGIN_NAME.' v.'.PLUGIN_VERSION ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_ulogin['plugin_desc'] ?></p>
				<form action="<?php echo PLUGIN_URL ?>" method="post">
					<p>
<?php

if (!isset($pun_config['o_ulogin_set']))
{

?>
						<input type="submit" name="installation" value="<?php echo $lang_ulogin['installation'] ?>" />&#160;<?php echo $lang_ulogin['installation_info'] ?><br />
					</p>
				</form>
			</div>
		</div>
<?php

} else {

?>
						<input type="submit" name="delete" value="<?php echo $lang_ulogin['delete'] ?>" />&#160;<?php echo $lang_ulogin['delete_info'] ?><br /><br />
					</p>
				</form>
			</div>
		</div>

		<h2 class="block2"><span><?php echo $lang_ulogin['configuration'] ?></span></h2>
		<div class="box">
			<form method="post" action="<?php echo PLUGIN_URL ?>">
				<p class="submittop"><input type="submit" name="update" value="<?php echo $lang_ulogin['update'] ?>" tabindex="<?php echo $tabindex++ ?>" /></p>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_ulogin['legend'] ?></legend>
						<div class="infldset">
						<table cellspacing="0">
							<tr>
								<th scope="row"><label for="prov"><?php echo $lang_ulogin['prov'] ?></label></th>
								<td>
									<textarea name="prov" rows="4" cols="60" tabindex="<?php echo $tabindex++ ?>" ><?php echo pun_htmlspecialchars($pun_config['o_ulogin_net']) ?></textarea>
									<span><?php echo $lang_ulogin['prov_info2'].str_replace(',', ', ', PLUGIN_NET)."\n" ?></span>
									<span><?php echo $lang_ulogin['prov_info']."\n" ?></span>
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="update" value="<?php echo $lang_ulogin['update'] ?>" tabindex="<?php echo $tabindex++ ?>" /></p>
			</form>
		</div>
<?php

}

?>
	</div>
<?php
