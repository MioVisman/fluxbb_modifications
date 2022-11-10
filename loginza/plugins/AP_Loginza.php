<?php
/**
 * Copyright (C) 2011-2012 Visman (visman@inbox.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', '1.3.0');
define('PLUGIN_REVISION', 5);
define('PLUGIN_NAME', 'Loginza');
define('PLUGIN_URL', pun_htmlspecialchars(get_base_url(true).'/admin_loader.php?plugin='.$_GET['plugin']));
define('PLUGIN_PROV', 'google,yandex,mailruapi,vkontakte,facebook,twitter,loginza,myopenid,linkedin,webmoney,rambler,flickr,lastfm,verisign,aol,steam,openid,mailru,livejournal');
$tabindex = 1;

// Load language file
if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/reglog.php'))
	require PUN_ROOT.'lang/'.$pun_user['language'].'/reglog.php';
else
	require PUN_ROOT.'lang/English/reglog.php';

if (file_exists(PUN_ROOT.'include/header.php'))
	$prefhf = 'include/header.php';
else
	$prefhf = 'header.php';

$arr_files = array(
	'login.php',
	'register.php',
	'profile.php',
	'include/email.php',
	'include/functions.php',
	'admin_users.php',
	$prefhf,
);
$arr_search = array(
	'				<fieldset>'."\n".'					<legend><?php echo $lang_login[\'Login legend\'] ?></legend>',
	'				<fieldset>'."\n".'					<legend><?php echo $lang_register[\'Username legend\'] ?></legend>',
	'		$db->query(\'DELETE FROM \'.$db->prefix.\'users WHERE id=\'.$id) or error(\'Unable to delete user\', __FILE__, __LINE__, $db->error());',
	'function pun_mail($to, $subject, $message, $reply_to_email = \'\', $reply_to_name = \'\')'."\n".'{'."\n".'	global $pun_config, $lang_common;',
	'					<li<?php if ($page == \'privacy\') echo \' class="isactive"\'; ?>><a href="profile.php?section=privacy&amp;id=<?php echo $id ?>"><?php echo $lang_profile[\'Section privacy\'] ?></a></li>',
	'		redirect(\'admin_users.php\', $lang_admin_users[\'Users delete redirect\']);',
	'	$page_statusinfo = \'<p class="conl">\'.$lang_common[\'Not logged in\'].\'</p>\';',
);
$arr_new = array(
	'<?php require PUN_ROOT.\'include/loginza/login.php\'; ?>'."\n".'%search%',
	'<?php require PUN_ROOT.\'include/loginza/register.php\'; ?>'."\n".'%search%',
	'%search%'."\n".'		require PUN_ROOT.\'include/loginza/profile.php\';',
	'%search%'."\n".'if (!is_valid_email($to)) { return; }',
	'%search%'."\n".'<?php if ($pun_user[\'id\'] == $id): ?>					<li<?php if ($page == \'loginza\') echo \' class="isactive"\'; ?>><a href="reglog.php">Loginza</a></li>'."\n".'<?php endif; ?>',
	'require PUN_ROOT.\'include/loginza/admin_users.php\';'."\n\n".'%search%',
	'{'."\n".'	require PUN_ROOT.\'include/loginza/header.php\';'."\n".'} // %search%',
);
?><?php
// установка изменений в файлы
function InstallModInFiles ()
{
	global $arr_files, $arr_search, $arr_new, $lang_rl;
	
	$max = count($arr_files);
	$errors = array();

	for ($i=0; $i < $max; $i++)
	{
		$file_content = file_get_contents(PUN_ROOT.$arr_files[$i]);
		if ($file_content === false)
		{
			$errors[] = $arr_files[$i].$lang_rl['Error open file'];
			continue;
		}
		$search = str_replace('%search%', $arr_search[$i], $arr_new[$i]);
		if (strpos($file_content, $search) !== false)
		{
			continue;
		}
		if (strpos($file_content, $arr_search[$i]) === false)
		{
			$errors[] = $arr_files[$i].$lang_rl['Error search'];
			continue;
		}
		$file_content = str_replace($arr_search[$i], $search, $file_content);
		$fp = fopen(PUN_ROOT.$arr_files[$i], 'wb');
		if ($fp === false)
		{
			$errors[] = $arr_files[$i].$lang_rl['Error save file'];
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
	global $arr_files, $arr_search, $arr_new, $lang_rl;

	$max = count($arr_files);
	$errors = array();

	for ($i=0; $i < $max; $i++)
	{
		$file_content = file_get_contents(PUN_ROOT.$arr_files[$i]);
		if ($file_content === false)
		{
			$errors[] = $arr_files[$i].$lang_rl['Error open file'];
			continue;
		}
		$search = str_replace('%search%', '', $arr_new[$i]);
		if (strpos($file_content, $search) === false)
		{
			$errors[] = $arr_files[$i].$lang_rl['Error delete'];
			continue;
		}
		$file_content = str_replace($search, '', $file_content);
		$fp = fopen(PUN_ROOT.$arr_files[$i], 'wb');
		if ($fp === false)
		{
			$errors[] = $arr_files[$i].$lang_rl['Error save file'];
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
	if (!function_exists('curl_init') && !ini_get('allow_url_fopen'))
		message($lang_rl['Error curl']);

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
			'provider'		=> array(
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

	$db->create_table('reglog', $schema) or error('Unable to create reglog table', __FILE__, __LINE__, $db->error());

	$db->query('DELETE FROM '.$db->prefix.'config WHERE conf_name LIKE "o_loginza_%"') or error('Unable to remove config entries', __FILE__, __LINE__, $db->error());;
	$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES(\'o_loginza_set\', \''.PLUGIN_REVISION.'\')') or error('Unable to insert into table config.', __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES(\'o_loginza_prov\', \''.$db->escape(PLUGIN_PROV).'\')') or error('Unable to insert into table config.', __FILE__, __LINE__, $db->error());

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();
	
	$err = InstallModInFiles();
	if (empty($err))
		redirect(PLUGIN_URL, $lang_rl['Red installation']);

	$pun_config['o_redirect_delay'] = 30;
	redirect(PLUGIN_URL, implode('<br />', $err));
}

// Обновления параметров
else if (isset($_POST['update']))
{
	if (isset($_POST['prov']))
	{
		$provs = explode(',', PLUGIN_PROV);
		$p = explode(',', str_replace(' ', '', $_POST['prov']));
		$y = array();
		foreach ($p as $x)
		{
			if (in_array($x, $provs))
				$y[] = $x;
		}
		if (empty($y))
			$prov = PLUGIN_PROV;
		else
			$prov = implode(',', $y);
	}
	else
		$prov = PLUGIN_PROV;

	$java = (isset($_POST['rejim']) && $_POST['rejim'] == '1') ? '-java' : '';

	$db->query('DELETE FROM '.$db->prefix.'config WHERE conf_name LIKE "o_loginza_%"') or error('Unable to remove config entries', __FILE__, __LINE__, $db->error());;
	$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES(\'o_loginza_set\', \''.PLUGIN_REVISION.$java.'\')') or error('Unable to insert into table config.', __FILE__, __LINE__, $db->error());
	$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES(\'o_loginza_prov\', \''.$db->escape($prov).'\')') or error('Unable to insert into table config.', __FILE__, __LINE__, $db->error());

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();

	redirect(PLUGIN_URL, $lang_rl['Reg update']);
}

// Удаление мода (таблицу reglog через плагин не удалить! :P)
else if (isset($_POST['delete']))
{
	$db->query('DELETE FROM '.$db->prefix.'config WHERE conf_name LIKE "o_loginza_%"') or error('Unable to remove config entries', __FILE__, __LINE__, $db->error());;

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();
	
	$err = DeleteModInFiles();
	if (empty($err))
		redirect(PLUGIN_URL, $lang_rl['Red delete']);

	$pun_config['o_redirect_delay'] = 30;
	redirect(PLUGIN_URL, implode('<br />', $err));
}

// Display the admin navigation menu
generate_admin_menu($plugin);
?>
	<div id="loginza" class="plugin blockform">
		<h2><span><?php echo PLUGIN_NAME.' v.'.PLUGIN_VERSION ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_rl['plugin_desc'] ?></p>
				<form action="<?php echo PLUGIN_URL ?>" method="post">
					<p>
<?php
if (!isset($pun_config['o_loginza_set']))
{
?>
						<input type="submit" name="installation" value="<?php echo $lang_rl['installation'] ?>" />&nbsp;<?php echo $lang_rl['installation_info'] ?><br />
					</p>
				</form>
			</div>
		</div>
<?php
} else {
?>
						<input type="submit" name="delete" value="<?php echo $lang_rl['delete'] ?>" />&nbsp;<?php echo $lang_rl['delete_info'] ?><br /><br />
					</p>
				</form>
			</div>
		</div>

		<h2 class="block2"><span><?php echo $lang_rl['configuration'] ?></span></h2>
		<div class="box">
			<form method="post" action="<?php echo PLUGIN_URL ?>">
				<p class="submittop"><input type="submit" name="update" value="<?php echo $lang_rl['update'] ?>" tabindex="<?php echo $tabindex++ ?>" /></p>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_rl['legend'] ?></legend>
						<div class="infldset">
						<table cellspacing="0">
							<tr>
								<th scope="row"><label for="prov"><?php echo $lang_rl['prov'] ?></label></th>
								<td>
									<input type="text" name="prov" size="50" maxlength="250" tabindex="<?php echo $tabindex++ ?>" value="<?php echo pun_htmlspecialchars($pun_config['o_loginza_prov']) ?>" />
									<?php echo $lang_rl['prov_info']."\n" ?>
									<br />
									<?php echo $lang_rl['prov_info2'].str_replace(',', ', ', PLUGIN_PROV)."\n" ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="rejim"><?php echo $lang_rl['rejim'] ?></label></th>
								<td>
									<label><input type="checkbox" name="rejim" value="1" tabindex="<?php echo $tabindex++ ?>"<?php echo (strstr($pun_config['o_loginza_set'], 'java') !== false) ? ' checked="checked"' : '' ?> />&#160;&#160;<?php echo $lang_rl['rejim info'] ?></label>
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="update" value="<?php echo $lang_rl['update'] ?>" tabindex="<?php echo $tabindex++ ?>" /></p>
			</form>
		</div>
<?php
}
?>
	</div>
<?php
