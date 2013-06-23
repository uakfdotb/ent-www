<?php
/*

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

*/

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
