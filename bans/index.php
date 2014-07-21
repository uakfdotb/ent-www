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

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	$username_clean = $user->data['username_clean'];
	?>

	<html>
	<head><title>ENT Gaming Ban Manager</title></head>
	<body>
	<h1>ENT Gaming Ban Management</h1>
	<p>Hi, <?= $username_clean ?>. This page allows you to manage bans on ENT Gaming bots. Select an option from the links below to get started.</p>
	<p>Note that before banning anyone, you should be sure about both that the user violated a ENT Gaming rule and the appropriate punishment to be given to the user. If you are not completely sure about either, which <b>should be the case</b> at least on your first twenty bans or so, then you should ask another game moderator. If no one is online in the chat or on battle.net, then you can post a new topic in the <a href="/forum/viewforum.php?f=26">Ban Discussion</a> subsection of the Administration board. Only moderators will be able to view this board.</p>

	<h3>Primary ban tools</h3>
	<ul>
	<li><a href="search.php">Search a user by username</a></li>
	<li><a href="ban.php">Ban or unban a user</a></li>
	<li><a href="bans.php">List bans</a></li>
	<li><a href="games.php">Find a game and view its details</a></li>
	</ul>

	<h3>Secondary ban tools</h3>
	<ul>
	<li><a href="whitelist.php">Whitelist on ENT Connect</a></li>
	<li><a href="rangeban.php">Range ban</a></li>
	<li><a href="alias.php">Find player aliases</a></li>
	<li><a href="namelookup.php">Find players who have used given IP address</a></li>
	<li><a href="iplookup.php">Find IP addresses that a player has used</a></li>
	<li><a href="simname.php">Search for names by wildcard</a></li>
	<li><a href="hostlookup.php">Search for/by hostname</a></li>
	<li><a href="together.php">Find frequently played with</a></li>
	</ul>

	<h3>Other links</h3>
	<ul>
	<li><a href="/admin">Administration tools section</a></li>
	<li><a href="/status">Bot status</a></li>
	</ul>

	</body>
	</html>

	<?
}
?>
