<?php
include ("../config.php");
require_once "../psw/Security.class.php";
include('../include.php');
$mySecurity = new Security();
$Accounts = new Accounts();

if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])) {
    $mySecurity-> GotoThisPage( "../psw/login.php" );
}

if (isset($_POST["cmd"])) {
    $cmd = $_POST["cmd"];
    $FormElements = $_POST['form_PermissionForm'];

    if ($cmd == "cargarTabla") { 
        echo json_encode($Accounts->ListPermission());
    }

    if ($cmd == "agregarPermiso") { 
        echo json_encode($Accounts->EventualPermission($FormElements));
    }

    if ($cmd == "generarReporte") {
        echo json_encode($Accounts-> GenerateReport($FormElements));
        // $Accounts-> GenerateReport($FormElements);
    }

    if ($cmd == "eliminarPermiso") {
        echo json_encode($Accounts-> DeletePermission($FormElements));
        // $Accounts-> DeletePermission($FormElements);
    }


}

?>
