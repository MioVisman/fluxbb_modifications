<?php
/**
 * Copyright (C) 2011 Visman (visman@inbox.ru)
 * based on code by kg (kg@as-planned.com)
 * Poll Mod for FluxBB, written by As-Planned.com
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if (!defined('PUN'))
	exit;

if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/poll.php'))
	require PUN_ROOT.'lang/'.$pun_user['language'].'/poll.php';
else
	require PUN_ROOT.'lang/English/poll.php';

// получение данных из формы ***************************************************
function poll_post($var, $default = null)
{
	return isset($_POST[$var]) ? $_POST[$var] : $default;
}

// запрещено ли редактировать **************************************************
function poll_noedit($tid)
{
	global $is_admmod, $pun_config;

	$top = poll_topic($tid);
	return (!$is_admmod && ($top[0] == 2 || ($top[0] == 1 && $pun_config['o_poll_time'] != 0 && (time() - $top[1] > 60 * $pun_config['o_poll_time']))));
}

// доступ запрещен *************************************************************
function poll_bad()
{
	global $pun_user, $pun_config;

	return ($pun_user['is_guest'] || $pun_config['o_poll_enabled'] != '1');
}

// может ли голосовать юзер ****************************************************
function poll_can_vote($tid, $uid)
{
	global $db, $pun_user;

	if ($pun_user['is_guest']) return false;
	
	$result = $db->query('SELECT 1 FROM '.$db->prefix.'poll_voted WHERE tid='.$tid.' AND uid='.$uid) or error('Unable to fetch poll voted info', __FILE__, __LINE__, $db->error());
	return ($db->num_rows($result) == 0);
}

// получение информации по опросу **********************************************
function poll_info($tid, $uid = NULL)
{
	global $db;

	if ($tid == 0) return null;

	$result = $db->query('SELECT question, field, choice, votes FROM '.$db->prefix.'poll WHERE tid='.$tid.' ORDER BY question, field') or error('Unable to fetch poll info', __FILE__, __LINE__, $db->error());

  $kol = 0;
  $questions = $type = $choices = $votes = array();
	while ($cur = $db->fetch_assoc($result))
	{
		$kol = $cur['question'];
		if ($cur['field'] == 0)
		{
			$questions[$kol] = $cur['choice'];
			$type[$kol] = $cur['votes'];
			$choices[$kol] = array();
			$votes[$kol] = array();
		}
		else
		{
			$choices[$kol][$cur['field']] = $cur['choice'];
			$votes[$kol][$cur['field']] = $cur['votes'];
		}
	}

	if ($kol == 0) return null;

	return array(
		'questions' => $questions,
		'choices' => $choices,
		'votes' => $votes,
		'type' => $type,
		'canVote' => (is_null($uid)) ? false : poll_can_vote($tid, $uid),
		'isGuest' => (is_null($uid) || $uid > 1) ? false : true
		);
}

// форма ввода в новой теме ****************************************************
function poll_form_post($tid)
{
	if ($tid == 0)
		poll_form(0);
}

// форма редактирования ********************************************************
function poll_form_edit($tid)
{
	poll_form($tid);
}

// данные по опросу ************************************************************
function poll_topic($tid)
{
	global $cur_post, $cur_topic;
	
	if ($tid == 0)
	{
		$rez = array(0,time(),0,0);
  }
	else if (isset($cur_topic['poll_type']))
	{
		$rez = array($cur_topic['poll_type'], $cur_topic['poll_time'], $cur_topic['poll_term'], $cur_topic['poll_kol']);
		if ($cur_topic['closed'] != '0')
			$rez[0] = 2;
	}
	else if (isset($cur_post['poll_type']))
	{
		$rez = array($cur_post['poll_type'], $cur_post['poll_time'], $cur_post['poll_term'], $cur_post['poll_kol']);
	}
	else
		$rez = array(0,time(),0,0);
		
	return $rez;
}

// форма ввода/редактирования **************************************************
function poll_form($tid)
{
	global $cur_index, $lang_poll, $pun_config, $tpl_main;

	if (poll_bad()) return;

	$top = poll_topic($tid);
	$enabled = ($top[0] > 0);
	$resu = ($top[2] > 1);
	$term = max($top[2],$pun_config['o_poll_term']);
	
	if (poll_noedit($tid))
		$edit = false;
	else
		$edit = true;

	$questions = $type = $choices = array();
	if ($edit && poll_post('form_sent', false))
	{
		$enabled = (poll_post('poll_enabled', 0) == 1);
		$resu = (poll_post('poll_result', 0) == 1);

		$questions = poll_post('poll_question', array());
		$choices = poll_post('poll_choice', array());
		$type = poll_post('poll_type', array());
	}
	else
	{
		$info = poll_info($tid);
		if (!is_null($info))
		{
			$questions = $info['questions'];
			$choices = $info['choices'];
			$type = $info['type'];
		}
  }

	$questions = array_map('pun_trim', $questions);
	$type = array_map('intval', $type);

	if (poll_noedit($tid))
	{
	?>
		<div class="inform">
			<fieldset>
				<legend><?php echo $lang_poll['Form legend'] ?></legend>
				<div class="infldset txtarea">
					<div class="rbox"><label><input disabled="disabled" type="checkbox" id="poll_enabled" name="poll_enabled" value="1" <?php if ($enabled) echo 'checked="checked"'?> /> <?php echo $lang_poll['Form enable'] ?></label></div>
					<div id="poll_input">
<?php
		if ($term > 1)
		{
?>
						<div class="rbox"><label><input disabled="disabled" type="checkbox" name="poll_result"  value="1" <?php if ($resu) echo 'checked="checked"'?> /> <?php printf($lang_poll['Form result'], $term) ?></label></div>
<?php
		}
		$fk = true;
		for ($k = 1; $k <= $pun_config['o_poll_max_ques'] && $fk; $k++)
		{
			$question = (isset($questions[$k])) ? pun_htmlspecialchars($questions[$k]) : '';
			if (empty($question))
			{
				$fk = false;
				break;
			}
			$fi = $fk;
?>
						<div id="poll_number_<?php echo $k ?>">
<?php if ($k > 1) echo "\t\t\t\t\t\t\t<br /><hr><br />\n"; ?>
							<label><?php printf($lang_poll['Form question'], $k) ?><br /><input disabled="disabled" id="poll_ques_<?php echo $k ?>" class="longinput" type="text" name="poll_question[<?php echo $k ?>]" value="<?php echo $question ?>" size="80" maxlength="250" /></label>
							<label><?php echo $lang_poll['Form type'] ?>&nbsp;<input disabled="disabled" type="text" name="poll_type[<?php echo $k ?>]" value="<?php echo ((!isset($type[$k]) || $type[$k]<2) ? '1' : $type[$k]) ?>" size="4" maxlength="2" /></label>
<?php
			for ($i = 1; $i <= $pun_config['o_poll_max_field'] && $fi; $i++)
			{
				$choice = (isset($choices[$k][$i])) ? pun_htmlspecialchars(pun_trim($choices[$k][$i])) : '';
				if (empty($choice))
				{
					$fi = false;
					break;
				}
?>
							<label><?php printf($lang_poll['Form choice'], $i) ?><br /><input disabled="disabled" class="longinput" type="text" name="poll_choice[<?php echo $k ?>][<?php echo $i?>]" value="<?php echo $choice ?>" size="80" maxlength="250" /></label>
<?php
			}
?>
						</div>
<?php
		}
?>
					</div>
				</div>
			</fieldset>
		</div>
<?php

		return;
	}

	?>
		<div class="inform">
			<fieldset>
				<legend><?php echo $lang_poll['Form legend'] ?></legend>
				<div class="infldset txtarea">
					<div class="rbox"><label><input type="checkbox" id="poll_enabled" name="poll_enabled" onclick="ForEnabled();" value="1" <?php if ($enabled) echo 'checked="checked"'?> tabindex="<?php echo $cur_index++ ?>" /> <?php echo $lang_poll['Form enable'] ?></label></div>
					<div id="poll_input">
<?php
	if ($term > 1)
	{
?>
						<div class="rbox"><label><input type="checkbox" name="poll_result"  value="1" <?php if ($resu) echo 'checked="checked"'?> tabindex="<?php echo $cur_index++ ?>" /> <?php printf($lang_poll['Form result'], $term) ?></label></div>
<?php
	}
	$fk = true;
	for ($k = 1; $k <= $pun_config['o_poll_max_ques']; $k++)
	{
		$question = (isset($questions[$k]) && $fk) ? pun_htmlspecialchars($questions[$k]) : '';
?>
						<div id="poll_number_<?php echo $k ?>">
<?php if ($k > 1) echo "\t\t\t\t\t\t\t<br /><hr><br />\n"; ?>
							<label><?php printf($lang_poll['Form question'], $k) ?><br /><input id="poll_ques_<?php echo $k ?>" class="longinput" type="text" name="poll_question[<?php echo $k ?>]" value="<?php echo $question ?>" tabindex="<?php echo $cur_index++ ?>" size="80" maxlength="250" onkeyup="ForQues(<?php echo $k ?>)" /></label>
							<label><?php echo $lang_poll['Form type'] ?>&nbsp;<input type="text" name="poll_type[<?php echo $k ?>]" value="<?php echo ((!isset($type[$k]) || $type[$k]<2) ? '1' : $type[$k]) ?>" tabindex="<?php echo $cur_index++ ?>" size="4" maxlength="2" /></label>
<?php
		if (empty($question))
			$fk = false;
		$fi = $fk;
		
		for ($i = 1; $i <= $pun_config['o_poll_max_field']; $i++)
		{
			$choice = (isset($choices[$k][$i]) && $fi) ? pun_htmlspecialchars(pun_trim($choices[$k][$i])) : '';
?>
							<label><?php printf($lang_poll['Form choice'], $i) ?><br /><input class="longinput" type="text" name="poll_choice[<?php echo $k ?>][<?php echo $i?>]" value="<?php echo $choice ?>" tabindex="<?php echo $cur_index++ ?>" size="80" maxlength="250" onkeyup="ForChoice(<?php echo $k ?>,<?php echo $i?>)" /></label>
<?php
			if (empty($choice))
				$fi = false;
		}
?>
						</div>
<?php
	}
?>
					</div>
				</div>
			</fieldset>
		</div>
<script type="text/javascript">
/* <![CDATA[ */
var max_ques = <?php echo $pun_config['o_poll_max_ques'] ?>;
var max_field = <?php echo $pun_config['o_poll_max_field'] ?>;
function ForEnabled(){var c=document.getElementById('poll_enabled');if(c.checked==true){document.getElementById('poll_input').style.display='';ForQues(1,'')}else{document.getElementById('poll_input').style.display='none'}return false}
function ForChoice(num,field,t){if(num > max_ques || field > max_field){return false}var div=document.getElementById('poll_number_'+num);var i=field+1;if(typeof t != 'undefined'){if(t == 'none'){div.getElementsByTagName('label')[i].style.display='none';ForChoice(num,i,'none')}else{div.getElementsByTagName('label')[i].style.display='';ForChoice(num,field)}}else{var inp=div.getElementsByTagName('label')[i].getElementsByTagName('input')[0];if(inp.value == ""){ForChoice(num, i, 'none')}else{ForChoice(num, i, '')}}return false}
function ForQues(num,t){if(num > max_ques){return false}var div=document.getElementById('poll_number_'+num);if(typeof t != 'undefined'){if(t == 'none'){div.style.display='none';ForQues(num+1,'none')}else{div.style.display='';ForQues(num)}}else{var inp=document.getElementById('poll_ques_'+num);if(inp.value == ""){div.getElementsByTagName('label')[1].style.display='none';ForChoice(num,1,'none');ForQues(num+1,'none')}else{div.getElementsByTagName('label')[1].style.display='';ForChoice(num,1,'');ForQues(num+1,'')}}return false}
/* ]]> */
</script>
<?php
	$tpl_main = str_replace('<body onload="', '<body onload="ForEnabled();', $tpl_main);
	$tpl_main = str_replace('<body>', '<body onload="ForEnabled()">', $tpl_main);
}

