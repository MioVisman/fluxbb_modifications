##
##
##        Mod title:  New Private Messaging System
##
##      Mod version:  1.8.1
##  Works on FluxBB:  1.5.9
##     Release date:  2015-12-30
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (mio.visman@yandex.ru)
##
##      Description:  Добавляет новую систему личных сообщений.
##                    Мод несовместим с системами личных сообщений от других авторов.
##                    Adds New Private Messaging System. 
##                    This mod is incompatible with Private Messaging System from other authors.
##
##   Repository URL:  http://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##                    http://fluxbb.org.ru/forum/viewtopic.php?id=3133
##                    http://fluxbb.org/forums/viewtopic.php?id=4495
##
##   Affected files:  viewtopic.php
##                    profile.php
##                    header.php
##                    /lang/[language]/common.php
##                    admin_users.php
##
##       Affects DB:  Yes
##
##            Notes:  Russian/English
##                    Спасибо artoodetoo за помощь.
##                    Thanks to artoodetoo for help.
##                    Thanks to quy for help.
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

install_mod.php to /
pmsnew.php to /
img/ to /img/
include/ to /include/
lang/ to /lang/
plugins/ to /plugins/
style/ to /style/

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
'PMsend' => 'Send private message',
'PMnew' => 'New private message',
'PMmess' => 'You have new private messages (%s msgs.).',
'Show' => 'Show',

#   ATTENTION!!!   ATTENTION!!!   ATTENTION!!!
# For Russian
# 'PM' => 'ЛС',
# 'PMsend' => 'Отправить личное сообщение',
# 'PMnew' => 'Новое личное сообщение',
# 'PMmess' => 'У вас есть новые личные сообщения (%s шт.).',
# 'Show' => 'Показать',

#
#---------[ 6. SAVE ]---------------------------------------------------------
#

/lang/[language]/common.php

#
#---------[ 7. OPEN ]---------------------------------------------------------
#

viewtopic.php

#
#---------[ 8. FIND ]---------------------------------------------------------
#

// Retrieve the posts (and their respective poster/online status)
$result = $db->query('SELECT u.email, u.title, u.url, u.location, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, g.g_promote_next_group, o.user_id AS is_online FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.id IN ('.implode(',', $post_ids).') ORDER BY p.id', true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 9. REPLACE WITH ]-------------------------------------------------
#

// Retrieve the posts (and their respective poster/online status)
// add "g.g_pm, u.messages_enable," - New PMS
$result = $db->query('SELECT u.email, u.title, u.url, u.location, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, u.messages_enable, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, g.g_pm, g.g_promote_next_group, o.user_id AS is_online FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.id IN ('.implode(',', $post_ids).') ORDER BY p.id', true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 10. FIND ]--------------------------------------------------------
#

				$user_contacts[] = '<span class="website"><a href="'.pun_htmlspecialchars($cur_post['url']).'" rel="nofollow">'.$lang_topic['Website'].'</a></span>';
			}
		}

#
#---------[ 11. AFTER, ADD ]--------------------------------------------------
#

// New PMS
		if (!$pun_user['is_guest'] && $pun_config['o_pms_enabled'] == '1' && $pun_user['g_pm'] == 1 && $pun_user['messages_enable'] == 1 && $cur_post['poster_id'] != $pun_user['id'])
			if ($pun_user['g_id'] == PUN_ADMIN || ($cur_post['g_pm'] == 1 && $cur_post['messages_enable'] == 1))
			{
				$user_contacts[] = '<span class="pmsnew"><a href="pmsnew.php?mdl=post&amp;uid='.$cur_post['poster_id'].'">'.$lang_common['PM'].'</a></span>';
			}
// New PMS

#
#---------[ 12. SAVE ]--------------------------------------------------------
#

viewtopic.php

#
#---------[ 13. OPEN ]--------------------------------------------------------
#

profile.php

#
#---------[ 14. FIND ]--------------------------------------------------------
#

$result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.date_format, u.time_format, u.last_visit, g.g_id, g.g_user_title, g.g_moderator FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

#
#---------[ 15. REPLACE WITH ]------------------------------------------------
#

// add "g.g_pm, u.messages_enable," - New PMS
$result = $db->query('SELECT u.username, u.email, u.title, u.realname, u.url, u.jabber, u.icq, u.msn, u.aim, u.yahoo, u.location, u.signature, u.disp_topics, u.disp_posts, u.email_setting, u.notify_with_post, u.auto_notify, u.show_smilies, u.show_img, u.show_img_sig, u.show_avatars, u.show_sig, u.timezone, u.dst, u.language, u.style, u.num_posts, u.last_post, u.registered, u.registration_ip, u.admin_note, u.date_format, u.time_format, u.last_visit, u.messages_enable, g.g_id, g.g_user_title, g.g_moderator, g.g_pm FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

#
#---------[ 16. FIND ]--------------------------------------------------------
#

	if ($email_field != '')
	{
		$user_personal[] = '<dt>'.$lang_common['Email'].'</dt>';
		$user_personal[] = '<dd><span class="email">'.$email_field.'</span></dd>';
	}

#
#---------[ 17. AFTER, ADD ]--------------------------------------------------
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
#---------[ 18. FIND ]--------------------------------------------------------
#

			else
				$email_field = '<label class="required"><strong>'.$lang_common['Email'].' <span>'.$lang_common['Required'].'</span></strong><br /><input type="text" name="req_email" value="'.$user['email'].'" size="40" maxlength="80" /><br /></label>'."\n";
		}

