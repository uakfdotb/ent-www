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
	page_header('EntLink: validate');
	
	$message = false;
	
	if(isset($_REQUEST['username']) && isset($_REQUEST['realm'])) {
		if($_REQUEST['realm'] != 'useast' && $_REQUEST['realm'] != 'uswest' && $_REQUEST['realm'] != 'europe' && $_REQUEST['realm'] != 'asia') {
			$message = "Invalid realm";
		} else {
			$realm = $_REQUEST['realm'] . ".battle.net";
			
			$validateCheck = isValidated($_REQUEST['username'], $realm); //returns forum account if found
		
			if($validateCheck === false) {
				$validatingCheck = isValidating($_REQUEST['username'], $realm, $fuser); //returns key if found
			
				if($validatingCheck === false) {
					$key = addValidate($_REQUEST['username'], $realm, $fuser); //returns key
					$message = "Your account has been successfully added to the validation queue. In order to complete the validation process, you should login on " . $_REQUEST['username'] . " and whisper Clan.Enterprise with: <b><font face=\"Courier New\">\"!validate " . $key . "\"</font></b> (without the quotes). Once this step is complete, your account will be validated and will appear on the accounts added page.<br /><br />Note that the bot may not respond to you when you whisper the command; you will have to check the <a href=\"accounts.php\">accounts page</a> to see if validation was successful.";
				} else {
					$message = "You have already attempted to validate this account, but validation is not yet complete. You must login on " . $_REQUEST['username'] . " and whisper Clan.Enterprise with: <b><font face=\"Courier New\">\"!validate " . $validatingCheck . "\"</font></b> (without the quotes). Once this step is complete, your account will be validated and will appear on the accounts added page.<br /><br />Note that the bot may not respond to you when you whisper the command; you will have to check the <a href=\"accounts.php\">accounts page</a> to see if validation was successful.";
				}
			} else {
				$message = "Error: that battle.net account has already been validated on " . $validateCheck . ".";
			}
		}
	}
	
	if($message) {
		$template->set_filenames(array('body' => 'link_message.html'));
	} else {
		$template->set_filenames(array('body' => 'link_validate.html'));
	}
	
	page_footer();
}

?>
