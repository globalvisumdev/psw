<?php

include_once "ControlGroup.class.php";

/**
* A Class that creates an Html Form and spits out the results in a formatted/non-formatted
* fashion. Formated form will be inside a table.
* @author Bulent Tezcan
*/

class Form
{
	
	var $mFormName;
	var $mAction;
	var $mMethod;
	var $mNumberOfColumns;
	var $mCSS;
	var $mCellClass;
	var $mCellSpacing;
	var $mCellPadding;
	var $mBorder;
	var $mBgColor;
	var $mTableClass;
	var $mAlign;
	var $mValign;
	var $mTableWidth;
	var $mHeight;
	var $mControlGroup;
	var $mFormErrorMessage;
	var $mIsFormTagRequired;
	
	
	/**
	* Constructor of the class Form.
	* @public
	*/
	function Form( $formName="myform")
	{
		$this->mFormName = $formName;
		$this->mAction = "";
		$this->mMethod = "post";
		$this->mNumberOfColumns	=	2;
		$this->mCellSpacing			= 0;
		$this->mCellPadding			= 0;
		$this->mBorder					= 0;
		$this->mBgColor					= "";
		$this->mCSS							= "";
		$this->mAlign						= "";
		$this->mValign					= "";
		$this->mTableWidth			= "";
		$this->mTableHeight			= "";
		$this->mIsFormTagRequired=1;
		$this->mExtraFormTags		= "";
		$this->mFillEmptyCells	= true;
		$this->mTableTRMouseoverColor = "";
		$this->mTableTRMouseoutColor = "";
		$this->mTableTRMouseoverColorStartingRow = "";
	}
	
	
	/**
	* Method to set the Form's URL.
	* @public
	* @returns void
	*/
	function SetAction($action)
	{
		$this->mAction = $action;
	}
	
	/**
	* Method to set the Form's post method, ie(get,post).
	* @public
	* @returns void
	*/
	function SetMethod($method)
	{
		$this->mMethod = $method;
	}	
	/**
	* Method to set the number of columns to be displayed.
	* @public
	* @returns void
	*/
	function SetNumberOfColumns($columnNumber)
	{
		$this->mNumberOfColumns = $columnNumber;
	}
	/**
	* Method to get the number of columns to be displayed.
	* @public
	* @returns void
	*/
	function GetNumberOfColumns( )
	{
		return $this->mNumberOfColumns;
	}
	/**
	* Method to set the cellspacing.
	* @public
	* @returns void
	*/
	function SetCellSpacing($cellSpace)
	{
		$this->mCellSpacing = $cellSpace;
	}
	/**
	* Method to set the cellpadding.
	* @public
	* @returns void
	*/
	function SetCellPadding($cellPadding)
	{
		$this->mCellPadding = $cellPadding;
	}
	/**
	* Method to set the border size.
	* @public
	* @returns void
	*/
	function SetBorder($size)
	{
		$this->mBorder = $size;
	}
	/**
	* Method to set the horizontal allignment.
	* @public
	* @returns void
	*/
	function SetAlign($allignment)
	{
		$this->mAlign = $allignment;
	}
	/**
	* Method to set the vertical allignment.
	* @public
	* @returns void
	*/
	function SetValign($allignment)
	{
		$this->mValign = $allignment;
	}
	/**
	* Method to set the table width.
	* @public
	* @returns void
	*/
	function SetTableWidth($width)
	{
		$this->mTableWidth = $width;
	}
	/**
	* Method to set the table height.
	* @public
	* @returns void
	*/
	function SetTableHeight($height)
	{
		$this->mTableHeight = $height;
	}
	/**
	* Method to set the class for CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to set the error messages if needed to be used in the form.
	* @public
	* @returns void
	*/
	function SetErrorMessage($message)
	{
		$this->mFormErrorMessage = $message;
	}
	
	/**
	* Method to set if the form tag is required or not. Set it to FALSE if you don't want
	* any form tags to be displayed.
	* @public
	* @returns void
	*/
	function SetFormTagRequired( $answer=TRUE )
	{
		$this->mIsFormTagRequired = $answer;
	}
	
	/**
	* Method to set any extra form tags that would be required.
	* @public
	* @returns void
	*/
	function SetExtraFormTags( $stringValue )
	{
		$this->mExtraFormTags = $stringValue;
	}
	
	/**
	* Method to get the Form's URL.
	* @public
	* @returns string
	*/
	function GetAction( )
	{
		return $this->mAction;
	}
	
	/**
	* Method to get the Form's post method, ie(get,post).
	* @public
	* @returns string
	*/
	function GetMethod( )
	{
		return $this->mMethod;
	}
	
	/**
	* Method to get the Form's name.
	* @public
	* @returns string
	*/
	function GetFormName( )
	{
		return $this->mFormName;
	}
	
	/**
	* Method to get the Form's error message.
	* @public
	* @returns string
	*/
	function GetErrorMessage( )
	{
		return $this->mFormErrorMessage;
	}

