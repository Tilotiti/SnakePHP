<?php
/**
 * Adds/edit a cookie
 * 
 * @param String $name cookie name
 * @param mixed $var cookie value
 * @param Integer $time[optional] cookie expires (Unix timestamp) - default: the past
 * @return void
 */
function cookie($name, $var, $time = 32140800) {
    $time += time();
    setcookie($name, $var, $time, "/");
}