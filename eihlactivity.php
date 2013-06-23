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
