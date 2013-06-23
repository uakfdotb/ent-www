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

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	?>

	<html>
	<head><title>ENT Gaming Admin</title></head>
	<body>
	<h1>Administration</h1>
	<p>Welcome to administration section.</p>

	<ul>
	<li><a href="spoof.php">Spoof (player special names)</a></li>
	<li><a href="announce.php">Announcements</a></li>
	<li><a href="validate.php">Validate lookup</a></li>
	<li><a href="pstats.php">Transfer/clear player stats</a></li>
	<li><a href="manadmin.php">Manage admins</a></li>
	<li><a href="games.php">Cool gamelist</a></li>
	<li><a href="log.php">Admin log</a></li>
	</ul>

	<p>Here are some links to other tools around the website.</p>

	<ul>
	<li><a href="/status/">Bot status</a></li>
	<li><a href="/tourney/">Tournament system</a> (<a href="/tourney/admin/">admin</a>)</li>
	<li><a href="/stats.php">Game/player overall stats</a></li>
	<li><a href="/statsbot.php">Individual bot stats</a></li>
	<li><a href="/forum/stats.php">Forum stats</a></li>
	<li><a href="/bans/">Ban manager</a></li>
	</ul>

	</body>
	</html>

	<?
}
?>
