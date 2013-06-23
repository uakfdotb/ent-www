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

/*
Provides admin tools relating to Battle.net account validation.
Allows finding forum user from linked account or vice versa.
Also allows manual account validation.
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
	$admin_name = $user->data['username_clean'];
	$bigadmin = isbigadmin($user->data['user_id']);
	$message = "";
	
	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}
	
	if(isset($_POST['action'])) {
		if($_POST['action'] == "checkbyb" && isset($_POST['buser']) && isset($_POST['brealm'])) {
			$buser = trim($_POST['buser']);
			$brealm = trim($_POST['brealm']);
			$result = databaseQuery("SELECT fuser FROM validate WHERE buser = ? AND brealm = ? AND `key` = ''", array($buser, $brealm));
			
			if($row = $result->fetch()) {
				$message = "Forum user found: " . $row[0];
			} else {
				$message = "Error: no forum user found with that Battle.net account.";
			}
		} else if($_POST['action'] == "checkbyf" && isset($_POST['fuser'])) {
			$fuser = $_POST['fuser'];
			$result = databaseQuery("SELECT buser, brealm FROM validate WHERE fuser = ? AND `key` = ''", array($fuser));
			
			while($row = $result->fetch()) {
				if($message != "") {
					$message .= ", ";
				}
				
				$message .= $row[0] . "@" . $row[1];
			}
			
			if($message == "") {
				$message = "none";
			}
			
			$message = "Found accounts: " . $message;
		} else if($_POST['action'] == "validate" && isset($_POST['fuser']) && isset($_POST['buser']) && isset($_POST['brealm']) && $bigadmin) {
			$fuser = trim($_POST['fuser']);
			$buser = trim($_POST['buser']);
			$brealm = trim($_POST['brealm']);
			
			if(!isset($_POST['unvalidate'])) {
				//make sure not already validated
				$result = databaseQuery("SELECT fuser FROM validate WHERE buser = ? AND brealm = ? AND `key` = ''", array($buser, $brealm));
			
				if($row = $result->fetch()) {
					$message = "Error: $buser@$brealm already validated by {$row[0]}.";
				} else {
					databaseQuery("DELETE FROM validate WHERE buser = ? AND brealm = ?", array($buser, $brealm));
					databaseQuery("INSERT INTO validate (fuser, buser, brealm, `key`) VALUES (?, ?, ?, '')", array($fuser, $buser, $brealm));
					$message = "The $buser@$brealm account has been validated under $fuser successfully.";
				}
			} else {
				databaseQuery("DELETE FROM validate WHERE buser = ? AND brealm = ?", array($buser, $brealm));
				$message = "Unvalidated the account.";
			}
		}
		
		header('Location: validate.php?message=' . urlencode($message));
		return;
	}
	
	$result = databaseQuery("SELECT botid, message FROM announcements ORDER BY message, botid");
	$array = array();
	
	while($row = $result->fetch()) {
		if(!array_key_exists($row[1], $array)) {
			$array[$row[1]] = array();
		}
		
		$array[$row[1]][] = $row[0];
	}
	
	$result = databaseQuery("SELECT v1.buser, v1.brealm FROM validate AS v1 LEFT JOIN validate AS v2 ON v1.buser = v2.buser AND v1.brealm = v2.brealm AND v1.fuser != v2.fuser WHERE v1.`key` = '' AND v2.`key` = ''");
	$problematic = array();
	
	while($row = $result->fetch()) {
		$problematic[] = array($row[0], $row[1]);
	}
	
	?>
	
	<html>
	<head><title>ENT Gaming - Validate Lookup</title></head>
	<body>
	<h1>Validate lookup</h1>
	
	<? if($message != "") { ?>
	<p><b><i><?= htmlspecialchars($message) ?></i></b></p>
	<? } ?>
	
	<p>You can either lookup for the Battle.net account from forum account or the forum account from Battle.net account.</p>
	<p><a href="./">Click here to return to index.</a></p>
	
	<form method="post" action="validate.php">
	<input type="hidden" name="action" value="checkbyf" />
	Forum username: <input type="text" name="fuser" />
	<input type="submit" value="Get Battle.net account" />
	</form>
	
	<form method="post" action="validate.php">
	<input type="hidden" name="action" value="checkbyb" />
	Battle.net account: <input type="text" name="buser" />
	<select name="brealm">
		<option value="useast.battle.net">USEast</option>
		<option value="uswest.battle.net">USWest</option>
		<option value="europe.battle.net">Europe</option>
		<option value="asia.battle.net">Asia</option>
	</select>
	<input type="Submit" value="Get forum account" />
	</form>
	
	
	<? if($bigadmin) { ?>
	<h3>Validate an account</h3>
	
	<form method="post" action="validate.php">
	<input type="hidden" name="action" value="validate" />
	Forum username: <input type="text" name="fuser" />
	<br />Battle.net username: <input type="text" name="buser" />
	<br />Battle.net realm: <select name="brealm">
		<option value="useast.battle.net">USEast</option>
		<option value="uswest.battle.net">USWest</option>
		<option value="europe.battle.net">Europe</option>
		<option value="asia.battle.net">Asia</option>
	</select>
	<br /><input type="checkbox" name="unvalidate" value="unvalidate" /> Unvalidate (if checked, will unvalidate the Battle.net account; in this case forum username is not required)
	<br /><input type="Submit" value="Validate" />
	</form>
	<? } ?>
	
	<h3>Problematic accounts</h3>
	
	<p>These are Battle.net accounts that have been registered under multiple forum accounts. Hopefully none show up below...</p>
	
	<table>
	<tr>
		<th>Username</th>
		<th>Realm</th>
	</tr>
	<? foreach($problematic as $problem) { ?>
	<tr>
		<td><?= $problem[0] ?></td>
		<td><?= $problem[1] ?></td>
	</tr>
	<? } ?>
	</table>
	
	</body>
	</html>
	
	<?
}
?>
