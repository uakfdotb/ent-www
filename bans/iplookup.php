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
	?>

	<html>
	<body>

	<p>This function requires whitelist. But if you're whitelisted, you can enter a player name here and get his or her IP address.</p>

	<form method="get" action="iplookup.php">
	Player (name@realm): <input type="text" name="player"> <input type="submit" value="Search">
	</form>

	<table>
	<tr>
		<th>IP</th>
		<th># games</th>
	</tr>

	<?php

	$player = array("", "");
	$hours = 24 * 40;

	if(isset($_REQUEST['player'])) {
		$player = getPlayer($_REQUEST['player']);
	}

	if(isset($_REQUEST['hours'])) {
		$hours = $_REQUEST['hours'];
	}

	$result = iplookup($player[0], $player[1], $hours);

	while($row = $result->fetch()) {
		$ip = htmlspecialchars($row[0]);
		echo "<tr>\n";
		echo "\t<td><a href=\"namelookup.php?ip=$ip\">$ip</a></td>\n";
		echo "\t<td>" . countGamesIP($ip) . "</td>\n";
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
