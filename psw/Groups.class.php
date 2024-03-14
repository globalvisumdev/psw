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
      
      $sql = "SELECT * FROM groups WHERE groupid=:id and cliente_id=:cli";
      try {
          $resultp2 = $this->pDB->prepare($sql);
          $datadb=array(':id'=>$key,':cli'=>$_SESSION['cliente_id']);
          $resultp2->execute($datadb);
          while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
              return $row2;
          }
      }catch(PDOException  $e ){
          $this-> SetErrorMessage('Error de lectura gg','');
          if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gg','');
          return false;
      }
      
      return false;
//       $key =intval($key);
//     $sql = "SELECT * FROM groups WHERE groupid=$key and cliente_id='".$_SESSION['cliente_id']."'";
  

//     $result = $this->gDB->Execute($sql);

//     if ($result === false)
//     {
//       $this->SetErrorMessage('error reading: '
//                             .$this->gDB->ErrorMsg( ));

//       if ($_SESSION['IS_ERROR_REPORTING'])
//         $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

//       return false;
//     }

//     return $result;
  }

  /**
  * Method to get the group name with a given key.
  * @public
  * @returns string
  */
  function GetGroupName($key)
  {
      
      $sql = "SELECT groupname FROM groups WHERE groupid=:id and cliente_id=:cli";
      try {
          $resultp2 = $this->pDB->prepare($sql);
          $datadb=array(':id'=>$key,':cli'=>$_SESSION['cliente_id']);
          $resultp2->execute($datadb);
          while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
              return $row2->groupname;
          }
      }catch(PDOException  $e ){
          $this-> SetErrorMessage('Error de lectura ggn','');
          if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura ggn','');
          return false;
      }
      
      return false;
//       $key =intval($key);
      
//     $sql = "SELECT groupname FROM groups WHERE groupid=$key and cliente_id='".$_SESSION['cliente_id']."'";

//     $result = $this->gDB->Execute($sql);

//     if ($result === false)
//     {
//       $this->SetErrorMessage('error reading: '
//                             .$this->gDB->ErrorMsg( ));

//       if ($_SESSION['IS_ERROR_REPORTING'])
//         $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

//       return false;
//     }

//     return $result->fields("groupname");
  }

  /**
  * Method to get the group information with a given name.
  * @public
  * @returns array
  */
  function GetGroupIdByName($name)
  {

      $sql = "SELECT * FROM groups WHERE groupname=:name and cliente_id=:cli";
      
      try {
          $resultp2 = $this->pDB->prepare($sql);
          $datadb=array(':name'=>$name,':cli'=>$_SESSION['cliente_id']);
          $resultp2->execute($datadb);
          
          while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
              return $row2->groupid;
          }
          
      }catch(PDOException  $e ){
          $this-> SetErrorMessage('Error de lectura ggbin','');
          if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura ggbin','');
          return false;
      }
      
      return false;
//     $sql = "SELECT * FROM groups WHERE groupname='".$name."' and cliente_id='".$_SESSION['cliente_id']."'";

//     $result = $this->gDB->Execute($sql);

//     if ($result === false)
//     {
//       $this->SetErrorMessage('error reading: '
//                             .$this->gDB->ErrorMsg( ));

//       if ($_SESSION['IS_ERROR_REPORTING'])
//         $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

//       return false;
//     }

//     return $result->fields("groupid");
  }

  /**
  * Method to check if there are any users under this group.
  * @public
  * @returns array
  */
  function isAnyUserForThisGroup($groupId)
  {
      $sql = "SELECT count(*) c FROM groupaccounts WHERE groupid=:id";
      try {
          $resultp2 = $this->pDB->prepare($sql);
          $datadb=array(':id'=>$groupId);
          $resultp2->execute($datadb);
          
          while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
              return $row2->c;
          }
          
      }catch(PDOException  $e ){
          $this-> SetErrorMessage('Error de lectura iauftg','');
          if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura iauftg','');
          return false;
      }
      return 0;
//     $sql = "SELECT count(*) FROM groupaccounts WHERE groupid=".intval($groupId);

//     $result = $this->gDB->Execute($sql);

//     if ($result === false)
//     {
//       $this->SetErrorMessage('error reading: '
//                             .$this->gDB->ErrorMsg( ));

//       if ($_SESSION['IS_ERROR_REPORTING'])
//         $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

//       return false;
//     }

