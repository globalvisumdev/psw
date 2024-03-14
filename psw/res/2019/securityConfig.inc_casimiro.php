<?php
/*
* @author Bulent Tezcan. bulent@greenpepper.ca
*/


#############################################################################
#
#     Start your seetings from here
#
#############################################################################



# we don't want to use Global variables.

# check the adodb/readme.htm for more information about the databse types

#define("DATABASE_SOFTWARE",        "postgres7");
define("DATABASE_SOFTWARE",         "mysql");

define("DB_LOCATION",               "localhost");

#define("DB_ACCOUNT",               "postgres");
define("DB_ACCOUNT",                "root");

define("DB_PASSWORD",               "clavemysql");

define("DB_DATABASE",               "megacontrol");

# type the directory where you installed css, again no trailing slash
#define("CSS_DIRECTORY",            "/security/css");
define("CSS_DIRECTORY",             "/css");

# type the directory where you installed ADODB, again no trailing slash
# this is relative to where the security directory is.
# By default it comes inside the security directory
# If you want to move it outside the security directory than you can write
# something like that: ../adodb
define("ADODB_DIRECTORY",           "adodb");


# this defines where to go after you logout from the admin menu.
# Type the page name you want to go. This page also could be an html page
# like index.html, index.htm or myOwnPage.whatever
#
define("GOTO_PAGE_AFTER_LOGOUT",    "index.php");

# your admin e-mail goes here
define("ADMIN_EMAIL","bcasasnovas@cosmosit.com.ar");

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

$result = $myDatabase->gDB->Execute($sql);

if ($result === false) {
   print_r( "Can't read the configuration file. We can not continue.<br>".$myDatabase->gDB->ErrorMsg() );
   die;
}

# We keep the passwords in MD5 format. Default is 1. If you don't want
# than change it to zero. If you use MD5, create the admin password
# in MD5 format from the admin menu, and update the accounts table.
define("USE_MD5",   $result->fields("md5"));

$_SESSION["USE_MD5"] = $result->fields("md5") == 1 ? TRUE : FALSE;

# number of times the user could try until they are banned
#define("BAD_ATTEMPTS_MAX",                  $result->fields("bad_attempts_max"));

$_SESSION["BAD_ATTEMPTS_MAX"] = $result->fields("bad_attempts_max");

# the waiting period in seconds, incase of a limit reach for bad attempts
#define("BAD_ATTEMPTS_WAIT_SECONDS",      $result->fields("bad_attempts_wait"));

$_SESSION["BAD_ATTEMPTS_WAIT_SECONDS"] = $result->fields("bad_attempts_wait");

# If you want to log the activities set this one to true, otherwise false
#define("LOG_ACTIVITIES",     $result->fields("log_activities"));

$_SESSION["LOG_ACTIVITIES"] = $result->fields("log_activities")==1? TRUE : FALSE;

# Set the timeout for inactivity
#define("TIMEOUT_SECONDS",    $result->fields("timeout"));

$_SESSION["TIMEOUT_SECONDS"] = (int)$result->fields("timeout");

# Set it to true or false if you want to see the SQL errors.
#define("IS_ERROR_REPORTING",    $result->fields("error_reporting"));

$_SESSION["IS_ERROR_REPORTING"] = $result->fields("error_reporting")==1? TRUE : FALSE;

# if you want to use Cascading Style Sheets, type the name of the file
#
# You can choose styles from css directory, like :
#           Pilsner, LabattBlue, Corona
#
$_SESSION["CSS"] = $result->fields("stylesheet");

?>