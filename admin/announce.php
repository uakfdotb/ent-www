<?php

/*
announce will modify the five-minute announcements sent to bots.
It can also be used to send an announcement immediately.
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
	
	//whether they have access to modify the announcements
	$bigadmin = isbigadmin($user->data['user_id']);
	$admin_name = $user->data['username_clean'];
	
	if($bigadmin && isset($_POST['action'])) {
		if($_POST['action'] == "Save" && isset($_POST['oldmessage']) && isset($_POST['message']) && isset($_POST['bots']) && isset($_POST['oldbots'])) {
			$oldmessage = $_POST['oldmessage'];
			$message = $_POST['message'];
			$bots = explode(',', str_replace(' ', '', $_POST['bots']));
			$oldbots = explode(',', $_POST['oldbots']);
			
			foreach($bots as $bot) {
				//if the bot is new, add it
				if(!in_array($bot, $oldbots)) {
					databaseQuery("INSERT INTO announcements (botid, message) VALUES (?, ?)", array($bot, $message));
				}
				
				//if the bot is old but message is different now, update it
				else if($message != $oldmessage) {
					databaseQuery("UPDATE announcements SET message = ? WHERE message = ? AND botid = ?", array($message, $oldmessage, $bot));
				}
			}
			
			foreach($oldbots as $bot) {
				//if the bot was deleted, delete it..
				if(!in_array($bot, $bots)) {
					databaseQuery("DELETE FROM announcements WHERE message = ? AND botid = ?", array($oldmessage, $bot));
				}
			}
		} else if($_POST['action'] == "Delete" && isset($_POST['oldmessage']) && isset($_POST['oldbots'])) {
			$oldmessage = $_POST['oldmessage'];
			$oldbots = explode(',', $_POST['oldbots']);
			
			foreach($oldbots as $bot) {
				databaseQuery("DELETE FROM announcements WHERE message = ? AND botid = ?", array($oldmessage, $bot));
			}
		} else if($_POST['action'] == "add" && isset($_POST['message'])) {
			$message = $_POST['message'];
			databaseQuery("INSERT INTO announcements (botid, message) VALUES ('0', ?);", array($message));
		} else if($_POST['action'] == "announce" && isset($_POST['message']) && isset($_POST['botid'])) {
			$message = $_POST['message'];
			$botids = explode(',', $_POST['botid']);
			
			foreach($botids as $botid) {
				$botid = trim($botid);
				databaseQuery("INSERT INTO commands (botid, command) VALUES (?, ?)", array($botid, "!saygames $message"));
			}
			
			adminLog("Announcement", $message, $admin_name);
		}
		
		header('Location: announce.php');
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
	
	?>
	
	<html>
	<head><title>ENT Gaming Announcement Manager</title></head>
	<body>
	<h1>Announcement Manager</h1>
	<p>Add, edit, and remove announcements using the stuff below. Announcements run every fifteen minutes.</p>
	<p><a href="./">Click here to return to index.</a></p>
	
	<? if($bigadmin) { ?>
		<form method="post" action="announce.php">
		<input type="hidden" name="action" value="add" />
		Message: <textarea name="message" rows="5" cols="30"></textarea>
		<br /><input type="submit" value="Add (edit bots later)" />
		</form>
	<? } ?>
	
	<table>
	<tr>
		<th width="50%">Message</th>
		<th width="40%">Bots</th>
		<? if($bigadmin) { ?>
		<th width="10%">Save</th>
		<th width="10%">Delete</th>
		<? } ?>
	</tr>
	
	<? foreach($array as $message => $bots ) {
		$bots_string = implode(', ', $bots); ?>
		
		<? if($bigadmin) { ?>
		<form method="post" action="announce.php">
		<input type="hidden" name="oldmessage" value="<?= htmlspecialchars($message) ?>">
		<input type="hidden" name="oldbots" value="<?= htmlspecialchars($bots_string) ?>">
		<? } ?>
		
		<tr>
			<td><textarea name="message" style="width: 100%;"><?= htmlspecialchars($message) ?></textarea></td>
			<td><textarea name="bots" style="width: 100%;"><?= htmlspecialchars($bots_string) ?></textarea></td>
			<? if($bigadmin) { ?>
			<td><input type="submit" name="action" value="Save" /></td>
			<td><input type="submit" name="action" value="Delete" /></td>
			<? } ?>
		</tr>
		
		<? if($bigadmin) { ?>
		</form>
		<? } ?>
	<? } ?>
	
	</table>
	
	<? if($bigadmin) { ?>
	<p>Use this to announce a message now (there will be no confirmation on success/failure).</p>
	
	<form method="post" action="announce.php">
	<input type="hidden" name="action" value="announce" />
	Message: <textarea name="message" rows="5" cols="30"></textarea>
	<br />Bot id(s): <input type="text" name="botid" /> if multiple, separate with commas
	<br /><input type="submit" value="Announce now" />
	</form>
	<? } ?>
	
	</body>
	</html>
	
	<?
}
?>
