<?php
  /*
  * @author Bulent Tezcan. bulent@greenpepper.ca
  */
  require_once "Security.class.php";
  require_once "Accounts.class.php";
  session_start();

  $Accounts = new Accounts();
  $mySecurity = new Security( );

  #
  # This should be added in every script. Ofcourse the action name
  # will be different for each script.
  #
//   if ($mySecurity-> isNotAllowedTo('Modify Account') and
//       $mySecurity-> isNotAllowedTo('Delete Account'))
//   {
//     if ($mySecurity-> isAllowedTo('Show Admin Menu'))
//       $mySecurity-> GotoThisPage( "adminmenu.php" );
//     else
//       $mySecurity-> GotoThisPage( "login.php" );
//   }
  if ($mySecurity-> isNotAllowedTo(12) and
  		$mySecurity-> isNotAllowedTo(11))
  {
  	if ($mySecurity-> isAllowedTo(1))
  		$mySecurity-> GotoThisPage( "adminmenu.php" );
  		else
  			$mySecurity-> GotoThisPage( "login.php" );
  }
  
  ob_start( );

  #
  # If the user manually enters some accountid, we should check if it does exist
  # originally in their database query......
  #
  if (!array_key_exists ( $_GET['accountId'],
                          $_SESSION['accounts_read_from_table']))
  {
    $mySecurity-> GotoThisPage( "bogus.php" );
  }

  include "header.inc.php";

  //$FormElements = $_POST['form_AccountsForm'];
  $FormElements = array_merge((array) $_POST['form_AccountsForm'],(array) $_POST['form_accounts_group_information']);

  if ($FormElements["B_clear"])
    unset ($FormElements);

  if ($_GET['mode'] == 'edit')
  {
//    if ($mySecurity-> isAllowedTo('Modify Account'))
if ($mySecurity-> isAllowedTo(12))

    {
      # we can modify the Account information
      if ($FormElements["B_submit"])
      {
        if (!$Accounts-> ErrCheckAccountsForm($FormElements,$_GET['accountId'],$_GET['mode']))
          if ($Accounts-> UpdateAccount($FormElements,$_GET['accountId']))
            $mySecurity-> GoToThisPage( "accountsList.php", "selectlisttype=".$_GET['selectlisttype'] );
      }
      if ($FormElements["B_add_groups"])
      {
        $Accounts-> AddGroupsToAccount($FormElements);
      }
      if ($FormElements["B_remove_groups"])
      {
        $Accounts-> RemoveGroupsFromAccount($FormElements);
      }
    }
    else
      $mySecurity-> GotoNotAuthorized( );
  }
  elseif ($_GET['mode'] == 'delete')
  {
//    if ($mySecurity-> isAllowedTo('Delete Account'))
    if ($mySecurity-> isAllowedTo(11))
	{
      # we can delete the Account
      if ($FormElements["B_submit"]) {
        if ($Accounts-> DeleteAccount($_GET['accountId'])) {
          $mySecurity-> GotoThisPage( "accountsList.php" );
        }
      }
    }
    else
      $mySecurity-> GotoNotAuthorized( );
  }
  else
  {
    $mySecurity-> GotoThisPage( "adminmenu.php" );
  }

  if ($FormElements["B_cancel"])
  {
    $mySecurity-> GotoThisPage( "accountsList.php", "selectlisttype=".$_GET['selectlisttype'] );
  }


  if ($_GET['accountId'] AND $FormElements['firstname'] == "")
  {
    $ObjectResult = $Accounts-> GetAccount($_GET['accountId']);

    $FormElements['accountId'] = $ObjectResult->fields("accountid");
    $FormElements['cliente_id'] = $ObjectResult->fields("cliente_id");
    $FormElements['firstname'] = $ObjectResult->fields("firstname");
    $FormElements['lastname'] = $ObjectResult->fields("lastname");
    $FormElements['initials'] = $ObjectResult->fields("initials");
    $FormElements['username'] = $ObjectResult->fields("username");
    $FormElements['groupid'] = $ObjectResult->fields("groupid");
    $FormElements['email'] = $ObjectResult->fields("email");
    $FormElements['hintquestion'] = $ObjectResult->fields("hintquestion");
    $FormElements['hintanswer'] = $ObjectResult->fields("hintanswer");
    $FormElements['expired'] = $ObjectResult->fields("expired");
    $FormElements['tries'] = $ObjectResult->fields("tries");
    $FormElements['lasttrieddate'] = $ObjectResult->fields("lasttrieddate");
    $FormElements['fecha_desde_reporte'] = $ObjectResult->fields("fecha_desde_reporte");
    $FormElements['validez_desde'] = $ObjectResult->fields("validez_desde");
    $FormElements['validez_hasta'] = $ObjectResult->fields("validez_hasta");
  }

  if ($_GET['mode'] == 'edit')
    $alsoSendAccountsGroupForm = TRUE;

  echo $Accounts->SendAccountsForm($FormElements,$alsoSendAccountsGroupForm);

//   if ($mySecurity-> isAllowedTo('Show Admin Menu'))
  if ($mySecurity-> isAllowedTo(1))
include "donate.inc.php";

  include "footer.inc.php";

  ob_end_flush( );

  return true;
?>