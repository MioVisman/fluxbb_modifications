##
##
##        Mod title:  Stick First Post
##
##      Mod version:  1.0.2
##  Works on FluxBB:  1.4.4
##     Release date:  2011-02-04
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (visman@inbox.ru)
##
##      Description:  Мод закрепления первого сообщения темы на всех ее страницах (Могут включать только админы и модераторы).
##                    To stick the first post on all pages of topic (Administrators and moderators can on/off only).
##                    
##                    
##
##   Repository URL:  http://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##                    http://fluxbb.org.ru/forum/viewtopic.php?id=3299
##                    http://fluxbb.org/forums/viewtopic.php?id=4764
##
##   Affected files:  viewtopic.php
##                    post.php
##                    edit.php
##                    /lang/[language]/post.php
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

install_mod.php to /

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

/lang/[language]/post.php

#
#---------[ 5. ADD NEW ELEMENTS OF ARRAY ]------------------------------------
#

'Stick first post' => 'To stick the first post on all pages of topic',

# For Russian
# 'Stick first post' => 'Закрепить первое сообщение на всех страницах темы',

#
#---------[ 6. SAVE ]---------------------------------------------------------
#

/lang/[language]/post.php

#
#---------[ 7. OPEN ]---------------------------------------------------------
#

viewtopic.php

#
#---------[ 8. FIND ]---------------------------------------------------------
#

// Fetch some info about the topic
if (!$pun_user['is_guest'])
	$result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, t.first_post_id, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'topic_subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$pun_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
else
	$result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, t.first_post_id, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, 0 AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());

#
#---------[ 9. REPLACE WITH ]-------------------------------------------------
#