//     return $result->fields("count");
  }

  	#####################################################################################################################3
	// function getGroupActions($FormElements){


	// 	// Acciones actuales
  //   $accionesActuales = "SELECT a.* FROM actions a LEFT JOIN groupactions ga ON a.actionid=ga.actionid WHERE groupid=:grupo order by actionname";

  //   $actionsExceptGroupHas = $this-> GetAllActionsExceptGroupHas($FormElements["groupid"]);
  //   if ($actionsExceptGroupHas){
  //     $accionesDisponibles = "SELECT actions.* FROM actions 
  //     left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
  //     WHERE acd.actionid is null and actions.actionid NOT IN ($actionsExceptGroupHas)";
  //   }
  //   else{
  //     $accionesDisponibles = "SELECT actions.* FROM actions 
  //     left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id']).
  //     "where acd.actionid is null and 1=1 and (actionclase = 0 or :hier=1) order by actionname";

  //   }

	// 	// Usuarios
  //   $usuariosActuales = "SELECT a.* FROM accounts a LEFT JOIN groupaccounts ga ON a.accountid=ga.accountid WHERE groupid=:grupo AND a.accountid > 1";

  //   $accountsExceptGroupHas = $this->GetAllAccountsExceptGroupHas($FormElements["groupid"]);

  //   $usuariosDisponibles = "SELECT accountid,lastname,firstname FROM accounts WHERE accountid NOT IN ($accountsExceptGroupHas)";
  
  //   // Empresas
  //   $empresasExceptGroupHas = $this->GetAllEmpresasExceptGroupHas($FormElements["groupid"]);

  //   $empresasActuales = "SELECT empresa_id,empresa_nombre FROM ".$_SESSION['db_cli'].".empresa WHERE empresa_id IN ($empresasExceptGroupHas)";

  //   $empresasDisponibles = "SELECT empresa_id empresaid,empresa_nombre FROM ".$_SESSION['db_cli'].".empresa WHERE empresa_id NOT IN ($empresasExceptGroupHas)";

	// 	// Conexion con la base de datos
	// 	$SQL = array(
	// 		"dataAccAct" => $accionesActuales,
	// 		"dataAccDisp"=> $accionesDisponibles,
	// 		"dataUsrAct" => $usuariosActuales,
	// 		"dataUsrDisp" => $usuariosDisponibles,
  //     "dataEmprAct" => $empresasActuales,
	// 		"dataEmprDisp" => $empresasDisponibles,
	// 		);

	// 	$resultSQL = array(
	// 		"dataAccAct" => array(),
	// 		"dataAccDisp"=> array(),
	// 		"dataUsrAct" => array(),
	// 		"dataUsrDisp" => array(),
  //     "dataEmprAct" => array(),
	// 		"dataEmprDisp" => array(),
	// 		);


	// 	foreach ($resultSQL as $clave => $valor){

  //     try {
  //       $this->pDB->query( "SET NAMES 'UTF8' ");
	// 			$resultp = $this->pDB->prepare($SQL[$clave]);
	// 			$datadb=array(':grupo'=> $FormElements ["groupid"]);
	// 			$resultp->execute($datadb);
        
	// 			$queryResult = array();
	// 			while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
	// 				$queryResult[] = $row;
	// 			}
	// 			$resultSQL[$clave] = $queryResult;
	// 		}
	// 		catch(PDOException  $e ){
  //       echo $e -> getMessage();
  //       die();
	// 			$this-> SetErrorMessage('Error de lectura saaaf','');
	// 			if ($_SESSION['IS_ERROR_REPORTING']){
	// 				return array(
	// 					"ok" => false,
	// 					"errorMsg" =>  'Error de lectura lg',
	// 				);
	// 			};
	// 		}
	// 	}

	// 	return array(
	// 		"ok" => true,
	// 		"dataAccAct" =>  $resultSQL["dataAccAct"],
	// 		"dataAccDisp" =>  $resultSQL["dataAccDisp"],
	// 		"dataUsrAct" => $resultSQL["dataUsrAct"],
	// 		"dataUsrDisp" => $resultSQL["dataUsrDisp"],
  //     "dataEmprAct" => $resultSQL["dataEmprAct"],
	// 		"dataEmprDisp" => $resultSQL["dataEmprDisp"],
	// 	);
	// }

  function getGroupActions($FormElements){

    switch (TRUE)
		{
			Case $FormElements["idSelect"] == "dataAccAct":
        // Acciones actuales
        $datosActuales = "SELECT a.* FROM actions a LEFT JOIN groupactions ga ON a.actionid=ga.actionid WHERE groupid=:grupo order by actionname";

        $actionsExceptGroupHas = $this-> GetAllActionsExceptGroupHas($FormElements["groupid"]);
        if ($actionsExceptGroupHas != NULL ){
          $datosDisponibles = "SELECT actions.* FROM actions 
          left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
          WHERE acd.actionid is null and actions.actionid NOT IN ($actionsExceptGroupHas)";
        }
        else{
          $datosDisponibles = "SELECT actions.* FROM actions 
          left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id']).
          "where acd.actionid is null and 1=1 and (actionclase = 0 or :hier=1) order by actionname";

        }
      break;

      Case $FormElements["idSelect"] == "dataUsrAct":

        // Usuarios
        $datosActuales = "SELECT a.* FROM accounts a LEFT JOIN groupaccounts ga ON a.accountid=ga.accountid WHERE groupid=:grupo AND a.accountid > 1";

        $accountsExceptGroupHas = $this->GetAllAccountsExceptGroupHas($FormElements["groupid"]);

        $datosDisponibles = "SELECT accountid,lastname,firstname FROM accounts WHERE accountid NOT IN ($accountsExceptGroupHas)";
    
      break;

      Case $FormElements["idSelect"] == "dataEmprAct":
        // Empresas
        $empresasExceptGroupHas = $this->GetAllEmpresasExceptGroupHas($FormElements["groupid"]);

        $datosActuales = "SELECT empresa_id,empresa_nombre FROM ".$_SESSION['db_cli'].".empresa WHERE empresa_id IN ($empresasExceptGroupHas)";

        $datosDisponibles = "SELECT empresa_id,empresa_nombre FROM ".$_SESSION['db_cli'].".empresa WHERE empresa_id NOT IN ($empresasExceptGroupHas)";

      break;
    }

		// Conexion con la base de datos
		$SQL = array(
			"datosActuales" => $datosActuales,
			"datosDisponibles" => $datosDisponibles,
		);

		$resultSQL = array(
			"datosActuales" => array(),
			"datosDisponibles" => array()
		);


		foreach ($resultSQL as $clave => $valor){

      try {
        $this->pDB->query( "SET NAMES 'UTF8' ");
				$resultp = $this->pDB->prepare($SQL[$clave]);
				$datadb=array(':grupo'=> $FormElements ["groupid"]);
				$resultp->execute($datadb);
        
				$queryResult = array();
				while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
					$queryResult[] = $row;
				}
				$resultSQL[$clave] = $queryResult;
			}
			catch(PDOException  $e ){
        echo $e -> getMessage();
        die();
				$this-> SetErrorMessage('Error de lectura saaaf','');
				if ($_SESSION['IS_ERROR_REPORTING']){
					return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura lg',
					);
				};
			}
		}

    return array(
			"ok" => true,
			"datosActuales" =>  $resultSQL["datosActuales"],
			"datosDisponibles" =>  $resultSQL["datosDisponibles"],
		);
	}

  /**
  * Method to add actions to groupactions table.
  * @public
  * @returns bool
  */
  function AddGroupActions($FormElements){
		$FormElements ["allactions"] = explode(",", $FormElements ["allactions"]);
    
    if (count($FormElements["allactions"])){

      # Check for the bogus actions
      #
      // foreach( $FormElements["allactions"] as $key=>$actionid)
      // {
      //   if (!array_key_exists ( $actionid,
      //                     $_SESSION['available_actions']))
      //   {
      //     $this->mySecurity-> GotoThisPage( "bogus.php" );
      //   }
      // }

      $this->pDB->beginTransaction();
      $sql = "INSERT INTO groupactions (groupid,actionid) VALUES(:grupo,:action)";
      $resultp = $this->pDB->prepare($sql);
      
      foreach( $FormElements["allactions"] as $key=>$actionid){
          try {

              $datadb=array(':action'=>$actionid,':grupo'=>$FormElements ["groupid"]);
              $resultp->execute($datadb);
          }catch(PDOException  $e ){
              $this->pDB->rollBack();
              $this-> SetErrorMessage('Error de lectura aga','');
              if ($_SESSION['IS_ERROR_REPORTING']) 						
              return array(
                "ok" => false,
                "errorMsg" =>  'Error de lectura lg',
              );
          }
      }
      
      $this->pDB->commit();
      return array(
				"ok" => true,
				"errorMsg" =>  'Se agregaron las acciones correctamente.',
			);
      
    }
    return true;
  }

  /**
  * Method to remove actions from groupactions table.
  * @public
  * @returns bool
  */
  function RemoveGroupActions($FormElements){
		$FormElements ["groupactions"] = explode(",", $FormElements ["groupactions"]);

    if (count($FormElements["groupactions"])){
      # Check for the bogus actions
      #
      // foreach( $FormElements["groupactions"] as $key=>$actionid)
      // {
      //   if (!array_key_exists ( $actionid,
      //                     $_SESSION['group_actions']))
      //   {
      //     $this->mySecurity-> GotoThisPage( "bogus.php" );
      //   }
      // }

      $this->pDB->beginTransaction();
      $sql = "DELETE FROM groupactions WHERE groupid =:grupo AND actionid=:action";
      $resultp = $this->pDB->prepare($sql);
      
      foreach( $FormElements["groupactions"] as $key=>$actionid){
          try {
              $datadb=array(':action'=>$actionid,':grupo'=>$FormElements ["groupid"]);
              $resultp->execute($datadb);
          }catch(PDOException  $e ){
              $this->pDB->rollBack();
              $this-> SetErrorMessage('Error de lectura rga','');
              if ($_SESSION['IS_ERROR_REPORTING'])
              return array(
                "ok" => false,
                "errorMsg" =>  'Error de lectura lg',
              );
          }
      }
      
      $this->pDB->commit();
      return array(
				"ok" => true,
				"errorMsg" =>  'Se eliminaron las acciones correctamente.',
			);
    }
    return true;
  }

  /**
  * Method to add accounts to groupaccounts table.
  * @public
  * @returns bool
  */
  function AddGroupAccounts($FormElements){
		$FormElements ["allAccountsExceptGroup"] = explode(",", $FormElements ["allAccountsExceptGroup"]);

    if (count($FormElements["allAccountsExceptGroup"]))
    {
      # Check for the bogus accounts
      #
      // foreach( $FormElements["allAccountsExceptGroup"] as $key=>$accountid)
      // {
      //   if (!array_key_exists ( $accountid,
      //                     $_SESSION['available_accounts']))
      //   {
      //     $this->mySecurity-> GotoThisPage( "bogus.php" );
      //   }
      // }

      $this->pDB->beginTransaction();
      $sql = "INSERT INTO groupaccounts (groupid,accountid) VALUES(:grupo,:acc)";
      $resultp = $this->pDB->prepare($sql);
      
      foreach( $FormElements["allAccountsExceptGroup"] as $key=>$accountid)
      {
          try {
              $datadb=array(':acc'=>$accountid,':grupo'=>$FormElements ["groupid"]);
              $resultp->execute($datadb);
          }catch(PDOException  $e ){
              $this->pDB->rollBack();
              $this-> SetErrorMessage('Error de lectura aga','');
              if ($_SESSION['IS_ERROR_REPORTING'])
              return array(
                "ok" => false,
                "errorMsg" =>  'Error de lectura lg',
              );
          }
      }
      
      $this->pDB->commit();
      return array(
				"ok" => true,
				"errorMsg" =>  'Se agregaron los usuarios correctamente.',
			);
      
    }

    return true;
  }

  /**
  * Method to remove accounts from groupaccounts table.
  * @public
  * @returns bool
  */
  function RemoveGroupAccounts($FormElements){
    $FormElements ["groupaccounts"] = explode(",", $FormElements ["groupaccounts"]);
    

    if (count($FormElements["groupaccounts"]))
    {

      # Check for the bogus accounts
      #
      // foreach( $FormElements["groupaccounts"] as $key=>$accountid)
      // {
      //   if (!array_key_exists ( $accountid,
      //                     $_SESSION['group_accounts']))
      //   {
      //     $this->mySecurity-> GotoThisPage( "bogus.php" );
      //   }
      // }

      $this->pDB->beginTransaction();
      $sql = "DELETE FROM groupaccounts WHERE groupid =:grupo and accountid=:acc";
      $resultp = $this->pDB->prepare($sql);
      
       foreach( $FormElements["groupaccounts"] as $key=>$accountid)
       {
          try {
              $datadb=array(':acc'=>$accountid,':grupo'=>$FormElements ["groupid"]);
              $resultp->execute($datadb);
          }
          catch(PDOException  $e ){
              $this->pDB->rollBack();
              $this-> SetErrorMessage('Error de lectura rga','');
              if ($_SESSION['IS_ERROR_REPORTING'])
              return array(
                "ok" => false,
                "errorMsg" =>  'Error de lectura lg',
              );
          }
      }
      
      $this->pDB->commit();
      return array(
				"ok" => true,
				"errorMsg" =>  'Se eliminaron los usuarios correctamente.',
			);
      
    }

    return true;
  }
  
  /**
  * Method to add empresas to groupempresa table.
  * @public
  * @ Pablo 310809
  * @returns bool
  */
  function AddGroupEmpresas($FormElements){
		$FormElements ["allEmpresasExceptGroup"] = explode(",", $FormElements ["allEmpresasExceptGroup"]);

    if (count($FormElements["allEmpresasExceptGroup"]))
    {
      # Check for the bogus empresas
      #
      // foreach( $FormElements["allEmpresasExceptGroup"] as $key=>$empresaid)
      // {
      //   if (!array_key_exists ( $empresaid,
      //                     $_SESSION['available_empresas']))
      //   {
      //     $this->mySecurity-> GotoThisPage( "bogus.php" );
      //   }
      // }

      $this->pDB->beginTransaction();
      $sql = "INSERT INTO groupempresa (groupid,empresaid) VALUES(:grupo,:empresa)";
      $resultp = $this->pDB->prepare($sql);
      
      foreach( $FormElements["allEmpresasExceptGroup"] as $key=>$empresaid)
      {
          try {
              $datadb=array(':empresa'=>$empresaid,':grupo'=>$FormElements ["groupid"]);
              $resultp->execute($datadb);
          }catch(PDOException  $e ){
              $this->pDB->rollBack();
              $this-> SetErrorMessage('Error de lectura age','');
              if ($_SESSION['IS_ERROR_REPORTING'])
              return array(
                "ok" => false,
                "errorMsg" =>  'Error de lectura lg',
              );
          }
      }
      
      $this->pDB->commit();
      return array(
				"ok" => true,
				"errorMsg" =>  'Se agregaron las empresas correctamente.',
			);
      
    }

    return true;
  }

  /**
  * Method to remove empresas from groupempresa table.
  * @public
  * @Pablo 310809
  * @returns bool
  */
  function RemoveGroupEmpresas($FormElements){
		$FormElements ["groupempresa"] = explode(",", $FormElements ["groupempresa"]);

    if (count($FormElements["groupempresa"]))
    {
      # Check for the bogus accounts
      #
      // foreach( $FormElements["groupempresa"] as $key=>$empresaid)
      // {
      //   if (!array_key_exists ( $empresaid,
      //                     $_SESSION['group_empresas']))
      //   {
      //     $this->mySecurity-> GotoThisPage( "bogus.php" );
      //   }
      // }

      $this->pDB->beginTransaction();
      $sql = "DELETE FROM groupempresa WHERE groupid =:grupo and empresaid=:empresa";
      $resultp = $this->pDB->prepare($sql);
      
      foreach( $FormElements["groupempresa"] as $key=>$empresaid)
      {
          try {
              $datadb=array(':empresa'=>$empresaid,':grupo'=>$FormElements ["groupid"]);
              $resultp->execute($datadb);
          }catch(PDOException  $e ){
              $this->pDB->rollBack();
              $this-> SetErrorMessage('Error de lectura rge','');
              if ($_SESSION['IS_ERROR_REPORTING']) 
              return array(
                "ok" => false,
                "errorMsg" =>  'Error de lectura lg',
              );
          }
      }
      
      $this->pDB->commit();
      return array(
				"ok" => true,
				"errorMsg" =>  'Se eliminaron  las empresas correctamente.',
			);
      
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

        $sql = "DELETE FROM groupaccounts WHERE groupid = :grupo";
        
        try {
            $resultp2 = $this->pDB->prepare($sql);
            $datadb=array(':grupo'=>$groupid);
            $resultp2->execute($datadb);
        }catch(PDOException  $e ){
            $this-> SetErrorMessage('Error de lectura rgafg','');
            if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura rgafg','');
            return false;
        }
        
//       $sql = "DELETE FROM groupaccounts "
//       ."      WHERE groupid = $groupid";

//       if ($result = $this->gDB->Execute($sql) === false)
//       {
//         if ($_SESSION['IS_ERROR_REPORTING'])
//           $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

//         return false;
//       }
    }

    return true;
  }

  /**
  * Method to delete group from the table with a given key.
  * @public
  * @returns bool
  */
  function DeleteGroup($groupId){
    # we dont want to delete the admins group
    # please make sure admins group stays as groupid 1
    if ($groupId == 1){
      return false;
    }

    $this->pDB->beginTransaction();
    $sql = "DELETE FROM groups WHERE groupid=:grupo and cliente_id=:cli";
    
    try{
      $resultp = $this->pDB->prepare($sql);
      $datadb=array(':cli'=>$_SESSION['cliente_id'],':grupo'=>$groupId);
      $resultp->execute($datadb);
      $this->mErrorMessage = "Grupo borrado exitosamente.";
      if ($this-> RemoveGroupAccountsForGroup($groupId) === false){
        $this->pDB->rollBack();
        return false;
      }
      
    }
    catch(PDOException  $e ){
      $this->pDB->rollBack();
      $this-> SetErrorMessage('Error de lectura dg','');
      if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura dg','');
      return false;
    }
    
    $this->pDB->commit();

    return array(
      "ok" => true,
      "errorMsg" =>  $this->mErrorMessage
    );

  }

  /**
  * Method to update groups table with a given key, fields and values.
  * @public
  * @returns bool
  */
  function UpdateGroup($FormElements,$groupId){
   $groupId = intval($groupId);

   
   if (!$this->ErrCheckGroupsForm($FormElements,$groupId)){
      $this->Field($this->mKeyName,$groupId);
      
      foreach ($this->mTableFields as $key =>$value){
        if ($key <> $this->mKeyName){
          if ($FormElements[$key])
          $this->Field($key,htmlspecialchars($FormElements[$key]));
        }
      }

      // $this->Update();

      if ($this-> Update( )) {
        return array(
          "ok" => true,
          "errorMsg" =>  $this->mErrorMessage
        );
      }
      else{
        return array(
          "ok" => false,
          "errorMsg" =>  $this->mErrorMessage
        );
      }
    }
    return array(
      "ok" => false,
      "errorMsg" =>  $this->mErrorMessage
    );
  }

  /**
  * Method to add groups to the table.
  * @public
  * @returns bool
  */
  Function AddGroup($FormElements){
    
    $FormElements['hierarchy']= 4;
  	$FormElements['cliente_id']= $_SESSION['cliente_id'];
    if (!$this->ErrCheckGroupsForm($FormElements,null)){

      foreach ($this->mTableFields as $key =>$value)
      {
        if ($key <> $this->mKeyName)
        {
          if ($FormElements[$key])
          $this->Field($key,htmlspecialchars($FormElements[$key]));
        }
      }

      if ($this-> InsertNew() ){
        $this->mErrorMessage = "Grupo añadido exitosamente.";
        return array(
          "ok" => true,
          "errorMsg" =>  $this->mErrorMessage
        );
      }
      else {
        return array(
          "ok" => false,
          "errorMsg" =>  $this->mErrorMessage
        );
      }
    }
    return array(
      "ok" => false,
      "errorMsg" =>  $this->mErrorMessage
    );
    
  }

  /**
  * Method to get the actions that the group doesn't have.
  * @public
  * @returns string
  */
  Function GetAllActionsExceptGroupHas($groupid)
  {
      
      $sql = "SELECT actionid from groupactions WHERE groupid=:grupo order by 1";
      $string = null;
      try {
          $resultp2 = $this->pDB->prepare($sql);
          $datadb=array(':grupo'=>$groupid);
          $resultp2->execute($datadb);
          while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
              if ($string) $string .= ",";
              $string .= $row2->actionid;
          }
      }catch(PDOException  $e ){
          $this-> SetErrorMessage('Error de lectura gaaegh','');
          if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaaegh','');
          return "0";
      }
      
      
