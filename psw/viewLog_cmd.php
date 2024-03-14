<?php
  require_once "Security.class.php";
  $mySecurity = new Security();
  $Accounts = new Accounts();


  if ($mySecurity-> isNotAllowedTo(18))
  {
    if ($mySecurity-> isAllowedTo(1))
      $mySecurity-> GotoThisPage( "usuarioServices.html" );
    else
      $mySecurity-> GotoThisPage( "login.php" );
  }



if (isset($_POST["cmd"])) {
    $cmd = $_POST["cmd"];
    $FormElements = $_POST['form_viewLogForm'];


    if ($cmd == "generarReporte") {
        echo json_encode($Accounts->GenerateReportLog($FormElements));
    }

}

?>