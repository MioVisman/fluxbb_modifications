##
##
##        Mod title:  New Private Messaging System
##
##      Mod version:  0.9 for TEST
##  Works on FluxBB:  1.4.2
##     Release date:  2010-08-23
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (visman@inbox.ru)
##
##      Description:  ��������� ����� ������� ������ ���������.
##                    ��� ����������� � ��������� ������ ��������� �� ������ �������.
##                    Adds New Private Messaging System. 
##                    This mod is incompatible with Private Messaging System from other authors.
##
##   Repository URL:  http://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##                    http://fluxbb.org.ru/forum/viewtopic.php?id=3133
##                    http://fluxbb.org/forums/viewtopic.php?id=4495
##
##   Affected files:  viewtopic.php
##                    profile.php
##                    /include/functions.php
##                    /lang/[language]/common.php
##
##       Affects DB:  Yes
##
##            Notes:  Russian/English
##
##       DISCLAIMER:  Please note that "mods" are not officially supported by
##                    FluxBB. Installation of this modification is done at 
##                    your own risk. Backup your forum database and any and
##                    all applicable files before proceeding.
##
##


#
#---------[ 1. UPLOAD ]-------------------------------------------------------
#

all files *.php and folders to /

#
#---------[ 2. RUN ]----------------------------------------------------------
#

install_mod.php

#
#---------[ 3. DELETE ]-------------------------------------------------------
#

install_mod.php

#
#---------[ 4. OPEN ]---------------------------------------------------------
#

/lang/[language]/common.php

#
#---------[ 5. ADD NEW ELEMENTS OF ARRAY ]------------------------------------
#

'PM' => 'PM',

# For Russian
# 'PM' => '��',
# 'PMsend' => '��������� ������ ���������',

#
#---------[ 6. SAVE ]---------------------------------------------------------
#

/lang/[language]/common.php

#
#---------[ 7. OPEN ]---------------------------------------------------------
#

/include/functions.php

#
#---------[ 8. FIND ]---------------------------------------------------------
#

	else
	{
		if (!$pun_user['is_admmod'])
		{
			if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
				$links[] = '<li id="navsearch"'.((PUN_ACTIVE_PAGE == 'search') ? ' class="isactive"' : '').'><a href="search.php">'.$lang_common['Search'].'</a></li>';

			$links[] = '<li id="navprofile"'.((PUN_ACTIVE_PAGE == 'profile') ? ' class="isactive"' : '').'><a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].'</a></li>';
			$links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.pun_hash($pun_user['id'].pun_hash(get_remote_address())).'">'.$lang_common['Logout'].'</a></li>';
		}
		else
		{
			$links[] = '<li id="navsearch"'.((PUN_ACTIVE_PAGE == 'search') ? ' class="isactive"' : '').'><a href="search.php">'.$lang_common['Search'].'</a></li>';
			$links[] = '<li id="navprofile"'.((PUN_ACTIVE_PAGE == 'profile') ? ' class="isactive"' : '').'><a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].'</a></li>';
			$links[] = '<li id="navadmin"'.((PUN_ACTIVE_PAGE == 'admin') ? ' class="isactive"' : '').'><a href="admin_index.php">'.$lang_common['Admin'].'</a></li>';
			$links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.pun_hash($pun_user['id'].pun_hash(get_remote_address())).'">'.$lang_common['Logout'].'</a></li>';
		}
	}

