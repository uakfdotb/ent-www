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
	
	//timezone stuff
	date_default_timezone_set(AUTOMATIC_DST_TIMEZONE);
	$timezoneAbbr = timezoneAbbr(AUTOMATIC_DST_TIMEZONE);
	?>
	
	<html>
	<head><title>ENT Ban - Game information</title></head>
	<body>
	<h1>Game information</h1>
	
	<p>Your timezone is <?= $timezoneAbbr ?>. The current time is <?= uxtDate() ?>.</p>
	
	<?
	if(isset($_REQUEST['id'])) {
		$id = htmlspecialchars($_REQUEST['id']);
		$result = databaseQuery("SELECT gamename, datetime, map, duration, ownername FROM games WHERE id = ?", array($id));
		
		if($row = $result->fetch()) {
			echo "<table>";
			echo "<tr>";
			echo "<td><b>Game name</b></td>";
			echo "<td>" . htmlspecialchars($row[0]) . "</td>";
			echo "</tr><tr>";
			echo "<tr>";
			echo "<td><b>Date</b></td>";
			echo "<td>" . uxtDate(convertTime($row[1])) . "</td>";
			echo "</tr><tr>";
			echo "<tr>";
			echo "<td><b>Map</b></td>";
			echo "<td>" . htmlspecialchars($row[2]) . "</td>";
			echo "</tr><tr>";
			echo "<tr>";
			echo "<td><b>Duration</b></td>";
			echo "<td>" . round($row[3] / 60, 2) . "</td>";
			echo "</tr><tr>";
			echo "<tr>";
			echo "<td><b>Owner</b></td>";
			echo "<td>" . htmlspecialchars($row[4]) . "</td>";
			echo "</tr><tr>";
			echo "<tr>";
			echo "<td><b>Replay</b></td>";
			echo "<td><a href=\"/replay.php?id=$id\">($id)</a></td>";
			echo "</tr><tr>";
			echo "<tr>";
			echo "<td><b>Lobby chat</b></td>";
			echo "<td><a href=\"/replay.php?id=$id&chat\">($id)</a></td>";
			echo "</tr>";
			
			echo "<table cellpadding=\"5\">";
			echo "<tr>";
			echo "<th>Player name</th>";
			echo "<th>IP</th>";
			echo "<th>Realm</th>";
			echo "<th>Left</th>";
			echo "<th>Reason</th>";
			echo "</tr>";
			
			$result = databaseQuery("SELECT name, ip, spoofedrealm, `left`, leftreason, hostname FROM gameplayers WHERE gameid = ? ORDER BY colour", array($id));
			
			while($row = $result->fetch()) {
				echo "<tr>";
				echo "<td><a href=\"search.php?username=" . htmlspecialchars(urlencode($row[0])) . "&realm=" . htmlspecialchars(urlencode($row[2])) . "\">" . htmlspecialchars($row[0]) . "</a></td>";
				echo "<td>" . htmlspecialchars($row[1]) . "<br />" . niceHostname($row[5]) . "</td>";
				echo "<td>" . htmlspecialchars($row[2]) . "</td>";
				echo "<td>" . round($row[3] / 60, 2) . "</td>";
				echo "<td>" . htmlspecialchars($row[4]) . "</td>";
				echo "</tr>";
			}
			
			echo "</table>";
		}
	} else {
		echo "<p><b><i>Error: no game specified</i></b></p>";
	}
	?>
	
	<p><a href="./">back to index</a></p>
	</body>
	</html>
	
	<?
}
?>
