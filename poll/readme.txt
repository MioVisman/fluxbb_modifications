##
##
##        Mod title:  Poll Mod
##
##      Mod version:  1.3.3
##  Works on FluxBB:  1.5.4
##     Release date:  2013-08-18
##      Review date:  YYYY-MM-DD (Leave unedited)
##           Author:  Visman (visman@inbox.ru)
##                    based on code by kg (kg@as-planned.com)
##
##      Description:  This mod will allow you to add polls of any complexity to the first post in a topic.
##                    Мод позволяет вам добавить опрос любой сложности в первый пост темы.
##
##                    v 1.1.0
##                      French is added. Thanks to jojaba.
##                      Has added use of a cache for polls (-1 query).
##                      Добавил использование кэша для опросов (-1 запрос к базе).
##                    v 1.1.1
##                      Fix French.
##                      Fix create cache.
##                      Исправлено создание кэш файлов.
##                    v 1.2.0
##                      Polish is added. Thanks to KAT.
##                      Preview of poll is added in post.php/edit.php.
##                      Добавлены превью опросов в post.php/edit.php.
##                      Оптимизировал число запросов к базе при сохранении голоса.
##                    v 1.2.1
##                      Fix form in post.php.
##                    v 1.3.0
##                      For FluxBB 1.5.0.
##                      Fix view of topics for $db_type = 'mysql';
##                    v 1.3.1
##                      For FluxBB 1.5.1.
##                      Fix markup.
##                      Fix French. Thanks to tosca.
##                    v 1.3.2
##                      Dutch is added. Thanks to glennvho.
##                      Fix for close topic.
##                    v 1.3.3
##                      For FluxBB 1.5.4.
##                      Fix for InnoDB.
##
##
##   Repository URL:  http://fluxbb.org/resources/mods/?s=author&t=Visman&v=all&o=name
##                    http://fluxbb.org.ru/forum/viewtopic.php?id=3349
##
##   Affected files:  edit.php
##                    post.php
##                    include/functions.php
##                    viewtopic.php
##                    viewforum.php
##                    search.php
##                    lang/[language]/forum.php
##
##       Affects DB:  Yes
##
##            Notes:  Russian/English/French/Polish/Dutch
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

file install_mod.php; folders include, lang, plugins to /
add_for_style-copy_to_files (contents of files) to /style (contents of files)

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

/lang/[language]/forum.php

#
#---------[ 5. ADD NEW ELEMENTS OF ARRAY ]------------------------------------
#

'Poll' => 'Poll',

#   ATTENTION!!!   ATTENTION!!!   ATTENTION!!!
# For Russian
# 'Poll' => 'Опрос',
# For French
# 'Poll' => 'Sondage',
# For Polish
# 'Poll' => 'Ankieta',
# For Dutch
# 'Poll' => 'Poll',
#   ATTENTION!!!   ATTENTION!!!   ATTENTION!!!

#
#---------[ 6. SAVE ]---------------------------------------------------------
#

/lang/[language]/forum.php

#
#---------[ 7. OPEN ]---------------------------------------------------------
#

edit.php

#
#---------[ 8. FIND ]---------------------------------------------------------
#

require PUN_ROOT.'include/common.php';

#
#---------[ 9. AFTER, ADD ]---------------------------------------------------
#

require PUN_ROOT.'include/poll.php';

#
#---------[ 10. FIND ]--------------------------------------------------------
#

// Fetch some info about the post, the topic and the forum
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.id AS tid, t.subject, t.posted, t.first_post_id, t.sticky, t.closed, p.poster, p.poster_id, p.message, p.hide_smilies FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 11. REPLACE WITH ]------------------------------------------------
#

// Fetch some info about the post, the topic and the forum
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.id AS tid, t.subject, t.posted, t.first_post_id, t.sticky, t.closed, t.poll_type, t.poll_time, t.poll_term, t.poll_kol, p.poster, p.poster_id, p.message, p.hide_smilies FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

#
#---------[ 12. FIND ]--------------------------------------------------------
#

			$errors[] = $lang_post['All caps subject'];

#
#---------[ 13. AFTER, ADD ]--------------------------------------------------
#

		poll_form_validate($cur_post['tid'], $errors);

#
#---------[ 14. FIND ]--------------------------------------------------------
#

		redirect('viewtopic.php?pid='.$id.'#p'.$id, $lang_post['Edit redirect']);

#
#---------[ 15. BEFORE, ADD ]-------------------------------------------------
#

		if ($can_edit_subject)
			poll_save($cur_post['tid']);

