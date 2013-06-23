<?php
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
