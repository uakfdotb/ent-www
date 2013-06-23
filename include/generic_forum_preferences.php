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
