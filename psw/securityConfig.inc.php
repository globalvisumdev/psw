<?php
/*
* @author Bulent Tezcan. bulent@greenpepper.ca
*/


#############################################################################
#
#     Start your seetings from here
#
#############################################################################

include($_SERVER['DOCUMENT_ROOT'].'/config_web.php'); //trae db_location

# we don't want to use Global variables.

# check the adodb/readme.htm for more information about the databse types

#define("DATABASE_SOFTWARE",        "postgres7");
define("DATABASE_SOFTWARE",         "mysqli");
if (!defined("DB_LOCATION"))
define("DB_LOCATION",               "192.168.100.252");

#define("DB_ACCOUNT",               "postgres");
if (!defined("DB_ACCOUNT"))
define("DB_ACCOUNT",                "micro_admin");

if (!defined("DB_PASSWORD"))
define("DB_PASSWORD",               "adminmatrix");

define("DB_DATABASE",               "megacontrol");

define("USA_MICRONAUTA",               false);              
# type the directory where you installed css, again no trailing slash
#define("CSS_DIRECTORY",            "/security/css");
define("CSS_DIRECTORY",             "/css");

# type the directory where you installed ADODB, again no trailing slash
# this is relative to where the security directory is.
# By default it comes inside the security directory
# If you want to move it outside the security directory than you can write
# something like that: ../adodb
define("ADODB_DIRECTORY",           "adodb5");


# this defines where to go after you logout from the admin menu.
# Type the page name you want to go. This page also could be an html page
# like index.php, index.htm or myOwnPage.whatever
#
define("GOTO_PAGE_AFTER_LOGOUT",    "index.php");

# your admin e-mail goes here
define("ADMIN_EMAIL","pcaceres@siscadat.com.ar");

#############################################################################
#
#     End your seetings here
#
#############################################################################


if (isset($_SESSION["USE_MD5"]) and
    isset($_SESSION["BAD_ATTEMPTS_MAX"]) and
    isset($_SESSION["BAD_ATTEMPTS_WAIT_SECONDS"]) and
    isset($_SESSION["LOG_ACTIVITIES"]) and
    isset($_SESSION["TIMEOUT_SECONDS"]) and
    isset($_SESSION["IS_ERROR_REPORTING"]) and
    isset($_SESSION["CSS"]) )
   return true;

include_once( ADODB_DIRECTORY."/adodb.inc.php" );
require_once "MyDatabase.class.php";

$myDatabase = new MyDatabase( );

$sql = "SELECT * FROM configuration";


try {
$oresultp = $myDatabase->pDBisla->query($sql);
while($orow=$oresultp->fetch(PDO::FETCH_OBJ)) {
    define("USE_MD5",   $orow->md5);
    
    $_SESSION["USE_MD5"] = $orow ->md5 == 1 ? TRUE : FALSE;
    $_SESSION["USE_MD5"] = TRUE;
    $_SESSION["BAD_ATTEMPTS_MAX"] = $orow->bad_attempts_max;
    $_SESSION["BAD_ATTEMPTS_WAIT_SECONDS"] = $orow->bad_attempts_wait;
    $_SESSION["LOG_ACTIVITIES"] = $orow->log_activities==1? TRUE : FALSE;
    $_SESSION["TIMEOUT_SECONDS"] = (int)$orow->timeout;
    $_SESSION["IS_ERROR_REPORTING"] = $orow->error_reporting ==1? TRUE : FALSE;

    define("BAD_ATTEMPTS_MAX",                  $orow->bad_attempts_max);
    define("BAD_ATTEMPTS_WAIT_SECONDS",      $orow->bad_attempts_wait);
    define("TIMEOUT_SECONDS",    (int)$orow->timeout);
    
}
}catch(PDOException  $e ){
    print_r( "No puedo continuar. Reintente.<br>" );
    die;
}


?>