//     $sql = "SELECT actionid from groupactions WHERE groupid=".intval($groupid)." order by 1";

//     $result = $this->gDB->Execute($sql);

//     if ($result === false)
//     {
//       $this->mErrorMessage = 'error reading: '
//                           .$this->gDB->ErrorMsg( );

//       if ($_SESSION['IS_ERROR_REPORTING'])
//         $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

//     }

//       $string = null;

//       while (!$result->EOF)
//       {
//         if ($string)
//           $string .= ",";
//         $string .= $result->fields("actionid");

//         $result->MoveNext( );
//       }

      return $string;
  }

  /**
  * Method to get the accounts that the group doesn't have.
  * @public
  * @returns string
  */
  Function GetAllAccountsExceptGroupHas($groupid)
  {

      $sql = "SELECT accountid from groupaccounts WHERE groupid=:grupo";
      $string = "1";
      try {
          $resultp2 = $this->pDB->prepare($sql);
          $datadb=array(':grupo'=>$groupid);
          $resultp2->execute($datadb);
          while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
              if ($string) $string .= ",";
              $string .= $row2->accountid;
          }
      }catch(PDOException  $e ){
          $this-> SetErrorMessage('Error de lectura gaaegh','');
          if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaaegh','');
          return "1";
      }
      
      
