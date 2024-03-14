<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	require_once "Security.class.php";

	require_once "Groups.class.php";

	session_start();

	$myGroups = new Groups();
	$mySecurity = new Security( );
	
	#
	# This should be added in every script. Ofcourse the action name
	# will be different for each script.
	#

	if ($mySecurity-> isNotAllowedTo(20) and
			$mySecurity-> isNotAllowedTo(21))
	{
		if ($mySecurity-> isAllowedTo(1))
			$mySecurity-> GotoThisPage( "usuarioServices.html" );
			else
				$mySecurity-> GotoThisPage( "login.php" );
	}
	
	ob_start( );

    if (isset($_POST["cmd"])) {
        $cmd = $_POST["cmd"];
		$FormElements = $_POST['form_GroupsForm'];

		if ($cmd == "cargarOpciones") { 
            echo json_encode($myGroups->getGroupActions($FormElements));
		}
        
		if ($cmd == "agregarAcciones") { 
            echo json_encode($myGroups->AddGroupActions($FormElements));
		}

		if ($cmd == "quitarAcciones") { 
			echo json_encode($myGroups->RemoveGroupActions($FormElements));
		}

		if ($cmd == "agregarUsuarios") { 
            echo json_encode($myGroups->AddGroupAccounts($FormElements));
		}
        
		if ($cmd == "quitarUsuarios") { 
            echo json_encode($myGroups->RemoveGroupAccounts($FormElements));
		}
        
        if ($cmd == "agregarEmpresas") { 
            echo json_encode($myGroups->AddGroupEmpresas($FormElements));
        }

        if ($cmd == "quitarEmpresas") { 
            echo json_encode($myGroups->RemoveGroupEmpresas($FormElements));
        }
	}

	
	ob_end_flush( );

?>