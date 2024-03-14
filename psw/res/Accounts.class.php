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
		$sql = "SELECT * FROM accounts " . "WHERE accountid = ".intval($key);

		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
		    $resultp = $this->pDB->prepare($sql);
		    $resultp->execute();
		    while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
		        $result = $row;
		    }
		    
		}catch(PDOException  $e ){
		    $this-> SetErrorMessage('Error de lectura gfn','');
		    if ($_SESSION['IS_ERROR_REPORTING'])
			return array(
				"ok" => true,
				"errorMsg" =>  'Error de lectura gfn'
			  );
		}

		return array(
			"ok" => true,
			"data" =>  $result
		  );

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
		$sql = "SELECT firstname,lastname " . "FROM accounts WHERE accountid =".intval( $accountid);
		
		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
		    $resultp = $this->pDB->prepare($sql);
		    $resultp->execute();
		    while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
		        $result = $row->firstname.", ".$row->lastname;
		    }
		    
		}catch(PDOException  $e ){
		    $this-> SetErrorMessage('Error de lectura gan','');
		    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gan','');
		    return false;
		}
		
		return $result;
		

	}
	
	/**
	 * Method to get the account information with a given name.
	 * @public
	 * 
	 * @return array
	 */
	function GetAccountIdByUserName($name) {
		$sql = "SELECT accountid FROM accounts WHERE username= :user";

		$result=false;
		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
		    $resultp = $this->pDB->prepare($sql);
		    $datadb=array(':user'=>$name);
		    $resultp->execute($datadb);
		    while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
		        $result = $row->accountid;
		    }
		    
		}catch(PDOException  $e ){
		    $this-> SetErrorMessage('Error de lectura gaibun','');
		    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaibun','');
		    return false;
		}
		
		return $result;
		
		

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
		$accountid=intval($accountid);
		if ($accountid == 1 or $accountid == $_SESSION ['myAccount'])
			return false;
		
		$sql = "DELETE FROM accounts WHERE accountid =" . $accountid;
		
		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
		    $resultp = $this->pDB->prepare($sql);
		    $resultp->execute();
		    
		}catch(PDOException  $e ){
		    $this-> SetErrorMessage('Error de lectura da','');
		    if ($_SESSION['IS_ERROR_REPORTING'])
			return array(
				"ok" => false,
				"errorMsg" =>  "Error de lectura D.A.",
			);	
		}
		
		return array(
			"ok" => true,
			"errorMsg" =>  "Usuario eliminado correctamente.",
		);	
		
	}
	
	/**
	 * Method to add groups to groupaccounts table for a specific account.
	 * @public
	 * 
	 * @return bool
	 */
	function AddGroupsToAccount($FormElements) {
		$FormElements ["availablegroups"] = explode(",", $FormElements ["availablegroups"]);

		if (count ( $FormElements ["availablegroups"] )) {
			// Check for the bogus actions
			//
			// foreach ( $FormElements ["availablegroups"] as $key => $groupid ) {
			// 	if (! array_key_exists ( $groupid, $_SESSION ['available_groups'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }
			$this->pDB->beginTransaction();
			$sql = "INSERT INTO groupaccounts (groupid,accountid) VALUES(:grupo,:account)";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["availablegroups"] as $key => $groupid ) {

			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':grupo'=>$groupid,':account'=>$_POST ['accountId']);
			        $resultp->execute($datadb);
			        
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura agta','');
			        if ($_SESSION['IS_ERROR_REPORTING'])
					return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura agta',
					);
			    }
			}
			
			$this->pDB->commit();
			return array(
				"ok" => true,
				"errorMsg" =>  'Se agregaron los grupos correctamente.',
			);
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
		$FormElements ["accountgroups"] = explode(",", $FormElements ["accountgroups"]);
	

		if (count ( $FormElements ["accountgroups"] )) {
			// Check for the bogus actions
			//
			// foreach ( $FormElements ["accountgroups"] as $key => $groupid ) {
			// 	if (! array_key_exists ( $groupid, $_SESSION ['accounts_group'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }

			
			$this->pDB->beginTransaction();
			$sql = "DELETE FROM groupaccounts WHERE accountid =:account and groupid=:grupo";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["accountgroups"] as $key => $groupid ) {
			
				try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':grupo'=>$groupid,':account'=>$_POST ['accountId']);
			        $resultp->execute($datadb);
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura rgfa','');
			        if ($_SESSION['IS_ERROR_REPORTING'])
					return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura rgfa',
					);
			    }
			}
			
			$this->pDB->commit();
			return array(
				"ok" => true,
				"errorMsg" =>  'Se quitaron los grupos correctamente.',
			);
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
		
		
		$input = $FormElements ["fecha_desde_reporte"];
		$input = trim ( $input );
		$time = strtotime ( $input );
		$is_valid = date ( 'Y-m-d', $time ) == $input;
		
		if (date ( 'Y-m-d', $time ) != $input) {
			$this->Field ( 'fecha_desde_reporte', '0' );
		}
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

		if ($this->Update()){
			return array(
				"ok" => true,
				"errorMsg" =>  $this -> mErrorMessage,
			);
		}
		else{
			return array(
				"ok" => false,
				"errorMsg" =>  $this -> mErrorMessage,
			);
		}
	}
	
	/**
	 * Method to add Accounts to the table.
	 * @public
	 * 
	 * @return bool
	 */
	Function AddAccount($FormElements) {

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
			$this->mErrorMessage = "Usuario agregado con éxito.";
		else
			return false;
			
	    try {
	        $sql = "INSERT INTO groupaccounts (groupid,accountid) VALUES(:grupo,:account)";
	        $resultp = $this->pDB->prepare($sql);
	        $datadb=array(':grupo'=>$FormElements ['groupid'],':account'=>$newAccountId);
	        $resultp->execute($datadb);
	        
	    }
		catch(PDOException  $e ){
			echo $e-> getMessage();
	        $this-> SetErrorMessage('Error de lectura aa','');
	        if ($_SESSION['IS_ERROR_REPORTING']){
				return array(
					"ok" => false,
					"errorMsg" =>  'Error de lectura aa',
				);
			}
	    }
		return array(
			"ok" => true,
			"errorMsg" =>  $this -> mErrorMessage,
		);
	}
	
	/**
	 * Method to get the groupids the account belongs to.
	 * @public
	 * 
	 * @return string
	 */
	Function GetAllGroupsAccountHas($accountID) {
	    $string = "0"; // we put zero, not to get SQL error
	    $sql = "SELECT groupid FROM groupaccounts WHERE accountid=:account";
	    
	    try {
	        $resultp = $this->pDB->prepare($sql);
	        $datadb=array(':account'=>$accountID);
	        $resultp->execute($datadb);
	        while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
	            if ($string) $string .= ",";
                $string .= $row->groupid;
	        }
	        
	    }catch(PDOException  $e ){
	        $this-> SetErrorMessage('Error de lectura gagah','');
	        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gagah','');
	        return false;
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
	function ListPermission() {

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
		$ban=0;
        $Q= "select * from accountaction_eventual where aae_id=:key and aae_fecha_hasta > now()";
        
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
    
        $Q = "delete from accountaction_eventual where aae_id=:key";
    
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

	function GenerateReportUserPermission($FormElements){

		$formato_ver = $FormElements['formato_ver'];
	
		if ($formato_ver == 'xls') {
			include '../admin/Classes/PHPExcel.php';
		}
	
		if (!isset($FormElements['origen']) or $FormElements['origen'] != 'megabus') {
			$sql = "select * from accounts where cliente_id =:cli";
	
			try {

				$this->pDB->query( "SET NAMES 'UTF8' ");
				$resultp2 = $this->pDB->prepare($sql);
				$datadb=array(':cli'=>$_SESSION['cliente_id']);

				$resultp2->execute($datadb);

				$primera_vez = true;
				// $renglon = 0;
				// $cant = 1;
				
				// $titulo = 'Listado de Permisos Micronauta al '.date('Y-m-d H:i:s');
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
					
					
					$doc->salidaXls($nombre_archivo, $objPHPExcel);
				}

				if ($formato_ver == 'csv') {
					$doc->salidaCsv($nombre_archivo);
				}
				// if ($formato_ver == 'html') {
				// 	// $doc->salidaHtml();
				// 	return array(
				// 		"ok" => true,
				// 		"data" =>  $result
				// 	);
				// }

				$result = array();
				while($row=$resultp2->fetch(PDO::FETCH_ASSOC)) {
					
					if ($primera_vez) {
						$primera_vez = false;
						
					// 		$doc->setCell(0, $xlsRow, $titulo, array(
					// 			'style' => 'colspan="100" align="left"',
					// 			'format' => 'b'
					// 		));
					// 		$xlsRow ++;
					// 		$doc->openTable('class="w3-table-all w3-hoverable w3-border " width="100%"');
					// 		$doc->setRowFormat('class="w3-teal"');
							
					// 		$doc->setCell(0, $xlsRow, utf8_encode('Nombre'), array(
					// 			'tag' => 'th',
					// 			'style' => 'align="center"'
					// 		));
					// 		$doc->setCell(1, $xlsRow, utf8_encode('Apellido'), array(
					// 			'tag' => 'th',
					// 			'style' => 'align="center"'
					// 		));
					// 		$doc->setCell(2, $xlsRow, utf8_encode('Username'), array(
					// 			'tag' => 'th',
					// 			'style' => 'align="center"'
					// 		));
					// 		$doc->setCell(3, $xlsRow, utf8_encode('Permiso'), array(
					// 			'tag' => 'th',
					// 			'style' => 'align="center"'
					// 		));
					// 		$doc->setRowFormat('class=""');
					}

					$sql = "select a.accountid_equivale equivale
						from  accountcliente a,accounts b
						WHERE a.clienteid=0 and a.accountid=" . $row['accountid'] . "
						and a.accountid_equivale <> 0
						and a.accountid_equivale = b.accountid
						and b.cliente_id = :cli
						UNION
						select accountid_equivale equivale
						from  accountcliente
						WHERE clienteid = :cli and accountid=" . $row['accountid'] . "
						and accountid_equivale > 0
						limit 1";
	
					$account = $row['accountid'];

					$resultp3 = $this->pDB->prepare($sql);
					$datadb=array(':cli'=>$_SESSION['cliente_id']);
					$resultp3->execute($datadb);
					
					while($rowe=$resultp3->fetch(PDO::FETCH_ASSOC)) {
						if ($rowe['equivale'] > '') {$account = $rowe['equivale'];}
					}

					
					$qa = " SELECT  actionname FROM groupaccounts ag
					LEFT JOIN groupactions ga ON ag.groupid=ga.groupid
					LEFT JOIN actions a ON ga.actionid = a.actionid
					WHERE ag.accountid=" . $account . " and actionname > ''
					UNION
					SELECT actionname FROM accountaction ag
					LEFT JOIN actions a ON ag.actionid = a.actionid
					WHERE ag.accountid=" . $account . " and actionname > ''";
	
					try {

						$this->pDB->query( "SET NAMES 'UTF8' ");
						$resultp3 = $this->pDB->prepare($qa);
						$resultp3->execute();

						while($rowa=$resultp3->fetch(PDO::FETCH_ASSOC)) {
						    $rowa['lastname']=$row['lastname'];
						    $rowa['firstname']=$row['firstname'];
						    $rowa['username']=$row['username'];
						    $result[] = $rowa;

							// $xlsRow += 1;
							
							// // echo $row[username]." ".$rowa[0].'<br>';
							// $doc->setCell(0, $xlsRow, ($row[firstname]), array(
							// 	'style' => 'align="left"'
							// ));
							// $doc->setCell(1, $xlsRow, ($row[lastname]), array(
							// 	'style' => 'align="left"'
							// ));
							// $doc->setCell(2, $xlsRow, ($row[username]), array(
							// 	'style' => 'align="left"'
							// ));
							// $doc->setCell(3, $xlsRow, ($rowa[actionname]), array(
							// 	'style' => 'align="left"'
							// ));
						}
					}catch(PDOException  $e ){
						echo $e -> getMessage();
						$this-> SetErrorMessage('Error de lectura rlp','');
						if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura rlp','');
						die();
					}
					

				   
				   //    $xlsRow ++;
				   
				}
				
				
				if ($formato_ver == 'html') {
					// $doc->salidaHtml();
					return array(
						"ok" => true,
						"data" =>  $result
					);
				}
				// $doc->closeTable();
				// $doc->addHtml('</table>');
				// $doc->addHtml($LinkRetorno);
				// $doc->addHtml('<br><br>');
				
				// $doc->output();
				// die();
				
			}catch(PDOException  $e ){
				// $e -> getMessage();
				$this-> SetErrorMessage('Error de lectura rlp','');
				if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura rlp','');
				die();
			}
			
		}
		
		if ($FormElements['origen'] == 'megabus') {
			
			$sql = "SELECT u.*,mo.menu_nombre FROM usuario u ,menu_opcion_xgrupo mg
				, menu_opcion mo
				where u.gpo_codigo = mg.gpo_codigo
				and mg.menu_codigo = mo.menu_codigo
				and usuario_activo ='S'
				";

			try {
				$this->pDB->query( "SET NAMES 'UTF8' ");
				$resultp2 = $this->pDB->prepare($sql);
				$datadb=array(':cli'=>$_SESSION['cliente_id']);
				$resultp2->execute($datadb);

				// $result = array();
				// while($row=$resultp2->fetch(PDO::FETCH_OBJ)) {
				// 	$result[] = $row;
				// }

				// $primera_vez = true;
				// $renglon = 0;
				// $cant = 1;
				
				// $titulo = 'Listado de Permisos Megabus al '.date('Y-m-d H:i:s');
				// $nombre_archivo = $titulo . '.' . $formato_ver;
				// include '../admin/salidaWriter.php';
				// $doc = new salidaWriter();
				// $xlsRow = 1;
				
				if ($formato_ver == 'xls') {
					PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp, array('memoryCacheSize' => '256MB'));
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
					
					
					$doc->salidaXls($nombre_archivo, $objPHPExcel);
				}
				
				if ($formato_ver == 'csv') {
					$doc->salidaCsv($nombre_archivo);
				}

				if ($formato_ver == 'html') {
					// $doc->salidaHtml();
					return array(
						"ok" => true,
						"data" =>  $result
					);
				}
				
				// while($row=$resultp3->fetch(PDO::FETCH_ASSOC)) {
				// 	if ($primera_vez) {
				// 		$primera_vez = false;
						
				// 		$doc->setCell(0, $xlsRow, $titulo, array(
				// 			'style' => 'colspan="100" align="left"',
				// 			'format' => 'b'
				// 		));
				// 		$xlsRow ++;
				// 		$doc->openTable('class="w3-table-all w3-hoverable w3-border " width="100%"');
				// 		$doc->setRowFormat('class="w3-teal"');
						
				// 		$doc->setCell(0, $xlsRow, utf8_encode('Nombre'), array('tag' => 'th','style' => 'align="center"'));
				// 		$doc->setCell(1, $xlsRow, utf8_encode('Boleteria'), array('tag' => 'th','style' => 'align="center"'));
				// 		$doc->setCell(2, $xlsRow, utf8_encode('Permiso'), array('tag' => 'th','style' => 'align="center"'));
				// 		$doc->setRowFormat('class=""');
				// 	}
					
				// 	if ($row['usuario_nombre'] != $usuarioant) {$xlsRow ++;}
				// 	$usuarioant=$row['usuario_nombre'] ;
				// 	$xlsRow += 1;
					
				// 	$doc->setCell(0, $xlsRow, ($row['usuario_nombre']), array('style' => 'align="left"'));
				// 	$doc->setCell(1, $xlsRow, ($row['usuario_boleteria_id']), array('style' => 'align="left"'));
				// 	$doc->setCell(2, $xlsRow, ($row['menu_nombre']), array('style' => 'align="left"'));
				// }
				
				
				
				// $doc->closeTable();
				// $doc->addHtml('</table>');
				// $doc->addHtml($LinkRetorno);
				// $doc->addHtml('<br><br>');
				
				// $doc->output();
				// die();
			}
			catch(PDOException  $e ){
				$this-> SetErrorMessage('Error de lectura le','');
				if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura le','');
				return false;
			}
			
		}

	}

	function GenerateReportLog($FormElements){
		include('../include.php');
		// getLang(); // load lang file
		// doHTML(); // print HTML tag
	  
		$fecha_desde = (isset($FormElements["fecha_desde"])) ? $FormElements["fecha_desde"] : date("Y-m-d", mktime());
		$fecha_hasta = (isset($FormElements["fecha_hasta"])) ? $FormElements["fecha_hasta"] : date("Y-m-d", mktime());
	  //   $myTitle = sprintf($L['title_lista_log']);



		  list($anio, $mes, $dia  ) = preg_split( '[\W]', $fecha_desde );
		  $fecha_desde_sql = $anio.'-'.$mes.'-'.$dia;
		  list($anio, $mes, $dia,  ) = preg_split( '[\W]', $fecha_hasta );
		  $fecha_hasta_sql = $anio.'-'.$mes.'-'.$dia;


	  
		  $query = "SELECT * from log "
		  . " left join activity on log.activityid = activity.activityid"
		  . " left join accounts on log.accountid = accounts.accountid"
		  . " left join cliente on accounts.cliente_id = cliente.cliente_id"
		  . " where log.fechayhora >=:fdesde"
		  . " AND log.fechayhora <=:fhasta";
		  if ($_SESSION['myHierarchy'] > 1) {
			$query.= " AND accounts.cliente_id = ".intval($FormElements['cliente_id']);
		  }
		  $query.= " ORDER BY fechayhora desc";
	
	
		  $primera_vez = 1;
		  if ($primera_vez) {
			$primera_vez = 0;
			// $titulo = "<p>Log de Actividades entre el <b>".$fecha_desde."</b> y el <b>".$fecha_hasta."</b><br></p>";
		  }
	
		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
			$resultp2 = $this->pDB->prepare($query);
			$datadb=array(':fdesde'=>$fecha_desde_sql." 00:00:00",':fhasta'=>$fecha_hasta_sql." 23:59:59");
			$resultp2->execute($datadb);
	
			$result = array();
			while($row=$resultp2->fetch(PDO::FETCH_OBJ)) {
				$result[] = $row;
			}
			return array(
				"ok" => true,
				"data" =>  $result
			);     
		}
		catch(PDOException  $e ){
			$this-> SetErrorMessage('Error de lectura le','');
			if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura vl','');
			return false;
		}
	}
	
    function GenerateReport ($FormElements) {
  		// ini_set('display_errors', 1);	


        $formato_ver = $FormElements['formato_ver'];
    
        if ($formato_ver == 'xls') {
            include '../admin/Classes/PHPExcel.php';
        }
    
        $sql = "select * from accountaction_eventual,accounts,actions
			where cliente_id =:cli
			and accountid=aae_accountid
			and actionid = aae_actionid 
			order by username";
    
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
            
            // $titulo = 'Listado de Permisos Eventuales al '.date('Y-m-d H:i:s');
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
                
                
                $doc->salidaXls($nombre_archivo, $objPHPExcel);
            }

            if ($formato_ver == 'csv') {
                $doc->salidaCsv($nombre_archivo);
            }
            if ($formato_ver == 'html') {
                // $doc->salidaHtml();
				return array(
					"ok" => true,
					"data" =>  $result
				);
            }
			
            // while($row=$resultp2->fetch(PDO::FETCH_ASSOC)) {
                
            //     if ($primera_vez) {
            //         $primera_vez = false;
                    
                
            //         $doc->setCell(0, $xlsRow, $titulo, array(
            //         'style' => 'colspan="100" align="left"',
            //         'format' => 'b'
            //         ));
            //         $xlsRow ++;
            //         $doc->openTable('class="w3-table-all w3-hoverable w3-border " width="100%"');
            //         $doc->setRowFormat('class="w3-teal"');
                    
            //         $doc->setCell(0, $xlsRow, utf8_encode('Nombre'), array(
            //             'tag' => 'th',
            //             'style' => 'align="center"'
            //         ));
            //         $doc->setCell(1, $xlsRow, utf8_encode('Apellido'), array(
            //             'tag' => 'th',
            //             'style' => 'align="center"'
            //         ));
            //         $doc->setCell(2, $xlsRow, utf8_encode('Username'), array(
            //             'tag' => 'th',
            //             'style' => 'align="center"'
            //         ));
            //         $doc->setCell(3, $xlsRow, utf8_encode('Permiso'), array(
            //             'tag' => 'th',
            //             'style' => 'align="center"'
            //         ));
            //         $doc->setCell(4, $xlsRow, utf8_encode('Desde'), array(
            //             'tag' => 'th',
            //             'style' => 'align="center"'
            //         ));
            //         $doc->setCell(5, $xlsRow, utf8_encode('Hasta'), array(
            //             'tag' => 'th',
            //             'style' => 'align="center"'
            //         ));
            //         $doc->setCell(6, $xlsRow, utf8_encode('Creado'), array(
            //             'tag' => 'th',
            //             'style' => 'align="center"'
            //         ));
            //         $doc->setRowFormat('class=""');
            //     }
                
            //     $xlsRow += 1;
                
            //     $doc->setCell(0, $xlsRow, ($row['firstname']), array(
            //         'style' => 'align="left"'
            //     ));
            //     $doc->setCell(1, $xlsRow, ($row['lastname']), array(
            //         'style' => 'align="left"'
            //     ));
            //     $doc->setCell(2, $xlsRow, ($row['username']), array(
            //         'style' => 'align="left"'
            //     ));
            //     $doc->setCell(3, $xlsRow, ($row['actionname']), array(
            //         'style' => 'align="left"'
            //     ));
            //     $doc->setCell(4, $xlsRow, ($row['aae_fecha_desde']), array(
            //         'style' => 'align="left"'
            //     ));
            //     $doc->setCell(5, $xlsRow, ($row['aae_fecha_hasta']), array(
            //         'style' => 'align="left"' 
            //     ));
            //     $doc->setCell(6, $xlsRow, ($row['aae_username_alta']." ".$row['aae_fecha_alta']), array(
            //         'style' => 'align="left"'
            //     ));
                
            //     if ($formato_ver =='html') {
            //         $doc->setCell(7,$xlsRow,'<span onclick="myFunction('.$row['aae_id'].')" class="w3-button ">&times;</span>');
            //     }
                
                
            // }
            
        }catch(PDOException  $e ){
            $this-> SetErrorMessage('Error de lectura le','');
            if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura le','');
            return false;
        }
        
        // $doc->closeTable();
        // $doc->addHtml('</table>');
        // if ($primera_vez) echo "No hay información para mostrar.";
        // $doc->addHtml($LinkRetorno);
        // $doc->addHtml('<br><br>');
    
        // $doc->output();
        // die();
            
    }

	function EventualPermission($FormElements) {

        $sql="insert into accountaction_eventual (aae_accountid,aae_actionid,aae_fecha_desde,aae_fecha_hasta,aae_username_alta,aae_fecha_alta)
        	values(:acc,:permiso,:fdesde,:fhasta,:user,now())";
		
		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
			$resultp = $this->pDB->prepare($sql);
            $datadb=array(':acc'=>$FormElements['usuario'],':permiso'=> $FormElements['permiso'],':fdesde'=>substr($FormElements['fdesde'],0,10).' '.substr($FormElements['hdesde'],0,5),':fhasta'=>substr($FormElements['fhasta'],0,10).' '.substr($FormElements['hhasta'],0,5),':user'=>$_SESSION['username']);
			$resultp->execute($datadb);

			return array(
				"ok" => true,
				"errorMsg" => "Permiso grabado Correctamente"
			);
		}
		catch(PDOException  $e ){
			$this-> SetErrorMessage('Error de lectura la','');
			if ($_SESSION['IS_ERROR_REPORTING']){
				return array(
					"ok" => false,
					"errorMsg" =>  'No se pudo grabar el permiso, reintente.',
				  );
			} 
		}
				
		// the reason of this one is to prevent people, sending bogus
		// accounts rom the URL. We will match the accountid coming from the URL
		// with the one in session.

	}

	function ListAccounts($search,$sort,$offset, $limit) {
		if ($_SESSION ['myAccount'] == 1) {
		} 
		else {
			if (strlen($search) != 0) {
				$filter = "AND ( a.lastname LIKE '". $search ."%' OR a.username LIKE '". $search ."%' OR g.groupname LIKE '". $search ."%' ) ";
				
			}
			else{
				$filter = "";
			}

			if ($limit != 0) {
				$page = "LIMIT  $offset, $limit ";
			}
			else{
				$page = "";
			}

		$sql = "SELECT cliente_nombre,hierarchy,a.accountid,a.firstname,a.lastname,a.username,group_concat(groupname) grupos 
				FROM accounts a 
				LEFT JOIN groupaccounts ga ON a.accountid=ga.accountid 
				LEFT JOIN groups g ON g.groupid=ga.groupid 
				LEFT JOIN cliente ON a.cliente_id=cliente.cliente_id 
				WHERE (hierarchy >= :hier or hierarchy is null) AND a.accountid > 1 AND a.cliente_id =:cli ". $filter .
				"GROUP BY accountid ORDER BY lastname " . $sort . " " . $page;
		}
		$accounts_read_from_table = array ();
		
		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
			$resultp = $this->pDB->prepare($sql);
			$datadb=array(':hier'=>$_SESSION ['myHierarchy'] ,':cli'=>$_SESSION ['cliente_id']);
			$resultp->execute($datadb);

			$result = array();
			$total = 0;
			while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
				$accounts_read_from_table [$row->accountid] = $row->accountid;

				$row -> edit = false;
				$row -> delete = false;

				if ($this->mySecurity->isAllowedTo ( 12 )) {
					$row -> edit = true;
				}
				if ($this->mySecurity->isAllowedTo ( 11) and $row->accountid != 1 and $row->accountid != $_SESSION ['myAccount']) {
					$row -> delete = true;
				}
				$result[] = $row;
				$total++;
			}
			$_SESSION ['accounts_read_from_table'] = $accounts_read_from_table;

			return array(
				"ok" => true,
				"data" =>  $result,
				"total" => $total
			);
		}
		catch(PDOException  $e ){
			echo $e -> getMessage();
			$this-> SetErrorMessage('Error de lectura la','');
			if ($_SESSION['IS_ERROR_REPORTING']){
				return array(
					"ok" => false,
					"errorMsg" =>  'Error de lectura lg',
				  );
			} 
		}
				
		// the reason of this one is to prevent people, sending bogus
		// accounts rom the URL. We will match the accountid coming from the URL
		// with the one in session.

	}
	
	/**
	 * Method to send the form.
	 * The form is displayed within the method with echo.
	 * @private
	 * 
	 * @return string
	 */
	function SendAccountsForm($FormElements, $alsoSendAccountsGroupForm = false, $basico = false) {
		// $basico=true solo permite editar la contraseña
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
					where accountid =".intval($_GET['accountId'])."
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
			$extraSql = " WHERE groupid <> 1 and hierarchy >= " . intval($_SESSION ['myHierarchy']) ." and (cliente_id='".intval($_SESSION['cliente_id'])."' or cliente_id=0) ORDER BY hierarchy";
			$myForm->AddFormElement ( new GetOptions ( $name = "groupid", $table = "groups", $field = "groupname", $key = "groupid", $extraSql , $selected = $FormElements ["groupid"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 0, $multiple = FALSE, $extra = "" ) );
		}
		
		if ("EDIT" == strtoupper ( $_GET ['mode'] )) {
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Contraseña Anterior :" ) );
			$myForm->AddFormElement ( new Password ( $name = "oldpassword", $value = "", $size = 16, $maxlength = 16, $extra = "" ) );
			if ($basico == false) {
				$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Blanquear Contraseña :" ) );
				$myForm->AddFormElement ( new CheckBox ( "blanquear", 1, false, false ) );
			}
		}
		
		if ("DELETE" != strtoupper ( $_GET ['mode'] )) {
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Contraseña Nueva :" ) );
			$myForm->AddFormElement ( new Password ( $name = "newpassword", $value = "", $size = 16, $maxlength = 16, $extra = "" ) );
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Confirme :" ) );
			$myForm->AddFormElement ( new Password ( $name = "confirmpassword", $value = "", $size = 16, $maxlength = 16, $extra = "" ) );
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

	function AccountGroups($FormElements){

		switch (TRUE){
			Case $FormElements["idSelect"] == "dataGrpAct":
			// Grupos
			$datosActuales = "SELECT g.* FROM groups g LEFT JOIN groupaccounts ga ON g.groupid=ga.groupid WHERE ga.accountid=:account";
			
			$accountID = intval($FormElements ["accountId"]);
	
			$accountsGroup = $this->GetAllGroupsAccountHas ( $accountID );
	
			$datosDisponibles = "SELECT * FROM groups,accounts WHERE hierarchy >= " . $_SESSION ['myHierarchy'] . "
								and accountid = :account
								and groupid <> 1
								and (groups.cliente_id='".intval($_SESSION['cliente_id'])."'
								or (groups.cliente_id=0 ) )
								AND groupid NOT IN (" . $accountsGroup . ")
								AND ( gr_nivel = 0 or accounts.cliente_id =1 )
								order by hierarchy,groupname";

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
				$datadb=array(':account'=> $FormElements ["accountId"]);
				$resultp->execute($datadb);
	
				$queryResult = array();
				while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
					$queryResult[] = $row;
				}
				
				$resultSQL[$clave] = $queryResult;
			}
			catch(PDOException  $e ){
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
		
		$accountID = intval($_GET ['accountId']);
		
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

		$SQL = "SELECT g.* FROM groups g LEFT JOIN groupaccounts ga ON g.groupid=ga.groupid WHERE ga.accountid=:account";

		$_SESSION ['accounts_group'] = "";
		$accounts_group = array ();
		
		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
		    $resultp2 = $this->pDB->prepare($SQL);
		    $datadb=array(':account'=>$accountID);
		    $resultp2->execute($datadb);
		    
		    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
		        $accounts_group [$row2->groupid] = $row2->groupid;
		    }
		    
		}catch(PDOException  $e ){
		    $this-> SetErrorMessage('Error de lectura agf','');
		    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura agf','');
		    return false;
		}

		$_SESSION ['accounts_group'] = $accounts_group;
		
		
        $groupAccounts->SetSQL ( $SQL ,array(':account'=>$accountID));
		
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
					and accountid = :account
					and groupid <> 1
    				and (groups.cliente_id='".intval($_SESSION['cliente_id'])."'
    						or (groups.cliente_id=0 ) )
				AND groupid NOT IN (" . $accountsGroup . ")
		    AND ( gr_nivel = 0 or accounts.cliente_id =1 )
					order by hierarchy,groupname";

		$_SESSION ['available_groups'] = "";
		$available_Groups = array ();
		
		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
		    $resultp2 = $this->pDB->prepare($SQL);
		    $datadb=array(':account'=>$accountID);
		    $resultp2->execute($datadb);
		    
		    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
		        $available_Groups [$row2->groupid] = $row2->groupid;
		    }
		    
		}catch(PDOException  $e ){
		    $this-> SetErrorMessage('Error de lectura agf2','');
		    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura agf2','');
		    return false;
		}
		
		$_SESSION ['available_groups'] = $available_Groups;
		
		
		$availableGroups->SetSQL ( $SQL,$datadb);
		
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
		$this->mIsError = true;
		$this->mErrorMessage = "";
		
		$accountid = intval($accountid);
		
		function validar_clave($clave,&$error_clave){
			if(strlen($clave) < 8){
				$error_clave = "La clave debe tener al menos 8 caracteres";
				return false;
			}
			if(strlen($clave) > 16){
				$error_clave = "La clave no puede tener más de 16 caracteres";
				return false;
			}
			if (!preg_match('`[a-z]`',$clave)){
				$error_clave = "La clave debe tener al menos una letra minúscula";
				return false;
			}
			if (!preg_match('`[A-Z]`',$clave)){
				$error_clave = "La clave debe tener al menos una letra mayúscula";
				return false;
			}
			if (!preg_match('`[0-9]`',$clave)){
				$error_clave = "La clave debe tener al menos un caracter numérico";
				return false;
			}
			$error_clave = "";
			return true;
		}


		if ("EDIT" != strtoupper ( $mode ) and "DELETE" != strtoupper ( $mode )){

			if ($FormElements['newpassword'] == "" or $FormElements['confirmpassword'] == "") {
				$this->mErrorMessage = "Por favor ingrese una contraseña.";
				$this->mIsError = false;
			}
	
			if ($FormElements['newpassword'] != $FormElements['confirmpassword']) {
				$this->mErrorMessage = 'La Contraseña nueva no coincide';
				$this->mIsError = false;
			}
	
			if (validar_clave($FormElements['newpassword'],$error)) {} else {
				$this->mErrorMessage = $error;
				$this->mIsError = false;
			}

			if (! $FormElements ["groupid"]) {
				$this->mErrorMessage = "Seleccione un grupo para este usuario.";
				$this->mFormErrors ["groupid"] = 1;
				$this->mIsError = false;
			}
		}

		if (! $FormElements ["username"]) {
			$this->mErrorMessage = "Por favor, ingrese nombre de usuario.";
			$this->mFormErrors ["username"] = 1;
			$this->mIsError = false;
		} else {
			
			$count=0;
			$sql = "Select count(*) c FROM accounts WHERE username=:user";
			if ($accountid) $sql .= " AND accountid NOT IN($accountid)";
			
			try {
				$resultp2 = $this->pDB->prepare($sql);
				$datadb=array(':user'=>htmlspecialchars ( $FormElements ["username"] ));
				$resultp2->execute($datadb);
				
				while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
					$count=$row2->c;
				}
				
			}catch(PDOException  $e ){
				$this-> SetErrorMessage('Error de lectura ecaf','');
				if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura ecaf','');
				return false;
			}
			if ($count > 0) {
				$this->mErrorMessage = "Nombre de Usuario ya existente.";
				$this->mFormErrors ["username"] = 1;
				$this->mIsError = false;
			}
			
			
		}

		if (! $FormElements ["firstname"]) {
			$this->mErrorMessage = "Por favor ingrese el nombre.";
			$this->mFormErrors ["firstname"] = 1;
			$this->mIsError = false;
		}

		if (! $FormElements ["lastname"]) {
			$this->mErrorMessage = "Por favor ingrese el apellido.";
			$this->mFormErrors ["lastname"] = 1;
			$this->mIsError = false;
		}


	
	if ("EDIT" == strtoupper ( $mode )) {
		if ($FormElements ["oldpassword"] != "" and $FormElements ["blanquear"] != "1")
			if ($FormElements ["newpassword"] == "" or $FormElements ["newpassword"] != $FormElements ["confirmpassword"]) {
				$this->mErrorMessage = "Asegúrese de haber tipeado su nueva contraseña correctamente las dos veces.";
				$this->mFormErrors ["newpassword"] = 1;
				$this->mIsError = false;
			} 				// make sure they know what they are doing. Only the owner can change
			// their password, or you have to know the old password to change it.
			else {
				$password = htmlspecialchars ( $FormElements ["oldpassword"] );
				
				if (USE_MD5)
					$password = md5 ( $password );
				
					$count=0;
					$sql = "SELECT accountid FROM accounts WHERE accountid=:account AND password=:pass";
					try {
						$resultp2 = $this->pDB->prepare($sql);
						$datadb=array(':account'=>$accountid,':pass'=>$password);
						$resultp2->execute($datadb);
						
						while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
							$count=1;
						}
						
					}catch(PDOException  $e ){
						$this-> SetErrorMessage('Error de lectura ecaf','');
						if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura ecaf','');
						return false;
					}
					if ($count==0) {
						$this->mErrorMessage = "Asegúrese de haber tipeado su vieja contraseña correctamente.";
						$this->mFormErrors ["password"] = 1;
						$this->mIsError = false;
					}
					
			}
		if ($FormElements ["blanquear"] == "1")
			if ($FormElements ["newpassword"] == "" or $FormElements ["newpassword"] != $FormElements ["confirmpassword"]) {
				$this->mErrorMessage = "Asegúrese de haber tipeado su nueva contraseña correctamente las dos veces.";
				$this->mFormErrors ["newpassword"] = 1;
				$this->mIsError = false;
			} 				// make sure they know what they are doing. Only the owner can change
			// their password, or you have to know the old password to change it.
			else {
				
				$count=0;
				$sql = "SELECT accountid FROM accounts WHERE accountid=:account limit 1";
				try {
					$resultp2 = $this->pDB->prepare($sql);
					$datadb=array(':account'=>$accountid);
					$resultp2->execute($datadb);
					
					while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
						$count=1;
					}
					
				}catch(PDOException  $e ){
					$this-> SetErrorMessage('Error de lectura ecaf','');
					if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura ecaf','');
					return false;
				}
				if ($count==0) {
					$this->mErrorMessage = "Asegúrese de haber tipeado su vieja contraseña correctamente.";
					$this->mFormErrors ["password"] = 1;
					$this->mIsError = false;
				}
				
			}
	}
	

		
		// check if the username is in the database
		
		$accountID = $this->GetAccountIdByUserName ( $FormElements ['username'] );
		
		if ($accountID) {
			if ($accountid != $accountID) {
				$this->SetErrorMessage ( "Nombre de usuario ya existe. Intente con otro." );
				$this->mFormErrors ["username"] = 1;
				$this->mIsError = false;
			}
		}
		
		return array(
			"ok" => $this->mIsError,
			"errorMsg" =>  $this->mErrorMessage,
		);
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
		$this->myForm->SetFormHeader ( "Lista de Usuarios" );
		
		$mylabel = new Label ( $name = "lb1", $value = "Apellido, Nombre (Nombre de Usuario)" );
		$mylabel->SetClass ( "ColumnTD" );
		$this->myForm->AddFormElementToNewLine ( $mylabel );
		
		$mylabel = new Label ( $name = "lb1", $value = "Apellido, Nombre (Nombre de Usuario)" ); // Pablo 280809
		$mylabel->SetValue ( "Expirado" );
		$this->myForm->AddFormElement ( $mylabel );
		
		$mylabel = new Label ( $name = "lb1", $value = "Apellido, Nombre (Nombre de Usuario)" ); // Pablo 280809
		$mylabel->SetValue ( "Multi<br>Grupo" );
		$this->myForm->AddFormElement ( $mylabel );
		
		$mylabel = new Label ( $name = "lb1", $value = "Apellido, Nombre (Nombre de Usuario)" ); // Pablo 280809
		$mylabel->SetValue ( "Editar" );
		$this->myForm->AddFormElement ( $mylabel );
		
		$mylabel = new Label ( $name = "lb1", $value = "Apellido, Nombre (Nombre de Usuario)" ); // Pablo 280809
		$mylabel->SetValue ( "Borrar" );
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

	#####################################################################################################################3
	function getClientes($nuevaCuenta){

		if($nuevaCuenta != "true"){
			$newAccount = " or accounts.cliente_id=cliente.cliente_id ";
		}
		else{
			$newAccount = ""; 
		}

		if ($_SESSION ['myHierarchy'] == 1) {
			// El usuario "admin" (jerarqu�a 1) es el �nico que puede asignar un usuario a un cliente determinado
			// el resto de los administradores solamente pueden agregar usuarios dentro del ambito del mismo
			// cliente.

			if (isset($_SESSION ['myAccount'])) {
			    //solamente paarece el cliente 1 si el ususario editado pertenece al cliente 1
    			$sql="select cliente_nombre,cliente.cliente_id from cliente ,accounts
					where accountid =".intval($_SESSION ['myAccount'])."
					and (cliente.cliente_id <> 1". $newAccount ." )
					order by cliente_nombre
					";
			}
			
		}

		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
			$resultp = $this->pDB->prepare($sql);
			$datadb=array(':hier'=>$_SESSION ['myHierarchy'] ,':cli'=>$_SESSION ['cliente_id']);
			$resultp->execute($datadb);

			$result = array();
			while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
				$accounts_read_from_table [$row->accountid] = $row->accountid;
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
					"errorMsg" =>  'Error de lectura lg',
				  );
			} 
		}



	}


	// function getAccountActions($FormElements){

	// 	// Acciones actuales
	// 	$accionesActuales = "SELECT a.* FROM actions a LEFT JOIN accountaction ga ON a.actionid=ga.actionid WHERE ga.accountid= :account order by actionname";

	// 	$actionsExceptAccountHas = $this->GetAllActionsExceptAccountHas ( $FormElements ["accountid"] );
			
	// 	if ($actionsExceptAccountHas){
	// 		$accionesDisponibles = "SELECT actions.* FROM actions 
	// 				left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
	// 				WHERE acd.actionid is null and actions.actionid NOT IN " . "($actionsExceptAccountHas)";
	// 	}
	// 	else{
	// 		$accionesDisponibles = "SELECT actions.* FROM actions 
	// 			left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
	// 			WHERE acd.actionid is null and 1=1";
	// 	}
		
	// 	if ($_SESSION ['myHierarchy'] != 1 or $_SESSION ['cliente_id'] != 1) {
	// 		$accionesDisponibles .= " and actionclase = 0 ";
	// 	}
	// 	$accionesDisponibles .= " order by actionname";

	// 	// Clientes
	// 	$clientesExceptAccountHas = $this->GetAllClientesClientesExceptAccountHas ( $FormElements ["accountid"] );
			
	// 	$clientesActuales = "SELECT cliente_nombre,cliente_id clienteid FROM cliente " . "WHERE cliente_id IN ($clientesExceptAccountHas) order by 1";

	// 	$clientesDisponibles = "SELECT cliente_nombre,cliente_id clienteid FROM cliente " . "WHERE cliente_id NOT IN ($clientesExceptAccountHas) order by 1";

	// 	// Empresas
	// 	$empresasExceptAccountHas = $this->GetAllEmpresasExceptAccountHas ( $FormElements ["accountid"] );

	// 	$empresasActuales = "SELECT empresa_nombre,empresa_id FROM " . $_SESSION ['db_cli'] . ".empresa " . "WHERE empresa_id IN ($empresasExceptAccountHas)";
	// 	$empresasDisponibles = "SELECT empresa_nombre,empresa_id empresaid FROM " . $_SESSION ['db_cli'] . ".empresa " . "WHERE empresa_id NOT IN ($empresasExceptAccountHas)";

	// 	// Jurisdicciones
	// 	$jurisdiccionesExceptAccountHas = $this->GetAllJurisdiccionesExceptAccountHas ( $FormElements ["accountid"] );
			

	// 	$jurisdiccionesActuales = "select jur_nombre, jur_id, 0 FROM " . $_SESSION ['db_cli'] . ".jurisdiccion
	// 			where jur_id in ($jurisdiccionesExceptAccountHas)
	// 			union
	// 			select concat('jurisdiccion',jurisdiccionid) jur_nombre , jurisdiccionid jur_id
	// 			,(select count(*) from " . $_SESSION ['db_cli'] . ".jurisdiccion where jur_id=jurisdiccionid) c
	// 			from megacontrol.accountjurisdiccion
	// 			where accountid=:acc
	// 			having c = 0
	// 			order by 1";

	// 	$jurisdiccionesDisponibles = "SELECT jur_nombre,jur_id jurisdiccionid FROM " . $_SESSION ['db_cli'] . ".jurisdiccion " . "WHERE jur_id NOT IN ($jurisdiccionesExceptAccountHas)";
		
		
	// 	// Vehiculos
	// 	$vehiculosExceptAccountHas  = $this->GetAllVehiculosExceptAccountHas ( $FormElements ["accountid"] );
		
	// 	$vehiculosActuales = "select concat(vehiculo_id,' - ',vehiculo_nombre) vehiculo_nombre , vehiculo_id FROM
	// 		" . $_SESSION ['db_cli'] . ".vehiculo where vehiculo_id in ($vehiculosExceptAccountHas)";
			
	// 	$vehiculosDisponibles = "SELECT concat(vehiculo_id,' - ',vehiculo_nombre) vehiculo_nombre , vehiculo_id  FROM " . $_SESSION ['db_cli'] . ".vehiculo " . "WHERE vehiculo_id NOT IN ($jurisdiccionesExceptAccountHas)";

	// 	// Servicios

	// 	$serviciosExceptAccountHas = $this->GetAllServiciosExceptAccountHas ( $FormElements ["accountid"] );
 			
 	// 	$serviciosActuales = "SELECT concat(horc_id,' - ',coalesce(horlinea_nombre,'Sin Linea')) horcnombre,horc_id  FROM " . $_SESSION ['db_cli'] . ".horario_cabecera left join ".$_SESSION ['db_cli'] . ".horario_linea on horlinea_id=horc_linea" . " WHERE horc_id IN ($serviciosExceptAccountHas)";

	// 	$serviciosDisponibles = "SELECT concat(horc_id,' - ',coalesce(horlinea_nombre,'Sin Linea')) horcnombre,horc_id  FROM " . $_SESSION ['db_cli'] . ".horario_cabecera left join ".$_SESSION ['db_cli'] . ".horario_linea on horlinea_id=horc_linea" . " WHERE horc_id NOT IN ($servicios)";

	// 	// Clientes Equivalentes

	// 	$clientesEquivalesExceptAccountHas = $this->GetAllClientesEquivalesExceptAccountHas ( $FormElements ["accountid"] );
			
	// 	$clientesEquivalentesActuales = "SELECT concat(lastname,',',firstname,' (',cliente_nombre,')') nombre,accountid FROM accounts
	// 	,cliente where accounts.cliente_id=cliente.cliente_id
	// 	and accountid IN ($clientesEquivalesExceptAccountHas) order by 1";

	// 	$clientesEquivalentesDisponibles = "SELECT concat(lastname,',',firstname,' (',cliente_nombre,')') nombre,accountid FROM accounts
	// 	,cliente where accounts.cliente_id=cliente.cliente_id
	// 	and accountid NOT IN ($clientesEquivalesExceptAccountHas) order by 1";



	// 	// Conexion con la base de datos
	// 	$SQL = array(
	// 		"dataAccAct" => $accionesActuales,
	// 		"dataAccDisp"=> $accionesDisponibles,

	// 		"dataCliAct" => $clientesActuales,
	// 		"dataCliDisp" => $clientesDisponibles,

	// 		"dataEmprDisp" => $empresasActuales,
	// 		"dataEmprAct" => $empresasDisponibles,

	// 		"dataJurisAct" => $jurisdiccionesActuales,
	// 		"dataJurisDisp" => $jurisdiccionesDisponibles,

	// 		"dataVehiAct" => $vehiculosActuales,
	// 		"dataVehiDisp" => $vehiculosDisponibles,

	// 		"dataServAct" => $serviciosActuales,
	// 		"dataServDisp" => $serviciosDisponibles,

	// 		"dataCliEquiAct" => $clientesEquivalentesActuales,
	// 		"dataCliEquiDisp" => $clientesEquivalentesDisponibles,
	// 		);

	// 	$resultSQL = array(
	// 		"dataAccAct" => array(),
	// 		"dataAccDisp"=> array(),

	// 		"dataCliAct" => array(),
	// 		"dataCliDisp" => array(),

	// 		"dataEmprDisp" => array(),
	// 		"dataEmprAct" => array(),

	// 		"dataJurisAct" => array(),
	// 		"dataJurisDisp" => array(),

	// 		"dataVehiAct" => array(),
	// 		"dataVehiDisp" => array(),

	// 		"dataServAct" => array(),
	// 		"dataServDisp" => array(),

	// 		"dataCliEquiAct" => array(),
	// 		"dataCliEquiDisp" => array(),
	// 		);


	// 	foreach ($resultSQL as $clave => $valor){

	// 		try {
	// 			$this->pDB->query( "SET NAMES 'UTF8' ");
	// 			if ($clave == "dataAccAct" || $clave == "dataAccDisp" || $clave == "dataCliAct" || $clave == "dataCliDisp" || $clave == "dataCliEquiAct" || $clave == "dataCliEquiDisp") {
	// 				$resultp = $this->pDB->prepare($SQL[$clave]);
	// 			}else{
	// 				$resultp = $this->pDBcli->prepare($SQL[$clave]);
	// 			}
	// 			$datadb=array(':account'=> $FormElements ["accountid"]);
	// 			$resultp->execute($datadb);
	
	// 			$queryResult = array();
	// 			while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
	// 				$queryResult[] = $row;
	// 			}
				
	// 			$resultSQL[$clave] = $queryResult;
	// 		}
	// 		catch(PDOException  $e ){
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

	// 		"dataCliAct" => $resultSQL["dataCliAct"],
	// 		"dataCliDisp" => $resultSQL["dataCliDisp"],
			
	// 		"dataEmprDisp" => $resultSQL["dataEmprDisp"],
	// 		"dataEmprAct" => $resultSQL["dataEmprAct"],

	// 		"dataJurisAct" => $resultSQL["dataJurisAct"],
	// 		"dataJurisDisp" => $resultSQL["dataJurisDisp"],

	// 		"dataVehiAct" => $resultSQL["dataVehiAct"],
	// 		"dataVehiDisp" => $resultSQL["dataVehiDisp"],

	// 		"dataServAct" => $resultSQL["dataServAct"],
	// 		"dataServDisp" => $resultSQL["dataServDisp"],

	// 		"dataCliEquiAct" => $resultSQL["dataCliEquiAct"],
	// 		"dataCliEquiDisp" => $resultSQL["dataCliEquiDisp"],
	// 	);
	// }

	function getAccountActions($FormElements){

		switch (TRUE){
			Case $FormElements["idSelect"] == "dataAccAct":
				
				// Acciones actuales
				$datosActuales = "SELECT a.* FROM actions a LEFT JOIN accountaction ga ON a.actionid=ga.actionid WHERE ga.accountid= :account order by actionname";
		
				$actionsExceptAccountHas = $this->GetAllActionsExceptAccountHas ( $FormElements ["accountid"] );
					
				if ($actionsExceptAccountHas){
					$datosDisponibles = "SELECT actions.* FROM actions 
							left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
							WHERE acd.actionid is null and actions.actionid NOT IN " . "($actionsExceptAccountHas)";
				}
				else{
					$datosDisponibles = "SELECT actions.* FROM actions 
						left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
						WHERE acd.actionid is null and 1=1";
				}
				
				if ($_SESSION ['myHierarchy'] != 1 or $_SESSION ['cliente_id'] != 1) {
					$datosDisponibles .= " and actionclase = 0 ";
				}
				$datosDisponibles .= " order by actionname";
				
			break;

			Case $FormElements["idSelect"] == "dataCliAct":
				
				// Clientes
				$clientesExceptAccountHas = $this->GetAllClientesClientesExceptAccountHas ( $FormElements ["accountid"] );
					
				$datosActuales = "SELECT cliente_nombre,cliente_id clienteid FROM cliente " . "WHERE cliente_id IN ($clientesExceptAccountHas) order by 1";

				$datosDisponibles = "SELECT cliente_nombre,cliente_id clienteid FROM cliente " . "WHERE cliente_id NOT IN ($clientesExceptAccountHas) order by 1";
			
			break;

			Case $FormElements["idSelect"] == "dataEmprAct":

				// Empresas
				$empresasExceptAccountHas = $this->GetAllEmpresasExceptAccountHas ( $FormElements ["accountid"] );

				$datosActuales = "SELECT empresa_nombre,empresa_id FROM " . $_SESSION ['db_cli'] . ".empresa " . "WHERE empresa_id IN ($empresasExceptAccountHas)";
				$datosDisponibles = "SELECT empresa_nombre,empresa_id FROM " . $_SESSION ['db_cli'] . ".empresa " . "WHERE empresa_id NOT IN ($empresasExceptAccountHas)";
			
			break;

			Case $FormElements["idSelect"] == "dataJurisAct":
				// Jurisdicciones
				$jurisdiccionesExceptAccountHas = $this->GetAllJurisdiccionesExceptAccountHas ( $FormElements ["accountid"] );

				// var_dump($jurisdiccionesExceptAccountHas);
				// die();
				$datosActuales = "select jur_nombre, jur_id jurisdiccionid, 0 FROM " . $_SESSION ['db_cli'] . ".jurisdiccion
					where jur_id in ($jurisdiccionesExceptAccountHas)
					union
					select concat('jurisdiccion',jurisdiccionid) jur_nombre , jurisdiccionid jur_id
					,(select count(*) from " . $_SESSION ['db_cli'] . ".jurisdiccion where jur_id=jurisdiccionid) c
					from ".DB_DATABASE.".accountjurisdiccion
					where accountid=:account
					having c = 0
					order by 1";
					// aca dice megacontrol


				$datosDisponibles = "SELECT jur_nombre,jur_id jurisdiccionid FROM " . $_SESSION ['db_cli'] . ".jurisdiccion " . "WHERE jur_id NOT IN ($jurisdiccionesExceptAccountHas)";
			break;

			Case $FormElements["idSelect"] == "dataVehiAct":
				// Vehiculos
				$vehiculosExceptAccountHas  = $this->GetAllVehiculosExceptAccountHas ( $FormElements ["accountid"] );
				
				$datosActuales = "select concat(vehiculo_id,' - ',vehiculo_nombre) vehiculo_nombre , vehiculo_id FROM
					" . $_SESSION ['db_cli'] . ".vehiculo where vehiculo_id in ($vehiculosExceptAccountHas)";
					
				$datosDisponibles = "SELECT concat(vehiculo_id,' - ',vehiculo_nombre) vehiculo_nombre , vehiculo_id  FROM " . $_SESSION ['db_cli'] . ".vehiculo " . "WHERE vehiculo_id NOT IN ($vehiculosExceptAccountHas)";

			break;

			Case $FormElements["idSelect"] == "dataServAct":
				// Servicios

				$serviciosExceptAccountHas = $this->GetAllServiciosExceptAccountHas ( $FormElements ["accountid"] );

				$datosActuales = "SELECT concat(horc_id,' - ',coalesce(horlinea_nombre,'Sin Linea')) horcnombre,horc_id  FROM " . $_SESSION ['db_cli'] . ".horario_cabecera left join ".$_SESSION ['db_cli'] . ".horario_linea on horlinea_id=horc_linea" . " WHERE horc_id IN ($serviciosExceptAccountHas)";

				$datosDisponibles = "SELECT concat(horc_id,' - ',coalesce(horlinea_nombre,'Sin Linea')) horcnombre,horc_id  FROM " . $_SESSION ['db_cli'] . ".horario_cabecera left join ".$_SESSION ['db_cli'] . ".horario_linea on horlinea_id=horc_linea" . " WHERE horc_id NOT IN ($serviciosExceptAccountHas)";
			
			break;

			Case $FormElements["idSelect"] == "dataCliEquiAct":
				// Clientes Equivalentes

				$clientesEquivalesExceptAccountHas = $this->GetAllClientesEquivalesExceptAccountHas ( $FormElements ["accountid"] );
					
				$datosActuales = "SELECT concat(lastname,',',firstname,' (',cliente_nombre,')') nombre,accountid FROM accounts
				,cliente where accounts.cliente_id=cliente.cliente_id
				and accountid IN ($clientesEquivalesExceptAccountHas) order by 1";

				$datosDisponibles = "SELECT concat(lastname,',',firstname,' (',cliente_nombre,')') nombre,accountid FROM accounts
				,cliente where accounts.cliente_id=cliente.cliente_id
				and accountid NOT IN ($clientesEquivalesExceptAccountHas) order by 1";

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
				if ($FormElements["idSelect"] == "dataAccAct" || $FormElements["idSelect"] == "dataCliAct" || $FormElements["idSelect"] == "dataJurisAct" || $FormElements["idSelect"] == "dataCliEquiAct") {
					$resultp = $this->pDB->prepare($SQL[$clave]);
				}else{
					$resultp = $this->pDBcli->prepare($SQL[$clave]);
				}
				$datadb=array(':account'=> $FormElements ["accountid"]);

				$resultp->execute($datadb);
	
				$queryResult = array();
				while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
					$queryResult[] = $row;
				}
				
				$resultSQL[$clave] = $queryResult;
			}
			catch(PDOException  $e ){
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

	// function getAccountActions($FormElements){
	// 	$SQL = "SELECT a.* FROM actions a 
	// 			LEFT JOIN accountaction ga ON a.actionid=ga.actionid
	// 			WHERE ga.accountid= :account order by actionname";

	// 	// $_SESSION ['account_actions'] = "";
	// 	// $account_Actions = array ();

	// 	try {
	// 		$this->pDB->query( "SET NAMES 'UTF8' ");
	// 		$resultp = $this->pDB->prepare($SQL);
	// 		$datadb=array(':account'=> $FormElements ["accountid"]);
	// 		$resultp->execute($datadb);

	// 		$resultActualActions = array();
	// 		while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
	// 			// $account_Actions [$row->actionid] = $row->actionid;
	// 			$resultActualActions[] = $row;
	// 		}

	// 	}
	// 	catch(PDOException  $e ){
	// 		$this-> SetErrorMessage('Error de lectura saaaf','');
	// 		if ($_SESSION['IS_ERROR_REPORTING']){
	// 			return array(
	// 				"ok" => false,
	// 				"errorMsg" =>  'Error de lectura lg',
	// 			);
	// 		};
	// 	}

	// 	$actions = $this->GetAllActionsExceptAccountHas ( $FormElements ["accountid"] );
			
	// 	if ($actions){
	// 		$SQL = "SELECT actions.* FROM actions 
	// 				left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
	// 				WHERE acd.actionid is null and actions.actionid NOT IN " . "($actions)";
	// 	}
	// 	else{
	// 		$SQL = "SELECT actions.* FROM actions 
	// 			left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
	// 			WHERE acd.actionid is null and 1=1";
	// 	}
		
	// 	if ($_SESSION ['myHierarchy'] != 1 or $_SESSION ['cliente_id'] != 1) {
	// 		$SQL .= " and actionclase = 0 ";
	// 	}
	// 	$SQL .= " order by actionname";

	// 	// $_SESSION ['available_actions'] = "";
	// 	// $availableActions = array ();
		
	// 	try {
	// 		$this->pDB->query( "SET NAMES 'UTF8' ");
	// 		$resultp2 = $this->pDB->prepare($SQL);
	// 		$resultp2->execute();

	// 		$resultAllActions = array();
	// 		while($row=$resultp2->fetch(PDO::FETCH_OBJ)) {
	// 			// $availableActions [$row->actionid] = $row->actionid;
	// 			$resultAllActions[] = $row;
	// 		}
	// 	}
	// 	catch(PDOException  $e ){
	// 		$this-> SetErrorMessage('Error de lectura saaaf','');
	// 		if ($_SESSION['IS_ERROR_REPORTING']) 				
	// 		return array(
	// 			"ok" => false,
	// 			"errorMsg" =>  'Error de lectura lg',
	// 		);
	// 	}

		
	// 	return array(
	// 		"ok" => true,
	// 		"dataActualActions" =>  $resultActualActions,
	// 		"dataAllActions" =>  $resultAllActions,
	// 	);
	// }


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
			//$sql = "SELECT hierarchy,a.accountid,a.* FROM megacontrol.accounts a " . " LEFT JOIN megacontrol.groupaccounts ga ON a.accountid=ga.accountid " . " LEFT JOIN megacontrol.groups g ON g.groupid=ga.groupid " . " WHERE (hierarchy >=" . $_SESSION ['myHierarchy'] . " or hierarchy is null)" . " GROUP BY accountid" . " ORDER BY lastname,firstname";
			// ." AND a.accountid=ga.accountid AND g.groupid=ga.groupid " . $userExtra
		} else {

		}
		
		$sql = "SELECT hierarchy,a.accountid,lastname,firstname FROM megacontrol.accounts a
                LEFT JOIN megacontrol.groupaccounts ga ON a.accountid=ga.accountid
                LEFT JOIN megacontrol.groups g ON g.groupid=ga.groupid
                WHERE (hierarchy >= :hier or hierarchy is null)
                 AND a.accountid > 1 AND a.cliente_id = :cliente
                GROUP BY accountid ORDER BY lastname,firstname";
		
		$accounts_read_from_table = array ();
		
		try {
			$this->pDB->query( "SET NAMES 'UTF8' ");
		    $resultp2 = $this->pDB->prepare($sql);
		    $datadb=array(':hier'=> $_SESSION ['myHierarchy'],':cliente'=>$_SESSION ['cliente_id']);
		    $resultp2->execute($datadb);
		    
		    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
		        $accounts_read_from_table [$row2->accountid] = $row2->accountid;
		        $css_files [$row2->accountid] = trim ( $row2->lastname ) . "," . $row2->firstname;
		    }
		    
		}catch(PDOException  $e ){
		    $this-> SetErrorMessage('Error de lectura saaaf','');
		    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
		    return false;
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
			                                 
			$myForm->AddFormElement ( $mylabel );
			
			$myForm->AddFormElementToNewLine ( new Label ( "lb1", "Acciones del Usuario :<br><br><hr><font style='font-size:8pt; font-style:italic;'>Puede seleccionar varias</font>" ) );
			
			$accountActions = new GetOptions ( "accountactions", "actions", "actionname", "actionid", "ORDER BY actionname", $FormElements ["accountactions"], $default = "-Select-", $displayonly = $this->mDisplayOnly, $size = 10, $multiple = TRUE, $extra = "", $concat = "" );
			
			$SQL = "SELECT a.* FROM actions a 
                    LEFT JOIN accountaction ga ON a.actionid=ga.actionid
                    WHERE ga.accountid= :account order by actionname";
			
			$_SESSION ['account_actions'] = "";
			$account_Actions = array ();
			
			try {
			    $resultp2 = $this->pDB->prepare($SQL);
			    $datadb=array(':account'=> $FormElements ["accountid"]);
			    $resultp2->execute($datadb);
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $account_Actions [$row2->actionid] = $row2->actionid;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
			}

			
			$_SESSION ['account_actions'] = $account_Actions;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$accountActions->SetSQL ( $SQL ,$datadb);
			
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
			
			$actions = $this->GetAllActionsExceptAccountHas ( $FormElements ["accountid"] );
			
			if ($actions) 
				$SQL = "SELECT actions.* FROM actions 
                    left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
					WHERE acd.actionid is null and actions.actionid NOT IN " . "($actions)";
			else
				$SQL = "SELECT actions.* FROM actions 
                    left join actionclientedenied acd on acd.actionid=actions.actionid and acd.cliente_id= ".intval($_SESSION['cliente_id'])."
					WHERE acd.actionid is null and 1=1";
			
			if ($_SESSION [myHierarchy] != 1 or $_SESSION [cliente_id] != 1) {
				$SQL .= " and actionclase = 0 ";
			}
			$SQL .= " order by actionname";

			$_SESSION ['available_actions'] = "";
			$availableActions = array ();
			
			try {
			    $resultp2 = $this->pDB->prepare($SQL);
			    $resultp2->execute();
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $availableActions [$row2->actionid] = $row2->actionid;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
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
			
			$_SESSION ['account_empresas'] = "";
			$account_Empresas = array ();
			
			try {
			    $resultp2 = $this->pDBcli->prepare($SQL);
			    $resultp2->execute();
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $account_Empresas [$row2->empresa_id] = $row2->empresa_id;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
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
			
			$_SESSION ['available_empresas'] = "";
			$availableEmpresas = array ();
			try {
			    $resultp2 = $this->pDBcli->prepare($SQL);
			    $resultp2->execute();
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $availableEmpresas [$row2->empresaid] = $row2->empresaid;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
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
			

			$SQL = "select jur_nombre, jur_id, 0 FROM " . $_SESSION [db_cli] . ".jurisdiccion
					   where jur_id in ($jurisdicciones)
                union
                select concat('jurisdiccion',jurisdiccionid) jur_nombre , jurisdiccionid jur_id
                ,(select count(*) from " . $_SESSION [db_cli] . ".jurisdiccion where jur_id=jurisdiccionid) c
                 from megacontrol.accountjurisdiccion
                where accountid=:acc
                having c = 0
                order by 1";
			
			$_SESSION ['account_jurisdicciones'] = "";
			$account_Jurisdicciones = array ();
			unset($datadb);$datadb=array();
			try {
			    $resultp2 = $this->pDBcli->prepare($SQL);
			    $datadb=array(':acc'=>$FormElements ["accountid"]);
			    $resultp2->execute($datadb);
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $account_Jurisdicciones [$row2->jur_id] = $row2->jur_id;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
			}
			
			
			
			$_SESSION ['account_jurisdicciones'] = $account_Jurisdicciones;
			
			//
			// end of Store this information in session, so we wont get bogus
			//
			
			$accountJurisdiccion->SetSQL ( $SQL,$datadb );
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
			
			
			$_SESSION ['available_jurisdicciones'] = "";
			$availableJurisdicciones = array ();
			try {
			    $resultp2 = $this->pDBcli->prepare($SQL);
			    $resultp2->execute();
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $availableJurisdicciones [$row2->jurisdiccionid] = $row2->jurisdiccionid;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
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

			$_SESSION ['account_vehiculos'] = "";
			$account_Vehiculos = array ();
			try {
			    $resultp2 = $this->pDBcli->prepare($SQL);
			    $resultp2->execute();
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $account_Vehiculos [$row2->vehiculo_id] = $row2->vehiculo_id;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
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

			$_SESSION ['available_vehiculos'] = "";
			$availableVehiculos = array ();
			try {
			    $resultp2 = $this->pDBcli->prepare($SQL);
			    $resultp2->execute();
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $availableVehiculos [$row2->vehiculo_id] = $row2->vehiculo_id;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
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

 			$_SESSION ['account_servicios'] = "";
 			$account_Servicios = array ();
 			try {
 			    $resultp2 = $this->pDBcli->prepare($SQL);
 			    $resultp2->execute();
 			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
 			        $account_Servicios [$row2->horc_id] = $row2->horc_id;
 			    }
 			}catch(PDOException  $e ){
 			    $this-> SetErrorMessage('Error de lectura saaaf','');
 			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
 			    return false;
 			}
			
			// Store this information in session, so we wont get bogus

			
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

 			$_SESSION ['available_servicios'] = "";
 			$availableServicios = array ();
 			try {
 			    $resultp2 = $this->pDBcli->prepare($SQL);
 			    $resultp2->execute();
 			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
 			        $availableServicios [$row2->horc_id] = $row2->horc_id;
 			    }
 			}catch(PDOException  $e ){
 			    $this-> SetErrorMessage('Error de lectura saaaf','');
 			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
 			    return false;
 			}
 			
			// Store this information in session, so we wont get bogus

			
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

			$_SESSION ['account_clientesclientes'] = "";
			$account_ClientesClientes = array ();
			try {
			    $resultp2 = $this->pDB->prepare($SQL);
			    $resultp2->execute();
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $account_ClientesClientes [$row2->cliente_id] = $row2->cliente_id;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
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

			$_SESSION ['available_clientesclientes'] = "";
			$availableClientesClientes = array ();
			
			try {
			    $resultp2 = $this->pDB->prepare($SQL);
			    $resultp2->execute();
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $availableClientesClientes [$row2->clienteid] = $row2->clienteid;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
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
				
			$_SESSION ['account_clientesequivales'] = "";
			$account_ClientesEquivales = array ();
			
			try {
			    $resultp2 = $this->pDB->prepare($SQL);
			    $resultp2->execute();
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $account_ClientesEquivales [$row2->accountid] = $row2->accountid;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
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

			$_SESSION ['available_clientesequivales'] = "";
			$availableClientesEquivales = array ();
			try {
			    $resultp2 = $this->pDB->prepare($SQL);
			    $resultp2->execute();
			    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
			        $availableClientesEquivales [$row2->accountid] = $row2->accountid;
			    }
			}catch(PDOException  $e ){
			    $this-> SetErrorMessage('Error de lectura saaaf','');
			    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura saaaf','');
			    return false;
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
	function GetAllActionsExceptAccountHas($accountid) {
	    
	    $sql = "SELECT actionid from accountaction WHERE accountid=:acc";
        $string = null;	    
	    try {
	        $resultp2 = $this->pDB->prepare($sql);
	        $datadb=array(':acc'=>$accountid);
	        $resultp2->execute($datadb);
	        while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
	            if ($string) $string .= ",";
                $string .= $row2->actionid;
	        }
	    }catch(PDOException  $e ){
	        $this-> SetErrorMessage('Error de lectura gaaeag','');
	        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaaeag','');
	        return null;
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
		$FormElements ["allactions"] = explode(",", $FormElements ["allactions"]);

		if (count ( $FormElements ["allactions"] )) {
			// Check for the bogus actions
			//

			// foreach ( $FormElements ["allactions"] as $key => $actionid ) {
			// 	if (! array_key_exists ( $actionid, $_SESSION ['available_actions'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }

			$this->pDB->beginTransaction();
			$sql = "INSERT INTO accountaction (accountid,actionid) VALUES(:acc,:action)";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["allactions"] as $key => $actionid) {
			    
			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':action'=>$actionid,':acc'=>$FormElements ["accountid"]);
			        $resultp->execute($datadb);
			    }catch(PDOException  $e ){
    			        $this->pDB->rollBack();
    			        $this-> SetErrorMessage('Error de lectura aaa','');
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
	 * Method to remove actions from accountaction table.
	 * @public
	 * @Pablo 280809
	 * 
	 * @return bool
	 */
	function RemoveAccountActions($FormElements) {
		$FormElements ["accountactions"] = explode(",", $FormElements ["accountactions"]);

		if (count ( $FormElements ["accountactions"] )) {
			// Check for the bogus actions
			//
			// foreach ( $FormElements ["accountactions"] as $key => $actionid ) {
			// 	if (! array_key_exists ( $actionid, $_SESSION ['account_actions'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }

			
			$this->pDB->beginTransaction();
			$sql = "DELETE FROM accountaction WHERE accountid =:acc AND actionid=:action";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["accountactions"] as $key => $actionid ) {
			    
			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':action'=>$actionid,':acc'=>$FormElements ["accountid"]);
			        $resultp->execute($datadb);
			        
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura raa','');
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
	 * Method to get the empresas that the account doesn't have.
	 * @public
	 * @ Pablo 310809
	 * 
	 * @return string
	 */
	Function GetAllEmpresasExceptAccountHas($accountid) {
	    
	    $sql = "SELECT empresaid from accountempresa WHERE accountid=:acc";
	    $string = "0";
	    try {
	        $resultp2 = $this->pDB->prepare($sql);
	        $datadb=array(':acc'=>$accountid);
	        $resultp2->execute($datadb);
	        while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
	            if ($string) $string .= ",";
	            $string .= $row2->empresaid;
	        }
	    }catch(PDOException  $e ){
	        $this-> SetErrorMessage('Error de lectura gaaeag','');
	        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaaeag','');
	        return "0";
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
	
		$FormElements ["allempresas"] = explode(",", $FormElements ["allempresas"]);

		if (count ( $FormElements ["allempresas"] )) {
			// Check for the bogus actions
			//
			// foreach ( $FormElements ["allempresas"] as $key => $empresaid ) {
			// 	if (! array_key_exists ( $empresaid, $_SESSION ['available_empresas'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }

			$this->pDB->beginTransaction();
			$sql = "INSERT INTO accountempresa (accountid,empresaid) VALUES(:acc,:empresa)";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["allempresas"] as $key => $empresaid ) {
			    
			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':empresa'=>$empresaid,':acc'=>$FormElements ["accountid"]);
			        $resultp->execute($datadb);
			        
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura aae','');
			        if ($_SESSION['IS_ERROR_REPORTING']) 		
					return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura aae',
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
	 * Method to remove empresas from accountempresa table.
	 * @public
	 * @Pablo 310809
	 * 
	 * @return bool
	 */
	function RemoveAccountEmpresas($FormElements) {
		$FormElements ["accountempresa"] = explode(",", $FormElements ["accountempresa"]);

		if (count ( $FormElements ["accountempresa"] )) {
			// Check for the bogus actions
			//
			// foreach ( $FormElements ["accountempresa"] as $key => $empresaid ) {
			// 	if (! array_key_exists ( $empresaid, $_SESSION ['account_empresas'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }

			$this->pDB->beginTransaction();
			$sql = "DELETE FROM accountempresa WHERE accountid =:acc AND empresaid=:empresa";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["accountempresa"] as $key => $empresaid ) {
			    
			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':empresa'=>$empresaid,':acc'=>$FormElements ["accountid"]);
					$resultp->execute($datadb);
			        
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura rae','');
			        if ($_SESSION['IS_ERROR_REPORTING'])
					return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura rae',
					);
			    }
			}
			
			$this->pDB->commit();
			return array(
				"ok" => true,
				"errorMsg" =>  'Se quitaron las empresas correctamente.',
			);
			
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
	    
	    $sql = "SELECT jurisdiccionid from accountjurisdiccion WHERE accountid=:acc";
	    $string = "0";
	    try {
	        $resultp2 = $this->pDB->prepare($sql);
	        $datadb=array(':acc'=>$accountid);
	        $resultp2->execute($datadb);
	        while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
	            if ($string) $string .= ",";
	            $string .= $row2->jurisdiccionid;
	        }
	    }catch(PDOException  $e ){
	        $this-> SetErrorMessage('Error de lectura gajeah','');
	        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gajeah','');
	        return "0";
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
		$FormElements ["alljurisdicciones"] = explode(",", $FormElements ["alljurisdicciones"]);

		if (count ( $FormElements ["alljurisdicciones"] )) {
			// Check for the bogus actions
			//
			// foreach ( $FormElements ["alljurisdicciones"] as $key => $jurisdiccionid ) {
			// 	if (! array_key_exists ( $jurisdiccionid, $_SESSION ['available_jurisdicciones'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }

			$this->pDB->beginTransaction();
			$sql = "INSERT INTO accountjurisdiccion (accountid,jurisdiccionid) VALUES(:acc,:jur)";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["alljurisdicciones"] as $key => $jurisdiccionid ) {
			    
			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':jur'=>$jurisdiccionid,':acc'=>$FormElements ["accountid"]);
			        $resultp->execute($datadb);
			        
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura aaj','');
			        if ($_SESSION['IS_ERROR_REPORTING'])	return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura aaj',
					);
			    }
			}
			
			$this->pDB->commit();
			return array(
				"ok" => true,
				"errorMsg" =>  'Se agregaron las jurisdicciones correctamente.',
			);
			

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
		$FormElements ["accountjurisdiccion"] = explode(",", $FormElements ["accountjurisdiccion"]);

		if (count ( $FormElements ["accountjurisdiccion"] )) {
			// Check for the bogus actions
			//
			// foreach ( $FormElements ["accountjurisdiccion"] as $key => $jurisdiccionid ) {
			// 	if (! array_key_exists ( $jurisdiccionid, $_SESSION ['account_jurisdicciones'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }

			$this->pDB->beginTransaction();
			$sql = "DELETE FROM accountjurisdiccion WHERE accountid =:acc and jurisdiccionid=:jur";
		
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["accountjurisdiccion"] as $key => $jurisdiccionid ) {
			    
			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':jur'=>$jurisdiccionid,':acc'=>$FormElements ["accountid"]);
			        $resultp->execute($datadb);
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura raj','');
			        if ($_SESSION['IS_ERROR_REPORTING'])	return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura raj',
					);
			    }
			}
			
			$this->pDB->commit();
			return array(
				"ok" => true,
				"errorMsg" =>  'Se quitaron las jurisdicciones correctamente.',
			);
			
			

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

	    $sql = "SELECT vehiculo_id from accountvehiculo WHERE accountid=:acc";
	    $string = "0";
	    try {
	        $resultp2 = $this->pDB->prepare($sql);
	        $datadb=array(':acc'=>$accountid);
	        $resultp2->execute($datadb);
	        while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
	            if ($string) $string .= ",";
	            $string .= $row2->vehiculo_id;
	        }
	    }catch(PDOException  $e ){
	        $this-> SetErrorMessage('Error de lectura gaveah','');
	        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaveah','');
	        return "0";
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
		$FormElements ["allvehiculos"] = explode(",", $FormElements ["allvehiculos"]);

	    if (count ( $FormElements ["allvehiculos"] )) {
	        // Check for the bogus actions
	        //
	        // foreach ( $FormElements ["allvehiculos"] as $key => $vehiculo_id ) {
	        //     if (! array_key_exists ( $vehiculo_id, $_SESSION ['available_vehiculos'] )) {
	        //         $this->mySecurity->GotoThisPage ( "bogus.php" );
	        //     }
	        // }

	        $this->pDB->beginTransaction();
	        $sql = "INSERT INTO accountvehiculo (accountid,vehiculo_id) VALUES(:acc,:veh)";
	        $resultp = $this->pDB->prepare($sql);
	        
	        foreach ( $FormElements ["allvehiculos"] as $key => $vehiculo_id ) {
	            try {
	                $datadb=array(':veh'=>$vehiculo_id,':acc'=>$FormElements ["accountid"]);
	                $resultp->execute($datadb);
	            }catch(PDOException  $e ){
	                $this->pDB->rollBack();
	                $this-> SetErrorMessage('Error de lectura aav','');
	                if ($_SESSION['IS_ERROR_REPORTING'])
					return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura aav',
					);
	            }
	        }
	        
	        $this->pDB->commit();
			return array(
				"ok" => true,
				"errorMsg" =>  'Se agregaron los vehiculos correctamente.',
			);
	        
	        

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
		$FormElements ["accountvehiculo"] = explode(",", $FormElements ["accountvehiculo"]);

	    if (count ( $FormElements ["accountvehiculo"] )) {
	        // Check for the bogus actions
	        //
	        // foreach ( $FormElements ["accountvehiculo"] as $key => $vehiculo_id ) {
	        //     if (! array_key_exists ( $vehiculo_id, $_SESSION ['account_vehiculos'] )) {
	        //         $this->mySecurity->GotoThisPage ( "bogus.php" );
	        //     }
	        // }
	        $this->pDB->beginTransaction();
	        $sql = "DELETE FROM accountvehiculo WHERE accountid =:acc and vehiculo_id=:veh";
	        $resultp = $this->pDB->prepare($sql);
	        
	        foreach ( $FormElements ["accountvehiculo"] as $key => $vehiculo_id ) {
	            try {
	                $datadb=array(':veh'=>$vehiculo_id,':acc'=>$FormElements ["accountid"]);
	                $resultp->execute($datadb);
	            }catch(PDOException  $e ){
	                $this->pDB->rollBack();
	                $this-> SetErrorMessage('Error de lectura rav','');
	                if ($_SESSION['IS_ERROR_REPORTING'])
					return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura rav',
					);
	            }
	        }
	        
	        $this->pDB->commit();
			return array(
				"ok" => true,
				"errorMsg" =>  'Se quitaron los vehiculos correctamente.',
			);
			
	        

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
    
    $sql = "SELECT servicio_id from accountservicio WHERE accountid=:acc";
    $string = "0";
    try {
        $resultp2 = $this->pDB->prepare($sql);
        $datadb=array(':acc'=>$accountid);
        $resultp2->execute($datadb);
        while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
            if ($string) $string .= ",";
            $string .= $row2->servicio_id;
        }
    }catch(PDOException  $e ){
        $this-> SetErrorMessage('Error de lectura gaseah','');
        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaseah','');
        return "0";
    }
    
//     $sql = "SELECT servicio_id from accountservicio WHERE accountid=" . $accountid;
    
//     $result = $this->gDB->Execute ( $sql );
    
//     if ($result === false) {
//         $this->mErrorMessage = 'error reading: ' . $this->gDB->ErrorMsg ();
        
//         if ($_SESSION ['IS_ERROR_REPORTING'])
//             $this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
//     }
    
//     $string = "0";
    
//     while ( ! $result->EOF ) {
//         if ($string)
//             $string .= ",";
//             $string .= $result->fields ( "servicio_id" );
            
//             $result->MoveNext ();
//     }
    
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
	$FormElements ["allservicios"] = explode(",", $FormElements ["allservicios"]);

    if (count ( $FormElements ["allservicios"] )) {
        // Check for the bogus actions
        //
        // foreach ( $FormElements ["allservicios"] as $key => $servicio_id ) {
        //     if (! array_key_exists ( $servicio_id, $_SESSION ['available_servicios'] )) {
        //         $this->mySecurity->GotoThisPage ( "bogus.php" );
        //     }
        // }

        $this->pDB->beginTransaction();
        $sql = "INSERT INTO accountservicio (accountid,servicio_id) VALUES(:acc,:ser)";
        $resultp = $this->pDB->prepare($sql);
        
        foreach ( $FormElements ["allservicios"] as $key => $servicio_id ) {
            try {
                $datadb=array(':ser'=>$servicio_id,':acc'=>$FormElements ["accountid"]);
                $resultp->execute($datadb);
            }catch(PDOException  $e ){
                $this->pDB->rollBack();
                $this-> SetErrorMessage('Error de lectura aas','');
                if ($_SESSION['IS_ERROR_REPORTING'])
				return array(
					"ok" => false,
					"errorMsg" =>  'Error de lectura aas',
				);
            }
        }
        
        $this->pDB->commit();
		return array(
			"ok" => true,
			"errorMsg" =>  'Se agregaron los servicios correctamente.',
		);
        
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
	$FormElements ["accountservicio"] = explode(",", $FormElements ["accountservicio"]);

    if (count ( $FormElements ["accountservicio"] )) {
        // Check for the bogus actions
        //
        // foreach ( $FormElements ["accountservicio"] as $key => $servicio_id ) {
        //     if (! array_key_exists ( $servicio_id, $_SESSION ['account_servicios'] )) {
        //         $this->mySecurity->GotoThisPage ( "bogus.php" );
        //     }
        // }
        
        $this->pDB->beginTransaction();
        $sql = "DELETE FROM accountservicio WHERE accountid =:acc and servicio_id =:ser";
        $resultp = $this->pDB->prepare($sql);
        
        foreach ( $FormElements ["accountservicio"] as $key => $servicio_id ) {
            try {
                $datadb=array(':ser'=>$servicio_id,':acc'=>$FormElements ["accountid"]);
                $resultp->execute($datadb);
            }catch(PDOException  $e ){
                $this->pDB->rollBack();
                $this-> SetErrorMessage('Error de lectura ras','');
                if ($_SESSION['IS_ERROR_REPORTING']) 
				return array(
					"ok" => false,
					"errorMsg" =>  'Error de lectura ras',
				);
            }
        }
        
        $this->pDB->commit();
		return array(
			"ok" => true,
			"errorMsg" =>  'Se quitaron los servicios correctamente.',
		);
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
	    
	    $sql = "SELECT clienteid from accountcliente WHERE clienteid > 0 and accountid_equivale = 0 and accountid=:acc";
	    $string = "0";
	    try {
	        $resultp2 = $this->pDB->prepare($sql);
	        $datadb=array(':acc'=>$accountid);
	        $resultp2->execute($datadb);
	        while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
	            if ($string) $string .= ",";
	            $string .= $row2->clienteid;
	        }
	    }catch(PDOException  $e ){
	        $this-> SetErrorMessage('Error de lectura gaceah','');
	        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaceah','');
	        return "0";
	    }
	    
// 		$sql = "SELECT clienteid from accountcliente WHERE clienteid > 0 and accountid_equivale = 0 and accountid=" . $accountid;
		
// 		$result = $this->gDB->Execute ( $sql );
		
// 		if ($result === false) {
// 			$this->mErrorMessage = 'error reading: ' . $this->gDB->ErrorMsg ();
			
// 			if ($_SESSION ['IS_ERROR_REPORTING'])
// 				$this->mySecurity->EchoError ( $this->gDB->ErrorMsg (), $sql );
// 		}
		
// 		$string = "0";
		
// 		while ( ! $result->EOF ) {
// 			if ($string)
// 				$string .= ",";
// 			$string .= $result->fields ( "clienteid" );
			
// 			$result->MoveNext ();
// 		}
		
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
		$FormElements ["allclientesclientes"] = explode(",", $FormElements ["allclientesclientes"]);

		if (count ( $FormElements ["allclientesclientes"] )) {
			// Check for the bogus actions
			//
			// foreach ( $FormElements ["allclientesclientes"] as $key => $clienteid ) {
			// 	if (! array_key_exists ( $clienteid, $_SESSION ['available_clientesclientes'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }
			$this->pDB->beginTransaction();
			$sql = "INSERT INTO accountcliente (accountid,clienteid,accountid_equivale) VALUES(:acc,:cli,0)";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["allclientesclientes"] as $key => $clienteid ) {
			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':cli'=>$clienteid,':acc'=>$FormElements ["accountid"]);
			        $resultp->execute($datadb);
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura aacc','');
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
				"errorMsg" =>  'Se agregaron los clientes correctamente.',
			);
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
		$FormElements ["accountclientecliente"] = explode(",", $FormElements ["accountclientecliente"]);
		if (count ( $FormElements ["accountclientecliente"] )) {
			// foreach ( $FormElements ["accountclientecliente"] as $key => $clienteid ) {
			// 	if (! array_key_exists ( $clienteid, $_SESSION ['account_clientesclientes'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }

			$this->pDB->beginTransaction();
			$sql = "DELETE FROM accountcliente WHERE accountid =:acc and clienteid=:cli and accountid_equivale=0";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["accountclientecliente"] as $key => $clienteid ) {
			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':cli'=>$clienteid,':acc'=>$FormElements ["accountid"]);
			        $resultp->execute($datadb);
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura racc','');
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
				"errorMsg" =>  'Se quitaron los clientes correctamente.',
			);
			
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

	    $sql = "SELECT accountid_equivale from accountcliente WHERE clienteid = 0 and accountid_equivale > 0 and accountid=:acc";
	    $string = "0";
	    try {
	        $resultp2 = $this->pDB->prepare($sql);
	        $datadb=array(':acc'=>$accountid);
	        $resultp2->execute($datadb);
	        while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
	            if ($string) $string .= ",";
	            $string .= $row2->accountid_equivale;
	        }
	    }catch(PDOException  $e ){
	        $this-> SetErrorMessage('Error de lectura gaceeah','');
	        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaceeah','');
	        return "0";
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
		$FormElements ["allclientesequivales"] = explode(",", $FormElements ["allclientesequivales"]);

		if (count ( $FormElements ["allclientesequivales"] )) {
			// Check for the bogus actions
			//
			// foreach ( $FormElements ["allclientesequivales"] as $key => $equivaleid ) {
			// 	if (! array_key_exists ( $equivaleid, $_SESSION ['available_clientesequivales'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }
			
			$this->pDB->beginTransaction();
			$sql = "INSERT INTO accountcliente (accountid,clienteid,accountid_equivale) VALUES(:acc,0,:cli)";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["allclientesequivales"] as $key => $clienteid ) {
			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':cli'=>$clienteid,':acc'=>$FormElements ["accountid"]);
			        $resultp->execute($datadb);
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura aace','');
			        if ($_SESSION['IS_ERROR_REPORTING']) 
					return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura aace',
					);
			    }
			}
			
			$this->pDB->commit();
			return array(
				"ok" => true,
				"errorMsg" =>  'Se agregaron los clientes equivalentes correctamente.',
			);
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
		$FormElements ["accountclienteequivale"] = explode(",", $FormElements ["accountclienteequivale"]);

		if (count ( $FormElements ["accountclienteequivale"] )) {
			// foreach ( $FormElements ["accountclienteequivale"] as $key => $equivaleid ) {
			// 	if (! array_key_exists ( $equivaleid, $_SESSION ['account_clientesequivales'] )) {
			// 		$this->mySecurity->GotoThisPage ( "bogus.php" );
			// 	}
			// }
			
			$this->pDB->beginTransaction();
			$sql = "DELETE FROM accountcliente WHERE accountid =:acc AND clienteid=0 and accountid_equivale=:cli";
			$resultp = $this->pDB->prepare($sql);
			
			foreach ( $FormElements ["accountclienteequivale"] as $key => $clienteid ) {
			    try {
					$this->pDB->query( "SET NAMES 'UTF8' ");
			        $datadb=array(':cli'=>$clienteid,':acc'=>$FormElements ["accountid"]);
			        $resultp->execute($datadb);
			    }catch(PDOException  $e ){
			        $this->pDB->rollBack();
			        $this-> SetErrorMessage('Error de lectura race','');
			        if ($_SESSION['IS_ERROR_REPORTING'])
					return array(
						"ok" => false,
						"errorMsg" =>  'Error de lectura race',
					);
			    }
			}
			
			$this->pDB->commit();
			return array(
				"ok" => true,
				"errorMsg" =>  'Se quitaron los clientes equivalentes correctamente.',
			);
			
		}
		return true;
	}
}
