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
include($phpbb_root_path . 'common.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup();

if ($user->data['user_id'] == ANONYMOUS) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/common.php");
	include("../include/link.php");
	include("../include/dbconnect.php");
	
	$fuser = $user->data['username_clean'];
	linkInit($fuser);
	
	if(isset($_POST['action'])) {
		if($_POST['action'] == "delete" && isset($_POST['username']) && isset($_POST['realm'])) {
			databaseQuery("DELETE FROM validate WHERE fuser = ? AND buser = ? AND brealm = ?", array($fuser, $_REQUEST['username'], $_REQUEST['realm']));
		}
	}
	
	$array = listValidated($fuser);
	
	foreach($array as $account) {
	    $template->assign_block_vars('accounts', array(
									'ACCOUNT_NAME' => $account[0],
									'ACCOUNT_REALM' => $account[1],
									));
	}
	
	page_header('EntLink: accounts');
	$template->set_filenames(array('body' => 'link_accounts.html'));
	page_footer();
}

?>
