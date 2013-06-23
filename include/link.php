<?php

//validates the forum account as ENT Connect
function linkInit($fuser) {
	if(!isValidated($fuser, "entconnect")) {
		addValidate($fuser, "entconnect", $fuser, true);
	}
}

//returns forum account on success, or false if no validated account found
function isValidated($username, $realm) {
	$result = databaseQuery("SELECT fuser FROM validate WHERE buser = ? AND brealm = ? AND `key` = ''", array($username, $realm));

	if($row = $result->fetch()) {
		return $row[0];
	} else {
		return false;
	}
}

//checks if the battle.net account is currently being validated on the forum account
//returns the key on success, or false if not found
function isValidating($username, $realm, $fuser) {
	$result = databaseQuery("SELECT `key` FROM validate WHERE buser = ? AND brealm = ? AND fuser = ?", array($username, $realm, $fuser));

	if($row = $result->fetch()) {
		return $row[0];
	} else {
		return false;
	}
}

//adds account and returns key
function addValidate($username, $realm, $fuser, $force = false) {
	$key = uid(7);
	
	if($force) {
		$key = "";
	}

	databaseQuery("INSERT INTO validate (`key`, buser, brealm, fuser) VALUES (?, ?, ?, ?)", array($key, $username, $realm, $fuser));
	return $key;
}

//returns array of (battle.net username, battle.net realm)
function listValidated($fuser) {
	$result = databaseQuery("SELECT buser, brealm FROM validate WHERE `key` = '' AND fuser = ?", array($fuser));
	$array = array();

	while($row = $result->fetch()) {
		$array[] = array($row[0], $row[1]);
	}

	return $array;
}

//loads the list of usernames who have uploaded a map
function loadMapUploaderNames() {
	if(!isset($GLOBALS['map_uploaders_loaded'])) {
		$GLOBALS['map_uploaders_loaded'] = true;

		$result = databaseQuery("SELECT DISTINCT user_id FROM makemehost_maps");
		$array = array();

		while($row = $result->fetch()) {
			$array[] = $row[0];
		}

		$usernames = array();
		if(($error = user_get_id_name($array, $usernames)) !== false) {
			die("Error while resolving usernames: $error");
		}

		$GLOBALS['map_uploaders'] = array();

		foreach($array as $i) {
			$GLOBALS['map_uploaders'][$i] = $usernames[$i];
		}
	}
}

function getMapUploaderName($user_id) {
	loadMapUploaderNames();

	if(isset($GLOBALS['map_uploaders'][$user_id])) {
		return $GLOBALS['map_uploaders'][$user_id];
	} else {
		return "Unknown";
	}
}

?>
