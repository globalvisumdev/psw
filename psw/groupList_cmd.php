<?php
// ini_set('display_errors', 1);

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

      if ($cmd == "listarGrupo") {
        echo json_encode($Groups->ListGroupAccounts());
      }
      

    };



    


    ob_end_flush( );

    // return true;
?>