//       $sql = "SELECT accountid from groupaccounts WHERE groupid=".intval($groupid);

//     $result = $this->gDB->Execute($sql);

//     if ($result === false)
//     {
//       $this->mErrorMessage = 'error reading: '
//                           .$this->gDB->ErrorMsg( );

//       if ($_SESSION['IS_ERROR_REPORTING'])
//         $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

//     }

//       # make sure we put the admin account which is one, by default
//       # so we dont see it.

//       $string = "1"; # this is the admin account

//       while (!$result->EOF)
//       {
//         if ($string)
//           $string .= ",";
//         $string .= $result->fields("accountid");

//         $result->MoveNext( );
//       }

      return $string;
  }
  /**
  * Method to get the emrpesas that the group doesn't have.
  * @public
  * @returns string
  */
  Function GetAllEmpresasExceptGroupHas($groupid)
  {
      
      $sql = "SELECT empresaid from groupempresa WHERE groupid=:grupo";
      $string = "0";
      try {
          $resultp2 = $this->pDB->prepare($sql);
          $datadb=array(':grupo'=>$groupid);
          $resultp2->execute($datadb);
          while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
              if ($string) $string .= ",";
              $string .= $row2->empresaid;
          }
      }catch(PDOException  $e ){
          $this-> SetErrorMessage('Error de lectura gaeegh','');
          if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaeegh','');
          return "0";
      }
      
//     $sql = "SELECT empresaid from groupempresa WHERE groupid=".intval($groupid);

//     $result = $this->gDB->Execute($sql);

//     if ($result === false)
//     {
//       $this->mErrorMessage = 'error reading: '
//                           .$this->gDB->ErrorMsg( );

//       if ($_SESSION['IS_ERROR_REPORTING'])
//         $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

//     }

//       # make sure we put the admin account which is one, by default
//       # so we dont see it.

//       $string = "0"; # this is la empresa de usuarios

//       while (!$result->EOF)
//       {
//         if ($string)
//           $string .= ",";
//         $string .= $result->fields("empresaid");

