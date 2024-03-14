<?php

  require_once "Security.class.php";
  require_once "Accounts.class.php";

  session_start();

  $Accounts = new Accounts();
  $mySecurity = new Security();

if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])){
  $mySecurity-> GotoThisPage( "login.php" );
  die();
}


if ($mySecurity-> isNotAllowedTo(10)){
	if ($mySecurity-> isAllowedTo(1)){
		$mySecurity-> GotoThisPage( "usuariosServices.html" );
  }
  else{
    $mySecurity-> GotoThisPage( "login.php" );
  }
}

  ob_start( );

  if (isset($_POST["cmd"])) {
    $cmd = $_POST["cmd"];
    $FormElements = $_POST['form_AccountsForm'];

    if ($cmd == "cargarTabla") { 
      // ver la query
      echo json_encode($Accounts->getClientes($FormElements['newAccount']));
    }
    
    if ($cmd == "agregarUsuario") {
      $ErrCheckAccountsForm = $Accounts->ErrCheckAccountsForm($FormElements,null,'add');
      if ($ErrCheckAccountsForm["ok"] ){
        echo json_encode($Accounts->AddAccount($FormElements));
      }
      else {
        echo json_encode($ErrCheckAccountsForm);
      }

    }



  }

//   $FormElements = $_POST['form_AccountsForm'];

//   if ($FormElements["B_clear"])
//     unset ($FormElements);

//   if ($FormElements["B_add_submit"])
//   {
//     if (!$Accounts->ErrCheckAccountsForm($FormElements,null,'add'))
//       if ($Accounts->AddAccount($FormElements))
//         unset ($FormElements);
//   }

//   echo $Accounts->SendAccountsForm($FormElements,null);

//   include "donate.inc.php";

//   include "footer.inc.php";

  ob_end_flush( );

//   return true;
