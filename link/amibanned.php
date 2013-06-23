<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup();

if ($user->data['user_id'] == ANONYMOUS) {
    header('Location: https://entgaming.net/forum/ucp.php?mode=login');
} else {
	include("../include/common.php");
	include("../include/link.php");
	include("../include/dbconnect.php");
	
	$fuser = $user->data['username_clean'];
	
	//timezone stuff
	date_default_timezone_set(AUTOMATIC_DST_TIMEZONE);
	$timezoneAbbr = timezoneAbbr(AUTOMATIC_DST_TIMEZONE);
	
	$result = databaseQuery("SELECT name, server, gamename, admin, reason, expiredate, id FROM bans WHERE ip = ? AND context = 'ttr.cloud'", array($_SERVER['REMOTE_ADDR']));
	$checkedBans = array();
	
	$template->assign_var('TIMEZONE', $timezoneAbbr);
	$template->assign_var('TIME', uxtDate());
	
	while($row = $result->fetch()) {
		if(!in_array($row[6], $checkedBans)) {
			$checkedBans[] = $row[6];
			$template->assign_block_vars('bans', array(
										'BAN_NAME' => $row[0],
										'BAN_SERVER' => $row[1],
										'BAN_GAMENAME' => $row[2],
										'BAN_ADMIN' => $row[3],
										'BAN_REASON' => $row[4],
										'BAN_EXPIREDATE' => uxtDate(convertTime($row[5])),
										));
		}
	}
	
	$array = listValidated($fuser);
	
	foreach($array as $account) {
	    $template->assign_block_vars('accounts', array(
									'ACCOUNT_NAME' => $account[0],
									'ACCOUNT_REALM' => $account[1],
									));
		$result = databaseQuery("SELECT name, server, gamename, admin, reason, expiredate, id FROM bans WHERE name = ? AND server = ? AND context = 'ttr.cloud'", array($account[0], $account[1]));
		
		while($row = $result->fetch()) {
			if(!in_array($row[6], $checkedBans)) {
				$checkedBans[] = $row[6];
				$template->assign_block_vars('bans', array(
											'BAN_NAME' => $row[0],
											'BAN_SERVER' => $row[1],
											'BAN_GAMENAME' => $row[2],
											'BAN_ADMIN' => $row[3],
											'BAN_REASON' => $row[4],
											'BAN_EXPIREDATE' => uxtDate(convertTime($row[5])),
											));
			}
		}
	}
	
	page_header('EntLink: am I banned?');
	$template->set_filenames(array('body' => 'link_amibanned.html'));
	page_footer();
}

?>