//         $result->MoveNext( );
//       }

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
	function ListPermission() {
  	ini_set('display_errors', 1);	

		$sql="SELECT a.actionid,actionname FROM actions a
				left join actionclientedenied acd on acd.actionid=a.actionid and acd.cliente_id= :cli ,groupaccounts ag
				LEFT JOIN groupactions ga ON ag.groupid=ga.groupid
				WHERE ag.accountid=:acc and acd.actionid is null and (a.actionid = ga.actionid or ga.actionid = floor(a.actionid/100)*100 ) and actionclase = 0
				union
				SELECT a.actionid,actionname FROM accountaction ag ,actions a
				left join actionclientedenied acd on acd.actionid=a.actionid and acd.cliente_id= :cli
				WHERE ag.accountid=:acc
				and acd.actionid is null 
				and (a.actionid = ag.actionid
				or ag.actionid = floor(a.actionid/100)*100 )
				and actionclase = 0
				order by 2";
		
		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
			$resultp = $this->pDB->prepare($sql);
			$datadb=array(':acc'=>$_SESSION['myAccount'], ':cli'=>$_SESSION['cliente_id']);
			$resultp->execute($datadb);

			$result = array();
			while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
				$result[] = $row;
			}
			return array(
				"ok" => true,
				"data" =>  $result
			);
		}
		catch(PDOException  $e ){
			$this-> SetErrorMessage('Error de lectura la','');
			if ($_SESSION['IS_ERROR_REPORTING']){
				return array(
					"ok" => false,
					"errorMsg" =>  'Error de lectura lp',
				  );
			} 
		}
				
		// the reason of this one is to prevent people, sending bogus
		// accounts rom the URL. We will match the accountid coming from the URL
		// with the one in session.

	}
	
	function DeletePermission($FormElements){
    $id = addslashes($_REQUEST['id']);
    
    $ban=0;
    $Q="select * from groupaction_eventual where gae_id=:key and gae_fecha_hasta > now()";
    
		$this->pDB->query( "SET NAMES 'UTF8' ");
		$stmt = $this->pDB->prepare($Q);
		$datadb=array(':key'=>$FormElements['id']);
    $stmt->execute($datadb);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ban=1;        
    }
    $stmt=null;
        
    if ($ban==0) {
			return array(
				"ok" => false,
				"errorMsg" =>  'No puede eliminar un permiso pasado.',
			  );
      die();    
    }

    $Q = "delete from groupaction_eventual where gae_id=:key";

    try {
      $this->pDB->query( "SET NAMES 'UTF8' ");
      $stmt = $this->pDB->prepare($Q);
      $datadb=array(':key'=>$FormElements['id']);
        $stmt->execute($datadb);
        return array(
          "ok" => true,
          "errorMsg" =>  'Permiso eliminado correctamente.',
        );
    }catch(PDOException  $e ){
      return array(
				"ok" => false,
				"errorMsg" =>  'Error de lectura DP.',
			);
    }
    
    die();
	}
	
  function GenerateReport ($FormElements) {
  	// ini_set('display_errors', 1);	
    $formato_ver = $FormElements['formato_ver'];

    if ($formato_ver == 'xls') {
        include '../admin/Classes/PHPExcel.php';
    }

    $sql = "select gae_id,groupname,GROUP_CONCAT(cliente_nombre) empresas,actionname,accounts.cliente_id,groupaction_eventual.* from groupaction_eventual,groups,actions,cliente,accounts
      where groupid=gae_groupid
      and actionid = gae_actionid 
      and find_in_set(cliente.cliente_id,gae_clientes) > 0
      and username=gae_username_alta 
      and (accounts.cliente_id=:cli or find_in_set(:cli , gae_clientes) > 0)
      group by gae_id
      order by groupname,cliente_nombre";

    try {
      $this->pDB->query( "SET NAMES 'UTF8' ");
      $resultp2 = $this->pDB->prepare($sql);
      $datadb=array(':cli'=>$_SESSION['cliente_id']);
      $resultp2->execute($datadb);

      $result = array();
			while($row=$resultp2->fetch(PDO::FETCH_OBJ)) {
				$result[] = $row;
			}

      // $primera_vez = true;
      // $renglon = 0;
      // $cant = 1;
      
      // $titulo = 'Listado de Permisos Eventuales por grupo al '.date('Y-m-d H:i:s');
      // $nombre_archivo = $titulo . '.' . $formato_ver;
      // include '../admin/salidaWriter.php';
      // $doc = new salidaWriter();
      // $xlsRow = 1;
      
    if ($formato_ver == 'xls') {
      PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp, array(
        'memoryCacheSize' => '256MB'
      ));
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->getProperties()->setCreator("Micronauta");
      $objPHPExcel->getDefaultStyle()
      ->getFont()
      ->setName('Calibri')
      ->setSize(10)
      ->setBold(false);
      
      $objPHPExcel->setActiveSheetIndex(0);
      
      $objPHPExcel->getActiveSheet()
      ->getDefaultStyle()
      ->getAlignment()
      ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $objPHPExcel->getActiveSheet()
      ->getDefaultStyle()
      ->getAlignment()
      ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
      
      // $objPHPExcel->getActiveSheet ()->getStyle ( 'A1:A200' )->getAlignment ()->setHorizontal ( PHPExcel_Style_Alignment::HORIZONTAL_LEFT );
      
      $doc->salidaXls($nombre_archivo, $objPHPExcel);
    }
    if ($formato_ver == 'csv') {
      $doc->salidaCsv($nombre_archivo);
    }
    if ($formato_ver == 'html') {
      // echo "entro";
      // $doc->salidaHtml();
      return array(
        "ok" => true,
        "data" =>  $result
      );
    }
    // while($row=$resultp2->fetch(PDO::FETCH_ASSOC)) {
    //   if ($primera_vez) {
    //     $primera_vez = false;
        
  
    //     $doc->setCell(0, $xlsRow, $titulo, array(
    //     'style' => 'colspan="100" align="left"',
    //     'format' => 'b'
    //     ));
    //     $xlsRow ++;
    //     $doc->openTable('class="w3-table-all w3-hoverable w3-border " width="100%"');
    //     $doc->setRowFormat('class="w3-teal"');
        
    //     $doc->setCell(0, $xlsRow, utf8_encode('Grupo'), array(
    //         'tag' => 'th',
    //         'style' => 'align="center"'
    //     ));
    //     $doc->setCell(1, $xlsRow, utf8_encode('Empresas'), array(
    //         'tag' => 'th',
    //         'style' => 'align="center"'
    //     ));
    //     $doc->setCell(2, $xlsRow, utf8_encode('Permiso'), array(
    //         'tag' => 'th',
    //         'style' => 'align="center"'
    //     ));
    //     $doc->setCell(3, $xlsRow, utf8_encode('Desde'), array(
    //         'tag' => 'th',
    //         'style' => 'align="center"'
    //     ));
    //     $doc->setCell(4, $xlsRow, utf8_encode('Hasta'), array(
    //         'tag' => 'th',
    //         'style' => 'align="center"'
    //     ));
    //     $doc->setCell(5, $xlsRow, utf8_encode('Creado'), array(
    //         'tag' => 'th',
    //         'style' => 'align="center"'
    //     ));
    //     $doc->setRowFormat('class=""');
    //     // $doc->closeTable ();
    //   }
        
    //   $xlsRow += 1;
      
    //   $doc->setCell(0, $xlsRow, ($row['groupname']), array(
    //       'style' => 'align="left"'
    //   ));
    //   if ($row['cliente_id'] == $_SESSION['cliente_id']) { 
    //       $doc->setCell(1, $xlsRow, ($row['empresas']), array('style' => 'align="left"'));
    //   } else {
    //       $doc->setCell(1, $xlsRow, '', array('style' => 'align="left"'));
    //   }
    //   $doc->setCell(2, $xlsRow, ($row['actionname']), array(
    //       'style' => 'align="left"'
    //   ));
    //   $doc->setCell(3, $xlsRow, ($row['gae_fecha_desde']), array(
    //       'style' => 'align="left"'
    //   ));
    //   $doc->setCell(4, $xlsRow, ($row['gae_fecha_hasta']), array(
    //       'style' => 'align="left"'
    //   ));
    //   $doc->setCell(5, $xlsRow, ($row['gae_username_alta']." ".$row['gae_fecha_alta']), array(
    //       'style' => 'align="left"'
    //   ));
      
    //   if ($formato_ver =='html' and $row['cliente_id'] == $_SESSION['cliente_id']) {
    //       $doc->setCell(7,$xlsRow,'<span onclick="myFunction('.$row['gae_id'].')" class="w3-button ">&times;</span>');
    //   }
    // }
          
    }catch(PDOException  $e ){

      $e -> getMessage();
      $this-> SetErrorMessage('Error de lectura le','');
      if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura le','');
      return false;
    }
    // $doc->closeTable();
    // $doc->addHtml('</table>');
    // if ($primera_vez) echo "No hay informaci�n para mostrar.";
    // $doc->addHtml($LinkRetorno);
    // $doc->addHtml('<br><br>');

    // $doc->output();
    // // HardFlush();
    // die();
    
            
  }

	function EventualPermission($FormElements) {
		$FormElements ["comandos_id"] = explode(",", $FormElements ["comandos_id"]);

    $cli="";
    $todos=0;
    foreach($FormElements['comandos_id'] as $v) {
      if ($v == "-1") {$todos=1;}
      if ($todos != 1) {$cli.=",".$v;}
    }

    if ($todos) {
      $tipo=4;
      if ($tipo == 4 and $_SESSION['myHierarchy'] == 1) { $tipo =5;}
      
      if ($tipo == 4) {
      $Q="select distinct cliente_id,cliente_nombre from
        (select clienteid id from accountcliente
        where accountid= :accountid and clienteid > 0 and accountid_equivale=0
        union
        select if(ace.cliente_id> 0,ace.cliente_id,if(b.clienteid > 0 ,b.clienteid,if (ac.cliente_id > 0,ac.cliente_id,aca.cliente_id))) from accountcliente a
        left join accountcliente b on a.accountid_equivale=b.accountid
        left join accounts ac on ac.accountid=b.accountid
        left join accounts ace on ace.accountid=b.accountid_equivale
        left join accounts aca on aca.accountid=a.accountid_equivale
        where a.accountid= :accountid
        and a.accountid_equivale > 0
        and aca.cliente_id > 0
        ) ids
        left join cliente
        on ids.id=cliente_id";
        $datadb=array(':accountid'=>$_SESSION['myAccount']);
      }
      if($tipo==5){
        $Q = "SELECT distinct cliente.cliente_id,cliente_nombre FROM cliente where cliente_inactivo=0 ";
        $datadb=array();
      }
      
      $cli="";
      try {
        
        $this->pDB->query( "SET NAMES 'UTF8' ");
        $stmt = $this->pDB->prepare($Q);
        $stmt->execute($datadb);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $cli.=",".$row['cliente_id'];                
      }
      }catch(PDOException  $e ){
        echo $e->getMessage();
      }
    
    }

    $cli = substr($cli,1);
    if ($cli=='') $cli=$_SESSION['cliente_id'];

    $q="insert into groupaction_eventual (gae_groupid,gae_actionid,gae_clientes,gae_fecha_desde,gae_fecha_hasta,gae_username_alta,gae_fecha_alta)
        values(:acc,:permiso,:clientes,:fdesde,:fhasta,:user,now())";
    try {
      $this->pDB->query( "SET NAMES 'UTF8' ");
			$resultp = $this->pDB->prepare($q);
      $datadb=array(':acc'=>$FormElements['grupo'],':permiso'=> $FormElements['permiso'],':clientes'=>$cli,':fdesde'=>substr($FormElements['fdesde'],0,10).' '.substr($FormElements['hdesde'],0,5),':fhasta'=>substr($FormElements['fhasta'],0,10).' '.substr($FormElements['hhasta'],0,5),':user'=>$_SESSION['username']);
			$resultp->execute($datadb);
      
			return array(
				"ok" => true,
				"errorMsg" => "Permiso grabado Correctamente"
			);
    }catch(PDOException  $e ){
      // echo $e -> getMessage();
      return array(
        "ok" => false,
        "errorMsg" =>  'No se pudo grabar el permiso, reintente.',
      );
    }

	}

  function ListGroups($search ){
      if (strlen($search) != 0) {
        $filter = "AND groupname LIKE '". $search ."%' ";
      }
      else{
        $filter = "";
      }

      
      // $sql = "SELECT * FROM groups WHERE hierarchy >= :hier and (cliente_id=:cli or cliente_id = 0)". $filter .
      // "ORDER BY hierarchy,groupname";

      $sql="SELECT * FROM groups WHERE hierarchy >=".intval($_SESSION['myHierarchy'])."
      and groupid <> 1
      and (cliente_id='".intval($_SESSION['cliente_id'])."'
      or (cliente_id=0 and ".intval($_SESSION['myHierarchy'])." = 1 ) )". $filter .
      "ORDER BY hierarchy";

      $_SESSION['groups_read_from_table'] = "";
      $groups_read_from_table = array();

      try {
        
        $resultp2 = $this->pDB->prepare($sql);
        $datadb=array(':hier'=>$_SESSION['myHierarchy'],':cli'=>$_SESSION['cliente_id']);
        $resultp2->execute($datadb);

        $result = array();
        while($row=$resultp2->fetch(PDO::FETCH_OBJ)) {
          $groups_read_from_table[$row->groupid] = $row->groupid;

          $row -> edit = false;
          $row -> delete = false;


          if ($row->cliente_id == $_SESSION['cliente_id']) {
            # Edit
            if ($this->mySecurity-> isAllowedTo(8)){
              $row -> edit = true;
            }
            # Delete groupid: 1 should always be the admins
            if ($this->mySecurity-> isAllowedTo(7) AND $row->groupid != $_SESSION['myGroupId'] AND $row->groupid != 1){
              $row -> delete = true;
            }
          }
          $result[] = $row;
        }

        $_SESSION['groups_read_from_table'] = $groups_read_from_table;

        return array(
          "ok" => true,
          "data" =>  $result
        );
      }
      catch(PDOException  $e ){
        // $e-> getMessage();
        $this-> SetErrorMessage('Error de lectura lg','');
        if ($_SESSION['IS_ERROR_REPORTING']){
          return array(
            "ok" => false,
            "errorMsg" =>  'Error de lectura lg',
          );
        } 
      }


          
          
          // while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
          //         $listar = "&nbsp;";
          //         $edit = "&nbsp;";
          //         $delete = "&nbsp;";
                  
          //         $groups_read_from_table[$row2->groupid] = $row2->groupid;
                  
          //         $listar = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"groupList.php?groupId=".$row2->groupid."&groupNombre=".$row2->groupname."&mode=listar\">Usuarios</a>";
                  
          //         if ($row2->cliente_id == $_SESSION['cliente_id']) {
          //             # Edit
          //             if ($this->mySecurity-> isAllowedTo(8))
          //                 $edit = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"groupsModify.php?groupId=".$row2->groupid."&mode=edit\">Editar</a>";
          //                 # Delete
          //                 # groupid: 1 should always be the admins
          //                 if ($this->mySecurity-> isAllowedTo(7) AND
          //                     $row2->groupid != $_SESSION['myGroupId'] AND
          //                     $row2->groupid != 1)
          //                     $delete = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"groupsModify.php?groupId=".$row2->groupid."&mode=delete\">Borrar</a>";
          //         }

          //         $passtru = new PassTru();
          //         $passtru->SetValue($value=$row2->groupname);
          //         $passtru->SetClass("DataTD");
          //         $this->myForm-> AddFormElementToNewLine($passtru);
                  
          //         $passtru = new PassTru(); //Anadido Pablo 10/2008
          //         $passtru->SetValue($value=$row2->hierarchy);
          //         $passtru->SetClass("DataTD");
          //         $this->myForm-> AddFormElement($passtru);
                  
          //         $passtru = new PassTru(); //Anadido Pablo 09/2017
          //         $passtru->SetValue($listar);
          //         $passtru->SetClass("DataTD");
          //         $this->myForm-> AddFormElement($passtru);
                  
          //         $passtru = new PassTru(); //Anadido Pablo 10/2008
          //         $passtru->SetValue($edit);
          //         $passtru->SetClass("DataTD");
          //         $this->myForm-> AddFormElement($passtru);
                  
          //         $passtru = new PassTru(); //Anadido Pablo 10/2008
          //         $passtru->SetValue($delete);
          //         $passtru->SetClass("DataTD");
          //         $this->myForm-> AddFormElement($passtru);
          // }
      // }catch(PDOException  $e ){
      //     $this-> SetErrorMessage('Error de lectura lg','');
      //     if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura lg','');
          
      // }
      

      # the reason of this one is to prevent people, sending bogus
      # groups rom the URL. We will match the groupid coming from the URL
      # with the one in session.

      // $_SESSION['groups_read_from_table'] = $groups_read_from_table;

      // $passtru = new PassTru("");
      // $value = "<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"adminmenu.php\"><-- Volver al Men� Seguridad</a>";
      // $passtru-> SetClass("");
      // $passtru-> SetColSpan( $this->myForm-> GetNumberOfColumns() );
      // $passtru-> SetCellAttributes(array('align'=>'left'));
      // $passtru-> SetValue( $value );

      // $this->myForm-> AddFormElement  ($passtru);

      // $this->SendTrailer( );
    
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
            $this-> SetErrorMessage("Por favor, aseg�rese que no haya usuarios en este grupo.");

          $buttons = new ObjectArray("buttons");
          $buttons->AddObject(new SubmitButton("B_submit","Confirma el borrado"));
          $buttons->AddObject(new SubmitButton("B_cancel","Cancelar"));
          $buttons->SetColSpan($myForm-> GetNumberOfColumns());
          $buttons->SetCellAttributes(array("align"=>"middle"));

          $myForm-> AddFormElement ($buttons);
        }

        break;

      default:

        if ($this->mySecurity-> isAllowedTo(6))
		{
          $buttons = new ObjectArray("buttons");
          $buttons->AddObject(new SubmitButton("B_add_submit","A�ade Nuevo Grupo"));
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

    $extraSql=" WHERE hierarchy >=".intval($_SESSION['myHierarchy'])."
    				and groupid <> 1
    				and (cliente_id='".intval($_SESSION['cliente_id'])."'
    						or (cliente_id=0 and ".intval($_SESSION['myHierarchy'])." = 1 ) )
    		
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
              ." WHERE groupid=:grupo
            		order by actionname";
      $_SESSION['group_actions'] = "";
      $group_Actions = array();
      
      try {
          $resultp2 = $this->pDB->prepare($SQL);
          $datadb=array(':grupo'=>$FormElements["groupid"]);
          $resultp2->execute($datadb);
          while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
              $group_Actions[$row2->actionid] = $row2->actionid;
          }
      }catch(PDOException  $e ){
          $this-> SetErrorMessage('Error de lectura sqgaaf','');
          if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura sqgaaf','');
          return false;
      }
      
      
      
