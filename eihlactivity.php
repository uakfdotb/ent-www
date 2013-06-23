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

<p>Note: times are in CDT/CST. The current time is <?= date("j M Y H:i:s T"); ?>.</p>

<table>
<tr>
	<th>Hour</th>
	<th>Number of games played (last two weeks)</th>
</tr>

<?php

include("include/common.php");
include("include/dbconnect.php");

for($i = 0; $i < 24; $i++) {
	$result = databaseQuery("SELECT COUNT(*) FROM eihlgames LEFT JOIN games ON games.id = eihlgames.gameid WHERE HOUR(games.datetime) = $i AND datetime > DATE_SUB(NOW(), INTERVAL 14 DAY)");
	$row = $result->fetch();
	
	if($row[0] > 0) {
		echo "<tr>";
		echo "<td>$i:00 to ". ($i + 1) . ":00</td>";
		echo "<td><center>{$row[0]}</center></td>";
		echo "</tr>";
	}
}

?>

</body>
</html>
