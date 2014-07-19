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

		if($location != "atlanta" && $location != "ny" && $location != "seattle" && $location != "europe" && $location != "la" && $location != "au" && $location != "jp") {
			$location = "atlanta";
		}

		$maptype = "map";

		if($map[0] == ":") {
			$map = substr($map, 1);
			$maptype = "load";
		}

		$observers = 0;

		if(isset($_REQUEST['observers'])) {
			$observers = 1;
		}

		//make sure no games like this already
		$result = databaseQuery("SELECT COUNT(*) FROM gamequeue WHERE username = ? OR gamename LIKE ?", array($owner, "{$fuser}__"));
		$row = $result->fetch();

		if($row[0] > 0) {
			$message = "Error hosting game: you appear to already have a game in the hosting queue. Please <a href=\"host.php\">go back to the hosting page</a> and check the queue to see the status of hosting your game.";
		} else {
			$result = databaseQuery("SELECT gamename FROM gamelist WHERE (ownername = ? OR gamename LIKE ?) AND lobby = 1", array($owner, "{$fuser}__"));

			if($row = $result->fetch()) {
				$message = "Error hosting game: you appear to already have a game hosted. Check the <a href=\"/forum/games.php\">game list</a> (the gamename is <b>" . htmlspecialchars($row[0]) . "</b>).";
			} else {
				databaseQuery("INSERT INTO gamequeue (realm, username, gamename, command, mapname, maptype, location, obs) VALUES ('1', ?, ?, ?, ?, ?, ?, ?)", array($owner, $gamename, $type, $map, $maptype, $location, $observers));
				$message = "The game should be hosted shortly (the gamename is your forum username followed by two digits; you can change this with !pub NEW GAMENAME after you join the game). If it doesn't host, make sure that you don't already have hosted and that the selected map is valid.<br /><br /><b>GAMENAME: $gamename</b><br /><br />Note: if there are too many games hosted, your host request may be queued. You can <a href=\"host.php\">check the queue status here</a>.";

				//if user hasn't hosted in thirty minutes, increment host counter
				if(time() - genericForumPreferencesGet($fuser, "link_host_time", 0) > 30 * 60) {
					databaseQuery("UPDATE makemehost_maps SET count = count + 1 WHERE randname = ?", array(substr($_REQUEST['map'], 1)));
				}

				//save the owner for future hosting
				genericForumPreferencesSet($fuser, "link_host_owner", $_REQUEST['owner']);

				//also updated last host time
				genericForumPreferencesSet($fuser, "link_host_time", time());
			}
		}
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

		//oh and the hosting queue for each location
		$locations = array("seattle", "europe", "atlanta", "ny", "la", "au", "jp");

		foreach($locations as $location) {
			$result = databaseQuery("SELECT username, gamename, position FROM gamequeue_status WHERE location = ? ORDER BY position", array($location));

			while($row = $result->fetch()) {
				$template->assign_block_vars("queue_$location", array(
											 'QUEUE_USER' => htmlspecialchars($row[0]),
											 'QUEUE_GAMENAME' => htmlspecialchars($row[1]),
											 'QUEUE_POSITION' => htmlspecialchars($row[2])
											));
			}
		}

		$template->set_filenames(array('body' => 'link_host.html'));
	}

	page_footer();
}

?>