// проверяем правильность ******************************************************
function poll_form_validate($tid, &$errors)
{
	global $lang_poll, $pun_config;

	if (poll_bad() || poll_noedit($tid)) return;

	$enabled = (poll_post('poll_enabled', 0) == 1);
	if ($enabled)
	{
		$questions = poll_post('poll_question', array());
		$choices = poll_post('poll_choice', array());
		$type = poll_post('poll_type', array());

		$questions = array_map('pun_trim', $questions);
		$type = array_map('intval', $type);

		$fk = true;
		$kol = 0;
		for ($k = 1; $k <= $pun_config['o_poll_max_ques'] && $fk; $k++)
		{
			$question = (isset($questions[$k]) && $fk) ? $questions[$k] : '';
			if ($question == '')
    		$fk = false;
			else
			{
				$kol++;
    		if (pun_strlen($question) > 250)
					$errors[] = sprintf($lang_poll['Question too long'], $k);

				$koc = 0;
				$fi = $fk;
				for ($i = 1; $i <= $pun_config['o_poll_max_field'] && $fi; $i++)
				{
					$choice = (isset($choices[$k][$i]) && $fi) ? pun_trim($choices[$k][$i]) : '';
					if ($choice == '')
						$fi = false;
					else
					{
						$koc++;
						if (pun_strlen($choice) > 250)
							$errors[] = sprintf($lang_poll['Choice too long'], $k, $i);
					}
				}
				if ($koc < 2)
					$errors[] = sprintf($lang_poll['Not enough choices'], $k);
        else if (!isset($type[$k]) || $type[$k] < 1 || $type[$k] >= $koc)
					$errors[] = sprintf($lang_poll['Max variant'], $k);
			}
		}
		if ($kol == 0)
			$errors[] = $lang_poll['No question'];
	}
}

