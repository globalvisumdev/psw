<?php

include_once "Table.class.php";
include_once "FormElements.class.php";
include_once "FormVerifications.php";

/**
* A Class to be used in Form.class.php. Even though you can use it stand alone, its better
* to be used thru Form.class.php. This way Form can contain multiple instances of this
* classes objects.
* Requires Table.class.php, FormElements.class.php, FormVerifications.php
* @author Bulent Tezcan
*/

class ControlGroup
{
	var $mFormObjects;
	var $mFormObjectNames;
	var $mName;
	var $mNumberOfColumns;
	var $mHiddenObjects;
	var $mFormErrorMessage;
	
	/**
	* Constructor of the class ControlGroup.
	* @public
	*/
	function ControlGroup($name="myform")
	{
		$this->mFormObjects			= array( );
		$this->mName						= $name;
		$this->mFormObjects			= array();
		$this->mFormObjectNames = array();
		$this->mHiddenObjects		= array();
		$this->mAlign						= "";
		$this->mValign					= "";
		$this->mCSS							= "";
		$this->mNumberOfColumns = 2;
		$this->mFillEmptyCells	= true;
		$this->mDefaultClassForAllCells = "";
		$this->mFormHeader			= "";
	}
	
	/**
	* Method to add a new object like a textbox or maybe a radio button.
	* @public
	* @returns void
	*/
	function Add($object)
	{
		if ($object->IsObjectAnArray( ))
		{
			$getObjects = $object->GetObjects( );
			$newArray = array( );
			foreach ($getObjects as $objectArray)
			{
				$objectArray->SetName("form_" .$this->mName ."["
				.$objectArray->GetName() ."]");
				array_push($newArray, $objectArray);
			}
			$object->SetObjects($newArray);
		}
		else
		$object->SetName("form_".$this->mName."[".$object->GetName()."]");
		
		array_push($this->mFormObjectNames, "$this->mName.$object->mName");
		array_push($this->mFormObjects, $object);
	}
	
	/**
	* Method to add a new object to a new line like a textbox or maybe a radio button.
	* @public
	* @returns void
	*/
	function AddToNewLine($object)
	{
		$object->SetNewLine( );
		$this->Add($object);
	}
	
	/**
	* Method to set the error message to be returned with the form.
	* @public
	* @returns void
	*/
	function SetErrorMessage($message)
	{
		$this->mFormErrorMessage = $message;
	}
	
	/**
	* Method to set the alignment of the table cells.
	* @public
	* @returns void
	*/
	function SetAlign($stringValue)
	{
		$this->mAlign = $stringValue;
	}
	
	/**
	* Method to set the vertical alignment of the table cells.
	* @public
	* @returns void
	*/
	function SetValign($stringValue)
	{
		$this->mValign = $stringValue;
	}
	
	/**
	* Method to set the class (CSS) of the table cells.
	* @public
	* @returns void
	*/
	function SetCellClass($stringValue)
	{
		$this->mCellClass = $stringValue;
	}

	/**
	* Method to set the class (CSS) of the table cells.
	* @public
	* @returns void
	*/
	function SetEmptyCells($trueFalse)
	{
		if ($trueFalse)
			$this->mFillEmptyCells = true;
		else
			$this->mFillEmptyCells = false;
	}
	
	/**
	* Method to set the TR mouseover color.
	* @public
	* @returns void
	*/
	function SetTRMouseOverColor( $overcolor, $outcolor, $startingRow )
	{
		$this->mTableTRMouseoverColor = $overcolor;
		$this->mTableTRMouseoutColor = $outcolor;
		$this->mTableTRMouseoverColorStartingRow = $startingRow;
	}

	/**
	* Method to set the class (CSS) for the table and Form elements.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}

	/**
	* Method to set the table attributes.
	* @public
	* @returns void
	*/
	function SetTableAttributes($tableAttributes)
	{
		# this has to be an array
		if (is_array($tableAttributes))
			$this->mTableAttributes = $tableAttributes;
	}
	
