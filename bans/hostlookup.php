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
include("../include/iplookup.php");
include("../include/csrfguard.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");

	$username_clean = $user->data['username_clean'];

	$where = "";
	$parameters = array();

	if(isset($_REQUEST['filter']) && isset($_REQUEST['filter_type'])) {
		$parameters[] = $_REQUEST['filter'];

		if($_REQUEST['filter_type'] == "host") {
			$where = "hostname LIKE ?";
		} else if($_REQUEST['filter_type'] == "name") {
			$where = "name = ?";
		}
	}

	//timezone stuff
	date_default_timezone_set(AUTOMATIC_DST_TIMEZONE);
	$timezoneAbbr = timezoneAbbr(AUTOMATIC_DST_TIMEZONE);
	?>

	<html>
	<head><title>ENT Ban - Host Lookup</title></head>
	<body>
	<h1>Host Lookup</h1>

	<p>For hostname, you can use % for wildcard.</p>

	<form method="get">
	Filter: <input type="text" name="filter" />
	<select name="filter_type">
		<option value="host">Hostname</option>
		<option value="name">Username</option>
	</select>
	<input type="submit" value="Filter" />
	</form>
	<?

	if(!empty($where)) {
		?>
		<table cellpadding="2">
		<tr><th>Username</th><th>Realm</th><th>IP</th><th>Hostname</th><th>Last seen</th><th>Bans</th><th>Games</th><th>Banned?</th></tr>

		<?

		$result = databaseQuery("SELECT DISTINCT name, spoofedrealm, ip, hostname FROM gameplayers, games WHERE gameplayers.gameid = games.id AND games.datetime > DATE_SUB(NOW(), INTERVAL 30 DAY) AND $where ORDER BY gameplayers.id DESC LIMIT 50", $parameters);

		while($row = $result->fetch()) {
			echo "<tr>";
			echo "<td><a href=\"search.php?username=" . htmlspecialchars(urlencode($row[0])) . "&realm=" . htmlspecialchars(urlencode($row[1])) . "\">" . htmlspecialchars($row[0]) . "</a></td>";
			echo "<td>" . htmlspecialchars($row[1]) . "</td>";
			echo "<td>" . htmlspecialchars($row[2]) . "</td>";
			echo "<td>" . htmlspecialchars($row[3]) . "</td>";
			echo "<td>" . lastTimePlayed($row[0]) . "</td>";
			echo "<td>" . countBans($row[0], $row[1]) . "</td>";
			echo "<td>" . countGames($row[0], $row[1]) . "</td>";
			echo "<td>" . isBanned($row[0], $row[1]) . "</td>";
			echo "</tr>";
		}

		?>

		</table>
		<?
		}
	?>

	<p><a href="./">back to index</a></p>
	</body>
	</html>

	<?
}
?>
