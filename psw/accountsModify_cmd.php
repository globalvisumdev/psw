<?php
  /*
  * @author Bulent Tezcan. bulent@greenpepper.ca
  */
  require_once "Security.class.php";
  require_once "Accounts.class.php";
  session_start();

  $Accounts = new Accounts();
  $mySecurity = new Security( );

  if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])) {
      $mySecurity-> GotoThisPage( "login.php" );
  }
  
  // if ($mySecurity-> isNotAllowedTo(12) and
  // 		$mySecurity-> isNotAllowedTo(11))
  // {
  // 	if ($mySecurity-> isAllowedTo(1))
  // 		$mySecurity-> GotoThisPage( "adminmenu.php" );
  // 		else
  // 		$mySecurity-> GotoThisPage( "login.php" );
  // }
  
  ob_start( );

  # If the user manually enters some accountid, we should check if it does exist
  # originally in their database query......

  // if (!array_key_exists ( $_POST['accountId'], $_SESSION['accounts_read_from_table'])) {
  //   $mySecurity-> GotoThisPage( "bogus.php" );
  // }


    // $FormElements = array_merge((array) $_POST['form_AccountsForm'],(array) $_POST['form_accounts_group_information']);


  if (isset($_POST["cmd"])) {
		$cmd = $_POST["cmd"];
    $FormElements = $_POST['form_AccountsForm'];

    if ($cmd == "cargarFormulario") {
      if ($_POST['accountId'] AND $FormElements['firstname'] == ""){
        echo json_encode( $Accounts-> GetAccount($_POST['accountId']));
      }
    }
    if ($cmd == "datosUsuario") {
      $username = $Accounts-> GetAccount($_SESSION["myAccount"]);
      echo json_encode( array( "firstname" => $username["data"] -> firstname, "username" => $username["data"] -> username, "accountid" => $username["data"] -> accountid))  ;
    }
    if ($cmd == "cargarGrupos") {
        echo json_encode( $Accounts-> AccountGroups($FormElements));
    }
    if ($cmd == "agregarGrupos") { 
			echo json_encode($Accounts->AddGroupsToAccount($FormElements));
		}
		if ($cmd == "quitarGrupos") { 
			echo json_encode($Accounts->RemoveGroupsFromAccount($FormElements));
		}

		if ($cmd == "editarUsuario") {
      if ($mySecurity-> isAllowedTo(12)){
        $ErrCheckAccountsForm = $Accounts->ErrCheckAccountsForm($FormElements,$_POST['accountId'],"edit");
        if ($ErrCheckAccountsForm["ok"] ){
          echo json_encode($Accounts-> UpdateAccount($FormElements,$_POST['accountId']));
        }
        else {
          echo json_encode($ErrCheckAccountsForm);
        }
      }
      else{
        $mySecurity-> GotoThisPage( "login.php" );
        // $mySecurity-> GotoNotAuthorized( );
      }
		}

    if ($cmd == "eliminarUsuario") {
      if ($mySecurity-> isAllowedTo(11)){
          if ($FormElements["B_submit"]) {
              if ($Accounts-> DeleteAccount($_GET['accountId'])) {
              $mySecurity-> GotoThisPage( "accountsList.php" );
          }
        }
      }
      else
        $mySecurity-> GotoNotAuthorized( );
    }
      


  }
  else{
      $mySecurity-> GotoThisPage( "usuarioServices.html" );
  }



  ob_end_flush( );

?>