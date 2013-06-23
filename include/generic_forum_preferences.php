<?php

function genericForumPreferencesGet($fuser, $key, $default) {
	$result = databaseQuery("SELECT v FROM generic_forum_preferences WHERE fuser = ? AND k = ?", array($fuser, $key));
	
	if($row = $result->fetch()) {
		return $row[0];
	} else {
		return $default;
	}
}

function genericForumPreferencesSet($fuser, $key, $value) {
	$result = databaseQuery("SELECT COUNT(*) FROM generic_forum_preferences WHERE fuser = ? AND k = ?", array($fuser, $key));
	$row = $result->fetch();
	
	if($row[0] == 0) {
		databaseQuery("INSERT INTO generic_forum_preferences (fuser, k, v) VALUES (?, ?, ?)", array($fuser, $key, $value));
	} else {
		databaseQuery("UPDATE generic_forum_preferences SET v = ? WHERE fuser = ? AND k = ?", array($value, $fuser, $key));
	}
}

?>
