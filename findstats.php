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

<?php

include("include/common.php");
include("include/dbconnect.php");

if(isset($_REQUEST['id'])) {
	$gameid = $_REQUEST['id'];
	$result = databaseQuery("SELECT botid FROM games WHERE id = ?", array($gameid));

	if($row = $result->fetch()) {
		$botid = $row[0];

		$statstype = 0;
		$statsmode = 0;

		if(($botid >= 5 && $botid <= 11) || $botid == 38 || $botid == 66 || $botid == 67 || $botid == 68 || $botid == 69 || ($botid >= 90 && $botid <= 94)) {
			$statstype = "open";
			$statsmode = "dota";
		} else if($botid == 30 || $botid == 14) {
			$statstype = "open";
			$statsmode = "dota2";
		} else if($botid == 26 || $botid == 27 || $botid == 28) {
			$statstype = "custom";
			$statsmode = "lihl";
		} else if($botid == 32 || $botid == 77) {
			$statstype = "open";
			$statsmode = "lod";
		} else if($botid == 1) {
			$statstype = "custom";
			$statsmode = "castlefight";
		} else if($botid == 65) {
			$statstype = "custom";
			$statsmode = "cfone";
		} else if($botid == 18 || $botid == 57 || $botid == 79 || $botid == 96) {
			$statstype = "custom";
			$statsmode = "legionmega";
		} else if($botid == 35) {
			$statstype = "custom";
			$statsmode = "legionmegaone";
		} else if($botid == 31) {
			$statstype = "custom";
			$statsmode = "legionmega_nc";
		} else if($botid == 15) {
			$statstype = "custom";
			$statsmode = "treetag";
		} else if($botid == 16) {
			$statstype = "custom";
			$statsmode = "civwars";
		} else if($botid == 19) {
			$statstype = "custom";
			$statsmode = "battleships";
		} else if($botid == 82) {
			$statstype = "custom";
			$statsmode = "rvs";
		} else if($botid == 54) {
			$statstype = "custom";
			$statsmode = "herolinewars";
		} else if($botid == 60) {
			$statstype = "custom";
			$statsmode = "islanddefense";
		} else if($botid == 71) {
			$statstype = "custom";
			$statsmode = "nwu";
		} else if($botid == 49) {
			$statstype = "custom";
			$statsmode = "enfo";
		}

		if($statstype !== 0) {
			session_start();
			$_SESSION['statsmode'] = $statsmode;
			$_REQUEST['game'] = $gameid;
			$_GET['game'] = $gameid;
			chdir("{$statstype}stats");
			include("index.php");
		} else {
			//show the default stats page
			fsHeader();
			fsShowStats($gameid);
			fsFooter();
		}
	} else {
		fsHeader();
		echo "<b>Could not find the requested game!</b>";
		fsFooter();
	}
} else {
	?>
	<form method="get" action="findstats.php">
	Game ID: <input type="text" name="id"> <input type="submit" value="Get stats page">
	</form>
	<?
}

function fsHeader() {
	?>
	<html>
	<body>
	<?
}

function fsShowStats($gid) {
	$result = databaseQuery("SELECT gamename, datetime, map, duration, ownername FROM games WHERE id = ?", array($gid));

	if($row = $result->fetch()) {
		$gid = htmlspecialchars($gid);
		echo "<p><b><i><a href=\"/replay.php?id=$gid\">Click here to download the replay for this game.</a></i></b></p>";

		echo "<table>";
		echo "<tr>";
		echo "<td><b>Game name</b></td>";
		echo "<td>" . htmlspecialchars($row[0]) . "</td>";
		echo "</tr><tr>";
		echo "<tr>";
		echo "<td><b>Date</b></td>";
		echo "<td>" . uxtDate(convertTime($row[1])) . "</td>";
		echo "</tr><tr>";
		echo "<tr>";
		echo "<td><b>Map</b></td>";
		echo "<td>" . htmlspecialchars($row[2]) . "</td>";
		echo "</tr><tr>";
		echo "<tr>";
		echo "<td><b>Duration</b></td>";
		echo "<td>" . round($row[3] / 60, 2) . "</td>";
		echo "</tr><tr>";
		echo "<tr>";
		echo "<td><b>Owner</b></td>";
		echo "<td>" . htmlspecialchars($row[4]) . "</td>";
		echo "</tr><tr>";
		echo "<tr>";
		echo "<td><b>Replay</b></td>";
		echo "<td><a href=\"/replay.php?id=$gid\">($gid)</a></td>";
		echo "</tr><tr>";
		echo "<tr>";
		echo "<td><b>Lobby chat</b></td>";
		echo "<td><a href=\"/replay.php?id=$gid&chat\">($gid)</a></td>";
		echo "</tr>";

		echo "<table cellpadding=\"5\">";
		echo "<tr>";
		echo "<th>Player name</th>";
		echo "<th>Realm</th>";
		echo "<th>Left</th>";
		echo "<th>Reason</th>";
		echo "</tr>";

		$result = databaseQuery("SELECT name, spoofedrealm, `left`, leftreason FROM gameplayers WHERE gameid = ? ORDER BY colour", array($gid));

		while($row = $result->fetch()) {
			echo "<tr>";
			echo "<td><a href=\"findreplay.php?player=" . htmlspecialchars(urlencode($row[0])) . "@" . htmlspecialchars(urlencode($row[1])) . "\">" . htmlspecialchars($row[0]) . "</a></td>";
			echo "<td>" . htmlspecialchars($row[1]) . "</td>";
			echo "<td>" . round($row[2] / 60, 2) . "</td>";
			echo "<td>" . htmlspecialchars($row[3]) . "</td>";
			echo "</tr>";
		}

		echo "</table>";
	}
}

function fsFooter() {
	?>
	</body>
	</html>
	<?
}

?>
