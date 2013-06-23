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

include("include/common.php");
include("include/dbconnect.php");
include("include/botlocate.php");

if(isset($_REQUEST['id'])) {
	$gameid = $_REQUEST['id'];
	$result = databaseQuery("SELECT botid FROM games WHERE id = ?", array($gameid));

	if($row = $result->fetch()) {
		$botid = intval($row[0]);
		$sid = 0;

		if(isset($idToServer[$botid])) {
			$sid = $idToServer[$botid];
		}

		if($sid === 0) {
			echo "<b>Replay does not exist for that game ($botid).</b>";
		} else {
			if(!isset($_GET['chat'])) {
				header("Location: http://$sid.entgaming.net/replay/view_replay.php?file=$gameid.w3g");
			} else {
				header("Location: http://$sid.entgaming.net/replay/replays/$gameid.txt");
			}
		}
	} else {
		echo "<b>Could not find the requested game!</b>";
	}
} else {
	?>
	<form method="get" action="replay.php">
	Game ID: <input type="text" name="id"> <input type="submit" value="Get replay">
	</form>
	<?
}

?>
