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
include("../include/admin.php");
include("../include/csrfguard.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");

	$username_clean = $user->data['username_clean'];
	?>

	<html>
	<head><title>ENT Ban - Ban User</title></head>
	<body>
	<h1>Ban User</h1>

	<p>Fill out the form below to ban a user. Be sure (using the <a href="search.php">User Search</a> function) that other players do not have the same username on other realms. Because this bans IP addresses that the player has used, it is advised that you simply select the realm on which the user played in the game in which he or she was banned.</p>

	<p>If you wish to <b>unban</b> a user, then check the unban box. If this is not selected it will instead ban the user.</p>

	<p>Normally bans should be for under five days. Bans over that duration are only for multiple offenses and very serious offenses like maphacking. You should post in the ban discussion area if you have <b>ANY DOUBT</b> when banning for more than five days (and also if you're unsure for shorter duration bans).</p>

	<p>Note that only IP addresses used within the last 15 days will be banned. This is so that innocent users are not banned.</p>

	<form method="POST" action="ban.php">
	Username: <input type="text" name="username" /> <input type="checkbox" name="isip" value="true" /> Check if this is an IP address
	<br />Reason: <input type="text" name="reason" />
	<br />Category: <select name="category">
		<option value=""></option>
		<option value="maphack">Map hacking</option>
		<option value="third party">Third party tool</option>
		<option value="glitch exploit">Exploiting glitch in map</option>
		<option value="game ruining">Game ruining / griefing</option>
		<option value="feeding">Feeding</option>
		<option value="dodging">Ban dodging</option>
		<option value="leaving">Leaving</option>
		<option value="security">Abusing bot vulnerability</option>
		<option value="other">Other</option>
		</select>
	<br />Realm: <select name="realm">
		<option value="uswest.battle.net">USWest</option>
		<option value="useast.battle.net">USEast</option>
		<option value="europe.battle.net">Europe</option>
		<option value="asia.battle.net">Asia</option>
		<option value="entconnect">Ent Connect</option>
		<option value="garena">Garena</option>
		<option value="server.eurobattle.net">EuroBattle</option>
		<option value="serpi90.no-ip.info">serpi90.no-ip.info</option>
		<option value="">All realms (use with caution!)</option>
		</select>
	<br />Duration: <select name="duration">
		<option value="48">Two days (default)</option>
		<option value="0">Warning</option>
		<option value="2">Two hours</option>
		<option value="4">Four hours</option>
		<option value="8">Eight hours</option>
		<option value="12">Twelve hours</option>
		<option value="24">One day</option>
		<option value="36">Thirty-six hours</option>
		<option value="48">Two days</option>
		<option value="120">Five days</option>
		<option value="168">Seven days</option>
		<option value="240">Ten days</option>
		<option value="336">Two weeks</option>
		<option value="720">One month</option>
		<option value="2160">Three months</option>
		<option value="8640">One year</option>
		</select>
	OR
		<input type="text" name="duration_num" size="4" />
		<select name="duration_period">
		<option value=""></option>
		<option value="1">Hours</option>
		<option value="24">Days</option>
		<option value="168">Weeks</option>
		</select>
	<br />Bot ID: <input type="text" name="targetbot" /> (enter a bot ID to only ban on one specific bot; otherwise leave blank)
	<br /><input type="checkbox" name="unban" value="unban" /> Unban user (if not checked, user will be banned)
	<br /><input type="checkbox" name="reban" value="reban" /> Force reban user (if already banned, delete old ban)
	<br /><input type="submit" value="Ban user" />
	</form>

	<?

	if(isset($_POST['username']) && $_POST['username'] != '' && isset($_POST['realm']) && isset($_POST['duration'])) {
		$unban = false;
		$reban = false;

		if(isset($_POST['unban']) && $_POST['unban'] == 'unban') $unban = true;
		if(isset($_POST['reban']) && $_POST['reban'] == 'reban') $reban = true;

		//make sure reason is set and good; no validation if we're unbanning though
		if(isset($_POST['reason']) && strlen($_POST['reason']) >= 6 && strlen($_POST['reason']) <= 255 && ($unban || !empty($_POST['category']))) {
			$usernames = explode(',', strtolower($_POST['username']));

			foreach($usernames as $username) {
				$username = trim($username);
				$realm = $_POST['realm'];
				$targetbot = 0;

				if(!empty($_POST['targetbot'])) {
					$targetbot = intval($_POST['targetbot']);
				}

				if($unban) {
					$reason = "Unban by " . $username_clean . ": " . $_POST['reason'];
				} else {
					$reason = $_POST['reason'] . " (" . $_POST['category'] . ")";
				}

				$duration = intval($_POST['duration']) * 3600;
				$is_ip = isset($_POST['isip']); //whether the entered username is actually an IP address

				if(!empty($_POST['duration_num']) && !empty($_POST['duration_period'])) {
					$duration = $_POST['duration_num'] * $_POST['duration_period'] * 3600;
				}

				//maximum duration is a year
				if($duration <= 31104000) {
					$realms = array("uswest.battle.net", "useast.battle.net", "europe.battle.net", "asia.battle.net", "entconnect", "");

					//extract realm from username, if set
					$username_realm_parts = explode('@', $username);
					if(count($username_realm_parts) == 2) {
						$username = $username_realm_parts[0];
						$realm = $username_realm_parts[1];

						if($realm == 'west' || $realm == 'uswest') $realm = 'uswest.battle.net';
						else if($realm == 'east' || $realm == 'useast') $realm = 'useast.battle.net';
						else if($realm == 'euro' || $realm == 'europe') $realm = 'europe.battle.net';
						else if($realm == 'asia') $realm = 'asia.battle.net';
					} else if(count($usernames) > 1) { //if banning multiple users, we require them to use user-specific realm feature
						echo "<br />Error: banning multiple usernames but realm not set for [" . htmlspecialchars($username) . "]";
						continue;
					}

					if($realm != "") $realms = array($realm);

					if($unban) {
						adminLog("Unban", "Unbanning $username@$realm", $username_clean);
					} else {
						adminLog("Ban", "Banning $username@$realm", $username_clean);
					}

					foreach($realms as $realm_it) {
						if($realm_it == "garena") {
							$realm_it = "";
						}

						$where = "WHERE name = '$username' AND spoofedrealm = '$realm_it'";

						//unban the user if we're supposed to
						if($unban || $reban) {
							echo "<br /><b>Unbanning " . htmlspecialchars($username) . " on " . $realm_it . "</b>";

							$unban_reason = $reason;

							if($reban) {
								$unban_reason = "Force reban";
							}

							databaseQuery("UPDATE ban_history, bans SET unban_reason = ? WHERE bans.id = ban_history.banid AND ((bans.name = ? AND bans.server = ?) OR (bans.ip = ?))", array($unban_reason, $username, $realm_it, $username));
							databaseQuery("DELETE FROM bans WHERE name = ? AND server = ?", array($username, $realm_it));
							databaseQuery("UPDATE bans SET ip = '' WHERE ip = ?", array($username));

							if($unban) {
								continue;
							}
						}

						echo "<br /><b>Banning " . htmlspecialchars($username) . " on " . $realm_it . " for " . $duration . " seconds</b>";

						//make sure user isn't already banned
						$result = databaseQuery("SELECT COUNT(*) FROM bans WHERE name = ? AND server = ? AND context = 'ttr.cloud'", array($username, $realm_it));
						$row = $result->fetch();
						if($row[0] > 0) {
							echo "<br />Error: [" . htmlspecialchars($username) . "] already banned on [$realm_it]; unban first and try again if you want to make sure all IPs are banned";
							continue;
						}

						//last few IP addresses logged; limited to 15 addresses within the last 30 days
						$result = databaseQuery("SELECT DISTINCT ip FROM gameplayers LEFT JOIN games ON gameplayers.gameid = games.id $where AND datetime > DATE_SUB( NOW( ), INTERVAL 30 DAY) ORDER BY gameplayers.id DESC LIMIT 15");

						if($result->rowCount() > 0) {
							while($row = $result->fetch()) {
								$ip = $row[0];
								echo "<br />Banning " . $ip;
								$ban_realm = $realm_it;

								databaseQuery("INSERT INTO bans (botid, server, name, ip, date, gamename, admin, reason, expiredate, context, targetbot) VALUES ('0', ?, ?, ?, CURDATE(), '', ?, ?, DATE_ADD( NOW( ), INTERVAL ? second ), 'ttr.cloud', ?)", array($ban_realm, $username, $ip, $username_clean, $reason, $duration, $targetbot));
							}
						} else {
							//no previous games found; ban by username only if this is an actual realm
							if($realm_it != "" || $realm == "garena") {
								echo "<br />No IPs found on [$realm_it] just banning by username [" . htmlspecialchars($username) . "]";

								$ip = "";

								if($is_ip) {
									$ip = $username;
								}

								databaseQuery("INSERT INTO bans (botid, server, name, ip, date, gamename, admin, reason, expiredate, context, targetbot) VALUES ('0', ?, ?, ?, CURDATE(), '', ?, ?, DATE_ADD( NOW( ), INTERVAL ? second ), 'ttr.cloud', ?)", array($realm_it, $username, $ip, $username_clean, $reason, $duration, $targetbot));
							} else {
								echo "<br />No IPs found on [$realm_it], skipping because this is non-spoofchecked \"realm\"";
							}
						}
					}
				} else {
					echo "<p><b><i>Error: duration too long.</i></b></p>";
				}
			}
		} else {
			echo "<p><b><i>Error: please keep reason between 6 and 255 characters (make sure category is selected as well).</i></b></p>";
		}
	}
	?>

	<p><a href="./">back to index</a></p>
	</body>
	</html>

	<?
}
?>