#
#---------[ 9. REPLACE WITH ]-------------------------------------------------
#

	else
	{
// New PMS
		if ($pun_config['o_pms_enabled'] == '1' && ($pun_user['g_pm'] == 1 || $pun_user['messages_new'] > 0))
		{
			$links_pmsn = '<li id="navpmsnew"'.(((PUN_ACTIVE_PAGE == 'pms_new') || ($pun_user['messages_new'] > 0)) ? ' class="isactive"' : '').'><a href="pmsnew.php">'.$lang_common['PM'].(($pun_user['messages_new'] > 0) ? ' ('.$pun_user['messages_new'].')' : '').'</a></li>';
		}
// New PMS
		if (!$pun_user['is_admmod'])
		{
			if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
				$links[] = '<li id="navsearch"'.((PUN_ACTIVE_PAGE == 'search') ? ' class="isactive"' : '').'><a href="search.php">'.$lang_common['Search'].'</a></li>';

			$links[] = '<li id="navprofile"'.((PUN_ACTIVE_PAGE == 'profile') ? ' class="isactive"' : '').'><a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].'</a></li>';
// New PMS
			if (isset($links_pmsn))
				$links[] = $links_pmsn;
// New PMS
			$links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.pun_hash($pun_user['id'].pun_hash(get_remote_address())).'">'.$lang_common['Logout'].'</a></li>';
		}
		else
		{
			$links[] = '<li id="navsearch"'.((PUN_ACTIVE_PAGE == 'search') ? ' class="isactive"' : '').'><a href="search.php">'.$lang_common['Search'].'</a></li>';
			$links[] = '<li id="navprofile"'.((PUN_ACTIVE_PAGE == 'profile') ? ' class="isactive"' : '').'><a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].'</a></li>';
// New PMS
			if (isset($links_pmsn))
				$links[] = $links_pmsn;
// New PMS
			$links[] = '<li id="navadmin"'.((PUN_ACTIVE_PAGE == 'admin') ? ' class="isactive"' : '').'><a href="admin_index.php">'.$lang_common['Admin'].'</a></li>';
			$links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.pun_hash($pun_user['id'].pun_hash(get_remote_address())).'">'.$lang_common['Logout'].'</a></li>';
		}
	}

#
#---------[ 10. SAVE ]--------------------------------------------------------
#

/include/functions.php

#
#---------[ 11. OPEN ]--------------------------------------------------------
#

viewtopic.php

#
#---------[ 12. FIND ]--------------------------------------------------------
#

// Retrieve the posts (and their respective poster/online status)
$result = $db->query('SELECT u.email, u.title, u.url, u.location, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, o.user_id AS is_online FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.id IN ('.implode(',', $post_ids).') ORDER BY p.id', true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 13. REPLACE WITH ]------------------------------------------------
#

// Retrieve the posts (and their respective poster/online status)
// add "g.g_pm, u.messages_enable," - New PMS
$result = $db->query('SELECT u.email, u.title, u.url, u.location, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, u.messages_enable, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, g.g_pm, o.user_id AS is_online FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.id IN ('.implode(',', $post_ids).') ORDER BY p.id', true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 14. FIND ]--------------------------------------------------------
#

			if ($cur_post['url'] != '')
			{
				if ($pun_config['o_censoring'] == '1')
						$cur_post['url'] = censor_words($cur_post['url']);
						
				$user_contacts[] = '<span class="website"><a href="'.pun_htmlspecialchars($cur_post['url']).'">'.$lang_topic['Website'].'</a></span>';
			}
		}

#
#---------[ 15. AFTER, ADD ]--------------------------------------------------
#

// New PMS
		if (!$pun_user['is_guest'] && $pun_config['o_pms_enabled'] == '1' && $pun_user['g_pm'] == 1 && $pun_user['messages_enable'] == 1 && $cur_post['poster_id'] != $pun_user['id'])
			if ($pun_user['g_id'] == PUN_ADMIN || ($cur_post['g_pm'] == 1 && $cur_post['messages_enable'] == 1))
			{
				$user_contacts[] = '<span class="pmsnew"><a href="pmsnew.php?mdl=post&amp;uid='.$cur_post['poster_id'].'">'.$lang_common['PM'].'</a></span>';
			}
// New PMS

#
#---------[ 16. SAVE ]--------------------------------------------------------
#

viewtopic.php

#
#---------[ 17. OPEN ]--------------------------------------------------------
#

profile.php

#
#---------[ 18. FIND ]--------------------------------------------------------
#

$result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.date_format, u.time_format, g.g_id, g.g_user_title, g.g_moderator FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

#
#---------[ 19. REPLACE WITH ]------------------------------------------------
#

// add "g.g_pm, u.messages_enable," - New PMS
$result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.date_format, u.time_format, u.messages_enable, g.g_id, g.g_user_title, g.g_moderator, g.g_pm FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

