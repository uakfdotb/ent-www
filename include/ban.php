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

function niceHostname($hostname) {
	$hostname = htmlspecialchars($hostname);
	
	if(strlen($hostname) > 34) {
		$hostname = substr($hostname, 0, 33) . "<b>...</b>";
	}
	
	return $hostname;
}

function niceReason($reason) {
	//process reason to change links to links and tid's to links as well
	$reason = htmlentities($reason);
	$lastLink = 0;

	for($i = 0; $i < 10; $i++) { //parse up to ten links in case we enter infinite loop somehow
		$linkStart = @strpos($reason, "http://", $lastLink);
	
		if($linkStart === false) {
			$linkStart = @strpos($reason, "https://", $lastLink);
			
			if($linkStart === false) {
				break;
			}
		}
	
		//switch up to next space
		$linkEnd = @strpos($reason, " ", $linkStart);
	
		if($linkEnd === false) {
			$linkEnd = strlen($reason);
		}
	
		$link = substr($reason, $linkStart, $linkEnd - $linkStart);
		$newLink = "<a href=\"$link\">$link</a>";
		$reason = substr($reason, 0, $linkStart) . $newLink . substr($reason, $linkEnd);
		$lastLink = $linkStart + strlen($newLink);
	}

	$lastLink = 0;

	for($i = 0; $i < 10; $i++) { //parse up to ten TID's in case we enter infinite loop somehow
		$linkStart = stripos($reason, "tid", $lastLink);
	
		if($linkStart === false) {
			break;
		}
	
		//skip the "tid" and seperator
		$linkStart += 4;
	
		if(strlen($reason) > $linkStart + 1 && $reason[$linkStart] == " ") {
			//increment if it's a space
			$linkStart++;
		}
	
		//switch up to next space
		$linkEnd = @strpos($reason, " ", $linkStart);
	
		if($linkEnd === false) {
			$linkEnd = strlen($reason);
		}
	
		//if length is less than two, probably not an actual TID
		if($linkEnd - $linkStart < 2) {
			$lastLink = $linkEnd;
			continue;
		}
	
		$link = substr($reason, $linkStart, $linkEnd - $linkStart);
		$newLink = "<a href=\"https://entgaming.net/forum/viewtopic.php?t=$link\">$link</a>";
		$reason = substr($reason, 0, $linkStart) . $newLink . substr($reason, $linkEnd);
		$lastLink = $linkStart + strlen($newLink);
	}
	
	return $reason;
}

function niceUnbanReason($reason, $banid) {
	if(!empty($reason)) {
		return niceReason($reason);
	} else {
		//check if the user is still banned, or if no unban reason was provided
		$result = databaseQuery("SELECT COUNT(*) FROM bans WHERE id = ?", array($banid));
		$row = $result->fetch();
		
		if($row[0] == 0) {
			return "Expired";
		} else {
			return "Still banned";
		}
	}
}

?>
