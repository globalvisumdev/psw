<?php
  /*
  * @author Bulent Tezcan. bulent@greenpepper.ca
  */

if ($_GET['user'] and isset($_GET['h'])) {

    include ('../config.php');
    try {
        $pdo = new PDO("mysql:host=$ezMap[host];dbname=$ezMap[db];charset=utf8",$ezMap[user_admin],$ezMap[pass_admin]);
        $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    }
    catch(PDOException $e) {
        echo "Se ha producido un error ";//.$e->getMessage();
        return false;
    }
    
    $resp['error'] = "";
    $resp['aviso'] = "";
    
    $accountid=0;
    $q = "SELECT accounts.*,datediff(now(),fecha_cambio_pass) dias FROM accounts where username= :username";
    try {
        $registros = $pdo->prepare($q);
        $datos = array(':username' => $_GET['user']);
        $registros->execute($datos);
        $result = $registros->fetch(PDO::FETCH_ASSOC);
        $accountid =$result['accountid'];
        $fecha_cambio=$result['fecha_cambio_pass'];
        $dias =$result['dias'];
        $cli = $result['cliente_id'];
        
        //si es de sisca 90 dias, sino 180 dias
    }
    catch(PDOException $e) {
        echo "Error: consulta cp.";//.$e->getMessage();
    }
    
    if ($accountid == 0) {
        $resp['error'] = ' ' ; //'Usuario no encontrado.';
    }


    if ($resp['error'] == "") {
        $max = 90;
        if ($cli != 1) $max = 180;
        
            if (is_null($fecha_cambio)) {
                //cambia la fecha a un dia entre 170 y 150 dias atras rand()*(170-150)+150 day
                $q="update accounts set fecha_cambio_pass = date(DATE_SUB(now(),INTERVAL rand()*20+".($max - 40)." day))
        where accountid=".$accountid ;
                
                try {
                    $registros = $pdo->prepare($q);
                    $registros->execute();
                }
                catch(PDOException $e) {
                    echo "Error: consulta cp.";//.$e->getMessage();
                }
                
        } else {
            if ($dias > $max) {
                $resp['error'] = utf8_encode("Contraseña vencida, debe cambiarla.");
            } else {
                $en = $max - $dias;
                if ($en < 10) {
                    $resp['aviso'] = utf8_encode("Su contraseña vence en ".$en." días, debería cambiarla.");
                    if ($en == 0) {
                        $resp['aviso'] = utf8_encode("Su contraseña vence hoy, debería cambiarla.");
                    }
                }
            }
            
            
        }
    }
    echo json_encode($resp);
    die();
}


  require_once "Security.class.php";
  session_start( );
  ob_start( );
  include "header.inc.php";
  ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../Classes/w3.css">
  <?php
  #include "../ver_posts_y_gets.php";
      $_SESSION = array();       //agregado pablo 061118
    session_destroy(); 
    session_start( );
    //     $_SESSION['myAccount']  = null;
//     $_SESSION['username']  = null;
//     $_SESSION['myHierarchy']= null;
//     $_SESSION['db_cli']     = null;
//     $_SESSION['cliente_id'] = null;
//     $_SESSION['fecha_desde_reporte'] = null;
  
  $mySecurity = new Security( );

