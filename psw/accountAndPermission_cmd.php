<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/
	require_once "Security.class.php";

	require_once "Accounts.class.php";

	session_start();

	$myAccounts = new Accounts();
	$mySecurity = new Security( );
	
	#
	# This should be added in every script. Ofcourse the action name
	# will be different for each script.
	#

	if ($mySecurity-> isNotAllowedTo(23) and
			$mySecurity-> isNotAllowedTo(24))
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
			echo json_encode($myAccounts->getAccountActions($FormElements));
		}

		if ($cmd == "agregarAcciones") { 
			echo json_encode($myAccounts->AddAccountActions($FormElements));
		}

		if ($cmd == "quitarAcciones") { 
			echo json_encode($myAccounts->RemoveAccountActions($FormElements));
		}

		if ($cmd == "agregarClientes") { 
			echo json_encode($myAccounts->AddAccountClientesClientes($FormElements));
		}

		if ($cmd == "quitarClientes") { 
			echo json_encode($myAccounts->RemoveAccountClientesClientes($FormElements));
		}

		if ($cmd == "agregarEmpresas") { 
			echo json_encode($myAccounts->AddAccountEmpresas($FormElements));
		}

		if ($cmd == "quitarEmpresas") { 
			echo json_encode($myAccounts->RemoveAccountEmpresas($FormElements));
		}

		if ($cmd == "agregarJurisdicciones") { 
			echo json_encode($myAccounts->AddAccountJurisdicciones($FormElements));
		}

		if ($cmd == "quitarJurisdicciones") { 
			echo json_encode($myAccounts->RemoveAccountJurisdicciones($FormElements));
		}

		if ($cmd == "agregarVehiculos") { 
			echo json_encode($myAccounts->AddAccountVehiculos($FormElements));
		}

		if ($cmd == "quitarVehiculos") { 
			echo json_encode($myAccounts->RemoveAccountVehiculos($FormElements));
		}

		if ($cmd == "agregarServicios") { 
			echo json_encode($myAccounts->AddAccountServicios($FormElements));
		}

		if ($cmd == "quitarServicios") { 
			echo json_encode($myAccounts->RemoveAccountServicios($FormElements));
		}

		if ($cmd == "agregarClientesEquivalentes") { 
			echo json_encode($myAccounts->AddAccountClientesEquivales($FormElements));
		}

		if ($cmd == "quitarClientesEquivalentes") { 
			echo json_encode($myAccounts->RemoveAccountClientesEquivales($FormElements));
		}

	}

	ob_end_flush( );

?>