//       $SQL = "SELECT a.* FROM actions a "
//             ." LEFT JOIN groupactions ga ON a.actionid=ga.actionid "
//             ." WHERE groupid=".intval($FormElements["groupid"])."
//             		order by actionname";

            
//       #
//       # Store this information in session, so we wont get bogus
//       #
//       $result2 = $this->gDB->Execute($SQL);

//       if ($result2 === false)
//       {
//         $this->SetErrorMessage('error reading: '
//                               .$this->gDB->ErrorMsg( ));

//         if ($_SESSION['IS_ERROR_REPORTING'])
//           $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

//         return false;
//       }

//       $_SESSION['group_actions'] = "";
//       $group_Actions = array();

//       while (!$result2->EOF)
//       {
//         $group_Actions[$result2-> fields('actionid')] = $result2-> fields('actionid');
//         $result2->MoveNext( );
//       }

      $_SESSION['group_actions'] = $group_Actions;

      #
      # end of Store this information in session, so we wont get bogus
      #

      $groupActions-> SetSQL($SQL,$datadb);

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
          $SQL = "SELECT actions.* FROM actions 
                    left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
WHERE acd.actionid is null and actions.actionid NOT IN "
              ."($actions)";
              else
                  $SQL = "SELECT actions.* FROM actions 
                    left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
