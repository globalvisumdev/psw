<?php
include_once "securityConfig.inc.php";
require_once "Security.class.php";
include_once "commonFunctions.php";
include_once( ADODB_DIRECTORY."/adodb.inc.php" );
require_once "Form.class.php";
require_once "MyDatabase.class.php";

session_start();

/**
* A class that handles adding, modifying and deleting the actions
* from the table.
* @author Bulent Tezcan. bulent@greenpepper.ca
*/

class Actions extends MyDatabase
{
	/**
	* Constructor of the class Actions.
	* @public
	*/
	function Actions( )
	{		
		// set the table properties
		$this->mTableName = "actions";
		$this->mKeyName = "actionid";
		
		// set the Column Properties. These are required to be able to
		// write the table
		$this->mTableFields['actionid']  ['type']   = "integer";
		$this->mTableFields['actionname']['type']   = "string";
		$this->mTableFields['actionname']['unique'] = TRUE;

		// set other properties
		$this->mFormName = "ActionsForm";
		$this->mExtraFormText = "";

		$this->mySecurity = new Security( );

		// Set up database connection
		//$this->MyDatabase();
		parent::__construct();
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
	* Method to get the action information with a given key.
	* @public
	* @returns array
	*/
	function GetAction($key)
	{
	    
	    $sql = "SELECT * FROM actions WHERE actionid=:key";
	    try {
	        $resultp2 = $this->pDB->prepare($sql);
	        $datadb=array(':key'=>$key);
	        $resultp2->execute($datadb);
	        while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
	            return $row2;
	        }
	    }catch(PDOException  $e ){
	        $this-> SetErrorMessage('Error de lectura ga','');
	        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura ga','');
	        return false;
	    }

        return false;
        
// 		$sql = "SELECT * FROM actions WHERE actionid=$key";
		
// 		$result = $this->gDB->Execute($sql);
		
// 		if ($result === false)
// 		{
// 			$this->SetErrorMessage('error reading: '
// 														.$this->gDB->ErrorMsg( ));
			
// 			if ($_SESSION['IS_ERROR_REPORTING'])
// 				$this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

// 			return false;
// 		}

// 		return $result;
	}
	/**
	* Method to get the action information with a given name.
	* @public
	* @returns array
	*/
	function GetActionIdByName($name)
	{
	    $sql = "SELECT * FROM actions WHERE actionname=:key";
	    try {
	        $resultp2 = $this->pDB->prepare($sql);
	        $datadb=array(':key'=>$name);
	        $resultp2->execute($datadb);
	        while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
	            return $row2->actionid;
	        }
	    }catch(PDOException  $e ){
	        $this-> SetErrorMessage('Error de lectura gaibn','');
	        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gaibn','');
	        return false;
	    }
	    
