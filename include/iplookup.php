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

$whitelist = array('173.57.42.213');

function getPlayer($player) {
	$playerParts = explode('@', $player);
	
	if(count($playerParts) >= 2) {
		$name = strtolower($playerParts[0]);
		$realm = strtolower($playerParts[1]);
		
		if($realm == "uswest" || $realm == "west") {
			$realm = "uswest.battle.net";
		} else if($realm == "useast" || $realm == "east") {
			$realm = "useast.battle.net";
		} else if($realm == "europe") {
			$realm = "europe.battle.net";
		} else if($realm == "asia") {
			$realm = "asia.battle.net";
		}
		
		return array($name, $realm);
	} else {
		return array($player, "*");
	}
}

function isWhitelist($ip) {
	return in_array($ip, $GLOBALS['whitelist']);
}

function iplookup($name, $realm, $hours = 336) {
	$hours = intval($hours);
	
	$whereRealm = "";
	$whereArray = array();
	
	if($realm != "*") {
		$whereRealm = "AND spoofedrealm = ?";
		$whereArray = array($realm);
	}
	
	return databaseQuery("SELECT DISTINCT ip FROM gameplayers LEFT JOIN games ON games.id = gameplayers.gameid WHERE name = ? $whereRealm AND games.datetime > DATE_SUB( NOW( ), INTERVAL ? HOUR) AND ip != '0.0.0.0' AND ip != '127.0.0.1'", array_merge(array($name), $whereArray, array($hours)));
}

function simname($name, $hours = 336) {
	$name = str_replace("*", "%", $name);
	$hours = intval($hours);
	
	return databaseQuery("SELECT DISTINCT name, spoofedrealm FROM gameplayers LEFT JOIN games ON games.id = gameplayers.gameid WHERE name LIKE ? AND games.datetime > DATE_SUB( NOW( ), INTERVAL ? HOUR) LIMIT 150", array($name, $hours));
}

function namelookup($ip) {
	if(substr($ip, -1) == ".") {
		$parts = explode(".", $ip);
		$safe_ip = "";
		$counter = 0;
	
		foreach($parts as $part) {
			if(trim($part) != '') {
				$safe_ip .= intval($part) . ".";
				$counter++;
			}
		}
		
		if($counter >= 2) {
			return databaseQuery("SELECT DISTINCT name, spoofedrealm FROM gameplayers WHERE ip LIKE ? LIMIT 40", array("$safe_ip%"));
		}
	}
	
	return databaseQuery("SELECT DISTINCT name, spoofedrealm FROM gameplayers WHERE ip = ?", array($ip));
}

function alias($name, $realm, $depth = 1, &$array, $hours = 720, &$iparray = array()) {
	if($depth > 3) return;
	
	//set the parameter player as seen
	$array[$name . '@' . $realm] = true;
	
	//decrement depth
	$depth--;
	
	//find used IP addresses
	$used_ips = iplookup($name, $realm, $hours);
	
	while($row = $used_ips->fetch()) {
		if(!isset($iparray[$row[0]])) {
			$iparray[$row[0]] = true;
			
			$names = namelookup($row[0]);
			
			while($row2 = $names->fetch()) {
				$player = $row2[0] . '@' . $row2[1];
				
				if(!isset($array[$player])) {
					$array[$player] = true;
					
					if($depth > 0) {
						alias($row2[0], $row2[1], $depth, $array, $hours, $iparray);
					}
				}
			}
		}
	}
}

function lastTimePlayed($name) {
	$result = databaseQuery("SELECT MAX(games.datetime) FROM gameplayers LEFT JOIN games ON gameplayers.gameid = games.id WHERE gameplayers.name = ?", array($name));
	$row = $result->fetch();
	
	if(is_null($row[0])) return "Never";
	else return $row[0];
}

function countBans($name, $realm) {
	$result = databaseQuery("SELECT COUNT(*) FROM ban_history WHERE name = ? AND server = ?", array($name, $realm));
	$row = $result->fetch();
	return $row[0];
}

function countGames($name, $realm) {
	$result = databaseQuery("SELECT num_games FROM gametrack WHERE name = ? AND realm = ?", array($name, $realm));
	if($row = $result->fetch()) {
		return $row[0];
	} else {
		return 0;
	}
}

function isBanned($name, $realm) {
	$result = databaseQuery("SELECT COUNT(*) FROM bans WHERE name = ? AND server = ? AND context = 'ttr.cloud'", array($name, $realm));
	$row = $result->fetch();
	return $row[0] == 0 ? "No" : "Yes";
}

?>
