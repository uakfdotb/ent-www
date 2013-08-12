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

if(!isset($id) && !empty($_REQUEST['id'])) {
	$id = $_REQUEST['id'];
}

if(!isset($id) || $id == 0) {
	return;
} else {
	// strip non-digits from id
	$id = preg_replace("/[^0-9]/", "", $id);
	$id = intval($id);

	$cachefile = "gamescache/slots.$id.cache";

	if(file_exists($cachefile) && time() - filemtime($cachefile) < 5) {
		echo file_get_contents($cachefile);
		return;
	}

	ob_start();

	include("../include/common.php");
	include("../include/dbconnect.php");
}

$result = databaseQuery("SELECT botid, gamename, usernames, lobby FROM gamelist WHERE id = ?", array($id));
if($row = $result->fetch()) {
	$botid = $row[0];

	$mode = "";
	
	if($row[3] == 1) {
		if(strpos(strtolower($row[1]), "lod") !== FALSE) $mode = "lod";
		else if(strpos(strtolower($row[1]), "eihl") !== FALSE) $mode = "eihl";
		else if(($botid >= 5 && $botid <= 11) || strpos(strtolower($row[1]), "dota") !== FALSE) $mode = "dota";
		else if($botid == 1) $mode = "castlefight";
		else if($botid == 15) $mode = "treetag";
		else if($botid == 18 || $botid == 57 || $botid == 79 || $botid == 96) $mode = "legionmega";
		else if(strpos(strtolower($row[1]), "lihl") !== FALSE) $mode = "lihl";
		else if($botid == 16) $mode = "civwars";
		else if($botid == 65) $mode = "battleships";
		else if($botid == 82) $mode = "rvs";
		else if($botid == 54) $mode = "herolinewars";
		else if($botid == 60) $mode = "islanddefense";
		else if($botid == 71) $mode = "nwu";
	}
?>

	<h2>Gamename: <?= $row[1] ?></h2>
	<table>
	<tr>
		<th class="games">Username</th>
		<th class="games">Realm</th>
		<th class="games">Ping</th>
		<? if($mode != "") { ?>
		<th class="games">ELO</th>
		<th class="games">W/L</th>
			<? if($mode == "dota" || $mode == "lod" || $mode == "eihl") { ?>
			<th class="games">K/D</th>
			<? }
		} ?>
	</tr>

<?

	$array = explode("\t", $row[2]);

	//if scored game, let's precompute all the scores
	if($mode != "") {
		$cutoff = 4;
		$striped = false; //means that even slots are first team, odd slots are second team

		if($mode == "castlefight" || $mode == "civwars" || $mode == "herolinewars") {
			$cutoff = 2;
		} else if($mode == "legionmega") {
			$cutoff = 3;
		} else if($mode == "treetag") {
			$cutoff = 8;
		} else if($mode == "islanddefense") {
			$cutoff = 9;
		} else if($mode == "rvs") {
			$cutoff = 4;
			$striped = true;
		}

		include("elo.php");

		$stats = array();
		$player_ratings = array();
		$player_teams = array();
		$num_teams = 2;
		$team_ratings = array(0.0, 0.0);
		$team_count = array(0, 0);
		$team_difference = array();

		for($i = 0; $i * 4 < count($array) - 3; $i++) {
			$username = $array[$i * 4];
			$realm = $array[$i * 4 + 1];

			if($username == "") {
				continue;
			}

			if($mode == "lod" || $mode == "dota" || $mode == "eihl") {
				$result = databaseQuery("SELECT IFNULL(SUM(kills), 0), IFNULL(SUM(deaths), 1), IFNULL(SUM(wins), 0), IFNULL(SUM(losses), 0), IFNULL(MAX(score), 1000) FROM " . $mode . "_elo_scores WHERE name=? AND server=?", array($username, $realm));
			} else {
				$result = databaseQuery("SELECT 0 AS zero1, 0 AS zero2, IFNULL(SUM(wins), 0), IFNULL(SUM(losses), 0), IFNULL(MAX(score), 1000) FROM w3mmd_elo_scores WHERE name=? AND server=? AND category = ?", array($username, $realm, $mode));
			}

			$row = $result->fetch();

			//update player stats
			$stats[] = array($row[0], $row[1], $row[2], $row[3]);
			$player_ratings[] = $row[4];

			//update team stats
			if($striped) {
				$team = $i % 2 == 0 ? 0 : 1;
			} else {
				$team = $i <= $cutoff ? 0 : 1;
			}
			
			$player_teams[] = $team;
			$team_ratings[$team] += $row[4];
			$team_count[$team]++;
		}

		$numPlayers = $team_count[0] + $team_count[1];

		if($team_count[0] > 0) {
			$team_ratings[0] /= $team_count[0];
		} else {
			$team_ratings[0] = 1000;

			//set default team difference because this won't be set later
			$team_difference[0] = array(0, 0);
		}

		if($team_count[1] > 0) {
			$team_ratings[1] /= $team_count[1];
		} else {
			$team_ratings[1] = 1000;

			//set default team difference because this won't be set later
			$team_difference[$cutoff + 1] = array(0, 0);
		}

		//see what happens if the each team wins
		$sentinelRatings = elo_recalculate_ratings($numPlayers, $player_ratings, $player_teams, $num_teams, $team_ratings, array(1, 0));
		$scourgeRatings = elo_recalculate_ratings($numPlayers, $player_ratings, $player_teams, $num_teams, $team_ratings, array(0, 1));

		//this loop is kind of pointless now!
		for($i = 0; $i < $numPlayers; $i++) {
			if($player_teams[$i] == 0) {
				$team_difference[$i] = array(abs($sentinelRatings[$i] - $player_ratings[$i]), abs($scourgeRatings[$i] - $player_ratings[$i]));
				if(!isset($team_difference[0])) $team_difference[0] = $team_difference[$i];
			} else {
				$team_difference[$i] = array(abs($scourgeRatings[$i] - $player_ratings[$i]), abs($sentinelRatings[$i] - $player_ratings[$i]));
				if(!isset($team_difference[$cutoff + 1])) $team_difference[$cutoff + 1] = $team_difference[$i];
			}
		}
	}

	$counter = 0; //counter for non-empty slots

	for($i = 0; $i < count($array) - 3; $i+=4) {
		$array_index = $i;
		
		//if striped, the array index has to be specially calculated
		if($striped) {
			$slot_index = intval($i / 4); //this is the slot index, if the slots weren't striped!
			
			if($slot_index <= $cutoff) { //on first team
				$array_index = $slot_index * 8;
			} else { //on second team
				$array_index = ($slot_index - $cutoff) * 8 - 4;
			}
		}
		
		if($mode != "" && $i % (($cutoff + 1) * 4) == 0) {
			if($mode == "dota" || $mode == "lod" || $mode == "eihl") {
				if($i == 0) {
					$team = '<font color="#9E0000">Sentinel</font>';
				} else {
					$team = '<font color="#0B7600">Scourge</font>';
				}
			} else if($mode == "castlefight" || $mode == "legionmega" || $mode == "civwars") {
				if($i == 0) {
					$team = "East";
				} else {
					$team = "West";
				}
			} else if($mode == "treetag") {
				if($i == 0) {
					$team = "Ents";
				} else {
					$team = "Infernals";
				}
			} else if($mode == "battleships") {
				if($i == 0) {
					$team = "South";
				} else {
					$team = "North";
				}
			} else if($mode == "islanddefense") {
				if($i == 0) {
					$team = "Builders";
				} else {
					$team = "Titan";
				}
			} else if($mode == "rvs") {
				if($i == 0) {
					$team = "Kobold heroes";
				} else {
					$team = "Gnoll heroes";
				}
			} else {
				if($i == 0) {
					$team = "First team";
				} else {
					$team = "Second team";
				}
			}

			$x = $i == 0 ? 0 : 1;
			$y = $i == 0 ? 0 : $cutoff + 1;
			echo '<tr><td colspan="6" class="slotbig"><b>' . $team . '</b> (avg: ' . round($team_ratings[$x], 2) . '; change: <i>+' . round($team_difference[$y][0], 1) . '/-' . round($team_difference[$y][1], 1) . '</i>)</td></tr>';
		}

		$username = $array[$array_index];
		$realm = $array[$array_index + 1];

		if($realm == "cloud.ghostclient.com") $realm = "GClient";
		else if($realm == "uswest.battle.net") $realm = "USWest";
		else if($realm == "useast.battle.net") $realm = "USEast";
		else if($realm == "europe.battle.net") $realm = "Europe";
		else if($realm == "asia.battle.net") $realm = "Asia";

		$ping = $array[$array_index + 2];

		if($username == "") {
			$colspan = 3;
			if($mode == "dota" || $mode == "lod" || $mode == "eihl") $colspan = 6;

			echo "<tr><td colspan=\"$colspan\" class=\"slot\">Empty</td></tr>";
		} else {
			echo "<tr>";

			//link to stats depending on mode
			if($mode != "") {
				if($mode == "dota" || $mode == "lod" || $mode == "eihl") {
					echo "<td class=\"slot\"><a href=\"/openstats/$mode/player/" . urlencode($username) . "/\" target=\"blank\">" . htmlentities($username) . "</a></td>";
				} else {
					echo "<td class=\"slot\"><a href=\"/customstats/$mode/player/" . urlencode($username) . "/\" target=\"blank\">" . htmlentities($username) . "</a></td>";
				}
			} else {
				echo "<td class=\"slot\">" . htmlentities($username) . "</td>";
			}

			echo "<td class=\"slot\">$realm</td>";
			echo "<td class=\"slot\">$ping</td>";

			//get stats depending on mode
			if($mode != "") {
				echo "<td class=\"slot\">" . round($player_ratings[$counter], 1) . "</td>";
				echo "<td class=\"slot\">" . $stats[$counter][2] . "/" . $stats[$counter][3] . "</td>";

				//kill-death ratio is only for dota
				if($mode == "dota" || $mode == "lod" || $mode == "eihl") {
					$kd = 0;
					if($stats[$counter][1] != 0) {
						$kd = round($stats[$counter][0] / $stats[$counter][1], 2);
					}
					echo "<td class=\"slot\">" . $kd . "</td>";
				}
			}

			echo "</tr>";
			$counter++;
		}
	}
} else {
	echo "<h3>No users found for this game.</h3>";
}

?>

</table>

<?
file_put_contents($cachefile, ob_get_contents());
ob_end_flush();
?>
