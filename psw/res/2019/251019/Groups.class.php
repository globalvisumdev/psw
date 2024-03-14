<?php
include_once "securityConfig.inc.php";
require_once "Security.class.php";
include_once "commonFunctions.php";
include_once( ADODB_DIRECTORY."/adodb.inc.php" );
require_once "Form.class.php";
require_once "MyDatabase.class.php";

session_start( );

/**
* A class that handles adding, modifying and deleting the groups
* from the table.
* @author Bulent Tezcan. bulent@greenpepper.ca
*/

class Groups extends MyDatabase
{
  /**
  * Constructor of the class Groups.
  * @public
  */
  function Groups( )
  {

    // set the table properties
    $this->mTableName = "groups";
    $this->mKeyName = "groupid";

    // set the Column Properties. These are required to be able to
    // write the table
    $this->mTableFields['groupid']  ['type']    = "integer";
    $this->mTableFields['groupname']['type']    = "string";
    $this->mTableFields['groupname']['unique']  = TRUE;
    $this->mTableFields['hierarchy']['type']    = "integer";
    $this->mTableFields['cliente_id']['type']    = "integer";
    
    // set other properties
    $this->mFormName = "GroupsForm";
    $this->mExtraFormText = "";

    $this->mySecurity = new Security( );

    // Set up database connection
    $this->MyDatabase();
  }
  /**
  * Method to get the form name.
  * @public
  * @returns string
  */
  function GetFormName( )
  {
    return $this->mFormName;
  }

