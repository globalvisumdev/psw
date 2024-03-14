<?php
include ("../config.php");
require_once "../psw/Security.class.php";
include('../include.php');
require_once "Groups.class.php";

$mySecurity = new Security();
$Groups = new Groups();

if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])) {
    $mySecurity-> GotoThisPage( "login.php" );
}

if (isset($_POST["cmd"])) {
    $cmd = $_POST["cmd"];
    $FormElements = $_POST['form_PermissionForm'];

    if ($cmd == "cargarTabla") { 
        echo json_encode($Groups->ListPermission());
    }

    if ($cmd == "agregarPermiso") { 
        echo json_encode($Groups->EventualPermission($FormElements));
    }

    if ($cmd == "generarReporte") {
        echo json_encode($Groups-> GenerateReport($FormElements));
        // $Groups-> GenerateReport($FormElements);
    }

    if ($cmd == "eliminarPermiso") {
        echo json_encode($Groups-> DeletePermission($FormElements));
    }


}

?>
