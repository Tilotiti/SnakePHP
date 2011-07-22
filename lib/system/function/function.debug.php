<?php
function debug($string, $file = "log") {
    $fp = fopen(LOG.'/'.$file.'_'.date('Y').'-'.date('m').'-'.date('d').'.txt', "a");
    fputs($fp, '['.date("j F Y, H:i").'] '.$string."\n");
    fclose($fp);
}
?>