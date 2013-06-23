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
	<head><title>ENT Ban - Games</title></head>
	<body>
	<h1>Games</h1>
	
	<p>The most recent games are shown below. To search a specific game, use the form.</p>
	
	<p>Your timezone is <?= $timezoneAbbr ?>. The current time is <?= uxtDate() ?>.</p>
	
	<form method="GET" action="games.php">
	Gamename: <input type="text" name="gamename" />
	<br />Username: <input type="text" name="username" /> leave blank to ignore usernames
	<br />Realm: <input type="text" name="realm" value="*" /> only applicable if username is non-blank
	<br /><input type="submit" value="Search" />
	</form>
	
	<table cellpadding="5">
	
	<?
	$start = 0;
	$step = 100;
	
	if(isset($_REQUEST['start'])) {
		$start = intval($_REQUEST['start']);
	}
	
	$previous = 0;
	if($start >= $step) $previous = $start - $step;
	$next = $start + $step;
	
	//set extra get parameters to make it easier
	$extra_get = "";
	if(isset($_REQUEST['gamename'])) $extra_get .= "&gamename=" . $_REQUEST['gamename'];
	if(isset($_REQUEST['username'])) $extra_get .= "&username=" . $_REQUEST['username'];
	if(isset($_REQUEST['realm'])) $extra_get .= "&realm=" . $_REQUEST['realm'];
	?>
	
	<tr>
		<td><a href="games.php?start=<?= $previous . $extra_get ?>">&lt;</a></td>
		<td colspan="3" style="text-align:center;">Displaying results <?= $start ?> to <?= $next ?></td>
		<td><a href="games.php?start=<?= $next . $extra_get ?>">&gt;</a></td>
	</tr>
	<tr>
		<th>Game name</th>
		<th>Date</th>
		<th>Map</th>
		<th>Duration (min)</th>
		<th>Owner</th>
	</tr>
	
	<?
	$query = "SELECT id, gamename, datetime, map, duration, ownername FROM games";
	$where = "";
	$parameters = array();
	
	//search gameplayers instead if we're dealing with username
	if(isset($_REQUEST['username']) && $_REQUEST['username'] != '' && isset($_REQUEST['realm'])) {
		$query = "SELECT games.id, games.gamename, games.datetime, games.map, games.duration, games.ownername FROM gameplayers LEFT JOIN games ON games.id = gameplayers.gameid";
		
		$where_add = " gameplayers.name = ?";
		$parameters[] = $_REQUEST['username'];
		
		if($_REQUEST['realm'] != "*") {
			$where_add .= " AND gameplayers.spoofedrealm = ?";
			$parameters[] = $_REQUEST['realm'];
		}
		
		if($where == "") $where = " WHERE" . $where_add;
		else $where .= " AND" . $where_add;
	}
	
	//add gamename where clause (works with username)
	if(isset($_REQUEST['gamename'])) {
		//we have to escape gamename for the MySQL LIKE construct; we use = here to escape
		$gamename = likeEscape($_REQUEST['gamename'], '=');
		
		$where_add = " games.gamename LIKE ? ESCAPE '='";
		$parameters[] = "%$gamename%";
		
		if($where == "") $where = " WHERE" . $where_add;
		else $where .= " AND" . $where_add;
	}
	
	$query .= $where;
	//in all cases limit results to $step and show most recent first
	$query .= " ORDER BY games.id DESC LIMIT $start, $step";
	
	$result = databaseQuery($query, $parameters);
	
	while($row = $result->fetch()) {
		echo "<tr>";
		echo "<td><a href=\"game.php?id=" . urlencode($row[0]) . "\">" . htmlspecialchars($row[1]) . "</a></td>";
		echo "<td>" . uxtDate(convertTime($row[2])) . "</td>";
		echo "<td>" . htmlspecialchars($row[3]) . "</td>";
		echo "<td>" . round($row[4] / 60, 2) . "</td>";
		
		if($row[5] != "") {
			echo "<td><a href=\"search.php?username=" . htmlspecialchars(urlencode($row[5])) . "&realm=\">" . htmlspecialchars($row[5]) . "</a></td>";
		} else {
			echo "<td></td>";
		}
		
		echo "</tr>";
	}
	?>
	
	<tr>
		<td><a href="games.php?start=<?= $previous . $extra_get ?>">&lt;</a></td>
		<td colspan="3"></td>
		<td><a href="games.php?start=<?= $next . $extra_get ?>">&gt;</a></td>
	</tr>
	</table>
	<p><a href="./">back to index</a></p>
	</body>
	</html>
	
	<?
}
?>
