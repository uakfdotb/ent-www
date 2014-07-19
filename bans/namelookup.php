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
include("../include/iplookup.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");

	$ip = $_SERVER['REMOTE_ADDR'];
	$gamename = "";

	if(isset($_REQUEST['ip'])) {
		$ip = $_REQUEST['ip'];
	}

	if(isset($_REQUEST['gamename'])) {
		$gamename = $_REQUEST['gamename'];
	}
	?>

	<html>
	<body>

	<p>Enter an IP address here, and I will look up the name. You can also enter a partial IP address, but make sure you have a leading dot. For example, "8.8.8.". But do not do "8.8.8", because you need leading dot or it won't do partial search.</p>

	<form method="get" action="namelookup.php">
	IP: <input type="text" name="ip" value="<?= htmlentities($ip) ?>">
	<br />Gamename: <input type="text" name="gamename" value="<?= htmlentities($gamename) ?>" /> leave this blank unless you're stupid
	<br /><input type="submit" value="Search">
	</form>

	<table>
	<tr>
		<th><a href="namelookup.php?sort=name&ip=<?= $ip ?>">Name</a></th>
		<th><a href="namelookup.php?sort=realm&ip=<?= $ip ?>">Realm</a></th>
		<th><a href="namelookup.php?sort=last_time_sort&ip=<?= $ip ?>">Last seen</a></th>
		<th><a href="namelookup.php?sort=count_ban&ip=<?= $ip ?>">Count bans</a></th>
		<th><a href="namelookup.php?sort=count_game&ip=<?= $ip ?>">Count games</a></th>
		<th><a href="namelookup.php?sort=isbanned&ip=<?= $ip ?>">Is banned?</a></th>
	</tr>

	<?php

	$result = namelookup($ip, $gamename);
	$players = array();

	//construct map from player info string to last time played
	while($row = $result->fetch()) {
		$players[] = array(
			'name' => $row[0],
			'realm' => $row[1],
			'last_time_sort' => strtotime(lastTimePlayed($row[0])),
			'last_time' => lastTimePlayed($row[0]),
			'count_ban' => countBans($row[0], $row[1]),
			'count_game' => countGames($row[0], $row[1]),
			'isbanned' => isBanned($row[0], $row[1])
			);
	}

	$sort_key = 'last_time_sort';

	if(isset($_GET['sort']) && ($_GET['sort'] == 'name' || $_GET['sort'] == 'realm' || $_GET['sort'] == 'last_time_sort' || $_GET['sort'] == 'count_ban' || $_GET['sort'] == 'count_game' || $_GET['sort'] == 'isbanned')) {
		$sort_key = $_GET['sort'];
	}

	$sort_function = function($a, $b) use ($sort_key) {
		if($sort_key == 'name' || $sort_key == 'realm' || $sort_key == 'isbanned') {
			return strcmp($a[$sort_key], $b[$sort_key]);
		} else {
			return $b[$sort_key] - $a[$sort_key];
		}
	};

	uasort($players, $sort_function);

	foreach($players as $p_data) {
		echo "<tr>\n";
		echo "\t<td><a href=\"search.php?username=" . htmlspecialchars(urlencode($p_data['name'])) . "&realm=" . htmlspecialchars(urlencode($p_data['realm'])) . "\">" . htmlspecialchars($p_data['name']) . "</a></td>\n";
		echo "\t<td>" . htmlspecialchars($p_data['realm']) . "</td>\n";
		echo "\t<td>" . $p_data['last_time'] . "</td>\n";
		echo "\t<td>" . $p_data['count_ban'] . "</td>\n";
		echo "\t<td>" . $p_data['count_game'] . "</td>\n";
		echo "\t<td>" . $p_data['isbanned'] . "</td>\n";
		echo "</tr>\n";
	}

	?>

	</table>
	<p><a href="./">back to index</a></p>
	</body>
	</html>

	<?
}
?>
