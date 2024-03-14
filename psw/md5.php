<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	ob_start( );

	include "header.inc.php";
	
	require_once "Security.class.php";

	$mySecurity = new Security( );

	#
	# This should be added in every script. Ofcourse the action name
	# will be different for each script.
	#
// 	if ($mySecurity-> isNotAllowedTo('View MD5'))
// 	{
// 		if ($mySecurity-> isAllowedTo('Show Admin Menu'))
// 			$mySecurity-> GotoThisPage( "adminmenu.php" );
// 		else
// 			$mySecurity-> GotoThisPage( "login.php" );
// 	}
if ($mySecurity-> isNotAllowedTo(22))
{
	if ($mySecurity-> isAllowedTo(1))
		$mySecurity-> GotoThisPage( "adminmenu.php" );
		else
			$mySecurity-> GotoThisPage( "login.php" );
}


	$FormElements = $_POST["form_md5"];

	$FormElements['__error'] = "";
	
	if ($FormElements['password'] == "")
	{
		$FormElements['__error'] = "Please enter password";
		$FormElements['md5'] = "";
	}
	else
	{
		$FormElements['md5'] = md5(htmlspecialchars($FormElements['password']));
		$FormElements['__error'] = "Copy the MD5 password and use it for your admin account.";
	}


	$mySecurity-> PromptMD5Password($FormElements);

	include "donate.inc.php";

	include "footer.inc.php";

	ob_end_flush( );

	return true;
?>