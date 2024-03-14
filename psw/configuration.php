<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	require_once "Security.class.php";

	require_once "Configuration.class.php";

	session_start();

	$Configuration = new Configuration();
	$mySecurity = new Security( );
	
	#
	# This should be added in every script. Ofcourse the action name
	# will be different for each script.
	#
// 	if ($mySecurity-> isNotAllowedTo('Modify Config'))
// 	{
// 		if ($mySecurity-> isAllowedTo('Show Admin Menu'))
// 			$mySecurity-> GotoThisPage( "adminmenu.php" );
// 		else
// 			$mySecurity-> GotoThisPage( "login.php" );
// 	}
	if ($mySecurity-> isNotAllowedTo(19))
	{
		if ($mySecurity-> isAllowedTo(1))
			$mySecurity-> GotoThisPage( "adminmenu.php" );
			else
				$mySecurity-> GotoThisPage( "login.php" );
	}
	
	ob_start( );

	include "header.inc.php";
	
	$FormElements = $_POST['form_configuration'];

	# we can modify the configuration options.
	if ($FormElements["B_submit"])
	{
		if (!$Configuration-> ErrCheckConfigurationForm($FormElements))
			if ($Configuration-> UpdateConfiguration($FormElements))
				$mySecurity-> GotoThisPage( "adminmenu.php" );
	}
	
	if ($FormElements["B_cancel"])
	{
		$mySecurity-> GotoThisPage( "adminmenu.php" );
	}

	if (!is_array($FormElements))
	{
		$ObjectResult = $Configuration-> GetConfiguration( );

		$FormElements['md5'] = $ObjectResult->fields("md5");
		$FormElements['bad_attempts_max'] = $ObjectResult->fields("bad_attempts_max");
		$FormElements['bad_attempts_wait'] = $ObjectResult->fields("bad_attempts_wait");
		$FormElements['log_activities'] = $ObjectResult->fields("log_activities");
		$FormElements['timeout'] = $ObjectResult->fields("timeout");
		$FormElements['error_reporting'] = $ObjectResult->fields("error_reporting");
		$FormElements['stylesheet'] = $ObjectResult->fields("stylesheet");
	}

	echo $Configuration->SendConfigurationForm($FormElements);

	
	include "donate.inc.php";

	include "footer.inc.php";

	ob_end_flush( );

	return true;
?>