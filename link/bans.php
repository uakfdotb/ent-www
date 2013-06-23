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
	include("../include/iplookup.php");
	include("../include/dbconnect.php");
	
	$fuser = $user->data['username_clean'];
	linkInit($fuser);
	
	$defaultAccount; //default validated account to ban on
	$array = listValidated($fuser);
	
	if(isset($_REQUEST['action']) && count($array) > 0) {
		if($_REQUEST['action'] == "Add ban" && isset($_REQUEST['ban_user']) && isset($_REQUEST['ban_realm']) && isset($_REQUEST['account']) && isset($_REQUEST['ban_reason'])) {
			$ban_user = $_REQUEST['ban_user'];
			$ban_realm = $_REQUEST['ban_realm'];
			$ban_reason = $_REQUEST['ban_reason'];
			
			$pinfo = getPlayer($_REQUEST['account']);
			$account_user = $pinfo[0];
			$account_realm = $pinfo[1];
			
			$fail = true; //make sure account is validated
			
			foreach($array as $account) {
				if(strtolower($account[0]) == $account_user && strtolower($account[1]) == $account_realm) {
					$fail = false;
				}
			}
			
			if(!$fail) {
				$result = databaseQuery("SELECT COUNT(*) FROM bans WHERE name = ? AND server = ? AND context = ?", array($ban_name, $ban_realm, "$account_user@$account_realm"));
				$row = $result->fetch();
				
				if($row[0] == 0) {
					databaseQuery("INSERT INTO bans (botid, server, name, ip, date, gamename, admin, reason, context, expiredate) VALUES ('0', ?, ?, '', NOW(), '', ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 YEAR))", array($ban_realm, $ban_user, $account_user,$ban_reason, "$account_user@$account_realm"));
				}
			}
		} else if($_REQUEST['action'] == "delban" && isset($_REQUEST['id'])) {
			$id = $_REQUEST['id'];
			$result = databaseQuery("SELECT context FROM bans WHERE id = ?", array($id));
			
			if($row = $result->fetch()) {
				if(!empty($row[0]) && $row[0] != "ttr.cloud") {
					$pinfo = getPlayer($row[0]);
			
					$fail = true; //make sure account is validated
				
					foreach($array as $account) {
						if(strtolower($account[0]) == $pinfo[0] && strtolower($account[1]) == $pinfo[1]) {
							$fail = false;
						}
					}
				
					if(!$fail) {
						databaseQuery("DELETE FROM bans WHERE id = ?", array($id));
					}
				}
			}
		} else if($_REQUEST['action'] == "Update bans to use this account" && isset($_REQUEST['account'])) {
			$pinfo = getPlayer($_REQUEST['account']);
			$account_user = $pinfo[0];
			$account_realm = $pinfo[1];
			
			$fail = true; //make sure account is validated
			
			foreach($array as $account) {
				if(strtolower($account[0]) == $account_user && strtolower($account[1]) == $account_realm) {
					$fail = false;
				}
			}
			
			if(!$fail) {
				foreach($array as $account) {
					$i_user = $account[0];
					$i_realm = $account[1];
					
					databaseQuery("UPDATE bans SET context = ? WHERE context = ?", array("$account_user@$account_realm", "$i_user@$i_realm"));
					
					if(!empty($i_user) && $i_user != 'ttr.cloud') {
						databaseQuery("UPDATE bans SET context = ? WHERE context = ?", array("$account_user@$account_realm", $i_user));
					}
				}
			}
		}
	}
	
	foreach($array as $account) {
		$account_name = $account[0];
		$account_realm = $account[1];
		
		$banResult = databaseQuery("SELECT id, name, server, reason, context FROM bans WHERE context = ? OR context = ?", array("$account_name@$account_realm", $account_name));
		
		while($banRow = $banResult->fetch()) {
			$template->assign_block_vars('bans', array(
										'BAN_ID' => $banRow[0],
										'BAN_NAME' => htmlentities($banRow[1]),
										'BAN_REALM' => htmlentities($banRow[2]),
										'BAN_REASON' => htmlentities($banRow[3]),
										'BAN_CONTEXT' => htmlentities($banRow[4])
										));

			$defaultAccount = $account_name . "@" . $account_realm;
		}
	}
	
	//send default account and validated accounts to template
	foreach($array as $account) {
		$template->assign_block_vars('accounts', array('ACCOUNT_NAME' => htmlentities($account[0]), 'ACCOUNT_REALM' => htmlentities($account[1]), 'ACCOUNT_COMBINED' => $account[0] . '@' . $account[1]));
	}
	
	$template->assign_var('DEFAULTACCOUNT', $defaultAccount);
	
	page_header('EntLink: personal bans');
	$template->set_filenames(array('body' => 'link_bans.html'));
	page_footer();
}

?>
