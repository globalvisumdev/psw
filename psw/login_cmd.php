<?php
// ini_set('display_errors', 1);

// if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
// } 
// else {
//     if ($_SERVER['SERVER_NAME'] == '190.220.132.134' OR $_SERVER['SERVER_NAME'] == 'micronauta.dnsalias.net') {
//         if ( checkdnsrr('micronauta.dnsalias.net', 'A')) {
//             header("Location:https://micronauta.dnsalias.net".$_SERVER['REQUEST_URI']);die();
//         }
//         if ( checkdnsrr('micronauta2.dnsalias.net', 'A')) {
//             header("Location:https://micronauta2.dnsalias.net".$_SERVER['REQUEST_URI']);die();
//         }
//     }

//     if ($_SERVER['SERVER_NAME'] == '201.216.249.194' OR $_SERVER['SERVER_NAME'] == 'micronauta2.dnsalias.net') {
//         if ( checkdnsrr('micronauta.dnsalias.net', 'A')) {
//             header("Location:https://micronauta.dnsalias.net".$_SERVER['REQUEST_URI']);die();
//         }
//         if ( checkdnsrr('micronauta2.dnsalias.net', 'A')) {
//             header("Location:https://micronauta2.dnsalias.net".$_SERVER['REQUEST_URI']);die();
//         }
//     }
// }

