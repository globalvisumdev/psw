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
// 	if ($mySecurity-> isNotAllowedTo('Modify Activity') and
// 			$mySecurity-> isNotAllowedTo('View Activity') and
// 			$mySecurity-> isNotAllowedTo('Add Activity') and
// 			$mySecurity-> isNotAllowedTo('Delete Activity'))
// 	{
// 		if ($mySecurity-> isAllowedTo('Show Admin Menu'))
// 			$mySecurity-> GotoThisPage( "adminmenu.php" );
// 		else
// 			$mySecurity-> GotoThisPage( "login.php" );
// 	}
	if ($mySecurity-> isNotAllowedTo(16) and
			$mySecurity-> isNotAllowedTo(17) and
			$mySecurity-> isNotAllowedTo(14) and
			$mySecurity-> isNotAllowedTo(15))
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

	if ($_GET['mode'] == 'edit')
	{
// 		if ($mySecurity-> isAllowedTo('Modify Activity'))
		if ($mySecurity-> isAllowedTo(16))
		{
			# we can modify the Activity name
			if ($FormElements["B_submit"])
			{
				if ($Activity-> UpdateActivity($FormElements,$_GET['activityId']))
					$mySecurity-> GotoThisPage( "activity.php" );
			}
		}
		else
			$mySecurity-> GotoNotAuthorized( );
	}
	elseif ($_GET['mode'] == 'delete')
	{
// 		if ($mySecurity-> isAllowedTo('Delete Activity'))
		if ($mySecurity-> isAllowedTo(15))
		{
			# we can delete the Activity
			if ($FormElements["B_submit"])
			{
				if ($Activity-> DeleteActivity($_GET['activityId']))
					$mySecurity-> GotoThisPage( "activity.php" );
			}
		}
		else
			$mySecurity-> GotoNotAuthorized( );
	}
	else
	{
		$mySecurity-> GotoThisPage( "adminmenu.php" );
	}
	
	if ($FormElements["B_cancel"])
	{
		$mySecurity-> GotoThisPage( "activity.php" );
	}

	if (!is_array($FormElements))
	{
		$ObjectResult = $Activity-> GetActivity($_GET['activityId']);

		$FormElements['description'] = $ObjectResult->fields("description");
		$FormElements['activityId'] = $ObjectResult->fields("activityid");
	}

	echo $Activity->SendActivityForm($FormElements,$_GET['mode']);

	
	include "donate.inc.php";

	include "footer.inc.php";

	ob_end_flush( );

	return true;
?>