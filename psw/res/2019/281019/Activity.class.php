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
class Activity extends MyDatabase
{
	/**
	* Constructor of the class Activity.
	* @public
	*/
	function Activity( )
	{
		// set the table properties
		$this->mTableName = "activity";
		$this->mKeyName = "activityid";
		
		// set the Column Properties. These are required to be able to
		// write the table
		$this->mTableFields['activityid']  ['type']   = "integer";
		$this->mTableFields['description']['type']   = "string";
		$this->mTableFields['description']['unique'] = TRUE;

		// set other properties
		$this->mFormName = "ActivityForm";
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
	* Method to get the activity information with a given key.
	* @public
	* @returns array
	*/
	function GetActivity($key)
	{
		$sql = "SELECT * FROM activity WHERE activityid=$key";
		
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
	* Method to get the activity name with a given key.
	* @public
	* @returns string
	*/
	function GetDescription($key)
	{
		$sql = "SELECT * FROM activity WHERE activityid=$key";
		
		$result = $this->gDB->Execute($sql);
		
		if ($result === false)
		{
			$this->SetErrorMessage('error reading: '
														.$this->gDB->ErrorMsg( ));
			
			if ($_SESSION['IS_ERROR_REPORTING'])
				$this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

			return false;
		}

		return $result->fields("description");
	}

	/**
	* Method to get the activity information with a given name.
	* @public
	* @returns array
	*/
	function GetActivityIdByName($name)
	{
		$sql = "SELECT * FROM activity WHERE description='".$name."'";
		
		$result = $this->gDB->Execute($sql);
		
		if ($result === false)
		{
			$this->SetErrorMessage('error reading: '
														.$this->gDB->ErrorMsg( ));
			
			if ($_SESSION['IS_ERROR_REPORTING'])
				$this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

			return false;
		}

		return $result->fields("activityid");
	}

	/**
	* Method to delete activity from the table with a given key.
	* @public
	* @returns bool
	*/
	function DeleteActivity($activityId)
	{
		$sql = "DELETE FROM activity WHERE activityid=$activityId";

		if ($this->gDB->Execute($sql) === false)
		{
			$this->mErrorMessage = 'error deleting: '
													.$this->gDB->ErrorMsg( );
			
			if ($_SESSION['IS_ERROR_REPORTING'])
				$this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

			return false;
		}
		else
			$this->mErrorMessage = "Actividad borrada exitosamente.";

		return true;
	}
	
	/**
	* Method to update activities table with a given key, fields and values.
	* @public
	* @returns bool
	*/
	function UpdateActivity($FormElements,$activityId)
	{
		if (!$this->ErrCheckActivityForm($FormElements,$activityId))
		{
			$this->Field($this->mKeyName,$activityId);

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
	* Method to add activities to the table.
	* @public
	* @returns bool
	*/
	Function AddActivity($FormElements)
	{
		if (!$this->ErrCheckActivityForm($FormElements,null))
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
				$this->mErrorMessage = "Actividad añadida exitosamente.";
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
	* Method to display all the activities.
	* @private
	* @returns string
	*/
	function ListActivity( )
	{
		$sql = "SELECT * FROM activity order by description";
		
		$result = &$this->gDB->Execute($sql);
		
		if (!$result->EOF)
		{
			$this->SendHeader( );
		
			while (!$result->EOF)
			{
				$edit = "&nbsp;";
				$delete = "&nbsp;";
				
				# Edit
// 				if ($this->mySecurity-> isAllowedTo('modify activity'))
				if ($this->mySecurity-> isAllowedTo(16))
				$edit = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"activityModify.php?activityId=".$result->fields("activityid")
				."&mode=edit\">Editar</a>";
				# Delete
// 				if ($this->mySecurity-> isAllowedTo('delete activity'))
				if ($this->mySecurity-> isAllowedTo(15))
				$delete = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"activityModify.php?activityId=".$result->fields("activityid")
				."&mode=delete\">Borrar</a>";
				
				$passtru = new PassTru();

				$passtru->SetValue($value=$result->fields("activityid"));
				$passtru->SetClass("FieldCaptionTD");

				$this->myForm-> AddFormElementToNewLine($passtru);

				$passtru = new PassTru(); //Pablo 280809
				$passtru->SetValue($value=$result->fields("description"));
				$passtru->SetClass("DataTD");

				$this->myForm-> AddFormElement($passtru);

				$passtru = new PassTru(); //Pablo 280809
				$passtru->SetValue($edit);
				$passtru->SetClass("DataTD");

				$this->myForm-> AddFormElement($passtru);

				$passtru = new PassTru(); //Pablo 280809
				$passtru->SetValue($delete);

				$this->myForm-> AddFormElement($passtru);
				
				$result->MoveNext( );
			}
					
			$value = "<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"adminmenu.php\"><--Volver al Menú Seguridad</a>";
			$passtru = new PassTru($value);
			$passtru->SetColSpan( $this->myForm-> GetNumberOfColumns() );
			$passtru->SetClass("");
			$passtru->SetCellAttributes(array('align'=>'left'));

			$this->myForm->	AddFormElement	($passtru);

			$this->SendTrailer( );
		}
	}
		
	/**
	* Method to send the form. The form is displayed within the method with echo.
	* @private
	* @returns void
	*/
	function SendActivityForm($FormElements,$mode)
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
			$actionMode = ' Modificando ';
		elseif ($mode == 'delete')
			$actionMode = ' Borrando ';

		$myForm-> SetFormHeader($actionMode."Actividad");
		
		$myForm->	AddFormElementToNewLine	(new Label("lb1","Descripción :"));
		$myForm-> AddFormElement (new TextField("description",$FormElements["description"],50,50));
		
		switch ( TRUE )
		{
			case "EDIT" == strtoupper($mode):

// 				if ($this->mySecurity-> isAllowedTo('Modify Activity'))
				if ($this->mySecurity-> isAllowedTo(16))
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
			
// 				if ($this->mySecurity-> isAllowedTo('Delete Activity'))
				if ($this->mySecurity-> isAllowedTo(15))
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

// 				if ($this->mySecurity-> isAllowedTo('Add Activity'))
				if ($this->mySecurity-> isAllowedTo(14))
				{
					$buttons = new ObjectArray("buttons");
					$buttons->AddObject(new SubmitButton("B_add_submit","Agregar una nueva Actividad"));
					$buttons->AddObject(new SubmitButton("B_clear","Limpiar"));
					$buttons->SetColSpan( $myForm-> GetNumberOfColumns() );
					$buttons->SetCellAttributes(array("align"=>"middle"));

					$myForm-> AddFormElement	($buttons);
				}
		}
		
		$myForm-> SetErrorMessage($this->GetErrorMessage());

		return $myForm->GetFormInTable( );
	}
	/**
	* Method to check the form. Sets the error message and which field is wrong.
	* @public
	* @returns bool
	*/
	function ErrCheckActivityForm($FormElements,$activityId)
	{
		$this->mIsError = 0;
		
		if (!$FormElements["description"])
		{
			$this->mErrorMessage = "Por favor, ingrese una descripción.";
			$this->mFormErrors["groupname"]=1;
			$this->mIsError = 1;
		}

		# check if the activity name is in the database

		$activityid = $this-> GetActivityIdByName($FormElements['description']);
		
		if ($activityid)
		{
			if ($activityid != $activityId)
			{
				$this->SetErrorMessage("Descripción ya existe. Intente con otra.");
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

		$this->myForm-> SetNumberOfColumns( 4 );
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
		$this->myForm-> SetFormHeader("Lista de Actividades");
		
		$mylabel = new Label($name="lb1",$value="ID de Actividad");
		$mylabel-> SetClass("ColumnTD");

		$this->myForm-> AddFormElementToNewLine($mylabel);

		$mylabel = new Label($name="lb1",$value="Descripcion"); //Pablo 280809
		$mylabel->SetValue("Descripción");
		$this->myForm-> AddFormElement($mylabel);

		$mylabel = new Label($name="lb1",$value="Descripcion"); //Pablo 280809
		$mylabel->SetValue("Editar");
		$this->myForm-> AddFormElement($mylabel);

		$mylabel = new Label($name="lb1",$value="Descripcion"); //Pablo 280809
		$mylabel->SetValue("Borrar");
		$this->myForm-> AddFormElement($mylabel);
	}
	
	/**
	* Method to send the Html in a table.This method is called from the ListActivity
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