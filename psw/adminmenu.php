<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	require_once "AdminFunctions.class.php";
	require_once "Security.class.php";
	
	$mySecurity = new Security( );

	#
	# This should be added in every script. Ofcourse the action name
	# will be different for each script.
	#
// 	if ($mySecurity-> isNotAllowedTo('Show Admin Menu'))
	if ($mySecurity-> isNotAllowedTo(1))
	{
		$mySecurity-> GotoLoginPage( );
		return false;
	}

	ob_start( );

	include "header.inc.php";
	
	$myAdminFunctions = new AdminFunctions( );

	$FormElements = $_POST["form_adminmenu"];

	$FormElements['__error'] = "";

	$myAdminFunctions-> ShowAdminMenu($FormElements);

	include "donate.inc.php";

	include "footer.inc.php";

	ob_end_flush( );

	return true;
?>