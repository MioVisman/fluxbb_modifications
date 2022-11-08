##
##
##        Mod title:  Timelimit
##
##      Mod version:  1.0.4
##  Works on FluxBB:  1.5.1
##     Release date:  2012-10-20
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (visman@inbox.ru)
##
##      Description:  Добавляет ограничение времени редактирования и удаление сообщений/тем пользователями.
##                    Adds restriction of time of editing and removal of messages / topics for users. 
##
##   Repository URL:  http://fluxbb.org/resources/mods/timelimit/
##                    http://fluxbb.org/forums/viewtopic.php?id=4395
##
##   Affected files:  viewtopic.php
##                    edit.php
##                    delete.php
##                    /lang/[language]/post.php
##
##       Affects DB:  Yes
##
##            Notes:  Russian/English/French
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

file "AP_Timelimit.php" to "/plugins/"
file "/Russian/admin_plugin_timelimit.php" to "/lang/Russian/"
file "/English/admin_plugin_timelimit.php" to "/lang/English/"
file "/French/admin_plugin_timelimit.php" to "/lang/French/"

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

viewtopic.php

#
#---------[ 5. FIND ]---------------------------------------------------------
#

// Retrieve the posts (and their respective poster/online status)
$result = $db->query('SELECT u.email, u.title, u.url, u.location, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, o.user_id AS is_online FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.id IN ('.implode(',', $post_ids).') ORDER BY p.id', true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 6. REPLACE WITH ]-------------------------------------------------
#

// Retrieve the posts (and their respective poster/online status)
$result = $db->query('SELECT u.email, u.title, u.url, u.location, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, p.edit_post, g.g_id, g.g_user_title, o.user_id AS is_online FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.id IN ('.implode(',', $post_ids).') ORDER BY p.id', true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 7. FIND ]---------------------------------------------------------
#

			if ($cur_post['poster_id'] == $pun_user['id'])


#
#---------[ 8. REPLACE WITH ]-------------------------------------------------
#

			if ($cur_post['poster_id'] == $pun_user['id'] && ($pun_user['g_deledit_interval'] == 0 || $cur_post['edit_post'] == 1 || time()-$cur_post['posted'] < $pun_user['g_deledit_interval']))

#
#---------[ 9. SAVE ]---------------------------------------------
#

viewtopic.php

#
#---------[ 10. OPEN ]---------------------------------------------------
#

edit.php

#
#---------[ 11. FIND) ]--------------------------------------------
#

// Fetch some info about the post, the topic and the forum
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.id AS tid, t.subject, t.posted, t.first_post_id, t.sticky, t.closed, p.poster, p.poster_id, p.message, p.hide_smilies FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 12. REPLACE WITH ]-------------------------------------------------
#

// Fetch some info about the post, the topic and the forum
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.id AS tid, t.subject, t.posted, t.first_post_id, t.sticky, t.closed, p.poster, p.poster_id, p.message, p.hide_smilies, p.posted as pposted, p.edit_post FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 13. FIND ]--------------------------------------------
#

// Do we have permission to edit this post?
if (($pun_user['g_edit_posts'] == '0' ||
	$cur_post['poster_id'] != $pun_user['id'] ||
	$cur_post['closed'] == '1') &&
	!$is_admmod)
	message($lang_common['No permission'], false, '403 Forbidden');


#
#---------[ 14. AFTER, ADD ]-------------------------------------------------
#

if (!$is_admmod && $pun_user['g_deledit_interval'] != 0 && $cur_post['edit_post'] != 1 && (time()-$cur_post['pposted']) > $pun_user['g_deledit_interval'])
	message($lang_common['No permission']);


#
#---------[ 15. FIND ]---------------------------------------------------------
#

if ($is_admmod)
{
	if ((isset($_POST['form_sent']) && isset($_POST['silent'])) || !isset($_POST['form_sent']))
		$checkboxes[] = '<label><input type="checkbox" name="silent" value="1" tabindex="'.($cur_index++).'" checked="checked" />'.$lang_post['Silent edit'].'<br /></label>';
	else
		$checkboxes[] = '<label><input type="checkbox" name="silent" value="1" tabindex="'.($cur_index++).'" />'.$lang_post['Silent edit'].'<br /></label>';
}

#
#---------[ 16. AFTER, ADD ]---------------------------------------------
#

if ($pun_user['g_id'] == PUN_ADMIN)
{
	if ((isset($_POST['form_sent']) && !isset($_POST['editpost'])) || (!isset($_POST['form_sent']) && $cur_post['edit_post'] != 1))
		$checkboxes[] = '<label><input type="checkbox" name="editpost" value="1" tabindex="'.($cur_index++).'" />'.$lang_post['EditPost edit'].'<br /></label>';
	else
		$checkboxes[] = '<label><input type="checkbox" name="editpost" value="1" tabindex="'.($cur_index++).'" checked="checked" />'.$lang_post['EditPost edit'].'<br /></label>';
}

#
#---------[ 17. FIND ]-------------------------------------------------
#

		$edited_sql = (!isset($_POST['silent']) || !$is_admmod) ? ', edited='.time().', edited_by=\''.$db->escape($pun_user['username']).'\'' : '';
		
#
#---------[ 18. AFTER, ADD ]---------------------------------------------
#

		$edited_sql .= ($pun_user['g_id'] == PUN_ADMIN) ? ', edit_post='.(isset($_POST['editpost']) ? '1' : '0') : '';

#
#---------[ 19. SAVE ]---------------------------------------------------
#

edit.php

#
#---------[ 20. OPEN ]--------------------------------------------
#

delete.php

#
#---------[ 21. FIND ]-------------------------------------------------
#

// Do we have permission to edit this post?
if (($pun_user['g_delete_posts'] == '0' ||
	($pun_user['g_delete_topics'] == '0' && $is_topic_post) ||
	$cur_post['poster_id'] != $pun_user['id'] ||
	$cur_post['closed'] == '1') &&
	!$is_admmod)
	message($lang_common['No permission'], false, '403 Forbidden');

#
#---------[ 22. AFTER, ADD ]-------------------------------------------------
#

if (!$is_admmod && $pun_user['g_deledit_interval'] != 0 && (time()-$cur_post['posted']) > $pun_user['g_deledit_interval'])
	message($lang_common['No permission']);

#
#---------[ 23. SAVE ]---------------------------------------------------
#

delete.php

#
#---------[ 24. OPEN ]--------------------------------------------
#

/lang/[language]/post.php

#
#---------[ 25. ADD NEW ELEMENT OF ARRAY ]--------------------------------------------
#

'EditPost edit' => 'To allow to edit the given message without restrictions',

#   ATTENTION!!!   ATTENTION!!!   ATTENTION!!!
# For Russian
# 'EditPost edit' => 'Разрешить редактировать данное сообщение без ограничений',
# For French
# 'EditPost edit' => 'Permettre de réviser le message donné sans restrictions',

#
#---------[ 26. SAVE ]---------------------------------------------------
#

/lang/[language]/post.php