	    return false;
	    
// 		$sql = "SELECT * FROM actions WHERE actionname='".$name."'";
		
// 		$result = $this->gDB->Execute($sql);
		
// 		if ($result === false)
// 		{
// 			$this->SetErrorMessage('error reading: '
// 														.$this->gDB->ErrorMsg( ));
			
// 			if ($_SESSION['IS_ERROR_REPORTING'])
// 				$this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

// 			return false;
// 		}

// 		return $result->fields("actionid");
	}
	/**
	* Method to delete action from the table with a given key.
	* @public
	* @returns bool
	*/
	function DeleteAction($actionId)
	{
	    $sql = "DELETE FROM actions WHERE actionid=:key";
	    try {
	        $resultp2 = $this->pDB->prepare($sql);
	        $datadb=array(':key'=>$actionId);
	        $resultp2->execute($datadb);
	    }catch(PDOException  $e ){
	        $this-> SetErrorMessage('Error de lectura da','');
	        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura da','');
	        return false;
	    }
	    
// 		$sql = "DELETE FROM actions WHERE actionid=$actionId";

// 		if ($this->gDB->Execute($sql) === false)
// 		{
// 			$this->mErrorMessage = 'error inserting: '
// 													.$this->gDB->ErrorMsg( );
			
// 			if ($_SESSION['IS_ERROR_REPORTING'])
// 				$this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

// 		}
// 		else
			$this->mErrorMessage = "Acción eliminada exitosamente.";
	}
	
	/**
	* Method to update actions table with a given key, fields and values.
	* @public
	* @returns bool
	*/
	function UpdateAction($FormElements,$actionId)
	{
		if (!$this->ErrCheckActionsForm($FormElements,$actionId))
		{
			$this->Field($this->mKeyName,$actionId);

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
	* Method to add actions to the table.
	* @public
	* @returns bool
	*/
	Function AddAction($FormElements)
	{
		if (!$this->ErrCheckActionsForm($FormElements,null))
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
				$this->mErrorMessage = "Acción añadida exitosamente.";
			else
				return false;
		}

		return true;
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
	* Method to display all the actions.
	* @private
	* @returns string
	*/
	function ListActions( )
	{
		include_once "Paging.class.php";			
		
		$sql = "SELECT count(*) as totalrecord FROM actions";
	
		$number=0;
		try {
		    $resultp2 = $this->pDB->prepare($sql);
		    $resultp2->execute();
		    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
		        $number=(int)$row2->totalrecord;#record results selected from db
		    }
		}catch(PDOException  $e ){
		    $this-> SetErrorMessage('Error de lectura la','');
		    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura la','');
		    return false;
		}
		
// 		$result = &$this->gDB->Execute($sql);
		
// 		if ($result === false 	AND $_SESSION['IS_ERROR_REPORTING'])
// 				$this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql );

// 		$number=(int)$result->fields("totalrecord");#record results selected from db 
		$displayperpage="8";# record displayed per page 
		$pageperstage="5";# page displayed per stage 
		$allpage=ceil($number/$displayperpage);# how much page will it be ? 
		$allstage=ceil($allpage/$pageperstage);# how many page will it be ? 
		if(trim($_GET['startpage'])==""){$_GET['startpage']=1;}  
		if(trim($_GET['nowstage'])==""){$_GET['nowstage']=1;}  
		if(trim($_GET['nowpage'])==""){$_GET['nowpage']=$_GET['startpage'];}  

		$_GET['startpage']=intval($_GET['startpage']);
		$_GET['nowstage'] = intval($_GET['nowstage']);
		$_GET['nowpage'] = intval($_GET['nowpage']);
		
		$p = new Paging($_GET['nowstage'],$_GET['startpage'],$allpage,$_GET['nowpage'],
										$pageperstage,$allstage, $extrargv="" );

		$sql = "SELECT * FROM actions ORDER BY actionname";

//		$result = &$this->gDB->SelectLimit($sql,$numrows=$displayperpage,$offset=($_GET['nowpage']-1)*$displayperpage,$inputarr=false);
		$numrows=$displayperpage;
		$offset=($_GET['nowpage']-1)*$displayperpage;
		$sql .=" limit ".$offset.",".$numrows;

		try {
		    $resultp2 = $this->pDB->prepare($sql);
		    $datadb=array(':acc'=>$accountid);
		    $resultp2->execute($datadb);
		    
		    $this->SendHeader( );
		    
		    if ($displayperpage >= 15)
		    {
		        $passtru = new PassTru("");
		        $passtru->SetColSpan( $this->myForm-> GetNumberOfColumns() );
		        $passtru->SetClass("");
		        $passtru->SetCellAttributes(array('align'=>'center'));
		        $passtru->SetValue( $p->printPagingNavigation() );
		        $this->myForm->	AddFormElementToNewLine	($passtru);
		    }
		    
		    while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
		        
		        $edit = "&nbsp;";
		        $delete = "&nbsp;";
		        
		        # Edit
		        // 				if ($this->mySecurity-> isAllowedTo('modify action'))
		        if ($this->mySecurity-> isAllowedTo(4))
		            $edit = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"actionsModify.php?actionId=".$row2->actionid."&mode=edit\">Editar</a>";
		            # Delete
		            //if ($this->mySecurity-> isAllowedTo('delete action'))
		            if ($this->mySecurity-> isAllowedTo(3))
		                $delete = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"actionsModify.php?actionId=".$row2->actionid."&mode=delete\">Borrar</a>";
		                
		                $passtru = new PassTru();
		                
		                $passtru->SetValue($value=$row2->actionname);
		                $passtru->SetClass("DataTD");
		                
		                $this->myForm-> AddFormElementToNewLine($passtru);
		                
		                $passtru = new PassTru();//Pablo 280809
		                $passtru->SetValue($edit);
		                $passtru->SetClass("DataTD");
		                
		                $this->myForm-> AddFormElement($passtru);
		                
		                $passtru = new PassTru();//Pablo 280809
		                $passtru->SetValue($delete);
		                
		                $this->myForm-> AddFormElement($passtru);
		                
		                $result->MoveNext( );
		                
		        
		    }
		}catch(PDOException  $e ){
		    $this-> SetErrorMessage('Error de lectura la','');
		    if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura la','');
		    return false;
		}
		
		$passtru = new PassTru("");
		$passtru->SetColSpan( $this->myForm-> GetNumberOfColumns() );
		$passtru->SetClass("");
		$passtru->SetCellAttributes(array('align'=>'center'));
		$passtru->SetValue( $p->printPagingNavigation() );
		$this->myForm->	AddFormElementToNewLine	($passtru);
		
		$passtru = new PassTru("");//Pabo 280809
		$passtru->SetCellAttributes(array('align'=>'left'));
		$passtru->SetValue("<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"adminmenu.php\"><-- Volver al Menú Seguridad</a>");
		$this->myForm->	AddFormElementToNewLine	($passtru);
		
		$this->SendTrailer( );
		
// 		if ($result === false 	AND $_SESSION['IS_ERROR_REPORTING'])
// 				$this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql );

//		if (!$result->EOF)
// 		{
// 			$this->SendHeader( );
			
// 			if ($displayperpage >= 15)
// 			{
// 				$passtru = new PassTru("");
// 				$passtru->SetColSpan( $this->myForm-> GetNumberOfColumns() );
// 				$passtru->SetClass("");
// 				$passtru->SetCellAttributes(array('align'=>'center'));
// 				$passtru->SetValue( $p->printPagingNavigation() );		
// 				$this->myForm->	AddFormElementToNewLine	($passtru);
// 			}

// 			while (!$result->EOF)
// 			{
// 				$edit = "&nbsp;";
// 				$delete = "&nbsp;";
				
// 				# Edit
// // 				if ($this->mySecurity-> isAllowedTo('modify action'))
// 				if ($this->mySecurity-> isAllowedTo(4))
// 				$edit = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"actionsModify.php?actionId=".$result->fields("actionid")
// 				."&mode=edit\">Editar</a>";
// 				# Delete
// 				//if ($this->mySecurity-> isAllowedTo('delete action'))
// 				if ($this->mySecurity-> isAllowedTo(3))
// 					$delete = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"actionsModify.php?actionId=".$result->fields("actionid")
// 				."&mode=delete\">Borrar</a>";
				
// 				$passtru = new PassTru();

// 				$passtru->SetValue($value=$result->fields("actionname"));
// 				$passtru->SetClass("DataTD");

// 				$this->myForm-> AddFormElementToNewLine($passtru);

// 				$passtru = new PassTru();//Pablo 280809
// 				$passtru->SetValue($edit);
// 				$passtru->SetClass("DataTD");

// 				$this->myForm-> AddFormElement($passtru);

// 				$passtru = new PassTru();//Pablo 280809
// 				$passtru->SetValue($delete);

// 				$this->myForm-> AddFormElement($passtru);
				
// 				$result->MoveNext( );
// 			}
					
// 			$passtru = new PassTru("");
// 			$passtru->SetColSpan( $this->myForm-> GetNumberOfColumns() );
// 			$passtru->SetClass("");
// 			$passtru->SetCellAttributes(array('align'=>'center'));
// 			$passtru->SetValue( $p->printPagingNavigation() );		
// 			$this->myForm->	AddFormElementToNewLine	($passtru);
			
// 			$passtru = new PassTru("");//Pabo 280809
// 			$passtru->SetCellAttributes(array('align'=>'left'));
// 			$passtru->SetValue("<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"adminmenu.php\"><-- Volver al Menú Seguridad</a>");			
// 			$this->myForm->	AddFormElementToNewLine	($passtru);

// 			$this->SendTrailer( );
// 		}
	}
		
	/**
	* Method to send the form. The form is displayed within the method with echo.
	* @private
	* @returns void
	*/
	function SendActionsForm($FormElements,$mode)
	{		
		$myForm = new Form($this->GetFormName( ));

		$myForm-> SetNumberOfColumns( 2 );
		$myForm-> SetCellSpacing( 1 );
		$myForm-> SetCellPadding( 5 );
		$myForm-> SetBorder	( 0 );
		$myForm-> SetAlign ("center");
		$myForm-> SetTableWidth ("500");
		$myForm-> SetTableHeight (null);
		$myForm-> SetCSS ( $_SESSION["CSS"] );
		$myForm-> SetEmptyCells (true);
		
		if ($mode == 'edit')
			$actionMode = ' Modify';
		elseif ($mode == 'delete')
			$actionMode = ' Delete';

		$myForm-> SetFormHeader("Acciones".$actionMode);
		
		$myForm-> SetErrorMessage($this->GetErrorMessage());
		
		$myForm->	AddFormElementToNewLine	(new Label("lb1","Nombre de la Acción :"));
		$myForm-> AddFormElement (new TextField("actionname",$FormElements["actionname"],30,50));
		
		switch ( TRUE )
		{
			case "EDIT" == strtoupper($mode):

// 				if ($this->mySecurity-> isAllowedTo('Modify Action'))
				if ($this->mySecurity-> isAllowedTo(4))
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
			
// 				if ($this->mySecurity-> isAllowedTo('Delete Action'))
				if ($this->mySecurity-> isAllowedTo(3))
				{
					$buttons = new ObjectArray("buttons");
					$buttons->AddObject(new SubmitButton("B_submit","Confirme el borrado"));
					$buttons->AddObject(new SubmitButton("B_cancel","Cancelar"));
					$buttons->SetColSpan( $myForm-> GetNumberOfColumns() );
					$buttons->SetCellAttributes(array("align"=>"middle"));

					$myForm-> AddFormElement ($buttons);
				}
				
				break;
			
			default:

// 				if ($this->mySecurity-> isAllowedTo('Add Action'))
				if ($this->mySecurity-> isAllowedTo(2))
				{
					$buttons = new ObjectArray("buttons");
					$buttons->AddObject(new SubmitButton("B_add_submit","Agregar nueva acción"));
					$buttons->AddObject(new SubmitButton("B_clear","Limpiar"));
					$buttons->SetColSpan( $myForm-> GetNumberOfColumns() );
					$buttons->SetCellAttributes(array("align"=>"middle"));

					$myForm-> AddFormElement	($buttons);
				}
		}
		return $myForm->GetFormInTable( );
	}
	/**
	* Method to check the form. Sets the error message and which field is wrong.
	* @public
	* @returns bool
	*/
	function ErrCheckActionsForm($FormElements,$actionId)
	{
		$this->mIsError = 0;
		
		if (!$FormElements["actionname"])
		{
			$this->mErrorMessage = "Por favor, ingrese nombre de acción.";
			$this->mFormErrors["actionname"]=1;
			$this->mIsError = 1;
		}

		# check if the action name is in the database

		$actionid = $this-> GetActionIdByName($FormElements['actionname']);
		
		if ($actionid)
		{
			if ($actionid != $actionId)
			{
				$this->SetErrorMessage("Nombre de acción ya existente. Intente con otro.");
				$this->mFormErrors["actionname"]=1;
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

		$this->myForm-> SetNumberOfColumns( 3 );
		$this->myForm-> SetCellSpacing( 1 );
		$this->myForm-> SetCellPadding( 5 );
		$this->myForm-> SetBorder	( 0 );
		$this->myForm-> SetAlign ("center");
		$this->myForm-> SetTableWidth ("400");
		$this->myForm-> SetTableHeight (null);
		$this->myForm-> SetCSS ( $_SESSION["CSS"] );
		$this->myForm-> SetEmptyCells (false);
		$this->myForm-> SetFormTagRequired (false);
		#$this->myForm-> SetTRMouseOverColor( $overcolor="#FFFF66", $outcolor="#ffcc44", $startingRow=2 );
		$this->myForm-> SetFormHeader("Lista de Acciones");
		
		$mylabel = new Label($name="lb1",$value="Nombre de la Acción");
		$mylabel-> SetClass("ColumnTD");

		$this->myForm-> AddFormElementToNewLine($mylabel);

		$mylabel = new Label($name="lb1",$value="Editar"); //Pablo 280809
		$mylabel->SetValue("Editar");
		$this->myForm-> AddFormElement($mylabel);

		$mylabel = new Label($name="lb1",$value="Borrar"); //Pablo 280809
		$mylabel->SetValue("Borrar");
		$this->myForm-> AddFormElement($mylabel);
	}
	
	/**
	* Method to send the Html in a table.This method is called from the ListActions
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