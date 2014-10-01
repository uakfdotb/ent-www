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

	$player_raw = '';
	$player = array("", "");
	$hours = 24 * 30;
	$depth = 1;

	if(isset($_REQUEST['player'])) {
		$player_raw = $_REQUEST['player'];
		$player = getPlayer($player_raw);
	}

	if(isset($_REQUEST['hours'])) {
		$hours = $_REQUEST['hours'];
	}

	if(isset($_REQUEST['depth'])) {
		$depth = $_REQUEST['depth'];
	}
	?>
	<html>
	<body>

	<p>Enter player name and realm and you'll see aliases. Make sure to use format, name@realm. For example, uakf.b@uswest.battle.net. The ".battle.net" is optional.</p>

	<form method="get" action="alias.php">
	Player (name@realm): <input type="text" name="player"> <input type="submit" value="Search">
	</form>

	<table>
	<tr>
		<th><a href="alias.php?sort=name&player=<?= htmlspecialchars($player_raw) ?>">Name</a></th>
		<th><a href="alias.php?sort=realm&player=<?= htmlspecialchars($player_raw) ?>">Realm</a></th>
		<th><a href="alias.php?sort=last_time_sort&player=<?= htmlspecialchars($player_raw) ?>">Last seen</a></th>
		<th><a href="alias.php?sort=count_ban&player=<?= htmlspecialchars($player_raw) ?>">Count bans</a></th>
		<th><a href="alias.php?sort=count_game&player=<?= htmlspecialchars($player_raw) ?>">Count games</a></th>
		<th><a href="alias.php?sort=isbanned&player=<?= htmlspecialchars($player_raw) ?>">Is banned?</a></th>
	</tr>

	<?

	$array = array();
	alias($player[0], $player[1], $depth, $array, $hours);
	$players = array();

	//construct map from player info string to last time played
	foreach($array as $p_str => $ignore) {
		$p_info = getPlayer($p_str);
		$players[$p_str] = array(
			'name' => $p_info[0],
			'realm' => $p_info[1],
			'last_time_sort' => strtotime(lastTimePlayed($p_info[0], $p_info[1])),
			'last_time' => lastTimePlayed($p_info[0], $p_info[1]),
			'count_ban' => countBans($p_info[0], $p_info[1]),
			'count_game' => countGames($p_info[0], $p_info[1]),
			'isbanned' => isBanned($p_info[0], $p_info[1])
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

	foreach($players as $p_str => $p_data) {
		$p_info = getPlayer($p_str);

		echo "<tr>\n";
		echo "\t<td><a href=\"search.php?username=" . htmlspecialchars(urlencode($p_info[0])) . "&realm=" . htmlspecialchars(urlencode($p_info[1])) . "\">" . htmlspecialchars($p_info[0]) . "</a></td>\n";
		echo "\t<td>" . htmlspecialchars($p_info[1]) . "</td>\n";
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