where acd.actionid is null and 1= 1";
                  
                  $SQL .=" and (actionclase = 0 or :hier=1)";
                  $SQL .= " order by actionname";
      
                  $_SESSION['available_actions'] = "";
                  $availableActions = array();
      try {
          $resultp2 = $this->pDB->prepare($SQL);
          $datadb=array(':hier'=>$_SESSION['myHierarchy']);
          $resultp2->execute($datadb);
          while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
              $availableActions[$row2->actionid] = $row2->actionid;
          }
      }catch(PDOException  $e ){
          $this-> SetErrorMessage('Error de lectura sqgaaf','');
          if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura sqgaaf','');
          return false;
      }
      
//       if ($actions)
//         $SQL = "SELECT * FROM actions WHERE actionid NOT IN "
//               ."($actions)";
//       else
//         $SQL = "SELECT * FROM actions where 1= 1";

//         $SQL .=" and (actionclase = 0 or ".$_SESSION['myHierarchy']."=1)";
//         $SQL .= " order by actionname";

//       #
//       # Store this information in session, so we wont get bogus
//       #
//       $result2 = $this->gDB->Execute($SQL);

//       if ($result2 === false)
//       {
//         $this->SetErrorMessage('error reading: '
//                               .$this->gDB->ErrorMsg( ));

//         if ($_SESSION['IS_ERROR_REPORTING'])
//           $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

//         return false;
//       }

//       $_SESSION['available_actions'] = "";
//       $availableActions = array();

//       while (!$result2->EOF)
//       {
//         $availableActions[$result2-> fields('actionid')] = $result2-> fields('actionid');
//         $result2->MoveNext( );
//       }

      $_SESSION['available_actions'] = $availableActions;

      #
      # end of Store this information in session, so we wont get bogus
      #


      $allActionsExceptGroup-> SetSQL($SQL,$datadb);

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
              ." WHERE groupid=:grupo AND a.accountid > 1";
              
      $_SESSION['group_accounts'] = "";
      $group_Accounts = array();
      try {
          $resultp2 = $this->pDB->prepare($SQL);
          $datadb=array(':grupo'=>$FormElements["groupid"]);
          $resultp2->execute($datadb);
          while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
              $group_Accounts[$row2->accountid] = $row2->accountid;
          }
      }catch(PDOException  $e ){
          $this-> SetErrorMessage('Error de lectura sqgaaf','');
          if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura sqgaaf','');
          return false;
      }
      
//       $SQL = "SELECT a.* FROM accounts a "
//             ." LEFT JOIN groupaccounts ga ON a.accountid=ga.accountid "
//             ." WHERE groupid=".intval($FormElements["groupid"])." AND a.accountid > 1";


//       #
//       # Store this information in session, so we wont get bogus
//       #
//       $result2 = $this->gDB->Execute($SQL);

//       if ($result2 === false)
//       {
//         $this->SetErrorMessage('error reading: '
//                               .$this->gDB->ErrorMsg( ));

//         if ($_SESSION['IS_ERROR_REPORTING'])
//           $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

//         return false;
//       }

//       $_SESSION['group_accounts'] = "";
//       $group_Accounts = array();

//       while (!$result2->EOF)
//       {
//         $group_Accounts[$result2-> fields('accountid')] = $result2-> fields('accountid');
//         $result2->MoveNext( );
//       }

      $_SESSION['group_accounts'] = $group_Accounts;

      #
      # end of Store this information in session, so we wont get bogus
      #

      $groupAccounts-> SetSQL($SQL,$datadb);

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

      $SQL = "SELECT accountid,lastname,firstname FROM accounts "
            ."WHERE accountid NOT IN ($accounts)";

        $_SESSION['available_accounts'] = "";
        $availableAccounts = array();
                
        try {
            $resultp2 = $this->pDB->prepare($SQL);
            $resultp2->execute();
            while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
                $availableAccounts[$row2->accountid] = $row2->accountid;
            }
        }catch(PDOException  $e ){
            $this-> SetErrorMessage('Error de lectura sqgaaf','');
            if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura sqgaaf','');
            return false;
        }
            
//       #
//       # Store this information in session, so we wont get bogus
//       #
//       $result2 = $this->gDB->Execute($SQL);

//       if ($result2 === false)
//       {
//         $this->SetErrorMessage('error reading: '
//                               .$this->gDB->ErrorMsg( ));

