<?php

/**
 * Copyright (C) 2008-2010 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * Copyright (C) 2010 Visman (visman@inbox.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Load the language file
require PUN_ROOT.'lang/'.$admin_language.'/admin_plugin_pms_new.php';

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);

// If the "Show text" button was clicked
if (isset($_POST['show_text']))
{

	$en_pms = intval($_POST['enable_pms']);
	$en_pms = ($en_pms == 1) ? 1 : 0;
	$g_limit = isset($_POST['g_limit']) ? array_map('trim', $_POST['g_limit']) : array();
	$g_pm = isset($_POST['g_pm']) ? array_map('trim', $_POST['g_pm']) : array();

	$db->query('UPDATE '.$db->prefix.'config SET conf_value=\''.$en_pms.'\' WHERE conf_name=\'o_pms_enabled\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());

	$result = $db->query('SELECT g_id FROM '.$db->prefix.'groups ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

	while ($cur_group = $db->fetch_assoc($result))
		if ($cur_group['g_id'] > PUN_ADMIN && $cur_group['g_id'] != PUN_GUEST)
			if (isset($g_limit[$cur_group['g_id']]))
			{
				$g_lim = isset($g_limit[$cur_group['g_id']]) ? intval($g_limit[$cur_group['g_id']]) : 0;
				$g_p = (isset($g_pm[$cur_group['g_id']]) || $cur_group['g_id'] == PUN_ADMIN) ? 1 : 0;

				$db->query('UPDATE '.$db->prefix.'groups SET g_pm='.$g_p.', g_pm_limit='.$g_lim.' WHERE g_id='.$cur_group['g_id']) or error('Unable to update user group list', __FILE__, __LINE__, $db->error());
			}

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();

	redirect(pun_htmlspecialchars('admin_loader.php?plugin=AP_PMS_New.php'), $lang_admin_plugin_pms_new['Plugin redirect']);

}
else
{
	// Display the admin navigation menu
	generate_admin_menu($plugin);

	$cur_index = 1;

?>
	<div class="plugin blockform">
		<h2><span><?php echo $lang_admin_plugin_pms_new['Plugin title'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_admin_plugin_pms_new['Explanation 1'] ?></p>
				<p><?php echo $lang_admin_plugin_pms_new['Explanation 2'] ?></p>
			</div>
		</div>

		<h2 class="block2"><span><?php echo $lang_admin_plugin_pms_new['Form title'] ?></span></h2>
		<div class="box">
			<form id="example" method="post" action="<?php echo pun_htmlspecialchars($_SERVER['REQUEST_URI']) ?>&amp;foo=<?php echo time() ?>">
				<p class="submittop"><input type="submit" name="show_text" value="<?php echo $lang_admin_plugin_pms_new['Show text button'] ?>" tabindex="<?php echo ($cur_index++) ?>" /></p>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_plugin_pms_new['Legend1'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<td>
										<span><input type="checkbox" name="enable_pms" value="1" tabindex="<?php echo ($cur_index++) ?>"<?php echo ($pun_config['o_pms_enabled'] == '1') ? ' checked="checked"' : '' ?> />&#160;&#160;<?php echo $lang_admin_plugin_pms_new['Q1'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
<?php
if ($pun_config['o_pms_enabled'] == '1')
{
?>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_plugin_pms_new['Legend2'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
							<thead>
								<tr>
									<th class="tcl" scope="col"><?php echo $lang_admin_plugin_pms_new['Group'] ?></th>
									<th class="tc2" scope="col"><?php echo $lang_admin_plugin_pms_new['Allow'] ?></th>
									<th scope="tcr"><?php echo $lang_admin_plugin_pms_new['Kolvo'] ?></th>
								</tr>
							</thead>
							<tbody>
<?php

	$result = $db->query('SELECT g_id, g_title, g_pm, g_pm_limit FROM '.$db->prefix.'groups ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

	while ($cur_group = $db->fetch_assoc($result))
		if ($cur_group['g_id'] > PUN_ADMIN && $cur_group['g_id'] != PUN_GUEST)
		{
?>
								<tr>
									<td class="tcl"><?php echo pun_htmlspecialchars($cur_group['g_title']) ?></td>
									<td class="tc2"><input type="checkbox" name="g_pm[<?php echo $cur_group['g_id'] ?>]" value="1" tabindex="<?php echo ($cur_index++) ?>"<?php echo ($cur_group['g_pm'] == 1 ? ' checked="checked"' : '')?> /></td>
									<td class="tcr"><input type="text" name="g_limit[<?php echo $cur_group['g_id'] ?>]" value="<?php echo $cur_group['g_pm_limit'] ?>"  tabindex="<?php echo ($cur_index++) ?>" size="10" maxlength="10" /></td>
								</tr>
<?php
		}
?>
							</tbody>
							</table>
						</div>
					</fieldset>
				</div>
<?php
}
?>
				<p class="submitend"><input type="submit" name="show_text" value="<?php echo $lang_admin_plugin_pms_new['Show text button'] ?>" tabindex="<?php echo ($cur_index++) ?>" /></p>
			</form>
		</div>
	</div>
<?php
}