<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	require_once "Security.class.php";
	
	session_start( );

	ob_start( );

	include "header.inc.php";


	session_destroy( );

	echo "<br><br><b>El usuario ha intentado algo que no est� permitido.</b>";
	echo "<br><br><font color='#FF0000'><b>Su sesi�n ha sido DESTRUIDA.</b></font>";
	


	include "footer.inc.php";

	ob_end_flush( );

	return true;

?>