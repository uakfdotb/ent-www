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
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

page_header('Games list');
$isadmin = 'no';

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

	if(isadmin($user->data['user_id'])) {
		$isadmin = 'yes';
	}
}

$template->assign_vars(array('BOTS' => $bots));
$template->assign_vars(array('ISADMIN' => $isadmin));
$template->set_filenames(array(
                               'body' => 'games.html',
                               ));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>
