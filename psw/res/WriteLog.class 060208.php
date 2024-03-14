<?php

include_once "securityConfig.inc.php";
require_once "Security.class.php";
include_once "commonFunctions.php";
include_once( ADODB_DIRECTORY."/adodb.inc.php" );
require_once "Form.class.php";
require_once "MyDatabase.class.php";
require_once "Accounts.class.php";
require_once "Activity.class.php";

session_start( );

/**
* A class that handles anything with the log table.
* @author Bulent Tezcan. bulent@greenpepper.ca
*/

class WriteLog extends MyDatabase
{
  /**
  * Constructor of the class WriteLog.
  * @public
  */
  function WriteLog( )
  {
    // set the table properties
    $this->mTableName = "log";
    $this->mKeyName   = "timestamp";

    // set the Column Properties. These are required to be able to
    // write the table
    $this->mTableFields['timestamp']  ['type']  = "integer";
    $this->mTableFields['ip']['type']           = "string";
    $this->mTableFields['accountid']['type']    = "integer";
    $this->mTableFields['username']['type']     = "string";
    $this->mTableFields['activityid']['type']   = "integer";

    // set other properties
    $this->mFormName = "Log";
    $this->mExtraFormText = "";

    $this->mySecurity = new Security( );

    // Set up database connection
    $this->MyDatabase();
  }

  /**
  * Method to set the accountid.
  * @public
  */
  function SetAccountId($accountid)
  {
    $this->mAccountId = $accountid;
  }

  /**
  * Method to set the username.
  * @public
  */
  function SetUserName($username)
  {
    $this->mUserName = $username;
  }

