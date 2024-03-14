<?php
include_once "securityConfig.inc.php";
require_once "Security.class.php";
include_once "commonFunctions.php";
include_once (ADODB_DIRECTORY . "/adodb.inc.php");
require_once "Form.class.php";
require_once "MyDatabase.class.php";

session_start ();

/**
 * A class that handles adding, modifying and deleting the Accounts
 * from the table.
 * 
 * @author Bulent Tezcan. bulent@greenpepper.ca
 *        
 */
class Accounts extends MyDatabase {
	var $mIsEdit;
	
	/**
	 * Constructor of the class Accounts.
	 * @public
	 */
	function Accounts() {
		// set the table properties
		$this->mTableName = "accounts";
		$this->mKeyName = "accountid";
		
		// set the Column Properties. These are required to be able to
		// write/update the table
		$this->mTableFields ['accountid'] ['type'] = "integer";
		$this->mTableFields ['cliente_id'] ['type'] = "integer";
		$this->mTableFields ['firstname'] ['type'] = "string";
		$this->mTableFields ['lastname'] ['type'] = "string";
		$this->mTableFields ['initials'] ['type'] = "string";
		$this->mTableFields ['username'] ['type'] = "string";
		$this->mTableFields ['username'] ['unique'] = TRUE;
		$this->mTableFields ['password'] ['type'] = "string";
		$this->mTableFields ['expired'] ['type'] = "integer";
		$this->mTableFields ['expireddate'] ['type'] = "integer";
		$this->mTableFields ['tries'] ['type'] = "integer";
		$this->mTableFields ['lasttrieddate'] ['type'] = "integer";
		$this->mTableFields ['fecha_desde_reporte'] ['type'] = "string";
		$this->mTableFields ['validez_desde'] ['type'] = "string";
		$this->mTableFields ['validez_hasta'] ['type'] = "string";
		
		// set other properties
		$this->mFormName = "AccountsForm";
		$this->mExtraFormText = "";
		
		$this->mySecurity = new Security ();
		
		// Set up database connection
		$this->MyDatabase ();
	}
	/**
	 * Method to get the form name.
	 * @public
	 * 
	 * @return string
	 */
	function GetFormName() {
		return $this->mFormName;
	}
	/**
	 * Method to get the group information with a given key.
	 * @public
	 * 
	 * @return array
	 */
	function GetAccount($key) {
		$sql = "SELECT * FROM accounts " . "WHERE accountid = $key";
		
		$result = $this->gDB->Execute ( $sql );
		
		if ($result === false) {
			$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () . "<br>" . $sql );
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
			
			return false;
		}
		
