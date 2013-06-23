<?

function adminLog($action, $desc, $admin) {
	databaseQuery("INSERT INTO admin_actions (action, `desc`, admin) VALUES (?, ?, ?)", array($action, $desc, $admin));
}

function statsClear($username, $realm, $category, $admin_name) {
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
	} else if($category == "treetag" || $category == "civwars" || $category == "battleships" || $category == "legionmega" || $category == "legionmega2" || $category == "castlefight" || $category == "cfone") {
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
	} else if($category == "treetag" || $category == "civwars" || $category == "battleships" || $category == "legionmega" || $category == "legionmega2" || $category == "castlefight" || $category == "cfone") {
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
