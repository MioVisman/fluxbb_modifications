<?php
/**
 * Copyright (C) 2011-2012 Visman (visman@inbox.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if (!defined('PUN'))
	exit;

$db->query('DELETE FROM '.$db->prefix.'reglog WHERE user_id='.$id) or error('Unable to delete user from reglog', __FILE__, __LINE__, $db->error());
