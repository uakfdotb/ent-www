<?php

$cachefile = "gamescache/games.cache";

if(file_exists($cachefile) && time() - filemtime($cachefile) < 10) {
	echo file_get_contents('gamescache/games.cache');
	return;
}

ob_start();

include("../include/common.php");
include("../include/dbconnect.php");

$result = databaseQuery("SELECT id, botid, slotstaken, slotstotal, lobby, gamename FROM gamelist WHERE lobby = 1 ORDER BY gamename", array(), true);

while($row = $result->fetch()) {
	if($row['gamename'] != "") {
		echo $row['id'] . "|" . $row['botid'] . "|" . $row['slotstaken'] . "|" . $row['slotstotal'] . "|" . $row['lobby'] . "|" . htmlspecialchars($row['gamename']) . "\n";
	}
}

file_put_contents($cachefile, ob_get_contents());
ob_end_flush();

?>
