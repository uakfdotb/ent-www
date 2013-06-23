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

$hit = 0;
$total = 0;

//do housekeeping whenever this file is included
$hostsResult = databaseQuery("SELECT COUNT(*) FROM hosts");
$hostsRow = $hostsResult->fetch();

if($hostsRow[0] > 75000) {
	mysql_query("DELETE FROM hosts ORDER BY id LIMIT " . ($hostsRow[0] - 75000));
}

function getHost($ip) {
	global $total, $hit;
	$total++;
	
	if(!empty($ip)) {
		$iplong = ip2long($ip);
		
		if($ip !== false) {
			$result = databaseQuery("SELECT hostname FROM hosts WHERE ip = ?", array($iplong));
			
			if($row = $result->fetch()) {
				$hit++;
				return $row[0];
			} else {
				$host = gethostbyaddr($ip);
				databaseQuery("INSERT INTO hosts (ip, hostname) VALUES (?, ?)", array($iplong, $host));
				return $host;
			}
		}
	}
	
	return '';
}

?>
