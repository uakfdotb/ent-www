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

/*
This is the admin gamelist, which shows the IP address (not displayed on the public gamelist).
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

if ($user->data['user_id'] == ANONYMOUS || !isadmin($user->data['user_id'])) {
    header('Location: /forum/ucp.php?mode=login');
} else {
	include("../include/dbconnect.php");
	include("../include/host.php");

	$result = databaseQuery("SELECT gamename, usernames FROM gamelist WHERE lobby = 1 AND gamename != '' AND REPLACE(usernames, '\\t', '') != '' ORDER BY id DESC");

	while($row = $result->fetch()) {
		$gamename = htmlspecialchars($row[0]);

		echo "<h2>$gamename</h2>";
		echo "<table><tr><th>Name</th><th>Realm</th><th>Ping</th><th>IP</th><th>Host</th><th>Country</th><th>Region</th><th>City</th></tr>";

		$array = explode("\t", $row[1]);

		for($i = 0; $i < count($array) - 3; $i += 4) {
			$name = htmlspecialchars($array[$i]);
			$realm = htmlspecialchars($array[$i + 1]);
			$ping = htmlspecialchars($array[$i + 2]);
			$ip = htmlspecialchars($array[$i + 3]);
			$host = htmlspecialchars(getHost($ip));
			$country = htmlspecialchars(@geoip_country_name_by_name($ip));
			$region = "Unknown";
			$city = "Unknown";
			
			$record = @geoip_record_by_name($ip);
			
			if(isset($record['city'])) {
				$city = htmlspecialchars($record['city']);
			}
			
			if(isset($record['region'])) {
				$region = htmlspecialchars($record['region']);
			}

			if($name != '') {
				echo "<tr><td>$name</td><td>$realm</td><td>$ping</td><td>$ip</td><td>$host</td><td>$country</td><td>$city</td><td>$region</td></tr>";
			}
		}

		echo "</table>";
	}
}
?>