	/**
	* Method to set the class for all cells in the table.
	* @public
	* @returns void
	*/
	function SetDefaultClassForAllCells($class)
	{
		$this->mDefaultClassForAllCells = $class;
	}
	
	/**
	* Method to set the header for the form.
	* @public
	* @returns void
	*/
	function SetFormHeader($header)
	{
		$this->mFormHeader = $header;
	}

	/**
	* Method to get the form in plain html format.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		if ( !is_array($this->mFormObjects) )
		return "";
		
		$bFirstTime = TRUE;
		$nColumns = 0;
		
		foreach ($this->mFormObjects as $object)
		{
			if	( $object->IsNewLine( ) )
			{
				$nColumns = 0;
			}
			
			if ( !$bFirstTime )
			if ( 0==$nColumns )
			{
				$string .= "<br>\n";
			}
			else
			{
				$string .= "&nbsp;\n";
			}
			
			if ($object->IsHidden( ))
			array_push($this->mHiddenObjects, $object);
			else
			{
				$string .= $object->GetString( );
				$nColumns	= ($nColumns + 1) %$this->mNumberOfColumns;
			}
			$bFirstTime = FALSE;
		}
		
		# this loop is to print the hidden values inside the table
		foreach ($this->mHiddenObjects as $object)
		{
			$string .= $object->GetString( );
		}
		
		return $string;
	}
	
	/**
	* Method to get the form in plain html inside a table.(Formatted)
	* @public
	* @returns string
	*/
	function GetStringInTable( )
	{
		$table = new Table( );
		
		$table->AddRow();

		#if ($this->mCellClass)
		#$table->AddRowAttribute ( "class", (array)$this->mCellClass );
		
		#if ($this->mAlign)
		#$table->AddColAttribute("align",$this->mAlign );
		
		#if ($this->mValign)
		#$table->AddColAttribute("valign",$this->mValign );
		
		if ($this->mCSS)
		{
			$table->SetTableClass($this->mCSS."FormTABLE");
			$FormHeaderFont = $this->mCSS."FormHeaderFont";
		}


		if (is_array($this->mTableAttributes))
			$table->SetTableAttributes($this->mTableAttributes);

		if ($this->mDefaultClassForAllCells != "")
			$table->SetDefaultClassForAllCells ( $this->mDefaultClassForAllCells );
		

		if ($this->mTableTRMouseoverColor != "" and
				$this->mTableTRMouseoutColor != "" and
				$this->mTableTRMouseoverColorStartingRow != "")
		{
			$table-> 
			SetTRMouseOverColor($this->mTableTRMouseoverColor,
													$this->mTableTRMouseoutColor,
													$this->mTableTRMouseoverColorStartingRow);
		}

		if ( !is_array($this->mFormObjects) )
		return "";
		

		if ($this->mFormHeader)
		{
			$table->SetCellColSpan( $table->GetCurrentRow( ), $Col=1,
															$this->mNumberOfColumns );
			$table->SetCellAttributes( $table->GetCurrentRow( ), $Col=1, array("class"=>$FormHeaderFont,"width"=>"100%","align"=>"middle"));
			$table->SetCellContent( $table->GetCurrentRow( ), $Col=1, $this->mFormHeader );
			
			$table->AddRow();
		}

		# the following code is for error messages to appear at the
		# beginning of the table.
		#
		if ($this->mFormErrorMessage != "")
		{
			$table->SetCellColSpan( $table->GetCurrentRow( ), $Col=1,
															$this->mNumberOfColumns );
			$table->SetCellAttribute( $table->GetCurrentRow( ), $Col=1, "width", "100%" );
			$table->SetCellClass($table->GetCurrentRow( ), $Col=1, $this->mCSS."Error");
			$table->SetCellContent( $table->GetCurrentRow( ), $Col=1, $this->mFormErrorMessage );
		}
		else
			$doNotAddFirstRow = 1;
		

		$nColumns = 0; # the purpose of the -1 is like a switch.
		$col = 1;
		$dummyObject = new Dummy();
		$dummyObject->SetCSS($this->mCSS);
		
		foreach ($this->mFormObjects as $object)
		{
			
			if	( $object->IsNewLine( ) )
			{				
				if ($nColumns and $this->mFillEmptyCells)
					for ($i = $col; $i < $this->mNumberOfColumns+1-$colspan; $i ++)
					{
						if ($dummyObject->GetCSS( ) == "")
						{
							$dummyObject->SetCSS($this->mCSS);
							$CSS = $dummyObject->GetCSS( );
						}
					
						# The class takes presidence of the general CSS
						#
						if ($dummyObject->GetClass( ) != "")
							$CSS = $dummyObject->GetClass( );


						$content = $dummyObject->GetString( );
						$table->SetCellContent( $table->GetCurrentRow (), $i, $content );
						$table->SetCellClass( $table->GetCurrentRow (), $i, $CSS );
					}
				$nColumns = 0;
			}
			

			if ( 0 == $nColumns and $doNotAddFirstRow == 0)
			{				
				$table->AddRow();
				$col = 1;
				$colspan = 0;
			}
			elseif (1 == $doNotAddFirstRow)	
				$doNotAddFirstRow = 0;					

			if ($object->IsHidden( ))
				array_push($this->mHiddenObjects, $object);
			else
			{				
				if ($this->mCSS != "")
				{
					# if the object has a Style sheet, than we will use it's CSS,
					# otherwise we will set it to the common CSS for that form.
					if ($object->GetCSS( ) == "")
					{
						$object->SetCSS($this->mCSS);
						$CSS = $object->GetCSS( );
					}
					
					# The class takes presidence of the general CSS
					#
					if ($object->GetClass( ) != "")
						$CSS = $object->GetClass( );
				}
				else
					$CSS = "";


				$content = $object->GetString( );

				if ($content == '')
					$content = "&nbsp;";

				$table->SetCellClass( $table->GetCurrentRow (), $col, $CSS );
				$table->SetCellContent( $table->GetCurrentRow (), $col, $content );
				
				$arrayCellAttr = $object->GetCellAttributes();
				
				if ($object->GetColSpan( ))
					$arrayCellAttr['colspan'] = $object->GetColSpan( );

				if (is_array($arrayCellAttr))
					$table->SetCellAttributes( $table->GetCurrentRow (), $col, $arrayCellAttr );
				
				if ($object-> GetColspan( ))
					$colspan += $object-> GetColspan( ) - 1;

				$col++;

				$nColumns	= ($nColumns + 1 + $colspan) % $this->mNumberOfColumns;
			}
		}
	
		if ($nColumns and $this->mFillEmptyCells)
		{
			for ($i = $col; $i < $this->mNumberOfColumns+1-$colspan; $i ++)
			{
				if ($dummyObject->GetClass( ) != "")
					$CSS = $dummyObject->GetClass( );
				if ($dummyObject->GetCSS( ) == "")
				{
					$dummyObject->SetCSS($this->mCSS);
					$CSS = $dummyObject->GetCSS( );
				}
				$content = $dummyObject->GetString( );
				$table->SetCellContent( $table->GetCurrentRow (), $i, $content );
				$table->SetCellClass( $table->GetCurrentRow (), $i, $CSS );
			}
		}

		$table->AddPassTru("\n\n<!-- Start of Hidden Values -->\n");
		
		# this loop is to print the hidden values inside the table
		foreach ($this->mHiddenObjects as $object)
		{
			$table->AddPassTru( $object->GetString( ) );
		}
		
		return $table->GetTable( );
	}
	
}	# end of class ControlGroup
?>