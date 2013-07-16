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
include("../include/ban.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");
	
	$username_clean = $user->data['username_clean'];
	
	//timezone stuff
	date_default_timezone_set(AUTOMATIC_DST_TIMEZONE);
	$timezoneAbbr = timezoneAbbr(AUTOMATIC_DST_TIMEZONE);
	?>
	
	<html>
	<head><title>ENT Ban - User Search</title></head>
	<body>
	<h1>Search a user</h1>
	
	<p>You can search for a user on this page using the form below. All realms separately will display statistics for the username on each realm, while all realms aggregated will ignore the realm completely and just look up based on username. When using all realms separately or aggregated, you will also be able to see statistics when the username was not spoof checked; for the former, this will be labeled like "uakf.b@", with nothing following the @ symbol.</p>
	
	<p>Your timezone is <?= $timezoneAbbr ?>. The current time is <?= uxtDate() ?>.</p>
	
	<form method="GET" action="search.php">
	Username: <input type="text" name="username" />
	<br />Realm: <select name="realm">
		<option value="">All realms separately</option>
		<option value="*">All realms aggregated</option>
		<option value="uswest.battle.net">USWest</option>
		<option value="useast.battle.net">USEast</option>
		<option value="europe.battle.net">Europe</option>
		<option value="asia.battle.net">Asia</option>
		<option value="entconnect">Ent Connect</option>
		<option value="cloud.ghostclient.com">Ghost Client</option>
		<option value="">Garena</option>
		</select>
	<br /><input type="submit" value="Search" />
	</form>
	
	<?
	
	if(isset($_REQUEST['username']) && $_REQUEST['username'] != '' && isset($_REQUEST['realm'])) {
		$username = trim($_REQUEST['username']);
		$realm = trim($_REQUEST['realm']);
		
		$realms = array("", "uswest.battle.net", "useast.battle.net", "europe.battle.net", "asia.battle.net", "entconnect", "cloud.ghostclient.com");
		if($realm != "") $realms = array($realm);
		
		foreach($realms as $realm_it) {
			$where = "WHERE name = ?";
			$parameters = array($username);
			
			if($realm_it != "*") {
				$where .= " AND realm = ?";
				$parameters[] = $realm_it;
			}
			
			//grab general statistics
			$result = databaseQuery("SELECT time_created, time_active, num_games, (total_leftpercent / num_games)*100, lastgames, ROUND(playingtime / 3600) FROM gametrack $where", $parameters);
			$row = $result->fetch();
			
			$firstgame = uxtDate(convertTime($row[0]));
			$lastgame = uxtDate(convertTime($row[1]));
			$totalgames = $row[2];
			$staypercent = $row[3];
			$lastgames = $row[4];
			$playingtime = $row[5];
			
			echo "<h2>" . htmlspecialchars($username) . "@" . htmlspecialchars($realm_it) . "</h2>";
			
			if($totalgames != 0) {
				echo "<h3>General statistics</h3>";
				echo "<table>";
				echo "<tr><th>Field</th><th>Value</th></tr>";
				echo "<tr><td>First game</td><td>" . $firstgame . "</td></tr>";
				echo "<tr><td>Last game</td><td>" . $lastgame . "</td></tr>";
				echo "<tr><td>Total games</td><td>" . $totalgames . "</td></tr>";
				echo "<tr><td>Stay percent</td><td>" . $staypercent . "</td></tr>";
				echo "<tr><td>Playing time</td><td>" . $playingtime . " hours</td></tr>";
				echo "</table>";
			
				//last few games
				echo "<h3>Last few games</h3>";
				
				$lastgames = explode(",", $lastgames);
				
				echo "<ul>";
				//process in reverse
				for($i = count($lastgames) - 1; $i >= 0 && $i >= count($lastgames) - 12; $i--) {
					$gameid = $lastgames[$i];
					$result = databaseQuery("SELECT gamename FROM games WHERE id = ?", array($gameid));
					
					if($row = $result->fetch()) {
						echo "<li><a href=\"game.php?id=$gameid\">" . $row[0] . "</a></li>";
					}
				}
				echo "<li><b><a href=\"games.php?username=" . urlencode($username) . "&realm=" . urlencode($realm_it) . "\">More</a></b></li>";
				echo "</ul>";
				
				//ban history
				$result = databaseQuery("SELECT admin, reason, gamename, date, expiredate, unban_reason, banid FROM ban_history WHERE name = ? AND server = ? ORDER BY id DESC LIMIT 6", array($username, $realm_it));
				
				if($result->rowCount() > 0) {
					echo "<h3>Ban history</h3>";
				
					echo "<table cellpadding=\"2\">";
					echo "<tr><th>Admin</th><th>Reason</th><th>Gamename</th><th>Date</th><th>Expire</th><th>Unban</th></tr>";
					
					while($row = $result->fetch()) {
						echo "<tr>";
						echo "<td>" . htmlentities($row[0]) . "</td>";
						echo "<td>" . niceReason($row[1]) . "</td>";
						echo "<td>" . htmlentities($row[2]) . "</td>";
						echo "<td>" . htmlentities($row[3]) . "</td>";
						echo "<td style=\"padding-left:15px;\">" . uxtDate(convertTime($row[4])) . "</td>";
						echo "<td style=\"padding-left:15px;\">" . niceUnbanReason($row[5], $row[6]) . "</td>";
						echo "</tr>";
					}
					
					if($result->rowCount() == 6) {
						$result = databaseQuery("SELECT COUNT(*) FROM ban_history WHERE name = ? AND server = ?", array($username, $realm_it));
						$row = $result->fetch();
						echo "<tr>";
						echo "<td colspan=\"5\"><a href=\"banhist.php?name=" . urlencode($username) . "&server=" . urlencode($realm_it) . "\">More (total: {$row[0]})</a></td>";
						echo "</tr>";
					}
					
					echo "</table>";
				}
			} else {
				echo "<p><b><i>No games found for this user.</i></b></p>";
			}
		}
	}
	?>
	
	<p><a href="./">back to index</a></p>
	</body>
	</html>
	
	<?
}
?>
