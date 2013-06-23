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

include("include/common.php");
include("include/dbconnect.php");
include("include/iplookup.php");

$ip = $_SERVER['REMOTE_ADDR'];

if(isset($_REQUEST['ip'])) {
	$ip = $_REQUEST['ip'];
}

$result = namelookup($ip);

while($row = $result->fetch()) {
	echo "<tr>\n";
	echo "\t<td><a href=\"bans/search.php?username=" . htmlspecialchars(urlencode($row[0])) . "&realm=" . htmlspecialchars(urlencode($row[1])) . "\">" . htmlspecialchars($row[0]) . "</a></td>\n";
	echo "\t<td>" . htmlspecialchars($row[1]) . "</td>\n";
	echo "\t<td>" . lastTimePlayed($row[0]) . "</td>\n";
	echo "\t<td>" . countBans($row[0], $row[1]) . "</td>\n";
	echo "\t<td>" . countGames($row[0], $row[1]) . "</td>\n";
	echo "\t<td>" . isBanned($row[0], $row[1]) . "</td>\n";
	echo "</tr>\n";
}

?>

</table>
</body>
</html>
