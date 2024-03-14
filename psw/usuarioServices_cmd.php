<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	require_once "AdminFunctions.class.php";
	require_once "Security.class.php";
	
	$mySecurity = new Security( );
	$myAdminFunctions = new AdminFunctions();

	#
	# This should be added in every script. Ofcourse the action name
	# will be different for each script.
	#

	if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])){
		$mySecurity-> GotoThisPage( "login.php" );
	  die();
    }

	if ($mySecurity-> isNotAllowedTo(1))
	{
		// $mySecurity-> GotoLoginPage( );
		// return false;
		die();

	}

	ob_start( );

	$FormElements['__error'] = "";

    if (isset($_POST["cmd"])) {
        $cmd = $_POST["cmd"];
        $FormElements = $_POST['form_AccountsForm'];

        if ($cmd == "cargarServicios") { 
            echo json_encode($myAdminFunctions-> ShowAdminMenu());
        }
        
    }

	ob_end_flush( );

?>