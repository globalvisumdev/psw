<?php
    $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);

//    header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_HOST']);
    header("Access-Control-Allow-Origin: ".$host);
    header("Vary: Origin");
    header("X-Frame-Options: DENY");
?>