<?php

	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	/**
	*		This function prepares the variables to SQL command. You don't have
	*		to worry about the ' ' tags before and after a text field for instance.
	*/
    function ToSQL($Value, $ValueType)
    {
        $ValueType = strtoupper($ValueType);
				
				if(!strlen($Value))
        {
            return "NULL";
        }
        else
        {
            if($ValueType == 'INTEGER' || $ValueType == 'INT' || $ValueType == 'FLOAT')
            {
                return doubleval(str_replace(",", ".", $Value));
            }
            else if($ValueType == 'DATE')
            {
                return  "'" . str_replace("'", "''", $Value) . "'";
            }
						else if($ValueType == 'BOOL' or $ValueType == 'BOOLEAN')
						{
								if ($Value == 1 or $Value == "1" or
										strtoupper($Value) == 'T' or
										strtoupper($Value) == 'TRUE')
									return "'TRUE'";
								else
									return "'FALSE'";
						}
            else # String, Text
            {
                return "'" . str_replace("'", "''", $Value) . "'";
            }
        }
    }

	/**
	* verifies that the email address looks ok, and that
	* it refers to a meaningful domain.
	*/

	function isEmailValid ($email) 
	{ 
		if (eregi("^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,3}$", $email, $check)) 
		{ 		
			if ( getmxrr(substr(strstr($check[0], '@'), 1), $validate_email_temp) ) 
			{ 
				return TRUE; 
			} 
			// THIS WILL CATCH DNSs THAT ARE NOT MX. 
			if(checkdnsrr(substr(strstr($check[0], '@'), 1),"ANY"))
			{ 
				return TRUE; 
			} 
		} 
		return FALSE; 
	}

function is_hidden($path)	## Checks whether the file is hidden.
{
	# Hidden files and directories
	$hide_file_extension       = array(
																			"foo",
																			"bar",
															 );

	$hide_file_string          = array(
																			".htaccess",
															 );

	$hide_directory_string     = array(
																			"secret dir",
															 );

	$extension = strtolower(substr(strrchr($path, "."),1));

	foreach ($hide_file_extension as $hidden_extension)
		if ($hidden_extension == $extension)
		 return TRUE;

	foreach ($hide_file_string as $hidden_string)
		if (stristr(basename($path), $hidden_string))
		 return TRUE;

	foreach ($hide_directory_string as $hidden_string)
		if (stristr(dirname($path), $hidden_string))
		 return TRUE;

	return FALSE;
}

?>