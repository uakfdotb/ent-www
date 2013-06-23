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
include("../include/ban.php");
include("../include/csrfguard.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else if(isset($_REQUEST['name']) && isset($_REQUEST['server'])) {
	include("../include/dbconnect.php");
	
	$username_clean = $user->data['username_clean'];
	
	//timezone stuff
	date_default_timezone_set(AUTOMATIC_DST_TIMEZONE);
	$timezoneAbbr = timezoneAbbr(AUTOMATIC_DST_TIMEZONE);
	?>
	
	<html>
	<head><title>ENT Ban - Ban History</title></head>
	<body>
	<h1>Ban history</h1>
	
	<?
	//ban history
	$result = databaseQuery("SELECT admin, reason, gamename, date, expiredate, unban_reason, banid FROM ban_history WHERE name = ? AND server = ? ORDER BY id DESC", array($_REQUEST['name'], $_REQUEST['server']));
	
	if($result->rowCount() > 0) {
		echo "<table cellpadding=\"2\">";
		echo "<tr><th>Admin</th><th>Reason</th><th>Gamename</th><th>Date</th><th>Expire</th><th>Unban</th></tr>";
		
		while($row = $result->fetch()) {
			echo "<tr>";
			echo "<td>" . htmlspecialchars($row[0]) . "</td>";
			echo "<td>" . niceReason($row[1]) . "</td>";
			echo "<td>" . htmlspecialchars($row[2]) . "</td>";
			echo "<td>" . uxtDate(convertTime($row[3])) . "</td>";
			echo "<td>" . uxtDate(convertTime($row[4])) . "</td>";
			echo "<td>" . niceUnbanReason($row[5], $row[6]) . "</td>";
			echo "</tr>";
		}
		
		echo "</table>";
	}
	?>
	
	<p><a href="./">back to index</a></p>
	</body>
	</html>
	
	<?
}
?>
