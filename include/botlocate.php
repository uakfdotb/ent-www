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

$idToServer = array(
	1 => 'atl',
	2 => 'atl',
	3 => 'atl',
	4 => 'atl',
	5 => 'atl',
	6 => 'atl',
	7 => 'atl',
	8 => 'atl',
	9 => 'atl',
	10 => 'atl',
	11 => 'atl',
	12 => 'atl',
	13 => 'atl',
	14 => 'atl',
	15 => 'atl',
	16 => 'atl',
	17 => 'atl',
	18 => 'atl',
	19 => 'atl',
	20 => 'atl',
	21 => 'atl',
	22 => 'atl',
	23 => 'atl',
	24 => 'atl',
	25 => 'atl',
	26 => 'atl',
	27 => 'atl',
	28 => 'atl',
	29 => 'atl',
	30 => 'atl',
	31 => 'atl',
	32 => 'atl',
	33 => 'atl',
	34 => 'atl',
	35 => 'atl',
	36 => 'atl',
	37 => 'atl',
	38 => 'atl',
	39 => 'atl',
	40 => 'unknown',
	41 => 'eihl',
	42 => 'unknown',
	43 => 'unknown',
	44 => 'unknown',
	45 => 'unknown',
	46 => 'unknown',
	47 => 'atl',
	48 => 'atl',
	49 => 'atl',
	50 => 'unknown',
	51 => 'atl',
	52 => 'atl',
	53 => 'atl',
	54 => 'atl',
	55 => 'unknown',
	56 => 'atl',
	57 => 'atl',
	58 => 'unknown',
	59 => 'atl',
	60 => 'atl',
	61 => 'atl',
	62 => 'atl',
	63 => 'atl',
	64 => 'unknown',
	65 => 'atl',
	66 => 'atl',
	67 => 'atl',
	68 => 'atl',
	69 => 'atl',
	70 => 'unknown',
	71 => 'atl',
	72 => 'unknown',
	73 => 'atl',
	74 => 'atl',
	75 => 'unknown',
	76 => 'unknown',
	77 => 'atl',
	78 => 'atl',
	79 => 'chicago',
	80 => 'unknown',
	81 => 'unknown',
	82 => 'chicago',
	83 => 'atl',
	84 => 'atl',
	85 => 'atl',
	86 => 'atl',
	87 => 'atl',
	88 => 'chicago',
	89 => 'atl',
	90 => 'ee',
	91 => 'ee',
	92 => 'ee',
	93 => 'ee',
	94 => 'ee',
	95 => 'ee',
	96 => 'ee',
	101 => 's4',
	102 => 's4',
	103 => 's4',
	104 => 's4',
	105 => 's4',
	106 => 's4',
	107 => 's4',
	108 => 's4',
	'201' => 'chicago',
	'202' => 'chicago',
	'203' => 'chicago',
	'204' => 'chicago',
	'301' => 'nl',
	'302' => 'nl',
	'303' => 'nl',
	'304' => 'nl',
	'305' => 'nl',
	'306' => 'nl',
	'307' => 'nl',
	'308' => 'nl',
	'401' => 'la',
	'402' => 'la',
	'403' => 'la',
	'404' => 'la');

function getBotName($botid) {
	if($botid == 60) {
		return "ENTID";
	} else if($botid < 100) {
		return "ENT$botid";
	} else if($botid < 101) {
		return "ENT)RAND";
	} else if($botid < 200) {
		return "Ent.Hosting" . ($botid - 100);
	} else if($botid < 300) {
		return "Ent.Chicago" . ($botid - 200);
	} else if($botid < 400) {
		return "Ent.Europe" . ($botid - 300);
	} else if($botid < 500) {
		return "Ent.Seattle" . ($botid - 400);
	} else if($botid < 600) {
		return "AMH" . ($botid - 500);
	} else if($botid < 700) {
		return "Ent.LA" . ($botid - 600);
	} else {
		return "Unknown";
	}
}

?>
