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
	page_header('EntLink: add a map to host');

	if(isset($_REQUEST['map_id'])) {
		$map_id = $_REQUEST['map_id'];
		
		if($_REQUEST['action'] == "add") {
			$result = databaseQuery("SELECT user_id FROM makemehost_maps WHERE id = ?", array($map_id));

			if($row = $result->fetch() && $row[0] != $user->data['user_id']) {
				databaseQuery("INSERT INTO link_maps (fuser, mmh_map) VALUES (?, ?)", array($fuser, $map_id));
			}
		} else if($_REQUEST['action'] == "remove") {
			databaseQuery("DELETE FROM link_maps WHERE fuser = ? AND mmh_map = ?", array($fuser, $map_id));
		}
	}

	//get maps added from other users
	$result = databaseQuery("SELECT makemehost_maps.id, mapname, randname, user_id FROM link_maps, makemehost_maps WHERE link_maps.fuser = ? AND link_maps.mmh_map = makemehost_maps.id ORDER BY mapname", array($fuser));

	while($row = $result->fetch()) {
		$template->assign_block_vars('maps', array(
									'MAP_ID' => htmlspecialchars($row[0]),
									'MAP_NAME' => htmlspecialchars($row[1]),
									'MAP_USER' => htmlspecialchars(getMapUploaderName($row[3])),
									'MAP_LOAD' => htmlspecialchars($row[2])
									));
	}

	//put all other maps in a separate list
	$result = databaseQuery("SELECT makemehost_maps.id, mapname, randname, user_id FROM makemehost_maps WHERE makemehost_maps.id NOT IN (SELECT mmh_map FROM link_maps WHERE link_maps.fuser = ?) ORDER BY mapname", array($fuser));

	while($row = $result->fetch()) {
		$template->assign_block_vars('othermaps', array(
									'MAP_ID' => htmlspecialchars($row[0]),
									'MAP_NAME' => htmlspecialchars($row[1]),
									'MAP_USER' => htmlspecialchars(getMapUploaderName($row[3])),
									'MAP_LOAD' => htmlspecialchars($row[2])
									));
	}

	$template->set_filenames(array('body' => 'link_host_add.html'));

	page_footer();
}

?>
