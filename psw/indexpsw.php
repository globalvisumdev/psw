<?php
	/*
	* @author Bulent Tezcan. bulent@greenpepper.ca
	*/

	session_start( );

	require_once "Form.class.php";
	
	ob_start( );

	include "header.inc.php";

	if ($_GET['isOK'] == 'OK')
	{
		$FormElements['__error'] = "Thank you. We hope you found it useful.";
		$_SESSION['resetDatabaseError'] = "";
	}
	elseif($_GET['isOK'] == 'ERROR')
		$FormElements['__error'] = $_SESSION['resetDatabaseError'];

	$myForm = new Form("login");
	$myForm-> SetNumberOfColumns( 2 );
	$myForm-> SetCellSpacing( 1 );
	$myForm-> SetCellPadding( 5 );
	$myForm-> SetBorder	( 0 );
	$myForm-> SetAlign ("center");
	$myForm-> SetTableWidth ("400");
	$myForm-> SetTableHeight (null);
	$myForm-> SetCSS ( $_SESSION["CSS"] );
	$myForm-> SetEmptyCells (false);
	$myForm-> SetFormHeader("<font class=\"".$_SESSION["CSS"]."FormHeaderFont\">Welcome to Security Demo</font>");


	$myForm-> SetErrorMessage($FormElements['__error']);
		
	$label = new Label("lbl1","After you are done with the testing,<br>PLEASE reset the database for others.<br><br>User : admin<br>Password: secret");
	$label-> SetColspan(2);

	$myForm-> AddFormElementToNewLine($label);

	$adminMenu = "\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"adminmenu.php\">Start the Demo</a>";

	$passtru = new PassTru();

	$passtru->SetValue($adminMenu);
	$passtru->SetClass("DataTD");

	$myForm-> AddFormElementToNewLine($passtru);

	$passtru->SetValue("\n<a class=\"".$_SESSION["CSS"]."LinkButton\" href=\"resetDatabase.php\">Reset the Database</a>");
	$myForm-> AddFormElement($passtru);

	echo $myForm-> GetFormInTable( );

	include "footer.inc.php";

	ob_end_flush( );
?>