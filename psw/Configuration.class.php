<?php
include_once "securityConfig.inc.php";
require_once "Security.class.php";
include_once "commonFunctions.php";
include_once( ADODB_DIRECTORY."/adodb.inc.php" );
require_once "Form.class.php";
require_once "MyDatabase.class.php";

session_start( );

/**
* A class that handles anything with the configuration table.
* @author Bulent Tezcan. bulent@greenpepper.ca
*/
class Configuration extends MyDatabase
{
  /**
  * Constructor of the class WriteLog.
  * @public
  */
  function __construct( )
  {
    // set the table properties
    $this->mTableName = "configuration";
    $this->mKeyName   = "";

    // set the Column Properties. These are required to be able to
    // write the table
    $this->mTableFields['md5']  ['type']              = "integer";
    $this->mTableFields['bad_attempts_max']['type']   = "integer";
    $this->mTableFields['bad_attempts_wait']['type']  = "integer";
    $this->mTableFields['log_activities']['type']     = "integer";
    $this->mTableFields['timeout']['type']            = "integer";
    $this->mTableFields['error_reporting']['type']    = "integer";
    $this->mTableFields['stylesheet']['type']         = "text";

    // set other properties
    $this->mFormName = "configuration";
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
  * Method to get the configuration file.
  * @public
  * @returns array
  */
  function GetConfiguration( )
  {
    $sql = "SELECT * FROM configuration";

    try {
        $resultp2 = $this->pDB->prepare($sql);
        $resultp2->execute();
        while($row2=$resultp2->fetch(PDO::FETCH_OBJ)) {
            return $row2;
        }
    }catch(PDOException  $e ){
        $this-> SetErrorMessage('Error de lectura gc','');
        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error de lectura gc','');
        return false;
    }
    
    return false;
    
    
//     $result = $this->gDB->Execute($sql);

//     if ($result === false)
//     {
//       $this->SetErrorMessage('error reading: '
//                             .$this->gDB->ErrorMsg( ));

//       if ($_SESSION['IS_ERROR_REPORTING'])
//         $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

//       return false;
//     }

//     return $result;
  }

  /**
  * Method to update configuration table.
  * @public
  * @returns bool
  */
  function UpdateConfiguration($FormElements)
  {
    foreach ($this->mTableFields as $key =>$value)
    {
      if ($key <> $this->mKeyName)
      {
        if ($FormElements[$key])
          $this->Field($key,htmlspecialchars($FormElements[$key]));
      }
    }

    if (!$FormElements["md5"])
      $this->Field("md5",0);

    if (!$FormElements["log_activities"])
      $this->Field("log_activities",0);

    if (!$FormElements["error_reporting"])
      $this->Field("error_reporting",0);

    return $this-> Update( );
  }
  /**
  * Method to send the form. The form is displayed within the method with echo.
  * @private
  * @returns void
  */
  function SendConfigurationForm($FormElements)
  {
    $myForm = new Form($this->GetFormName( ));

    $myForm-> SetNumberOfColumns( 2 );
    $myForm-> SetCellSpacing( 1 );
    $myForm-> SetCellPadding( 5 );
    $myForm-> SetBorder ( 0 );
    $myForm-> SetAlign ("center");
    $myForm-> SetTableWidth ("350");
    $myForm-> SetTableHeight (null);
    $myForm-> SetCSS ( $_SESSION["CSS"] );
    $myForm-> SetEmptyCells (true);
    $myForm-> SetFormHeader("Configuración");

    $myForm-> AddFormElementToNewLine (new Label("lb1","Usar MD5 para campos de contraeña :"));

    if ($FormElements["md5"]==1)
      $IsChecked = 1;
    else
      $IsChecked = 0;

    $md5 = new CheckBox($name="md5",$value=1,$IsChecked,$displayOnly=false,
    $displayText="");

    $myForm-> AddFormElement  ($md5);

    $myForm-> AddFormElementToNewLine (new Label("lb1","Límite de intentos fallidos<br><i>Cero: sin límite</i>"));
    $myForm-> AddFormElement (new TextField("bad_attempts_max",$FormElements["bad_attempts_max"],2,2));

    $myForm-> AddFormElementToNewLine (new Label("lb1","Intento fallido espera en segundos :"));
    $myForm-> AddFormElement (new TextField("bad_attempts_wait",$FormElements["bad_attempts_wait"],5,5));

    $myForm-> AddFormElementToNewLine (new Label("lb1","Log de Actividades :"));

    $log_activities = new CheckBox($name="log_activities",$value=1,$IsChecked=$FormElements["log_activities"],$displayOnly=false,
    $displayText="");

    $myForm-> AddFormElement  ($log_activities);

    $myForm-> AddFormElementToNewLine (new Label("lb1","Corta si no activa la sesión en segundos<br><i>Cero: no corta</i>"));
    $myForm-> AddFormElement (new TextField("timeout",$FormElements["timeout"],5,5));

    $myForm-> AddFormElementToNewLine (new Label("lb1","Report de Errores en pantalla :"));

    $error_reporting = new CheckBox($name="error_reporting",$value=1,$IsChecked=$FormElements["error_reporting"],$displayOnly=false,
    $displayText="");

    $myForm-> AddFormElement  ($error_reporting);

    $myForm-> AddFormElementToNewLine (new Label("lb1","Estilo de las Páginas :"));

    $css_directory = "./css";

    $css_files = array();

    if ($open = opendir($css_directory))
    {
      for($i=0;($file = readdir($open)) != FALSE; $i++)
      {
        $dot_pos = strrpos($file,".");
        if ($dot_pos != FALSE)
          if (is_file($css_directory."/".$file) && !is_hidden($css_directory."/".$file) and
            strtolower(substr($file,$dot_pos+1,3)) == "css")
            $css_files[$i] = substr($file,0,$dot_pos);
      }

     closedir($open);

     if (isset($css_files))
     {
      ksort($css_files);
      reset($css_files);
     }
    }

    $cssArray = array();

    foreach ($css_files as $key => $css)
      array_push($cssArray,$css);

    # create a selectBox object called CSS. You can pass extra things like
    # javascript on certain events.

    $CSS = new SelectBox($name="stylesheet",$values=$cssArray,$selected=$FormElements['stylesheet'],$default="-Please Select a CSS-",$displayOnly=false,$size=0,$multiple=FALSE,$extra="");

    # if you want your select box elements to have different color
    # you can use this method.

    $CSS-> SetZebraColor($color="#D9DCC5");

    # What this means, when you select a value from the selectbox, it will return
    # the text you see in that select box. If you don't set this, default is the
    # index sequence, like 0=>Carling, 1=>bla, 2=>bla_bla

    $CSS-> SetReturnValueAsText( );

    # add the select box into the form

    $myForm-> AddFormElement($CSS);

    $buttons = new ObjectArray("buttons");
    $buttons->AddObject(new SubmitButton("B_submit","Confirmar"));
    $buttons->AddObject(new SubmitButton("B_cancel","Cancelar"));
    $buttons->SetColSpan($myForm-> GetNumberOfColumns());
    $buttons->SetCellAttributes(array("align"=>"middle"));

    $myForm-> AddFormElementToNewLine ($buttons);

    $myForm-> SetErrorMessage($this->GetErrorMessage());

    return $myForm->GetFormInTable( );
  }

  /**
  * Method to check the form. Sets the error message and which field is wrong.
  * @public
  * @returns bool
  */
  function ErrCheckConfigurationForm($FormElements)
  {
    $this->mIsError = 0;

    if (!$FormElements["bad_attempts_max"] or
        $FormElements["bad_attempts_max"] < 0)
    {
      $this->mErrorMessage = "Cantidad inválida de intentos fallidos.";
      $this->mFormErrors["bad_attempts_max"]=1;
      $this->mIsError = 1;
    }

    if (!$FormElements["bad_attempts_wait"] or
        $FormElements["bad_attempts_wait"] < 0)
    {
      $this->mErrorMessage = "Espera inválida a intentos fallidos.";
      $this->mFormErrors["bad_attempts_wait"]=1;
      $this->mIsError = 1;
    }

    if (!$FormElements["timeout"] or
        $FormElements["timeout"] < 0)
    {
      $this->mErrorMessage = "Corte inválido.";
      $this->mFormErrors["timeout"]=1;
      $this->mIsError = 1;
    }
    return $this->mIsError;
  }
}
?>