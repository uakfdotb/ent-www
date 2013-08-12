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

$statsLastResult = false; //whether last operation succeeded/failed

function statsTransfer($source_username, $source_realm, $target_username, $target_realm, $category, $admin_name, $force = true) {
	global $dotaCategories, $w3mmdCategories, $statsLastResult;
	
	$statsLastResult = false;
	
	if(isset($dotaCategories[$category])) {
		//confirm that source stats entry exists
		$result = databaseQuery("SELECT id FROM {$category}_elo_scores WHERE name = ? AND server = ?", array($source_username, $source_realm));
		
		if($row = $result->fetch()) {
			$source_id = $row[0];
			
			//check if target stats already exist
			$result = databaseQuery("SELECT id FROM {$category}_elo_scores WHERE name = ? AND server = ?", array($target_username, $target_realm));
			
			if($row = $result->fetch()) {
				//target stats exist, we have to merge
				$target_id = $row[0];
				
				//get old source stats first
				$result = databaseQuery("SELECT score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills FROM {$category}_elo_scores WHERE id = ?", array($source_id));
				$row = $result->fetch();
				
				//get old target stats too, for the log
				$result = databaseQuery("SELECT score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills FROM {$category}_elo_scores WHERE id = ?", array($target_id));
				$target_row = $result->fetch();
				
				//if we're not force, then make sure this user has enough games to do a transfer
				if(!$force && $row[1] < 15) {
					return "Error transferring stats: source account must have fifteen games played!";
				}
				
				//now merge in the stats
				// to do this, we take the maximum score, and sum all other stats
				$result = databaseQuery("UPDATE {$category}_elo_scores SET score = GREATEST(score, ?), games = games + ?, wins = wins + ?, losses = losses + ?, kills = kills + ?, deaths = deaths + ?, creepkills = creepkills + ?, creepdenies = creepdenies + ?, assists = assists +?, neutralkills = neutralkills + ?, towerkills = towerkills + ?, raxkills = raxkills + ?, courierkills = courierkills + ? WHERE id = ?", array_merge($row, array($target_id)));
				
				//hopefully merged properly, delete the old stats
				$message = "Transferred stats from $source_username@$source_realm to $target_username@$target_realm ($category). Old source: {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}}. Old target: {{$target_row[0]}, {$target_row[1]}, {$target_row[2]}, {$target_row[3]}, {$target_row[4]}, {$target_row[5]}, {$target_row[6]}, {$target_row[7]}, {$target_row[8]}, {$target_row[9]}, {$target_row[10]}, {$target_row[11]}, {$target_row[12]}}.";
				databaseQuery("DELETE FROM {$category}_elo_scores WHERE id = ?", array($source_id));
				
				if($admin_name !== false) {
					adminLog("Transferred stats", $message, $admin_name);
				}
				
				$statsLastResult = true;
			} else {
				//target stats do not exist, all we have to do is update the id
				
				//get old source stats first (for logging)
				$result = databaseQuery("SELECT score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills FROM {$category}_elo_scores WHERE id = ?", array($source_id));
				$row = $result->fetch();
				
				//update the id
				databaseQuery("UPDATE {$category}_elo_scores SET name = ?, server = ? WHERE id = ?", array($target_username, $target_realm, $source_id));
				
				$message = "Transferred stats from $source_username@$source_realm to $target_username@$target_realm ($category). No merge performed (target didn't have stats). Old source: {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}}.";
				
				if($admin_name !== false) {
					adminLog("Transferred stats", $message, $admin_name);
				}
				
				$statsLastResult = true;
			}
		} else {
			$message = "Source statistics do not exist ($category) ! Failed to transfer";
		}
	} else if(isset($w3mmdCategories[$category])) {
		//confirm that source stats entry exists
		$result = databaseQuery("SELECT id FROM w3mmd_elo_scores WHERE name = ? AND server = ? AND category = ?", array($source_username, $source_realm, $category));
		
		if($row = $result->fetch()) {
			$source_id = $row[0];
			
			//check if target stats already exist
			$result = databaseQuery("SELECT id FROM w3mmd_elo_scores WHERE name = ? AND server = ? AND category = ?", array($target_username, $target_realm, $category));
			
			if($row = $result->fetch()) {
				//target stats exist, we have to merge
				$target_id = $row[0];
				
				//get old source stats first
				$result = databaseQuery("SELECT score, games, wins, losses, intstats0, intstats1, intstats2, intstats3, intstats4, intstats5, intstats6, intstats7, doublestats0, doublestats1, doublestats2, doublestats3 FROM w3mmd_elo_scores WHERE id = ?", array($source_id));
				$row = $result->fetch();
				
				//get old target stats too, for the log
				$result = databaseQuery("SELECT score, games, wins, losses, intstats0, intstats1, intstats2, intstats3, intstats4, intstats5, intstats6, intstats7, doublestats0, doublestats1, doublestats2, doublestats3 FROM w3mmd_elo_scores WHERE id = ?", array($target_id));
				$target_row = $result->fetch();
				
				//if we're not force, then make sure this user has enough games to do a transfer
				if(!$force && $row[1] < 15) {
					return "Error transferring stats: source account must have fifteen games played!";
				}
				
				//now merge in the stats
				// to do this, we take the maximum score, and sum all other stats
				$result = databaseQuery("UPDATE w3mmd_elo_scores SET score = GREATEST(score, ?), games = games + ?, wins = wins + ?, losses = losses + ?, intstats0 = intstats0 + ?, intstats1 = intstats1 + ?, intstats2 = intstats2 + ?, intstats3 = intstats3 + ?, intstats4 = intstats4 + ?, intstats5 = intstats5 + ?, intstats6 = intstats6 + ?, intstats7 = intstats7 + ?, doublestats0 = doublestats0 + ?, doublestats1 = doublestats1 + ?, doublestats2 = doublestats2 + ?, doublestats3 = doublestats3 + ? WHERE id = ?", array_merge($row, array($target_id)));
				
				//hopefully merged properly, delete the old stats
				$message = "Transferred stats from $source_username@$source_realm to $target_username@$target_realm ($category). Old source: {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}, {$row[13]}, {$row[14]}, {$row[15]}}. Old target: {{$target_row[0]}, {$target_row[1]}, {$target_row[2]}, {$target_row[3]}, {$target_row[4]}, {$target_row[5]}, {$target_row[6]}, {$target_row[7]}, {$target_row[8]}, {$target_row[9]}, {$target_row[10]}, {$target_row[11]}, {$target_row[12]}, {$target_row[13]}, {$target_row[14]}, {$target_row[15]}}.";
				
				if($admin_name !== false) {
					adminLog("Transferred stats", $message, $admin_name);
				}
				
				$statsLastResult = true;
				databaseQuery("DELETE FROM w3mmd_elo_scores WHERE id = ?", array($source_id));
			} else {
				//target stats do not exist, all we have to do is update the id
				
				//get old source stats first (for logging)
				$result = databaseQuery("SELECT score, games, wins, losses, intstats0, intstats1, intstats2, intstats3, intstats4, intstats5, intstats6, intstats7, doublestats0, doublestats1, doublestats2, doublestats3 FROM w3mmd_elo_scores WHERE id = ?", array($source_id));
				$row = $result->fetch();
				
				//update id
				databaseQuery("UPDATE w3mmd_elo_scores SET name = ?, server = ? WHERE id = ?", array($target_username, $target_realm, $source_id));
				
				$message = "Transferred stats from $source_username@$source_realm to $target_username@$target_realm ($category). No merge performed (target didn't have stats). Old source: {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}, {$row[13]}, {$row[14]}, {$row[15]}}.";
				
				if($admin_name !== false) {
					adminLog("Transferred stats", $message, $admin_name);
				}
				
				$statsLastResult = true;
			}
		} else {
			$message = "Source statistics do not exist ($category)! Failed to transfer";
		}
	} else {
		$message = "Error: invalid category [" . $category . "].";
	}
	
	return $message;
}