// удаление опроса *************************************************************
function poll_delete($tid, $flag = false)
{
	global $db;

	$db->query('DELETE FROM '.$db->prefix.'poll WHERE tid='.$tid) or error('Unable to remove poll', __FILE__, __LINE__, $db->error());
	$db->query('DELETE FROM '.$db->prefix.'poll_voted WHERE tid='.$tid) or error('Unable to remove poll_voted', __FILE__, __LINE__, $db->error());
	if ($flag)
		$db->query('UPDATE '.$db->prefix.'topics SET poll_type=0, poll_time=0, poll_term=0, poll_kol=0 WHERE id='.$tid) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

}

// сохраняем опрос *************************************************************
function poll_save($tid)
{
	global $db, $pun_config;

	if (poll_bad() || poll_noedit($tid)) return;

	$top = poll_topic($tid);
	$enabled = (poll_post('poll_enabled', 0) == 1);
	if ($enabled)
	{
		$term = 0;
		if (poll_post('poll_result', 0) == 1)
			$term = max($top[2],$pun_config['o_poll_term']);

		$questions = poll_post('poll_question', array());
		$choices = poll_post('poll_choice', array());
		$type = poll_post('poll_type', array());

		$questions = array_map('pun_trim', $questions);
		$type = array_map('intval', $type);

		$cur_ch = array();
		$result = $db->query('SELECT question, field FROM '.$db->prefix.'poll WHERE tid='.$tid.' ORDER BY question, field') or error('Unable to fetch poll info', __FILE__, __LINE__, $db->error());
		while ($ch = $db->fetch_assoc($result))
			$cur_ch[$ch['question']][$ch['field']] = true;

		$fk = true;
		for ($k = 1; $k <= $pun_config['o_poll_max_ques'] && $fk; $k++)
		{
			$question = (isset($questions[$k]) && $fk) ? $questions[$k] : '';
			if ($question == '')
    		$fk = false;
			else
			{
				if (isset($cur_ch[$k][0]))
				{
					$db->query('UPDATE '.$db->prefix.'poll SET choice=\''.$db->escape($question).'\', votes='.$type[$k].' WHERE tid='.$tid.' AND question='.$k.' AND field=0') or error('Unable to update poll question', __FILE__, __LINE__, $db->error());
					unset($cur_ch[$k][0]);
					unset($cur_ch[$k][0]);
				}
				else
					$db->query('INSERT INTO '.$db->prefix.'poll (tid, question, field, choice, votes) VALUES ('.$tid.','.$k.',0,\''.$db->escape($question).'\','.$type[$k].')') or error('Unable to create poll question', __FILE__, __LINE__, $db->error());

				$fi = $fk;
				for ($i = 1; $i <= $pun_config['o_poll_max_field'] && $fi; $i++)
				{
					$choice = (isset($choices[$k][$i]) && $fi) ? pun_trim($choices[$k][$i]) : '';
					if ($choice == '')
						$fi = false;
					else
					{
						if (isset($cur_ch[$k][$i]))
						{
							$db->query('UPDATE '.$db->prefix.'poll SET choice=\''.$db->escape($choice).'\' WHERE tid='.$tid.' AND question='.$k.' AND field='.$i) or error('Unable to update poll choice', __FILE__, __LINE__, $db->error());
							unset($cur_ch[$k][$i]);
							unset($cur_ch[$k][$i]);
						}
						else
							$db->query('INSERT INTO '.$db->prefix.'poll (tid, question, field, choice, votes) VALUES ('.$tid.','.$k.','.$i.',\''.$db->escape($choice).'\',0)') or error('Unable to create poll choice', __FILE__, __LINE__, $db->error());

					}
				}
			}
		}
		$db->query('UPDATE '.$db->prefix.'topics SET poll_type=1, poll_time='.$top[1].', poll_term='.$term.', poll_kol='.$top[3].' WHERE id='.$tid) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

		foreach($cur_ch as $k => $ch)
		{
			foreach($ch as $i => $c)
			{
				$db->query('DELETE FROM '.$db->prefix.'poll  WHERE tid='.$tid.' AND question='.$k.' AND field='.$i) or error('Unable to delete poll choice', __FILE__, __LINE__, $db->error());
			}
		}
	}
	else
	{
		if ($top[0] == 0)
			return;
		poll_delete($tid, true);
	}
}

