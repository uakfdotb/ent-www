<html>
<body>

<?php

include("include/common.php");
include("include/dbconnect.php");

if(isset($_REQUEST['player'])) {
	$player = $_REQUEST['player'];
	$name = $player;
	$realm = FALSE;
	
	$tokenPos = strpos($player, '@');
	
	if($tokenPos !== FALSE) {
		$name = substr($player, 0, $tokenPos);
		$realm = substr($player, $tokenPos + 1);
	}
	
	$query = "SELECT gameid, games.gamename FROM gameplayers LEFT JOIN games ON gameid = games.id WHERE name = ?";
	$parameters = array($name);
	
	if(!empty($realm)) {
		$query .= " AND spoofedrealm = ?";
		$parameters[] = $realm;
	}
	
	$query .= " ORDER BY gameplayers.id DESC LIMIT 50";
	$result = databaseQuery($query, $parameters);
	
	echo "<ul>";
	
	while($row = $result->fetch()) {
		$gamename = htmlspecialchars($row[1]);
		echo "<li><a href=\"findstats.php?id={$row[0]}\">$gamename</a></li>";
	}
	
	echo "</ul>";
}

?>
<form method="get" action="findreplay.php">
Username[@realm] <input type="text" name="player"> <input type="submit" value="Find games/replays">
</form>

</body>
</html>
