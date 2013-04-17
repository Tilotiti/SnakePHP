<?php

/*
 * flushSQL: deletes all SQL cache in /cache/sql/
 * Use it in case of updates in the database
 * flushSQL: string ($file, optional, contains md5 checksum of an SQL request)
*/

function flushSQL($fileName = '') {
	$dir = SQLCACHE;

	// Specific filename was passed
	if (!empty($fileName)):
		$fileName .= '.cache';
		return (is_file($dir.'/'.$fileName) && unlink($dir.'/'.$fileName));
	else:
		// What files are in the directory?
		foreach(glob(SQLCACHE.'/*.cache') as $file):
			unlink($file);
		endforeach;
	endif;
}
