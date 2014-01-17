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
require($phpbb_root_path . 'includes/functions_user.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

include("../include/common.php");
include("../include/admin.php");
include("../include/csrfguard.php");
include("../include/botlocate.php");
include("../include/dbconnect.php");
include("../include/host.php");

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
	exit;
}

if(isset($_POST['action']) && $_POST['action'] == 'kick' && isset($_REQUEST['username'])) {
	$username = $_REQUEST['username'];
	databaseQuery("INSERT INTO commands (botid, command) VALUES (?, ?)", array(0, "!kick $username"));
	adminLog("Gamelist lobby kick", "Kicking $username from lobbies", $user->data['username_clean']);
	exit;
}

if(!isset($id) && !empty($_REQUEST['id'])) {
	$id = $_REQUEST['id'];
}

if(!isset($id) || $id == 0) {
	return;
} else {
	// strip non-digits from id
	$id = preg_replace("/[^0-9]/", "", $id);
	$id = intval($id);
}

$result = databaseQuery("SELECT botid, gamename, usernames, lobby, ownername, IFNULL(TIMESTAMPDIFF(SECOND, eventtime, NOW()), '-1') FROM gamelist WHERE id = ?", array($id));
if($row = $result->fetch()) {
	$botid = $row[0];
?>

	<h2>Gamename: <?= $row[1] ?>
	<br />Bot name: <?= getBotName($row[0]) ?>
	<? if($botid > 100 && !empty($row[4])) { ?>
		<br />Owner: <?= $row[4] ?>
	<? } ?>
	<? if($row[5] != '-1') { ?>
		<br />Duration: <?= sprintf('%02d:%02d:%02d', ($row[5]/3600),($row[5]/60%60), $row[5]%60) ?>
	<? } ?></h2>
	<table>
	<tr>
		<th class="games">Username</th>
		<th class="games">Realm</th>
		<th class="games">Ping</th>
		<th class="games">IP</th>
		<th class="games">Country</th>
	</tr>

<?
	$array = explode("\t", $row[2]);

	$counter = 0; //counter for non-empty slots

	for($i = 0; $i < count($array) - 3; $i+=4) {
		$array_index = $i;

		//if striped, the array index has to be specially calculated
		if($striped) {
			$slot_index = intval($i / 4); //this is the slot index, if the slots weren't striped!

			if($slot_index <= $cutoff) { //on first team
				$array_index = $slot_index * 8;
			} else { //on second team
				$array_index = ($slot_index - $cutoff) * 8 - 4;
			}
		}

		$username = $array[$array_index];
		$realm = $array[$array_index + 1];

		if($realm == "cloud.ghostclient.com") $realm = "GClient";
		else if($realm == "uswest.battle.net") $realm = "USWest";
		else if($realm == "useast.battle.net") $realm = "USEast";
		else if($realm == "europe.battle.net") $realm = "Europe";
		else if($realm == "asia.battle.net") $realm = "Asia";

		$ping = $array[$array_index + 2];
		$ip = $array[$array_index + 3];
		$host = htmlspecialchars(getHost($ip));
		$country = htmlspecialchars(@geoip_country_name_by_name($ip));

		if($username == "") {
			echo "<tr><td colspan=\"3\" class=\"slot\">Empty</td></tr>";
		} else {
			$safe_username = htmlentities($username);
			$safer_username = json_encode($safe_username);
			echo "<tr><td class=\"slot\" id=\"$safer_username\"><a href=\"javascript:actionDialog('$safe_username');\">$safe_username</a></td>";

			echo "<td class=\"slot\">$realm</td>";
			echo "<td class=\"slot\">$ping</td>";
			echo "<td class=\"slot\">$ip<br />$host</td>";
			echo "<td class=\"slot\">$country</td>";
			echo "</tr>";
			$counter++;
		}
	}
} else {
	echo "<h3>No users found for this game.</h3>";
}

//CSRF values to use from JS forms
$csrfname = "CSRFGuard_".mt_rand(0,mt_getrandmax());
$csrftoken = csrfguard_generate_token($csrfname);

?>

</table>

<script type="text/javascript">
function actionDialog(username) {
	$('body').append('<div id="dialog_' + username + '"><h2>' + username + '</h3></div>');
	$('#dialog_' + username).dialog({
		height: 150,
		modal: true,
		buttons: {
			Kick: function() {
				$.post('/forum/slots_slow.php', {'CSRFName': '<?= $csrfname ?>', 'CSRFToken': '<?= $csrftoken ?>', 'action': 'kick', 'username': username});
				$(this).dialog('close');
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
}
</script>
