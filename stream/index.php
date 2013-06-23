<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);
 
// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
 
if ($user->data['user_id'] == ANONYMOUS) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include('../include/common.php');
	include('../include/dbconnect.php');
	
	if(isset($_REQUEST['filter'])) {
		$filter = $_REQUEST['filter'];
		
		//filter by gamename/username
		$result = databaseQuery("SELECT gamename FROM stream_games WHERE gamename = ? UNION SELECT gamename FROM stream_players WHERE name = ?", array($filter, $filter));
		
		while($row = $result->fetch()) {
			$template->assign_block_vars('games', array(
										'GAMENAME' => htmlspecialchars($row[0]),
										'GAMENAME_URL' => htmlspecialchars(urlencode($row[0]))
										));
		}
	}
	
	page_header('ENT Stream');
	$template->set_filenames(array('body' => 'stream_index.html'));
	page_footer();
}
?>
