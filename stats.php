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

?>

</table>
</body>
</html>
