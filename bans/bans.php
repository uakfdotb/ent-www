<?php
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
include("../include/ban.php");
include("../include/csrfguard.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");
	
	$username_clean = $user->data['username_clean'];
	
	$search = "";
	
	if(isset($_REQUEST['search'])) {
		$search = $_REQUEST['search'];
	}
	
	//timezone stuff
	date_default_timezone_set(AUTOMATIC_DST_TIMEZONE);
	$timezoneAbbr = timezoneAbbr(AUTOMATIC_DST_TIMEZONE);
	?>
	
	<html>
	<head><title>ENT Ban - Ban List</title></head>
	<body>
	<h1>Ban List</h1>
	
	<p>Your timezone is <?= $timezoneAbbr ?>. The current time is <?= uxtDate() ?>.</p>
	
	<? if(strlen($search) < 3 && !empty($search)) { ?>
	<p><b><i>Search filter too short (three characters minimum).</i></b></p>
	<? } ?>
	
	<form method="get" action="bans.php">
	Filter: <input type="text" name="search" />
	<input type="submit" value="Filter" />
	</form>
	
	<table cellpadding="2">
	<tr><th>Username</th><th>Realm</th><th>IP</th><th>Admin</th><th>Gamename</th><th>Date</th><th>Expires on</th><th>Reason</th></tr>
	
	<?
	
	if(strlen($search) >= 3) {
		$result = databaseQuery("SELECT * FROM bans WHERE (context = 'ttr.cloud' OR context = '' OR context IS NULL) AND (name LIKE ? OR ip LIKE ? OR admin LIKE ?) ORDER BY id", array("%$search%", "%$search%", "%$search%"), true);
	
		while($row = $result->fetch()) {
			echo "<tr>";
			echo "<td><a href=\"search.php?username=" . urlencode($row['name']) . "&realm=" . $row['server'] . "\">" . $row['name'] . "</a></td>";
		
			$server = $row['server'];
		
			if($server == "useast.battle.net") $server = "USEast";
			else if($server == "uswest.battle.net") $server = "USWest";
			else if($server == "asia.battle.net") $server = "Asia";
			else if($server == "europe.battle.net") $server = "Europe";
			else if($server == "cloud.ghostclient.com") $server = "Cloud";
		
			echo "<td>" . $server . "</td>";
			echo "<td>" . htmlspecialchars($row['ip']) . "<br />" . niceHostname($row['hostname']) . "</td>";
			echo "<td>" . htmlspecialchars($row['admin']) . "</td>";
		
			//link to games search, but gamename might be blank in which case don't
			if($row['gamename'] != "") {
				echo "<td><a href=\"games.php?gamename=" . urlencode($row['gamename']) . "\">" . $row['gamename'] . "</a></td>";
			} else {
				echo "<td></td>";
			}
		
			//only show the date part for dates
			$date = dayDate(convertTime($row['date']));
			$expiredate = dayDate(convertTime($row['expiredate']));
		
			//process reason to change links to links and tid's to links as well
			$reason = niceReason($row['reason']);
		
			echo "<td>" . $date . "</td>";
			echo "<td>" . $expiredate . "</td>";
			echo "<td>" . $reason . "</td>";
			echo "</tr>";
		}
	}
	
	?>
	
	</table>
	<p><a href="./">back to index</a></p>
	</body>
	</html>
	
	<?
}
?>
