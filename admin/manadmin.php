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
manadmin controls who has access to admin privileges on the host bots.
This only affects host bot permissions.
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
	$hugeadmin = ishugeadmin($user->data['user_id']);

	if(!$bigadmin) {
		header('Location: /');
		return;
	}

	if($hugeadmin && isset($_POST['action'])) {
		if($_POST['action'] == "delete" && isset($_POST['id'])) {
			$id = $_POST['id'];

			//get the admin name and realm
			$result = databaseQuery("SELECT name, server FROM admins WHERE id = ?", array($id));

			if($row = $result->fetch()) {
				adminLog("Delete admin", "Deleted admin {$row[0]}@{$row[1]}", $admin_name);
				databaseQuery("DELETE FROM admins WHERE id = ?", array($id));
			}
		} else if($_POST['action'] == "add" && isset($_POST['username']) && isset($_POST['realm'])) {
			$name = strtolower(trim($_POST['username']));
			$realm = $_POST['realm'];

			databaseQuery("INSERT INTO admins (botid, name, server) VALUES ('0', ?, ?)", array($name, $realm));
			adminLog('Add admin', "Added admin $name@$realm", $admin_name);
		} else if($_POST['action'] == "update" && isset($_POST['id']) && isset($_POST['comments'])) {
			$id = $_POST['id'];
			$comments = $_POST['comments'];

			//get the admin name and realm
			$result = databaseQuery("SELECT name, server, comments FROM admins WHERE id = ?", array($id));

			if($row = $result->fetch()) {
				adminLog("Updated admin", "Updated comments for admin {$row[0]}@{$row[1]}", $admin_name);
				databaseQuery("UPDATE admins SET comments = ? WHERE id = ?", array($comments, $id));
			}
		}

		header("Location: manadmin.php");
	}

	$result = databaseQuery("SELECT id, name, server, comments FROM admins ORDER BY id");

	?>

	<html>
	<head><title>ENT Gaming - Admin Manager</title></head>
	<body>
	<h1>Admin manager</h1>

	<? if($message != "") { ?>
	<p><b><i><?= htmlspecialchars($message) ?></i></b></p>
	<? } ?>

	<p>Manage bot admins using the form below. You'll have to modify group memberships via the forum administration control panel for website administrative access as well.</p>
	<p><a href="./">Click here to return to index.</a></p>

	<? if($hugeadmin) { ?>
	<form method="post" action="manadmin.php">
	<input type="hidden" name="action" value="add" />
	Username: <input type="text" name="username" />
	<br />Realm: <input type="text" name="realm" />
	<br /><input type="submit" value="Add bot admin" />
	</form>
	<? } ?>

	<table>
	<tr>
		<th>Name</th>
		<th>Server</th>
		<? if($hugeadmin) { ?>
		<th>Comments</th>
		<th>Delete</th>
		<? } ?>
	</tr>

	<? while($row = $result->fetch()) { ?>
	<tr>
		<td><?= htmlspecialchars($row[1]) ?></td>
		<td><?= htmlspecialchars($row[2]) ?></td>
		<? if($hugeadmin) { ?>
		<td>
			<form method="post" action="manadmin.php">
			<input type="text" name="comments" value="<?= htmlspecialchars($row[3]) ?>" />
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="id" value="<?= htmlspecialchars($row[0]) ?>" />
			<input type="submit" value="Update" />
			</form>
		</td>
		<td>
			<form method="post" action="manadmin.php">
			<input type="hidden" name="action" value="delete" />
			<input type="hidden" name="id" value="<?= htmlspecialchars($row[0]) ?>" />
			<input type="submit" value="Delete" />
			</form>
		</td>
		<? } ?>
	</tr>
	<? } ?>

	</table>

	</body>
	</html>

	<?
}
?>
