<?php
return true; //anulado 211019
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
    //if (!$mySecurity-> isAllowedTo('Show Admin Menu')) 
    if (!$mySecurity-> isAllowedTo(1)) 
    	$mySecurity-> GotoThisPage( "login.php" );

  ob_start( );
/*
  #
  # If the user manually enters some accountid, we should check if it does exist
  # originally in their database query......
  #
  if (!array_key_exists ( $_GET['accountId'],
                          $_SESSION['accounts_read_from_table']))
  {
    $mySecurity-> GotoThisPage( "bogus.php" );
  }
*/
  include "header.inc.php";

  $FormElements = $_POST['form_AccountsForm'];
  if ($FormElements["B_clear"])
    unset ($FormElements);

  if ($_GET['mode'] == 'edit')
  {
    if ($mySecurity-> isAllowedTo(1))
    {
      # we can modify the Account information
      if ($FormElements["B_submit"])
      {
        if (!$Accounts-> ErrCheckAccountsForm($FormElements,$_GET['accountId'],$_GET['mode']))
          if ($Accounts-> UpdateAccount($FormElements,$_GET['accountId']))
            $mySecurity-> GotoThisPage( "adminmenu.php" );
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
//     if ($mySecurity-> isAllowedTo('Delete Account'))
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
    $mySecurity-> GotoThisPage( "adminmenu.php" );
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
  }

  if ($_GET['mode'] == 'edit')
    $alsoSendAccountsGroupForm = false;

  echo $Accounts->SendAccountsForm($FormElements,$alsoSendAccountsGroupForm,true);

//  if ($mySecurity-> isAllowedTo('Show Admin Menu'))
  if ($mySecurity-> isAllowedTo(1))
	include "donate.inc.php";

  include "footer.inc.php";

  ob_end_flush( );

  return true;
?>