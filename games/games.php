<?php

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

page_header('Games list');

$bots = "";
if($user->data['user_id'] != ANONYMOUS) {
	include("../include/common.php");
	include("../include/dbconnect.php");
	
	$result = databaseQuery("SELECT bots FROM validate, gametrack WHERE validate.buser = gametrack.name AND validate.brealm = gametrack.realm AND validate.fuser = ?", array($user->data['username_clean']));
	$botArray = array();
	
	while($row = $result->fetch()) {
		$botSub = explode(",", $row[0]);
		
		foreach($botSub as $bot) {
			if(!empty($bot) && !in_array($bot, $botArray)) {
				$botArray[] = $bot;
			}
		}
	}
	
	$bots = implode(",", $botArray);
}

$template->assign_vars(array('BOTS' => $bots));
$template->set_filenames(array(
                               'body' => 'games.html',
                               ));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>
