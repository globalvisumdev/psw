<?php
###########################################################################
#
#	This file contains all the major form elements as classes.
#
#	Currently we have :
# -----------------------------------------------------------------
#	TextField($name,$value,$size=15,$maxLength=15,$displayOnly=false)
#
#	TextArea($name,$value,$rows,$cols,$displayOnly=false)
#
#	Label($name,$value)
#
#	CheckBox($name,$value,$IsChecked,$displayOnly=false,
#	$displayText="")
#
#	RadioButton($name,$vertical=FALSE,$displayOnly=false)
#
#	SelectBox(	$name,$value,$selected="",$default="-Select-",
#							$displayOnly=false,$size=0,$multiple=FALSE,$extra="" )
#
# GetOptions(	$name,$table,$field,$key,$extraSql="",$selected=0,
#							$default="-Select-",$displayOnly=false,
#							$size=0,$multiple=FALSE,$extra="",$concat="")
#
#	Hidden(	$name,$value,$extra="" )
#
# PassTru($value="&nbsp;")
#
# Dummy ( )
#
#	Password($name,$value="",$size=15,$maxLength=15,$extra="")
#
#	InputFile($name,$size=25,$extra="")
#
#	SubmitButton($name="Submit",$value="Submit",$extra="")
#
# ResetButton($name="Reset",$value="Reset",$extra="")
#
# Button($name,$value,$type="button",$extra="",$image="")
#
# ImageButton(	$name,$source,$align="middle",$border="0",$alt="", $extra="")
#
#	ObjectArray( $name )
#
#
###########################################################################

require_once "MyDatabase.class.php";


