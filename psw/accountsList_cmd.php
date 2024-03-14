<?php

    require_once "Security.class.php";

    require_once "Accounts.class.php";

    session_start();

    $Accounts = new Accounts();
    $mySecurity = new Security( );
	if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])){
		$mySecurity-> GotoThisPage( "login.php" );
		die();
    }
	
    if ($mySecurity-> isNotAllowedTo(10) and
        $mySecurity-> isNotAllowedTo(11) and
        $mySecurity-> isNotAllowedTo(12) and
        $mySecurity-> isNotAllowedTo(13)){

		if ($mySecurity-> isAllowedCmd(1)){

			$mySecurity-> GotoThisPage( "usuarioServices.html" );
			die();
		}
		else{
			$mySecurity-> GotoThisPage( "login.php" );
			die();
		}
	}

	ob_start( );

	if (isset($_POST["cmd"])) {
		$cmd = $_POST["cmd"];
		$FormElements = $_POST['form_GroupsForm'];

		if ($cmd == "cargarTabla") {
			echo json_encode($Accounts->ListAccounts($_POST['search'], $_POST['sort'],$_POST['offset'],$_POST['limit']));
		}

		if ($cmd == "eliminarUsuario") {
			echo json_encode($Accounts->DeleteAccount($FormElements["accountid"]));
		}

	}

	ob_end_flush( );

?>