<?php

include("include/common.php");
include("include/dbconnect.php");
include("include/botlocate.php");

if(isset($_REQUEST['id'])) {
	$gameid = $_REQUEST['id'];
	$result = databaseQuery("SELECT botid FROM games WHERE id = ?", array($gameid));

	if($row = $result->fetch()) {
		$botid = intval($row[0]);
		$sid = 0;

		if(isset($idToServer[$botid])) {
			$sid = $idToServer[$botid];
		}

		if($sid === 0) {
			echo "<b>Replay does not exist for that game ($botid).</b>";
		} else {
			if(!isset($_GET['chat'])) {
				header("Location: http://$sid.entgaming.net/replay/view_replay.php?file=$gameid.w3g");
			} else {
				header("Location: http://$sid.entgaming.net/replay/replays/$gameid.txt");
			}
		}
	} else {
		echo "<b>Could not find the requested game!</b>";
	}
} else {
	?>
	<form method="get" action="replay.php">
	Game ID: <input type="text" name="id"> <input type="submit" value="Get replay">
	</form>
	<?
}

?>
