<?php
function cookie($name, $var, $time = 32140800) {
    $time += time();
    setcookie($name, $var, $time, "/");
}
?>