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
	include("../include/generic_forum_preferences.php");

	$fuser = $user->data['username_clean'];
	page_header('EntLink: host');

	$message = false;

	if(isset($_REQUEST['map']) && isset($_REQUEST['owner']) && isset($_REQUEST['location'])) {
		$map = $_REQUEST['map'];
		$owner = $_REQUEST['owner'];
		$location = $_REQUEST['location'];
		$gamename = $fuser . rand(10, 99);
		$type = "pub";
		
		if(isset($_REQUEST['private'])) {
			$type = "priv";
		}

		if($location != "atlanta" && $location != "chicago" && $location != "seattle" && $location != "europe") {
			$location = "atlanta";
		}

		$maptype = "map";

		if($map[0] == ":") {
			$map = substr($map, 1);
			$maptype = "load";
		}

		databaseQuery("INSERT INTO gamequeue (realm, username, gamename, command, mapname, maptype, location) VALUES ('1', ?, ?, ?, ?, ?, ?)", array($owner, $gamename, $type, $map, $maptype, $location));
		$message = "The game should be hosted shortly (the gamename is your forum username followed by two digits; you can change this with !pub NEW GAMENAME after you join the game). If it doesn't host, make sure that you don't already have hosted and that the selected map is valid.<br /><br /><b>GAMENAME: $gamename</b>";
		
		//save the owner for future hosting
		genericForumPreferencesSet($fuser, "link_host_owner", $_REQUEST['owner']);
	}

	if($message) {
		$template->set_filenames(array('body' => 'link_message.html'));
	} else {
		//first, get the user's own uploaded maps
		$result = databaseQuery("SELECT mapname, randname FROM makemehost_maps WHERE user_id = ? ORDER BY mapname", array($user->data['user_id']));

		while($row = $result->fetch()) {
			$template->assign_block_vars('maps', array(
										'MAP_NAME' => htmlspecialchars(":" . $row[1]),
										'MAP_DESC' => htmlspecialchars($row[0])
										));
		}

		//next, get the default map configuration files
		$template->assign_block_vars('maps', array(
									'MAP_NAME' => ":dota_ref.cfg",
									'MAP_DESC' => "DotA with referees"
									));

		//lastly, get maps added from other users
		$result = databaseQuery("SELECT mapname, randname, user_id FROM link_maps, makemehost_maps WHERE link_maps.fuser = ? AND link_maps.mmh_map = makemehost_maps.id ORDER BY mapname", array($fuser));

		while($row = $result->fetch()) {
			$template->assign_block_vars('maps', array(
										'MAP_NAME' => htmlspecialchars(":" . $row[1]),
										'MAP_DESC' => htmlspecialchars($row[0] . " (uploaded by " . getMapUploaderName($row[2]) . ")")
										));
		}
		
		//also get the default owner to use
		$owner = genericForumPreferencesGet($fuser, "link_host_owner", $fuser);
		$template->assign_var('OWNER', htmlspecialchars($owner));

		$template->set_filenames(array('body' => 'link_host.html'));
	}

	page_footer();
}

?>
