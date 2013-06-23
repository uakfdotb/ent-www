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
<tr>
	<th><a href="statsbot.php">Bot ID<a></th>
	<th><a href="statsbot.php?order=count">Games in last week</a></th>
	<th>Last gamename</th>
</tr>

<?php

include("include/common.php");
include("include/dbconnect.php");

$order = "botid";

if(isset($_REQUEST['order']) && $_REQUEST['order'] == "count") {
	$order = "COUNT(botid) DESC";
}

$result = databaseQuery("SELECT botid, COUNT(botid), MAX(id) FROM games WHERE datetime > DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY botid ORDER BY $order");
$botids = array();

while($row = $result->fetch()) {
	$botid = $row[0];
	$botids[] = $botid;
	$games = $row[1];
	$gameid = $row[2];
	
	$inner_result = databaseQuery("SELECT gamename FROM games WHERE id = ?", array($gameid));
	if($inner_row = $inner_result->fetch()) {
		$gamename = $inner_row[0];
	} else {
		$gamename = "None";
	}
	
	echo "<tr>";
	echo "<td>$botid</td>";
	echo "<td>$games</td>";
	echo "<td>" . htmlspecialchars($gamename) . "</td>";
	echo "</tr>";
}

$result = databaseQuery("SELECT botid, gamename FROM gamelist WHERE totalplayers != 0 OR gamename != '' ORDER BY botid");

while($row = $result->fetch()) {
	$botid = $row[0];
	$gamename = $row[1];

	if(!in_array($botid, $botids)) {
		echo "<tr>";
		echo "<td>$botid</td>";
		echo "<td>0</td>";
		echo "<td>" . htmlspecialchars($gamename) . "</td>";
		echo "</tr>";
	}
}

?>

</table>
</body>
</html>