function statsClear($username, $realm, $category, $admin_name, $force = true) {
	global $w3mmdCategories, $dotaCategories, $statsLastResult;

	$message = "";
	
	if(isset($dotaCategories[$category])) {
		//save old stats for log, also to check if it exists at all
		$result = databaseQuery("SELECT score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills, id FROM {$category}_elo_scores WHERE name = ? AND server = ?", array($username, $realm));
	
		if($row = $result->fetch()) {
			//if we're not force, then make sure this user has enough games to clear
			if(!$force && $row[1] < 15) {
				return "Error clearing stats: account must have fifteen games played!";
			}
			
			databaseQuery("DELETE FROM {$category}_elo_scores WHERE id = ?", array($row[13]));
			$message = "Deleted stats on $username@$realm ($category): {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}}.";
			
			if($admin_name !== false) {
				adminLog("Deleted stats", $message, $admin_name);
			}
			
			$statsLastResult = true;
		} else {
			$message = "Error: no stats found to clear";
		}
	} else if(isset($w3mmdCategories[$category])) {
		//save old stats for log, also to check if it exists at all
		$result = databaseQuery("SELECT score, games, wins, losses, intstats0, intstats1, intstats2, intstats3, intstats4, intstats5, intstats6, intstats7, doublestats0, doublestats1, doublestats2, doublestats3, id FROM w3mmd_elo_scores WHERE name = ? AND server = ? AND category = ?", array($username, $realm, $category));
	
		if($row = $result->fetch()) {
			//if we're not force, then make sure this user has enough games to clear
			if(!$force && $row[1] < 15) {
				return "Error clearing stats: account must have fifteen games played!";
			}
			
			databaseQuery("DELETE FROM w3mmd_elo_scores WHERE id = ?", array($row[16]));
			$message = "Deleted stats on $username@$realm ($category): {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}, {$row[13]}, {$row[14]}, {$row[15]}}.";
			
			if($admin_name !== false) {
				adminLog("Deleted stats", $message, $admin_name);
			}
			
			$statsLastResult = true;
		} else {
			$message = "Error: no stats found to clear";
		}
	} else {
		$message = "Error: invalid category [" . $category . "].";
	}
	
	return $message;
}

function statsRestore($username, $realm, $category, $stats_string, $admin_name) {
	global $w3mmdCategories, $dotaCategories, $statsLastResult;

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
	
	if(isset($dotaCategories[$category])) {
		if(count($array) == 13) {
			//make sure no existing stats
			$result = databaseQuery("SELECT COUNT(*) FROM {$category}_elo_scores WHERE name = ? AND server = ?", array($username, $realm));
			$row = $result->fetch();
		
			if($row[0] == 0) {
				databaseQuery("INSERT INTO {$category}_elo_scores (name, server, score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills) VALUES (?, ?, $insertString)", array_merge(array($username, $realm), $insertArray));
				$message = "Restored stats on $username@$realm ($category): '$stats_string'.";
				
				if($admin_name !== false) {
					adminLog("Restored stats", $message, $admin_name);
				}
			
				$statsLastResult = true;
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
				
				if($admin_name !== false) {
					adminLog("Restored stats", $message, $admin_name);
				}
			
				$statsLastResult = true;
			} else {
				$message = "Error: stats for that player in that category already exist.";
			}
		} else {
			$message = "Error: stats string should have 16 entries.";
		}
	} else {
		$message = "Error: invalid category [" . $category . "].";
	}
	
	return $message;
}

?>
