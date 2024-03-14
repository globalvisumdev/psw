<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/
	require_once "Security.class.php";

	require_once "Accounts.class.php";

	session_start();

	$myGroups = new Accounts();
	$mySecurity = new Security( );
	
	#
	# This should be added in every script. Ofcourse the action name
	# will be different for each script.
	#
// 	if ($mySecurity-> isNotAllowedTo('Insert Account Actions') and
// 			$mySecurity-> isNotAllowedTo('Delete Account Actions'))
// 	{
// 		if ($mySecurity-> isAllowedTo('Show Admin Menu'))
// 			$mySecurity-> GotoThisPage( "adminmenu.php" );
// 		else
// 			$mySecurity-> GotoThisPage( "login.php" );
// 	}
	if ($mySecurity-> isNotAllowedTo(23) and
			$mySecurity-> isNotAllowedTo(24))
	{
		if ($mySecurity-> isAllowedTo(1))
			$mySecurity-> GotoThisPage( "adminmenu.php" );
			else
				$mySecurity-> GotoThisPage( "login.php" );
	}
	
	ob_start( );

	include "header.inc.php";
	
	$FormElements = $_POST['form_accounts_and_actions'];

	if ($FormElements["B_cancel"])
		$mySecurity-> GotoThisPage( "adminmenu.php" );

	switch (TRUE)
	{
		Case $FormElements["B_add_actions"]:
			
			$myGroups-> AddAccountActions($FormElements);
			
			break;
		
		Case $FormElements["B_remove_actions"]:
			
			$myGroups-> RemoveAccountActions($FormElements);
			
			break;
		
		Case $FormElements["B_add_empresa"]:
			
			$myGroups-> AddAccountEmpresas($FormElements);
			
			break;
		
		Case $FormElements["B_remove_empresa"]:
			
			$myGroups-> RemoveAccountEmpresas($FormElements);
			
			break;
		Case $FormElements["B_add_jurisdiccion"]:
					
			$myGroups-> AddAccountJurisdicciones($FormElements);
					
			break;
			
		Case $FormElements["B_remove_jurisdiccion"]:
					
			$myGroups-> RemoveAccountJurisdicciones($FormElements);
				
			break;
		Case $FormElements["B_add_vehiculo"]:
		    
		    $myGroups-> AddAccountVehiculos($FormElements);
		    
		    break;
		    
		Case $FormElements["B_remove_vehiculo"]:
		    
		    $myGroups-> RemoveAccountVehiculos($FormElements);
		    
		    break;
		Case $FormElements["B_add_servicio"]:
		    
		    $myGroups-> AddAccountServicios($FormElements);
		    
		    break;
		    
		Case $FormElements["B_remove_servicio"]:
		    
		    $myGroups-> RemoveAccountServicios($FormElements);
		    
		    break;
		    
			Case $FormElements["B_add_cliente"]:
					
				$myGroups-> AddAccountClientesClientes($FormElements);
					
				break;
					
			Case $FormElements["B_remove_cliente"]:
					
				$myGroups-> RemoveAccountClientesClientes($FormElements);
			
				break;
					
			
			Case $FormElements["B_add_usuario"]:
					
				$myGroups-> AddAccountClientesEquivales($FormElements);
					
				break;
					
			Case $FormElements["B_remove_usuario"]:
					
				$myGroups-> RemoveAccountClientesEquivales($FormElements);
			
				break;
	}

	echo $myGroups->SendAccountsAndActionsForm($FormElements);

	
	include "donate.inc.php";

	include "footer.inc.php";

	ob_end_flush( );

	return true;
?>