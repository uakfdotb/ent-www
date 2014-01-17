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

	<p>Enter an IP address here, and I will look up the name. You can also enter a partial IP address, but make sure you have a leading dot. For example, "8.8.8.". But do not do "8.8.8", because you need leading dot or it won't do partial search.</p>

	<form method="get" action="namelookup.php">
	IP: <input type="text" name="ip"> <input type="submit" value="Search">
	</form>

	<table>
	<tr>
		<th>Name</th>
		<th>Realm</th>
		<th>Last seen</th>
		<th>Count bans</th>
		<th>Count games</th>
		<th>Is banned?</th>
	</tr>

	<?php

	$ip = $_SERVER['REMOTE_ADDR'];

	if(isset($_REQUEST['ip'])) {
		$ip = $_REQUEST['ip'];
	}

	$result = namelookup($ip);

	while($row = $result->fetch()) {
		echo "<tr>\n";
		echo "\t<td><a href=\"search.php?username=" . htmlspecialchars(urlencode($row[0])) . "&realm=" . htmlspecialchars(urlencode($row[1])) . "\">" . htmlspecialchars($row[0]) . "</a></td>\n";
		echo "\t<td>" . htmlspecialchars($row[1]) . "</td>\n";
		echo "\t<td>" . lastTimePlayed($row[0]) . "</td>\n";
		echo "\t<td>" . countBans($row[0], $row[1]) . "</td>\n";
		echo "\t<td>" . countGames($row[0], $row[1]) . "</td>\n";
		echo "\t<td>" . isBanned($row[0], $row[1]) . "</td>\n";
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
