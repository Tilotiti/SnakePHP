<?php
/**
 * Slugs a string
 * @param String $str string to slug
 * @param Boolean[optional] $convertSpecialChars if true, will "unaccentuate" chars (ex. é => e) - default: false
 * @param String[optional] $separator character to use to replace non-alphanum chars - default: '-'
 * @return String clean string
 */
function slug($str, $convertSpecialChars=false, $separator='-') {
	$clean = trim($str);
	if ($convertSpecialChars) {
		// that may seem weird but this seems to be the better way to do it
		$clean = htmlentities($clean,ENT_NOQUOTES,CHARSET);
		$clean = preg_replace('/&#?([a-zA-Z0-9])[a-zA-Z0-9]*;/i', '${1}', $clean);
	}
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $separator, $clean);

	return $clean;
}