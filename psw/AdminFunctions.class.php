<?php
include_once "securityConfig.inc.php";
require_once "Security.class.php";
include_once "commonFunctions.php";
include_once( ADODB_DIRECTORY."/adodb.inc.php" );

/**
* A class to show the admin functions, and define those actions
* @author Bulent Tezcan
*/

class AdminFunctions{
  /**
  * Constructor of the class AdminFunctions.
  * @public
  */
  function AdminFunctions( ){

    # It is easy to add an item to the admin menu.
    # Just list it in here in the order you want. Thats all.
    #
    $this->mAdminMenu['Creación de Nuevo Usuario']    = "accounts.php";
    $this->mAdminMenu['Lista de Usuarios']            = "accountsList.php";
    $this->mAdminMenu['Usuarios y Permisos'] 		  = "accountAndPermission.php";
    $this->mAdminMenu['Lista de Grupos']                       = "groups.php";
    $this->mAdminMenu['Grupo de Usuarios y Permisos'] = "groupAccountAndPermission.php";
    $this->mAdminMenu['Configuración']                = "configuration.php";
    $this->mAdminMenu['Ver Log']                      = "viewlog.php";
    $this->mAdminMenu['Lista de usuarios y Permisos']  = "rep_listado_permisos.php";
    $this->mAdminMenu['Permisos Eventuales por usuario']          = "accountaction_eventual.php";
    $this->mAdminMenu['Permisos Eventuales por grupo']          = "groupaction_eventual.php";
    $this->mSecurity = new Security( );
    
    # we defined actions for each of them, and we will check
    # if the admin can do those actions.

    $this->mActions['Creación de Nuevo Usuario']    = array(0=>10);
    $this->mActions['Lista de Usuarios']            = array(0=>13,10,11,12);
    $this->mActions['Usuarios y Permisos'] 			= array(0=>23,24);
    $this->mActions['Lista de Grupos']                       = array(0=>6,7,8,9);
    $this->mActions['Grupo de Usuarios y Permisos'] = array(0=>20,21);
    $this->mActions['Configuración']                = array(0=>19);
    $this->mActions['Ver Log']                      = array(0=>18);
    $this->mActions['Lista de usuarios y Permisos'] = array(0=>30);
    $this->mActions['Permisos Eventuales por usuario']          = array(0=>31);
    $this->mActions['Permisos Eventuales por grupo']          = array(0=>32);
    
  }

  /**
  * Method to show the admin menu.
  * @public
  */
  function ShowAdminMenu(){

    $result = array();
    foreach( $this->mAdminMenu as $title=>$scriptName ){
      $actionNames = $this->mActions[$title];
      $canDo = 0;
      foreach ( $actionNames as $actionIndex=>$action_Name ){
        if ($this->mSecurity->isAllowedTo($action_Name))
          $canDo = 1;
      }
      if ($canDo){
        $result[] = array(
          "title" => $title,
          "url" =>  $scriptName
        );
      }

    }

    return array(
      "ok" => true,
      "data" =>  $result
    );
    // $dummy = new PassTru("<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"login.php?mode=logout\" target=\"_top\"> Desconectarse </a>");
    // $dummy-> SetColSpan( $myForm-> GetNumberOfColumns() );
    // $dummy-> SetCellAttributes(array("align"=>"center"));
    // $dummy-> SetClass("");
    // $myForm-> AddFormElementToNewLine ($dummy);
    // echo $myForm-> GetFormInTable();
  }
}
?>