		return $result;
	}
	
	/**
	 * Method to get the account name by a given accountid.
	 * @public
	 * 
	 * @return string
	 */
	function GetAccountName($accountid) {
		// MySql doesn't support concatenation
		//
		$sql = "SELECT firstname,lastname " . "FROM accounts WHERE accountid = $accountid";
		
		$result = $this->gDB->Execute ( $sql );
		
		if ($result === false) {
			$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
			
			return false;
		}
		
		return $result->fields ( "firstname" ) . ", " . $result->fields ( "lastname" );
	}
	
	/**
	 * Method to get the account information with a given name.
	 * @public
	 * 
	 * @return array
	 */
	function GetAccountIdByUserName($name) {
		$sql = "SELECT * FROM accounts WHERE username=" . ToSQL ( htmlspecialchars ( $name ), $this->mTableFields ['username'] ['type'] );
		
		$result = $this->gDB->Execute ( $sql );
		
		if ($result === false) {
			$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
			return false;
		}
		return $result->fields ( "accountid" );
	}
	
	/**
	 * Method to delete group from the table with a given key.
	 * @public
	 * 
	 * @return bool
	 */
	function DeleteAccount($accountid) {
		// another precaution, admin account and signed in persons
		// account could not be deleted
		if ($accountid == 1 or $accountid == $_SESSION ['myAccount'])
			return false;
		
		$sql = "DELETE FROM accounts WHERE accountid =" . $accountid;
		$result = $this->gDB->Execute ( $sql );
		return $this->Update ();
	}
	
	/**
	 * Method to add groups to groupaccounts table for a specific account.
	 * @public
	 * 
	 * @return bool
	 */
	function AddGroupsToAccount($FormElements) {
		if (count ( $FormElements ["availablegroups"] )) {
			// Check for the bogus actions
			//
			foreach ( $FormElements ["availablegroups"] as $key => $groupid ) {
				if (! array_key_exists ( $groupid, $_SESSION ['available_groups'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["availablegroups"] as $key => $groupid ) {
				$sql = "INSERT INTO groupaccounts (groupid,accountid) " . "      VALUES(" . $groupid . "," . $_GET ['accountId'] . ")";
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to remove groups from groupaccounts table for a specific account.
	 * @public
	 * 
	 * @return bool
	 */
	function RemoveGroupsFromAccount($FormElements) {
		if (count ( $FormElements ["accountgroups"] )) {
			// Check for the bogus actions
			//
			foreach ( $FormElements ["accountgroups"] as $key => $groupid ) {
				if (! array_key_exists ( $groupid, $_SESSION ['accounts_group'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["accountgroups"] as $key => $groupid ) {
				$sql = "DELETE FROM groupaccounts " . " WHERE accountid =" . $_GET ['accountId'] . " AND groupid=$groupid";
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to update the counter for bad attempts.
	 * @public
	 * 
	 * @return bool
	 */
	function BadAttempt($accountid, $accountTries, $lastTriedDate) {
		$this->Field ( $this->mKeyName, $accountid );
		$this->Field ( "tries", $accountTries + 1 );
		$this->Field ( "lasttrieddate", time () );
		
		$errorMessage = $themepsw ['account'] ['error'];
		
		if ($accountTries + 1 > BAD_ATTEMPTS_MAX) {
			if (($lastTriedDate + BAD_ATTEMPTS_WAIT_SECONDS) > time ()) {
				$this->SetErrorMessage ( "You tried at least " . BAD_ATTEMPTS_MAX . " times. Now you have to wait for " . ( string ) (BAD_ATTEMPTS_WAIT_SECONDS / 60) . " min before the next try." );
				
				return false;
			} else {
				$this->Field ( "tries", 1 );
			}
		}
		
		$this->Update ();
		
		$this->SetErrorMessage ( $errorMessage );
		
		return false;
	}
	
	/**
	 * Method to update Accounts table with a given key, fields and values.
	 * @public
	 * 
	 * @return bool
	 */
	function UpdateAccount($FormElements, $accountid) {
		$this->Field ( $this->mKeyName, $accountid );
		
		foreach ( $this->mTableFields as $key => $value ) {
			if ($key != $this->mKeyName) {
				if ($FormElements [$key])
					$this->Field ( $key, $FormElements [$key] );
			}
		}
		
		// special case for accounts table
		if ($FormElements ["oldpassword"] != "" or $FormElements ["blanquear"] == "1") {
			if (isset ( $FormElements ['newpassword'] ) and isset ( $FormElements ['confirmpassword'] )) {
				if (USE_MD5)
					$password = md5 ( $FormElements ['newpassword'] );
				else
					$password = $FormElements ['newpassword'];
				
				$this->Field ( 'password', $password );
			}
		}
		if (isset ( $FormElements ['expired'] )) {
			$this->Field ( 'expired', 1 );
			$this->Field ( 'expireddate', time () );
		} else {
			$this->Field ( 'expired', 0 );
			$this->Field ( 'expireddate', 0 );
		}
		
		
		// if ($FormElements["fecha_desde_reporte"] > "") {
		$input = $FormElements ["fecha_desde_reporte"];
		$input = trim ( $input );
		$time = strtotime ( $input );
		$is_valid = date ( 'Y-m-d', $time ) == $input;
		
		if (date ( 'Y-m-d', $time ) != $input) {
			$this->Field ( 'fecha_desde_reporte', '0' );
		}
		// }
		$input = $FormElements ["validez_desde"];
		$input = trim ( $input );
		$time = strtotime ( $input );
		$is_valid = date ( 'Y-m-d H:i:s', $time ) == $input;
		
		if (date ( 'Y-m-d H:i:s', $time ) != $input) {
		    $this->Field ( 'validez_desde', '0' );
		}
		
		$input = $FormElements ["validez_hasta"];
		$input = trim ( $input );
		$time = strtotime ( $input );
		$is_valid = date ( 'Y-m-d H:i:s', $time ) == $input;
		
		if (date ( 'Y-m-d H:i:s', $time ) != $input) {
		    $this->Field ( 'validez_hasta', '0' );
		}
		
		
		return $this->Update ();
	}
	
	/**
	 * Method to add Accounts to the table.
	 * @public
	 * 
	 * @return bool
	 */
	Function AddAccount($FormElements) {
		global $ezMap;
		foreach ( $this->mTableFields as $key => $value ) {
			if ($key != $this->mKeyName) {
				if ($FormElements [$key])
					$this->Field ( $key, $FormElements [$key] );
			}
		}
		
		// we need to to this, since the field name DOES NOT match with the
		// table field name ie: newpassword is the name in the form
		// but password is the field name in the table
		
		if (USE_MD5)
			$password = md5 ( $FormElements ['newpassword'] );
		else
			$password = $FormElements ['newpassword'];
		
		$this->Field ( 'password', $password );
		
		$this->Field ( 'expired', 0 );
		
		$this->Field ( 'tries', 0 );
		
		// Agregado por Eugenio 1/09/06
		if ($_SESSION ['myHierarchy'] > 1) {
			$this->Field ( 'cliente_id', $_SESSION ['cliente_id'] );
		}
		
		// insert the new account
		
		$newAccountId = ( int ) $this->InsertNew ();
		
		if ($newAccountId)
			$this->mErrorMessage = "Usuario agregado con �xito.";
		else
			return false;
		
		$sql = "INSERT INTO groupaccounts (groupid,accountid) " . "VALUES(" . $FormElements ['groupid'] . "," . $newAccountId . ")";
		
		if ($result = $this->gDB->Execute ( $sql ) === false) {
			$this->mErrorMessage = 'error on insert: ' . $this->gDB->ErrorMsg ();
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Method to get the groupids the account belongs to.
	 * @public
	 * 
	 * @return string
	 */
	Function GetAllGroupsAccountHas($accountID) {
		$sql = "SELECT groupid FROM groupaccounts WHERE accountid=" . $accountID;
		
		$result = $this->gDB->Execute ( $sql );
		
		if ($result === false) {
			$this->mErrorMessage = 'error reading: ' . $this->gDB->ErrorMsg ();
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
		}
		
		$string = "0"; // we put zero, not to get SQL error
		
		while ( ! $result->EOF ) {
			if ($string)
				$string .= ",";
			$string .= $result->fields ( "groupid" );
			
			$result->MoveNext ();
		}
		
		return $string;
	}
	
	/**
	 * Method to set the error message.
	 * @public
	 * 
	 * @return bool
	 */
	function SetErrorMessage($message) {
		if (is_string ( $message ))
			$this->mErrorMessage = $message;
		return true;
	}
	/**
	 * Method to get the error message.
	 * @public
	 * 
	 * @return string
	 */
	function GetErrorMessage() {
		return $this->mErrorMessage;
	}
	/**
	 * Method to display all the Accounts.
	 * @private
	 * 
	 * @return string
	 */
	function ListAccounts($selectlisttype) {
		if ($_SESSION ['myAccount'] == 1) {
			$sql = "SELECT cliente_nombre,hierarchy,a.accountid,a.* FROM megacontrol.accounts a " . " LEFT JOIN megacontrol.groupaccounts ga ON a.accountid=ga.accountid " . " LEFT JOIN megacontrol.groups g ON g.groupid=ga.groupid " . " LEFT JOIN megacontrol.cliente ON a.cliente_id=cliente.cliente_id " . " WHERE (hierarchy >=" . $_SESSION ['myHierarchy'] . " or hierarchy is null)" . " GROUP BY accountid" . " ORDER BY firstname,lastname";
			// ." AND a.accountid=ga.accountid AND g.groupid=ga.groupid " . $userExtra
		} else {
			$sql = "SELECT cliente_nombre,hierarchy,a.accountid,a.* FROM megacontrol.accounts a " . " LEFT JOIN megacontrol.groupaccounts ga ON a.accountid=ga.accountid " . " LEFT JOIN megacontrol.groups g ON g.groupid=ga.groupid " . " LEFT JOIN megacontrol.cliente ON a.cliente_id=cliente.cliente_id " . " WHERE (hierarchy >=" . $_SESSION ['myHierarchy'] . " or hierarchy is null)" . " AND a.accountid > 1" . " AND a.cliente_id ='" . $_SESSION ['cliente_id'] . "' GROUP BY accountid" . " ORDER BY firstname,lastname";
			// ." AND a.accountid=ga.accountid AND g.groupid=ga.groupid " . $userExtra
		}
		?>
<table class="micronautaFormTABLE" cellspacing="1" cellpadding="5"
	align="center">
	<tr>
		<td colspan="6" class="micronautaFormHeaderFont" width="100%"
			align="middle">Lista de Usuarios</td>
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
			//if ($this->mySecurity->isAllowedTo ( 'modify account' )) {
			if ($this->mySecurity->isAllowedTo ( 12 )) {
				$edit = "\n<a class=\"" . $_SESSION ["CSS"] . "LinkButton\" href=\"accountsModify.php?accountId=" . $result->fields ( "accountid" );
				$edit .= "&mode=edit&selectlisttype=$selectlisttype\">Editar</a>";
			}
			// Delete
//			if ($this->mySecurity->isAllowedTo ( 'delete account' ) and $result->fields ( "accountid" ) != 1 and $result->fields ( "accountid" ) != $_SESSION ['myAccount']) {
			if ($this->mySecurity->isAllowedTo ( 11) and $result->fields ( "accountid" ) != 1 and $result->fields ( "accountid" ) != $_SESSION ['myAccount']) {
					
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
							Men� Seguridad</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php
	}
	
	/**
	 * Method to send the form.
	 * The form is displayed within the method with echo.
	 * @private
	 * 
	 * @return string
	 */
	function SendAccountsForm($FormElements, $alsoSendAccountsGroupForm = false, $basico = false) {
		// $basico=true solo permite editar la contrase�a
		$myForm = new Form ( $this->GetFormName () );
		
		$myForm->SetNumberOfColumns ( 2 );
		$myForm->SetCellSpacing ( 1 );
		$myForm->SetCellPadding ( 5 );
		$myForm->SetBorder ( 0 );
		$myForm->SetAlign ( "center" );
		$myForm->SetTableWidth ( "500" );
		$myForm->SetTableHeight ( null );
		$myForm->SetCSS ( $_SESSION ["CSS"] );
		$myForm->SetEmptyCells ( true );
		$myForm->SetFormHeader ( "Informaci�n de Usuarios" );
		
		if ($_SESSION ['myHierarchy'] == 1) {
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Cliente :" ) );
			// El usuario "admin" (jerarqu�a 1) es el �nico que puede asignar un usuario a un cliente determinado
			// el resto de los administradores solamente pueden agregar usuarios dentro del ambito del mismo
			// cliente.
			$cliente = new GetOptions ( $name = "cliente_id", $table = "cliente", $field = "cliente_nombre", $key = "cliente_id", $extraSql = "where cliente_id <> 1 ORDER BY cliente_nombre", $selected = $FormElements ["cliente_id"], $default = "-Seleccione un cliente-", $displayonly = $basico, $size = 0, $multiple = FALSE, $extra = "" );
			if (isset($_GET['accountId'])) {
			    //solamente paarece el cliente 1 si el ususario editado pertenece al cliente 1
    			$q="select cliente_nombre,cliente.cliente_id from cliente ,accounts
    where accountid =".$_GET['accountId']."
    and (cliente.cliente_id <> 1 or accounts.cliente_id=1)
    order by cliente_nombre
    ";
    			$cliente->SetSQL($q);
			}
			$myForm->AddFormElement ( $cliente );
		}
		
		$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Nombre :" ) );
		$myForm->AddFormElement ( new TextField ( "firstname", $FormElements ["firstname"], 30, 50, $basico ) );
		
		$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Apellido :" ) );
		$myForm->AddFormElement ( new TextField ( "lastname", $FormElements ["lastname"], 30, 50, $basico ) );
		
		$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Iniciales :" ) );
		$myForm->AddFormElement ( new TextField ( "initials", $FormElements ["initials"], 30, 50, $basico ) );
		
		$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Nombre de usuario :" ) );
		$myForm->AddFormElement ( new TextField ( "username", $FormElements ["username"], 30, 50, $basico ) );
		
		if ($basico == true) {
			$myForm->AddFormElement ( new Hidden ( "cliente_id", $FormElements ["cliente_id"] ) );
			$myForm->AddFormElement ( new Hidden ( "firstname", $FormElements ["firstname"] ) );
			$myForm->AddFormElement ( new Hidden ( "lastname", $FormElements ["lastname"] ) );
			$myForm->AddFormElement ( new Hidden ( "initials", $FormElements ["initials"] ) );
			$myForm->AddFormElement ( new Hidden ( "username", $FormElements ["username"] ) );
		}
		if ($_GET ['mode'] != 'edit' and $_GET ['mode'] != 'delete') {
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Grupo :" ) );
			$extraSql = " WHERE groupid <> 1 and hierarchy >= " . $_SESSION ['myHierarchy'] ." and (cliente_id='".$_SESSION['cliente_id']."' or cliente_id=0) ORDER BY hierarchy";
			$myForm->AddFormElement ( new GetOptions ( $name = "groupid", $table = "groups", $field = "groupname", $key = "groupid", $extraSql , $selected = $FormElements ["groupid"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 0, $muliple = FALSE, $extra = "" ) );
		}
		
		if ("EDIT" == strtoupper ( $_GET ['mode'] )) {
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Contrase�a Anterior :" ) );
			$myForm->AddFormElement ( new Password ( $name = "oldpassword", $value = "", $size = 10, $maxlength = 10, $extra = "" ) );
			if ($basico == false) {
				$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Blanquear Contrase�a :" ) );
				$myForm->AddFormElement ( new CheckBox ( "blanquear", 1, false, false ) );
			}
		}
		
		if ("DELETE" != strtoupper ( $_GET ['mode'] )) {
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Contrase�a Nueva :" ) );
			$myForm->AddFormElement ( new Password ( $name = "newpassword", $value = "", $size = 10, $maxlength = 10, $extra = "" ) );
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Confirme :" ) );
			$myForm->AddFormElement ( new Password ( $name = "confirmpassword", $value = "", $size = 10, $maxlength = 10, $extra = "" ) );
		}
		if ("EDIT" == strtoupper ( $_GET ['mode'] )) {
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Intentos de Acceso :" ) );
			$myForm->AddFormElement ( new TextField ( "tries", $FormElements ["tries"], 2, 2, $basico ) );
			
			if ($FormElements ["lasttrieddate"] != 0 or $FormElements ["lasttrieddate"] != null or $FormElements ["lasttrieddate"] != "")
				$lastLoginAttempt = date ( "F jS, Y -- g:ia", $FormElements ["lasttrieddate"] );
			else
				$lastLoginAttempt = "&nbsp;";
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "�ltimo intento de acceso:" ) );
			
			$passtru = new PassTru ( $lastLoginAttempt );
			$passtru->SetClass ( "DataTD" );
			
			$myForm->AddFormElement ( $passtru );
		}
		$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Fecha Inicial para reportes(AAAA-MM-DD):" ) );
		$myForm->AddFormElement ( new TextField ( "fecha_desde_reporte", $FormElements ["fecha_desde_reporte"], 10, 10, $basico ) );

		$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Fecha inicial de Validez (AAAA-MM-DD HH:MM:SS):" ) );
		$myForm->AddFormElement ( new TextField ( "validez_desde", $FormElements ["validez_desde"], 19, 19, $basico ) );

		$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Fecha final de Validez (AAAA-MM-DD HH:MM:SS):" ) );
		$myForm->AddFormElement ( new TextField ( "validez_hasta", $FormElements ["validez_hasta"], 19, 19, $basico ) );
		
		switch (TRUE) {
			case "EDIT" == strtoupper ( $_GET ['mode'] ) :
				
//				if ($this->mySecurity->isAllowedTo ( 'Modify Account' ) or $basico == true) {
				if ($this->mySecurity->isAllowedTo ( 12 ) or $basico == true) {
				
					$buttons = new ObjectArray ( "buttons" );
					$buttons->AddObject ( new SubmitButton ( "B_submit", "Confirmar" ) );
					$buttons->AddObject ( new SubmitButton ( "B_cancel", "Cancelar" ) );
					$buttons->SetColSpan ( $myForm->GetNumberOfColumns () );
					$buttons->SetCellAttributes ( array (
							"align" => "middle" 
					) );
					$buttons->SetClass ( "FieldCaptionTD" );
					
					$myForm->AddFormElement ( $buttons );
				}
				break;
			
			case "DELETE" == strtoupper ( $_GET ['mode'] ) :
				
				//if ($this->mySecurity->isAllowedTo ( 'Delete Account' )) {
				if ($this->mySecurity->isAllowedTo ( 11 )) {
					$buttons = new ObjectArray ( "buttons" );
					$buttons->AddObject ( new SubmitButton ( "B_submit", "Confirma borrado" ) );
					$buttons->AddObject ( new SubmitButton ( "B_cancel", "Cancelar" ) );
					$buttons->SetColSpan ( $myForm->GetNumberOfColumns () );
					$buttons->SetCellAttributes ( array (
							"align" => "middle" 
					) );
					$buttons->SetClass ( "FieldCaptionTD" );
					
					$myForm->AddFormElement ( $buttons );
				}
				
				break;
			
			default :
				
				//if ($this->mySecurity->isAllowedTo ( 'Add Account' )) {
				if ($this->mySecurity->isAllowedTo ( 10 )) {
					$buttons = new ObjectArray ( "buttons" );
					$buttons->AddObject ( new SubmitButton ( "B_add_submit", "A�adir nuevo Usuario" ) );
					$buttons->AddObject ( new SubmitButton ( "B_clear", "Limpiar" ) );
					$buttons->SetColSpan ( $myForm->GetNumberOfColumns () );
					$buttons->SetCellAttributes ( array (
							"align" => "middle" 
					) );
					$buttons->SetClass ( "FieldCaptionTD" );
					
					$myForm->AddFormElement ( $buttons );
				}
		}
		
		$myForm->SetErrorMessage ( $this->GetErrorMessage () );
		
		$value = "<a class=\"" . $_SESSION ["CSS"] . "LinkButton\" href=\"adminmenu.php\"><-- Volver al Men� Seguridad</a>";
		$passtru = new PassTru ( $value );
		$passtru->SetColSpan ( $myForm->GetNumberOfColumns () );
		$passtru->SetClass ( "" );
		
		$myForm->AddFormElement ( $passtru );
		
		if ($alsoSendAccountsGroupForm === TRUE) {
			$passtru = new PassTru ( $value ); // Pablo 280809
			$passtru->SetValue ( $this->AccountsGroupForm ( $FormElements, $formTagRequired = FALSE ) );
			$passtru->SetClass ( "DataTD" );
			$passtru->SetColSpan ( $myForm->GetNumberOfColumns () ); // Pablo 280809
			$myForm->AddFormElement ( $passtru );
		}
		
		return $myForm->GetFormInTable ();
	}
	
	/**
	 * Method to send the a form to modify accounts group information.
	 * This method will add or remove groups from the groupaccounts table.
	 * @private
	 * 
	 * @return string
	 */
	function AccountsGroupForm($FormElements, $formTagRequired = TRUE) {
		$myForm = new Form ( "accounts_group_information" );
		
		$myForm->SetNumberOfColumns ( 3 );
		$myForm->SetCellSpacing ( 1 );
		$myForm->SetCellPadding ( 5 );
		$myForm->SetBorder ( 0 );
		$myForm->SetAlign ( "center" );
		$myForm->SetTableWidth ( "75%" );
		$myForm->SetTableHeight ( null );
		$myForm->SetCSS ( $_SESSION ["CSS"] );
		$myForm->SetEmptyCells ( false );
		$myForm->SetFormHeader ( "Informaci�n de Grupos de Usuarios" );
		$myForm->SetFormTagRequired ( $formTagRequired );
		
		$accountID = $_GET ['accountId'];
		
		$accountName = $this->GetAccountName ( $accountID );
		
		$mylabel = new Label ( $name = "lb1", $value = "" );
		$mylabel->SetClass ( "ColumnTD" );
		$mylabel->SetValue ( "Grupos de " . $accountName );
		
		$myForm->AddFormElementToNewLine ( $mylabel );
		$myForm->AddFormElement ( new Dummy () );
		
		$mylabel = new Label ( $name = "lb1", $value = "Grupos Disponibles" );
		$mylabel->SetClass ( "ColumnTD" );
		
		$myForm->AddFormElement ( $mylabel );
		
		$groupAccounts = new GetOptions ( $name = "accountgroups", $table = "groups", $field = "groupname", $key = "groupid", $extraSql = "", $selected = $FormElements ["accountgroups"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 5, $multiple = TRUE, $extra = "", $concat = "" );
		
		$SQL = "SELECT g.* FROM groups g " . " LEFT JOIN groupaccounts ga ON g.groupid=ga.groupid " . " WHERE ga.accountid=" . $accountID;
		
		//
		// Store this information in session, so we wont get bogus
		//
		$result2 = $this->gDB->Execute ( $SQL );
		
		if ($result2 === false) {
			$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
			
			return false;
		}
		
		$_SESSION ['accounts_group'] = "";
		$accounts_group = array ();
		
		while ( ! $result2->EOF ) {
			$accounts_group [$result2->fields ( 'groupid' )] = $result2->fields ( 'groupid' );
			$result2->MoveNext ();
		}
		
		$_SESSION ['accounts_group'] = $accounts_group;
		
		//
		// end of Store this information in session, so we wont get bogus
		//
		
		$groupAccounts->SetSQL ( $SQL );
		
		$groupAccounts->SetZebraColor ( "#EBEBEB" );
		
		$myForm->AddFormElement ( $groupAccounts );
		
		$buttons = new ObjectArray ( "buttons" );
		$buttons->AddObject ( new SubmitButton ( "B_remove_groups", ">>", $class = "b1" ) );
		$buttons->AddObject ( new Label ( "lbl", "<br>" ) );
		$buttons->AddObject ( new SubmitButton ( "B_add_groups", "<<", $class = "b1" ) );
		$buttons->SetCellAttributes ( array (
				"align" => "middle",
				"valign" => "middle" 
		) );
		
		$myForm->AddFormElement ( $buttons );
		
		$availableGroups = new GetOptions ( $name = "availablegroups", $table = "groups", $field = "groupname", $key = "groupid", $extraSql = "", $selected = $FormElements ["availablegroups"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 5, $multiple = TRUE, $extra = "", $concat = "" );
		
		$accountsGroup = $this->GetAllGroupsAccountHas ( $accountID );

		//OR ".$_SESSION['myHierarchy']." = 1 
		$SQL = "SELECT * FROM groups,accounts WHERE hierarchy >= " . $_SESSION ['myHierarchy'] . "
and accountid = $accountID
					and groupid <> 1
    				and (groups.cliente_id='".$_SESSION['cliente_id']."'
    						or (groups.cliente_id=0 ) )
				AND groupid NOT IN (" . $accountsGroup . ")
		    AND ( gr_nivel = 0 or accounts.cliente_id =1 )
					order by hierarchy,groupname";
		
		//
		// Store this information in session, so we wont get bogus
		//
		$result2 = $this->gDB->Execute ( $SQL );
		
		if ($result2 === false) {
			$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
			
			return false;
		}
		
		$_SESSION ['available_groups'] = "";
		$available_Groups = array ();
		
		while ( ! $result2->EOF ) {
			$available_Groups [$result2->fields ( 'groupid' )] = $result2->fields ( 'groupid' );
			$result2->MoveNext ();
		}
		
		$_SESSION ['available_groups'] = $available_Groups;
		
		//
		// end of Store this information in session, so we wont get bogus
		//
		
		$availableGroups->SetSQL ( $SQL );
		
		$availableGroups->SetZebraColor ( "#EBEBEB" );
		
		$myForm->AddFormElement ( $availableGroups );
		
		return $myForm->GetFormInTable ();
	}
	
	/**
	 * Method to send the a form to ask the user if they want to see the
	 * expired accounts or not.
	 * @private
	 * 
	 * @return string
	 */
	function SendExpiredForm($FormElements) {
		$myForm = new Form ( "selectlisttype" );
		
		$myForm->SetNumberOfColumns ( 2 );
		$myForm->SetCellSpacing ( 1 );
		$myForm->SetCellPadding ( 5 );
		$myForm->SetBorder ( 0 );
		$myForm->SetAlign ( "center" );
		$myForm->SetTableWidth ( "300" );
		$myForm->SetTableHeight ( null );
		$myForm->SetCSS ( $_SESSION ["CSS"] );
		$myForm->SetEmptyCells ( true );
		$myForm->SetFormHeader ( "Selecci�n de Lista de Usuarios" );
		
		$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Ver s�lo usuarios desactivados" ) );
		
		if (! isset ( $FormElements ['selectlisttype'] ))
			$FormElements ['selectlisttype'] = 'b';
		
		$radio1 = new RadioButton ( $name = "selectlisttype", $vertical = false, $displayOnly = false );
		$radio1->AddOption ( $label = "", $value = "e", $IsChecked = $FormElements ['selectlisttype'] == 'e' ? 1 : 0, $extra = "onclick=\"javascript:this.form.submit();\"" );
		
		$myForm->AddFormElement ( $radio1 );
		
		$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Ver ambos tipos de usuarios" ) );
		
		$radio2 = new RadioButton ( $name = "selectlisttype", $vertical = false, $displayOnly = false );
		$radio2->AddOption ( $label = "", $value = "b", $IsChecked = $FormElements ['selectlisttype'] == 'b' ? 1 : 0, $extra = "onclick=\"javascript:this.form.submit();\"" );
		
		$myForm->AddFormElement ( $radio2 );
		
		$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Ver s�lo usuarios activos" ) );
		
		$radio3 = new RadioButton ( $name = "selectlisttype", $vertical = false, $displayOnly = false );
		$radio3->AddOption ( $label = "", $value = "r", $IsChecked = $FormElements ['selectlisttype'] == 'r' ? 1 : 0, $extra = "onclick=\"javascript:this.form.submit();\"" );
		
		$myForm->AddFormElement ( $radio3 );
		
		$buttons = new ObjectArray ( "buttons" );
		$buttons->AddObject ( new SubmitButton ( "B_submit", "Seguir" ) );
		$buttons->AddObject ( new SubmitButton ( "B_cancel", "Cancelar" ) );
		$buttons->SetColSpan ( $myForm->GetNumberOfColumns () );
		$buttons->SetCellAttributes ( array (
				"align" => "middle" 
		) );
		
		$myForm->AddFormElement ( $buttons );
		
		return $myForm->GetFormInTable ();
	}
	
	/**
	 * Method to check the form.
	 * Sets the error message and which field is wrong.
	 * @public
	 * 
	 * @return bool
	 */
	function ErrCheckAccountsForm($FormElements, $accountid, $mode) {
		$this->mIsError = 0;
		
		if ("EDIT" != strtoupper ( $mode ) and "DELETE" != strtoupper ( $mode ))
			if (! $FormElements ["groupid"]) {
				$this->mErrorMessage = "Seleccione un grupo para este usuario.";
				$this->mFormErrors ["groupid"] = 1;
				$this->mIsError = 1;
			}
		
		if ("EDIT" == strtoupper ( $mode )) {
			if ($FormElements ["oldpassword"] != "" and $FormElements ["blanquear"] != "1")
				if ($FormElements ["newpassword"] == "" or $FormElements ["newpassword"] != $FormElements ["confirmpassword"]) {
					$this->mErrorMessage = "Aseg�rese de haber tipeado su nueva contrase�a correctamente las dos veces.";
					$this->mFormErrors ["newpassword"] = 1;
					$this->mIsError = 1;
				} 				// make sure they know what they are doing. Only the owner can change
				// their password, or you have to know the old password to change it.
				else {
					$password = htmlspecialchars ( $FormElements ["oldpassword"] );
					
					if (USE_MD5)
						$password = md5 ( $password );
					
					$sql = "SELECT * FROM accounts WHERE accountid=$accountid " . "AND password=" . ToSQL ( $password, "text" );
					
					$result = $this->gDB->Execute ( $sql );
					
					if ($result === false) {
						$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
						
						if ($_SESSION ['IS_ERROR_REPORTING'])
							$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
						
						return false;
					}
					
					if (! $result->fields ( "accountid" )) {
						$this->mErrorMessage = "Aseg�rese de haber tipeado su vieja contrase�a correctamente.";
						$this->mFormErrors ["password"] = 1;
						$this->mIsError = 1;
					}
				}

			if ($FormElements ["blanquear"] == "1")
				if ($FormElements ["newpassword"] == "" or $FormElements ["newpassword"] != $FormElements ["confirmpassword"]) {
					$this->mErrorMessage = "Aseg�rese de haber tipeado su nueva contrase�a correctamente las dos veces.";
					$this->mFormErrors ["newpassword"] = 1;
					$this->mIsError = 1;
				} 				// make sure they know what they are doing. Only the owner can change
				// their password, or you have to know the old password to change it.
				else {
					$sql = "SELECT * FROM accounts WHERE accountid=$accountid limit 1";
					
					$result = $this->gDB->Execute ( $sql );
					
					if ($result === false) {
						$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
						
						if ($_SESSION ['IS_ERROR_REPORTING'])
							$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
						
						return false;
					}
					
					if (! $result->fields ( "accountid" )) {
						$this->mErrorMessage = "Aseg�rese de haber tipeado su vieja contrase�a correctamente.";
						$this->mFormErrors ["password"] = 1;
						$this->mIsError = 1;
					}
				}
		}
		
		if (! $FormElements ["username"]) {
			$this->mErrorMessage = "Por favor, ingrese nombre de usuario.";
			$this->mFormErrors ["username"] = 1;
			$this->mIsError = 1;
		} else {
			$sql = "Select count(*) FROM accounts WHERE username=" . ToSQL ( htmlspecialchars ( $FormElements ["username"] ), $this->mTableFields ['username'] ['type'] );
			if ($accountid)
				$sql .= " AND accountid NOT IN($accountid)";
			
			$result = $this->gDB->Execute ( $sql );
			
			if ($result === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () . "<br>" . $sql );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
				
				return false;
			} elseif ($result->fields ( "count" ) > 0) {
				$this->mErrorMessage = "Nombre de Usuario ya existente.";
				$this->mFormErrors ["username"] = 1;
				$this->mIsError = 1;
			}
		}
		
		if (! $FormElements ["lastname"]) {
			$this->mErrorMessage = "Por favor ingrese el apellido.";
			$this->mFormErrors ["lastname"] = 1;
			$this->mIsError = 1;
		}
		
		if (! $FormElements ["firstname"]) {
			$this->mErrorMessage = "Por favor ingrese el nombre.";
			$this->mFormErrors ["firstname"] = 1;
			$this->mIsError = 1;
		}
		
		// check if the username is in the database
		
		$accountID = $this->GetAccountIdByUserName ( $FormElements ['username'] );
		
		if ($accountID) {
			if ($accountid != $accountID) {
				$this->SetErrorMessage ( "Nombre de usuario ya existe. Intente con otro." );
				$this->mFormErrors ["username"] = 1;
				$this->mIsError = 1;
			}
		}
		
		return $this->mIsError;
	}
	
	/**
	 * Method to set the Html header.
	 * @private
	 * 
	 * @return void
	 */
	function SendHeader() {
		$this->myForm = new Form ( "dummy" );
		
		$this->myForm->SetNumberOfColumns ( 6 );
		$this->myForm->SetCellSpacing ( 1 );
		$this->myForm->SetCellPadding ( 5 );
		$this->myForm->SetBorder ( 0 );
		$this->myForm->SetAlign ( "center" );
		$this->myForm->SetTableWidth ( null );
		$this->myForm->SetTableHeight ( null );
		$this->myForm->SetCSS ( $_SESSION ["CSS"] );
		$this->myForm->SetEmptyCells ( false );
		$this->myForm->SetFormTagRequired ( false );
		// this->myForm-> SetTRMouseOverColor( $overcolor="#FFFF66", $outcolor="#ffcc44", $startingRow=2 );
		$this->myForm->SetFormHeader ( "Lista de Usuarios" );
		
		$mylabel = new Label ( $name = "lb1", $value = "Apellido, Nombre (Nombre de Usuario)" );
		$mylabel->SetClass ( "ColumnTD" );
		$this->myForm->AddFormElementToNewLine ( $mylabel );
		
		$mylabel = new Label ( $name = "lb1", $value = "Apellido, Nombre (Nombre de Usuario)" ); // Pablo 280809
		$mylabel->SetValue ( "Expirado" );
		// $mylabel = new Label($name="lb1",$value="Expirado");
		// $mylabel-> SetClass("ColumnTD");
		$this->myForm->AddFormElement ( $mylabel );
		
		$mylabel = new Label ( $name = "lb1", $value = "Apellido, Nombre (Nombre de Usuario)" ); // Pablo 280809
		$mylabel->SetValue ( "Multi<br>Grupo" );
		// $mylabel = new Label($name="lb1",$value="Multi<br>Grupo");
		// $mylabel-> SetClass("ColumnTD");
		$this->myForm->AddFormElement ( $mylabel );
		
		$mylabel = new Label ( $name = "lb1", $value = "Apellido, Nombre (Nombre de Usuario)" ); // Pablo 280809
		$mylabel->SetValue ( "Editar" );
		// $mylabel = new Label($name="lb1",$value="Editar");
		// $mylabel-> SetClass("ColumnTD");
		$this->myForm->AddFormElement ( $mylabel );
		
		$mylabel = new Label ( $name = "lb1", $value = "Apellido, Nombre (Nombre de Usuario)" ); // Pablo 280809
		$mylabel->SetValue ( "Borrar" );
		// $mylabel = new Label($name="lb1",$value="Borrar");
		// $mylabel-> SetClass("ColumnTD");
		$this->myForm->AddFormElement ( $mylabel );
	}
	
	/**
	 * Method to send the Html in a table.This method is called from the ListAccounts
	 * method.
	 * @private
	 * 
	 * @return void
	 */
	function SendTrailer() {
		echo $this->myForm->GetFormInTable ();
	}
	function SendAccountsAndActionsForm($FormElements) {
		$myForm = new Form ( "accounts_and_actions" );
		
		$myForm->SetNumberOfColumns ( 4 );
		$myForm->SetCellSpacing ( 1 );
		$myForm->SetCellPadding ( 5 );
		$myForm->SetBorder ( 0 );
		$myForm->SetAlign ( "center" );
		$myForm->SetTableWidth ( "50%" );
		$myForm->SetTableHeight ( null );
		$myForm->SetCSS ( $_SESSION ["CSS"] );
		$myForm->SetEmptyCells ( false );
		$myForm->SetFormHeader ( "Usuarios y Acciones" );
		
		$myForm->SetErrorMessage ( $this->mErrorMessage );
		
		$myForm->AddFormElement ( new Label ( "lb1", "Usuario :" ) );
		
		if ($_SESSION ['myAccount'] == 1) {
			$sql = "SELECT hierarchy,a.accountid,a.* FROM megacontrol.accounts a " . " LEFT JOIN megacontrol.groupaccounts ga ON a.accountid=ga.accountid " . " LEFT JOIN megacontrol.groups g ON g.groupid=ga.groupid " . " WHERE (hierarchy >=" . $_SESSION ['myHierarchy'] . " or hierarchy is null)" . " GROUP BY accountid" . " ORDER BY lastname,firstname";
			// ." AND a.accountid=ga.accountid AND g.groupid=ga.groupid " . $userExtra
		} else {
			$sql = "SELECT hierarchy,a.accountid,a.* FROM megacontrol.accounts a " . " LEFT JOIN megacontrol.groupaccounts ga ON a.accountid=ga.accountid " . " LEFT JOIN megacontrol.groups g ON g.groupid=ga.groupid " . " WHERE (hierarchy >=" . $_SESSION ['myHierarchy'] . " or hierarchy is null)" . " AND a.accountid > 1" . " AND a.cliente_id ='" . $_SESSION ['cliente_id'] . "' GROUP BY accountid" . " ORDER BY lastname,firstname";
			// ." AND a.accountid=ga.accountid AND g.groupid=ga.groupid " . $userExtra
		}
		
		$accounts_read_from_table = array ();
		$result = $this->gDB->Execute ( $sql );
		
		while ( ! $result->EOF ) {
			$accounts_read_from_table [$result->fields ( "accountid" )] = $result->fields ( "accountid" );
			// echo '<option value="'.$result->fields("accountid").'" >'.$result->fields("firstname");
			$css_files [$result->fields ( "accountid" )] = trim ( $result->fields ( "lastname" ) ) . "," . $result->fields ( "firstname" );
			
			$result->MoveNext ();
		}
		
		$CSS = new SelectBox ( $name = "accountid", $values = $css_files, $selected = $FormElements ["accountid"], $default = "-Select-", $displayOnly = false, $size = 0, $multiple = FALSE, $extra = "onchange=\"" . $myForm->GetFormName () . ".submit();\"" );
		$myForm->AddFormElement ( $CSS );
		
		// the reason of this one is to prevent people, sending bogus
		// accounts rom the URL. We will match the accountid coming from the URL
		// with the one in session.
		$_SESSION ['accounts_read_from_table'] = $accounts_read_from_table;
		
		$myForm->AddFormElement ( new Dummy () );
		
		$buttons = new ObjectArray ( "buttons" );
		$buttons->AddObject ( new SubmitButton ( "B_bring_account", "Ir" ) );
		$buttons->AddObject ( new SubmitButton ( "B_cancel", "Cancelar" ) );
		$buttons->SetCellAttributes ( array (
				"align" => "left" 
		) );
		
		$myForm->AddFormElement ( $buttons );
		
		$myForm->AddFormElementToNewLine ( new Dummy () );
		
		if ($FormElements ["accountid"]) {
			$accountName = $this->GetAccountName ( $FormElements ["accountid"] );
			
			$mylabel = new Label ( $name = "lb1", $value = "Acciones para $accountName" );
			$mylabel->SetClass ( "ColumnTD" );
			$mylabel->SetCellAttributes ( array (
					"colspan" => 2 
			) );
			
			$myForm->AddFormElementToNewLine ( $mylabel );
			
			$myForm->AddFormElement ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Acciones Disponibles" ); // Pablo 280809
			$mylabel->SetClass ( "ColumnTD" ); // Pablo 280809
			                                 
			// $mylabel-> SetValue("Acciones Disponibles"); //Pablo 280809
			                                 // $mylabel-> SetCellAttributes( "" );//Pablo 280809
			$myForm->AddFormElement ( $mylabel );
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Acciones del Usuario :<br><br><hr><font style='font-size:8pt; font-style:italic;'>Puede seleccionar varias</font>" ) );
			
			$accountActions = new GetOptions ( "accountactions", "actions", "actionname", "actionid", "ORDER BY actionname", $FormElements ["accountactions"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = TRUE, $extra = "", $concat = "" );
			
			// This was the original code, but didn't work for MySQL.
			//
			// SQL = "SELECT * FROM actions WHERE actionid in"
			// " (SELECT actionid FROM groupactions "
			// " WHERE groupid=".$FormElements["groupid"].")";
			
			$SQL = "SELECT a.* FROM actions a " . " LEFT JOIN accountaction ga ON a.actionid=ga.actionid " . " WHERE ga.accountid=" . $FormElements ["accountid"]. " order by actionname";
			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDB->Execute ( $SQL );
			
			if ($result2 === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
				
				return false;
			}
			
			$_SESSION ['account_actions'] = "";
			$account_Actions = array ();
			
			while ( ! $result2->EOF ) {
				$account_Actions [$result2->fields ( 'actionid' )] = $result2->fields ( 'actionid' );
				$result2->MoveNext ();
			}
			
			$_SESSION ['account_actions'] = $account_Actions;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$accountActions->SetSQL ( $SQL );
			
			$accountActions->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $accountActions );
			
			$buttons = new ObjectArray ( "buttons" );
			$buttons->AddObject ( new SubmitButton ( "B_remove_actions", ">>", $class = "b1" ) );
			$buttons->AddObject ( new Label ( "lbl", "<br>" ) );
			$buttons->AddObject ( new SubmitButton ( "B_add_actions", "<<", $class = "b1" ) );
			$buttons->SetCellAttributes ( array (
					"align" => "middle",
					"valign" => "middle" 
			) );
			
			$myForm->AddFormElement ( $buttons );
			
			$allActionsExceptAccount = new GetOptions ( "allactions", "actions", "actionname", "actionid", "ORDER BY actionname", $FormElements ["allactions"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = TRUE, $extra = "", $concat = "" );
			
			// This was the original code, but didn't work for MySQL.
			//
			// SQL = "SELECT * FROM actions WHERE actionid not in "
			// "(SELECT actionid FROM groupactions WHERE #groupid=".$FormElements["groupid"].")";
			
			$actions = $this->GetAllActionsExceptAccountHas ( $FormElements ["accountid"] );
			
			if ($actions)
				$SQL = "SELECT * FROM actions WHERE actionid NOT IN " . "($actions)";
			else
				$SQL = "SELECT * FROM actions WHERE 1=1";
			
			if ($_SESSION [myHierarchy] != 1 or $_SESSION [cliente_id] != 1) {
				$SQL .= " and actionclase = 0 ";
			}
			$SQL .= " order by actionname";
			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDB->Execute ( $SQL );
			
			if ($result2 === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
				
				return false;
			}
			
			$_SESSION ['available_actions'] = "";
			$availableActions = array ();
			
			while ( ! $result2->EOF ) {
				$availableActions [$result2->fields ( 'actionid' )] = $result2->fields ( 'actionid' );
				$result2->MoveNext ();
			}
			
			$_SESSION ['available_actions'] = $availableActions;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$allActionsExceptAccount->SetSQL ( $SQL );
			
			$allActionsExceptAccount->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $allActionsExceptAccount );
			
			// desde aca las empresas por usuario
			$myForm->AddFormElementToNewLine ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Empresas del Usuario $accountName" ); // Pablo 310809
			$mylabel->SetClass ( "ColumnTD" );
			$mylabel->SetCellAttributes ( array (
					"colspan" => 2 
			) );
			
			$myForm->AddFormElementToNewLine ( $mylabel );
			$myForm->AddFormElement ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Empresas Disponibles" );
			$mylabel->SetClass ( "ColumnTD" );
			
			$myForm->AddFormElement ( $mylabel );
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Empresas del Usuario :<br><br><hr><font style='font-size:8pt; font-style:italic;'>Puede seleccionar varios, ninguna seleccionada implica todas permitidas</font>" ) );
			
			$accountEmpresa = new GetOptions ( $name = "accountempresa", $table = $_SESSION [db_cli] . ".empresa", $field = "empresa_nombre", $key = "empresa_id", $xtrasql = "", $selected = $FormElements ["accountempresa"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = TRUE, $extra = "", $concat = "", "db_cli" );
			
			// This was the original code, but didn't work for MySQL.
			//
			// groupAccounts-> SetSQL("SELECT accountid, '('||lastname||', '||firstname||') '||username AS name FROM accounts WHERE accountid in (SELECT accountid FROM groupaccounts WHERE groupid=".$FormElements["groupid"].") AND accountid > 1");
			/*
			 * $SQL = "SELECT e.* FROM ".$_SESSION[db_cli].".empresa e " ." LEFT JOIN accountempresa ge ON e.empresa_id=ge.empresaid " ." WHERE accountid=".$FormElements["accountid"]." "; // ." and empresa_id <=9";
			 */
			$empresas = $this->GetAllEmpresasExceptAccountHas ( $FormElements ["accountid"] );
			
			$SQL = "SELECT empresa_nombre,empresa_id FROM " . $_SESSION [db_cli] . ".empresa " . "WHERE empresa_id IN ($empresas)";
			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDBcli->Execute ( $SQL );
			
			if ($result2 === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
				
				return false;
			}
			
			$_SESSION ['account_empresas'] = "";
			$account_Empresas = array ();
			
			while ( ! $result2->EOF ) {
				$account_Empresas [$result2->fields ( 'empresa_id' )] = $result2->fields ( 'empresa_id' );
				$result2->MoveNext ();
			}
			
			$_SESSION ['account_empresas'] = $account_Empresas;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$accountEmpresa->SetSQL ( $SQL );
			$accountEmpresa->SetConnection ( "db_cli" );
			
			$accountEmpresa->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $accountEmpresa );
			
			$buttons = new ObjectArray ( "buttons" );
			$buttons->AddObject ( new SubmitButton ( "B_remove_empresa", ">>", $class = "b1" ) );
			$buttons->AddObject ( new Label ( "lbl", "<br>" ) );
			$buttons->AddObject ( new SubmitButton ( "B_add_empresa", "<<", $class = "b1" ) );
			$buttons->SetCellAttributes ( array (
					"align" => "middle",
					"valign" => "middle" 
			) );
			
			$myForm->AddFormElement ( $buttons );
			
			$allEmpresasExceptAccount = new GetOptions ( $name = "allempresas", $table = $_SESSION [db_cli] . ".empresa", $field = "empresa_nombre", $key = "empresaid", $extraSql = "", $selected = $FormElements ["allempresas"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = true, $extra = "id=\"allempresas\"", "", "db_cli" );
			
			// This was the original code, but didn't work for MySQL.
			//
			// allAccountsExceptGroup-> SetSQL("SELECT a.accountid, '('||a.lastname||', '||a.firstname||') '||username AS name FROM accounts a WHERE accountid NOT IN (SELECT accountid FROM groupaccounts WHERE groupid=" .$FormElements["groupid"].") AND accountid > 1");
			
			$empresas = $this->GetAllEmpresasExceptAccountHas ( $FormElements ["accountid"] );
			
			$SQL = "SELECT empresa_nombre,empresa_id empresaid FROM " . $_SESSION [db_cli] . ".empresa " . "WHERE empresa_id NOT IN ($empresas)";
			// ." and empresa_id <=6";
			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDBcli->Execute ( $SQL );
			
			if ($result2 === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
				
				return false;
			}
			
			$_SESSION ['available_empresas'] = "";
			$availableEmpresas = array ();
			
			while ( ! $result2->EOF ) {
				$availableEmpresas [$result2->fields ( 'empresaid' )] = $result2->fields ( 'empresaid' );
				$result2->MoveNext ();
			}
			
			$_SESSION ['available_empresas'] = $availableEmpresas;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$allEmpresasExceptAccount->SetSQL ( $SQL );
			
			$allEmpresasExceptAccount->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $allEmpresasExceptAccount );
			
			// hasta aca las empresas por usuario
			
			// desde aca las jurisdicciones por usuario
			$myForm->AddFormElementToNewLine ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Jurisdicciones del Usuario $accountName" ); // Pablo 310809
			$mylabel->SetClass ( "ColumnTD" );
			$mylabel->SetCellAttributes ( array (
					"colspan" => 2 
			) );
			
			$myForm->AddFormElementToNewLine ( $mylabel );
			$myForm->AddFormElement ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Jurisdicciones Disponibles" );
			$mylabel->SetClass ( "ColumnTD" );
			
			$myForm->AddFormElement ( $mylabel );
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Jurisdicciones del Usuario :<br><br><hr><font style='font-size:8pt; font-style:italic;'>Puede seleccionar varios, ninguna seleccionada implica todas permitidas</font>" ) );
			
			$accountJurisdiccion = new GetOptions ( $name = "accountjurisdiccion", $table = $_SESSION [db_cli] . ".jurisdiccion", $field = "jur_nombre", $key = "jur_id", $xtrasql = "", $selected = $FormElements ["accountjurisdiccion"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = TRUE, $extra = "", $concat = "", "db_cli" );
			
			$jurisdicciones = $this->GetAllJurisdiccionesExceptAccountHas ( $FormElements ["accountid"] );
			
//			$SQL = "SELECT jur_nombre,jur_id FROM " . $_SESSION [db_cli] . ".jurisdiccion " . "WHERE jur_id IN ($jurisdicciones)";

			//muestra las jurisdicciones del usuario + juridicciones del usuario que sean inexistentes
			$SQL = "select jur_nombre, jur_id, 0 FROM " . $_SESSION [db_cli] . ".jurisdiccion 
					   where jur_id in ($jurisdicciones)
                union 
                select concat('jurisdiccion',jurisdiccionid) jur_nombre , jurisdiccionid jur_id
                ,(select count(*) from " . $_SESSION [db_cli] . ".jurisdiccion where jur_id=jurisdiccionid) c
                 from megacontrol.accountjurisdiccion
                where accountid=". $FormElements ["accountid"]."
                having c = 0
                order by 1";


// select coalesce(jur_nombre,concat('jurisdiccion',ide)) jur_nombre , ide jur_id FROM
// 			(select 1 ide union select 2 ide union select 3 ide union select 4 ide union select 5 ide union
// 					select 6 ide union select 7 ide union select 8 ide union select 9 ide union select 10 ide ) juri
// 					left join " . $_SESSION [db_cli] . ".jurisdiccion on jur_id =ide
// 					where ide in ($jurisdicciones)";
						
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDBcli->Execute ( $SQL );
			
			if ($result2 === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
				
				return false;
			}
			
			$_SESSION ['account_jurisdicciones'] = "";
			$account_Jurisdicciones = array ();
			
			while ( ! $result2->EOF ) {
				$account_Jurisdicciones [$result2->fields ( 'jur_id' )] = $result2->fields ( 'jur_id' );
				$result2->MoveNext ();
			}
			
			$_SESSION ['account_jurisdicciones'] = $account_Jurisdicciones;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$accountJurisdiccion->SetSQL ( $SQL );
			$accountJurisdiccion->SetConnection ( "db_cli" );
			
			$accountJurisdiccion->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $accountJurisdiccion );
			
			$buttons = new ObjectArray ( "buttons" );
			$buttons->AddObject ( new SubmitButton ( "B_remove_jurisdiccion", ">>", $class = "b1" ) );
			$buttons->AddObject ( new Label ( "lbl", "<br>" ) );
			$buttons->AddObject ( new SubmitButton ( "B_add_jurisdiccion", "<<", $class = "b1" ) );
			$buttons->SetCellAttributes ( array (
					"align" => "middle",
					"valign" => "middle" 
			) );
			
			$myForm->AddFormElement ( $buttons );
			
			$allJurisdiccionesExceptAccount = new GetOptions ( $name = "alljurisdicciones", $table = $_SESSION [db_cli] . ".jurisdiccion", $field = "jur_nombre", $key = "jurisdiccionid", $extraSql = "", $selected = $FormElements ["alljurisdicciones"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = true, $extra = "id=\"alljurisdicciones\"", "", "db_cli" );
			
			$jurisdicciones = $this->GetAllJurisdiccionesExceptAccountHas ( $FormElements ["accountid"] );
			
			$SQL = "SELECT jur_nombre,jur_id jurisdiccionid FROM " . $_SESSION [db_cli] . ".jurisdiccion " . "WHERE jur_id NOT IN ($jurisdicciones)";
			// ." and jur_id <=6";
			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDBcli->Execute ( $SQL );
			
			if ($result2 === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
				
				return false;
			}
			
			$_SESSION ['available_jurisdicciones'] = "";
			$availableJurisdicciones = array ();
			
			while ( ! $result2->EOF ) {
				$availableJurisdicciones [$result2->fields ( 'jurisdiccionid' )] = $result2->fields ( 'jurisdiccionid' );
				$result2->MoveNext ();
			}
			
			$_SESSION ['available_jurisdicciones'] = $availableJurisdicciones;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$allJurisdiccionesExceptAccount->SetSQL ( $SQL );
			
			$allJurisdiccionesExceptAccount->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $allJurisdiccionesExceptAccount );
			
			// hasta aca las jurisdicciones por usuario

			// desde aca los coches por usuario
			$myForm->AddFormElementToNewLine ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Vehiculos del Usuario $accountName" ); // Pablo 111018
			$mylabel->SetClass ( "ColumnTD" );
			$mylabel->SetCellAttributes ( array (
			    "colspan" => 2
			) );
			
			$myForm->AddFormElementToNewLine ( $mylabel );
			$myForm->AddFormElement ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Vehiculos Disponibles" );
			$mylabel->SetClass ( "ColumnTD" );
			
			$myForm->AddFormElement ( $mylabel );
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Vehiculos del Usuario :<br><br><hr><font style='font-size:8pt; font-style:italic;'>Puede seleccionar varios, ninguno seleccionado implica todos permitidos</font>" ) );
			
			$accountVehiculo = new GetOptions ( $name = "accountvehiculo", $table = $_SESSION [db_cli] . ".vehiculo", $field = "vehiculo_nombre", $key = "vehiculo_id", $xtrasql = "", $selected = $FormElements ["accountvehiculo"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = TRUE, $extra = "", $concat = "", "db_cli" );
			
			$vehiculos = $this->GetAllVehiculosExceptAccountHas ( $FormElements ["accountid"] );
			
			//			$SQL = "SELECT jur_nombre,jur_id FROM " . $_SESSION [db_cli] . ".jurisdiccion " . "WHERE jur_id IN ($jurisdicciones)";
			
			$SQL = "select concat(vehiculo_id,' - ',vehiculo_nombre) vehiculo_nombre , vehiculo_id FROM
                    " . $_SESSION [db_cli] . ".vehiculo where vehiculo_id in ($vehiculos)";
			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDBcli->Execute ( $SQL );
			
			if ($result2 === false) {
			    $this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
			    
			    if ($_SESSION ['IS_ERROR_REPORTING'])
			        $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
			        
			        return false;
			}
			
			$_SESSION ['account_vehiculos'] = "";
			$account_Vehiculos = array ();
			
			while ( ! $result2->EOF ) {
			    $account_Vehiculos [$result2->fields ( 'vehiculo_id' )] = $result2->fields ( 'vehiculo_id' );
			    $result2->MoveNext ();
			}
			
			$_SESSION ['account_vehiculos'] = $account_Vehiculos;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$accountVehiculo->SetSQL ( $SQL );
			$accountVehiculo->SetConnection ( "db_cli" );
			
			$accountVehiculo->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $accountVehiculo );
			
			$buttons = new ObjectArray ( "buttons" );
			$buttons->AddObject ( new SubmitButton ( "B_remove_vehiculo", ">>", $class = "b1" ) );
			$buttons->AddObject ( new Label ( "lbl", "<br>" ) );
			$buttons->AddObject ( new SubmitButton ( "B_add_vehiculo", "<<", $class = "b1" ) );
			$buttons->SetCellAttributes ( array (
			    "align" => "middle",
			    "valign" => "middle"
			) );
			
			$myForm->AddFormElement ( $buttons );
			
			$allVehiculosExceptAccount = new GetOptions ( $name = "allvehiculos", $table = $_SESSION [db_cli] . ".vehiculo", $field = "vehiculo_nombre", $key = "vehiculo_id", $extraSql = "", $selected = $FormElements ["allvehiculos"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = true, $extra = "id=\"allvehiculos\"", "", "db_cli" );
			
			$vehiculos = $this->GetAllVehiculosExceptAccountHas ( $FormElements ["accountid"] );
			
			$SQL = "SELECT concat(vehiculo_id,' - ',vehiculo_nombre) vehiculo_nombre , vehiculo_id  FROM " . $_SESSION [db_cli] . ".vehiculo " . "WHERE vehiculo_id NOT IN ($vehiculos)";
			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDBcli->Execute ( $SQL );
			
			if ($result2 === false) {
			    $this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
			    
			    if ($_SESSION ['IS_ERROR_REPORTING'])
			        $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
			        
			        return false;
			}
			
			$_SESSION ['available_vehiculos'] = "";
			$availableVehiculos = array ();
			
			while ( ! $result2->EOF ) {
			    $availableVehiculos [$result2->fields ( 'vehiculo_id' )] = $result2->fields ( 'vehiculo_id' );
			    $result2->MoveNext ();
			}
			
			$_SESSION ['available_vehiculos'] = $availableVehiculos;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$allVehiculosExceptAccount->SetSQL ( $SQL );
			
			$allVehiculosExceptAccount->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $allVehiculosExceptAccount );
			
			// hasta aca los coches por usuario
			
			// desde aca los servicios por usuario
			$myForm->AddFormElementToNewLine ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Servicios del Usuario $accountName" ); // Pablo 220519
			$mylabel->SetClass ( "ColumnTD" );
			$mylabel->SetCellAttributes ( array (
			    "colspan" => 2
			) );
			
			$myForm->AddFormElementToNewLine ( $mylabel );
			$myForm->AddFormElement ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Servicios Disponibles" );
			$mylabel->SetClass ( "ColumnTD" );
			
			$myForm->AddFormElement ( $mylabel );
			
 			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Servicios del Usuario :<br><br><hr><font style='font-size:8pt; font-style:italic;'>Puede seleccionar varios, ninguno seleccionado implica todos permitidos</font>" ) );
			
 			$accountServicio = new GetOptions ( $name = "accountservicio", $table = $_SESSION [db_cli] . ".horario_cabecera", $field = "horcnombre", $key = "horc_id", $xtrasql = "", $selected = $FormElements ["accountservicio"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = TRUE, $extra = "", $concat = "", "db_cli" );
			
 			$servicios = $this->GetAllServiciosExceptAccountHas ( $FormElements ["accountid"] );
			
 			//			$SQL = "SELECT jur_nombre,jur_id FROM " . $_SESSION [db_cli] . ".jurisdiccion " . "WHERE jur_id IN ($jurisdicciones)";
			
 			$SQL = "select horc_id hornombre,horc_id FROM
                     " . $_SESSION [db_cli] . ".horario_cabecera where horc_id in ($servicios)";
 			
 			$SQL = "SELECT concat(horc_id,' - ',coalesce(horlinea_nombre,'Sin Linea')) horcnombre,horc_id  FROM " . $_SESSION [db_cli] . ".horario_cabecera left join ".$_SESSION [db_cli] . ".horario_linea on horlinea_id=horc_linea" . " WHERE horc_id IN ($servicios)";
 			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDBcli->Execute ( $SQL );
			
			if ($result2 === false) {
			    $this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
			    
			    if ($_SESSION ['IS_ERROR_REPORTING'])
			        $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
			        
			        return false;
			}
			
			$_SESSION ['account_servicios'] = "";
			$account_Servicios = array ();
			
			while ( ! $result2->EOF ) {
			    $account_Servicios [$result2->fields ( 'horc_id' )] = $result2->fields ( 'horc_id' );
			    $result2->MoveNext ();
			}
			
			$_SESSION ['account_servicios'] = $account_Servicios;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$accountServicio->SetSQL ( $SQL );
			$accountServicio->SetConnection ( "db_cli" );
			
			$accountServicio->SetZebraColor ( "#EBEBEB" );
			
 			$myForm->AddFormElement ( $accountServicio );
			
			$buttons = new ObjectArray ( "buttons" );
			$buttons->AddObject ( new SubmitButton ( "B_remove_servicio", ">>", $class = "b1" ) );
			$buttons->AddObject ( new Label ( "lbl", "<br>" ) );
			$buttons->AddObject ( new SubmitButton ( "B_add_servicio", "<<", $class = "b1" ) );
			$buttons->SetCellAttributes ( array (
			    "align" => "middle",
			    "valign" => "middle"
			) );
			
			$myForm->AddFormElement ( $buttons );
			
			$allServiciosExceptAccount = new GetOptions ( $name = "allservicios", $table = $_SESSION [db_cli] . ".horario_cabecera", $field = "horcnombre", $key = "horc_id", $extraSql = "", $selected = $FormElements ["allservicios"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = true, $extra = "id=\"allservicios\"", "", "db_cli" );
			
 			$servicios = $this->GetAllServiciosExceptAccountHas ( $FormElements ["accountid"] );
			
 			$SQL = "SELECT concat(horc_id,' - ',coalesce(horlinea_nombre,'Sin Linea')) horcnombre,horc_id  FROM " . $_SESSION [db_cli] . ".horario_cabecera left join ".$_SESSION [db_cli] . ".horario_linea on horlinea_id=horc_linea" . " WHERE horc_id NOT IN ($servicios)";
			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDBcli->Execute ( $SQL );
			
			if ($result2 === false) {
			    $this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
			    
			    if ($_SESSION ['IS_ERROR_REPORTING'])
			        $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
			        
			        return false;
			}
			
			$_SESSION ['available_servicios'] = "";
			$availableServicios = array ();
			
			while ( ! $result2->EOF ) {
			    $availableServicios [$result2->fields ( 'horc_id' )] = $result2->fields ( 'horc_id' );
			    $result2->MoveNext ();
			}
			
			$_SESSION ['available_servicios'] = $availableServicios;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$allServiciosExceptAccount->SetSQL ( $SQL );
			
			$allServiciosExceptAccount->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $allServiciosExceptAccount );
			
			// hasta aca los servicios por usuario
			
	if ($_SESSION['myHierarchy']==1) { //solo siscadat		
			// desde aca las clientes por acountcliente Pablo 280714
			
			$myForm->AddFormElementToNewLine ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Clientes del Usuario $accountName" );
			$mylabel->SetClass ( "ColumnTD" );
			$mylabel->SetCellAttributes ( array (
					"colspan" => 2 
			) );
			
			$myForm->AddFormElementToNewLine ( $mylabel );
			$myForm->AddFormElement ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Clientes Disponibles" );
			$mylabel->SetClass ( "ColumnTD" );
			
			$myForm->AddFormElement ( $mylabel );
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Clientes del Usuario :<br><br><hr><font style='font-size:8pt; font-style:italic;'>Clientes a los que puede acceder el usuario</font>" ) );
			
			$accountClienteCliente = new GetOptions ( $name = "accountclientecliente", $table = "cliente", $field = "cliente_nombre", $key = "cliente_id", $xtrasql = "", $selected = $FormElements ["accountclientecliente"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = TRUE, $extra = "", $concat = "", "db" );
			
			$clientesclientes = $this->GetAllClientesClientesExceptAccountHas ( $FormElements ["accountid"] );
			
			$SQL = "SELECT cliente_nombre,cliente_id FROM cliente " . "WHERE cliente_id IN ($clientesclientes) order by 1";
			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDB->Execute ( $SQL );
			
			if ($result2 === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
				
				return false;
			}
			
			$_SESSION ['account_clientesclientes'] = "";
			$account_ClientesClientes = array ();
			
			while ( ! $result2->EOF ) {
				$account_ClientesClientes [$result2->fields ( 'cliente_id' )] = $result2->fields ( 'cliente_id' );
				$result2->MoveNext ();
			}
			
			$_SESSION ['account_clientesclientes'] = $account_ClientesClientes;
			
			$accountClienteCliente->SetSQL ( $SQL );
			$accountClienteCliente->SetConnection ( "db" );
			
			$accountClienteCliente->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $accountClienteCliente );
			
			$buttons = new ObjectArray ( "buttons" );
			$buttons->AddObject ( new SubmitButton ( "B_remove_cliente", ">>", $class = "b1" ) );
			$buttons->AddObject ( new Label ( "lbl", "<br>" ) );
			$buttons->AddObject ( new SubmitButton ( "B_add_cliente", "<<", $class = "b1" ) );
			$buttons->SetCellAttributes ( array (
					"align" => "middle",
					"valign" => "middle" 
			) );
			
			$myForm->AddFormElement ( $buttons );
			
			$allClientesClientesExceptAccount = new GetOptions ( $name = "allclientesclientes", $table = "cliente", $field = "cliente_nombre", $key = "clienteid", $extraSql = "", $selected = $FormElements ["allclientesclientes"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = true, $extra = "id=\"allclientesclientes\"", "", "db" );
			
			$clientesclientes = $this->GetAllClientesClientesExceptAccountHas ( $FormElements ["accountid"] );
			
			$SQL = "SELECT cliente_nombre,cliente_id clienteid FROM cliente " . "WHERE cliente_id NOT IN ($clientesclientes) order by 1";
			
			$result2 = $this->gDB->Execute ( $SQL );
			
			if ($result2 === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
				
				return false;
			}
			
			$_SESSION ['available_clientesclientes'] = "";
			$availableClientesClientes = array ();
			
			while ( ! $result2->EOF ) {
				$availableClientesClientes [$result2->fields ( 'clienteid' )] = $result2->fields ( 'clienteid' );
				$result2->MoveNext ();
			}
			
			$_SESSION ['available_clientesclientes'] = $availableClientesClientes;
			
			$allClientesClientesExceptAccount->SetSQL ( $SQL );
			
			$allClientesClientesExceptAccount->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $allClientesClientesExceptAccount );
			
			// hasta aca las clientes de accountcliente
			
			// desde aca las equivale por acountcliente Pablo 280714
			
			$myForm->AddFormElementToNewLine ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Usuarios equivalentes del Usuario $accountName" );
			$mylabel->SetClass ( "ColumnTD" );
			$mylabel->SetCellAttributes ( array (
					"colspan" => 2 
			) );
			
			$myForm->AddFormElementToNewLine ( $mylabel );
			$myForm->AddFormElement ( new Dummy () );
			
			$mylabel = new Label ( $name = "lb1", $value = "Usuarios Disponibles" );
			$mylabel->SetClass ( "ColumnTD" );
			
			$myForm->AddFormElement ( $mylabel );
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Usuarios Equivalentes del Usuario :<br><br><hr><font style='font-size:8pt; font-style:italic;'>Usuarios de los que hereda permisos, uno por cliente</font>" ) );
			
			$accountClienteEquivale = new GetOptions ( $name = "accountclienteequivale", $table = "accounts", $field = "nombre", $key = "accountid", $xtrasql = "", $selected = $FormElements ["accountclienteequivale"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = TRUE, $extra = "", $concat = "", "db" );
			
			$clientesequivales = $this->GetAllClientesEquivalesExceptAccountHas ( $FormElements ["accountid"] );
			
			$SQL = "SELECT concat(lastname,',',firstname,' (',cliente_nombre,')') nombre,accountid FROM accounts
			,cliente where accounts.cliente_id=cliente.cliente_id
			and accountid IN ($clientesequivales) order by 1";
				
			
			//
			// Store this information in session, so we wont get bogus
			//
			$result2 = $this->gDB->Execute ( $SQL );
			
			if ($result2 === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
				
				return false;
			}
			
			$_SESSION ['account_clientesequivales'] = "";
			$account_ClientesEquivales = array ();
			
			while ( ! $result2->EOF ) {
				$account_ClientesEquivales [$result2->fields ( 'accountid' )] = $result2->fields ( 'accountid' );
				$result2->MoveNext ();
			}
			
			$_SESSION ['account_clientesequivales'] = $account_ClientesEquivales;
			
			$accountClienteEquivale->SetSQL ( $SQL );
			$accountClienteEquivale->SetConnection ( "db" );
			
			$accountClienteEquivale->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $accountClienteEquivale );
			
			$buttons = new ObjectArray ( "buttons" );
			$buttons->AddObject ( new SubmitButton ( "B_remove_usuario", ">>", $class = "b1" ) );
			$buttons->AddObject ( new Label ( "lbl", "<br>" ) );
			$buttons->AddObject ( new SubmitButton ( "B_add_usuario", "<<", $class = "b1" ) );
			$buttons->SetCellAttributes ( array (
					"align" => "middle",
					"valign" => "middle" 
			) );
			
			$myForm->AddFormElement ( $buttons );
			
			$allClientesEquivalesExceptAccount = new GetOptions ( $name = "allclientesequivales", $table = "accounts", $field = "nombre", $key = "accountid", $extraSql = "", $selected = $FormElements ["allclientesequivales"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = true, $extra = "id=\"allclientesequivales\"", "", "db" );
			
			$clientesequivales = $this->GetAllClientesEquivalesExceptAccountHas ( $FormElements ["accountid"] );
			
			$SQL = "SELECT concat(lastname,',',firstname,' (',cliente_nombre,')') nombre,accountid FROM accounts
			,cliente where accounts.cliente_id=cliente.cliente_id
			and accountid NOT IN ($clientesequivales) order by 1";
			
			$result2 = $this->gDB->Execute ( $SQL );
			
			if ($result2 === false) {
				$this->SetErrorMessage ( 'error reading: ' . $this->gDB->ErrorMsg () );
				
				if ($_SESSION ['IS_ERROR_REPORTING'])
					$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $SQL );
				
				return false;
			}
			
			$_SESSION ['available_clientesequivales'] = "";
			$availableClientesEquivales = array ();
			
			while ( ! $result2->EOF ) {
				$availableClientesEquivales [$result2->fields ( 'accountid' )] = $result2->fields ( 'accountid' );
				$result2->MoveNext ();
			}
			
			$_SESSION ['available_clientesequivales'] = $availableClientesEquivales;
			
			$allClientesEquivalesExceptAccount->SetSQL ( $SQL );
			
			$allClientesEquivalesExceptAccount->SetZebraColor ( "#EBEBEB" );
			
			$myForm->AddFormElement ( $allClientesEquivalesExceptAccount );
			
			// hasta aca las equivale de accountcliente
			} //if es de siscadat
		}
		
		$passtru = new PassTru ( "" );
		$passtru->SetColSpan ( $myForm->GetNumberOfColumns () );
		$passtru->SetClass ( "" );
		$passtru->SetCellAttributes ( array (
				'align' => 'left' 
		) );
		
		$passtru->SetValue ( "<a class=\"" . $_SESSION ["CSS"] . "LinkButton\" href=\"adminmenu.php\"><-- Volver al Men� Seguridad</a>" );
		
		$myForm->AddFormElementToNewLine ( $passtru );
		
		echo $myForm->GetFormInTable ();
	}
	
	/**
	 * Method to get the actions that the account doesn't have.
	 * @public
	 * @ Pablo 280809
	 * 
	 * @return string
	 */
	Function GetAllActionsExceptAccountHas($accountid) {
		$sql = "SELECT actionid from accountaction WHERE accountid=" . $accountid;
		
		$result = $this->gDB->Execute ( $sql );
		
		if ($result === false) {
			$this->mErrorMessage = 'error reading: ' . $this->gDB->ErrorMsg ();
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
		}
		
		$string = null;
		
		while ( ! $result->EOF ) {
			if ($string)
				$string .= ",";
			$string .= $result->fields ( "actionid" );
			
			$result->MoveNext ();
		}
		
		return $string;
	}
	
	/**
	 * Method to add actions to accountpactions table.
	 * @public
	 * @ Pablo 280809
	 * 
	 * @return bool
	 */
	function AddAccountActions($FormElements) {
		if (count ( $FormElements ["allactions"] )) {
			// Check for the bogus actions
			//
			foreach ( $FormElements ["allactions"] as $key => $actionid ) {
				if (! array_key_exists ( $actionid, $_SESSION ['available_actions'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["allactions"] as $key => $actionid ) {
				$sql = "INSERT INTO accountaction (accountid,actionid) " . "      VALUES(" . $FormElements ["accountid"] . "," . $actionid . ")";
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to remove actions from accountaction table.
	 * @public
	 * @Pablo 280809
	 * 
	 * @return bool
	 */
	function RemoveAccountActions($FormElements) {
		if (count ( $FormElements ["accountactions"] )) {
			// Check for the bogus actions
			//
			foreach ( $FormElements ["accountactions"] as $key => $actionid ) {
				if (! array_key_exists ( $actionid, $_SESSION ['account_actions'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["accountactions"] as $key => $actionid ) {
				$sql = "DELETE FROM accountaction " . "      WHERE accountid =" . $FormElements ["accountid"] . " AND " . "            actionid=" . $actionid;
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to get the empresas that the account doesn't have.
	 * @public
	 * @ Pablo 310809
	 * 
	 * @return string
	 */
	Function GetAllEmpresasExceptAccountHas($accountid) {
		$sql = "SELECT empresaid from accountempresa WHERE accountid=" . $accountid;
		
		$result = $this->gDB->Execute ( $sql );
		
		if ($result === false) {
			$this->mErrorMessage = 'error reading: ' . $this->gDB->ErrorMsg ();
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
		}
		
		$string = "0";
		
		while ( ! $result->EOF ) {
			if ($string)
				$string .= ",";
			$string .= $result->fields ( "empresaid" );
			
			$result->MoveNext ();
		}
		
		return $string;
	}
	
	/**
	 * Method to add actions to accountempresa table.
	 * @public
	 * @ Pablo 310809
	 * 
	 * @return bool
	 */
	function AddAccountEmpresas($FormElements) {
		if (count ( $FormElements ["allempresas"] )) {
			// Check for the bogus actions
			//
			foreach ( $FormElements ["allempresas"] as $key => $empresaid ) {
				if (! array_key_exists ( $empresaid, $_SESSION ['available_empresas'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["allempresas"] as $key => $empresaid ) {
				$sql = "INSERT INTO accountempresa (accountid,empresaid) " . "      VALUES(" . $FormElements ["accountid"] . "," . $empresaid . ")";
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to remove empresas from accountempresa table.
	 * @public
	 * @Pablo 310809
	 * 
	 * @return bool
	 */
	function RemoveAccountEmpresas($FormElements) {
		if (count ( $FormElements ["accountempresa"] )) {
			// Check for the bogus actions
			//
			foreach ( $FormElements ["accountempresa"] as $key => $empresaid ) {
				if (! array_key_exists ( $empresaid, $_SESSION ['account_empresas'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["accountempresa"] as $key => $empresaid ) {
				$sql = "DELETE FROM accountempresa " . "      WHERE accountid =" . $FormElements ["accountid"] . " AND " . "            empresaid=" . $empresaid;
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to get the jurisdicciones that the account doesn't have.
	 * @public
	 * @ Pablo 230714
	 * 
	 * @return string
	 */
	Function GetAllJurisdiccionesExceptAccountHas($accountid) {
		$sql = "SELECT jurisdiccionid from accountjurisdiccion WHERE accountid=" . $accountid;
		
		$result = $this->gDB->Execute ( $sql );
		
		if ($result === false) {
			$this->mErrorMessage = 'error reading: ' . $this->gDB->ErrorMsg ();
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
		}
		
		$string = "0";
		
		while ( ! $result->EOF ) {
			if ($string)
				$string .= ",";
			$string .= $result->fields ( "jurisdiccionid" );
			
			$result->MoveNext ();
		}
		
		return $string;
	}
	
	/**
	 * Method to add actions to accountjurisdiccion table.
	 * @public
	 * @ Pablo 230714
	 * 
	 * @return bool
	 */
	function AddAccountJurisdicciones($FormElements) {
		if (count ( $FormElements ["alljurisdicciones"] )) {
			// Check for the bogus actions
			//
			foreach ( $FormElements ["alljurisdicciones"] as $key => $jurisdiccionid ) {
				if (! array_key_exists ( $jurisdiccionid, $_SESSION ['available_jurisdicciones'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["alljurisdicciones"] as $key => $jurisdiccionid ) {
				$sql = "INSERT INTO accountjurisdiccion (accountid,jurisdiccionid) " . "      VALUES(" . $FormElements ["accountid"] . "," . $jurisdiccionid . ")";
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to remove jurisdicciones from accountjurisdiccion table.
	 * @public
	 * @Pablo 230714
	 * 
	 * @return bool
	 */
	function RemoveAccountJurisdicciones($FormElements) {
		if (count ( $FormElements ["accountjurisdiccion"] )) {
			// Check for the bogus actions
			//
			foreach ( $FormElements ["accountjurisdiccion"] as $key => $jurisdiccionid ) {
				if (! array_key_exists ( $jurisdiccionid, $_SESSION ['account_jurisdicciones'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["accountjurisdiccion"] as $key => $jurisdiccionid ) {
				$sql = "DELETE FROM accountjurisdiccion " . "      WHERE accountid =" . $FormElements ["accountid"] . " AND " . "            jurisdiccionid=" . $jurisdiccionid;
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to get the vehiculos that the account doesn't have.
	 * @public
	 * @ Pablo 111018
	 *
	 * @return string
	 */
	Function GetAllVehiculosExceptAccountHas($accountid) {
	    $sql = "SELECT vehiculo_id from accountvehiculo WHERE accountid=" . $accountid;
	    
	    $result = $this->gDB->Execute ( $sql );
	    
	    if ($result === false) {
	        $this->mErrorMessage = 'error reading: ' . $this->gDB->ErrorMsg ();
	        
	        if ($_SESSION ['IS_ERROR_REPORTING'])
	            $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
	    }
	    
	    $string = "0";
	    
	    while ( ! $result->EOF ) {
	        if ($string)
	            $string .= ",";
	            $string .= $result->fields ( "vehiculo_id" );
	            
	            $result->MoveNext ();
	    }
	    
	    return $string;
	}
	
	/**
	 * Method to add actions to accountvehiculo table.
	 * @public
	 * @ Pablo 111018
	 *
	 * @return bool
	 */
	function AddAccountVehiculos($FormElements) {
	    if (count ( $FormElements ["allvehiculos"] )) {
	        // Check for the bogus actions
	        //
	        foreach ( $FormElements ["allvehiculos"] as $key => $vehiculo_id ) {
	            if (! array_key_exists ( $vehiculo_id, $_SESSION ['available_vehiculos'] )) {
	                $this->mySecurity->GotoThisPage ( "bogus.php" );
	            }
	        }
	        
	        $this->gDB->BeginTrans ();
	        
	        foreach ( $FormElements ["allvehiculos"] as $key => $vehiculo_id ) {
	            $sql = "INSERT INTO accountvehiculo (accountid,vehiculo_id) " . "      VALUES(" . $FormElements ["accountid"] . "," . $vehiculo_id . ")";
	            
	            $result = $this->gDB->Execute ( $sql );
	            
	            if ($result === false) {
	                $this->gDB->RollbackTrans ();
	                
	                if ($_SESSION ['IS_ERROR_REPORTING'])
	                    $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
	                    
	                    return false;
	            }
	        }
	        
	        $this->gDB->CommitTrans ();
	    }
	    return true;
	}
	
	/**
	 * Method to remove from accountvehiculo table.
	 * @public
	 * @Pablo 111018
	 *
	 * @return bool
	 */
	function RemoveAccountVehiculos($FormElements) {
	    if (count ( $FormElements ["accountvehiculo"] )) {
	        // Check for the bogus actions
	        //
	        foreach ( $FormElements ["accountvehiculo"] as $key => $vehiculo_id ) {
	            if (! array_key_exists ( $vehiculo_id, $_SESSION ['account_vehiculos'] )) {
	                $this->mySecurity->GotoThisPage ( "bogus.php" );
	            }
	        }
	        
	        $this->gDB->BeginTrans ();
	        
	        foreach ( $FormElements ["accountvehiculo"] as $key => $vehiculo_id ) {
	            $sql = "DELETE FROM accountvehiculo " . "      WHERE accountid =" . $FormElements ["accountid"] . " AND " . " vehiculo_id=" . $vehiculo_id;
	            
	            $result = $this->gDB->Execute ( $sql );
	            
	            if ($result === false) {
	                $this->gDB->RollbackTrans ();
	                
	                if ($_SESSION ['IS_ERROR_REPORTING'])
	                    $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
	                    
	                    return false;
	            }
	        }
	        
	        $this->gDB->CommitTrans ();
	    }
	    return true;
	}

	
/**
 * Method to get the servicios that the account doesn't have.
 * @public
 * @ Pablo 220519
 *
 * @return string
 */
Function GetAllServiciosExceptAccountHas($accountid) {
    $sql = "SELECT servicio_id from accountservicio WHERE accountid=" . $accountid;
    
    $result = $this->gDB->Execute ( $sql );
    
    if ($result === false) {
        $this->mErrorMessage = 'error reading: ' . $this->gDB->ErrorMsg ();
        
        if ($_SESSION ['IS_ERROR_REPORTING'])
            $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
    }
    
    $string = "0";
    
    while ( ! $result->EOF ) {
        if ($string)
            $string .= ",";
            $string .= $result->fields ( "servicio_id" );
            
            $result->MoveNext ();
    }
    
    return $string;
}

/**
 * Method to add actions to accountservicio table.
 * @public
 * @ Pablo 111018
 *
 * @return bool
 */
function AddAccountServicios($FormElements) {
    if (count ( $FormElements ["allservicios"] )) {
        // Check for the bogus actions
        //
        foreach ( $FormElements ["allservicios"] as $key => $servicio_id ) {
            if (! array_key_exists ( $servicio_id, $_SESSION ['available_servicios'] )) {
                $this->mySecurity->GotoThisPage ( "bogus.php" );
            }
        }
        
        $this->gDB->BeginTrans ();
        
        foreach ( $FormElements ["allservicios"] as $key => $servicio_id ) {
            $sql = "INSERT INTO accountservicio (accountid,servicio_id) " . "      VALUES(" . $FormElements ["accountid"] . "," . $servicio_id . ")";
            
            $result = $this->gDB->Execute ( $sql );
            
            if ($result === false) {
                $this->gDB->RollbackTrans ();
                
                if ($_SESSION ['IS_ERROR_REPORTING'])
                    $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
                    
                    return false;
            }
        }
        
        $this->gDB->CommitTrans ();
    }
    return true;
}

/**
 * Method to remove from accountservicio table.
 * @public
 * @Pablo 111018
 *
 * @return bool
 */
function RemoveAccountServicios($FormElements) {
    if (count ( $FormElements ["accountservicio"] )) {
        // Check for the bogus actions
        //
        foreach ( $FormElements ["accountservicio"] as $key => $servicio_id ) {
            if (! array_key_exists ( $servicio_id, $_SESSION ['account_servicios'] )) {
                $this->mySecurity->GotoThisPage ( "bogus.php" );
            }
        }
        
        $this->gDB->BeginTrans ();
        
        foreach ( $FormElements ["accountservicio"] as $key => $servicio_id ) {
            $sql = "DELETE FROM accountservicio " . "      WHERE accountid =" . $FormElements ["accountid"] . " AND " . " servicio_id=" . $servicio_id;
            
            $result = $this->gDB->Execute ( $sql );
            
            if ($result === false) {
                $this->gDB->RollbackTrans ();
                
                if ($_SESSION ['IS_ERROR_REPORTING'])
                    $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
                    
                    return false;
            }
        }
        
        $this->gDB->CommitTrans ();
    }
    return true;
}

	/**
	 * Method to get the clientes that the account doesn't have.
	 * @public
	 * @ Pablo 280714
	 * 
	 * @return string
	 */
	Function GetAllClientesClientesExceptAccountHas($accountid) {
		$sql = "SELECT clienteid from accountcliente WHERE clienteid > 0 and accountid_equivale = 0 and accountid=" . $accountid;
		
		$result = $this->gDB->Execute ( $sql );
		
		if ($result === false) {
			$this->mErrorMessage = 'error reading: ' . $this->gDB->ErrorMsg ();
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
		}
		
		$string = "0";
		
		while ( ! $result->EOF ) {
			if ($string)
				$string .= ",";
			$string .= $result->fields ( "clienteid" );
			
			$result->MoveNext ();
		}
		
		return $string;
	}
	
	/**
	 * Method to add actions of cliente to accountcliente table.
	 * @public
	 * @ Pablo 280714
	 * 
	 * @return bool
	 */
	function AddAccountClientesClientes($FormElements) {
		if (count ( $FormElements ["allclientesclientes"] )) {
			// Check for the bogus actions
			//
			foreach ( $FormElements ["allclientesclientes"] as $key => $clienteid ) {
				if (! array_key_exists ( $clienteid, $_SESSION ['available_clientesclientes'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["allclientesclientes"] as $key => $clienteid ) {
				$sql = "INSERT INTO accountcliente (accountid,clienteid,accountid_equivale) " . "      VALUES(" . $FormElements ["accountid"] . "," . $clienteid . ",0)";
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to remove clientes from accountcliente table.
	 * @public
	 * @Pablo 280714
	 * 
	 * @return bool
	 */
	function RemoveAccountClientesClientes($FormElements) {
		if (count ( $FormElements ["accountclientecliente"] )) {
			foreach ( $FormElements ["accountclientecliente"] as $key => $clienteid ) {
				if (! array_key_exists ( $clienteid, $_SESSION ['account_clientesclientes'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["accountclientecliente"] as $key => $clienteid ) {
				$sql = "DELETE FROM accountcliente " . "      WHERE accountid =" . $FormElements ["accountid"] . " AND " . "            clienteid=" . $clienteid . " and accountid_equivale=0";
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to get the equivaless that the account doesn't have.
	 * @public
	 * @ Pablo 280714
	 * 
	 * @return string
	 */
	Function GetAllClientesEquivalesExceptAccountHas($accountid) {
		$sql = "SELECT accountid_equivale from accountcliente WHERE clienteid = 0 and accountid_equivale > 0 and accountid=" . $accountid;
		
		$result = $this->gDB->Execute ( $sql );
		
		if ($result === false) {
			$this->mErrorMessage = 'error reading: ' . $this->gDB->ErrorMsg ();
			
			if ($_SESSION ['IS_ERROR_REPORTING'])
				$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
		}
		
		$string = "0";
		
		while ( ! $result->EOF ) {
			if ($string)
				$string .= ",";
			$string .= $result->fields ( "accountid_equivale" );
			
			$result->MoveNext ();
		}
		
		return $string;
	}
	
	/**
	 * Method to add actions of equivale to accountcliente table.
	 * @public
	 * @ Pablo 280714
	 * 
	 * @return bool
	 */
	function AddAccountClientesEquivales($FormElements) {
		if (count ( $FormElements ["allclientesequivales"] )) {
			// Check for the bogus actions
			//
			foreach ( $FormElements ["allclientesequivales"] as $key => $equivaleid ) {
				if (! array_key_exists ( $equivaleid, $_SESSION ['available_clientesequivales'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["allclientesequivales"] as $key => $equivaleid ) {
				$sql = "INSERT INTO accountcliente (accountid,clienteid,accountid_equivale) " . "      VALUES(" . $FormElements ["accountid"] . ",0," . $equivaleid . ")";
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
	
	/**
	 * Method to remove equivale from accountcliente table.
	 * @public
	 * @Pablo 280714
	 * 
	 * @return bool
	 */
	function RemoveAccountClientesEquivales($FormElements) {
		if (count ( $FormElements ["accountclienteequivale"] )) {
			foreach ( $FormElements ["accountclienteequivale"] as $key => $equivaleid ) {
				if (! array_key_exists ( $equivaleid, $_SESSION ['account_clientesequivales'] )) {
					$this->mySecurity->GotoThisPage ( "bogus.php" );
				}
			}
			
			$this->gDB->BeginTrans ();
			
			foreach ( $FormElements ["accountclienteequivale"] as $key => $equivaleid ) {
				$sql = "DELETE FROM accountcliente WHERE accountid =" . $FormElements ["accountid"] . " AND clienteid=0 and accountid_equivale=" . $equivaleid;
				
				$result = $this->gDB->Execute ( $sql );
				
				if ($result === false) {
					$this->gDB->RollbackTrans ();
					
					if ($_SESSION ['IS_ERROR_REPORTING'])
						$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
					
					return false;
				}
			}
			
			$this->gDB->CommitTrans ();
		}
		return true;
	}
}
?>