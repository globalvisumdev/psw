<?php
  require_once "Security.class.php";
  session_start( );
  ob_start( );
  include "header.inc.php";
  ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../Classes/w3.css">
  <style>
  .mySlides {display:none;}
  </style>
  <?php
  #include "../ver_posts_y_gets.php";
  $_SESSION = array();       //agregado pablo 061118
  session_destroy();
  session_start( );
  
  $mySecurity = new Security( );

  $FormElements = $_POST["form_login"];
  $FormElements['__error'] = "";
  $FormElements['__aviso'] = "";
  if ($FormElements['username'] == "" or $FormElements['password'] == "") {
      $FormElements['__aviso'] = '';//$themepsw['login']['error'];
      $FormElements['__error'] = " ";
  }
  
  if ($FormElements['__error'] == "") {
      if (!$mySecurity-> VerifyUser($FormElements['username'], $FormElements['password'])) {
          $FormElements['__error'] = $mySecurity-> GetErrorMessage( );
    }
  }
  if ($FormElements['__error'] == "") {
      $url = 'cambia_pass.php?user='.$FormElements['username']."&h=".rand();
      $url = 'http://localhost/megacontrol/psw/'.$url;
//      $url    = "$schema//$_SERVER[HTTP_HOST]".'/megacontrol/psw/'.$url;
      //$contents = file_get_contents($url);
  
      $ch = curl_init();
      curl_setopt ($ch, CURLOPT_URL, $url);
      curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 100);
      $contents = curl_exec($ch);
      curl_close($ch);
     // $contents = ($contents) ? $contents : FALSE;
      //If $contents is not a boolean FALSE value.
      if($contents !== false){
          $resp=array();
          $resp=json_decode($contents,true);
          $FormElements['__error'] = $resp['error'];
          $FormElements['__aviso'] = $resp['aviso'];
      } else {
          $FormElements['__error'] = 'Error al validar. Reintente.';
      }
      
   }
        
    if ($FormElements['__error'] == "") {
        $_SESSION['username']  = $FormElements['username'];
      $_SESSION['myAccount']  = $mySecurity-> GetAccountID( );
      $_SESSION['myHierarchy']= $mySecurity-> GetHierarchy( );
      $_SESSION['db_cli']     = $mySecurity-> GetDbCliente( );
      $_SESSION['cliente_id'] = $mySecurity-> GetClienteID( );
      $_SESSION['host_cli'] = $mySecurity-> GetHostCliente( );
      $_SESSION['fecha_desde_reporte'] = $mySecurity-> GetFechaDesdeReporte( );
      $mySecurity-> SuccessfulLogin();
      
        $_SESSION['loginPrompting'] = null;
        #header("Location: ".$_SESSION['http_referer']);
        header("Location: ../index.php");
        die();
  }
 ?>
 <script>
function mensaje(value) {
	var req = new XMLHttpRequest();
	req.open('GET', 'cambia_pass.php?user='+value+"&h="+Math.random(), true);
	req.onreadystatechange = function (aEvt) {
	  if (req.readyState == 4) {
	     if(req.status == 200) {
	    	 var result = JSON.parse(req.response);
	    	 document.getElementById('error').textContent = result['error'];
	    	 document.getElementById('aviso').textContent = result['aviso'];
	     } else {
	    	 document.getElementById('error').textContent = "Error al validar. Reintente";
	    	 document.getElementById('aviso').textContent = "";
	     }
	  }
	};
	req.send(null);
}
 </script>
 <div class="w3-container"  >
<div class="w3-auto"  >
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/imagen1.jpg" style="max-width:100%;max-height:100%;display: block; margin: 0 auto;">
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/imagen2.jpg" style="max-width:100%;max-height:100%;display: block; margin: 0 auto;">
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/imagen3.jpg" style="max-width:100%;max-height:100%;display: block; margin: 0 auto;">
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/contacto.jpg" style="max-width:100%;max-height:100%;display: block; margin: 0 auto;">
</div>
		<form method="post" name="login"  class="w3-container w3-card-8  w3-border w3-display-middle">
			  <h2 class="w3-light-grey" style="text-align:center">Bienvenido</h2>
						<input type="hidden" name="passwd_type" value="text" />
						<input type="hidden" name="account_type" value="u" />
					<p><label>Usuario</label>
					<input class="w3-input" type="text" name="form_login[username]"  onChange="mensaje(this.value)" size="30" required value="<?php echo $FormElements['username'];?>"/></p>
					<p><label>Contraseña</label>
					<input class="w3-input"  name="form_login[password]" type="password"  size="30" required/></p>
					<p><button class="w3-btn w3-blue " type="submit" name="submitit">Entrar</button>
 					<button class="w3-btn w3-blue" name="cambiar" onclick="location.href='cambia_pass.php';">Cambiar Contraseña</button></p>
     <p id="error" style="color:red;font-weight: bold;"><?php echo $FormElements['__error'];?></p><p id="aviso" style="color:black;font-weight: normal;"><?php echo $FormElements['__aviso'];?></p>
     </form>
     </div>
<script>
var slideIndex = 0;
carousel();

function carousel() {
    var i;
    var x = document.getElementsByClassName("mySlides");
    for (i = 0; i < x.length; i++) {
      x[i].style.display = "none";
    }
    slideIndex++;
    if (slideIndex > x.length) {slideIndex = 1}
    x[slideIndex-1].style.display = "block";
    setTimeout(carousel, 8000);
}
</script>

<?php 
  include "footer.inc.php";
  ob_end_flush( );
  return true;
?>