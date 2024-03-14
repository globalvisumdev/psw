<?php
  /*
  * @author Bulent Tezcan. bulent@greenpepper.ca
  */
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
  if ($_GET['mode'] == 'logout') {
      $_SESSION = array();       //agregado pablo 061118
    session_destroy; 
//     $_SESSION['myAccount']  = null;
//     $_SESSION['username']  = null;
//     $_SESSION['myHierarchy']= null;
//     $_SESSION['db_cli']     = null;
//     $_SESSION['cliente_id'] = null;
//     $_SESSION['fecha_desde_reporte'] = null;
  }
  $mySecurity = new Security( );
  if ($_GET['mode'] == 'logout') {
	 //Sacado por Pablo 16/02/09
    //$mySecurity-> GotoThisPage( GOTO_PAGE_AFTER_LOGOUT );
}
//Puesto por Pablo 16/02/09 para que bore los frams
//echo "<html>";
//echo "<body>";

// echo '<script LANGUAGE="JavaScript">';
// echo "function quitarFrame() ";
// echo "{";
// echo "if (self.parent.frames.length != 0)";
// echo "self.parent.location=document.location.href;";
// echo "}";
// echo "quitarFrame()";
// echo "</script>";

  $FormElements = $_POST["form_login"];
  $FormElements['__error'] = "";
  if ($FormElements['username'] == "" or $FormElements['password'] == "") {
      $FormElements['__error'] = $themepsw['login']['error'];
  }
  else {
    if (!$mySecurity-> VerifyUser($FormElements['username'], $FormElements['password'])) {
      $FormElements['__error'] = $mySecurity-> GetErrorMessage( );
    }
    else {
      $_SESSION['username']  = $FormElements['username'];
      $_SESSION['myAccount']  = $mySecurity-> GetAccountID( );
      $_SESSION['myHierarchy']= $mySecurity-> GetHierarchy( );
      $_SESSION['db_cli']     = $mySecurity-> GetDbCliente( );
      $_SESSION['cliente_id'] = $mySecurity-> GetClienteID( );
      $_SESSION['host_cli'] = $mySecurity-> GetHostCliente( );
      $_SESSION['fecha_desde_reporte'] = $mySecurity-> GetFechaDesdeReporte( );
      $mySecurity-> SuccessfulLogin();
    }
  }

  if ($FormElements['__error'] == "") {
    $_SESSION['loginPrompting'] = null;
    #header("Location: ".$_SESSION['http_referer']);
    header("Location: ../index.php");
    exit;
  }
 ?>
 <div class="w3-container"  >
<div class="w3-auto"  >
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/imagen1.jpg" style="max-width:100%;max-height:100%;display: block; margin: 0 auto;">
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/imagen2.jpg" style="max-width:100%;max-height:100%;display: block; margin: 0 auto;">
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/imagen3.jpg" style="max-width:100%;max-height:100%;display: block; margin: 0 auto;">
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/contacto.jpg" style="max-width:100%;max-height:100%;display: block; margin: 0 auto;">
</div>
		<form method="post" name="login"  class="w3-container w3-card-8  w3-border w3-display-middle" >
			  <h2 class="w3-light-grey">Bienvenido</h2>
						<input type="hidden" name="passwd_type" value="text" />
						<input type="hidden" name="account_type" value="u" />
					<p><label>Usuario</label>
					<input class="w3-input" type="text" name="form_login[username]" size="30" required /></p>
					<p><label>Contraseña</label>
					<input class="w3-input"  name="form_login[password]" type="password" onChange="this.form.submit()" size="30" required/></p>
					<p><button class="w3-btn w3-blue " type="submit" name="submitit">Entrar</button></p> 
     </form>
     </div>
<script>
//<div class="mySlides w3-container w3-xlarge w3-lightblue w3-card-8 w3-animate-bottom" >

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