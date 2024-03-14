<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	require_once "Security.class.php";

	require_once "Groups.class.php";

	session_start();

	$Groups = new Groups();
	$mySecurity = new Security( );
	
	#
	# This should be added in every script. Ofcourse the action name
	# will be different for each script.
	#
// 	if ($mySecurity-> isNotAllowedTo('Modify Group') and
// 			$mySecurity-> isNotAllowedTo('View Group') and
// 			$mySecurity-> isNotAllowedTo('Add Group') and
// 			$mySecurity-> isNotAllowedTo('Delete Group'))
// 	{
// 		if ($mySecurity-> isAllowedTo('Show Admin Menu'))
// 			$mySecurity-> GotoThisPage( "adminmenu.php" );
// 		else
// 			$mySecurity-> GotoThisPage( "login.php" );
// 	}
	if ($mySecurity-> isNotAllowedTo(8) and
			$mySecurity-> isNotAllowedTo(9) and
			$mySecurity-> isNotAllowedTo(6) and
			$mySecurity-> isNotAllowedTo(7))
	{
		if ($mySecurity-> isAllowedTo(1))
			$mySecurity-> GotoThisPage( "adminmenu.php" );
			else
				$mySecurity-> GotoThisPage( "login.php" );
	}
	
	ob_start( );

	#
	# If the user manually enters some groupid, we should check if it does exist
	# originally in their database query......
	#
	if (!array_key_exists ( intval($_GET['groupId']), 
													$_SESSION['groups_read_from_table']))
	{
		$mySecurity-> GotoThisPage( "bogus.php" );
	}

	include "header.inc.php";
	
	$FormElements = $_POST['form_GroupsForm'];

	if ($FormElements["B_clear"])
		unset ($FormElements);

	if ($_GET['mode'] == 'edit')
	{
// 		if ($mySecurity-> isAllowedTo('Modify Group'))
		if ($mySecurity-> isAllowedTo(8))
		{
			# we can modify the Group name
			if ($FormElements["B_submit"])
			{
				if ($Groups-> UpdateGroup($FormElements,$_GET['groupId']))
					$mySecurity-> GotoThisPage( "groups.php" );
			}
		}
		else
			$mySecurity-> GotoNotAuthorized( );
	}
	elseif ($_GET['mode'] == 'delete')
	{
// 		if ($mySecurity-> isAllowedTo('Delete Group'))
		if ($mySecurity-> isAllowedTo(7))
		{
			# we can delete the Group
			if ($FormElements["B_submit"])
			{
				if ($Groups-> DeleteGroup($_GET['groupId']))
					$mySecurity-> GotoThisPage( "groups.php" );
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
		$mySecurity-> GotoThisPage( "groups.php" );
	}

	if (!is_array($FormElements))
	{
		$ObjectResult = $Groups-> GetGroup($_GET['groupId']);

// 		$FormElements['groupname'] = $ObjectResult->fields("groupname");
// 		$FormElements['groupId'] = $ObjectResult->fields("groupid");
// 		$FormElements['hierarchy'] = $ObjectResult->fields("hierarchy");
// 		$FormElements['cliente_id'] = $ObjectResult->fields("cliente_id");
		$FormElements['groupname'] = $ObjectResult->groupname;
		$FormElements['groupId'] = $ObjectResult->groupid;
		$FormElements['hierarchy'] = $ObjectResult->hierarchy;
		$FormElements['cliente_id'] = $ObjectResult->cliente_id;
	}

	echo $Groups->SendGroupsForm($FormElements,$_GET['mode']);

	
	include "donate.inc.php";

	include "footer.inc.php";

	ob_end_flush( );

	return true;
?>