#
#---------[ 16. FIND ]--------------------------------------------------------
#

						<?php echo $preview_message."\n" ?>

#
#---------[ 17. AFTER, ADD ]--------------------------------------------------
#

<?php if ($can_edit_subject) poll_display_post($cur_post['tid'], $pun_user['id']); ?>

#
#---------[ 18. FIND ]--------------------------------------------------------
#

			<p class="buttons"><input type="submit" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /> <input type="submit" name="preview" value="<?php echo $lang_post['Preview'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="p" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>

#
#---------[ 19. BEFORE, ADD ]-------------------------------------------------
#

<?php if ($can_edit_subject) poll_form_edit($cur_post['tid']); ?>

#
#---------[ 20. SAVE ]--------------------------------------------------------
#

edit.php

#
#---------[ 21. OPEN ]--------------------------------------------------------
#

post.php

#
#---------[ 22. FIND ]--------------------------------------------------------
#

require PUN_ROOT.'include/common.php';

#
#---------[ 23. AFTER, ADD ]--------------------------------------------------
#

require PUN_ROOT.'include/poll.php';

#
#---------[ 24. FIND ]--------------------------------------------------------
#

	// Did everything go according to plan?
	if (empty($errors) && !isset($_POST['preview']))

#
#---------[ 25. BEFORE, ADD ]-------------------------------------------------
#

	poll_form_validate($tid, $errors);

#
#---------[ 26. FIND ]--------------------------------------------------------
#

			update_search_index('post', $new_pid, $message, $subject);

			update_forum($fid);

#
#---------[ 27. AFTER, ADD ]--------------------------------------------------
#

			poll_save($new_tid);

#
#---------[ 28. FIND ]--------------------------------------------------------
#

						<?php echo $preview_message."\n" ?>

#
#---------[ 29. AFTER, ADD ]--------------------------------------------------
#

<?php if ($fid) poll_display_post($tid, $pun_user['id']); ?>

#
#---------[ 30. FIND ]--------------------------------------------------------
#

			<p class="buttons"><input type="submit" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /> <input type="submit" name="preview" value="<?php echo $lang_post['Preview'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="p" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>

#
#---------[ 31. BEFORE, ADD ]-------------------------------------------------
#

<?php poll_form_post($tid); ?>

#
#---------[ 32. SAVE ]--------------------------------------------------------
#

post.php

#
#---------[ 33. OPEN ]--------------------------------------------------------
#

include/functions.php

#
#---------[ 34. FIND ]--------------------------------------------------------
#

	// Delete any subscriptions for this topic
	$db->query('DELETE FROM '.$db->prefix.'topic_subscriptions WHERE topic_id='.$topic_id) or error('Unable to delete subscriptions', __FILE__, __LINE__, $db->error());

#
#---------[ 35. AFTER, ADD ]--------------------------------------------------
#

	global $pun_user;
	require PUN_ROOT.'include/poll.php';
	poll_delete($topic_id);

#
#---------[ 36. SAVE ]--------------------------------------------------------
#

include/functions.php

#
#---------[ 37. OPEN ]--------------------------------------------------------
#

viewtopic.php

#
#---------[ 38. FIND ]--------------------------------------------------------
#

// Fetch some info about the topic
if (!$pun_user['is_guest'])
	$result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, t.first_post_id, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'topic_subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$pun_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
else
	$result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, t.first_post_id, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, 0 AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());

#
#---------[ 39. REPLACE WITH ]------------------------------------------------
#

require PUN_ROOT.'include/poll.php';

if (!is_null(poll_post('poll_submit')))
{
	poll_vote($id, $pun_user['id']);

	redirect('viewtopic.php?id='.$id.((isset($_GET['p']) && $_GET['p'] > 1) ? '&p='.intval($_GET['p']) : ''), $lang_poll['M0']);
}

// Fetch some info about the topic
if (!$pun_user['is_guest'])
	$result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, t.first_post_id, t.poll_type, t.poll_time, t.poll_term, t.poll_kol, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'topic_subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$pun_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
else
	$result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, t.first_post_id, t.poll_type, t.poll_time, t.poll_term, t.poll_kol, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, 0 AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());

#
#---------[ 40. FIND ]--------------------------------------------------------
#

// Retrieve the posts (and their respective poster/online status)

#
#---------[ 41. REPLACE WITH ]------------------------------------------------
#

if (in_array($cur_topic['first_post_id'], $post_ids))
	poll_display_topic($id, $pun_user['id'], $p, true);

// Retrieve the posts (and their respective poster/online status)

#
#---------[ 42. FIND ]--------------------------------------------------------
#

						<?php echo $cur_post['message']."\n" ?>

