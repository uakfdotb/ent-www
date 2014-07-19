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
include("../include/iplookup.php");
include("../include/csrfguard.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");

	$username_clean = $user->data['username_clean'];

	$where = "";
	$parameters = array();
	$ignorebannedchecked = "";

	if(isset($_REQUEST['filter']) && isset($_REQUEST['filter_type'])) {
		$req_string .= '';
		$parameters[] = $_REQUEST['filter'];

		if($_REQUEST['filter_type'] == "host") {
			$where = "gameplayers.hostname LIKE ?";
		} else if($_REQUEST['filter_type'] == "name") {
			$where = "gameplayers.name = ?";
		}

		if(!empty($_REQUEST['gamename'])) {
			$where .= " AND games.gamename LIKE ?";
			$parameters[] = "%" . $_REQUEST['gamename'] . "%";
		}

		if(!empty($_REQUEST['maxdays'])) {
			$interval = intval($_REQUEST['maxdays']);
			$where .= " AND gametrack.time_created > DATE_SUB(NOW(), INTERVAL $interval DAY)";
			$req_maxdays = $_REQUEST['maxdays'];
		}

		if(isset($_REQUEST['ignorebanned']) && $_REQUEST['ignorebanned'] == 'yes') {
			$where .= " AND (SELECT COUNT(*) FROM bans WHERE bans.name = gameplayers.name AND bans.server = gameplayers.spoofedrealm) = 0";
			$ignorebannedchecked = "checked";
			$req_ignorebanned = 'yes';
		}
	}

	$sort_req_data = create_form_target(array('sort'));

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
		<option value="host" <? if($_REQUEST['filter_type'] == "host") {echo "selected";} ?>>Filter by hostname</option>
		<option value="name" <? if($_REQUEST['filter_type'] == "name") {echo "selected";} ?>>Filter by username</option>
	</select>
	<br />Gamename: <input type="text" name="gamename" value="<?= htmlentities($_REQUEST['gamename']) ?>" /> leave this blank unless you're stupid
	<br />Account first seen in last ? days: <input type="text" name="maxdays" value="<?= htmlentities($_REQUEST['maxgames']) ?>" /> leave this blank unless you're stupid
	<br /><input type="checkbox" name="ignorebanned" value="yes" <?= $ignorebannedcheckde ?> /> Ignore banned players
	<br /><input type="submit" value="Filter" />
	</form>
	<?

	if(!empty($where)) {
		?>
		<table cellpadding="2">
		<tr>
			<th><a href="hostlookup.php?sort=name&<?= $sort_req_data['link_string'] ?>">Username</a></th>
			<th><a href="hostlookup.php?sort=realm&<?= $sort_req_data['link_string'] ?>">Realm</a></th>
			<th><a href="hostlookup.php?sort=ip&<?= $sort_req_data['link_string'] ?>">IP</a></th>
			<th><a href="hostlookup.php?sort=hostname&<?= $sort_req_data['link_string'] ?>">Hostname</a></th>
			<th><a href="hostlookup.php?sort=last_time_sort&<?= $sort_req_data['link_string'] ?>">Last seen</a></th>
			<th><a href="hostlookup.php?sort=count_ban&<?= $sort_req_data['link_string'] ?>">Bans</a></th>
			<th><a href="hostlookup.php?sort=count_game&<?= $sort_req_data['link_string'] ?>">Games</a></th>
			<th><a href="hostlookup.php?sort=isbanned&<?= $sort_req_data['link_string'] ?>">Banned?</a></th>
		</tr>

		<?

		$result = databaseQuery("SELECT DISTINCT gameplayers.name, gameplayers.spoofedrealm, gameplayers.ip, gameplayers.hostname FROM gameplayers, games, gametrack WHERE gameplayers.gameid = games.id AND games.datetime > DATE_SUB(NOW(), INTERVAL 30 DAY) AND gametrack.name = gameplayers.name AND gametrack.realm = gameplayers.spoofedrealm AND $where ORDER BY gameplayers.id DESC LIMIT 50", $parameters);
		$players = array();

		while($row = $result->fetch()) {
			$players[] = array('name' => $row[0], 'realm' => $row[1], 'ip' => $row[2], 'hostname' => $row[3], 'last_time' => lastTimePlayed($row[0]), 'last_time_sort' => strtotime(lastTimePlayed($row[0])), 'count_ban' => countBans($row[0], $row[1]), 'count_game' => countGames($row[0], $row[1]), 'isbanned' => isBanned($row[0], $row[1]));
		}

		$sort_key = '';

		if(isset($_GET['sort']) && ($_GET['sort'] == 'name' || $_GET['sort'] == 'realm' || $_GET['sort'] == 'ip' || $_GET['sort'] == 'hostname' || $_GET['sort'] == 'last_time_sort' || $_GET['sort'] == 'count_ban' || $_GET['sort'] == 'count_game' || $_GET['sort'] == 'isbanned')) {
			$sort_key = $_GET['sort'];
		}

		if($sort_key) {
			$sort_function = function($a, $b) use ($sort_key) {
				if($sort_key == 'name' || $sort_key == 'realm' || $sort_key == 'isbanned') {
					return strcmp($a[$sort_key], $b[$sort_key]);
				} else {
					return $b[$sort_key] - $a[$sort_key];
				}
			};

			uasort($players, $sort_function);
		}

		foreach($players as $p_data) {
			echo "<tr>";
			echo "<td><a href=\"search.php?username=" . htmlspecialchars(urlencode($p_data['name'])) . "&realm=" . htmlspecialchars(urlencode($p_data['realm'])) . "\">" . htmlspecialchars($p_data['name']) . "</a></td>";
			echo "<td>" . htmlspecialchars($p_data['realm']) . "</td>";
			echo "<td>" . htmlspecialchars($p_data['ip']) . "</td>";
			echo "<td>" . htmlspecialchars($p_data['hostname']) . "</td>";
			echo "<td>" . $p_data['last_time'] . "</td>";
			echo "<td>" . $p_data['count_ban'] . "</td>";
			echo "<td>" . $p_data['count_game'] . "</td>";
			echo "<td>" . $p_data['isbanned'] . "</td>";
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
