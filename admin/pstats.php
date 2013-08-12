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
pstats provides functions relating to statistics.
Specifically, it enables clearing, transferring, and recovering player stats.
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
include("../include/const.php");
include("../include/stats.php");

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
	
	if($bigadmin && isset($_POST['action']) && isset($_POST['confirm']) && $_POST['confirm'] == "conftrue") {
		if($_POST['action'] == "transfer" && isset($_POST['category']) && isset($_POST['source_username']) && isset($_POST['source_realm']) && isset($_POST['target_username']) && isset($_POST['target_realm'])) {
			$message = statsTransfer($_POST['source_username'], $_POST['source_realm'], $_POST['target_username'], $_POST['target_realm'], $_POST['category'], $admin_name);
		} else if($_POST['action'] == "clear" && isset($_POST['username']) && isset($_POST['realm']) && isset($_POST['category'])) {
			$message = statsClear($_POST['username'], $_POST['realm'], $_POST['category'], $admin_name);
		} else if($_POST['action'] == "restore" && isset($_POST['username']) && isset($_POST['realm']) && isset($_POST['category']) && isset($_POST['stats'])) {
			$message = statsRestore($_POST['username'], $_POST['realm'], $_POST['category'], $_POST['stats'], $admin_name);
		}
		
		header('Location: pstats.php?message=' . urlencode($message));
	}
	
	?>
	
	<html>
	<head><title>ENT Gaming - Stats Manager</title></head>
	<body>
	<h1>Stats manager</h1>
	
	<? if($message != "") { ?>
	<p><b><i><?= htmlspecialchars($message) ?></i></b></p>
	<? } ?>
	
	<? if($bigadmin) { ?>
		<p>Clear or transfer stats.</p>
		<p><a href="./">Click here to return to index.</a></p>
	
		<h3>Transfer stats</h3>
	
		<form method="post" action="pstats.php">
		<input type="hidden" name="action" value="transfer" />
		Source username: <input type="text" name="source_username" />
		<br />Source realm: <select name="source_realm">
			<option value="useast.battle.net">USEast</option>
			<option value="uswest.battle.net">USWest</option>
			<option value="europe.battle.net">Europe</option>
			<option value="asia.battle.net">Asia</option>
			<option value="entconnect">ENT Connect</option>
			<option value="cloud.ghostclient.com">Ghost Client</option>
			<option value="">Not spoof checked</option>
			</select>
		<br />Target username: <input type="text" name="target_username" />
		<br />Target realm: <select name="target_realm">
			<option value="useast.battle.net">USEast</option>
			<option value="uswest.battle.net">USWest</option>
			<option value="europe.battle.net">Europe</option>
			<option value="asia.battle.net">Asia</option>
			<option value="entconnect">ENT Connect</option>
			<option value="cloud.ghostclient.com">Ghost Client</option>
			<option value="">Not spoof checked</option>
			</select>
		<br />Category: <select name="category">
			<? foreach($dotaCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			<? foreach($w3mmdCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			</select>
		<br /><input type="checkbox" name="confirm" value="conftrue" /> I know what I'm doing!
		<br /><input type="submit" value="Transfer player statistics" />
		</form>
	
		<h3>Clear stats</h3>
	
		<form method="post" action="pstats.php">
		<input type="hidden" name="action" value="clear" />
		Username: <input type="text" name="username" />
		<br />Realm: <select name="realm">
			<option value="useast.battle.net">USEast</option>
			<option value="uswest.battle.net">USWest</option>
			<option value="europe.battle.net">Europe</option>
			<option value="asia.battle.net">Asia</option>
			<option value="entconnect">ENT Connect</option>
			<option value="cloud.ghostclient.com">Ghost Client</option>
			<option value="">Not spoof checked</option>
			</select>
		<br />Category: <select name="category">
			<? foreach($dotaCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			<? foreach($w3mmdCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			</select>
		<br /><input type="checkbox" name="confirm" value="conftrue" /> I know what I'm doing!
		<br /><input type="submit" value="Clear player statistics" />
		</form>
		
		<h3>Restore stats</h3>
		
		<pre>DotA format: {score, games, wins, losses, kills, deaths, creepkills, creepdenies, assists, neutralkills, towerkills, raxkills, courierkills}
W3MMD format: {score, games, wins, losses, ... twelve more values that vary by category}</pre>
		
		<form method="post" action="pstats.php">
		<input type="hidden" name="action" value="restore" />
		Username: <input type="text" name="username" />
		<br />Realm: <select name="realm">
			<option value="useast.battle.net">USEast</option>
			<option value="uswest.battle.net">USWest</option>
			<option value="europe.battle.net">Europe</option>
			<option value="asia.battle.net">Asia</option>
			<option value="entconnect">ENT Connect</option>
			<option value="cloud.ghostclient.com">Ghost Client</option>
			<option value="">Not spoof checked</option>
			</select>
		<br />Stats string: <input type="text" name="stats" /> this is what you get in {} when you delete stats; ex: "{0, 1, 0}"
		<br />Category: <select name="category">
			<? foreach($dotaCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			<? foreach($w3mmdCategories as $i_cat => $i_name) { ?>
			<option value="<?= $i_cat ?>"><?= $i_name ?></option>
			<? } ?>
			</select>
		<br /><input type="checkbox" name="confirm" value="conftrue" /> I know what I'm doing!
		<br /><input type="submit" value="Restore player stats" />
		</form>
	<? } else { ?>
		<p>Error: you do not have access to this page!</p>
	<? } ?>
	
	</body>
	</html>
	
	<?
}
?>
