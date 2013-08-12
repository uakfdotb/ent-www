<?php

$ENT_LOCK_FILENAME = NULL;
$ENT_LOCK_FH = NULL;

function entLock($name) {
	global $ENT_LOCK_FH, $ENT_LOCK_FILENAME;
	
	$ENT_LOCK_FILENAME = '/var/lock/' . md5($name) . '.pid';
	$ENT_LOCK_FH = @fopen($ENT_LOCK_FILENAME, 'a');
	
	if(!$ENT_LOCK_FH || !flock($ENT_LOCK_FH, LOCK_EX | LOCK_NB, $eWouldBlock) || $eWouldBlock) {
		die('Failed to acquire lock.');
	} else {
		register_shutdown_function('entLockRelease');
	}
}

function entLockRelease() {
	global $ENT_LOCK_FH, $ENT_LOCK_FILENAME;
	
	if($ENT_LOCK_FH !== NULL && $ENT_LOCK_FILENAME !== NULL) {
		fclose($ENT_LOCK_FH);
		unlink($ENT_LOCK_FILENAME);
	}
}

?>
