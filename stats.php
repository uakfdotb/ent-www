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

<table>

<?php

include("include/common.php");
include("include/dbconnect.php");

$result = databaseQuery("SELECT COUNT(*), SUM(totalplayers) FROM gamelist");
$row = $result->fetch();

echo "<tr><td>Current games</td><td>" . $row[0] . "</td></tr>";
echo "<tr><td>Current players</td><td>" . $row[1] . "</td></tr>";

$result = databaseQuery("SELECT COUNT(DISTINCT botid) FROM gamelist WHERE totalplayers != 0");
$row = $result->fetch();

echo "<tr><td>Bots with players</td><td>" . $row[0] . "</td></tr>";

$result = databaseQuery("SELECT MAX(id) FROM games");
$row = $result->fetch();
$gamesToDate = $row[0];

echo "<tr><td>Games hosted to date</td><td>" . number_format($row[0]) . "</td></tr>";

$result = databaseQuery("SELECT MAX(id) FROM gameplayers");
$row = $result->fetch();
$playersToDate = $row[0];

echo "<tr><td>Players to date</td><td>" . number_format($row[0]) . "</td></tr>";

echo "<tr><td>Average players per game</td><td>" . round($playersToDate / $gamesToDate, 2) . "</td></tr>";

$result = databaseQuery("SELECT COUNT(*) FROM gametrack");
$row = $result->fetch();

echo "<tr><td>Unique players to date</td><td>" . number_format($row[0]) . "</td></tr>";

$result = databaseQuery("SELECT COUNT(*) FROM makemehost_maps");
$row = $result->fetch();

echo "<tr><td>Player-uploaded maps</td><td>" . $row[0] . "</td></tr>";

$result = databaseQuery("SELECT COUNT(*) FROM validate WHERE `key` = ''");
$row = $result->fetch();

echo "<tr><td>Validated players</td><td>" . $row[0] . "</td></tr>";

$result = databaseQuery("SELECT COUNT(*) FROM bans");
$row = $result->fetch();

echo "<tr><td>Current bans</td><td>" . number_format($row[0]) . "</td></tr>";

$result = databaseQuery("SELECT MAX(id) FROM ban_history");
$row = $result->fetch();

echo "<tr><td>Bans to date</td><td>" . number_format($row[0]) . "</td></tr>";

$result = databaseQuery("SELECT id, gamename, duration FROM games WHERE duration = (SELECT MAX(duration) FROM games) LIMIT 1;");
$row = $result->fetch();

echo "<tr><td>Longest game</td><td>" . $row[1] . " at " . round($row[2] / 3600, 2) . " hours (gid=" . $row[0] . ")</td></tr>";

$result = databaseQuery("SELECT SUM(playingtime/3600/24/365) FROM gametrack");
$row = $result->fetch();

echo "<tr><td>Total game time</td><td>" . round($row[0], 2) . " years</td></tr>";

?>

</table>
</body>
</html>
