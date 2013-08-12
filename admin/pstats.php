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

/*
pstats provides functions relating to statistics.
Specifically, it enables clearing, transferring, and recovering player stats.
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_user.'.$phpEx);
 
// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

include("../include/common.php");
include("../include/admin.php");
include("../include/csrfguard.php");
include("../include/const.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");
	$admin_name = $user->data['username_clean'];
	$bigadmin = isbigadmin($user->data['user_id']);
	
	$message = "";
	
	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}
	
	if($bigadmin && isset($_POST['action']) && isset($_POST['confirm']) && $_POST['confirm'] == "conftrue") {
		if($_POST['action'] == "transfer" && isset($_POST['category']) && isset($_POST['source_username']) && isset($_POST['source_realm']) && isset($_POST['target_username']) && isset($_POST['target_realm'])) {
			$category = $_POST['category'];
			$source_username = $_POST['source_username'];
			$source_realm = $_POST['source_realm'];
			$target_username = $_POST['target_username'];
			$target_realm = $_POST['target_realm'];
			
			if($category == "dota") {
				//confirm that source stats entry exists
				$result = databaseQuery("SELECT id FROM dota_elo_scores WHERE name = ? AND server = ?", array($source_username, $source_realm));
				
				if($row = $result->fetch()) {
					$source_id = $row[0];
					
					//check if target stats already exist
					$result = databaseQuery("SELECT id FROM dota_elo_scores WHERE name = ? AND server = ?", array($target_username, $target_realm));
					
					if($row = $result->fetch()) {
						//target stats exist, we have to merge
						$target_id = $row[0];
						
						//get old source stats first
						$result = databaseQuery("SELECT score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills FROM dota_elo_scores WHERE id = ?", array($source_id));
						$row = $result->fetch();
						
						//get old target stats too, for the log
						$result = databaseQuery("SELECT score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills FROM dota_elo_scores WHERE id = ?", array($target_id));
						$target_row = $result->fetch();
						
						//now merge in the stats
						// to do this, we take the maximum score, and sum all other stats
						$result = databaseQuery("UPDATE dota_elo_scores SET score = GREATEST(score, ?), games = games + ?, wins = wins + ?, losses = losses + ?, kills = kills + ?, deaths = deaths + ?, creepkills = creepkills + ?, creepdenies = creepdenies + ?, assists = assists +?, neutralkills = neutralkills + ?, towerkills = towerkills + ?, raxkills = raxkills + ?, courierkills = courierkills + ? WHERE id = ?", array_merge($row, array($target_id)));
						
						//hopefully merged properly, delete the old stats
						$message = "Transferred stats from $source_username@$source_realm to $target_username@$target_realm ($category). Old source: {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}}. Old target: {{$target_row[0]}, {$target_row[1]}, {$target_row[2]}, {$target_row[3]}, {$target_row[4]}, {$target_row[5]}, {$target_row[6]}, {$target_row[7]}, {$target_row[8]}, {$target_row[9]}, {$target_row[10]}, {$target_row[11]}, {$target_row[12]}}.";
						databaseQuery("DELETE FROM dota_elo_scores WHERE id = ?", array($source_id));
						adminLog("Transferred stats", $message, $admin_name);
					} else {
						//target stats do not exist, all we have to do is update the id
						
						//get old source stats first (for logging)
						$result = databaseQuery("SELECT score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills FROM dota_elo_scores WHERE id = ?", array($source_id));
						$row = $result->fetch();
						
						//update the id
						databaseQuery("UPDATE dota_elo_scores SET name = ?, server = ? WHERE id = ?", array($target_username, $target_realm, $source_id));
						
						$message = "Transferred stats from $source_username@$source_realm to $target_username@$target_realm ($category). No merge performed (target didn't have stats). Old source: {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}}.";
						adminLog("Transferred stats", $message, $admin_name);
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
						
						//now merge in the stats
						// to do this, we take the maximum score, and sum all other stats
						$result = databaseQuery("UPDATE w3mmd_elo_scores SET score = GREATEST(score, ?), games = games + ?, wins = wins + ?, losses = losses + ?, intstats0 = intstats0 + ?, intstats1 = intstats1 + ?, intstats2 = intstats2 + ?, intstats3 = intstats3 + ?, intstats4 = intstats4 + ?, intstats5 = intstats5 + ?, intstats6 = intstats6 + ?, intstats7 = intstats7 + ?, doublestats0 = doublestats0 + ?, doublestats1 = doublestats1 + ?, doublestats2 = doublestats2 + ?, doublestats3 = doublestats3 + ? WHERE id = ?", array_merge($row, array($target_id)));
						
						//hopefully merged properly, delete the old stats
						$message = "Transferred stats from $source_username@$source_realm to $target_username@$target_realm ($category). Old source: {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}, {$row[13]}, {$row[14]}, {$row[15]}}. Old target: {{$target_row[0]}, {$target_row[1]}, {$target_row[2]}, {$target_row[3]}, {$target_row[4]}, {$target_row[5]}, {$target_row[6]}, {$target_row[7]}, {$target_row[8]}, {$target_row[9]}, {$target_row[10]}, {$target_row[11]}, {$target_row[12]}, {$target_row[13]}, {$target_row[14]}, {$target_row[15]}}.";
						adminLog("Transferred stats", $message, $admin_name);
						databaseQuery("DELETE FROM w3mmd_elo_scores WHERE id = ?", array($source_id));
					} else {
						//target stats do not exist, all we have to do is update the id
						
						//get old source stats first (for logging)
						$result = databaseQuery("SELECT score, games, wins, losses, intstats0, intstats1, intstats2, intstats3, intstats4, intstats5, intstats6, intstats7, doublestats0, doublestats1, doublestats2, doublestats3 FROM w3mmd_elo_scores WHERE id = ?", array($source_id));
						$row = $result->fetch();
						
						//update id
						databaseQuery("UPDATE w3mmd_elo_scores SET name = ?, server = ? WHERE id = ?", array($target_username, $target_realm, $source_id));
						
						$message = "Transferred stats from $source_username@$source_realm to $target_username@$target_realm ($category). No merge performed (target didn't have stats). Old source: {{$row[0]}, {$row[1]}, {$row[2]}, {$row[3]}, {$row[4]}, {$row[5]}, {$row[6]}, {$row[7]}, {$row[8]}, {$row[9]}, {$row[10]}, {$row[11]}, {$row[12]}, {$row[13]}, {$row[14]}, {$row[15]}}.";
						adminLog("Transferred stats", $message, $admin_name);
					}
				} else {
					$message = "Source statistics do not exist ($category)! Failed to transfer";
				}
			}
		} else if($_POST['action'] == "clear" && isset($_POST['username']) && isset($_POST['realm']) && isset($_POST['category'])) {
			$message = statsClear($_POST['username'], $_POST['realm'], $_POST['category'], $admin_name);
		} else if($_POST['action'] == "restore" && isset($_POST['username']) && isset($_POST['realm']) && isset($_POST['category']) && isset($_POST['stats'])) {
			$message = statsRestore($_POST['username'], $_POST['realm'], $_POST['category'], $_POST['stats'], $admin_name);
		}
		
		header('Location: pstats.php?message=' . urlencode($message));
	}
	
	?>
	
	<html>
	<head><title>ENT Gaming - Stats Manager</title></head>
	<body>
	<h1>Stats manager</h1>
	
	<? if($message != "") { ?>
	<p><b><i><?= htmlspecialchars($message) ?></i></b></p>
	<? } ?>
	
	<? if($bigadmin) { ?>
		<p>Clear or transfer stats.</p>
		<p><a href="./">Click here to return to index.</a></p>
	
		<h3>Transfer stats</h3>
	
		<form method="post" action="pstats.php">
		<input type="hidden" name="action" value="transfer" />
		Source username: <input type="text" name="source_username" />
		<br />Source realm: <select name="source_realm">
			<option value="useast.battle.net">USEast</option>
			<option value="uswest.battle.net">USWest</option>
			<option value="europe.battle.net">Europe</option>
			<option value="asia.battle.net">Asia</option>
			<option value="entconnect">ENT Connect</option>
			<option value="cloud.ghostclient.com">Ghost Client</option>
			<option value="">Not spoof checked</option>
			</select>
		<br />Target username: <input type="text" name="target_username" />
		<br />Target realm: <select name="target_realm">
			<option value="useast.battle.net">USEast</option>
			<option value="uswest.battle.net">USWest</option>
			<option value="europe.battle.net">Europe</option>
			<option value="asia.battle.net">Asia</option>
			<option value="entconnect">ENT Connect</option>
			<option value="cloud.ghostclient.com">Ghost Client</option>
			<option value="">Not spoof checked</option>
			</select>
		<br />Category: <select name="category">
			<? foreach($dotaCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			<? foreach($w3mmdCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			</select>
		<br /><input type="checkbox" name="confirm" value="conftrue" /> I know what I'm doing!
		<br /><input type="submit" value="Transfer player statistics" />
		</form>
	
		<h3>Clear stats</h3>
	
		<form method="post" action="pstats.php">
		<input type="hidden" name="action" value="clear" />
		Username: <input type="text" name="username" />
		<br />Realm: <select name="realm">
			<option value="useast.battle.net">USEast</option>
			<option value="uswest.battle.net">USWest</option>
			<option value="europe.battle.net">Europe</option>
			<option value="asia.battle.net">Asia</option>
			<option value="entconnect">ENT Connect</option>
			<option value="cloud.ghostclient.com">Ghost Client</option>
			<option value="">Not spoof checked</option>
			</select>
		<br />Category: <select name="category">
			<? foreach($dotaCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			<? foreach($w3mmdCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			</select>
		<br /><input type="checkbox" name="confirm" value="conftrue" /> I know what I'm doing!
		<br /><input type="submit" value="Clear player statistics" />
		</form>
		
		<h3>Restore stats</h3>
		
		<pre>DotA format: {score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills}
W3MMD format: {score, games, wins, losses, ... twelve more values that vary by category}</pre>
		
		<form method="post" action="pstats.php">
		<input type="hidden" name="action" value="restore" />
		Username: <input type="text" name="username" />
		<br />Realm: <select name="realm">
			<option value="useast.battle.net">USEast</option>
			<option value="uswest.battle.net">USWest</option>
			<option value="europe.battle.net">Europe</option>
			<option value="asia.battle.net">Asia</option>
			<option value="entconnect">ENT Connect</option>
			<option value="cloud.ghostclient.com">Ghost Client</option>
			<option value="">Not spoof checked</option>
			</select>
		<br />Stats string: <input type="text" name="stats" /> this is what you get in {} when you delete stats; ex: "{0, 1, 0}"
		<br />Category: <select name="category">
			<? foreach($dotaCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			<? foreach($w3mmdCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			</select>
		<br /><input type="checkbox" name="confirm" value="conftrue" /> I know what I'm doing!
		<br /><input type="submit" value="Restore player stats" />
		</form>
	<? } else { ?>
		<p>Error: you do not have access to this page!</p>
	<? } ?>
	
	</body>
	</html>
	
	<?
}
?>
