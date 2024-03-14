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
// 	if ($mySecurity-> isNotAllowedTo('Delete Action') and
// 			$mySecurity-> isNotAllowedTo('Modify Action'))
// 	{
// 		if ($mySecurity-> isAllowedTo('Show Admin Menu'))
// 			$mySecurity-> GotoThisPage( "adminmenu.php" );
// 		else
// 			$mySecurity-> GotoThisPage( "login.php" );
// 	}
	if ($mySecurity-> isNotAllowedTo(3) and
			$mySecurity-> isNotAllowedTo(4))
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

	if ($_GET['mode'] == 'edit')
	{
// 		if ($mySecurity-> isAllowedTo('Modify Action'))
		if ($mySecurity-> isAllowedTo(4))
		{
			# we can modify the action name
			if ($FormElements["B_submit"])
			{
				if ($Actions-> UpdateAction($FormElements,$_GET['actionId']))
					$mySecurity-> GotoThisPage( "actions.php" );
			}
		}
		else
			$mySecurity-> GotoNotAuthorized( );
	}
	elseif ($_GET['mode'] == 'delete')
	{
// 		if ($mySecurity-> isAllowedTo('Delete Action'))
		if ($mySecurity-> isAllowedTo(3))
		{
			# we can delete the action
			if ($FormElements["B_submit"])
			{
				if ($Actions-> DeleteAction($_GET['actionId']))
					$mySecurity-> GotoThisPage( "actions.php" );
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
		$mySecurity-> GotoThisPage( "actions.php" );
	}

	if (!is_array($FormElements))
	{
		$ObjectResult = $Actions-> GetAction($_GET['actionId']);

		$FormElements['actionname'] = $ObjectResult->fields("actionname");
	}

	echo $Actions->SendActionsForm($FormElements,$_GET['mode']);

	
	include "donate.inc.php";

	include "footer.inc.php";

	ob_end_flush( );

	return true;
?>