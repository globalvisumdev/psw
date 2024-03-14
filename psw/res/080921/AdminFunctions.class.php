<?php
include_once "securityConfig.inc.php";
require_once "Security.class.php";
include_once "commonFunctions.php";
include_once( ADODB_DIRECTORY."/adodb.inc.php" );
require_once "Form.class.php";

/**
* A class to show the admin functions, and define those actions
* @author Bulent Tezcan
*/

class AdminFunctions
{
  /**
  * Constructor of the class AdminFunctions.
  * @public
  */
  function AdminFunctions( )
  {
    # It is easy to add an item to the admin menu.
    # Just list it in here in the order you want. Thats all.
    #
    $this->mAdminMenu['Creación de Nuevo Usuario']    = "accounts.php";
    $this->mAdminMenu['Lista de Usuarios']            = "accountsList.php";
    $this->mAdminMenu['Usuarios y Permisos'] 		  = "AccountAndPermission.php";
    $this->mAdminMenu['Grupos']                       = "groups.php";
    $this->mAdminMenu['Grupo de Usuarios y Permisos'] = "groupAccountAndPermission.php";
    $this->mAdminMenu['Acciones']                     = "actions.php";
    $this->mAdminMenu['Actividades']                  = "activity.php";
    $this->mAdminMenu['Configuración']                = "configuration.php";
    $this->mAdminMenu['Ver Log']                      = "viewlog.php";
    $this->mAdminMenu['Lista de usuarios y Permisos']  = "rep_listado_permisos.php";
    $this->mAdminMenu['Permisos Eventuales']          = "accountaction_eventual.php";
    $this->mAdminMenu['MD5']                          = "md5.php";
    $this->mAdminMenu['Cambiar mis datos']            = "accountsModifypass.php?accountId=".$_SESSION['myAccount']."&mode=edit&selectlisttype=$selectlisttype";
    
    # we defined actions for each of them, and we will check
    # if the admin can do those actions.

//     $this->mActions['Creación de Nuevo Usuario']    = array(0=>"Add Account");
//     $this->mActions['Lista de Usuarios']            = array(0=>"View account","Add Account","Delete Account","Modify Account");
//     $this->mActions['Usuarios y Permisos'] 			= array(0=>"Insert Account Actions", "Delete Account Actions");
//     $this->mActions['Grupos']                       = array(0=>"Add group","Delete group","View group","Modify group");
//     $this->mActions['Grupo de Usuarios y Permisos'] = array(0=>"Insert Group Accounts", "Delete Group Accounts");
//     $this->mActions['Acciones']                     = array(0=>"Add Action","Delete Action", "Modify Action", "View Action");
//     $this->mActions['Actividades']                  = array(0=>"Add Activity","Delete Activity", "Modify Activity", "View Activity");
//     $this->mActions['Configuración']                = array(0=>"Modify config");
//     $this->mActions['Ver Log']                      = array(0=>"View Log");
//     $this->mActions['MD5']                          = array(0=>"View MD5");
//     $this->mActions['Cambiar mis datos']            = array(0=>"Show Admin Menu");

    $this->mActions['Creación de Nuevo Usuario']    = array(0=>10);
    $this->mActions['Lista de Usuarios']            = array(0=>13,10,11,12);
    $this->mActions['Usuarios y Permisos'] 			= array(0=>23,24);
    $this->mActions['Grupos']                       = array(0=>6,7,8,9);
    $this->mActions['Grupo de Usuarios y Permisos'] = array(0=>20,21);
    $this->mActions['Acciones']                     = array(0=>2,3,4,5);
    $this->mActions['Actividades']                  = array(0=>14,15,16,17);
    $this->mActions['Configuración']                = array(0=>19);
    $this->mActions['Ver Log']                      = array(0=>18);
    $this->mActions['Lista de usuarios y Permisos'] = array(0=>30);
    $this->mActions['Permisos Eventuales']          = array(0=>31);
    $this->mActions['MD5']                          = array(0=>22);
    $this->mActions['Cambiar mis datos']            = array(0=>1);
    
    $this->mSecurity = new Security( );
  }

  /**
  * Method to show the admin menu.
  * @public
  */
  function ShowAdminMenu($FormElements)
  {
    $myForm = new Form("adminmenu");
    $myForm-> SetNumberOfColumns( 2 );
    $myForm-> SetCellSpacing( 1 );
    $myForm-> SetCellPadding( 4 );
    $myForm-> SetBorder ( 0 );
    $myForm-> SetAlign ("center");
    $myForm-> SetTableWidth ("400");
    $myForm-> SetTableHeight (null);
    $myForm-> SetCSS ( $_SESSION["CSS"] );
    $myForm-> SetEmptyCells (true);
    $myForm-> SetFormHeader("Menú de Administración de Seguridad");
    #$myForm-> SetTRMouseOverColor( $overcolor="#FFFF66", $outcolor="#ffcc44", $startingRow=2 );
    $self = basename($_SERVER['PHP_SELF']);

    $myForm-> SetAction($self);
    $myForm-> SetErrorMessage($FormElements['__error']);
    foreach( $this->mAdminMenu as $title=>$scriptName )
    {
      $actionNames = $this->mActions[$title];
      $canDo = 0;
      foreach ( $actionNames as $actionIndex=>$action_Name )
        if ($this->mSecurity->isAllowedTo($action_Name))
          $canDo = 1;
      if ($canDo)
      {
        $myForm-> AddFormElement(new Label($name="lbl",$value="&nbsp;&nbsp;&nbsp;"));
        $value = "<a class=\"" .$_SESSION["CSS"] ."DataLink\" href=\"$scriptName\">$title</a>";
        $passtru = new PassTru($value);
        $passtru->SetClass("DataTD");
        $myForm-> AddFormElement  ($passtru);
      }
    }
    $dummy = new PassTru("<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"login.php?mode=logout\" target=\"_top\"> Desconectarse </a>");
    $dummy-> SetColSpan( $myForm-> GetNumberOfColumns() );
    $dummy-> SetCellAttributes(array("align"=>"center"));
    $dummy-> SetClass("");
    $myForm-> AddFormElementToNewLine ($dummy);
    echo $myForm-> GetFormInTable();
  }
}
?>