	/**
	* Method to set the class (CSS) of the table cells.
	* @public
	* @returns void
	*/
	function SetEmptyCells($trueFalse)
	{
		if ($trueFalse or $trueFalse == "true")
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
	* Method to set the header for the form.
	* @public
	* @returns void
	*/
	function SetFormHeader($header)
	{
		$this->mFormHeader = $header;
	}

	/**
	* Method to add a ControlGroup's Object into this Form. Like a TextField or a SubmitButton
	* @public
	* @returns void
	*/
	function AddFormElement( $object )
	{
		if ( isset($this->mControlGroup) )
		$this->mControlGroup->Add($object);
		else
		{
			$this->mControlGroup = new ControlGroup( $this->mFormName );
			$this->mControlGroup->Add($object);
		}
	}
	
	/**
	* Method to add a ControlGroup's Object to a new line or a new Table Row into this Form. Like a * TextField or a SubmitButton
	* @public
	* @returns void
	*/
	function AddFormElementToNewLine( $object )
	{
		if ( isset($this->mControlGroup) )
		$this->mControlGroup->AddToNewLine($object);
		else
		{
			$this->mControlGroup = new ControlGroup( $this->mFormName );
			$this->mControlGroup->AddToNewLine($object);
		}
	}
	
	/**
	* Method to Get the Html format of the form in a formatted way, which is a table.
	* @public
	* @returns string
	*/
	function GetFormInTable( )
	{
		
		if ($this->mIsFormTagRequired)
		{
			$html = "\n\n<!-- START FORM ($this->mFormName) -->";
			
			$html .= "\n\n<form method=\"$this->mMethod\" "
			."name=\"$this->mFormName\" ";
			
			if ($this->mAction != '')
				$html .= " action=\"$this->mAction\" ";
			
			if ($this->mExtraFormTags != '')
				$html .= "$this->mExtraFormTags";
			
			$html .= ">\n\n";
		}
		else
		$html = "\n\n<!-- START FORM WITHOUT FORM TAG ($this->mFormName) --><br>";
		
		$tableProperties = array( );	

		if ($this->mTableWidth != "")
			$tableProperties["width"]				=	$this->mTableWidth;
		if ($this->mTableHeight != "")
			$tableProperties["height"]			= $this->mTableHeight;
		if ($this->mCellSpacing != "")
			$tableProperties["cellspacing"]	= $this->mCellSpacing;
		if ($this->mCellPadding != "")
			$tableProperties["cellpadding"]	=	$this->mCellPadding;
		if ($this->mAlign != "")
			$tableProperties["align"]				=	$this->mAlign;
		if ($this->mValign != "")
			$tableProperties["valign"]			=	$this->mValign;
		if ($this->mBorder != "")
			$tableProperties["border"]			=	$this->mBorder;
		if ($this->mBgColor != "")
			$tableProperties["bgcolor"]			=	$this->mBgColor;

		if ($this->mTableTRMouseoverColor != "" and
				$this->mTableTRMouseoutColor != "" and
				$this->mTableTRMouseoverColorStartingRow != "")
		{
			$this->mControlGroup-> 
			SetTRMouseOverColor($this->mTableTRMouseoverColor,
													$this->mTableTRMouseoutColor,
													$this->mTableTRMouseoverColorStartingRow);
		}


		$this->mControlGroup->SetErrorMessage($this->mFormErrorMessage);
		$this->mControlGroup->SetCSS($this->mCSS);
		$this->mControlGroup->SetAlign($this->mAlign);
		$this->mControlGroup->SetValign($this->mValign);
		$this->mControlGroup->SetEmptyCells($this->mFillEmptyCells);
		$this->mControlGroup->SetTableAttributes($tableProperties);
		$this->mControlGroup->mNumberOfColumns = $this->mNumberOfColumns;
		$this->mControlGroup->mFormHeader = $this->mFormHeader;

		$html .= $this->mControlGroup->GetStringInTable( );
		
		if ($this->mIsFormTagRequired)
		{
			$html .= "\n\n</form>\n";
			$html .= "<!-- END FORM ($this->mFormName) -->\n\n";
		}
		else
		$html .="<!-- END FORM WITHOUT FORM TAG ($this->mFormName) -->\n\n<br>";
		
		return $html;
	}
	
	/**
	* Method to Get the Html format of the form in a non-formatted way.
	* @public
	* @returns string
	*/
	function GetForm( )
	{
		if ($this->mIsFormTagRequired)
		{
			$html = "\n\n<!-- START FORM ($this->mFormName) -->";
			
			$html .= "\n\n<form method=\"$this->mMethod\" "
			."name=\"$this->mFormName\" "
			." action=\"$this->mAction\">\n\n";
		}
		else
		$html = "\n\n<!-- START FORM WITHOUT FORM TAG ($this->mFormName) -->";
		
		$html .= $this->mControlGroup->GetString( );
		
		if ($this->mIsFormTagRequired)
		{
			$html .= "\n\n</form>\n";
			$html .= "<!-- END FORM ($this->mFormName) -->\n\n";
		}
		else
		$html .="<!-- END FORM WITHOUT FORM TAG ($this->mFormName) -->\n\n";
		
		return $html;
	}
	
	
	
}	# end of class From

?>