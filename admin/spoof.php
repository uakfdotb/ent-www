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
The spoof feature enables players to get a spoof name if, for example, they win a tournament.
This can be colored and basically shows up differently to other players.
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
	
	$bigadmin = isbigadmin($user->data['user_id']);
	$admin_name = $user->data['username_clean'];
	$message = "";
	
	if(isset($_REQUEST['message'])) {
		$message = $_REQUEST['message'];
	}
	
	if($bigadmin && isset($_POST['action'])) {
		if($_POST['action'] == "add" && isset($_POST['name']) && isset($_POST['spoof'])) {
			$name = strtolower($_POST['name']);
			$spoof = $_POST['spoof'];
			
			//make sure name and spoof don't exist yet
			$result = databaseQuery("SELECT COUNT(*) FROM spoof WHERE spoof = ? OR name = ?", array($spoof, $name));
			$row = $result->fetch();
			
			if($row[0] == 0) {
				if(strlen($name) <= 40 && strlen($name) >= 3 && strlen($spoof) <= 15 && strlen($spoof) >= 3) {
					if(strpos($name, '@') !== false) {
						databaseQuery("INSERT INTO spoof (name, spoof) VALUES (?, ?)", array($name, $spoof));
						adminLog("Added spoof", "Added $spoof as spoof for $name", $admin_name);
					} else {
						$message = "The requested name does not contain the @realm.";
					}
				} else {
					$message = "The length of the name or the alias is either too short or too long.";
				}
			} else {
				$message = "The name or the alias already exists.";
			}
		} else if($_POST['action'] == "remove" && isset($_POST['name']) && isset($_POST['spoof'])) {
			$name = $_POST['name'];
			$spoof = $_POST['spoof'];
			databaseQuery("DELETE FROM spoof WHERE name = ? AND spoof = ?", array($name, $spoof));
			adminLog("Deleted spoof", "Deleted $spoof as spoof for $name", $admin_name);
		}
		
		header('Location: spoof.php?message=' . urlencode($message));
		return;
	}
	
	$result = databaseQuery("SELECT name, spoof FROM spoof ORDER BY name");
	
	?>
	
	<html>
	<head><title>Spoof manager</title></head>
	<body>
	<h1>Spoof manageranager</h1>
	
	<? if($message != "") { ?>
	<p><b><i><?= htmlspecialchars($message) ?></i></b></p>
	<? } ?>
	
	<p>You can manage spoof aliases here.</p>
	<p><a href="./">Click here to return to index.</a></p>
	
	<? if($bigadmin) { ?>
	<form method="post" action="spoof.php">
	<input type="hidden" name="action" value="add" />
	Account: <input type="text" name="name" /> format: username@realm
	<br />Alias name: <input type="text" name="spoof" />
	<br /><input type="submit" value="Add" />
	</form>
	<? } ?>
	
	<table>
	<tr>
		<th>Account</th>
		<th>Alias</th>
		<? if($bigadmin) { ?>
		<th>Delete</th>
		<? } ?>
	</tr>
	
	<?  while($row = $result->fetch()) {
		$name = htmlspecialchars($row[0]);
		$spoof = htmlspecialchars($row[1]); ?>
		<tr>
		<td><?= $name ?></td>
		<td><?= $spoof ?></td>
		<? if($bigadmin) { ?>
		<td>
			<form method="post" action="spoof.php">
			<input type="hidden" name="name" value="<?= $name ?>" />
			<input type="hidden" name="spoof" value="<?= $spoof ?>" />
			<input type="hidden" name="action" value="remove" />
			<input type="submit" value="Delete" />
		</td>
		<? } ?>
		</tr>
		</form>
	<? } ?>
	
	</table>
	</body>
	</html>
	
	<?
}
?>
