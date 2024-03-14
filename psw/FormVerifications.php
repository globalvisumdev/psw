<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	function DateVerify($date,$format)
	{
		if (!$date or !$format)
			return FALSE;
	
		$format = strtoupper($format);

		Switch (TRUE)
		{
			case $format == "DMY":
			case $format == "DDMMYY":
				$day		= substr($date,0,2);
				$month	= substr($date,2,2);
				$year		= substr($date,4,2);
				break;
			case $format == "D/M/Y":
			case $format == "D:M:Y":
			case $format == "D-M-Y":
			case $format == "D_M_Y":
			case $format == "D M Y":
			case $format == "D.M.Y":
				$day		= substr($date,0,2);
				$month	= substr($date,3,2);
				$year		= substr($date,6,2);
				break;

			case $format == "MDY":
			case $format == "MMDDYY":
				$month	= substr($date,0,2);
				$day		= substr($date,2,2);
				$year		= substr($date,4,2);
				break;
			case $format == "M/D/Y":
			case $format == "M:D:Y":
			case $format == "M-D-Y":
			case $format == "M_D_Y":
			case $format == "M D Y":
			case $format == "M.D.Y":
				$month	= substr($date,0,2);
				$day		= substr($date,3,2);
				$year		= substr($date,6,2);
				break;

			case $format == "DDMMYYYY":
				$day		= substr($date,0,2);
				$month	= substr($date,2,2);
				$year		= substr($date,4,4);
				break;
			case $format == "DD/MM/YYYY":
			case $format == "DD:MM:YYYY":
			case $format == "DD-MM-YYYY":
			case $format == "DD_MM_YYYY":
			case $format == "DD MM YYYY":
			case $format == "DD.MM.YYYY":
				$day		= substr($date,0,2);
				$month	= substr($date,3,2);
				$year		= substr($date,6,4);
				break;

			case $format == "MMDDYYYY":
				$month	= substr($date,0,2);
				$day		= substr($date,2,2);
				$year		= substr($date,4,4);
				break;
			case $format == "MM/DD/YYYY":
			case $format == "MM:DD:YYYY":
			case $format == "MM-DD-YYYY":
			case $format == "MM_DD_YYYY":
			case $format == "MM DD YYYY":
			case $format == "MM.DD.YYYY":
				$month	= substr($date,0,2);
				$day		= substr($date,3,2);
				$year		= substr($date,6,4);
				break;
		}
		if ( IsMonthOk($month) and IsDayOk($day,$month,$year) )
			return TRUE;
		else
			return FALSE;
	}

	function IsDayOk($day,$month,$year)
	{
		if ($month == 1 || $month == 3 || $month == 5 || $month == 7
					 || $month == 8 || $month == 10 || $month == 12)
		{
			if ($day >= 1 && $day <= 31) 
				$result = 1;
			 else 
				 $result = 0;
		}
		else if ($month == 2)
		{
			if ($day >= 1 && $day <=28) 
				$result = 1;
			else if ($day == 29 && ($year % 4) == 0) 
				$result = 1;  // valid leap-year
			else if ($day == 29 && ($year % 4) != 0) 
				$result = 0;
			else 
				$result = 0;
		}
		else
		{
			if ($day >= 1 && $day <= 30) 
				$result = 1;
			else 
				$result = 0;
		}
		return $result;
	}

	function IsMonthOk($month)
	{
		 if($month <= 12 && $month != 0) 
			$result = 1;
		 else 
			 $result = 0;
		 
		 return $result;
	}


?>