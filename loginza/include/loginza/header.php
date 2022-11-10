<?php
/**
 * Copyright (C) 2011 Visman (visman@inbox.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if (!defined('PUN'))
	exit;

// Load language file
if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/reglog.php'))
	require PUN_ROOT.'lang/'.$pun_user['language'].'/reglog.php';
else
	require PUN_ROOT.'lang/English/reglog.php';

if (file_exists(PUN_ROOT.'img/loginza/button_'.$lang_rl['lang'].'.gif'))
	$btnlgz = 'img/loginza/button_'.$lang_rl['lang'].'.gif';
else
	$btnlgz = 'img/loginza/button_en.gif';

if (!class_exists('LoginzaAPI'))
	require PUN_ROOT.'include/loginza/LoginzaAPI.class.php';

$LgzAPI = new LoginzaAPI();
$urlLgz = $LgzAPI->getWidgetUrl(get_base_url(true).'/reglog.php?redirect_url='.get_current_url(), $pun_config['o_loginza_prov'], $lang_rl['lang']);

$page_statusinfo = '<p class="conl"><span>'.$lang_common['Not logged in'].'</span>&nbsp;<a href="'.pun_htmlspecialchars($urlLgz).'" class="loginza"><img alt="'.$lang_rl['Loginza reglog'].'" src="'.$btnlgz.'" style="margin-bottom: -0.5em;"></a></p>';
