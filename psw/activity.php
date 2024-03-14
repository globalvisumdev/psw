<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	require_once "Security.class.php";

	require_once "Activity.class.php";

	session_start();

	$Activity = new Activity();
	$mySecurity = new Security( );
	
	#
	# This should be added in every script. Ofcourse the action name
	# will be different for each script.
	#
// 	if ($mySecurity-> isNotAllowedTo('Add Activity') and
// 			$mySecurity-> isNotAllowedTo('Delete Activity') and
// 			$mySecurity-> isNotAllowedTo('Modify Activity') and
// 			$mySecurity-> isNotAllowedTo('View Activity'))
// 	{
// 		if ($mySecurity-> isAllowedTo('Show Admin Menu'))
// 			$mySecurity-> GotoThisPage( "adminmenu.php" );
// 		else
// 			$mySecurity-> GotoThisPage( "login.php" );
// 	}
	if ($mySecurity-> isNotAllowedTo(14) and
			$mySecurity-> isNotAllowedTo(15) and
			$mySecurity-> isNotAllowedTo(16) and
			$mySecurity-> isNotAllowedTo(17))
	{
		if ($mySecurity-> isAllowedTo(1))
			$mySecurity-> GotoThisPage( "adminmenu.php" );
			else
				$mySecurity-> GotoThisPage( "login.php" );
	}
	
	ob_start( );

	include "header.inc.php";
	
	$FormElements = $_POST['form_ActivityForm'];

	if ($FormElements["B_clear"])
		unset ($FormElements);

// 	if ($mySecurity-> isAllowedTo('Add Activity'))
	if ($mySecurity-> isAllowedTo(14))
	{
		if ($FormElements["B_add_submit"])
		{
			if ($Activity->AddActivity($FormElements))
				unset ($FormElements);
		}

		echo $Activity->SendActivityForm($FormElements,null);
	}

// 	if ($mySecurity-> isAllowedTo('Add Activity') or
// 			$mySecurity-> isAllowedTo('Delete Activity') or
// 			$mySecurity-> isAllowedTo('Modify Activity') or
// 			$mySecurity-> isAllowedTo('View Activity'))
// 		$Activity->ListActivity();
		if ($mySecurity-> isAllowedTo(14) or
				$mySecurity-> isAllowedTo(15) or
				$mySecurity-> isAllowedTo(16) or
				$mySecurity-> isAllowedTo(17))
			$Activity->ListActivity();
		
	include "donate.inc.php";

	include "footer.inc.php";

	ob_end_flush( );

	return true;
?>