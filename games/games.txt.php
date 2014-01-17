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
include("../include/common.php");
include("../include/dbconnect.php");
include("../include/botlocate.php");

$result = databaseQuery("SELECT botid, gamename, slotstaken, slotstotal FROM gamelist WHERE lobby = 1 ORDER BY botid");

while($row = $result->fetch()) {
	echo getBotName($row[0]) . ',' . $row[2] . ',' . $row[3] . ',' . $row[1] . "\n";
}

?>
