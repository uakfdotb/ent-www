<?php

function isadmin($user_id) {
	return group_memberships(4, $user_id) != false || group_memberships(5, $user_id) != false || group_memberships(8, $user_id) != false;
}

function isbigadmin($user_id) {
	return group_memberships(4, $user_id) != false || group_memberships(5, $user_id) != false || group_memberships(13, $user_id) != false;
}

function ishugeadmin($user_id) {
	return group_memberships(5, $user_id) != false || group_memberships(13, $user_id) != false;
}

function string_begins_with($string, $search)
{
	return (strncmp($string, $search, strlen($search)) == 0);
}

function boolToString($bool) {
	return $bool ? 'true' : 'false';
}

function escape($str) {
	die("Database error: attempted to use old database quoting method. If this error is unexpected, please <a href=\"mailto:ent@entgaming.net\">contact our web team</a>.");
}

function escapePHP($str) {
	return addslashes($str);
}

function chash($str) {
	return hash('sha512', $str);
}

function likeEscape($s, $e) {
    return str_replace(array($e, '_', '%'), array($e.$e, $e.'_', $e.'%'), $s);
}

function includePath() {
	$self = __FILE__;
	$lastSlash = strrpos($self, "/");
	return substr($self, 0, $lastSlash + 1);
}

function convertTime($time) {
	$fromTimezone = "America/Chicago";
	return strtotime($time . " " . $fromTimezone);
}

function uxtDate($time = -1) {
	if($time == -1) {
		$time = time();
	}
	
	return date("j M Y H:i:s T", $time);
}

function timezoneAbbr($timezone) {
	$dateTime = new DateTime();
	$dateTime->setTimeZone(new DateTimeZone($timezone));
	return $dateTime->format('T'); 
}

function dayDate($time = -1) {
	if($time == -1) {
		$time = time();
	}
	
	return date("j M Y", $time);
}

function uid($length) {
	$characters = "0123456789abcdefghijklmnopqrstuvwxyz";
	$string = "";	

	for ($p = 0; $p < $length; $p++) {
		$string .= $characters[mt_rand(0, strlen($characters) - 1)];
	}

	return $string;
}

?>
