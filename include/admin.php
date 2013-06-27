<?
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

function adminLog($action, $desc, $admin) {
	databaseQuery("INSERT INTO admin_actions (action, `desc`, admin) VALUES (?, ?, ?)", array($action, $desc, $admin));
}

function statsClear($username, $realm, $category, $admin_name) {
	global $w3mmdCategories;

	$message = "";
	
	if($category == "dota") {
		//save old stats for log, also to check if it exists at all
		$result = databaseQuery("SELECT score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills, id FROM dota_elo_scores WHERE name = ? AND server = ?", array($username, $realm));
	
		if($row = $result->fetch()) {
			databaseQuery("DELETE FROM dota_elo_scores WHERE id = ?", array($row[13]));
			$message = "Deleted stats on $username@$realm ($category): {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}}.";
			adminLog("Deleted stats", $message, $admin_name);
		} else {
			$message = "Error: no stats found to clear";
		}
	} else if(isset($w3mmdCategories[$category])) {
		//save old stats for log, also to check if it exists at all
		$result = databaseQuery("SELECT score, games, wins, losses, intstats0, intstats1, intstats2, intstats3, intstats4, intstats5, intstats6, intstats7, doublestats0, doublestats1, doublestats2, doublestats3, id FROM w3mmd_elo_scores WHERE name = ? AND server = ? AND category = ?", array($username, $realm, $category));
	
		if($row = $result->fetch()) {
			databaseQuery("DELETE FROM w3mmd_elo_scores WHERE id = ?", array($row[16]));
			$message = "Deleted stats on $username@$realm ($category): {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}, {$row[13]}, {$row[14]}, {$row[15]}}.";
			adminLog("Deleted stats", $message, $admin_name);
		} else {
			$message = "Error: no stats found to clear";
		}
	}
	
	return $message;
}

function statsRestore($username, $realm, $category, $stats_string, $admin_name) {
	global $w3mmdCategories;

	$message = "";
	
	$array = explode(",", str_replace(array('{', '}'), array('', ''), $stats_string));
	$insertString = "";
	$insertArray = array();
	
	foreach($array as $i) {
		$i = trim($i);
		
		if($insertString == "") {
			$insertString = "?";
		} else {
			$insertString .= ", ?";
		}
		
		$insertArray[] = $i;
	}
	
	if($category == "dota") {
		if(count($array) == 13) {
			//make sure no existing stats
			$result = databaseQuery("SELECT COUNT(*) FROM dota_elo_scores WHERE name = ? AND server = ?", array($username, $realm));
			$row = $result->fetch();
		
			if($row[0] == 0) {
				databaseQuery("INSERT INTO dota_elo_scores (name, server, score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills) VALUES (?, ?, $insertString)", array_merge(array($username, $realm), $insertArray));
				$message = "Restored stats on $username@$realm ($category): '$stats_string'.";
				adminLog("Restored stats", $message, $admin_name);
			} else {
				$message = "Error: stats for that player in that category already exist.";
			}
		} else {
			$message = "Error: stats string should have 13 entries.";
		}
	} else if(isset($w3mmdCategories[$category])) {
		if(count($array) == 16) {
			//make sure no existing stats
			$result = databaseQuery("SELECT COUNT(*) FROM w3mmd_elo_scores WHERE name = ? AND server = ? AND category = ?", array($username, $realm, $category));
			$row = $result->fetch();
		
			if($row[0] == 0) {
				databaseQuery("INSERT INTO w3mmd_elo_scores (name, server, category, score, games, wins, losses, intstats0, intstats1, intstats2, intstats3, intstats4, intstats5, intstats6, intstats7, doublestats0, doublestats1, doublestats2, doublestats3) VALUES (?, ?, ?, $insertString)", array_merge(array($username, $realm, $category), $insertArray));
				$message = "Restored stats on $username@$realm ($category): '$stats_string'.";
				adminLog("Restored stats", $message, $admin_name);
			} else {
				$message = "Error: stats for that player in that category already exist.";
			}
		} else {
			$message = "Error: stats string should have 16 entries.";
		}
	}
	
	return $message;
}

?>