  /**
  * Method to get the group information with a given key.
  * @public
  * @returns array
  */
  function GetGroup($key)
  {
    $sql = "SELECT * FROM groups WHERE groupid=$key and cliente_id='".$_SESSION['cliente_id']."'";
  

    $result = $this->gDB->Execute($sql);

    if ($result === false)
    {
      $this->SetErrorMessage('error reading: '
                            .$this->gDB->ErrorMsg( ));

      if ($_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

      return false;
    }

    return $result;
  }

  /**
  * Method to get the group name with a given key.
  * @public
  * @returns string
  */
  function GetGroupName($key)
  {
    $sql = "SELECT groupname FROM groups WHERE groupid=$key and cliente_id='".$_SESSION['cliente_id']."'";

    $result = $this->gDB->Execute($sql);

    if ($result === false)
    {
      $this->SetErrorMessage('error reading: '
                            .$this->gDB->ErrorMsg( ));

      if ($_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

      return false;
    }

    return $result->fields("groupname");
  }

  /**
  * Method to get the group information with a given name.
  * @public
  * @returns array
  */
  function GetGroupIdByName($name)
  {
    $sql = "SELECT * FROM groups WHERE groupname='".$name."' and cliente_id='".$_SESSION['cliente_id']."'";

    $result = $this->gDB->Execute($sql);

    if ($result === false)
    {
      $this->SetErrorMessage('error reading: '
                            .$this->gDB->ErrorMsg( ));

      if ($_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

      return false;
    }

    return $result->fields("groupid");
  }

  /**
  * Method to check if there are any users under this group.
  * @public
  * @returns array
  */
  function isAnyUserForThisGroup($groupId)
  {
    $sql = "SELECT count(*) FROM groupaccounts WHERE groupid=".$groupId;

    $result = $this->gDB->Execute($sql);

    if ($result === false)
    {
      $this->SetErrorMessage('error reading: '
                            .$this->gDB->ErrorMsg( ));

      if ($_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

      return false;
    }

    return $result->fields("count");
  }

  /**
  * Method to add actions to groupactions table.
  * @public
  * @returns bool
  */
  function AddGroupActions($FormElements)
  {
    if (count($FormElements["allactions"]))
    {
      # Check for the bogus actions
      #
      foreach( $FormElements["allactions"] as $key=>$actionid)
      {
        if (!array_key_exists ( $actionid,
                          $_SESSION['available_actions']))
        {
          $this->mySecurity-> GotoThisPage( "bogus.php" );
        }
      }

      $this->gDB->BeginTrans();

      foreach( $FormElements["allactions"] as $key=>$actionid)
      {
        $sql = "INSERT INTO groupactions (groupid,actionid) "
        ."      VALUES(" .$FormElements["groupid"] .","
        .$actionid  .")";

        $result = $this->gDB->Execute($sql);

        if ($result === false)
        {
          $this->gDB->RollbackTrans();

          if ($_SESSION['IS_ERROR_REPORTING'])
            $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

          return false;
        }
      }

      $this->gDB->CommitTrans();
    }
    return true;
  }

  /**
  * Method to remove actions from groupactions table.
  * @public
  * @returns bool
  */
  function RemoveGroupActions($FormElements)
  {
    if (count($FormElements["groupactions"]))
    {
      # Check for the bogus actions
      #
      foreach( $FormElements["groupactions"] as $key=>$actionid)
      {
        if (!array_key_exists ( $actionid,
                          $_SESSION['group_actions']))
        {
          $this->mySecurity-> GotoThisPage( "bogus.php" );
        }
      }

      $this->gDB->BeginTrans();

      foreach( $FormElements["groupactions"] as $key=>$actionid)
      {
        $sql = "DELETE FROM groupactions "
        ."      WHERE groupid =" .$FormElements["groupid"] ." AND "
        ."            actionid=" .$actionid;

        $result = $this->gDB->Execute($sql);

        if ($result === false)
        {
          $this->gDB->RollbackTrans();

          if ($_SESSION['IS_ERROR_REPORTING'])
            $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

          return false;
        }
      }

      $this->gDB->CommitTrans();
    }
    return true;
  }

  /**
  * Method to add accounts to groupaccounts table.
  * @public
  * @returns bool
  */
  function AddGroupAccounts($FormElements)
  {
    if (count($FormElements["allAccountsExceptGroup"]))
    {
      # Check for the bogus accounts
      #
      foreach( $FormElements["allAccountsExceptGroup"] as $key=>$accountid)
      {
        if (!array_key_exists ( $accountid,
                          $_SESSION['available_accounts']))
        {
          $this->mySecurity-> GotoThisPage( "bogus.php" );
        }
      }

      $this->gDB->BeginTrans();

      foreach( $FormElements["allAccountsExceptGroup"] as $key=>$accountid)
      {
        $sql = "INSERT INTO groupaccounts (groupid,accountid) "
              ."VALUES(".$FormElements['groupid'].",".$accountid.")";

        if ($result = $this->gDB->Execute($sql) === false)
        {
          $this->gDB->RollbackTrans();

          if ($_SESSION['IS_ERROR_REPORTING'])
            $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

          return false;
        }
      }

      $this->gDB->CommitTrans();
    }

    return true;
  }

  /**
  * Method to remove accounts from groupaccounts table.
  * @public
  * @returns bool
  */
  function RemoveGroupAccounts($FormElements)
  {
    if (count($FormElements["groupaccounts"]))
    {
      # Check for the bogus accounts
      #
      foreach( $FormElements["groupaccounts"] as $key=>$accountid)
      {
        if (!array_key_exists ( $accountid,
                          $_SESSION['group_accounts']))
        {
          $this->mySecurity-> GotoThisPage( "bogus.php" );
        }
      }

      $this->gDB->BeginTrans();

      foreach( $FormElements["groupaccounts"] as $key=>$accountid)
      {
        $sql = "DELETE FROM groupaccounts "
        ."      WHERE groupid =" .$FormElements["groupid"] ." AND "
        ."            accountid=" .$accountid;

        if ($result = $this->gDB->Execute($sql) === false)
        {
          $this->gDB->RollbackTrans();

          if ($_SESSION['IS_ERROR_REPORTING'])
            $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

          return false;
        }
      }

      $this->gDB->CommitTrans();
    }

    return true;
  }
  
  /**
  * Method to add empresas to groupempresa table.
  * @public
  * @ Pablo 310809
  * @returns bool
  */
  function AddGroupEmpresas($FormElements)
  {
    if (count($FormElements["allEmpresasExceptGroup"]))
    {
      # Check for the bogus empresas
      #
      foreach( $FormElements["allEmpresasExceptGroup"] as $key=>$empresaid)
      {
        if (!array_key_exists ( $empresaid,
                          $_SESSION['available_empresas']))
        {
          $this->mySecurity-> GotoThisPage( "bogus.php" );
        }
      }

      $this->gDB->BeginTrans();

      foreach( $FormElements["allEmpresasExceptGroup"] as $key=>$empresaid)
      {
        $sql = "INSERT INTO groupempresa (groupid,empresaid) "
              ."VALUES(".$FormElements['groupid'].",".$empresaid.")";

        if ($result = $this->gDB->Execute($sql) === false)
        {
          $this->gDB->RollbackTrans();

          if ($_SESSION['IS_ERROR_REPORTING'])
            $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

          return false;
        }
      }

      $this->gDB->CommitTrans();
    }

    return true;
  }

  /**
  * Method to remove empresas from groupempresa table.
  * @public
  * @Pablo 310809
  * @returns bool
  */
  function RemoveGroupEmpresas($FormElements)
  {
    if (count($FormElements["groupempresa"]))
    {
      # Check for the bogus accounts
      #
      foreach( $FormElements["groupempresa"] as $key=>$empresaid)
      {
        if (!array_key_exists ( $empresaid,
                          $_SESSION['group_empresas']))
        {
          $this->mySecurity-> GotoThisPage( "bogus.php" );
        }
      }

      $this->gDB->BeginTrans();

      foreach( $FormElements["groupempresa"] as $key=>$empresaid)
      {
        $sql = "DELETE FROM groupempresa "
        ."      WHERE groupid =" .$FormElements["groupid"] ." AND "
        ."            empresaid=" .$empresaid;

        if ($result = $this->gDB->Execute($sql) === false)
        {
          $this->gDB->RollbackTrans();

          if ($_SESSION['IS_ERROR_REPORTING'])
            $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

          return false;
        }
      }

      $this->gDB->CommitTrans();
    }

    return true;
  }
  
  /**
  * Method to remove accounts from groupaccounts table for a specific group.
  * @public
  * @returns bool
  */
  function RemoveGroupAccountsForGroup($groupid)
  {
    if ($groupid and $groupid != 1)
    {
      $sql = "DELETE FROM groupaccounts "
      ."      WHERE groupid = $groupid";

      if ($result = $this->gDB->Execute($sql) === false)
      {
        if ($_SESSION['IS_ERROR_REPORTING'])
          $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

        return false;
      }
    }

    return true;
  }

  /**
  * Method to delete group from the table with a given key.
  * @public
  * @returns bool
  */
  function DeleteGroup($groupId)
  {
    # we dont want to delete the admins group
    # please make sure admins group stays as groupid 1
    if ($groupid == 1)
      return false;

    $this->gDB->BeginTrans();

    $sql = "DELETE FROM groups WHERE groupid=$groupId  and cliente_id='".$_SESSION['cliente_id']."'";

    if ($this->gDB->Execute($sql) === false)
    {
      $this->mErrorMessage = 'error deleting: '
                          .$this->gDB->ErrorMsg( );

      $this->gDB->RollbackTrans();

      if ($_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

      return false;
    }
    else
      $this->mErrorMessage = "Grupo borrado exitosamente.";

    #
    # Now since the group is deleted we should remove all the accounts
    # from the groupaccounts table, so we wo't leave any garbage behind.
    #

    if ($this-> RemoveGroupAccountsForGroup($groupId) === false)
    {
      $this->gDB->RollbackTrans();
      return false;
    }

    $this->gDB->CommitTrans();

    return true;
  }

  /**
  * Method to update groups table with a given key, fields and values.
  * @public
  * @returns bool
  */
  function UpdateGroup($FormElements,$groupId)
  {
    if (!$this->ErrCheckGroupsForm($FormElements,$groupId))
    {
      $this->Field($this->mKeyName,$groupId);

      foreach ($this->mTableFields as $key =>$value)
      {
        if ($key <> $this->mKeyName)
        {
          if ($FormElements[$key])
            $this->Field($key,htmlspecialchars($FormElements[$key]));
        }
      }

      return $this-> Update( );
    }
    else
      return false;
  }

  /**
  * Method to add groups to the table.
  * @public
  * @returns bool
  */
  Function AddGroup($FormElements)
  {
  	$FormElements['hierarchy']= 4;
  	$FormElements['cliente_id']= $_SESSION['cliente_id'];
  	 
    if (!$this->ErrCheckGroupsForm($FormElements,null))
    {
      foreach ($this->mTableFields as $key =>$value)
      {
        if ($key <> $this->mKeyName)
        {
          if ($FormElements[$key])
            $this->Field($key,htmlspecialchars($FormElements[$key]));
        }
      }

      if ($this-> InsertNew() )
        $this->mErrorMessage = "Grupo añadido exitosamente.";
      else
        return false;
    }

    return true;
  }

  /**
  * Method to get the actions that the group doesn't have.
  * @public
  * @returns string
  */
  Function GetAllActionsExceptGroupHas($groupid)
  {
    $sql = "SELECT actionid from groupactions WHERE groupid=".$groupid." order by 1";

    $result = $this->gDB->Execute($sql);

    if ($result === false)
    {
      $this->mErrorMessage = 'error reading: '
                          .$this->gDB->ErrorMsg( );

      if ($_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

    }

      $string = null;

      while (!$result->EOF)
      {
        if ($string)
          $string .= ",";
        $string .= $result->fields("actionid");

        $result->MoveNext( );
      }

      return $string;
  }

  /**
  * Method to get the accounts that the group doesn't have.
  * @public
  * @returns string
  */
  Function GetAllAccountsExceptGroupHas($groupid)
  {
    $sql = "SELECT accountid from groupaccounts WHERE groupid=".$groupid;

    $result = $this->gDB->Execute($sql);

    if ($result === false)
    {
      $this->mErrorMessage = 'error reading: '
                          .$this->gDB->ErrorMsg( );

      if ($_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

    }

      # make sure we put the admin account which is one, by default
      # so we dont see it.

      $string = "1"; # this is the admin account

      while (!$result->EOF)
      {
        if ($string)
          $string .= ",";
        $string .= $result->fields("accountid");

        $result->MoveNext( );
      }

      return $string;
  }
  /**
  * Method to get the emrpesas that the group doesn't have.
  * @public
  * @returns string
  */
  Function GetAllEmpresasExceptGroupHas($groupid)
  {
    $sql = "SELECT empresaid from groupempresa WHERE groupid=".$groupid;

    $result = $this->gDB->Execute($sql);

    if ($result === false)
    {
      $this->mErrorMessage = 'error reading: '
                          .$this->gDB->ErrorMsg( );

      if ($_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

    }

      # make sure we put the admin account which is one, by default
      # so we dont see it.

      $string = "0"; # this is la empresa de usuarios

      while (!$result->EOF)
      {
        if ($string)
          $string .= ",";
        $string .= $result->fields("empresaid");

        $result->MoveNext( );
      }

      return $string;
  }
  
  /**
  * Method to set the error message.
  * @public
  * @returns bool
  */
  function SetErrorMessage( $message )
  {
    if (is_string($message))
    $this-> mErrorMessage = $message;
    return true;
  }

  /**
  * Method to get the error message.
  * @public
  * @returns string
  */
  function GetErrorMessage( )
  {
    return $this-> mErrorMessage;
  }

  /**
  * Method to display all the groups.
  * @private
  * @returns string
  */
  function ListGroups( )
  {
  	//empresa 1 puede ver los cliente=0 (comun)
    $sql = "SELECT * FROM groups WHERE hierarchy >= ".$_SESSION['myHierarchy']." and (cliente_id='".$_SESSION['cliente_id']."'
    		or cliente_id = 0 
    		 )
    		 ORDER BY hierarchy,groupname";
    //or (cliente_id=0 and ".$_SESSION['cliente_id']."=1 )
    
    $result = &$this->gDB->Execute($sql);

    if ($result === false   AND $_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql );

    if (!$result->EOF)
    {
      $this->SendHeader( );

      $_SESSION['groups_read_from_table'] = "";

      $groups_read_from_table = array();

      while (!$result->EOF)
      {
        $listar = "&nbsp;";
      	$edit = "&nbsp;";
        $delete = "&nbsp;";

        $groups_read_from_table[$result->fields("groupid")] = $result->fields("groupid");

        $listar = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"groupList.php?groupId=".$result->fields("groupid")
        ."&groupNombre=".$result->fields("groupname")."&mode=listar\">Usuarios</a>";

        if ($result->fields("cliente_id") == $_SESSION['cliente_id']) {  
        # Edit
//         if ($this->mySecurity-> isAllowedTo('Modify Group'))
        if ($this->mySecurity-> isAllowedTo(8))
		$edit = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"groupsModify.php?groupId=".$result->fields("groupid")
        ."&mode=edit\">Editar</a>";
        # Delete
        # groupid: 1 should always be the admins
//         if ($this->mySecurity-> isAllowedTo('Delete Group') AND
        if ($this->mySecurity-> isAllowedTo(7) AND
        $result->fields("groupid") != $_SESSION['myGroupId'] AND
            $result->fields("groupid") != 1)
        $delete = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"groupsModify.php?groupId=".$result->fields("groupid")
        ."&mode=delete\">Borrar</a>";
        }
        $passtru = new PassTru();
        $passtru->SetValue($value=$result->fields("groupname"));
        $passtru->SetClass("DataTD");
        $this->myForm-> AddFormElementToNewLine($passtru);

        $passtru = new PassTru(); //Anadido Pablo 10/2008
        $passtru->SetValue($value=$result->fields("hierarchy"));
        $passtru->SetClass("DataTD");
        $this->myForm-> AddFormElement($passtru);

        $passtru = new PassTru(); //Anadido Pablo 09/2017
        $passtru->SetValue($listar);
        $passtru->SetClass("DataTD");
        $this->myForm-> AddFormElement($passtru);
        
        $passtru = new PassTru(); //Anadido Pablo 10/2008
        $passtru->SetValue($edit);
        $passtru->SetClass("DataTD");
        $this->myForm-> AddFormElement($passtru);

        $passtru = new PassTru(); //Anadido Pablo 10/2008
        $passtru->SetValue($delete);
        $passtru->SetClass("DataTD");
        $this->myForm-> AddFormElement($passtru);

        $result->MoveNext( );
      }

      # the reason of this one is to prevent people, sending bogus
      # groups rom the URL. We will match the groupid coming from the URL
      # with the one in session.

      $_SESSION['groups_read_from_table'] = $groups_read_from_table;

      $passtru = new PassTru("");
      $value = "<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"adminmenu.php\"><-- Volver al Menú Seguridad</a>";
      $passtru-> SetClass("");
      $passtru-> SetColSpan( $this->myForm-> GetNumberOfColumns() );
      $passtru-> SetCellAttributes(array('align'=>'left'));
      $passtru-> SetValue( $value );

      $this->myForm-> AddFormElement  ($passtru);

      $this->SendTrailer( );
    }
  }

  /**
  * Method to send the form. The form is displayed within the method with echo.
  * @private
  * @returns string
  */
  function SendGroupsForm($FormElements,$mode)
  {
    $myForm = new Form($this->GetFormName( ));

    $myForm-> SetNumberOfColumns( 2 );
    $myForm-> SetCellSpacing( 1 );
    $myForm-> SetCellPadding( 5 );
    $myForm-> SetBorder ( 0 );
    $myForm-> SetAlign ("center");
    $myForm-> SetTableWidth ("500");
    $myForm-> SetTableHeight (null);
    $myForm-> SetCSS ( $_SESSION["CSS"] );
    $myForm-> SetEmptyCells (true);

    if ($mode == 'edit')
      $actionMode = ' Modificando ';
    elseif ($mode == 'delete')
      $actionMode = ' Borrando ';

    $myForm-> SetFormHeader($actionMode."Grupo");

    $myForm-> AddFormElementToNewLine (new Label("lb1","Nombre del Grupo :"));
    $myForm-> AddFormElement (new TextField("groupname",$FormElements["groupname"],30,50));

//    $myForm-> AddFormElementToNewLine (new Label("lb1","Jerarquía del Grupo :"));
 //   $myForm-> AddFormElement (new TextField("hierarchy",$FormElements["hierarchy"],2,2,true));
    $myForm-> AddFormElement (new Hidden("hierarchy",$FormElements["hierarchy"]));
    switch ( TRUE )
    {
      case "EDIT" == strtoupper($mode):

//         if ($this->mySecurity-> isAllowedTo('Modify Group'))
        if ($this->mySecurity-> isAllowedTo(8))
		{
          $buttons = new ObjectArray("buttons");
          $buttons->AddObject(new SubmitButton("B_submit","Confirmar"));
          $buttons->AddObject(new SubmitButton("B_cancel","Cancelar"));
          $buttons->SetColSpan( $myForm-> GetNumberOfColumns() );
          $buttons->SetCellAttributes(array("align"=>"middle"));

          $myForm-> AddFormElement ($buttons);
        }
        break;

      case "DELETE" == strtoupper($mode):

//         if ($this->mySecurity-> isAllowedTo('Delete Group'))
        if ($this->mySecurity-> isAllowedTo(7))
		{
          if ($this->isAnyUserForThisGroup($FormElements["groupId"]))
            $this-> SetErrorMessage("Por favor, asegúrese que no haya usuarios en este grupo.");

          $buttons = new ObjectArray("buttons");
          $buttons->AddObject(new SubmitButton("B_submit","Confirma el borrado"));
          $buttons->AddObject(new SubmitButton("B_cancel","Cancelar"));
          $buttons->SetColSpan($myForm-> GetNumberOfColumns());
          $buttons->SetCellAttributes(array("align"=>"middle"));

          $myForm-> AddFormElement ($buttons);
        }

        break;

      default:

//         if ($this->mySecurity-> isAllowedTo('Add Group'))
        if ($this->mySecurity-> isAllowedTo(6))
		{
          $buttons = new ObjectArray("buttons");
          $buttons->AddObject(new SubmitButton("B_add_submit","Añade Nuevo Grupo"));
          $buttons->AddObject(new SubmitButton("B_clear","Limpiar"));
          $buttons->SetColSpan( $myForm-> GetNumberOfColumns() );
          $buttons->SetCellAttributes(array("align"=>"middle"));

          $myForm-> AddFormElement  ($buttons);
        }
    }

    $myForm-> SetErrorMessage($this->GetErrorMessage());

    return $myForm->GetFormInTable( );
  }

  /**
  * Method to send the group accounts and actions form.
  * @private
  * @returns string
  */
  function SendGroupAccountsAndActionsForm($FormElements)
  {
    $myForm = new Form("group_accounts_and_actions");

    $myForm-> SetNumberOfColumns( 4 );
    $myForm-> SetCellSpacing( 1 );
    $myForm-> SetCellPadding( 5 );
    $myForm-> SetBorder ( 0 );
    $myForm-> SetAlign ("center");
    $myForm-> SetTableWidth ("50%");
    $myForm-> SetTableHeight (null);
    $myForm-> SetCSS ( $_SESSION["CSS"] );
    $myForm-> SetEmptyCells (false);
    $myForm-> SetFormHeader("Grupo de Usuarios y Acciones");

    $myForm-> SetErrorMessage($this->mErrorMessage);

    $myForm-> AddFormElement  (new Label("lb1","Grupo :"));

    $extraSql="WHERE hierarchy >=".$_SESSION['myHierarchy']."
    				and groupid <> 1
    				and (cliente_id='".$_SESSION['cliente_id']."'
    						or (cliente_id=0 and ".$_SESSION['myHierarchy']." = 1 ) )
    		
    		ORDER BY hierarchy";
    $getoptions = new GetOptions($name="groupid",$table="groups",$field="groupname",$key="groupid",$extraSql, $selected=$FormElements["groupid"], $default="-Select-",  $displayonly=$this->mDisplayOnly, $size=0,$multiple=FALSE,$extra="onchange=\"".$myForm->GetFormName( ).".submit();\"");

    $myForm-> AddFormElement  ($getoptions);
    $myForm-> AddFormElement  (new Dummy( ));

    $buttons = new ObjectArray("buttons");
    $buttons->AddObject(new SubmitButton("B_bring_group","Ir"));
    $buttons->AddObject(new SubmitButton("B_cancel","Cancelar"));
    $buttons->SetCellAttributes(array("align"=>"left"));

    $myForm-> AddFormElement  ($buttons);

    $myForm-> AddFormElementToNewLine (new Dummy( ));


    if ($FormElements["groupid"])
    {
      $groupName = $this-> GetGroupName($FormElements["groupid"]);

      $mylabel = new Label($name="lb1",$value="Acciones para el Grupo $groupName");
      $mylabel-> SetClass("ColumnTD");
      $mylabel-> SetCellAttributes( array("colspan"=>2) );

      $myForm-> AddFormElementToNewLine ($mylabel);

      $myForm-> AddFormElement  (new Dummy( ));

      $mylabel = new Label($name="lb1",$value="Acciones Disponibles"); //Pablo 280809
      $mylabel-> SetClass("ColumnTD");//Pablo 280809
      
//      $mylabel-> SetValue("Acciones Disponibles");  //Pablo 280809
//      $mylabel-> SetCellAttributes( "" );//Pablo 280809
      $myForm-> AddFormElement($mylabel);
      
      $myForm-> AddFormElementToNewLine (new Label("lb1","Acciones del Grupo :<br><br><hr><font style='font-size:8pt; font-style:italic;'>Puede seleccionar varias</font>"));

      $groupActions = new GetOptions("groupactions","actions","actionname","actionid","ORDER BY actionname", $FormElements["groupactions"], $default="-Select-",  $displayonly=$this->mDisplayOnly,$size=10,$multiple=TRUE,$extra="",$concat="");

      # This was the original code, but didn't work for MySQL.
      #
      #$SQL = "SELECT * FROM actions WHERE actionid in"
      #. " (SELECT actionid FROM groupactions "
      #. " WHERE groupid=".$FormElements["groupid"].")";

      $SQL = "SELECT a.* FROM actions a "
            ." LEFT JOIN groupactions ga ON a.actionid=ga.actionid "
            ." WHERE groupid=".$FormElements["groupid"]."
            		order by actionname";

      #
      # Store this information in session, so we wont get bogus
      #
      $result2 = $this->gDB->Execute($SQL);

      if ($result2 === false)
      {
        $this->SetErrorMessage('error reading: '
                              .$this->gDB->ErrorMsg( ));

        if ($_SESSION['IS_ERROR_REPORTING'])
          $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

        return false;
      }

      $_SESSION['group_actions'] = "";
      $group_Actions = array();

      while (!$result2->EOF)
      {
        $group_Actions[$result2-> fields('actionid')] = $result2-> fields('actionid');
        $result2->MoveNext( );
      }

      $_SESSION['group_actions'] = $group_Actions;

      #
      # end of Store this information in session, so we wont get bogus
      #

      $groupActions-> SetSQL($SQL);

      $groupActions-> SetZebraColor("#EBEBEB");

      $myForm-> AddFormElement($groupActions);

      $buttons = new ObjectArray("buttons");
      $buttons->AddObject(new SubmitButton( "B_remove_actions",">>",$class="b1"));
      $buttons->AddObject(new Label("lbl","<br>"));
      $buttons->AddObject(new SubmitButton( "B_add_actions","<<",$class="b1"));
      $buttons->SetCellAttributes(array("align"=>"middle","valign"=>"middle"));

      $myForm-> AddFormElement  ($buttons);

      $allActionsExceptGroup = new GetOptions("allactions","actions","actionname","actionid","ORDER BY actionname", $FormElements["allactions"], $default="-Select-",  $displayonly=$this->mDisplayOnly,$size=10,$multiple=TRUE,$extra="",$concat="");

      # This was the original code, but didn't work for MySQL.
      #
      #$SQL = "SELECT * FROM actions WHERE actionid not in "
      #."(SELECT actionid FROM groupactions WHERE #groupid=".$FormElements["groupid"].")";

      $actions = $this-> GetAllActionsExceptGroupHas($FormElements["groupid"]);

      if ($actions)
        $SQL = "SELECT * FROM actions WHERE actionid NOT IN "
              ."($actions)";
      else
        $SQL = "SELECT * FROM actions where 1= 1";

        $SQL .=" and (actionclase = 0 or ".$_SESSION['myHierarchy']."=1)";
        $SQL .= " order by actionname";

      #
      # Store this information in session, so we wont get bogus
      #
      $result2 = $this->gDB->Execute($SQL);

      if ($result2 === false)
      {
        $this->SetErrorMessage('error reading: '
                              .$this->gDB->ErrorMsg( ));

        if ($_SESSION['IS_ERROR_REPORTING'])
          $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

        return false;
      }

      $_SESSION['available_actions'] = "";
      $availableActions = array();

      while (!$result2->EOF)
      {
        $availableActions[$result2-> fields('actionid')] = $result2-> fields('actionid');
        $result2->MoveNext( );
      }

      $_SESSION['available_actions'] = $availableActions;

      #
      # end of Store this information in session, so we wont get bogus
      #


      $allActionsExceptGroup-> SetSQL($SQL);

      $allActionsExceptGroup-> SetZebraColor("#EBEBEB");

      $myForm-> AddFormElement($allActionsExceptGroup);

      if ($_SESSION['myHierarchy'] == 1) {
      $myForm-> AddFormElementToNewLine (new Dummy( ));
      
      $mylabel = new Label($name="lb1",$value="Usuarios del Grupo $groupName"); //Pablo 280809
//    $mylabel-> SetValue("Usuarios del Grupo $groupName");  //Pablo 280809
      $mylabel-> SetClass("ColumnTD");
      $mylabel-> SetCellAttributes( array("colspan"=>2) );

      $myForm-> AddFormElementToNewLine ($mylabel);
      $myForm-> AddFormElement  (new Dummy( ));

      $mylabel = new Label($name="lb1",$value="Usuarios Disponibles");
      $mylabel-> SetClass("ColumnTD");

      $myForm-> AddFormElement  ($mylabel);


      $myForm-> AddFormElementToNewLine (new Label("lb1","Usuarios del Grupo:<br><br><hr><font style='font-size:8pt; font-style:italic;'>Puede seleccionar varios</font>"));

      $groupAccounts = new GetOptions($name="groupaccounts",$table="accounts",$field="name",$key="accountid",$xtrasql="",$selected=$FormElements["groupaccounts"], $default="-Select-",  $displayonly=$this->mDisplayOnly,$size=10,$multiple=TRUE,$extra="",$concat=array(0=>"lastname","firstname"));

      # This was the original code, but didn't work for MySQL.
      #
      #$groupAccounts-> SetSQL("SELECT accountid, '('||lastname||', '||firstname||') '||username AS name FROM accounts WHERE accountid in (SELECT accountid FROM groupaccounts WHERE groupid=".$FormElements["groupid"].") AND accountid > 1");

      $SQL = "SELECT a.* FROM accounts a "
            ." LEFT JOIN groupaccounts ga ON a.accountid=ga.accountid "
            ." WHERE groupid=".$FormElements["groupid"]." AND a.accountid > 1";


      #
      # Store this information in session, so we wont get bogus
      #
      $result2 = $this->gDB->Execute($SQL);

      if ($result2 === false)
      {
        $this->SetErrorMessage('error reading: '
                              .$this->gDB->ErrorMsg( ));

        if ($_SESSION['IS_ERROR_REPORTING'])
          $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

        return false;
      }

      $_SESSION['group_accounts'] = "";
      $group_Accounts = array();

      while (!$result2->EOF)
      {
        $group_Accounts[$result2-> fields('accountid')] = $result2-> fields('accountid');
        $result2->MoveNext( );
      }

      $_SESSION['group_accounts'] = $group_Accounts;

      #
      # end of Store this information in session, so we wont get bogus
      #

      $groupAccounts-> SetSQL($SQL);

      $groupAccounts-> SetZebraColor("#EBEBEB");

      $myForm-> AddFormElement($groupAccounts);

      $buttons = new ObjectArray("buttons");
      $buttons->AddObject(new SubmitButton( "B_remove_accounts",">>",$class="b1"));
      $buttons->AddObject(new Label("lbl","<br>"));
      $buttons->AddObject(new SubmitButton( "B_add_accounts","<<",$class="b1"));
      $buttons->SetCellAttributes(array("align"=>"middle","valign"=>"middle"));

      $myForm-> AddFormElement  ($buttons);

      $allAccountsExceptGroup = new GetOptions($name="allAccountsExceptGroup",$table="accounts",$field="name",$key="accountid",$extraSql="", $selected=$FormElements["allAccountsExceptGroup"], $default="-Select-",  $displayonly=$this->mDisplayOnly, $size=10,$multiple=true,$extra="id=\"allAccountsExceptGroup\"", $concat=array(0=>"lastname","firstname"));

      # This was the original code, but didn't work for MySQL.
      #
      #$allAccountsExceptGroup-> SetSQL("SELECT a.accountid, '('||a.lastname||', '||a.firstname||')  '||username AS name FROM accounts a WHERE accountid NOT IN (SELECT accountid FROM groupaccounts WHERE groupid=" .$FormElements["groupid"].") AND accountid > 1");


      $accounts = $this->GetAllAccountsExceptGroupHas($FormElements["groupid"]);

      $SQL = "SELECT lastname,firstname,accountid FROM accounts "
            ."WHERE accountid NOT IN ($accounts)";

      #
      # Store this information in session, so we wont get bogus
      #
      $result2 = $this->gDB->Execute($SQL);

      if ($result2 === false)
      {
        $this->SetErrorMessage('error reading: '
                              .$this->gDB->ErrorMsg( ));

        if ($_SESSION['IS_ERROR_REPORTING'])
          $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

        return false;
      }

      $_SESSION['available_accounts'] = "";
      $availableAccounts = array();

      while (!$result2->EOF)
      {
        $availableAccounts[$result2-> fields('accountid')] = $result2-> fields('accountid');
        $result2->MoveNext( );
      }

      $_SESSION['available_accounts'] = $availableAccounts;

      #
      # end of Store this information in session, so we wont get bogus
      #

      $allAccountsExceptGroup-> SetSQL($SQL);


      $allAccountsExceptGroup-> SetZebraColor("#EBEBEB");

      $myForm-> AddFormElement($allAccountsExceptGroup);
      } //fin myhierarchy==1
      // desde aca las empresas por grupo
      $myForm-> AddFormElementToNewLine (new Dummy( ));
      
      $mylabel = new Label($name="lb1",$value="Empresas del Grupo $groupName"); //Pablo 280809
//    $mylabel-> SetValue("Usuarios del Grupo $groupName");  //Pablo 280809
      $mylabel-> SetClass("ColumnTD");
      $mylabel-> SetCellAttributes( array("colspan"=>2) );

      $myForm-> AddFormElementToNewLine ($mylabel);
      $myForm-> AddFormElement  (new Dummy( ));

      $mylabel = new Label($name="lb1",$value="Empresas Disponibles");
      $mylabel-> SetClass("ColumnTD");

      $myForm-> AddFormElement  ($mylabel);


      $myForm-> AddFormElementToNewLine (new Label("lb1","Empresas del Grupo :<br><br><hr><font style='font-size:8pt; font-style:italic;'>Puede seleccionar varios, ninguna seleccionada implica todas permitidas</font>"));

      $groupEmpresa = new GetOptions($name="groupempresa",$table=$_SESSION[db_cli].".empresa",$field="empresa_nombre",$key="empresa_id",$xtrasql="",$selected=$FormElements["groupempresa"], $default="-Select-",  $displayonly=$this->mDisplayOnly,$size=10,$multiple=TRUE,$extra="",$concat="","dbcli");

      # This was the original code, but didn't work for MySQL.
      #
      #$groupAccounts-> SetSQL("SELECT accountid, '('||lastname||', '||firstname||') '||username AS name FROM accounts WHERE accountid in (SELECT accountid FROM groupaccounts WHERE groupid=".$FormElements["groupid"].") AND accountid > 1");
/*
      $SQL = "SELECT e.* FROM ".$_SESSION[db_cli].".empresa e "
            ." LEFT JOIN groupempresa ge ON e.empresa_id=ge.empresaid "
            ." WHERE groupid=".$FormElements["groupid"]." ";
//            ." and empresa_id <=6";
*/
            $empresas = $this->GetAllEmpresasExceptGroupHas($FormElements["groupid"]);
      $SQL = "SELECT empresa_nombre,empresa_id FROM ".$_SESSION[db_cli].".empresa "
            ."WHERE empresa_id IN ($empresas)";
            
      #
      # Store this information in session, so we wont get bogus
      #
      $result2 = $this->gDBcli->Execute($SQL);

      if ($result2 === false)
      {
        $this->SetErrorMessage('error reading: '
                              .$this->gDB->ErrorMsg( ));

        if ($_SESSION['IS_ERROR_REPORTING'])
          $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

        return false;
      }

      $_SESSION['group_empresas'] = "";
      $group_Empresas = array();

      while (!$result2->EOF)
      {
        $group_Empresas[$result2-> fields('empresa_id')] = $result2-> fields('empresa_id');
        $result2->MoveNext( );
      }

      $_SESSION['group_empresas'] = $group_Empresas;

      #
      # end of Store this information in session, so we wont get bogus
      #

      $groupEmpresa-> SetSQL($SQL);
      $groupEmpresa-> SetConnection("db_cli");
      
      $groupEmpresa-> SetZebraColor("#EBEBEB");

      $myForm-> AddFormElement($groupEmpresa);

      $buttons = new ObjectArray("buttons");
      $buttons->AddObject(new SubmitButton( "B_remove_empresa",">>",$class="b1"));
      $buttons->AddObject(new Label("lbl","<br>"));
      $buttons->AddObject(new SubmitButton( "B_add_empresa","<<",$class="b1"));
      $buttons->SetCellAttributes(array("align"=>"middle","valign"=>"middle"));

      $myForm-> AddFormElement  ($buttons);

      $allEmpresasExceptGroup = new GetOptions($name="allEmpresasExceptGroup",$table=$_SESSION[db_cli].".empresa",$field="empresa_nombre",$key="empresaid",$extraSql="", $selected=$FormElements["allEmpresasExceptGroup"], $default="-Select-",  $displayonly=$this->mDisplayOnly, $size=10,$multiple=true,$extra="id=\"allEmpresasExceptGroup\"",$concat="","db_cli");

      # This was the original code, but didn't work for MySQL.
      #
      #$allAccountsExceptGroup-> SetSQL("SELECT a.accountid, '('||a.lastname||', '||a.firstname||')  '||username AS name FROM accounts a WHERE accountid NOT IN (SELECT accountid FROM groupaccounts WHERE groupid=" .$FormElements["groupid"].") AND accountid > 1");


      $empresas = $this->GetAllEmpresasExceptGroupHas($FormElements["groupid"]);

      $SQL = "SELECT empresa_nombre,empresa_id empresaid FROM ".$_SESSION[db_cli].".empresa "
            ."WHERE empresa_id NOT IN ($empresas)";
//            ." and empresa_id <=6";

      #
      # Store this information in session, so we wont get bogus
      #
      $result2 = $this->gDBcli->Execute($SQL);

      if ($result2 === false)
      {
        $this->SetErrorMessage('error reading: '
                              .$this->gDB->ErrorMsg( ));

        if ($_SESSION['IS_ERROR_REPORTING'])
          $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

        return false;
      }

      $_SESSION['available_empresas'] = "";
      $availableEmpresas = array();

      while (!$result2->EOF)
      {
        $availableEmpresas[$result2-> fields('empresaid')] = $result2-> fields('empresaid');
        $result2->MoveNext( );
      }

      $_SESSION['available_empresas'] = $availableEmpresas;

      #
      # end of Store this information in session, so we wont get bogus
      #

      $allEmpresasExceptGroup-> SetSQL($SQL);


      $allEmpresasExceptGroup-> SetZebraColor("#EBEBEB");

      $myForm-> AddFormElement($allEmpresasExceptGroup);
      
   //hasta aca las empresas por grupo
    }

    $passtru = new PassTru("");
    $passtru->SetColSpan( $myForm-> GetNumberOfColumns() );
    $passtru->SetClass("");
    $passtru->SetCellAttributes(array('align'=>'left'));

    $passtru->SetValue("<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"adminmenu.php\"><-- Volver al Menú Seguridad</a>");

    $myForm-> AddFormElementToNewLine ($passtru);

    echo $myForm->GetFormInTable( );
  }

  /**
  * Method to check the form. Sets the error message and which field is wrong.
  * @public
  * @returns bool
  */
  function ErrCheckGroupsForm($FormElements,$groupId)
  {
    $this->mIsError = 0;

    if (!$FormElements["hierarchy"]) 
    {
      $this->mErrorMessage = "Por favor, ingrese una Jerarquía al grupo.";
      $this->mFormErrors["hierarchy"]=1;
      $this->mIsError = 1;
    }

    if (!$FormElements["groupname"])
    {
      $this->mErrorMessage = "Por favor ingrese un nombre al grupo.";
      $this->mFormErrors["groupname"]=1;
      $this->mIsError = 1;
    }

    # check if the group name is in the database

    $groupid = $this-> GetGroupIdByName($FormElements['groupname']);

    if ($groupid)
    {
      if ($groupid != $groupId)
      {
        $this->SetErrorMessage("Nombre de Grupo ya existe. Intente con otro.");
        $this->mFormErrors["groupname"]=1;
        $this->mIsError = 1;
      }
    }

    return $this->mIsError;
  }

  /**
  * Method to set the Html header.
  * @private
  * @returns void
  */
  function SendHeader( )
  {
    $this->myForm = new Form("dummy");
    $this->myForm-> SetNumberOfColumns( 5 );
    $this->myForm-> SetCellSpacing( 1 );
    $this->myForm-> SetCellPadding( 5 );
    $this->myForm-> SetBorder ( 0 );
    $this->myForm-> SetAlign ("center");
    $this->myForm-> SetTableWidth ("400");
    $this->myForm-> SetTableHeight (null);
    $this->myForm-> SetCSS ( $_SESSION["CSS"] );
    $this->myForm-> SetEmptyCells (false);
    $this->myForm-> SetFormTagRequired (false);
    #$this->myForm-> SetTRMouseOverColor( $overcolor="#FFFF66", $outcolor="#ffcc44", $startingRow=2 );
    $this->myForm-> SetFormHeader("Lista de Grupos");

    $mylabel = new Label($name="lb1",$value="Nombre del Grupo");
    $mylabel-> SetClass("ColumnTD");
    $this->myForm-> AddFormElementToNewLine($mylabel);

    $mylabel = new Label($name="lb1",$value="Jerarquia"); //Anadido por Pablo 10/2008
    $mylabel-> SetClass("ColumnTD");//Anadido por Pablo 10/2008
//    $mylabel->SetValue("Jerarquía");
    $this->myForm-> AddFormElement($mylabel);

    $mylabel = new Label($name="lb1",$value="Listar");//Anadido por Pablo 10/2008
    $mylabel-> SetClass("ColumnTD");//Anadido por Pablo 10/2008
//    $mylabel->SetValue("Listar");
    $this->myForm-> AddFormElement($mylabel);
    
    $mylabel = new Label($name="lb1",$value="Editar");//Anadido por Pablo 10/2008
    $mylabel-> SetClass("ColumnTD");//Anadido por Pablo 10/2008
//    $mylabel->SetValue("Editar");
    $this->myForm-> AddFormElement($mylabel);

    $mylabel = new Label($name="lb1",$value="Borrar");//Anadido por Pablo 10/2008
    $mylabel-> SetClass("ColumnTD");//Anadido por Pablo 10/2008
//    $mylabel->SetValue("Borrar");
    $this->myForm-> AddFormElement($mylabel);
  }
  function ListGroupAccounts($selectlisttype) {
  	
  	if ($_SESSION ['myAccount'] == 1) {
  		$sql = "SELECT cliente_nombre,hierarchy,a.accountid,a.* FROM megacontrol.accounts a 
  				 LEFT JOIN megacontrol.groupaccounts ga ON a.accountid=ga.accountid 
  				 LEFT JOIN megacontrol.groups g ON g.groupid=ga.groupid 
  				 LEFT JOIN megacontrol.cliente ON a.cliente_id=cliente.cliente_id 
  				WHERE (hierarchy >=" . $_SESSION ['myHierarchy'] . " or hierarchy is null)
  						and g.groupid=".$_GET[groupId]."
  						GROUP BY accountid  ORDER BY firstname,lastname";
  		// ." AND a.accountid=ga.accountid AND g.groupid=ga.groupid " . $userExtra
  	} else {
  		$sql = "SELECT cliente_nombre,hierarchy,a.accountid,a.* FROM megacontrol.accounts a 
  				LEFT JOIN megacontrol.groupaccounts ga ON a.accountid=ga.accountid 
  				LEFT JOIN megacontrol.groups g ON g.groupid=ga.groupid 
  				LEFT JOIN megacontrol.cliente ON a.cliente_id=cliente.cliente_id 
  				WHERE (hierarchy >=" . $_SESSION ['myHierarchy'] . " or hierarchy is null) 
  						and g.groupid=".$_GET[groupId]."
  						AND a.accountid > 1 
  						AND a.cliente_id ='" . $_SESSION ['cliente_id'] . "' 
  				GROUP BY accountid  ORDER BY firstname,lastname";
  		// ." AND a.accountid=ga.accountid AND g.groupid=ga.groupid " . $userExtra
  	}
  	?>
  <table class="micronautaFormTABLE" cellspacing="1" cellpadding="5"
  	align="center">
  	<tr>
  		<td colspan="6" class="micronautaFormHeaderFont" width="100%"
  			align="middle">Lista de Usuarios de <?php echo $_GET[groupNombre];?></td>
  	</tr>
  	<tr>
  		<td>
  			<table border="0" cellspacing="0" cellpadding="4">
  				<tr class="thead" align="center">
  					<td class="micronautaColumnTD">Usuario</td>
  					<td class="micronautaColumnTD">Nombre</td>
  					<td class="micronautaColumnTD">Modificar</td>
  					<td class="micronautaColumnTD">Borrar</td>
  				</tr>
      <?php
  		$accounts_read_from_table = array ();
  		$result = $this->gDB->Execute ( $sql );
  		while ( ! $result->EOF ) {
  			$edit = "&nbsp;";
  			$delete = "&nbsp;";
  			$accounts_read_from_table [$result->fields ( "accountid" )] = $result->fields ( "accountid" );
  			// Edit
//   			if ($this->mySecurity->isAllowedTo ( 'modify account' )) {
  			if ($this->mySecurity->isAllowedTo ( 12 )) {
  				$edit = "\n<a class=\"" . $_SESSION ["CSS"] . "LinkButton\" href=\"accountsModify.php?accountId=" . $result->fields ( "accountid" );
  				$edit .= "&mode=edit&selectlisttype=$selectlisttype\">Editar</a>";
  			}
  			// Delete
//   			if ($this->mySecurity->isAllowedTo ( 'delete account' ) and $result->fields ( "accountid" ) != 1 and $result->fields ( "accountid" ) != $_SESSION ['myAccount']) {
  			if ($this->mySecurity->isAllowedTo ( 11 ) and $result->fields ( "accountid" ) != 1 and $result->fields ( "accountid" ) != $_SESSION ['myAccount']) {
  				$delete = "\n<a class=\"" . $_SESSION ["CSS"] . "LinkButton\" href=\"accountsModify.php?accountId=" . $result->fields ( "accountid" );
  				$delete .= "&mode=delete&selectlisttype=$selectlisttype\">Borrar</a>";
  			}
  			?>
              <tr align="center" valign="top">
  					<td class="micronautaDataTD" align="left"><? echo $result->fields("firstname");?> <? echo $result->fields("lastname");?></td>
  					<td class="micronautaDataTD" align="left"><? echo $result->fields("username");?></td>
  					<td class="micronautaDataTD">
                  <? echo $edit;?>
                </td>
  					<td class="micronautaDataTD">
                  <? echo $delete;?>
                </td>
  				</tr>
        <?php
  			$result->MoveNext ();
  		}
  		// the reason of this one is to prevent people, sending bogus
  		// accounts rom the URL. We will match the accountid coming from the URL
  		// with the one in session.
  		$_SESSION ['accounts_read_from_table'] = $accounts_read_from_table;
  		?>
              <tr>
  					<td class="micronauta" align="center" colspan="6"><a
  						class="micronautaLinkButton" href="adminmenu.php"><-- Volver al
  							Menú Seguridad</a></td>
  				</tr>
  			</table>
  		</td>
  	</tr>
  </table>
  <?php
  	}
    
  /**
  * Method to send the Html in a table.This method is called from the ListGroups
  * method.
  * @private
  * @returns void
  */
  function SendTrailer( )
  {
    echo $this->myForm-> GetFormInTable( );
  }
}
?>