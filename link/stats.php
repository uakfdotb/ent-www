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
	include("../include/const.php");
	include("../include/iplookup.php"); //for the getPlayer function
	include("../include/admin.php"); //needed since stats.php logs admin actions
	include("../include/stats.php");
	include("../include/dbconnect.php");
	include("../include/generic_forum_preferences.php");

	$fuser = $user->data['username_clean'];
	page_header('EntLink: stats manager');

	$message = false;
	$array = listValidated($fuser);
	
	foreach($array as $account) {
	    $template->assign_block_vars('accounts', array(
									'ACCOUNT_NAME' => $account[0],
									'ACCOUNT_REALM' => $account[1],
									));
	}
	
	foreach($w3mmdCategories as $category => $catname) {
		$template->assign_block_vars('w3mmdcat', array('CATEGORY' => $category, 'CATNAME' => $catname));
	}
	
	foreach($dotaCategories as $category => $catname) {
		$template->assign_block_vars('dotacat', array('CATEGORY' => $category, 'CATNAME' => $catname));
	}

	if(isset($_POST['action'])) {
		if(check_form_key('link_stats')) { //CSRF protection
			if(time() - genericForumPreferencesGet($fuser, "link_stats_last_action", 0) >= 600) { //flood protection
				if($_POST['action'] == "transfer" && isset($_POST['source']) && isset($_POST['target']) && isset($_POST['category'])) {
					//check validation and no same source/target
					$source_info = getPlayer($_POST['source']);
					$target_info = getPlayer($_POST['target']);
					$isSourceValidated = isValidated($source_info[0], $source_info[1]);
					$isTargetValidated = isValidated($target_info[0], $target_info[1]);
			
					if(($source_info[0] != $target_info[0] || $source_info[1] != $target_info[1]) && $isSourceValidated !== false && $isSourceValidated == $fuser && $isTargetValidated !== false && $isTargetValidated == $fuser) {
						$message = statsTransfer($source_info[0], $source_info[1], $target_info[0], $target_info[1], $_POST['category'], $fuser, false);
					} else {
						$message = "Invalid source/target account. This incident has been reported to the ENT web administration team.";
					}
				} else if($_POST['action'] == "clear" && isset($_POST['account']) && isset($_POST['category'])) {
					$p_info = getPlayer($_POST['account']);
					$isValidated = isValidated($p_info[0], $p_info[1]);
			
					if($isValidated != false && $isValidated == $fuser) {
						$message = statsClear($p_info[0], $p_info[1], $_POST['category'], $fuser, false);
					} else {
						$message = "Invalid account specified. This incident has been reported to the ENT web administration team.";
					}
				}
				
				if($statsLastResult) {
					genericForumPreferencesSet($fuser, "link_stats_last_action", time());
				} else if(empty($message)) {
					$message = "Stats operation encountered unknown error.";
				}
			} else {
				$message = "Please wait longer between transferring / clearing stats.";
			}
		} else {
			$message = "CSRF failure. Try again, and don't submit the same form twice.";
		}
	}

	if(!empty($message)) {
		$message = htmlspecialchars($message) . " <a href=\"stats.php\">Click here to return to stats manager.</a>";
		$template->set_filenames(array('body' => 'link_message.html'));
	} else {
		add_form_key('link_stats');
		$template->set_filenames(array('body' => 'link_stats.html'));
	}

	page_footer();
}

?>
