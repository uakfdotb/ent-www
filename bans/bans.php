<?php
/*

	ent-www
	Copyright [2012-2013] [Jack Lu]

	This file is part of the ent-www source code.

	ent-www is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	ent-www source code is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with ent-www source code. If not, see <http://www.gnu.org/licenses/>.

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
include("../include/ban.php");
include("../include/csrfguard.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");

	$username_clean = $user->data['username_clean'];

	$search = "";
	$filterbyreason = false;
	$filterbyreasonchecked = "";

	if(isset($_REQUEST['search'])) {
		$search = $_REQUEST['search'];
	}

	if(isset($_REQUEST['filterbyreason']) && $_REQUEST['filterbyreason'] == 'yes') {
		$filterbyreason = true;
		$filterbyreasonchecked = "checked";
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
	Filter: <input type="text" name="search" value="<?= htmlentities($search) ?>" />
	<br /><input type="checkbox" name="filterbyreason" value="yes" <?= $filterbyreasonchecked ?>/> Include matches in the reason field in results
	<br /><input type="submit" value="Filter" />
	</form>

	<table cellpadding="2">
	<tr><th>Username</th><th>Realm</th><th>IP</th><th>Admin</th><th>Gamename</th><th>Date</th><th>Expires on</th><th>Reason</th></tr>

	<?

	if(strlen($search) >= 3) {
		$select = "SELECT * FROM bans";
		$where = "WHERE (context = 'ttr.cloud' OR context = '' OR context IS NULL) AND (name LIKE ? OR ip LIKE ? OR admin LIKE ? OR (LENGTH(ip) > 2 AND SUBSTR(ip, 1, 1) = ':' AND SUBSTR(ip, 2) = SUBSTR(?, 1, LENGTH(ip) - 1)) OR (LENGTH(ip) > 3 AND SUBSTR(ip, 1, 2) = ':h' AND ? LIKE CONCAT('%', SUBSTR(ip, 3), '%')))";
		$orderby = "ORDER BY id";
		$vars = array("%$search%", "%$search%", "%$search%", "$search", "$search");

		if($filterbyreason) {
			$where .= " OR reason LIKE ?";
			$vars[] = "%$search%";
		}

		$result = databaseQuery($select . " " . $where . " " . $orderby, $vars, true);

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
