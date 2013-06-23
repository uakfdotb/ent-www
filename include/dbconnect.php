<?php

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