#
#---------[ 19. AFTER, ADD ]--------------------------------------------------
#

// New PMS
		if ($pun_config['o_pms_enabled'] == '1' && $pun_user['g_pm'] == 1 && $pun_user['messages_enable'] == 1 && $pun_user['id'] != $id)
			if ($pun_user['g_id'] == PUN_ADMIN || ($user['g_pm'] == 1 && $user['messages_enable'] == 1))
				$email_field .= "\t\t\t\t\t\t\t".'<p><span class="pmsnew"><a href="pmsnew.php?mdl=post&amp;uid='.$id.'">'.$lang_common['PMsend'].'</a></span></p>'."\n";
// New PMS

#
#---------[ 20. FIND ]--------------------------------------------------------
#

		// Delete the user
		$db->query('DELETE FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to delete user', __FILE__, __LINE__, $db->error());

#
#---------[ 21. AFTER, ADD ]--------------------------------------------------
#

// New PMS
		require PUN_ROOT.'include/pms_new/common_pmsn.php';

		pmsn_user_delete($id, 2);

		$db->query('DELETE FROM '.$db->prefix.'pms_new_block WHERE bl_id='.$id.' OR bl_user_id='.$id) or error('Unable to delete user in pms_new_block', __FILE__, __LINE__, $db->error());
// New PMS

#
#---------[ 22. FIND ]--------------------------------------------------------
#

	// If we changed the username we have to update some stuff
	if ($username_updated)
	{

#
#---------[ 23. AFTER, ADD ]--------------------------------------------------
#

// New PMS
		$db->query('UPDATE '.$db->prefix.'pms_new_topics SET starter=\''.$db->escape($form['username']).'\' WHERE starter_id='.$id) or error('Unable to update pms_new_topics', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'pms_new_topics SET to_user=\''.$db->escape($form['username']).'\' WHERE to_id='.$id) or error('Unable to update pms_new_topics', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'pms_new_posts SET poster=\''.$db->escape($form['username']).'\' WHERE poster_id='.$id) or error('Unable to update pms_new_posts', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'pms_new_posts SET edited_by=\''.$db->escape($form['username']).'\' WHERE edited_by=\''.$db->escape($old_username).'\'') or error('Unable to update pms_new_posts', __FILE__, __LINE__, $db->error());
// New PMS

#
#---------[ 24. SAVE ]--------------------------------------------------------
#

profile.php

#
#---------[ 25. OPEN ]--------------------------------------------------------
#

header.php

#
#---------[ 26. FIND ]--------------------------------------------------------
#

if (!empty($page_head))

#
#---------[ 27. BEFORE, ADD ]-------------------------------------------------
#

// New PMS
require PUN_ROOT.'include/pms_new/pmsnheader.php';

#
#---------[ 28. FIND ]--------------------------------------------------------
#

	$links[] = '<li id="navprofile"'.((PUN_ACTIVE_PAGE == 'profile') ? ' class="isactive"' : '').'><a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].'</a></li>';

#
#---------[ 29. REPLACE WITH ]------------------------------------------------
#

	$links[] = '<li id="navprofile"'.((PUN_ACTIVE_PAGE == 'profile') ? ' class="isactive"' : '').'><a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].'</a></li>';
// New PMS
	if ($pun_config['o_pms_enabled'] == '1' && ($pun_user['g_pm'] == 1 || $pun_user['messages_new'] > 0))
		$links[] = '<li id="navpmsnew"'.((PUN_ACTIVE_PAGE == 'pms_new' || $pun_user['messages_new'] > 0) ? ' class="isactive"' : '').'><a href="pmsnew.php">'.$lang_common['PM'].(($pun_user['messages_new'] > 0) ? ' (<span'.((empty($pun_config['o_pms_flasher']) || PUN_ACTIVE_PAGE == 'pms_new') ? '' : ' class="remflasher"' ).'>'.$pun_user['messages_new'].'</span>)' : '').'</a></li>';
// New PMS

#
#---------[ 30. SAVE ]--------------------------------------------------------
#

header.php

#
#---------[ 31. OPEN ]--------------------------------------------------------
#

admin_users.php

#
#---------[ 32. FIND ]--------------------------------------------------------
#

		redirect('admin_users.php', $lang_admin_users['Users delete redirect']);

#
#---------[ 33. BEFORE, ADD ]-------------------------------------------------
#

// New PMS
		require PUN_ROOT.'include/pms_new/common_pmsn.php';
		
		foreach ($user_ids as $user_id)
			pmsn_user_delete($user_id, 2);
// New PMS

#
#---------[ 34. SAVE ]--------------------------------------------------------
#

admin_users.php