/**
* A class to be used in class ControlGroup it will create a text box
* @author Bulent Tezcan
*/
#==============================================
class TextField
#==============================================
{
	var $mName;
	var $mSize;
	var $mMaxLength;
	var $mValue;
	var $mClass;
	var $mCSS;
	var $mDisplayOnly;
	var $mNewLine;
	/**
	* Constructor of the class TextField.
	* @public
	*/
	function TextField($name,$value,$size=15,$maxLength=15,$displayOnly=false)
	{
		$this->mName			= $name;
		$this->mSize			= $size;
		$this->mMaxLength = $maxLength;
		$this->mValue			= $value;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mDisplayOnly=$displayOnly;
		$this->mNewLine		= FALSE;
		$this->mCellAttr	= "";
		$this->mId				= "";
		$this->mIdTag			= "";
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the text field.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the text field.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to get the id.
	* @public
	* @returns string
	*/
	function GetId( )
	{
		return $this->mId;
	}
	/**
	* Method to set the id.
	* @public
	* @returns void
	*/
	function SetId($id)
	{
		$this->mId = $id;
		$this->mIdTag = " id=\"$id\" ";
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		if ($this->mDisplayOnly)
		{
			$html = $this->mValue;
		}
		else
		{
			$html = "\n\t\t<input ";

			if ($this->mClass != "")
				$html .= " class=\"".$this->GetClass( )."\" ";
			elseif ($this->mCSS != "")
				$html .= " class=\"".$this->mCSS."Input\" ";

			$html .= $this->mIdTag ." type=\"text\" name=\"$this->mName\" "
			."size=\"$this->mSize\" maxlength=\"$this->mMaxLength\" "
			."value=\"$this->mValue\" ";

			$html .= ">";
		}

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}
	/**
	* Method to validate the text fields.
	* @public
	* @returns bool
	*/
	function Validate( $fieldType, $min=0, $max=0, $intType="Int" )
	{
		$fieldType = ucwords($fieldType);
		$intType	 = ucwords($intType);

		switch ($fieldType)
		{
			case "Numeric":
			case "Num":
			case "N":
			switch ($intType)
			{
				case "Integer":
				case "Int":
				case "I":
				if (!is_integer($this->mValue))
				return FALSE;
				$numericValue = intval($this->mValue);
				case "Double":
				case "Dbl":
				case "D":
				if (!is_double($this->mValue))
				return FALSE;
				$numericValue = doubleval($this->mValue);
				case "Float":
				case "Flt":
				case "F":
				if (!is_float($this->mValue))
				return FALSE;
				$numericValue = (float)$this->mValue;
				case "Long":
				case "Lng":
				case "L":
				if (!is_long($this->mValue))
				return FALSE;
				$numericValue = doubleval($this->mValue);
				case "Real":
				case "R":
				if (!is_real($this->mValue))
				return FALSE;
				$numericValue = (real)$this->mValue;
			}

			if ($numericValue<$min || $numericValue>$max)
			return FALSE;
			break;

			case "Text":
			case "Txt":
			case "T":
			if (!is_string($this->mValue))
			return FALSE;
			if ( strlen($this->mValue)<$min || strlen($this->mValue)>$max )
			return FALSE;
			break;

			case "Boolean":
			case "Bool":
			case "B":
			if (!is_bool($this->mValue))
			return FALSE;
			break;

			default:
			return FALSE;
		}

		return TRUE;
	}
}	# end of class TextField
/**
* A class to be used in class ControlGroup, it will create a textarea.
* @author Bulent Tezcan
*/
#==============================================
class TextArea
#==============================================
{
	var $mName;
	var $mRows;
	var $mCols;
	var $mClass;
	var $mCSS;
	var $mDisplayOnly;
	var $mNewLine;
	/**
	* Constructor of the class TextArea.
	* @public
	*/
	function TextArea($name,$value,$rows,$cols,$displayOnly=false)
	{
		$this->mName			= $name;
		$this->mRows			= $rows;
		$this->mCols			= $cols;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mValue			= $value;
		$this->mDisplayOnly=$displayOnly;
		$this->mNewLine		= FALSE;
		$this->mId				= "";
		$this->mIdTag			= "";
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the text area.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the text area.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to get the id.
	* @public
	* @returns string
	*/
	function GetId( )
	{
		return $this->mId;
	}


	/**
	* Method to set the id.
	* @public
	* @returns void
	*/
	function SetId($id)
	{
		$this->mId = $id;
		$this->mIdTag = " id=\"$id\" ";
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		if ($this->mDisplayOnly)
		$html = "<strong>$this->mValue</strong>";
		else
		{
			$html = "\n\t\t<textarea ";

			if ($this->mClass != "")
				$html .= " class=\"".$this->GetClass( )."\" ";

			elseif ($this->mCSS != "")
				$html .= " class=\"".$this->mCSS."Textarea\" ";

			$html .= $this->mIdTag ." name=\"$this->mName\" "
			."rows=\"$this->mRows\" cols=\"$this->mCols\" wrap>";

			$html .= "$this->mValue</textarea>";
		}
		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class TextArea
/**
* A class to be used in class ControlGroup it will create a label
* @author Bulent Tezcan
*/
#==============================================
class Label
#==============================================
{
	var $mName;
	var $mValue;
	var $mClass;
	var $mNewLine;

	/**
	* Constructor of the class Label.
	* @public
	*/
	function Label($name,$value)
	{
		$this->mName			= $name;
		$this->mValue			= $value;
		$this->mClass			= "";
		$this->mCellAttr	= "";
		$this->mCSS				= "";
		$this->mNewLine		= FALSE;
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the label.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the label.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."FieldCaptionTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to set the value of the label.
	* @public
	* @returns void
	*/
	function SetValue($value)
	{
		$this->mValue = $value;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		return $this->mValue;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class Label

/**
* A class to be used in class ControlGroup it will create a check box
* @author Bulent Tezcan
*/
#==============================================
class CheckBox
#==============================================
{
	var $mName;
	var $mValue;
	var $mIsChecked;
	var $mClass;
	var $mCSS;
	var $mDisplayOnly;
	var $mNewLine;
	var $mCellAttr;
	/**
	* Constructor of the class CheckBox.
	* @public
	*/
	function CheckBox($name,$value,$IsChecked,$displayOnly=false,
	$displayText="")
	{
		$this->mName			= $name;
		$this->mValue			= $value;
		$this->mIsChecked = $IsChecked;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mDisplayOnly=$displayOnly;
		$this->mNewLine		= FALSE;
		$this->mCellAttr	= "";
		$this->mId				= "";
		$this->mIdTag			= "";
		$this->mDisplayText = $displayText;
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the check box.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the check box.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to get the id.
	* @public
	* @returns string
	*/
	function GetId( )
	{
		return $this->mId;
	}
	/**
	* Method to set the id.
	* @public
	* @returns void
	*/
	function SetId($id)
	{
		$this->mId = $id;
		$this->mIdTag = " id=\"$id\" ";
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		if ($this->mDisplayOnly)
		{
			if ($this->mIsChecked)
			$html = "<img $this->mIdTag src=\"" . "/images/"
			. "checked.gif\" width=\"13\" height=\"13\" alt=\"[x]\"> ";
			else
			$html = "<img $this->mIdTag src=\"" . "/images/"
			. "unchecked.gif\" width=\"13\" height=\"13\" alt=\"[ ]\"> ";

			$html .= $this->mDisplayText;
		}
		else
		{
			$html = "\n\t\t<input ";

			if ($this->mClass != "")
				$html .= " class=\"".$this->GetClass( )."\" ";
			elseif ($this->mCSS != "")
				$html .= " class=\"".$this->mCSS."Input\" ";

			$html .= $this->mIdTag ." type=\"checkbox\" name=\"$this->mName\" "
			."value=\"$this->mValue\" ";

			if ( $this->mIsChecked )
				$html .= " checked";

			$html	.= "> ";

			$html .= $this->mDisplayText;
		}

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class CheckBox
/**
* A class to be used in class ControlGroup it will create a radio button
* @author Bulent Tezcan
*/
#==============================================
class RadioButton
#==============================================
{
	/**
	* Constructor of the class RadioButton.
	* @public
	*/
	function
	RadioButton($name,$vertical=FALSE,$displayOnly=false)
	{
		$this->mName			= $name;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mNewLine		= FALSE;
		$this->mGroup			= array( );
		$this->mVertical	= $vertical;
		$this->mDisplayOnly=$displayOnly;
		$this->mCellAttr	= "";
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the radio button.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the radio button.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to add more radiobutton objects.
	* @public
	* @returns void
	*/
	function AddOption($label,$value,$IsChecked,$extra="")
	{
		$newRadio	= new RadioButton($this->mName,$this->mClass);
		$newRadio->mNewLine		= FALSE;
		$newRadio->mLabel			= $label;
		$newRadio->mValue			= $value;
		$newRadio->mIsChecked	= $IsChecked;
		$newRadio->mExtra			= $extra;
		array_push($this->mGroup, $newRadio);
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$html = "";

		if (count($this->mGroup))
		{
			foreach ($this->mGroup as $radioButton)
			{
				if ($this->mDisplayOnly)
				{
					if ($radioButton->mIsChecked)
					$html .= "\n\t\t<img src=\"" . "/images/"
					. "radio1.gif\" width=\"13\" height=\"13\" alt=\"[.]\"> $radioButton->mLabel ";
					else
					$html .= "\n\t\t<img src=\"" . "/images/"
					. "radio0.gif\" width=\"13\" height=\"13\" alt=\"[ ]\"> $radioButton->mLabel ";
				}
				else
				{
					$html .= "\n\t\t<input type=\"radio\" ";

					if ($this->mClass != "")
						$html .= " class=\"".$this->GetClass( )."\" ";
					elseif ($this->mCSS != "")
						$html .= " class=\"".$this->mCSS."Input\" ";

					$html .=" name=\"$this->mName\" "
								." value=\"$radioButton->mValue\" ";

					if ( $radioButton->mIsChecked )
						$html .= " checked";

					if ( $radioButton->mExtra )
						$html .= " ".$radioButton->mExtra;

					$html	.= "> $radioButton->mLabel";
				}

				if ( $this->mVertical )
				$html .= "<br>";
			}
		}

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class RadioButton
/**
* A class to be used in class ControlGroup it will create a select box
* @author Bulent Tezcan
*/
#==============================================
class SelectBox
#==============================================
{

	/**
	* Constructor of the class SelectBox.
	* @public
	*/
	function SelectBox(	$name,$value,$selected="",$default="-Select-",
	$displayOnly=false,$size=0,$multiple=FALSE,
	$extra="")
	{
		$this->mName			= $name;
		$this->mValue			= $value;
		$this->mSelected	= $selected;
		$this->mDefault		= $default;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mDisplayOnly=$displayOnly;
		$this->mExtra			= $extra;
		$this->mNewLine		= FALSE;
		$this->mSize			= $size;
		$this->mId				= "";
		$this->mIdTag			= "";
		$this->mCellAttr	= "";
		$this->mOptionColorIndex = 2;
		$this->mReturnValueAsText = false;
		$this->mColSpan		= 0;

		if($multiple)
		{
			$this->mMultiple			= "multiple";
			$this->mMultiBrackets	= "[]";
		}
		else
		{
			$this->mMultiple			= "";
			$this->mMultiBrackets = "";
		}
	}
	/**
	* Method to set the name of the select box.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the select box.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the reversing color in the select box.
	* @public
	* @returns void
	*/
	function SetZebraColor($color)
	{
		if (strlen($color) == 7 )
			$this->mZebraColor = $color;
		elseif (strlen($color) == 6 )
			$this->mZebraColor = "#".$color;
		else
			$this->mZebraColor = "";
	}
	/**
	* Method to set the reversing color index for the select box.
	* @public
	* @returns void
	*/
	function SetZebraColorEveryNtimes($number)
	{
		if (is_numeric($number))
			$this-> mOptionColorIndex = $number;
		else
			$this-> mOptionColorIndex = 2;
	}
	/**
	* Method to get the id.
	* @public
	* @returns string
	*/
	function GetId( )
	{
		return $this->mId;
	}
	/**
	* Method to set the id.
	* @public
	* @returns void
	*/
	function SetId($id)
	{
		$this->mId = $id;
		$this->mIdTag = " id=\"$id\" ";
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to set the option if you want the text or index to be the value.
	* @public
	* @returns void
	*/
	function SetReturnValueAsText( )
	{
		$this->mReturnValueAsText = true;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$colorIndex = 0;

		$html = "\n\t\t<select $this->mIdTag ";

		if ($this->mClass != "")
			$html .= " class=\"".$this->GetClass( )."\" ";
		elseif ($this->mCSS != "")
			$html .= " class=\"".$this->mCSS."Select\" ";

		if ($this->mSize)
		$html .= "size=\"$this->mSize\"";

		$html .= " $this->mExtra $this->mMultiple   name=\"$this->mName$this->mMultiBrackets\">";

		#if( !$this->mSize )
		#{
			$html .= "\n\t\t\t<option value=\"\" ";
			if ( $this->mZebraColor != "" )
			{
				$colorIndex = ($colorIndex + 1) %$this-> mOptionColorIndex;
				if ($colorIndex)
					$html .= "style=\"background=".$this->mZebraColor."\"";
			}
			$html .= " >$this->mDefault";
		#}

		if ( is_array($this->mValue) )
		{

			foreach ($this->mValue as $value=>$text)
			{
				if ($this->mReturnValueAsText == true)
					$VALUE = $text;
				else
					$VALUE = $value;

				$html .= "\n\t\t\t<option value=\"$VALUE\"";

				if (is_array($this->mSelected))
				{
					foreach ($this->mSelected as $selectedItem)
					{
						if ($selectedItem == $VALUE)
						{
							$html .= " selected ";
							break;
						}
					}
				}
				elseif ("" != $this->mSelected)
				{
					if (0 == strnatcmp($this->mSelected,$VALUE))
					$html .= " selected ";
				}

				if ( $this->mZebraColor != "" )
				{
					$colorIndex = ($colorIndex + 1) %$this-> mOptionColorIndex;
					if ($colorIndex)
						$html .= "style=\"background=".$this->mZebraColor."\"";
				}

				$html .= " >$text";

				if ($this->mDisplayOnly and $this->mSelected !="" and
				$this->mSelected == $VALUE)
				{
					$html = "<strong>$text</strong>";
					return $html;
				}
			}
		}

		$html .= "\n\t\t</select>";

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class SelectBox
/**
* A class to be used in class ControlGroup it will create a select box from
* database.
* @author Bulent Tezcan
*/
#==============================================
class GetOptions
#==============================================
{

	/**
	* Constructor of the class GetOptions.
	* @public
	* si $connection = "db_cli" se llama a la conexnio del cliente gdbcli, no a gdb
	*/
	function GetOptions(	$name,$table,$field,$key,$extraSql="",$selected=0,
	$default="-Select-",$displayOnly=false,
	$size=0,$multiple=FALSE,$extra="",$concat="",$connection="db")
	{
		$this->mName			= $name;
		$this->mSelected	= $selected;
		$this->mDefault		= $default;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mNewLine		= FALSE;
		$this->mTable			= $table;
		$this->mField			= $field;
		$this->mKey				= $key;
		$this->mExtraSql	= $extraSql;

		$this->mDisplayOnly=$displayOnly;
		$this->mSize       = $size;
		$this->mId				= "";
		$this->mIdTag			= "";
		$this->mCellAttr	= "";
		$this->mOptionColorIndex = 2;
		$this->mColSpan		= 0;

		if($multiple)
		{
			$this->mMultiple			= "multiple";
			$this->mMultiBrackets = "[]";
			if (!$size)
				$this->mSize = 4;
		}
		else
		{
			$this->mMultiple			= "";
			$this->mMultiBrackets = "";
		}

		$this->mExtra			= $extra;
		$this->mConcat		= $concat;
		$this->mSQL				= "";
		$this->mSQLdata				= array();
		$this->mconnection		= $connection;
	}
	/**
	* Method to set the connection sql statement.
	* @public
	* @returns void
	*/
	function SetConnection($con)
	{
		$this->mconnection = $con;
	}
	/**
	* Method to set the sql statement.
	* @public
	* @returns void
	*/
	function SetSQL($sql,$datadb=array())
	{
		$this->mSQL = $sql;
		$this->mSQLdata = $datadb;
	}
	/**
	* Method to get the sql statement.
	* @public
	* @returns string
	*/
	function GetSQL( ) //creo que no se usa
	{
		return $this->mSQL;
		return $this->mSQLdata;
	}
	/**
	* Method to set the name of the select box.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the select box.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the reversing color in the select box.
	* @public
	* @returns void
	*/
	function SetZebraColor($color)
	{
		if (strlen($color) == 7 )
			$this->mZebraColor = $color;
		elseif (strlen($color) == 6 )
			$this->mZebraColor = "#".$color;
		else
			$this->mZebraColor = "";
	}
	/**
	* Method to set the reversing color index for the select box.
	* @public
	* @returns void
	*/
	function SetZebraColorEveryNtimes($number)
	{
		if (is_numeric($number))
			$this-> mOptionColorIndex = $number;
		else
			$this-> mOptionColorIndex = 2;
	}
	/**
	* Method to get the id.
	* @public
	* @returns string
	*/
	function GetId( )
	{
		return $this->mId;
	}
	/**
	* Method to set the id.
	* @public
	* @returns void
	*/
	function SetId($id)
	{
		$this->mId = $id;
		$this->mIdTag = " id=\"$id\" ";
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$MyDatabase = new MyDatabase();

		$colorIndex = 0;

		if ($this->mSQL != "")
			$sql = $this->mSQL;
		else
			$sql =	"SELECT $this->mTable.$this->mField "
							.",$this->mTable.$this->mKey "
							."FROM $this->mTable $this->mExtraSql";
							
			try {
			    if ($this->mconnection=="db_cli") {
			        $resultp = $MyDatabase->pDBcli->prepare($sql);
			    }else {
			        $resultp = $MyDatabase->pDB->prepare($sql);
			    }
//			    var_dump($this->mSQLdata);
//			    echo $sql." ".count($this->mSQLdata)."<br>";

			    if (count($this->mSQLdata)> 0) {
	//		        var_dump($this->mSQLdata);
			        $resultp->execute($this->mSQLdata);
			    } else {
			        $resultp->execute();
			    }
			}catch(PDOException  $e ){
		//	    echo $e->getMessage();
			    return false;
			}
							
// 		if ($this->mconnection=="db_cli") {								
// 			$result = $MyDatabase->gDBcli->Execute($sql);
// 		}else {
//     		$result = $MyDatabase->gDB->Execute($sql);
// 		}
// 		if ($result === false)
// 		{
// 			//print_r ('error reading: '.$MyDatabase->gDB->ErrorMsg( ));

// 			return false;
// 		}

		$html = "\n\t\t<select ";

		if ($this->mClass != "")
			$html .= " class=\"".$this->GetClass( )."\" ";
		elseif ($this->mCSS != "")
			$html .= " class=\"".$this->mCSS."Select\" ";

		$html .= $this->mIdTag." size=\"$this->mSize\" $this->mMultiple ";
		$html .= "name=\"$this->mName$this->mMultiBrackets\" ";
		$html .= "$this->mExtra>";

		if( !$this->mSize )
		{
			$html .= "\n\t\t\t<option value=\"\" ";
			if ( $this->mZebraColor != "" )
			{
				$colorIndex = ($colorIndex + 1) %$this-> mOptionColorIndex;
				if ($colorIndex)
					$html .= "style=\"background=".$this->mZebraColor."\"";
			}
			$html .= " >$this->mDefault";
		}
		try{
		while($row=$resultp->fetch(PDO::FETCH_ASSOC)) 
		{
		    $this-> mKeyArray[$i] = $row["$this->mKey"];
		    
		    $id = $row["$this->mKey"];
		    $name = "";
		    
		    
		    # the reason for this is if you want to show lets say
		    # lastname, firstname as name, you can't do it with every
		    # database. With Postgresql you can concatanate the fields
		    # under one name. But thats life..
		    
		    if (is_array($this->mConcat))
		    {
		        
		        foreach ($this->mConcat as $value)
		        {
		            if ($name)
		                $name .= ", ";
		                
		                $name .= $row["$value"];
		        }
		    }
		    else
		        $name = $row["$this->mField"];
		        
		        if ($this->mDisplayOnly and $this->mSelected !=0 and $this->mSelected == $id)
		        {
		            $html = "<strong>$name</strong>";
		            return $html;
		        }
		        
		        $html .= "\n\t\t\t<option value=\"$id\" ";
		        
		        if (is_array($this->mSelected))
		        {
		            foreach ($this->mSelected as $selectedItem)
		            {
		                if ($selectedItem == $id)
		                {
		                    $html .= " selected ";
		                    break;
		                }
		            }
		        }
		        elseif (0 != $this->mSelected && $this->mSelected == $id)
		        $html .= " selected ";
		        
		        if ( $this->mZebraColor != "" )
		        {
		            $colorIndex = ($colorIndex + 1) %$this-> mOptionColorIndex;
		            if ($colorIndex)
		                $html .= "style=\"background=".$this->mZebraColor."\"";
		        }
		        
		        $html .= " >$name";
		        
		}
		
		}catch(PDOException  $e ){
		    	//    echo $e->getMessage();
		    
		}
		
// 		while (!$result->EOF)
// 		{
// 			$this-> mKeyArray[$i] = $result->fields("$this->mKey");

// 			$id = $result->fields("$this->mKey");
// 			$name = "";


// 			# the reason for this is if you want to show lets say
// 			# lastname, firstname as name, you can't do it with every
// 			# database. With Postgresql you can concatanate the fields
// 			# under one name. But thats life..

// 			if (is_array($this->mConcat))
// 			{
// 				foreach ($this->mConcat as $value)
// 				{
// 						if ($name)
// 							$name .= ", ";

// 						$name .= $result->fields("$value");
// 				}
// 			}
// 			else
// 				$name = $result->fields("$this->mField");


// 			if ($this->mDisplayOnly and $this->mSelected !=0 and $this->mSelected == $id)
// 			{
// 				$html = "<strong>$name</strong>";
// 				return $html;
// 			}

// 			$html .= "\n\t\t\t<option value=\"$id\" ";

// 			if (is_array($this->mSelected))
// 			{
// 				foreach ($this->mSelected as $selectedItem)
// 				{
// 					if ($selectedItem == $id)
// 					{
// 						$html .= " selected ";
// 						break;
// 					}
// 				}
// 			}
// 			elseif (0 != $this->mSelected && $this->mSelected == $id)
// 				$html .= " selected ";

// 			if ( $this->mZebraColor != "" )
// 			{
// 				$colorIndex = ($colorIndex + 1) %$this-> mOptionColorIndex;
// 				if ($colorIndex)
// 					$html .= "style=\"background=".$this->mZebraColor."\"";
// 			}

// 			$html .= " >$name";

// 			$result-> MoveNext( );
// 		}
		$html .= "\n\t\t</select>";

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class GetOptions


/**
* A class to be used in class ControlGroup it will create a hidden field.
* @author Bulent Tezcan
*/
#==============================================
class Hidden
#==============================================
{
	/**
	* Constructor of the class Hidden.
	* @public
	*/
	function Hidden(	$name,$value,$extra="" )
	{
		$this->mName			= $name;
		$this->mValue			= $value;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mExtra			= $extra;
		$this->mNewLine		= FALSE;
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the hidden field.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the hidden field.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$html = "\n\t\t<input type=\"hidden\" name=\"$this->mName\" "
		."value=\"$this->mValue\" $this->mExtra>";

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return TRUE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class Hidden


/**
* A class to be used in class ControlGroup it will create nothing just to fill
* a table cell, when you require the form in a table.
* @author Bulent Tezcan
*/
#==============================================
class PassTru
#==============================================
{
	/**
	* Constructor of the class PassTru.
	* @public
	*/
	function PassTru($value="&nbsp;")
	{
		$this->mName			= "PassTru";
		$this->mValue			= $value;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mNewLine		= FALSE;
		$this->mCellAttr	= "";
	}
	/**
	* Method to set the name of the passtru.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the passtru.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the value of the passtru.
	* @public
	* @returns void
	*/
	function SetValue($value)
	{
		$this->mValue = $value;
	}
	/**
	* Method to get the value of the passtru.
	* @public
	* @returns string
	*/
	function GetValue( )
	{
		if ($this->mValue == '')
			return "&nbsp;";
		else
			return $this->mValue;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS;
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		return $this->mValue;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class PassTru

/**
* A class to be used in class ControlGroup it will create nothing. Same as passtru.
* @author Bulent Tezcan
*/
#==============================================
class Dummy
#==============================================
{
	/**
	* Constructor of the class Dummy.
	* @public
	*/
	function Dummy(	)
	{
		$this->mName			= "Dummy";
		$this->mValue			= "";
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mNewLine		= FALSE;
		$this->mCellAttr	= "";
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the dummy field.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the dummy field.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mClass != "")
			return $this->mCSS;
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$html = "&nbsp;";

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class Dummy

/**
* A class to be used in class ControlGroup it will create a password field.
* @author Bulent Tezcan
*/
#==============================================
class Password
#==============================================
{
	/**
	* Constructor of the class Password.
	* @public
	*/
	function Password($name,$value="",$size=15,$maxLength=15,$extra="")
	{
		$this->mName			= $name;
		$this->mValue			=	$value;
		$this->mSize			= $size;
		$this->mMaxLength = $maxLength;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mExtra			= $extra;
		$this->mNewLine		= FALSE;
		$this->mCellAttr	= "";
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the password field.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the password field.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the value of the password field.
	* @public
	* @returns void
	*/
	function SetValue($value)
	{
		$this->mValue = $value;
	}
	/**
	* Method to get the value of the password field.
	* @public
	* @returns string
	*/
	function GetValue($value)
	{
		return $this->mValue;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$html = "\n\t\t<input ";

		if ($this->mClass != "")
			$html .= " class=\"".$this->GetClass( )."\" ";
		elseif ($this->mCSS != "")
			$html .= " class=\"".$this->mCSS."Input\" ";

		$html .= "type=\"password\" name=\"$this->mName\" value=\"$this->mValue\""
					."size=\"$this->mSize\" maxlength=\"$this->mMaxLength\" "
					."$this->mExtra ";

		$html .= ">";

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class Password


/**
* A class to be used in class ControlGroup it will create an input file for upload.
* @author Bulent Tezcan
*/
#==============================================
class InputFile
#==============================================
{
	/**
	* Constructor of the class InputFile.
	* @public
	*/
	function InputFile($name,$size=25,$extra="")
	{
		$this->mName			= $name;
		$this->mSize			= $size;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mExtra			= $extra;
		$this->mNewLine		= FALSE;
		$this->mId				= "";
		$this->mIdTag			= "";
		$this->mCellAttr	= "";
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the input file.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the input file.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to get the id.
	* @public
	* @returns string
	*/
	function GetId( )
	{
		return $this->mId;
	}
	/**
	* Method to set the id.
	* @public
	* @returns void
	*/
	function SetId($id)
	{
		$this->mId = $id;
		$this->mIdTag = " id=\"$id\" ";
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		return $this->mClass;
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$html = "\n\t\t<input ";

		if ($this->mClass != "")
			$html .= " class=\"".$this->GetClass( )."\" ";
		elseif ($this->mCSS != "")
			$html .= " class=\"".$this->mCSS."Input\" ";

		$html .= $this->mIdTag." type=\"file\" name=\"$this->mName\" "
							."$this->mExtra  ";

		#if ($this->mCSS != "")
			#$html .= "class=\"".$this->mCSS."Button\">";
		#else
			$html .=">";

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class InputFile

/**
* A class to be used in class ControlGroup it will create a submit button.
* @author Bulent Tezcan
*/
#==============================================
class SubmitButton
#==============================================
{
	/**
	* Constructor of the class SubmitButton.
	* @public
	*/
	function SubmitButton($name="Submit",$value="Submit",$extra="")
	{
		if ( !$name )
		$name = "Submit";

		if ( !$value )
		$value = "Submit";

		$this->mName			= $name;
		$this->mValue			= $value;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mExtra			= $extra;
		$this->mCellAttr	= "";
		$this->mNewLine		= FALSE;
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the submit button.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the submit button.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$html = "\n\t\t<input ";

		if ($this->mClass != "")
			$html .= " class=\"".$this->GetClass( )."\" ";
		elseif ($this->mCSS != "")
			$html .= "class=\"".$this->mCSS."Button\" ";

		$html .= "type=\"submit\" name=\"$this->mName\" "
		."value=\"$this->mValue\" $this->mExtra ";

		$html .=">";

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class SubmitButton

/**
* A class to be used in class ControlGroup it will create a reset button.
* @author Bulent Tezcan
*/
#==============================================
class ResetButton
#==============================================
{
	/**
	* Constructor of the class ResetButton.
	* @public
	*/
	function ResetButton($name="Reset",$value="Reset",$extra="")
	{
		if ( !$name )
		$name = "Reset";

		if ( !$value )
		$value = "Reset";

		$this->mName			= $name;
		$this->mValue			= $value;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mExtra			= $extra;
		$this->mNewLine		= FALSE;
		$this->mCellAttr	= "";
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the reset button.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the reset button.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$html = "\n\t\t<input ";

		if ($this->mClass != "")
			$html .= " class=\"".$this->GetClass( )."\" ";
		elseif ($this->mCSS != "")
			$html .= "class=\"".$this->mCSS."Button\" ";

		$html .= "type=\"reset\" name=\"$this->mName\" "
		."value=\"$this->mValue\" $this->mExtra ";

		$html .=">";

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class ResetButton


/**
* A class to be used in class ControlGroup it will create an ordinary button.
* @author Bulent Tezcan
*/
#==============================================
class Button
#==============================================
{
	/**
	* Constructor of the class Button.
	* @public
	*/
	function Button($name,$value,$type="button",$extra="",$image="")
	{
		$this->mName			= $name;
		$this->mValue			= $value;
		$this->mType			=	$type;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mExtra			= $extra;
		$this->mImage			= $image;
		$this->mCellAttr	= "";
		$this->mNewLine		= FALSE;
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the button.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to set the name of the button.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$html = "\n\t\t<button ";

		if ($this->mClass != "")
			$html .= " class=\"".$this->GetClass( )."\" ";
		elseif ($this->mCSS != "")
			$html .= "class=\"".$this->mCSS."Button\" ";

		$html .= "type=\"$this->mType\" name=\"$this->mName\" "
		."$this->mExtra ";

		if ($this->mImage != "")
		$html .= "> <img src=\"".$this->mImage."\">";
		else
		$html .= " value=\"".$this->mValue."\"";

		$html .= "</button>";
		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class Button

/**
* A class to be used in class ControlGroup it will create a button with image
* background.
* @author Bulent Tezcan
*/
#==============================================
class ImageButton
#==============================================
{
	/**
	* Constructor of the class ImageButton.
	* @public
	*/
	function ImageButton(	$name,$source,$align="middle",$border="0",$alt="", $extra="")
	{
		$this->mName			= $name;
		$this->mSource		= $source;
		$this->mAlign			= $align;
		$this->mBorder		= $border;
		$this->mAlt				= $alt;
		$this->mExtra			= $extra;
		$this->mNewLine		= FALSE;
		$this->mClass			= "";
		$this->mCSS				= "";
		$this->mCellAttr	= "";
		$this->mColSpan		= 0;
	}
	/**
	* Method to set the name of the image button.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the image button.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
		return $this->mCellAttr;
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mCSS != "")
			return $this->mCSS."DataTD";
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$html = "\n\t\t<input ";

		if ($this->mClass != "")
			$html .= " class=\"".$this->GetClass( )."\" ";
		elseif ($this->mCSS != "")
			$html .= "class=\"".$this->mCSS."Button\" ";

		$html .="type=\"image\" name=\"$this->mName\" "
		."src=\"$this->mSource\" "
		."align=\"$this->mAlign\" border=\"$this->mBorder\" "
		."alt=\"$this->mAlt\" $this->mExtra>";

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return FALSE;
	}

}	# end of class ImageButton

/**
* A class to be used in class ControlGroup it will create an array of form
* objects, like button, text area, radio button etc.
* @author Bulent Tezcan
*/
#==============================================
class ObjectArray
#==============================================
{
	/**
	* Constructor of the class ObjectArray.
	* @public
	*/
	function ObjectArray( $name )
	{
		$this->mName		= $name;
		$this->mObjects	= array( );
		$this->mNewLine	= false;
		$this->mClass		= "";
		$this->mCSS			= "";
		$this->mCellAttr= "";
		$this->mColSpan	= 0;
	}
	/**
	* Method to set the name of the object array.
	* @public
	* @returns void
	*/
	function SetName($name)
	{
		$this->mName = $name;
	}
	/**
	* Method to get the name of the object array.
	* @public
	* @returns string
	*/
	function GetName( )
	{
		return $this->mName;
	}
	/**
	* Method to set the cell attributes for CSS.
	* @public
	* @returns void
	*/
	function SetCellAttributes($cellAttr)
	{
		if (is_array($cellAttr))
		$this->mCellAttr = $cellAttr;
	}
	/**
	* Method to get the cell attributes.
	* @public
	* @returns string
	*/
	function GetCellAttributes( )
	{
		if (is_array($this->mCellAttr))
			return $this->mCellAttr;
	}
	/**
	* Method to add more objects to the array.
	* @public
	* @returns void
	*/
	function AddObject($object)
	{
		if (is_object($object))
		array_push($this->mObjects, $object);
	}
	/**
	* Method to get the array ob objects.
	* @public
	* @returns array
	*/
	function GetObjects( )
	{
		return $this->mObjects;
	}
	/**
	* Method to set the array for the objects.The method takes array of objects and
	* updates it's array.
	* @public
	* @returns array
	*/
	function SetObjects( $objectArray )
	{
		if (is_array($objectArray))
		{
			$this->mObjects = array( );
			$this->mObjects = $objectArray;
		}
	}
	/**
	* Method to set the CSS.
	* @public
	* @returns void
	*/
	function SetCSS($css)
	{
		$this->mCSS = $css;
	}
	/**
	* Method to get the CSS.
	* @public
	* @returns string
	*/
	function GetCSS( )
	{
		if ($this->mClass != "")
			return $this->mCSS;
		else
			return "";
	}
	/**
	* Method to set the class.
	* @public
	* @returns void
	*/
	function SetClass($class)
	{
		$this->mClass = $class;
	}
	/**
	* Method to get the Class.
	* @public
	* @returns string
	*/
	function GetClass( )
	{
		if ($this->mClass != "")
			return $this->mCSS.$this->mClass;
		else
			return "";
	}
	/**
	* Method to set the colspan.
	* @public
	* @returns void
	*/
	function SetColSpan( $colspan )
	{
		$this->mColSpan = $colspan;
	}
	/**
	* Method to get the colspan.
	* @public
	* @returns integer
	*/
	function GetColSpan( )
	{
		return $this->mColSpan;
	}
	/**
	* Method to get the html code of the class.
	* @public
	* @returns string
	*/
	function GetString( )
	{
		$html = "";

		if (count($this->mObjects))
		{
			foreach ($this->mObjects as $formElements)
			{
				$formElements->SetCSS($this->mCSS);
				$html .= $formElements->GetString();
			}
		}

		return $html;
	}
	/**
	* Method to set to put the object to a new line.
	* @public
	* @returns void
	*/
	function SetNewLine( $value=TRUE )
	{
		$this->mNewLine = $value;
	}
	/**
	* Method to check if the object should be placed to a new line.
	* @public
	* @returns bool
	*/
	function IsNewLine( )
	{
		return $this->mNewLine;
	}
	/**
	* Method to check if the object is a hidden element.
	* @public
	* @returns bool
	*/
	function IsHidden( )
	{
		return FALSE;
	}
	/**
	* Method to check if the object is an array of objects.
	* @public
	* @returns bool
	*/
	function IsObjectAnArray( )
	{
		return TRUE;
	}

}	# end of class ObjectArray

?>