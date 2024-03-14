<?php

session_start( );

include_once "securityConfig.inc.php";

$_SESSION["CSS"] = "micronauta";
/ $pathToCSS = "http".($_SERVER["HTTPS"]=="on"?"s":"")
//              ."://".$_SERVER["HTTP_HOST"].dirname($_SERVER["PHP_SELF"])
//              .CSS_DIRECTORY ."/".$_SESSION["CSS"].".css";

            $protocol='http';
            if ($_SERVER['HTTPS']=='on') $protocol='https';
            if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) $protocol='https'; //si viene por proy no trae HTTPS=on, asumo que es https
            $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
             $host = isset($host) ? $host : $_SERVER['SERVER_NAME'] . $port;
             $uri = $protocol . '://' . $host ;
             
             $pathToCSS =$uri
             .dirname($_SERVER["PHP_SELF"])
             .CSS_DIRECTORY ."/".$_SESSION["CSS"].".css";
             
             
             
#echo '$dirname: '.dirname($_SERVER["PHP_SELF"]).'<br>';
$tableClass = $_SESSION["CSS"]."FormTABLE";
$FieldCaptionTD = $_SESSION["CSS"]."FieldCaptionTD";
$DataTD = $_SESSION["CSS"]."DataTD";

// echo <<<HTML
// <html>
// <head>
// <title> Seguridad </title>
// <meta name="Author" content="">
// <meta name="Keywords" content="">
// <meta name="Description" content="">
// <meta HTTP-EQUIV="Expires" CONTENT="Tue, 01 Jan 2008 11:00:00 GMT">
// <link rel="stylesheet" type="text/css" href="$pathToCSS">
// <link href="../idots.css" type="text/css" rel="StyleSheet" />
// <link rel="manifest" href="../manifest.json" />
// </head>

// <body background="../images/login-background.jpg">
// HTML;
// echo <<<HTML
// HTML;

?>