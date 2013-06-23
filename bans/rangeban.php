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
include("../include/csrfguard.php");
include("../include/admin.php");

if ($user->data['user_id'] == ANONYMOUS || !isbigadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");

	$username_clean = $user->data['username_clean'];
	?>

	<html>
	<head><title>ENT Ban - Range ban</title></head>
	<body>
	<h1>Range ban</h1>

	<p>Bans IP range, only last digit. You must enter first three or four digits (four means single IP ban)! Will be a one-year ban.</p>

	<form method="POST" action="rangeban.php">
	IP: <input type="text" name="ip" /> example: "4.3.2." to ban 4.3.2.*, or "4.3.2.1" to ban a single IP
	<br />Reason: <input type="text" name="reason" />
	<br /><input type="checkbox" name="unban" value="unban" /> Unban user (if not checked, user will be banned)
	<br /><input type="submit" value="Ban user" />
	</form>

	<?

	if(isset($_POST['ip']) && isset($_POST['reason'])) {
		$unban = false;

		if(isset($_POST['unban']) && $_POST['unban'] == 'unban') $unban = true;

		$ip = $_POST['ip'];
		$reason = $_POST['reason'];

		if($reason == "") $reason = "Ban dodging";

		$realm = "asia.battle.net";
		adminLog("Range ban", "Range ban on $ip", $username_clean);

		echo "Banning/unbanning $ip* on $realm<br />";

		//unban the user if we're supposed to
		if($unban) {
			databaseQuery("DELETE FROM bans WHERE ip = ? AND name = 'rangeban'", array(":$ip"));
		} else {
			databaseQuery("INSERT INTO bans (botid, server, name, ip, date, gamename, admin, reason, expiredate, context) VALUES ('0', ?, 'rangeban', ?, CURDATE(), '', ?, ?, DATE_ADD( NOW( ), INTERVAL 1 year ), 'ttr.cloud')", array($realm, ":$ip", $username_clean, $reason));
		}
	}
	?>

	<p><a href="./">back to index</a></p>
	</body>
	</html>

	<?
}
?>
