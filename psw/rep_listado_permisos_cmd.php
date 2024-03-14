<?php
require_once "../psw/Security.class.php";
$mySecurity = new Security();
$Accounts = new Accounts();

if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])){
    $mySecurity-> GotoThisPage( "login.php" );
  die();
}

if (isset($_POST["cmd"])) {
    $cmd = $_POST["cmd"];
    $FormElements = $_POST['form_listPermForm'];

    if ($cmd == "generarReporte") {
        echo json_encode($Accounts->GenerateReportUserPermission($FormElements));
    }

}



?>
      