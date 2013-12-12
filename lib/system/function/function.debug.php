<?php
/**
 * Add a line to a logfile
 * Datetime will be automatically added before data
 * 
 * @param String $string string to log
 * @param String $file log basename where data will be appended 
 *  @return void
 */
function debug($string, $file = "log") {
    $fp = fopen(LOG.'/'.$file.'_'.date('Y').'-'.date('m').'-'.date('d').'.txt', "a");
    fputs($fp, '['.date("j F Y, H:i").'] '.$string."\n");
    fclose($fp);
}