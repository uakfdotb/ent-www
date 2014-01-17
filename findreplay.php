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

<?php

include("include/common.php");
include("include/dbconnect.php");

if(isset($_REQUEST['player']) || isset($_REQUEST['botid'])) {
	$name = false;
	$realm = false;
	$botid = false;

	if(!empty($_REQUEST['player'])) {
		$player = $_REQUEST['player'];
		$name = $player;
		$realm = FALSE;

		$tokenPos = strpos($player, '@');

		if($tokenPos !== FALSE) {
			$name = substr($player, 0, $tokenPos);
			$realm = substr($player, $tokenPos + 1);
		}
	}

	if(!empty($_REQUEST['botid'])) {
		$botid = $_REQUEST['botid'];
	}

	if($botid !== false || $name !== false) {
		$parameters = array();

		if($name !== false) {
			$query = "SELECT gameid, games.gamename FROM gameplayers LEFT JOIN games ON gameid = games.id WHERE name = ?";
			$parameters[] = $name;

			if($realm !== false) {
				$query .= " AND spoofedrealm = ?";
				$parameters[] = $realm;
			}

			if($botid !== false) {
				$query .= " AND gameplayers.botid = ?";
				$parameters[] = $botid;
			}
		} else {
			$query = "SELECT id, gamename FROM games WHERE botid = ?";
			$parameters[] = $botid;
		}

		$query .= " ORDER BY games.id DESC LIMIT 200";
		$result = databaseQuery($query, $parameters);

		echo "<ul>";

		while($row = $result->fetch()) {
			$gamename = htmlspecialchars($row[1]);
			echo "<li><a href=\"findstats.php?id={$row[0]}\">$gamename</a></li>";
		}

		echo "</ul>";
	}
}

?>
<form method="get" action="findreplay.php">
Username[@realm] <input type="text" name="player">
<br />Bot ID: <input type="text" name="botid">
<br /><input type="submit" value="Find games/replays">
</form>

</body>
</html>
