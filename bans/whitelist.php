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
include("../include/admin.php");
include("../include/csrfguard.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");

	$username_clean = $user->data['username_clean'];
	?>

	<html>
	<head><title>ENT Ban - Whitelist</title></head>
	<body>
	<h1>Whitelist</h1>

	<p>This is a list of whitelisted users on ENT Connect. They won't be subject to IP bans of other accounts. Add or remove a whitelisted user using the form below, or view the list of users.</p>

	<form method="POST" action="whitelist.php">
	Username: <input type="text" name="username" />
	<br /><input type="checkbox" name="remove" value="true" /> Remove user (if not checked, will attempt to whitelist user)
	<br /><input type="submit" value="Submit" />
	</form>

	<?

	if(isset($_POST['username']) && $_POST['username'] != '') {
		$remove = false;

		if(isset($_POST['remove']) && $_POST['remove'] == 'true') $remove = true;

		$username = trim(strtolower($_POST['username']));

		if($remove) {
			adminLog("Whitelist remove", "Removed $username from the whitelist", $username_clean);
			databaseQuery("DELETE FROM whitelist WHERE name = ?", array($username));
		} else {
			$result = databaseQuery("SELECT COUNT(*) FROM whitelist WHERE name = ?", array($username));
			$row = $result->fetch();

			if($row[0] == 0) {
				adminLog("Whitelist add", "Added $username to the whitelist", $username_clean);
				databaseQuery("INSERT INTO whitelist (name) VALUES (?)", array($username));
			}
		}
	}

	$result = databaseQuery("SELECT name FROM whitelist ORDER BY name");

	?>

	<table>
	<tr><th>Name</th></tr>

	<? while($row = $result->fetch()) { ?>
		<tr><td><?= htmlspecialchars($row[0]) ?></td></tr>
	<? } ?>

	</table>

	<p><a href="./">back to index</a></p>
	</body>
	</html>

	<?
}
?>
