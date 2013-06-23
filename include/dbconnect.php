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

// uncomment the line below if database is in maintenance
//die("Error: we are currently performing maintenance on the database. <a href=\"/\">Click here to return to the forum.</a>");

try {
	$database = new PDO('mysql:host=localhost;dbname=ghost', 'root', '', array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch(PDOException $ex) {
	die("Encountered database error: " . $ex->getMessage() . ". If this is unexpected, consider <a href=\"mailto:ent@entgaming.net\">reporting it to our web team</a>. Otherwise, <a href=\"/\">click here to return to the forum.</a>");
}

function databaseQuery($command, $array = array(), $assoc = false) {
	global $database;
	
	if(!is_array($array)) {
		die("Encountered database error: arguments array is not an array! If this is unexpected, consider <a href=\"mailto:ent@entgaming.net\">reporting it to our web team</a>. Otherwise, <a href=\"/\">click here to return to the forum.</a>");
	}
	
	try {
		$query = $database->prepare($command);
		
		if(!$query) {
			print_r($database->errorInfo());
			die("<br />Encountered database error 34 (see above). If this is unexpected, consider <a href=\"mailto:ent@entgaming.net\">reporting it to our web team</a>. Otherwise, <a href=\"/\">click here to return to the forum.</a>");
		}
		
		//set fetch mode depending on parameter
		if($assoc) {
			$query->setFetchMode(PDO::FETCH_ASSOC);
		} else {
			$query->setFetchMode(PDO::FETCH_NUM);
		}
		
		$success = $query->execute($array);
		
		if(!$success) {
			print_r($query->errorInfo());
			die("<br />Encountered database error 35 (see above). If this is unexpected, consider <a href=\"mailto:ent@entgaming.net\">reporting it to our web team</a>. Otherwise, <a href=\"/\">click here to return to the forum.</a>");
		}
		
		return $query;
	} catch(PDOException $ex) {
	die("Encountered database error: " . $ex->getMessage() . ". If this is unexpected, consider <a href=\"mailto:ent@entgaming.net\">reporting it to our web team</a>. Otherwise, <a href=\"/\">click here to return to the forum.</a>");
	}
}

?>
