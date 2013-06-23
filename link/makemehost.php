<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup();

if ($user->data['user_id'] == ANONYMOUS) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/common.php");
	include("../include/link.php");
	include("../include/dbconnect.php");
	
	$user_id = $user->data['user_id'];
	$fuser = $user->data['username_clean'];
	
	$key = uid(32);
	
	$result = databaseQuery("SELECT COUNT(*) FROM makemehost_session WHERE user_id = ?", array($user_id));
	$row = $result->fetch();
	
	if($row[0] == 0) {
		databaseQuery("INSERT INTO makemehost_session (user_id, sessionkey, lasttime) VALUES (?, ?, 0)", array($user_id, $key));
	} else {
		databaseQuery("UPDATE makemehost_session SET sessionkey = ? WHERE user_id = ?", array($key, $user_id));
	}

	header("Location: http://s4.entgaming.net/makemehost/auth.php?user_id=$user_id&key=$key");
}

?>
