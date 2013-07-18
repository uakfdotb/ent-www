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
Many admin actions, both in-game and through the website, are logged.
log.php will display these logged actions.
Filtering by admin name and other fields is supported.
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
	
	$n = 0;
	$filter = "";
	$parameters = array();
	
	if(isset($_REQUEST['n'])) {
		$n = intval($_REQUEST['n']);
	}
	
	if(isset($_REQUEST['filter'])) {
		$filterElement = "%" . $_REQUEST['filter'] . "%";
		$filter = "WHERE admin LIKE ? OR `desc` LIKE ? OR action LIKE ?";
		$parameters = array($filterElement, $filterElement, $filterElement);
	} else {
		$filter = "WHERE admin NOT LIKE '%stats-strip'";
	}
	
	$prev = $n - 1;
	if($prev < 0) $prev = 0;
	$next = $n + 1;
	
	$result = databaseQuery("SELECT action, `desc`, admin, time FROM admin_actions $filter ORDER BY id DESC LIMIT " . ($n * 50) . "," . ($n * 50 + 50), $parameters);
	
	//timezone stuff
	date_default_timezone_set(AUTOMATIC_DST_TIMEZONE);
	$timezoneAbbr = timezoneAbbr(AUTOMATIC_DST_TIMEZONE);
	?>
	
	<html>
	<head><title>ENT Gaming - Admin Log</title></head>
	<body>
	<h1>Admin log</h1>
	
	<p><a href="./">Click here to return to index.</a></p>
	
	<form method="get" action="log.php">
	<input type="text" name="filter" />
	<input type="submit" value="Filter" />
	</form>
	
	<table cellspacing="5">
	<tr>
		<td><a href="log.php?n=<?= $prev ?>&filter=<?= htmlentities($_REQUEST['filter']) ?>">&lt;</td>
		<td>Viewing log from <?= $n * 50 + 1 ?> to <?= $n * 50 + 50 ?></td>
		<td><a href="log.php?n=<?= $next ?>&filter=<?= htmlentities($_REQUEST['filter']) ?>">&gt;</td>
	</tr>
	<tr>
		<td colspan="3"></td>
	</tr>
	<tr>
		<th>Action</th>
		<th>Description</th>
		<th>Admin</th>
		<th>Time</th>
	</tr>
	
	<? while($row = $result->fetch()) { ?>
	<tr>
		<td><?= htmlspecialchars($row[0]) ?></td>
		<td><?= htmlspecialchars($row[1]) ?></td>
		<td><?= htmlspecialchars($row[2]) ?></td>
		<td><?= uxtDate(convertTime(htmlspecialchars($row[3]))) ?></td>
	</tr>
	<? } ?>
	
	</table>
	
	</body>
	</html>
	
	<?
}
?>
