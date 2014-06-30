<?php

/**
 * Deletes SQL cache in /cache/sql/ - Use it in case of updates in the database
 * 
 * How to use :
 * $filename param can be either the complete hash of the request (access by query::getCacheHash), so the identified
 * cached query will be uncached.
 * 
 * It can be a "cache category" name (first parameter of query::__construct).
 * In this case, all cache of this category will be removed.
 * 
 * If left empty, the function will erase all cached queries.
 * 
 * @param String $fileName cache hash, cache category, or empty string - default: empty string
 * @param return void
 */
function flushSQL($fileName = '') {
	$dir = CACHE.'/sql';

	// Specific filename was passed
	if (!empty($fileName) && is_file($dir.'/'.$fileName.'.cache')):
		$fileName .= '.cache';
		return unlink($dir.'/'.$fileName);
	elseif (!empty($fileName)):
		$cachePref = md5('prefix'.$fileName);	
				
		// What files of this category are in the directory ?
		foreach(glob($dir.'/'.$cachePref.'*.cache') as $file):
			unlink($file);
		endforeach;
	else:
		// What files are in the directory?
		foreach(glob($dir.'/*.cache') as $file):
			unlink($file);
		endforeach;
	endif;
}