// результат голосования в теме ************************************************
function poll_display_topic($tid, $uid, $p = 0)
{
	global $pun_config;

	if ($pun_config['o_poll_enabled'] != '1') return;

	$top = poll_topic($tid);
	if ($top[0] == 0) return;
		
	$top[4] = $p;
	$info = poll_info($tid, $uid);
	poll_display($tid, $uid, $info, $top);
}

// отображаем результат голосования ********************************************
function poll_display($tid, $uid, $info, $top)
{

	global $db, $lang_poll, $pun_config;

	if (is_null($info)) return;
	
	$can_vote = ($info['canVote'] && $top[0] != 2 && poll_post('poll_view') == null); //  && poll_post('preview') == null
	$can_visi = ((($info['isGuest'] && $pun_config['o_poll_guest'] == '1') || !$info['isGuest']) && $top[2] <= $top[3]);
	$fmess = '';
	if ($top[0] == 2)
		$fmess = $lang_poll['M1'];
	else if ($top[2] > $top[3])
		$fmess = sprintf($lang_poll['M2'], $top[2]);
	else if ($can_visi && $info['isGuest'])
		$fmess = $lang_poll['M3'];
	else if ($info['isGuest'])
		$fmess = $lang_poll['M4'];
	else if (!$can_vote)
		$fmess = $lang_poll['M0'];

	$questions = $info['questions'];
	$choices = $info['choices'];
	$types = $info['type'];
	$votes = $info['votes'];

	if ($can_vote)
	{
?>
<div id="poll_form">
<form method="post" action="viewtopic.php?id=<?php echo $tid.($top[4] > 1 ? '&amp;p='.$top[4] : '') ?>">
<?php
	}
	$amax = array();
	
	foreach($questions as $k => $question)
	{
		$choice = $choices[$k];
		$vote = $votes[$k];
		$amax[$k] = count($choice);

		$max = 0;
		foreach ($vote as $v)
		{
			if ($v > $max) $max = $v;
		}
		$maxPercent = ($top[3] == 0) ? 1 : 100 * $max / $top[3];
?>
<?php if ($can_vote): ?>
	<input type="hidden" name="poll_max[<?php echo $k ?>]" value="<?php echo $amax[$k] ?>" />
<?php endif ?>
	<fieldset class="poll">
		<p><?php echo htmlspecialchars($question) ?></p>
<?php if ($can_vote && $types[$k]>1): ?>
		<div class="poss"><?php printf($lang_poll['Possible choose'], $types[$k]) ?></div>
<?php endif ?>
		<ol>
<?php
		foreach ($choice as $i => $ch)
		{
			if (empty($ch)) continue;

			$percent = ($top[3] == 0) ? 0 : round(100 * $vote[$i] / $top[3],2);
?>
			<li>
<?php
			if ($can_vote)
			{
				if ($types[$k] < 2)
					echo "\t\t\t\t\t".'<label><input type="radio" name="poll_vote['.$k.'][0]" value="'.$i.'" /> '.htmlspecialchars($ch).'</label>';
				else
					echo "\t\t\t\t\t".'<label><input type="checkbox" name="poll_vote['.$k.']['.$i.']" value="1" /> '.htmlspecialchars($ch).'</label>';
			}
			else if ($can_visi)
			{
				echo "\t\t\t\t\t".'<span class="answer">'.htmlspecialchars($ch).'</span><span class="percent">('.$lang_poll['Votes'].$vote[$i].' ['.$percent.'%])</span>';
				echo '<p class="progressbar"><span style="width: '.round(100 * $percent / $maxPercent).'%;"><span>'.$percent.'%</span></span> </p>';
			}
			else
			{
				echo "\t\t\t\t\t".'<span class="answer">'.htmlspecialchars($ch).'</span>';
			}
?>

			</li>
<?php
		}
?>
		</ol>
		<div class="total"><?php printf($lang_poll['Vote total'], $top[3]) ?></div>
	</fieldset>
<?php
	}
	if ($can_vote)
	{
		$csrf = pun_hash($tid.(pun_hash($uid.count($questions).implode('0',$types))).get_remote_address().implode('.',$amax));
		foreach ($types as $i => $type)
		{
?>
	<input type="hidden" name="poll_type[<?php echo $i ?>]" value="<?php echo $type ?>" />
<?php
		}
?>
	<input type="hidden" name="poll_ques" value="<?php echo count($questions) ?>" />
	<input type="hidden" name="poll_csrf" value="<?php echo $csrf ?>" />
	<p class="pollbut"><input type="submit" name="poll_submit" value="<?php echo $lang_poll['Vote button'] ?>" /><?php echo (($can_visi && $top[3] > 0) ? '<input type="submit" name="poll_view" value="'.$lang_poll['View'].'" />' : '') ?></p>
</form>
</div>
<?php
	}
	else if (!empty($fmess))
		echo "\t".'<p class="poll_mess">'.$fmess.'</p>'."\n";
}