#
#---------[ 20. FIND ]--------------------------------------------------------
#

	if ($email_field != '')
	{
		$user_personal[] = '<dt>'.$lang_common['Email'].'</dt>';
		$user_personal[] = '<dd><span class="email">'.$email_field.'</span></dd>';
	}

#
#---------[ 21. AFTER, ADD ]--------------------------------------------------
#

// New PMS
	if (!$pun_user['is_guest'] && $pun_config['o_pms_enabled'] == '1' && $pun_user['g_pm'] == 1 && $pun_user['messages_enable'] == 1)
		if ($user['g_pm'] == 1 && $user['messages_enable'] == 1)
		{
			$user_personal[] = '<dt>'.$lang_common['PM'].'</dt>';
			$user_personal[] = '<dd><span class="pmsnew"><a href="pmsnew.php?mdl=post&amp;uid='.$id.'">'.$lang_common['PMsend'].'</a></span></dd>';
		}
// New PMS

#
#---------[ 22. FIND ]--------------------------------------------------------
#

			if ($pun_config['o_regs_verify'] == '1')
				$email_field = '<p>'.sprintf($lang_profile['Email info'], $user['email'].' - <a href="profile.php?action=change_email&amp;id='.$id.'">'.$lang_profile['Change email'].'</a>').'</p>'."\n";
			else
				$email_field = '<label class="required"><strong>'.$lang_common['Email'].' <span>'.$lang_common['Required'].'</span></strong><br /><input type="text" name="req_email" value="'.$user['email'].'" size="40" maxlength="80" /><br /></label>'."\n";
		}

#
#---------[ 23. AFTER, ADD ]--------------------------------------------------
#

// New PMS
		if ($pun_config['o_pms_enabled'] == '1' && $pun_user['g_pm'] == 1 && $pun_user['messages_enable'] == 1 && $pun_user['id'] != $id)
			if ($pun_user['g_id'] == PUN_ADMIN || ($user['g_pm'] == 1 && $user['messages_enable'] == 1))
				$email_field .= "\t\t\t\t\t\t\t".'<p><span class="pmsnew"><a href="pmsnew.php?mdl=post&amp;uid='.$id.'">'.$lang_common['PMsend'].'</a></span></p>'."\n";
// New PMS

#
#---------[ 24. FIND ]--------------------------------------------------------
#

		// Delete the user
		$db->query('DELETE FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to delete user', __FILE__, __LINE__, $db->error());

#
#---------[ 25. AFTER, ADD ]--------------------------------------------------
#

// New PMS
		require PUN_ROOT.'include/pms_new/common_pmsn.php';

		pmsn_user_delete($id, 2);

		$db->query('DELETE FROM '.$db->prefix.'pms_new_block WHERE bl_id='.$id.'OR bl_user='.$id) or error('Unable to delete user in pms_new_block', __FILE__, __LINE__, $db->error());
// New PMS

#
#---------[ 26. SAVE ]--------------------------------------------------------
#

profile.php

#
#---------[ 27. OPEN ]--------------------------------------------
#

header.php

#
#---------[ 28. FIND ]-------------------------------------------------
#

if (defined('PUN_ADMIN_CONSOLE'))
{
	if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/base_admin.css'))
		echo '<link rel="stylesheet" type="text/css" href="style/'.$pun_user['style'].'/base_admin.css" />'."\n";
	else
		echo '<link rel="stylesheet" type="text/css" href="style/imports/base_admin.css" />'."\n";
}

#
#---------[ 29. AFTER, ADD ]-------------------------------------------------
#

// New PMS
if (defined('PUN_PMS_NEW'))
{
	if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/base_admin.css'))
		echo '<link rel="stylesheet" type="text/css" href="style/'.$pun_user['style'].'/newpms.css" />'."\n";
	else
		echo '<link rel="stylesheet" type="text/css" href="style/imports/newpms.css" />'."\n";
}
// New PMS

#
#---------[ 30. SAVE ]---------------------------------------------------
#

header.php
