<?php
/*
* @author Bulent Tezcan. bulent@greenpepper.ca
*/

ob_start( );
if ($_POST['submit'] == 'Continuar') {
  require_once "Security.class.php";
  session_start( );
  ob_start( );
  $mySecurity = new Security( );
  $mySecurity-> GotoLoginPage( );
}
#include('ver_posts_y_gets.php');
include "header.inc.php";
?>
<form name="login" action="<?=$SCRIPT_NAME;?>" method="post">

<table class="megacontrolFormTABLE" width="400" cellspacing="1" cellpadding="5" align="center">
  <tr>
    <td colspan="2" class="megacontrolFormHeaderFont" width="100%" align="middle">
      Operación No Autorizada
    </td>
  </tr>
  <tr>
    <td colspan="2" width="100%" class="megacontrolError">
      Verifique que tenga los permisos apropiados y reintente nuevamente
    </td>
  </tr>
  <tr>
    <td align="middle" colspan="2">
    <input class="megacontrolButton" type="submit" name="submit" value="Continuar">
    </td>
  </tr>
</table>
</form>
<?php
  include "footer.inc.php";
  ob_end_flush( );
  return true;
?>