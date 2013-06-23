<?php

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
