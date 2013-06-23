<?php
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
include("../include/dbconnect.php");

$isadmin = group_memberships(10, $user->data['user_id']) != false;
$username_clean = $user->data['username_clean'];

if($isadmin) {
	if(isset($_REQUEST['vouch'])) {
		$playername = trim($_REQUEST['vouch']);
	
		//don't vouch twice
		$result = databaseQuery("SELECT COUNT(*) FROM league_list WHERE category = 'eihl' AND name = ?", array($playername));
		$row = $result->fetch();
	
		if($row[0] == 0) {
			databaseQuery("INSERT INTO league_list (category, name, server, voucher, datetime) VALUES ('eihl', ?, 'useast.battle.net', ?, NOW())", array($playername, $username_clean));
		}
	} else if(isset($_REQUEST['unvouch'])) {
		$playername = $_REQUEST['unvouch'];
		databaseQuery("DELETE FROM league_list WHERE category = 'eihl' AND name = ?", array($playername));
	
		$fh = fopen('unvouch.log', 'a');
		fwrite($fh, "$username_clean unvouched $playername on " . date(DATE_COOKIE) . "\n");
		fclose($fh);
	}
}

$result = databaseQuery("SELECT name, voucher, datetime FROM league_list WHERE category = 'eihl' ORDER BY name");

?>
<html>
<head><title>EIHL Management</title></head>
<body>
<h1>EIHL Management</h1>

<p>If you were looking for more information on EIHL, you are in the wrong place. Instead, go to <a href="/ihl">http://entgaming.net/ihl</a>.</p>

<? if($isadmin) { ?>
	<form action="index.php" method="get">
	<input type="text" name="vouch"> <input type="submit" value="Vouch player">
	</form>
<? } ?>

<table>
<tr>
	<th>Username</th>
	<th>Voucher</th>
	<? if($isadmin) { ?><th>Unvouch</th><? } ?>
	<th>Date vouched</th>
</tr>

<? while($row = $result->fetch()) { ?>
	<tr>
	<td><?= $row[0] ?></td>
	<td><?= $row[1] ?></td>
	
	<? if($isadmin) { ?>
		<td><a href="index.php?unvouch=<?= $row[0] ?>">unvouch</a></td>
	<? } ?>
	
	<td><?= $row[2] ?></td>
	</tr>
<?
}
?>
