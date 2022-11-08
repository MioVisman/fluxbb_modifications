<?php

/**
 * Copyright (C) 2008-2010 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Load the language file
require PUN_ROOT.'lang/'.$admin_language.'/admin_plugin_timelimit.php';

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);

// If the "Show text" button was clicked
if (isset($_POST['show_text']))
{

	$g_order = array_map('trim', $_POST['g_order']);

	$result = $db->query('SELECT g_id, g_title, g_deledit_interval FROM '.$db->prefix.'groups ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

	while ($cur_group = $db->fetch_assoc($result))
		if ($cur_group['g_id'] > PUN_ADMIN && $cur_group['g_id'] != PUN_GUEST)
			if ($g_order[$cur_group['g_id']] != '')
			{
        $g_time = intval($g_order[$cur_group['g_id']]);
        $db->query('UPDATE '.$db->prefix.'groups SET g_deledit_interval='.$g_time.' WHERE g_id='.$cur_group['g_id']) or error('Unable to update user group list', __FILE__, __LINE__, $db->error());
			}

	redirect(pun_htmlspecialchars('admin_loader.php?plugin=AP_Timelimit.php'), $lang_admin_plugin_timelimit['Plugin redirect']);

}
else
{
	// Display the admin navigation menu
	generate_admin_menu($plugin);


?>
	<div id="exampleplugin" class="blockform">
		<h2><span><?php echo $lang_admin_plugin_timelimit['Plugin title'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_admin_plugin_timelimit['Explanation 1'] ?></p>
				<p><?php echo $lang_admin_plugin_timelimit['Explanation 2'] ?></p>
			</div>
		</div>

		<h2 class="block2"><span><?php echo $lang_admin_plugin_timelimit['Form title'] ?></span></h2>
		<div class="box">
			<form id="example" method="post" action="<?php echo pun_htmlspecialchars($_SERVER['REQUEST_URI']) ?>&amp;foo=<?php echo time() ?>">
				<p class="submittop"><input type="submit" name="show_text" value="<?php echo $lang_admin_plugin_timelimit['Show text button'] ?>" tabindex="1" /></p>
				<div class="inform">
					<fieldset>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
							<thead>
								<tr>
									<th class="tcl" scope="col"><?php echo $lang_admin_plugin_timelimit['Group'] ?></th>
									<th scope="col"><?php echo $lang_admin_plugin_timelimit['Time'] ?></th>
								</tr>
							</thead>
							<tbody>
<?php

	$cur_index = 2;
	$result = $db->query('SELECT g_id, g_title, g_deledit_interval FROM '.$db->prefix.'groups ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

	while ($cur_group = $db->fetch_assoc($result))
		if ($cur_group['g_id'] > PUN_ADMIN && $cur_group['g_id'] != PUN_GUEST)
		{
?>
								<tr>
									<th class="tcl" scope="col"><?php echo pun_htmlspecialchars($cur_group['g_title']) ?></th>
									<td><input type="text" name="g_order[<?php echo $cur_group['g_id'] ?>]" value="<?php echo $cur_group['g_deledit_interval'] ?>"  tabindex="<?php echo ($cur_index++) ?>" size="10" maxlength="10" /></td>
								</tr>
<?php
		}
?>
							</tbody>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="show_text" value="<?php echo $lang_admin_plugin_timelimit['Show text button'] ?>" tabindex="<?php echo ($cur_index++) ?>" /></p>
			</form>
		</div>
	</div>
<?php
}