<?php

/**
 * Copyright (C) 2008-2010 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * Copyright (C) 2010 Visman (visman@inbox.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if (!defined('PUN'))
	exit;

require PUN_ROOT.'lang/'.$pun_user['language'].'/pms_new.php';

function generate_pmsn_menu($page = '')
{
	global $pun_config, $pun_user, $lang_pmsn, $lang_common, $pmsn_kol_list, $pmsn_kol_new, $pmsn_kol_save;
	global $sidamp, $sidvop;

?>
<div class="block2col">
	<div class="blockmenu">
<?php
	if ($pun_user['messages_enable'] == 1 && $pun_user['g_pm'] == 1)
	{
?>
		<h2><span><?php echo $lang_pmsn['Boxs'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li<?php if ($page == 'new')  echo ' class="isactive"'; ?>><a href="pmsnew.php<?php echo $sidvop ?>"><?php echo $lang_pmsn['mNew'].(($pmsn_kol_new==0) ? '' : '&nbsp;('.$pmsn_kol_new.')') ?></a></li>
					<li<?php if ($page == 'list') echo ' class="isactive"'; ?>><a href="pmsnew.php?mdl=list<?php echo $sidamp ?>"><?php echo $lang_pmsn['mList'].'&nbsp;('.$pmsn_kol_list.')' ?></a></li>
					<li<?php if ($page == 'save') echo ' class="isactive"'; ?>><a href="pmsnew.php?mdl=save<?php echo $sidamp ?>"><?php echo $lang_pmsn['mSave'].(($pmsn_kol_save==0) ? '' : '&nbsp;('.$pmsn_kol_save.')') ?></a></li>
				</ul>
			</div>
		</div>
<?php
		if ($pun_user['g_pm_limit'] != 0)
		{
?>
		<br />
		<h2><span><?php echo $lang_pmsn['Storage'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li<?php if ($pmsn_kol_list >= $pun_user['g_pm_limit']) echo ' class="isactive"'; ?>><?php echo $lang_pmsn['mList'].': '.intval($pmsn_kol_list/$pun_user['g_pm_limit']*100).'%' ?></li>
					<li<?php if ($pmsn_kol_save >= $pun_user['g_pm_limit']) echo ' class="isactive"'; ?>><?php echo $lang_pmsn['mSave'].': '.intval($pmsn_kol_save/$pun_user['g_pm_limit']*100).'%' ?></li>
				</ul>
			</div>
		</div>
<?php
    }
?>
		<br />
		<h2><span><?php echo $lang_pmsn['Options'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li><a href="pmsnew.php?action=onoff"><?php echo $lang_pmsn['Off'] ?></a></li>
					<li><a href="pmsnew.php?action=email"><?php echo (($pun_user['messages_email'] == 1) ? $lang_pmsn['Email on'] : $lang_pmsn['Email off']) ?></a></li>
					<li<?php if ($page == 'blocked') echo ' class="isactive"'; ?>><a href="pmsnew.php?mdl=blocked"><?php echo $lang_pmsn['blocked'] ?></a></li>
				</ul>
			</div>
		</div>
	</div>

<?php
	}
	else
	{
?>
		<h2><span><?php echo $lang_pmsn['Options'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li><a href="pmsnew.php?action=onoff"><?php echo $lang_pmsn['On'] ?></a></li>
				</ul>
			</div>
		</div>
	</div>

<?php
	}
}

function pmsn_user_update($user, $flag = false)
{
	global $db, $db_type;

	$mkol = $mnew = 0;
	$result = $db->query('SELECT id, starter_id, topic_st, topic_to FROM '.$db->prefix.'pms_new_topics WHERE (starter_id='.$user.' AND topic_st<2) OR (to_id='.$user.' AND topic_to<2)') or error('Unable to fetch pms topics IDs', __FILE__, __LINE__, $db->error());

	while ($ttmp = $db->fetch_assoc($result))
	{
		if ($ttmp['starter_id'] == $user)
			$ftmp = $ttmp['topic_st'];
		else
			$ftmp = $ttmp['topic_to'];

		$mkol++;
		$mnew += $ftmp;
	}

	if ($flag && $mnew > 0)
		$tempf = 'messages_flag=1, ';
	else
		$tempf = '';

	$db->query('UPDATE '.$db->prefix.'users SET '.$tempf.'messages_new='.$mnew.', messages_all='.$mkol.' WHERE id='.$user) or error('Unable to update user', __FILE__, __LINE__, $db->error());
}

function pmsn_user_delete($user, $mflag, $topics = array())
{
	global $db, $db_type;

	$user_up = array($user);
	$topic_full_st = array();
	$topic_full_to = array();
	$topic_move_st = array();
	$topic_move_to = array();

	if (count($topics) == 0)
		$result = $db->query('SELECT id, starter_id, to_id, see_to, topic_st, topic_to  FROM '.$db->prefix.'pms_new_topics WHERE starter_id='.$user.' OR to_id='.$user) or error('Unable to fetch pms topics IDs', __FILE__, __LINE__, $db->error());
	else
		$result = $db->query('SELECT id, starter_id, to_id, see_to, topic_st, topic_to  FROM '.$db->prefix.'pms_new_topics WHERE id IN ('.implode(',', $topics).')') or error('Unable to fetch pms topics IDs', __FILE__, __LINE__, $db->error());

	while ($cur_topic = $db->fetch_assoc($result))
	{
		if ($cur_topic['starter_id'] == $user && $cur_topic['see_to'] == 0 && $cur_topic['topic_to'] != 3)
		{
			$topic_full_st[] = $cur_topic['id'];
			if (!in_array($cur_topic['to_id'], $user_up))
				$user_up[] = $cur_topic['to_id'];
		}
		else if ($cur_topic['starter_id'] == $user)
		{
			if ($mflag == 2 && $cur_topic['topic_to'] == 2)
				$topic_full_st[] = $cur_topic['id'];
			else
				$topic_move_st[] = $cur_topic['id'];
		}
		else if ($cur_topic['to_id'] == $user)
		{
			if ($mflag == 2 && $cur_topic['topic_st'] == 2)
				$topic_full_to[] = $cur_topic['id'];
			else
				$topic_move_to[] = $cur_topic['id'];
		}
	}

	if (count($topic_move_st) > 0)
		$db->query('UPDATE '.$db->prefix.'pms_new_topics SET topic_st='.$mflag.' WHERE id IN ('.implode(',', $topic_move_st).')') or error('Unable to update pms_new_topics', __FILE__, __LINE__, $db->error());

	if (count($topic_move_to) > 0)
		$db->query('UPDATE '.$db->prefix.'pms_new_topics SET topic_to='.$mflag.' WHERE id IN ('.implode(',', $topic_move_to).')') or error('Unable to update pms_new_topics', __FILE__, __LINE__, $db->error());

	if ($mflag == 2)
	{
		$topic_full = $topic_full_st + $topic_full_to;
		if (count($topic_full) > 0)
		{
			$db->query('DELETE FROM '.$db->prefix.'pms_new_posts WHERE topic_id IN ('.implode(',', $topic_full).')') or error('Unable to remove posts in pms_new_posts', __FILE__, __LINE__, $db->error());;
			$db->query('DELETE FROM '.$db->prefix.'pms_new_topics WHERE id IN ('.implode(',', $topic_full).')') or error('Unable to remove topics in pms_new_topics', __FILE__, __LINE__, $db->error());;
		}
	}
	else
	{
		if (count($topic_full_st) > 0)
			$db->query('UPDATE '.$db->prefix.'pms_new_topics SET topic_st='.$mflag.', topic_to=2 WHERE id IN ('.implode(',', $topic_full_st).')') or error('Unable to update pms_new_topics', __FILE__, __LINE__, $db->error());

		if (count($topic_full_to) > 0)
			$db->query('UPDATE '.$db->prefix.'pms_new_topics SET topic_to='.$mflag.', topic_st=2 WHERE id IN ('.implode(',', $topic_full_to).')') or error('Unable to update pms_new_topics', __FILE__, __LINE__, $db->error());
	}

	// обновляем юзеров
	foreach ($user_up as $i => $s)
		pmsn_user_update($user_up[$i]);
}