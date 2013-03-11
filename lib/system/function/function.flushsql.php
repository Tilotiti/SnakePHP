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
		return (is_file($dir.$fileName) && unlink($dir.$fileName));
	else:
		// What files are in the directory?
		$dirContent = scandir($dir);

		// Are there any cache files in it?
		if (count($dirContent) > 3):
			
			unset($dirContent[0], $dirContent[1], $dirContent[2]);
			foreach ($dirContent as $fileName) {
				if (!unlink($dir.$fileName)):
					return false;
				endif;
			}
			return true;
		else:
			return false;
		endif;
	endif;
}