//         if ($_SESSION['IS_ERROR_REPORTING'])
//           $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

//         return false;
//       }

//       $_SESSION['available_accounts'] = "";
//       $availableAccounts = array();

//       while (!$result2->EOF)
//       {
//         $availableAccounts[$result2-> fields('accountid')] = $result2-> fields('accountid');
//         $result2->MoveNext( );
//       }

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
      $SQL = "SELECT empresa_id,empresa_nombre FROM ".$_SESSION[db_cli].".empresa "
            ."WHERE empresa_id IN ($empresas)";

        $_SESSION['group_empresas'] = "";
        $group_Empresas = array();
        try {
            $resultp2 = $this->pDBcli->prepare($SQL);
            $resultp2->execute();
            while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
                $group_Empresas[$row2->empresa_id] = $row2->empresa_id;
            }
        }catch(PDOException  $e ){
            $this-> SetErrorMessage('Error de lectura sqgaaf','');
            if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura sqgaaf','');
            return false;
        }
            
//       #
//       # Store this information in session, so we wont get bogus
//       #
//       $result2 = $this->gDBcli->Execute($SQL);

//       if ($result2 === false)
//       {
//         $this->SetErrorMessage('error reading: '
//                               .$this->gDB->ErrorMsg( ));

//         if ($_SESSION['IS_ERROR_REPORTING'])
//           $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

//         return false;
//       }

//       $_SESSION['group_empresas'] = "";
//       $group_Empresas = array();

//       while (!$result2->EOF)
//       {
//         $group_Empresas[$result2-> fields('empresa_id')] = $result2-> fields('empresa_id');
//         $result2->MoveNext( );
//       }

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

      $SQL = "SELECT empresa_id empresaid,empresa_nombre FROM ".$_SESSION[db_cli].".empresa "
            ."WHERE empresa_id NOT IN ($empresas)";
//            ." and empresa_id <=6";

        $_SESSION['available_empresas'] = "";
        $availableEmpresas = array();
            
        try {
            $resultp2 = $this->pDBcli->prepare($SQL);
            $resultp2->execute();
            while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
                $availableEmpresas[$row2->empresaid] = $row2->empresaid;
            }
        }catch(PDOException  $e ){
            $this-> SetErrorMessage('Error de lectura sqgaaf','');
            if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura sqgaaf','');
            return false;
        }
            
//       #
//       # Store this information in session, so we wont get bogus
//       #
//       $result2 = $this->gDBcli->Execute($SQL);

//       if ($result2 === false)
//       {
//         $this->SetErrorMessage('error reading: '
//                               .$this->gDB->ErrorMsg( ));

//         if ($_SESSION['IS_ERROR_REPORTING'])
//           $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$SQL  );

//         return false;
//       }

//       $_SESSION['available_empresas'] = "";
//       $availableEmpresas = array();

//       while (!$result2->EOF)
//       {
//         $availableEmpresas[$result2-> fields('empresaid')] = $result2-> fields('empresaid');
//         $result2->MoveNext( );
//       }

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

    $passtru->SetValue("<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"adminmenu.php\"><-- Volver al Men� Seguridad</a>");

    $myForm-> AddFormElementToNewLine ($passtru);

    echo $myForm->GetFormInTable( );
  }

  /**
  * Method to check the form. Sets the error message and which field is wrong.
  * @public
  * @returns bool
  */
  function ErrCheckGroupsForm($FormElements,$groupId){
    $this->mIsError = 0;

    if (!$FormElements["hierarchy"]) {
      $this->mErrorMessage = "Por favor, ingrese una Jerarquia al grupo.";
      $this->mFormErrors["hierarchy"]=1;
      $this->mIsError = 1;
    }

    if (!$FormElements["groupname"]){
      $this->mErrorMessage = "Por favor ingrese un nombre al grupo.";
      $this->mFormErrors["groupname"]=1;
      $this->mIsError = 1;
    }

    # check if the group name is in the database

    $groupid = $this-> GetGroupIdByName($FormElements['groupname']);

    if ($groupid){
      if ($groupid != $groupId){
        // $this->SetErrorMessage("El nombre de Grupo ya existe. Intente con otro.");
        $this->mErrorMessage = "El nombre de Grupo ya existe. Intente con otro.";
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
    $this->myForm-> SetFormHeader("Lista de Grupos");

    $mylabel = new Label($name="lb1",$value="Nombre del Grupo");
    $mylabel-> SetClass("ColumnTD");
    $this->myForm-> AddFormElementToNewLine($mylabel);

    $mylabel = new Label($name="lb1",$value="Jerarquia"); //Anadido por Pablo 10/2008
    $mylabel-> SetClass("ColumnTD");//Anadido por Pablo 10/2008
    $this->myForm-> AddFormElement($mylabel);

    $mylabel = new Label($name="lb1",$value="Listar");//Anadido por Pablo 10/2008
    $mylabel-> SetClass("ColumnTD");//Anadido por Pablo 10/2008
    $this->myForm-> AddFormElement($mylabel);
    
    $mylabel = new Label($name="lb1",$value="Editar");//Anadido por Pablo 10/2008
    $mylabel-> SetClass("ColumnTD");//Anadido por Pablo 10/2008
    $this->myForm-> AddFormElement($mylabel);

    $mylabel = new Label($name="lb1",$value="Borrar");//Anadido por Pablo 10/2008
    $mylabel-> SetClass("ColumnTD");//Anadido por Pablo 10/2008
    $this->myForm-> AddFormElement($mylabel);
  }


  function ListGroupAccounts() {
    // echo("cliente_id ");
    // var_dump($_SESSION ['cliente_id']);

  	if ($_SESSION['myAccount'] == 1) {} 
    else {
      $sql = "SELECT cliente_nombre,hierarchy,a.* FROM accounts a
      LEFT JOIN groupaccounts ga ON a.accountid=ga.accountid
      LEFT JOIN groups g ON g.groupid=ga.groupid
      LEFT JOIN cliente ON a.cliente_id=cliente.cliente_id
      WHERE (hierarchy >=:hier or hierarchy is null)
      AND g.groupid=:grupo
      AND a.accountid > 1
      AND a.cliente_id =:cli
      GROUP BY accountid  ORDER BY firstname,lastname";
  	    
  	}
    try {      
      $this->pDB->query( "SET NAMES 'UTF8' ");
      $resultp2 = $this->pDB->prepare($sql);
      $datadb=array(':hier'=>$_SESSION['myHierarchy'],':grupo'=>$_POST['groupId'],':cli'=>$_SESSION ['cliente_id']);

      $resultp2->execute($datadb);

      $result = array();
      while($row=$resultp2->fetch(PDO::FETCH_OBJ)) {
        $result[] = $row;
        $row -> edit = false;
				$row -> delete = false;

        $accounts_read_from_table [$row->accountid] = $row->accountid;
        // Edit
        if ($this->mySecurity->isAllowedTo ( 12 )) {
					$row -> edit = true;

        }
        // Delete
        if ($this->mySecurity->isAllowedTo ( 11 ) and $row->accountid != 1 and $row->accountid != $_SESSION ['myAccount']) {
					$row -> delete = true;

        }
      }
      return array(
        "ok" => true,
        "data" =>  $result
      );
    }
    catch(PDOException  $e ){
      $this-> SetErrorMessage('Error de lectura sqgaaf','');
      if ($_SESSION['IS_ERROR_REPORTING'])
      return array(
        "ok" => false,
        "ErrorMsg" =>  'Error de lectura sqgaaf'
      );
    }
    
  }
    
  function SendTrailer( )
  {
    echo $this->myForm-> GetFormInTable( );
  }
}
?>