#
#---------[ 43. AFTER, ADD ]--------------------------------------------------
#

<?php if ($cur_post['id'] == $cur_topic['first_post_id']) poll_display_topic($id, $pun_user['id'], $p); ?>

#
#---------[ 44. SAVE ]--------------------------------------------------------
#

viewtopic.php

#
#---------[ 45. OPEN ]--------------------------------------------------------
#

viewforum.php

#
#---------[ 46. FIND ]--------------------------------------------------------
#

	// Fetch list of topics to display on this page
	if ($pun_user['is_guest'] || $pun_config['o_show_dot'] == '0')
	{
		// Without "the dot"
		$sql = 'SELECT id, poster, subject, posted, last_post, last_post_id, last_poster, num_views, num_replies, closed, sticky, moved_to FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $topic_ids).') ORDER BY sticky DESC, '.$sort_by.', id DESC';
	}
	else
	{
		// With "the dot"
		$sql = 'SELECT p.poster_id AS has_posted, t.id, t.subject, t.poster, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id AND p.poster_id='.$pun_user['id'].' WHERE t.id IN('.implode(',', $topic_ids).') GROUP BY t.id'.($db_type == 'pgsql' ? ', t.subject, t.poster, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to, p.poster_id' : '').' ORDER BY t.sticky DESC, t.'.$sort_by.', t.id DESC';
	}

#
#---------[ 47. REPLACE WITH ]------------------------------------------------
#

	// Fetch list of topics to display on this page
	if ($pun_user['is_guest'] || $pun_config['o_show_dot'] == '0')
	{
		// Without "the dot"
		$sql = 'SELECT id, poster, subject, posted, last_post, last_post_id, last_poster, num_views, num_replies, closed, sticky, moved_to, poll_type FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $topic_ids).') ORDER BY sticky DESC, '.$sort_by.', id DESC';
	}
	else
	{
		// With "the dot"
		$sql = 'SELECT p.poster_id AS has_posted, t.id, t.subject, t.poster, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to, t.poll_type FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'posts AS p ON t.id=p.topic_id AND p.poster_id='.$pun_user['id'].' WHERE t.id IN('.implode(',', $topic_ids).') GROUP BY t.id'.($db_type == 'pgsql' ? ', t.subject, t.poster, t.posted, t.last_post, t.last_post_id, t.last_poster, t.num_views, t.num_replies, t.closed, t.sticky, t.moved_to, p.poster_id' : '').' ORDER BY t.sticky DESC, t.'.$sort_by.', t.id DESC';
	}

#
#---------[ 48. FIND ]--------------------------------------------------------
#

			$item_status .= ' iclosed';
		}

#
#---------[ 49. AFTER, ADD ]--------------------------------------------------
#

		if ($cur_topic['poll_type'] > 0)
		{
			$item_status .= ' ipoll';
			$status_text[] = '<span class="polltext">'.$lang_forum['Poll'].'</span>';
		}

#
#---------[ 50. SAVE ]--------------------------------------------------------
#

viewforum.php

#
#---------[ 51. OPEN ]--------------------------------------------------------
#

search.php

#
#---------[ 52. FIND ]--------------------------------------------------------
#

		else
			$result = $db->query('SELECT t.id AS tid, t.poster, t.subject, t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.closed, t.sticky, t.forum_id, f.forum_name FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id WHERE t.id IN('.implode(',', $search_ids).') ORDER BY '.$sort_by_sql.' '.$sort_dir) or error('Unable to fetch search results', __FILE__, __LINE__, $db->error());

#
#---------[ 53. REPLACE WITH ]------------------------------------------------
#

		else
			$result = $db->query('SELECT t.id AS tid, t.poster, t.subject, t.last_post, t.last_post_id, t.last_poster, t.num_replies, t.closed, t.sticky, t.poll_type, t.forum_id, f.forum_name FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id WHERE t.id IN('.implode(',', $search_ids).') ORDER BY '.$sort_by_sql.' '.$sort_dir) or error('Unable to fetch search results', __FILE__, __LINE__, $db->error());

#
#---------[ 54. FIND ]--------------------------------------------------------
#

					$item_status .= ' iclosed';
				}

#
#---------[ 55. AFTER, ADD ]--------------------------------------------------
#

				if ($cur_search['poll_type'] > 0)
				{
					$item_status .= ' ipoll';
					$status_text[] = '<span class="polltext">'.$lang_forum['Poll'].'</span>';
				}

#
#---------[ 56. SAVE ]--------------------------------------------------------
#

search.php
