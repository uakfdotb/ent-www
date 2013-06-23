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
