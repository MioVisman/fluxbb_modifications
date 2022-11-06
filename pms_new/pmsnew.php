<?php

/**
 * Copyright (C) 2008-2010 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * Copyright (C) 2010 Visman (visman@inbox.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_PMS_NEW', 1);

define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/pms_new/common_pmsn.php';

if ($pun_user['g_read_board'] == '0')
	message($lang_common['No view']);

// гостям нельзя
if ($pun_user['is_guest'])
	redirect('login.php', $lang_common['Redirecting']);

// если выключена совсем или выключена группа и нет новых сообщений
if ($pun_config['o_pms_enabled'] != '1' || ($pun_user['g_pm'] != 1 && $pun_user['messages_new'] == 0))
	message($lang_common['No permission']);

// если была отправка формы
if (isset($_POST['csrf_hash']) || isset($_GET['csrf_hash']))
{
	confirm_referrer('pmsnew.php');
	define('PUN_PMS_NEW_CONFIRM', 1);
}

$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
if ($action == 'onoff')
{
	if ($pun_user['messages_enable'] == 0 || ($pun_user['messages_enable'] == 1 && isset($_POST['action2']) && defined('PUN_PMS_NEW_CONFIRM')))
	{
		// удаляем сообщения пользователя
		if ($pun_user['messages_enable'] == 1)
			pmsn_user_delete($pun_user['id'], 2);
			
		$pun_user['messages_enable'] = ($pun_user['messages_enable'] == 0) ? 1 : 0;
		$db->query('UPDATE '.$db->prefix.'users SET messages_enable='.$pun_user['messages_enable'].' WHERE id='.$pun_user['id']) or error('Unable to update users table', __FILE__, __LINE__, $db->error());

		redirect('pmsnew.php', $lang_pmsn['Options redirect']);
	}
	else if ($pun_user['messages_enable'] == 1 && isset($_POST['action2']))
		message($lang_common['Bad referrer']);
	else
		$pmsn_modul = 'closeq';
}
else if ($action == 'email')
{
	if ($pun_user['messages_email'] == 1)
	{
		$action = $lang_pmsn['Email off Red'];
		$db->query('UPDATE '.$db->prefix.'users SET messages_email=0 WHERE id='.$pun_user['id']) or error('Unable to update users table', __FILE__, __LINE__, $db->error());
	}
	else
	{
		$action = $lang_pmsn['Email on Red'];
		$db->query('UPDATE '.$db->prefix.'users SET messages_email=1 WHERE id='.$pun_user['id']) or error('Unable to update users table', __FILE__, __LINE__, $db->error());
	}

	redirect('pmsnew.php', $action);
}
else if ($pun_user['messages_enable'] == 0 && $pun_user['messages_new'] == 0) // вдруг сообщение от админа придет
	$pmsn_modul = 'close';
else if ($pun_user['g_pm'] != 1 && $pun_user['messages_new'] > 0) // вдруг сообщение от админа придет
	$pmsn_modul = 'new';
else
{
	$pmsn_modul = isset($_REQUEST['mdl']) ? trim($_REQUEST['mdl']) : 'new';
	if ($pmsn_modul == 'new' && $pun_user['messages_new'] == 0)
		$pmsn_modul = 'list';
}

// проверка модуля
if (!preg_match('/^[a-z]+$/', $pmsn_modul))
	message($lang_common['Bad request']);

if (!file_exists(PUN_ROOT.'include/pms_new/mdl/'.$pmsn_modul.'.php'))
	message(sprintf($lang_pmsn['No modul message'], $pmsn_modul));

if (function_exists('csrf_hash'))
	$pmsn_csrf_hash = csrf_hash();
else
	$pmsn_csrf_hash = '1';

// запросы по папкам
$pmsn_arr_list = array();
$pmsn_arr_new = array();
$pmsn_arr_save = array();
$result = $db->query('SELECT id, starter_id, topic_st, topic_to  FROM '.$db->prefix.'pms_new_topics WHERE (starter_id = '.$pun_user['id'].' AND topic_st != 2) OR (to_id = '.$pun_user['id'].' AND topic_to != 2) ORDER BY last_posted DESC') or error('Unable to fetch pms topics IDs', __FILE__, __LINE__, $db->error());

while ($ttmp = $db->fetch_assoc($result))
{
	if ($ttmp['starter_id'] == $pun_user['id'])
		$ftmp = $ttmp['topic_st'];
	else
		$ftmp = $ttmp['topic_to'];

	if ($ftmp == 0)
		$pmsn_arr_list[] = $ttmp['id'];
	else if ($ftmp == 3)
		$pmsn_arr_save[] = $ttmp['id'];
	else if ($ftmp == 1)
	{
		$pmsn_arr_new[] = $ttmp['id'];
		$pmsn_arr_list[] = $ttmp['id'];
	}
}

$pmsn_kol_list = count($pmsn_arr_list);
$pmsn_kol_new = count($pmsn_arr_new);
$pmsn_kol_save = count($pmsn_arr_save);

// можно ли создать новый диалог
if ($pun_user['g_pm'] != 1 || $pun_user['messages_enable'] == 0 || ($pun_user['g_pm_limit'] != 0 && $pmsn_kol_list >= $pun_user['g_pm_limit'] && $pmsn_kol_save >= $pun_user['g_pm_limit']))
  $pmsn_f_cnt = '';
else
  $pmsn_f_cnt = '<span><a href="pmsnew.php?mdl=post">'.$lang_pmsn['New dialog'].'</a></span>';

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_pmsn['PM'], $lang_pmsn[$pmsn_modul]);
define('PUN_ACTIVE_PAGE', 'pms_new');
require PUN_ROOT.'header.php';

include PUN_ROOT.'include/pms_new/mdl/'.$pmsn_modul.'.php';

if (!defined('PUN_PMS_LOADED'))
	message(sprintf($lang_pmsn['Modul failed message'], $pmsn_modul));

// Output the clearer div
?>
	<div class="clearer"></div>
</div>
<?php

require PUN_ROOT.'footer.php';
