<?php
/**
 * Copyright (C) 2011-2013 Visman (visman@inbox.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if (!defined('PUN'))
	exit;

// Load language file
if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/reglog.php'))
	require PUN_ROOT.'lang/'.$pun_user['language'].'/reglog.php';
else
	require PUN_ROOT.'lang/English/reglog.php';

if (!class_exists('LoginzaAPI'))
	require PUN_ROOT.'include/loginza/LoginzaAPI.class.php';

$LgzAPI = new LoginzaAPI();
$urlLgz = $LgzAPI->getWidgetUrl(get_base_url(true).'/reglog.php'.(isset($redirect_url) ? '?redirect_url='.$redirect_url : ''), $pun_config['o_loginza_prov'], $lang_rl['lang']);
?>
				<fieldset>
					<legend><?php echo $lang_rl['Loginza reglog'] ?></legend>
					<div class="infldset">
<?php
if (strpos($pun_config['o_loginza_set'], 'java') !== false)
{
	if (isset($page_js))
		$page_js['f']['loginza'] = '//loginza.ru/js/widget.js';
	else
		echo '<script src="//loginza.ru/js/widget.js" type="text/javascript"></script>'."\n";
}
?>
						<p class="actions"><span><a rel="nofollow" href="<?php echo pun_htmlspecialchars($urlLgz) ?>" class="loginza"><?php echo $lang_rl['Loginza log'] ?></a></span></p>
					</div>
				</fieldset>
