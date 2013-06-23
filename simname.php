<!--

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

-->

<html>
<body>

<p>Enter wildcard player name, like "uakf.*", to get similar names.</p>

<form method="get" action="simname.php">
Player name: <input type="text" name="player"> <input type="submit" value="Search">
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

include("include/common.php");
include("include/dbconnect.php");
include("include/iplookup.php");

$player = "";
$hours = 24 * 30;

if(isset($_REQUEST['player'])) {
	$player = $_REQUEST['player'];
}

if(isWhitelist($_SERVER['REMOTE_ADDR'])) {
	if(isset($_REQUEST['hours'])) {
		$hours = $_REQUEST['hours'];
	}
}

$result = simname($player);

while($p_info = $result->fetch()) {
	echo "<tr>\n";
	echo "\t<td><a href=\"bans/search.php?username=" . htmlspecialchars(urlencode($p_info[0])) . "&realm=" . htmlspecialchars(urlencode($p_info[1])) . "\">" . htmlspecialchars($p_info[0]) . "</a></td>\n";
	echo "\t<td>" . htmlspecialchars($p_info[1]) . "</td>\n";
	echo "\t<td>" . lastTimePlayed($p_info[0]) . "</td>\n";
	echo "\t<td>" . countBans($p_info[0], $p_info[1]) . "</td>\n";
	echo "\t<td>" . countGames($p_info[0], $p_info[1]) . "</td>\n";
	echo "\t<td>" . isBanned($p_info[0], $p_info[1]) . "</td>\n";
	echo "</tr>\n";
}

?>

</table>
</body>
</html>
