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
	
	page_header('EntLink');
	$template->set_filenames(array('body' => 'link_index.html'));
	page_footer();
}

?>