// голосуем ********************************************************************
function poll_vote($tid, $uid)
{
	global $db;

	if (poll_bad() || !poll_can_vote($tid, $uid)) return;

	$csrf = poll_post('poll_csrf');
	$ques = poll_post('poll_ques');
	$type = poll_post('poll_type');
	$votes = poll_post('poll_vote');
	$amax = poll_post('poll_max');

	if (is_null($csrf) || is_null($ques) || is_null($type) || is_null($votes) || is_null($amax)) return;

	if (!is_array($type) || !is_array($votes) || !is_array($amax)) return;

	$type = array_map('intval', $type);
	$amax = array_map('intval', $amax);
	$ques = intval($ques);
	
	$csrf2 = pun_hash($tid.(pun_hash($uid.$ques.implode('0',$type))).get_remote_address().implode('.',$amax));
	
	if ($csrf2 != $csrf) return;

	$kol = 0;
	foreach($votes as $k => $vote)
	{
		if ($k < 1 || $k > $ques) return;
		$kol++;
		$kk = 0;
		$vote = array_map('intval', $vote);
		foreach($vote as $i => $vo)
		{
			if ($type[$k] < 2 && $i != 0) return;
			if ($type[$k] < 2 && $vo < 1) return;
			if ($type[$k] < 2 && $vo > $amax[$k]) return;
			if ($type[$k] > 1 && $i == 0) return;
			if ($type[$k] > 1 && $i > $amax[$k]) return;
			if ($type[$k] > 1 && $vo != 1) return;
			$kk++;
		}
		if ($type[$k] < 2 && $kk != 1) return;
		if ($type[$k] > 1 && ($kk < 1 || $kk > $type[$k])) return;
	}
	if ($kol != $ques) return;

	foreach($votes as $k => $vote)
	{
		$vote = array_map('intval', $vote);
		foreach($vote as $i => $vo)
		{
			if ($type[$k] < 2) $j = $vo;
			else $j = $i;
			$db->query('UPDATE '.$db->prefix.'poll SET votes=votes+1 WHERE tid='.$tid.' AND question='.$k.' AND field='.$j) or error('Unable to update poll choice', __FILE__, __LINE__, $db->error());
    }
	}
	$db->query('INSERT INTO '.$db->prefix.'poll_voted (tid, uid, rez) VALUES ('.$tid.','.$uid.',\''.$db->escape(serialize($votes)).'\')') or error('Unable to save vote', __FILE__, __LINE__, $db->error());
	$db->query('UPDATE '.$db->prefix.'topics SET poll_kol=poll_kol+1 WHERE id='.$tid) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
}