if (isset($_REQUEST['cmd'])) {
  $cmd = $_REQUEST['cmd'];

  if ($cmd == 'login') {

    include_once('../../conection/config.php');

    include("Security.class.php");
    $_SESSION = array();
    session_destroy();
    session_start();

    $mySecurity = new Security();

    if (isset($_POST["form_login"])) {
      $FormElements = $_POST["form_login"];
      $FormElements['__error'] = "";
      $FormElements['__aviso'] = "";

      if ($FormElements['username'] == "" or $FormElements['password'] == "") {
        $FormElements['__error'] = $themepsw['login']['error'];
      }


      if ($FormElements['__error'] == "") {
        if (!$mySecurity->VerifyUser($FormElements['username'], $FormElements['password'])) {
          $FormElements['__error'] = $mySecurity->GetErrorMessage();
        }
      }

      if ($FormElements['__error'] == "") {
        if (!$mySecurity->ValidarPassword("", $FormElements['password'])) {
          $FormElements['__error'] = "Debe cambiar la contraseña.";
        }
      }

      // if ($FormElements['__error'] == "") {

      // cambiar el curl por un metodo de my security
      // $url = 'cambia_pass.php?user='.$FormElements['username']."&h=".rand()."&hm=".$_SESSION['is'];

      // if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {

      //     $url = 'https://localhost/'.dirsistema().'/psw/'.$url;

      // } else { 

      //     $url = 'http://localhost/'.dirsistema().'/psw/'.$url;

      // }


      // $ch = curl_init();
      // curl_setopt ($ch, CURLOPT_URL, $url);
      // curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

      // //agregado por Fernando
      // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 

      // curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 100);
      // $contents = curl_exec($ch);
      // curl_close($ch);
      // if($contents !== false){
      //     $resp=array();
      //     $resp=json_decode($contents,true);
      //     $FormElements['__error'] = $resp['error'];
      //     $FormElements['__aviso'] = $resp['aviso'];
      // } else {
      //     $FormElements['__error'] = 'Error al validar. Reintente.';
      // }

      // }

      if ($FormElements['__error'] == "") {
        $_SESSION['username']  = $FormElements['username'];
        $_SESSION['myAccount']  = $mySecurity->GetAccountID();
        $_SESSION['myHierarchy'] = $mySecurity->GetHierarchy();
        $_SESSION['db_cli']     = $mySecurity->GetDbCliente();
        // $_SESSION['gobierno_provincia']     = $mySecurity-> GetGobProvincia( );
        $_SESSION['cliente_id'] = $mySecurity->GetClienteID();
        $_SESSION['host_cli'] = $mySecurity->GetHostCliente();
        $_SESSION['fecha_desde_reporte'] = $mySecurity->GetFechaDesdeReporte();
        //$mySecurity-> SuccessfulLogin();

        unset($_SESSION['is']);
        unset($_SESSION['seg_unico']);
        $_SESSION['loginPrompting'] = null;

        $_SESSION['userId'] = $mySecurity->GetAccountID();
        $_SESSION['tipoUsuario']  = "admin";
        $_SESSION['u-credential']  = $FormElements['username'];
        $_SESSION['p-credential']  = md5($FormElements['password']);
        $_SESSION["esAdministracion"] = true;

        echo json_encode(array(
          "ok" => true,
        ));
        die();
      } else {

        echo json_encode(array(
          "ok" => false,
          "errorMsg" =>  $FormElements['__error'],
          "avisoMsg" =>  $FormElements['__aviso'],
        ));
        die();
      }
    }
  }



  ### Formulario NUEVA CONTRASEÑA ###################################################################################
  if ($cmd == 'newPass') {
    include_once('../../conection/config.php');

    require_once("Security.class.php");
    $_SESSION = array();
    session_destroy();
    session_start();

    $mySecurity = new Security();
    session_start();


    if ($_GET['user'] and isset($_GET['h'])) {

      if ($_GET['hm'] == $_SESSION['is']) {
      } else {
        header('401 Not Authorized');
        die('Not authorized');
      }
      include_once('../../conection/config.php');
      try {
        $pdo = new PDO("mysql:host=$ezMap[host];dbname=$ezMap[db];charset=utf8", $ezMap['user_admin'], $ezMap['pass_admin']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo_isla = new PDO("mysql:host=" . $ezMap['isla_host'] . ";dbname=" . $ezMap['db'] . ";charset=utf8", $ezMap['isla_user_admin'], $ezMap['isla_pass_admin']);
        $pdo_isla->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
        echo "Se ha producido un error "; //.$e->getMessage();
        return false;
      }

      $resp['error'] = "";
      $resp['aviso'] = "";

      $accountid = 0;
      $q = "SELECT accounts.*,datediff(now(),fecha_cambio_pass) dias FROM accounts where username= :username";
      try {
        $registros = $pdo_isla->prepare($q);
        $datos = array(':username' => $_GET['user']);
        $registros->execute($datos);
        $result = $registros->fetch(PDO::FETCH_ASSOC);
        $accountid = $result['accountid'];
        $fecha_cambio = $result['fecha_cambio_pass'];
        $dias = $result['dias'];
        $cli = $result['cliente_id'];

        //si es de sisca 90 dias, sino 180 dias
      } catch (PDOException $e) {
        echo "Error: consulta cp."; //.$e->getMessage();
      }

      if ($accountid == 0) {
        $resp['error'] = ' '; //'Usuario no encontrado.';
      }


      if ($resp['error'] == "") {
        $max = 90;
        if ($cli != 1) $max = 180;

        if (is_null($fecha_cambio)) {
          //cambia la fecha a un dia entre 170 y 150 dias atras rand()*(170-150)+150 day
          $q = "update accounts set fecha_cambio_pass = date(DATE_SUB(now(),INTERVAL rand()*20+" . ($max - 40) . " day))
          where accountid=" . $accountid;

          try {
            $registros = $pdo->prepare($q);
            $registros->execute();
          } catch (PDOException $e) {
            echo "Error: consulta cp."; //.$e->getMessage();
          }
        } else {
          if ($dias > $max) {
            $resp['error'] = utf8_encode("Contraseña vencida, debe cambiarla.");
          } else {
            $en = $max - $dias;
            if ($en < 10) {
              $resp['aviso'] = utf8_encode("Su Contraseña vence en " . $en . " días, deber�a cambiarla.");
              if ($en == 0) {
                $resp['aviso'] = utf8_encode("Su Contraseña vence hoy, deber�a cambiarla.");
              }
            }
          }
        }
      }
      echo json_encode($resp);
      die();
    }

    require_once "Security.class.php";

    // segundoScript##########################################################################

    // $_SESSION = array(); 
    // include_once('../../conection/config.php');
    // require_once( "Security.class.php");
    // session_destroy();
    // session_start( );

    $mySecurity = new Security();

    function validar_clave($clave, &$error_clave)
    {
      if (strlen($clave) < 8) {
        $error_clave = "La clave debe tener al menos 8 caracteres";
        return false;
      }
      if (strlen($clave) > 16) {
        $error_clave = "La clave no puede tener más de 16 caracteres";
        return false;
      }
      if (!preg_match('`[a-z]`', $clave)) {
        $error_clave = "La clave debe tener al menos una letra minúscula";
        return false;
      }
      if (!preg_match('`[A-Z]`', $clave)) {
        $error_clave = "La clave debe tener al menos una letra mayúscula";
        return false;
      }
      if (!preg_match('`[0-9]`', $clave)) {
        $error_clave = "La clave debe tener al menos un caracter numérico";
        return false;
      }
      $error_clave = "";
      return true;
    }

    $FormElements = $_POST["form_login"];
    $FormElements['__error'] = "";

    if ($FormElements['username'] == "" or $FormElements['password'] == "" or $FormElements['password_new'] == "" or $FormElements['password_rep'] == "") {
      $FormElements['__error'] = $themepsw['login']['error'];
    }

    if ($FormElements['__error'] == "") {
      if ($FormElements['password_new'] != $FormElements['password_rep']) {
        $FormElements['__error'] = 'La Contraseña nueva no coincide';
      }
    }

    if ($FormElements['__error'] == "") {
      if ($FormElements['password'] == $FormElements['password_rep']) {
        $FormElements['__error'] = 'La Contraseña nueva no puede ser igual a la actual';
      }
    }

    if ($FormElements['__error'] == "") {
      if (validar_clave($FormElements['password_new'], $error)) {
      } else {
        $FormElements['__error'] = $error;
      }
    }

    if ($FormElements['__error'] == "") {
      if (!$mySecurity->VerifyUser($FormElements['username'], $FormElements['password'])) {
        $FormElements['__error'] = $mySecurity->GetErrorMessage();
      }
    }


    if ($FormElements['__error'] == "") {

      include_once('../../conection/config.php');
      try {
        $pdo_isla = new PDO("mysql:host=$ezMap[isla_host];dbname=$ezMap[db];charset=utf8", $ezMap['isla_user_admin'], $ezMap['isla_pass_admin']);
        $pdo_isla->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo = new PDO("mysql:host=$ezMap[host];dbname=$ezMap[db];charset=utf8", $ezMap['user_admin'], $ezMap['pass_admin']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
        echo "Se ha producido un error "; //.$e->getMessage();
      }

      $accountid = 0;
      $q = "SELECT * FROM accounts where username= :username and password=:pass";
      try {
        $registros = $pdo_isla->prepare($q);
        $datos = array(':pass' => md5($FormElements['password']), ':username' => $FormElements['username']);
        $registros->execute($datos);
        $result = $registros->fetch(PDO::FETCH_ASSOC);
        $accountid = $result['accountid'];
      } catch (PDOException $e) {
        echo "Error: consulta cp."; //.$e->getMessage();
      }

      if ($accountid == 0) {
        $FormElements['__error'] =  $mySecurity->GetErrorMessage();
      }
    }

    if ($FormElements['__error'] == "") {
      //actualizar ultima fecha y password
      $q = "update accounts 
          set  password= :password_new 
        , fecha_cambio_pass = now()  where accountid=" . $accountid;

      try {
        $registros = $pdo->prepare($q);
        $datos = array(':password_new' => md5($FormElements['password_new']));
        $registros->execute($datos);
      } catch (PDOException $e) {
        $FormElements['__error'] = "Error: No pude actualizar la Contraseña.Reintente."; //.$e->getMessage();
      }
    }

    if ($FormElements['__error'] == "") {
      function ipCheck()
      {
        /*
        This function checks if user is coming behind proxy server. Why is this important?
        If you have high traffic web site, it might happen that you receive lot of traffic
        from the same proxy server (like AOL). In that case, the script would count them all as 1 user.
        This function tryes to get real IP address.
        Note that getenv() function doesn't work when PHP is running as ISAPI module
        */
        if ($_SERVER['HTTP_CLIENT_IP']) {
          $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
          $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ($_SERVER['HTTP_X_FORWARDED']) {
          $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif ($_SERVER['HTTP_FORWARDED_FOR']) {
          $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif ($_SERVER['HTTP_FORWARDED']) {
          $ip = $_SERVER['HTTP_FORWARDED'];
        } else {
          $ip = $_SERVER['REMOTE_ADDR'];
        }

        // 020719 para sanitizar la direccion y evitar sproof
        preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $ip, $ip_match);
        $ip = $ip_match[0];

        return $ip;
      }

      $ip = ipCheck();
      $time = time();

      // list( $dia, $mes, $ano, $hora, $min, $seg ) = preg_split( '[\W]', date("d-m-Y H:i:s", mktime()) );
      list($dia, $mes, $ano, $hora, $min, $seg) = preg_split('[\W]', date("d-m-Y H:i:s", mktime()));
      $ahora_ymd = "$ano-$mes-$dia $hora:$min:$seg";

      $q = "INSERT INTO log (timestamp,ip,accountid,username,activityid,fechayhora) "
        . "VALUES($time, '$ip', $accountid,:username, 13 , '$ahora_ymd')";

      try {
        $registros = $pdo->prepare($q);
        $datos = array(':username' => $FormElements['username']);
        $registros->execute($datos);
      } catch (PDOException $e) {
        echo "Error: consulta cp."; //.$e->getMessage();
      }

      // header("Location: ../index.php");
      // exit;

      echo json_encode(array(
        "ok" => true
      ));

      die();
    } else {
      echo json_encode(array(
        "ok" => false,
        "errorMsg" =>  $FormElements['__error']

      ));
      die();
    }
  }
}
