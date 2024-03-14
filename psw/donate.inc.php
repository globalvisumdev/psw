<?php

	require_once "Security.class.php";

	$tableClass = $_SESSION["CSS"]."FormTABLE";
	$DataTD = $_SESSION["CSS"]."DataTD";

	$serverURL = "http".($_SERVER["HTTPS"]=="on"?"s":"")
							."://".$_SERVER["HTTP_HOST"]
							.strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"));

	$serverURL = substr($serverURL,0,-1);

	$mySecurity = new Security( );

	//if (!$mySecurity-> isAllowedTo('Show Admin Menu'))
	if (!$mySecurity-> isAllowedTo(1))
		return true;

/*
echo <<<HTML
<br><br>
<table class="$tableClass" cellspacing="1" cellpadding="4" align="center">
<tr>
	<td class="$DataTD"><i><b>Help us to help you. If you liked this software, please help us.</i></b></td>
	<td class="$DataTD"><a href="http://www.greenpepper.ca/index.php?link=donate"><img src="./donate.php" border=0 alt="Please help us. Thank you."></a></td>
</tr>
</table>
<br>
HTML;

*/


?>