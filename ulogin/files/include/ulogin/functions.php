<?php
/**
 * Copyright (C) 2015-2021 Visman (mio.visman@yandex.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;


function ulogin_lang($str)
{
	global $pun_user;
	static $lang_ulogin;

	if (!isset($lang_ulogin))
	{
		// Load language file
		if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/ulogin.php'))
			require PUN_ROOT.'lang/'.$pun_user['language'].'/ulogin.php';
		else
			require PUN_ROOT.'lang/English/ulogin.php';
	}

	if (isset($lang_ulogin[$str])) return $lang_ulogin[$str];

	return $str;
}


function ulogin_check_username($username)
{
	$username = preg_replace('%[@=\'\.-]+%u', '_', $username);
	$username = preg_replace('%[^\p{L}\p{N} _]%u', '', $username);
	$username = pun_trim(utf8_substr($username, 0, 25));

	return $username;
}


function ulogin_get_redirect_uri($url = false)
{
	global $pun_config;

	return urlencode(get_base_url(true).'/ulogin.php?csrf_token='.pun_hash(get_remote_address().pun_hash($pun_config['o_ulogin_set'])).'&redirect_url='.($url === false ? get_current_url() : $url));
}


function ulogin_set_reglog($redirect_url, $str = false)
{
	global $pun_config;

	if (!isset($pun_config['o_ulogin_set'])) return;

	ulogin_set_js();

?>
				<fieldset>
					<legend><?php echo $str === false ? ulogin_lang('uLogin reglog') : $str; ?></legend>
					<div class="infldset">
						<div id="uLogin" data-ulogin="display=panel;optional=first_name,last_name,nickname,email,sex,photo,photo_big;<?php echo ulogin_providers($pun_config['o_ulogin_net']) ?>redirect_uri=<?php echo ulogin_get_redirect_uri($redirect_url) ?>">
							<img onclick="fluxbb_ulogin_click()" alt="<?php echo ulogin_lang('uLogin log') ?>" title="<?php echo ulogin_lang('uLogin log') ?>" src="img/ulogin/ulogin-logo.png" style="height: 32px; cursor: pointer;" />
						</div>
					</div>
				</fieldset>
<?php

}


function ulogin_set_header(&$page_statusinfo)
{
	global $pun_config;

	if (!isset($pun_config['o_ulogin_set'])) return;
	if (is_array($page_statusinfo)) return;
	if (in_array(basename($_SERVER['PHP_SELF']), array('login.php', 'register.php'))) return;

	ulogin_set_js();

	$s = '<span>&#160;</span><div style="display: inline;" id="uLogin" data-ulogin="display=small;optional=first_name,last_name,nickname,email,sex,photo,photo_big;'.ulogin_providers($pun_config['o_ulogin_net']).'redirect_uri='.ulogin_get_redirect_uri().'"><img onclick="fluxbb_ulogin_click()" alt="'.ulogin_lang('uLogin reglog').'" title="'.ulogin_lang('uLogin reglog').'" src="img/ulogin/ulogin-logo.png" style="height: 19px;  margin-bottom: -0.5em; cursor: pointer;" /></div>';
	$page_statusinfo.= $s;
}


function ulogin_set_js()
{
	global $page_js, $tpl_main;
	static $script;

	if (isset($script))
	  return;

  $script = 'function fluxbb_ulogin_click()
{
	var e = document.createElement("script");
	e.src = "https://ulogin.ru/js/ulogin.js";
	e.type="text/javascript";
	document.getElementsByTagName("head")[0].appendChild(e);
}';

	if (isset($page_js) && is_array($page_js))
	{
	  $page_js['c']['ulogin'] = $script;
	  return;
	}

	$script = '<script type="text/javascript">'."\n".'/* <![CDATA[ */'."\n".$script."\n".'/* ]]> */'."\n".'</script>'."\n";

	$tpl_main = str_replace('</body>', $script.'</body>', $tpl_main);
}


function ulogin_providers($net)
{
	$net = explode(',', $net);
	$max = count($net);

	if (!$max) return '';

	$p = $h = array();

	for ($i=0; $i < $max; $i++)
	{
		if ($i < 4)
			$p[] = $net[$i];
		else
			$h[] = $net[$i];
	}

	return 'providers='.implode(',', $p).';'.(empty($h) ? '' : 'hidden='.implode(',', $h).';');
}
