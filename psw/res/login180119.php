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
echo '<script LANGUAGE="JavaScript">';
echo "function quitarFrame() ";
echo "{";
echo "if (self.parent.frames.length != 0)";
echo "self.parent.location=document.location.href;";
echo "}";
echo "quitarFrame()";
echo "</script>";

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
//reactivar la sig linea y desactivar el div posterior para volver al formato viejo de login  
//  $mySecurity-> PromptLogin($FormElements); 
//	<div id="divAppIconBar"></div>
// echo "<br>";
// echo "<br>";
// echo "<br>";
// echo "<br>";
// echo "<br>";

//echo '  <script type="text/javascript" src="../pngfix.js"></script>';
			
// 		<!-- 
// 			<table  cellspacing="0" cellpadding="2" border="0" align="center">
// 			<tr>
// 				<td rowspan="6" ALIGN="center">
// 					<img src="../images/login-banner.jpg"  height="70%" width="70%"/>
// 				</td>
// 			</tr>
// 			</table>
//  -->
//  <!-- 
// 			<table class="divLoginbox divSideboxEntry" cellspacing="0" cellpadding="2" border="0" >
// 				<tr class="divLoginboxHeader"> 
// 					<td colspan="3">Micronauta</td>
// 				</tr>
// 				<tr>
// 					<td colspan="2" height="20">
// 						<input type="hidden" name="passwd_type" value="text" />
// 						<input type="hidden" name="account_type" value="u" />
// 					</td>
// 					<td rowspan="6">
// 						<img src="../images/password.png" />
// 					</td>
// 				</tr>
// 				<tr>
// 					<td align="right">Nombre de usuario:&nbsp;</td>
// 					<td><input name="form_login[username]" tablindex="1"  size="30" /></td>
// 				</tr>
// 				<tr>
// 					<td align="right">Contraseña:&nbsp;</td>
// 					<td><input name="form_login[password]" type="password" onChange="this.form.submit()" size="30" /></td>
// 				</tr>
// 				<tr>
// 					<td>&nbsp;</td>
// 					<td>
// 						<input type="submit" value="  Entrar  " name="submitit" />
// 					</td>
// 				</tr>
// 			</table>
//  -->
// <div class="w3-content"  >
// <div class="mySlides w3-panel w3-blue w3-card-8 w3-animate-bottom" >
// <p style="margin-left:auto;margin-right:auto;"><i>
// Nueva línea telefónica para soporte 0351-4385037.<br>
// La línea 0810-777-3668 o 0351-4583668 sigue vigente.<br>
// </i></p>
// </div>
// <div class="mySlides w3-panel w3-blue w3-card-8 w3-animate-bottom">
// <p><i>
// Teléfono de guardia para Urgencias fuera de horarios de oficina.<br>
// No se atenderán SMS o Whatsapp.<br>
// El número es 3512305550.
// </i></p>
// </div>
// <img class="mySlides" src="../images/login-banner.jpg"  style="height:35%;width:35%;margin-left:auto;margin-right:auto;"/>
// </div>
// </div>

 ?>
<div class="w3-content"  >
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/imagen1.jpg" style="margin-left:auto;margin-right:auto;">
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/imagen2.jpg" style="margin-left:auto;margin-right:auto;">
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/imagen3.jpg" style="margin-left:auto;margin-right:auto;">
  <img class="mySlides w3-animate-bottom" src="../images/login_banner/contacto.jpg" style="margin-left:auto;margin-right:auto;">
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
		<form method="post" name="login"  class="w3-container w3-card-8  w3-border w3-display-middle" style="max-width:400px;">
			  <h2 class="w3-light-grey">Bienvenido</h2>
						<input type="hidden" name="passwd_type" value="text" />
						<input type="hidden" name="account_type" value="u" />
					<p><label>Usuario</label>
					<input class="w3-input" type="text" name="form_login[username]" size="30" required /></p>
					<p><label>Contraseña</label>
					<input class="w3-input"  name="form_login[password]" type="password" onChange="this.form.submit()" size="30" required/></p>
					<p><button class="w3-btn w3-blue " type="submit" name="submitit">Entrar</button></p> 
     </form>
<?php 
// <!-- 			
// 			<table class="divSidebox divSideboxEntry" cellspacing="0" cellpadding="2" border="0" align="center" >
// 				<tr class="divLoginboxHeader"> 
// 					<td colspan="3">Noticia de Interés</td>
// 				</tr>
// 				<tr>
// 					<td colspan="3" height="20">
// 					Hemos habilitado para soporte otra línea telefónica 0351-4385037.<br>
// 					Dados los reiterados problemas con la linea 0810-777-3668 o 0351-4583668<br>
// 					que aún siguen vigentes.<br>
// 					Disculpe los inconvenientes ocasionados.<br>
// 					Gracias. <br>
// 					</td>
// 				</tr>
// 			</table>
//  -->			
// 			<!-- 
// 			<br>
// 			<table class="divSidebox divSideboxEntry" cellspacing="0" cellpadding="2" border="0" align="center" >
// 				<tr class="divLoginboxHeader"> 
// 					<td colspan="3">Noticia de Interés</td>
// 				</tr>
// 				<tr>
// 					<td colspan="3" height="20">
// 					Ya tenemos línea telefónica fija en funcionamiento nuevamente!.<br>
// 					0810-777-3668 o 0351-4583668.
// 					Disculpe los inconvenientes ocasionados.<br>
// 					Gracias. <br>
// 					</td>
// 				</tr>
// 			</table>
// 			<br>
// 			<br>
// 			<br>
			 
// 			<br>
// 			<table class="divSidebox divSideboxEntry" cellspacing="0" cellpadding="2" border="0" align="center" >
// 				<tr class="divLoginboxHeader"> 
// 					<td colspan="3">Pedido de Repuestos</td>
// 				</tr>
// 				<tr>
// 					<td colspan="3" height="20">
// 					Con el objetivo de acelerar y evitar confusiones al pedir repuestos, <br>
// 					solicitamos hacer el pedido por mail a pedido_repuestos@siscadat.com.ar que es la vía directa.<br>
// 					El listado de repuestos puede consultarlo en Administración - Ayuda - Catálogo de Productos.<br>
// 					Por favor usar los códigos del catálogo para evitar errores en el envío.<br>
// 					Muchas Gracias. <br>
// 					</td>
// 				</tr>
// 			</table>
 			
// 			<br>
// 			<br>
// 			<br>
// 			<br>
// 			<table class="divSidebox divSideboxEntry" cellspacing="0" cellpadding="2" border="0" align="center" >
// 				<tr class="divLoginboxHeader"> 
// 					<td colspan="3">IMPORTANTE !!</td>
// 				</tr>
// 				<tr>
// 					<td colspan="3" height="20">
// 					El día 18 de Diciembre próximo desde las 22.00 hs a las 02.00 hs del día 19, por tareas de mudanza, el servidor estará desconectado.<br>
// 					Por favor agendar principalmente porque no habrá servicio de recarga de tarjetas via PC.<br>
// 					Si tiene algunos equipos de respaldo y no están conectados, le pido que los conecte el día Lunes o Martes para que actualicen, <br> 
// 					de no ser así deberán comunicarse con el servicio técnico. <br>
// 					Disculpe las molestias ocasionadas.<br>
// 					Muchas Gracias.
// 					</td>
// 				</tr>
// 			</table>
// -->

  include "footer.inc.php";
  ob_end_flush( );
  return true;
?>