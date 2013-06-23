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
	<head><title>ENT Ban - Together Search</title></head>
	<body>
	<h1>Search for frequently played with</h1>

	<p>This tool looks for either games where user A has played with user B (in the case that both fields are filled out) or users that user A has most frequently played with (in the case that user B is left blank). It may be useful in finding ban dodgers, by searching for their friends.</p>

	<p>Your timezone is <?= $timezoneAbbr ?>. The current time is <?= uxtDate() ?>.</p>

	<form method="GET">
	User A: <input type="text" name="user_a" />
	<br />User B: <input type="text" name="user_b" />
	<br /><input type="submit" value="Search" />
	</form>

	<?

	if(!empty($_REQUEST['user_a']) && !empty($_REQUEST['user_b'])) {
		$result = databaseQuery("SELECT id, gamename, datetime FROM games JOIN (SELECT a.gameid as id FROM gameplayers INNER JOIN (SELECT gameid FROM gameplayers WHERE NAME = ?) a USING (gameid) WHERE gameplayers.NAME = ?) game USING (id) ORDER BY datetime DESC LIMIT 50", array($_REQUEST['user_a'], $_REQUEST['user_b']));

		echo "<ul>";

		while($row = $result->fetch()) {
			$id = htmlspecialchars($row[0]);
			$gamename = htmlspecialchars($row[1]);
			$datetime = uxtDate(convertTime($row[2]));
			echo "<li><a href=\"game.php?id=$id\">$gamename ($datetime)</a></li>";
		}

		echo "</ul>";
	} else if(!empty($_REQUEST['user_a'])) {
		$result = databaseQuery("SELECT name, spoofedrealm, MAX(gameid), COUNT(*) as c FROM gameplayers INNER JOIN (SELECT gameid FROM gameplayers WHERE NAME = ?) a USING (gameid) WHERE name <> 'uakf.b' GROUP BY name HAVING COUNT(*) > 1 ORDER BY COUNT(*) DESC LIMIT 50", array($_REQUEST['user_a']));

		echo "<table><tr><th>Username</th><th>Realm</th><th>Last played together</th><th>Count</th></tr>";

		while($row = $result->fetch()) {
			$name = htmlspecialchars($row[0]);
			$realm = htmlspecialchars($row[1]);
			$gameid = htmlspecialchars($row[2]);
			$count = htmlspecialchars($row[3]);
			$datetime = "Unknown";

			$result2 = databaseQuery("SELECT datetime FROM games WHERE id = ?", array($gameid));
			if($row2 = $result2->fetch()) {
				$datetime = uxtDate(convertTime($row2[0]));
			}

			echo "<tr>";
			echo "<td><a href=\"search.php?username=$name&realm=$realm\">$name</a></td>";
			echo "<td>$realm</td>";
			echo "<td><a href=\"game.php?id=$gameid\">$datetime</a></td>";
			echo "<td>$count</td>";
			echo "</tr>";
		}

		echo "</table>";
	}

	?>

	<p><a href="./">back to index</a></p>
	</body>
	</html>

	<?
}
?>
