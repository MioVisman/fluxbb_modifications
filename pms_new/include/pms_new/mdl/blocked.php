<?php

/**
 * Copyright (C) 2008-2010 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * Copyright (C) 2010 Visman (visman@inbox.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN') || !defined('PUN_PMS_NEW'))
	exit;

define('PUN_PMS_LOADED', 1);

if ($pun_user['g_pm'] != 1 || $pun_user['messages_enable'] == 0)
	message($lang_common['Bad request']);

?>
<div class="linkst">
	<div class="inbox crumbsplus">
		<ul class="crumbs">
			<li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li>
			<li><span>»&#160;</span><a href="pmsnew.php"><?php echo $lang_pmsn['PM'] ?></a></li>
			<li><span>»&#160;</span><strong><?php echo $lang_pmsn[$pmsn_modul] ?></strong></li>
		</ul>
		<div class="pagepost"></div>
		<div class="clearer"></div>
	</div>
</div>
<?php

generate_pmsn_menu($pmsn_modul);

// Determine the topic offset (based on $_GET['p'])
$num_pages = ceil($pmsn_kol_save / $pun_user['disp_topics']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
$start_from = $pun_user['disp_topics'] * ($p - 1);

// Generate paging links
$paging_links = '<span class="pages-label">'.$lang_common['Pages'].' </span>'.paginate($num_pages, $p, 'pmsnew.php?mdl=save');

$pmsn_f_savedel = '<input type="submit" name="delete" value="'.$lang_pmsn['Delete'].'">';

?>
<script language="JavaScript" type="text/JavaScript">
function ChekUncheck()
{
	var i;
	for (i = 0; i < document.usernumb.elements.length; i++)
	{
		if(document.usernumb.chek.checked==true)
		{
			document.usernumb.elements[i].checked = true;
		} else {
			document.usernumb.elements[i].checked = false;
		}
	}
}
</script>

	<form method="post" action="pmsnew.php?mdl=blockedq" name="usernumb">
	<input type="hidden" name="csrf_hash" value="<?php echo $pmsn_csrf_hash; ?>" />
	<input type="hidden" name="p" value="<?php echo $p; ?>" />
	<div class="blockform">
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<h2><span>&#160;</span></h2>
		<div id="users1" class="blocktable">
			<div class="box">
				<div class="inbox">
					<table cellspacing="0">
					<thead>
						<tr>
							<th class="tcl" scope="col"><?php echo $lang_common['Username'] ?></th>
							<th class="tc2" scope="col"><?php echo $lang_common['Title'] ?></th>
							<th class="tcr" scope="col"><?php echo $lang_common['Registered'] ?></th>
							<th scope="col" style="width: 20px;"><input name="chek" type="checkbox" value="" onClick="ChekUncheck()"></th>
						</tr>
					</thead>
					<tbody>
<?php

$result = $db->query('SELECT b.bl_user_id, b.bl_user, u.id, u.title, u.registered, g.g_id, g.g_user_title FROM '.$db->prefix.'pms_new_block AS b LEFT JOIN '.$db->prefix.'users AS u ON b.bl_user_id=u.id LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE bl_id='.$pun_user['id'].' ORDER BY b.bl_user LIMIT '.$start_from.','.$pun_user['disp_topics']) or error('Unable to fetch pms_new_block and users', __FILE__, __LINE__, $db->error());

if ($db->num_rows($result))
{
	while ($user_data = $db->fetch_assoc($result))
	{
		if (!$user_data['id'])
		{
			$user_name_field = pun_htmlspecialchars($user_data['bl_user']);
			$user_title_field = '&nbsp;';
			$user_data_field = '&nbsp;';
		}
		else
		{
			$user_name_field = '<a href="profile.php?id='.$user_data['id'].'">'.pun_htmlspecialchars($user_data['bl_user']).'</a>';
			$user_title_field = get_title($user_data);
			$user_data_field = format_time($user_data['registered'], true);
		}

?>
						<tr>
							<td class="tcl"><?php echo $user_name_field ?></td>
							<td class="tc2"><?php echo $user_title_field ?></td>
							<td class="tcr"><?php echo $user_data_field ?></td>
							<td style="width: 20px;"><input type="checkbox" name="user_numb[<?php echo $user_data['bl_user_id']?>]" value="1"></td>
						</tr>
<?php

	}
}
else
{
	echo "\t\t\t\t\t\t".'<tr><td class="tcl" colspan="4">'.$lang_pmsn['Empty'].'</td></tr>'."\n";
	$pmsn_f_savedel = '';
}

?>
					</tbody>
					</table>
				</div>
			</div>
		</div>
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<p class="postlink conr"><?php echo $pmsn_f_savedel ?></p>
	</div>
	</form>
<?php
