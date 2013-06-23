<?php

/*
This tool is intended to penalize players who disconnect from LIHL games.
For more information, see the following topics:
	https://entgaming.net/forum/viewtopic.php?f=79&t=8990
	https://entgaming.net/forum/viewtopic.php?f=72&t=8848
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_user.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

include("../include/common.php");
include("../include/dbconnect.php");
include("../include/admin.php");
include("../include/csrfguard.php");

?>


<html>
<head><title>Stupid tool</title></head>
<body>
<h1>Stupid tool</h1>

<?

$isadmin = group_memberships(22, $user->data['user_id']) != false;
$username_clean = $user->data['username_clean'];

if($isadmin && isset($_POST['gid'])) {
	$gid = $_POST['gid'];
	$reverse = isset($_POST['reverse']);
	
	$result = databaseQuery("SELECT IFNULL(MIN(`left`), -1), COUNT(*) FROM gameplayers LEFT JOIN games ON games.id = gameplayers.gameid WHERE gameid = ? AND gamename LIKE 'lihl%' AND colour <= 7", array($gid));
	$row = $result->fetch();
	
	if($row && $row[0] >= 0 && $row[1] > 0 && ($row[1] == 4 || $row[1] == 6 || $row[1] == 8)) {
		$minleft = $row[0];
		$count = $row[1];
		
		$result = databaseQuery("SELECT name, spoofedrealm, `left` FROM gameplayers LEFT JOIN games ON games.id = gameplayers.gameid WHERE gameid = ? AND gamename LIKE 'lihl%' AND colour <= 7", array($gid));
		
		while($row = $result->fetch()) {
			$name = $row[0];
			$realm = $row[1];
			$left = $row[2];
			
			$change = "+0";
			
			if($left == $minleft) {
				if($count == 4) $change = "-18";
				if($count == 6) $change = "-20";
				if($count == 8) $change = "-21";
			} else {
				if($count == 4) $change = "+6";
				if($count == 6) $change = "+4";
				if($count == 8) $change = "+3";
			}
			
			if($reverse) {
				$sign = substr($change, 0, 1);
				$value = substr($change, 1);
				
				if($sign == "-") $change = "+" . $value;
				else $change = "-" . $value;
			}
			
			$message = "Adjusting stats for [" . htmlspecialchars($name) . "] by $change (gid=$gid)";
			adminLog("lihl-dcpenalty", $message, $username_clean);
			echo "<b><i>" . $message . "</i></b><br />";
			
			databaseQuery("UPDATE w3mmd_elo_scores SET score = score $change WHERE name = ? AND server = ? AND category = 'lihl' LIMIT 1", array($name, $realm));
		}
	}
}

?>

<p>If you were looking for more information on LIHL, you are really in the wrong place. Go away.</p>

<? if($isadmin) { ?>
	<form method="POST">
	Game ID: <input type="text" name="gid" />
	<br /><input type="checkbox" name="reverse" /> Check to apply in reverse
	<br /><input type="submit" value="Apply disconnection penalties" />
	</form>
<? } ?>