function validar_clave($clave,&$error_clave){
    if(strlen($clave) < 8){
        $error_clave = "La clave debe tener al menos 8 caracteres";
        return false;
    }
    if(strlen($clave) > 16){
        $error_clave = "La clave no puede tener más de 16 caracteres";
        return false;
    }
    if (!preg_match('`[a-z]`',$clave)){
        $error_clave = "La clave debe tener al menos una letra minúscula";
        return false;
    }
    if (!preg_match('`[A-Z]`',$clave)){
        $error_clave = "La clave debe tener al menos una letra mayúscula";
        return false;
    }
    if (!preg_match('`[0-9]`',$clave)){
        $error_clave = "La clave debe tener al menos un caracter numérico";
        return false;
    }
    $error_clave = "";
    return true;
}
  $FormElements = $_POST["form_login"];
  $FormElements['__error'] = "";
  
  if ($FormElements['username'] == "" or $FormElements['pass'] == "" or $FormElements['password_new'] == "" or $FormElements['password_rep'] == "") {
      $FormElements['__error'] = $themepsw['login']['error'];
  }

  if (  $FormElements['__error'] == "") {
      
      
      if ($FormElements['password_new'] != $FormElements['password_rep']) {
          $FormElements['__error'] = 'La contraseña nueva no coincide';
      }
      
      if ($FormElements['pass'] == $FormElements['password_rep']) {
          $FormElements['__error'] = 'La contraseña nueva no puede ser igual a la actual';
      }
      
      if (validar_clave($FormElements['password_new'],$error)) {} else {
          $FormElements['__error'] = $error;
      }
      
      if (!$mySecurity-> VerifyUser($FormElements['username'], $FormElements['pass'])) {
          $FormElements['__error'] = $mySecurity-> GetErrorMessage( );
      }
      
}
  
  if ($FormElements['__error'] == "") {
      include ('../config.php');
      try {
          $pdo = new PDO("mysql:host=$ezMap[host];dbname=$ezMap[db];charset=utf8",$ezMap[user_admin],$ezMap[pass_admin]);
          $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      }
      catch(PDOException $e) {
          echo "Se ha producido un error ";//.$e->getMessage();
      }
      
      $accountid=0;
      $q = "SELECT * FROM accounts where username= :username and password=:pass";
      try {
          $registros = $pdo->prepare($q);
          $datos = array(':pass' => md5($FormElements['pass']),':username' => $FormElements['username']);
          $registros->execute($datos);
          $result = $registros->fetch(PDO::FETCH_ASSOC);
          $accountid =$result['accountid'];
      }
      catch(PDOException $e) {
          echo "Error: consulta cp.";//.$e->getMessage();
      }
  
      if ($accountid == 0) {
          $FormElements['__error'] =  $mySecurity-> GetErrorMessage( );
      } 
  }
  
  if ($FormElements['__error'] == "") {
                //actualizar ultima fecha y password
              $q="update accounts 
                    set  password= :password_new 
                    , fecha_cambio_pass = now()  where accountid=".$accountid ;      
//        where username= :username and password=:pass";
              
              try {
                  $registros = $pdo->prepare($q);
                  $datos = array(':password_new' => md5($FormElements['password_new']));
                  $registros->execute($datos);
              }
              catch(PDOException $e) {
                  $FormElements['__error'] = "Error: No pude actualizar la contraseña.Reintente.";//.$e->getMessage();
              }
  }
  
  if ($FormElements['__error'] == "") {
              function ipCheck( )
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
                  }
                  elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
                      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                  }
                  elseif ($_SERVER['HTTP_X_FORWARDED']) {
                      $ip = $_SERVER['HTTP_X_FORWARDED'];
                  }
                  elseif ($_SERVER['HTTP_FORWARDED_FOR']) {
                      $ip = $_SERVER['HTTP_FORWARDED_FOR'];
                  }
                  elseif ($_SERVER['HTTP_FORWARDED']) {
                      $ip = $_SERVER['HTTP_FORWARDED'];
                  }
                  else {
                      $ip = $_SERVER['REMOTE_ADDR'];
                  }
                  
                  // 020719 para sanitizar la direccion y evitar sproof
                  preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $ip, $ip_match);
                  $ip = $ip_match[0];
                  return $ip;
              }
              
              
              
                //grabar log con el cambio
              #$ip = getenv('REMOTE_ADDR');
              $ip = ipCheck();
              $time = time();
              list( $dia, $mes, $ano, $hora, $min, $seg ) = split( '[ :/.-]', date("d-m-Y H:i:s", mktime()) );
              $ahora_ymd = "$ano-$mes-$dia $hora:$min:$seg";
              
              $q = "INSERT INTO log (timestamp,ip,accountid,username,activityid,fechayhora) "
                  ."VALUES($time, '$ip', $accountid,:username, 13 , '$ahora_ymd')";
                  
                  try {
                      $registros = $pdo->prepare($q);
                      $datos = array(':username' => $FormElements['username']);
                      $registros->execute($datos);
                  }
                  catch(PDOException $e) {
                      echo "Error: consulta cp.";//.$e->getMessage();
                  }
                  
            header("Location: ../index.php");
            exit;
  } 
  
 ?>
 <div class="w3-container"  >
		<form method="post" name="login"  class="w3-container w3-card-8  w3-border w3-display-middle" >
			  <h2 class="w3-light-grey">Cambio de contraseña</h2>
						<input type="hidden" name="passwd_type" value="text" />
						<input type="hidden" name="account_type" value="u" />
					<p><label>Usuario</label>
					<input class="w3-input" type="text" name="form_login[username]" value="<?php echo  $FormElements['username'];?>" size="30" required /></p>
					<p><label>Contraseña actual</label>
					<input class="w3-input"  name="form_login[pass]" type="password" value="<?php echo  $FormElements['pass'];?>" size="30" required/></p>
					<p><label>Nueva Contraseña</label>
					<input class="w3-input"  name="form_login[password_new]" type="password" size="30" required/></p>
					<p><label>Repetir Nueva Contraseña</label>
					<input class="w3-input"  name="form_login[password_rep]" type="password" size="30" required/></p>
					<p><button class="w3-btn w3-blue " type="submit" name="submitit">Cambiar</button>
					<button class="w3-btn w3-blue" name="VOVLER" onclick="location.href='login.php';">Volver</button></p>
					 
     <?php echo $FormElements['__error'];?>
     </form>
     </div>
<?php 
  include "footer.inc.php";
  ob_end_flush( );
  return true;
?>