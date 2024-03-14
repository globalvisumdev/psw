<?php

	require_once "Security.class.php";

	require_once "Groups.class.php";

	session_start();

	$Groups = new Groups();
	$mySecurity = new Security( );

	if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])){
		$mySecurity-> GotoThisPage( "login.php" );
	  die();
    }
	
	if ($mySecurity-> isNotAllowedTo(6) and
		$mySecurity-> isNotAllowedTo(7) and
		$mySecurity-> isNotAllowedTo(8) and
		$mySecurity-> isNotAllowedTo(9))
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

		if ($cmd == "cargarTabla") {
			echo json_encode($Groups->ListGroups($_POST['search']));
		}

		if ($cmd == "nuevoGrupo") {
			if ($mySecurity-> isAllowedTo(6)){
				echo json_encode($Groups->AddGroup($FormElements));
			}
		}
		
		if ($cmd == "editarGrupo") {
			if (!array_key_exists ( intval($FormElements['groupId']), $_SESSION['groups_read_from_table'])){
				$mySecurity-> GotoThisPage( "bogus.php" );
			}
			if ($mySecurity-> isAllowedTo(8)){
				echo json_encode($Groups-> UpdateGroup($FormElements,$FormElements['groupId']));
			}
			else{
				$mySecurity-> GotoThisPage( "login.php" );
				// $mySecurity-> GotoNotAuthorized( );
			}
		}

		if ($cmd == "eliminarGrupo"){
			if ($mySecurity-> isAllowedTo(7)){
				echo json_encode($Groups-> DeleteGroup($FormElements['groupId']));
			}
			else{
				$mySecurity-> GotoThisPage( "login.php" );
				// $mySecurity-> GotoNotAuthorized( );
			}
		}
	}

	ob_end_flush( );

?>