  /**
  * Method to set the activityid.
  * @public
  */
  function SetActivityId($activityid)
  {
    $this->mActivityId = $activityid;
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
  * Method to write to the Log.
  * @public
  */
  function WriteToLog( )
  {
    #$ip = getenv('REMOTE_ADDR');
    $ip = $this->ipCheck();
    $time = time();
    $accountid = $_SESSION['myAccount'];
    list( $dia, $mes, $ano, $hora, $min, $seg ) = split( '[ :/.-]', date("d-m-Y H:i:s", mktime()) );
    $ahora_ymd = "$ano-$mes-$dia $hora:$min:$seg";

    $sql = "INSERT INTO log (timestamp,ip,accountid,username,activityid,fechayhora) "
          ."VALUES($time, '$ip', $this->mAccountId, $this->mUserName, $this->mActivityId, '$ahora_ymd')";

    if ($this->gDB->Execute($sql) === false)
    {
      $this->mErrorMessage = 'error inserting: '.$this->gDB->ErrorMsg( )."<br>".$sql;

      if ($_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

      return false;
    }
    else
    {
      $this->mErrorMessage = "Registro agregado.";
      return true;
    }
  }

  function ipCheck( )
  {
  /*
  This function checks if user is coming behind proxy server. Why is this important?
  If you have high traffic web site, it might happen that you receive lot of traffic
  from the same proxy server (like AOL). In that case, the script would count them all as 1 user.
  This function tryes to get real IP address.
  Note that getenv() function doesn't work when PHP is running as ISAPI module
  */
    if (getenv('HTTP_CLIENT_IP')) {
      $ip = getenv('HTTP_CLIENT_IP');
    }
    elseif (getenv('HTTP_X_FORWARDED_FOR')) {
      $ip = getenv('HTTP_X_FORWARDED_FOR');
    }
    elseif (getenv('HTTP_X_FORWARDED')) {
      $ip = getenv('HTTP_X_FORWARDED');
    }
    elseif (getenv('HTTP_FORWARDED_FOR')) {
      $ip = getenv('HTTP_FORWARDED_FOR');
    }
    elseif (getenv('HTTP_FORWARDED')) {
      $ip = getenv('HTTP_FORWARDED');
    }
    else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }


  /**
  * Method to send the a form to ask the user for the dates they want to see
  * the log.
  * @private
  * @returns string
  */
  function SendSelectLogDate($FormElements)
  {
    $myForm = new Form("selectlogdate");

    $myForm-> SetNumberOfColumns( 2 );
    $myForm-> SetCellSpacing( 1 );
    $myForm-> SetCellPadding( 5 );
    $myForm-> SetBorder ( 0 );
    $myForm-> SetAlign ("center");
    $myForm-> SetTableWidth ("300");
    $myForm-> SetTableHeight (null);
    $myForm-> SetCSS ( $_SESSION["CSS"] );
    $myForm-> SetEmptyCells (true);
    $myForm-> SetFormHeader("Log de Actividades");

    $myForm-> SetErrorMessage($this->mErrorMessage);

    if (!isset($FormElements['timeframe']))
      $FormElements['timeframe'] = '86400';

    $timeSelection['1']       = 'Desde ahora';
    $timeSelection['3600']    = '1 hora';
    $timeSelection['7200']    = '2 horas';
    $timeSelection['10800']   = '3 horas';
    $timeSelection['14400']   = '4 horas';
    $timeSelection['18000']   = '5 horas';
    $timeSelection['21600']   = '6 horas';
    $timeSelection['25200']   = '7 horas';
    $timeSelection['28800']   = '8 horas';
    $timeSelection['32400']   = '9 horas';
    $timeSelection['36000']   = '10 horas';
    $timeSelection['39600']   = '11 horas';
    $timeSelection['43200']   = '12 horas';
    $timeSelection['86400']   = '1 día';
    $timeSelection['172800']  = '2 días';
    $timeSelection['259200']  = '3 días';
    $timeSelection['345600']  = '4 días';
    $timeSelection['432000']  = '5 días';
    $timeSelection['518400']  = '6 días';
    $timeSelection['604800']  = '1 semana';
    $timeSelection['1209600'] = '2 semanas';
    $timeSelection['1817400'] = '3 semana';
    $timeSelection['2592000'] = '1 mes';
    $timeSelection['5184000'] = '2 meses';
    $timeSelection['7776000'] = '3 meses';
    $timeSelection['10368000'] = '4 meses';
    $timeSelection['12960000'] = '5 meses';
    $timeSelection['15552000'] = '6 meses';
    $timeSelection['18144000'] = '7 meses';
    $timeSelection['20736000'] = '8 meses';
    $timeSelection['23328000'] = '9 meses';
    $timeSelection['25920000'] = '10 meses';
    $timeSelection['28512000'] = '11 meses';
    $timeSelection['31104000'] = '1 año';

    $myForm-> AddFormElementToNewLine (new Label("lb1","Tiempo de inicio"));

    $beginTime = new SelectBox($name="beginTime",$values=$timeSelection,$selected=$FormElements['beginTime'],$default="-Please Select a time frame -",$displayOnly=false,$size=0,$multiple=FALSE,
    $extra="");

    $beginTime-> SetCellAttributes(array("align"=>"center"));

    $myForm-> AddFormElement  ($beginTime);

    $myForm-> AddFormElementToNewLine (new Label("lb1","Tiempo de Fin<br><font style='font-size:8pt; font-style:italic;'>(Opcional)</font>"));

    $endTime = new SelectBox($name="endTime",$values=$timeSelection,$selected=$FormElements['endTime'],$default="-Please Select a time frame -",$displayOnly=false,$size=0,$multiple=FALSE,
    $extra="");

    $endTime-> SetCellAttributes(array("align"=>"center"));

    $myForm-> AddFormElement  ($endTime);

    $buttons = new ObjectArray("buttons");
    $buttons->AddObject(new SubmitButton("B_submit","Seguir"));
    $buttons->AddObject(new SubmitButton("B_cancel","Cancelar"));
    $buttons->SetColSpan( $myForm-> GetNumberOfColumns() );
    $buttons->SetCellAttributes(array("align"=>"middle"));

    $myForm-> AddFormElement ($buttons);

    return $myForm->GetFormInTable( );

  }

  /**
  * Method to list the Log.
  * @public
  */
  function ListLog($beginTime,$endTime='')
  {
    include_once "Paging.class.php";

    $now  = time();
    $time = "";

    if ($endTime)
    {
      $beginTime = time() - $beginTime;
      $endTime = time() - $endTime;
      $time = "timestamp >= $endTime AND timestamp <= $beginTime";
    }
    elseif ($beginTime and $beginTime != 1)
    {
      $beginTime = time() - $beginTime;
      $time = "timestamp >= $beginTime AND timestamp <= $now";
    }
    elseif ($beginTime == 1)
    {
      $time = "timestamp <= $now";
    }

    $sql = "SELECT count(*) as totalrecord FROM log "
          ."WHERE $time";

    $result = &$this->gDB->Execute($sql);

    if ($result === false   AND $_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql );

    $number=(int)$result->fields("totalrecord");#record results selected from db
    $displayperpage="20";# record displayed per page
    $pageperstage="10";# page displayed per stage
    $allpage=ceil($number/$displayperpage);# how much page will it be ?
    $allstage=ceil($allpage/$pageperstage);# how many page will it be ?
    if(trim($_GET['startpage'])==""){$_GET['startpage']=1;}
    if(trim($_GET['nowstage'])==""){$_GET['nowstage']=1;}
    if(trim($_GET['nowpage'])==""){$_GET['nowpage']=$_GET['startpage'];}

    $p = new Paging($_GET['nowstage'],$_GET['startpage'],$allpage,$_GET['nowpage'],
                    $pageperstage,$allstage, $extrargv="&beginTime=$beginTime&endTime=$endTime" );

    $Account = new Accounts( );
    $Activity = new Activity( );

    $sql = "SELECT * FROM log WHERE $time "
          ."ORDER BY timestamp DESC";

    $result = $this->gDB->SelectLimit($sql,$numrows=$displayperpage,$offset=($_GET['nowpage']-1)*$displayperpage,$inputarr=false);


    if ($result === false   AND $_SESSION['IS_ERROR_REPORTING'])
        $this->mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql );

    if (!$result->EOF)
    {
      $this->SendHeader( );

      if ($displayperpage >= 20)
      {
        $passtru = new PassTru("");
        $passtru->SetColSpan( $this->myForm-> GetNumberOfColumns() );
        $passtru->SetClass("");
        $passtru->SetCellAttributes(array("align"=>"center"));

        $passtru->SetValue( $p->printPagingNavigation() );
        $this->myForm-> AddFormElementToNewLine ($passtru);
      }

      while (!$result->EOF)
      {
        $passtru = new PassTru();

        $timestamp = date("F jS, Y -- g:ia",
                                $result->fields("timestamp"));

        $passtru->SetValue($value=$timestamp);
        $passtru->SetClass("DataTD");

        $this->myForm-> AddFormElementToNewLine($passtru);
        $passtru->SetValue($result->fields("ip"));

        $this->myForm-> AddFormElement($passtru);

        $accountName = $Account-> GetAccountName($result->fields("accountid"));
        $passtru->SetValue($accountName);

        $this->myForm-> AddFormElement($passtru);

        $passtru->SetValue($result->fields("username"));
        $this->myForm-> AddFormElement($passtru);

        $activity = $Activity-> GetDescription($result->fields("activityid"));

        $passtru->SetValue($activity);
        $this->myForm-> AddFormElement($passtru);

        $result->MoveNext( );
      }

      $value = "<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"".$_SERVER['PHP_SELF']."?beginTime=$beginTime&endTime=$endTime\">&nbsp;Refrescar&nbsp;</a>";

      $passtru = new PassTru("");
      $passtru->SetColSpan( $this->myForm-> GetNumberOfColumns() );
      $passtru->SetClass("");
      $passtru->SetCellAttributes(array("align"=>"center"));
      $passtru->SetValue( $value );
      $this->myForm-> AddFormElementToNewLine ($passtru);


      $passtru = new PassTru("");
      $passtru->SetColSpan( $this->myForm-> GetNumberOfColumns() );
      $passtru->SetClass("");
      $passtru->SetCellAttributes(array("align"=>"center"));
      $passtru->SetValue( $p->printPagingNavigation() );
      $this->myForm-> AddFormElementToNewLine ($passtru);

      $value = "<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"adminmenu.php\"><-- Volver al Menú Seguridad</a>";
      $passtru = new PassTru($value);
      $passtru->SetColSpan( $this->myForm-> GetNumberOfColumns() );
      $passtru->SetClass("");

      $this->myForm-> AddFormElement  ($passtru);

      $this->SendTrailer( );
    }
  }

  /**
  * Method to set the Html header.
  * @private
  * @returns void
  */
  function SendHeader( )
  {
    $this->myForm = new Form("loglist");

    $this->myForm-> SetNumberOfColumns( 5 );
    $this->myForm-> SetCellSpacing( 1 );
    $this->myForm-> SetCellPadding( 5 );
    $this->myForm-> SetBorder ( 0 );
    $this->myForm-> SetAlign ("center");
    $this->myForm-> SetTableWidth (null);
    $this->myForm-> SetTableHeight (null);
    $this->myForm-> SetCSS ( $_SESSION["CSS"] );
    $this->myForm-> SetEmptyCells (false);
    $this->myForm-> SetFormTagRequired (true);
    $this->myForm-> SetFormHeader("Lista del Log");

    $mylabel = new Label($name="lb1",$value="Fecha/Hora");
    $mylabel-> SetClass("ColumnTD");

    $this->myForm-> AddFormElementToNewLine($mylabel);

    $mylabel->SetValue("IP");
    $this->myForm-> AddFormElement($mylabel);

    $mylabel->SetValue("Usuario");
    $this->myForm-> AddFormElement($mylabel);

    $mylabel->SetValue("Nombre de usuario");
    $this->myForm-> AddFormElement($mylabel);

    $mylabel->SetValue("Descripción");
    $this->myForm-> AddFormElement($mylabel);
  }

  /**
  * Method to send the Html in a table.This method is called from the ListGroups
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