<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	require_once "Security.class.php";

	require_once "Actions.class.php";

	session_start();

	$Actions = new Actions();
	$mySecurity = new Security( );
	
	#
	# This should be added in every script. Ofcourse the action name
	# will be different for each script.
	#
// 	if ($mySecurity-> isNotAllowedTo('Add Action') and
// 			$mySecurity-> isNotAllowedTo('Delete Action') and
// 			$mySecurity-> isNotAllowedTo('Modify Action') and
// 			$mySecurity-> isNotAllowedTo('View Action'))
// 	{
// 		if ($mySecurity-> isAllowedTo('Show Admin Menu'))
// 			$mySecurity-> GotoThisPage( "adminmenu.php" );
// 		else
// 			$mySecurity-> GotoThisPage( "login.php" );
// 	}
	if ($mySecurity-> isNotAllowedTo(2) and
			$mySecurity-> isNotAllowedTo(3) and
			$mySecurity-> isNotAllowedTo(4) and
			$mySecurity-> isNotAllowedTo(5))
	{
		if ($mySecurity-> isAllowedTo(1))
			$mySecurity-> GotoThisPage( "adminmenu.php" );
			else
				$mySecurity-> GotoThisPage( "login.php" );
	}
	
	ob_start( );

	include "header.inc.php";
	
	$FormElements = $_POST['form_ActionsForm'];

	if ($FormElements["B_clear"])
		unset ($FormElements);

// 	if ($mySecurity-> isAllowedTo('Add Action'))
	if ($mySecurity-> isAllowedTo(2))
	{
		if ($FormElements["B_add_submit"])
			$Actions->AddAction($FormElements);
		
		echo $Actions->SendActionsForm($FormElements,null);
	}

	$Actions->ListActions();
	
	include "donate.inc.php";

	include "footer.inc.php";

	ob_end_flush( );

	return true;
?>