// Fetch some info about the topic
if (!$pun_user['is_guest'])
	$result = $db->query('SELECT t.stick_fp, t.subject, t.closed, t.num_replies, t.sticky, t.first_post_id, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'topic_subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$pun_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
else
	$result = $db->query('SELECT t.stick_fp, t.subject, t.closed, t.num_replies, t.sticky, t.first_post_id, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, 0 AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());

#
#---------[ 10. FIND ]--------------------------------------------------------
#

$post_ids = array();
for ($i = 0;$cur_post_id = $db->result($result, $i);$i++)
	$post_ids[] = $cur_post_id;

#
#---------[ 11. AFTER, ADD ]--------------------------------------------------
#

// StickFP
$stick_fp_flag = ($cur_topic['stick_fp'] != 0 || (isset($cur_topic['poll_type']) && $cur_topic['poll_type'] > 0));
$stick_fp_start_from = 0;
if ($stick_fp_flag)
	$post_ids[] = $cur_topic['first_post_id'];
// StickFP

#
#---------[ 12. FIND ]--------------------------------------------------------
#

while ($cur_post = $db->fetch_assoc($result))
{

#
#---------[ 13. AFTER, ADD ]--------------------------------------------------
#

	// StickFP
	if ($stick_fp_flag)
	{
		if ($post_count == 0 && $start_from > 0)
		{
			$stick_fp_start_from = $start_from;
			$start_from = 0;
		}
		else if ($start_from == 0 && $stick_fp_start_from > 0)
		{
			$post_count = 0;
			$start_from = $stick_fp_start_from;
?>
<hr />
<?php
		}
	}
	// StickFP

#
#---------[ 14. SAVE ]--------------------------------------------------------
#

viewtopic.php

#
#---------[ 15. OPEN ]--------------------------------------------------------
#

post.php

#
#---------[ 16. FIND ]--------------------------------------------------------
#

		// If it's a new topic
		else if ($fid)
		{
			// Create the topic
			$db->query('INSERT INTO '.$db->prefix.'topics (poster, subject, posted, last_post, last_poster, sticky, forum_id) VALUES(\''.$db->escape($username).'\', \''.$db->escape($subject).'\', '.$now.', '.$now.', \''.$db->escape($username).'\', '.$stick_topic.', '.$fid.')') or error('Unable to create topic', __FILE__, __LINE__, $db->error());
			$new_tid = $db->insert_id();

#
#---------[ 17. REPLACE WITH ]------------------------------------------------
#

		// If it's a new topic
		else if ($fid)
		{
			// StickFP
			$stick_fp = ($is_admmod && isset($_POST['stickfp'])) ? 1 : 0;
			// Create the topic
			$db->query('INSERT INTO '.$db->prefix.'topics (stick_fp, poster, subject, posted, last_post, last_poster, sticky, forum_id) VALUES('.$stick_fp.', \''.$db->escape($username).'\', \''.$db->escape($subject).'\', '.$now.', '.$now.', \''.$db->escape($username).'\', '.$stick_topic.', '.$fid.')') or error('Unable to create topic', __FILE__, __LINE__, $db->error());
			$new_tid = $db->insert_id();

#
#---------[ 18. FIND ]--------------------------------------------------------
#

		$checkboxes[] = '<label><input type="checkbox" name="subscribe" value="1" tabindex="'.($cur_index++).'"'.($subscr_checked ? ' checked="checked"' : '').' />'.($is_subscribed ? $lang_post['Stay subscribed'] : $lang_post['Subscribe']).'<br /></label>';
	}

#
#---------[ 19. AFTER, ADD ]--------------------------------------------------
#

	// StickFP
	if ($is_admmod && $fid)
		$checkboxes[] = '<label><input type="checkbox" name="stickfp" value="1" tabindex="'.($cur_index++).'"'.((isset($_POST['stickfp'])) ? ' checked="checked"' : '').' />'.$lang_post['Stick first post'].'<br /></label>';
	// StickFP

#
#---------[ 20. SAVE ]--------------------------------------------------------
#

post.php

#
#---------[ 21. OPEN ]--------------------------------------------------------
#

edit.php

#
#---------[ 22. FIND ]--------------------------------------------------------
#

if ($is_admmod)
{
	if ((isset($_POST['form_sent']) && isset($_POST['silent'])) || !isset($_POST['form_sent']))
		$checkboxes[] = '<label><input type="checkbox" name="silent" value="1" tabindex="'.($cur_index++).'" checked="checked" />'.$lang_post['Silent edit'].'<br /></label>';
	else
		$checkboxes[] = '<label><input type="checkbox" name="silent" value="1" tabindex="'.($cur_index++).'" />'.$lang_post['Silent edit'].'<br /></label>';
}

#
#---------[ 23. REPLACE WITH ]------------------------------------------------
#

if ($is_admmod)
{
	if ((isset($_POST['form_sent']) && isset($_POST['silent'])) || !isset($_POST['form_sent']))
		$checkboxes[] = '<label><input type="checkbox" name="silent" value="1" tabindex="'.($cur_index++).'" checked="checked" />'.$lang_post['Silent edit'].'<br /></label>';
	else
		$checkboxes[] = '<label><input type="checkbox" name="silent" value="1" tabindex="'.($cur_index++).'" />'.$lang_post['Silent edit'].'<br /></label>';
	// StickFP
	if ($can_edit_subject)
	{
		if ((isset($_POST['form_sent']) && isset($_POST['stickfp'])) || (!isset($_POST['form_sent']) && $cur_post['stick_fp'] != 0))
			$checkboxes[] = '<label><input type="checkbox" name="stickfp" value="1" tabindex="'.($cur_index++).'" checked="checked" />'.$lang_post['Stick first post'].'<br /></label>';
		else
			$checkboxes[] = '<label><input type="checkbox" name="stickfp" value="1" tabindex="'.($cur_index++).'" />'.$lang_post['Stick first post'].'<br /></label>';
	}
	// StickFP
}

#
#---------[ 24. FIND ]--------------------------------------------------------
#

// Fetch some info about the post, the topic and the forum
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.id AS tid, t.subject, t.posted, t.first_post_id, t.sticky, t.closed, p.poster, p.poster_id, p.message, p.hide_smilies FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 25. REPLACE WITH ]------------------------------------------------
#

// Fetch some info about the post, the topic and the forum
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.id AS tid, t.stick_fp, t.subject, t.posted, t.first_post_id, t.sticky, t.closed, p.poster, p.poster_id, p.message, p.hide_smilies FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 26. FIND ]--------------------------------------------------------
#

			// Update the topic and any redirect topics
			$db->query('UPDATE '.$db->prefix.'topics SET subject=\''.$db->escape($subject).'\', sticky='.$stick_topic.' WHERE id='.$cur_post['tid'].' OR moved_to='.$cur_post['tid']) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

#
#---------[ 27. REPLACE WITH ]------------------------------------------------
#

			// StickFP
			$stick_fp = isset($_POST['stickfp']) ? '1' : '0';
			if (!$is_admmod)
				$stick_fp = $cur_post['stick_fp'];
			// StickFP
			// Update the topic and any redirect topics
			$db->query('UPDATE '.$db->prefix.'topics SET stick_fp='.$stick_fp.', subject=\''.$db->escape($subject).'\', sticky='.$stick_topic.' WHERE id='.$cur_post['tid'].' OR moved_to='.$cur_post['tid']) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

#
#---------[ 28. SAVE ]--------------------------------------------------------
#

edit.php
