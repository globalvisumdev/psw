<?php

include_once "securityConfig.inc.php";
include_once( ADODB_DIRECTORY."/adodb.inc.php" );
include_once ('theme.inc.php');
include_once "commonFunctions.php";

require_once "Accounts.class.php";
require_once "MyDatabase.class.php";
require_once "WriteLog.class.php";
require_once "Form.class.php";

session_start( );


/**
* A class to check who can do what.
* @author Bulent Tezcan
*/

class Security extends MyDatabase
{

  /**
  * Constructor of the class Security.
  * @public
  */
  function Security( )
  {
    // Set up database connection
    $this->MyDatabase();

    if ($_SESSION['TIMEOUT_SECONDS'] > 0)
    {
      if (!isset($_SESSION['timestamp']) and isset($_SESSION['myAccount']) and
          $_SESSION['myAccount'] != null)
      {
        $_SESSION['timestamp'] = time();
      }
      elseif(isset($_SESSION['timestamp']) and isset($_SESSION['myAccount']))
      {
        $timeout = $_SESSION['timestamp'] + $_SESSION['TIMEOUT_SECONDS'];
        if ($timeout < time())
        {
          session_destroy();
          session_start();
          include "securityConfig.inc.php";
        }
        else
          $_SESSION['timestamp'] = time();
      }
    }
  }

  /**
  * Determines the login page's path and redirects it to there.
  * @public
  * @returns boolean
  */
  function GoToLoginPage( )
  {
    $_SESSION['loginPrompting'] = "1";

    $pathToLogin = "http".($_SERVER["HTTPS"]=="on"?"s":"")
                  ."://".$_SERVER["HTTP_HOST"]
                  #.dirname($_SERVER['PHP_SELF'])
                  .strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"));
    
	if (strpos($pathToLogin,'psw') === false) {
		$pathToLogin .= 'psw/';
	}
	                  
	$pathToLogin .= "login.php";

    header("Location: $pathToLogin");
    exit;
  }

  /**
  * Determines the notAuthorized.php location and redirects it to there.
  * @public
  * @returns boolean
  */
  function GotoNotAuthorized( )
  {
    $_SESSION['loginPrompting'] = "1";

    $pathToPage = "http".($_SERVER["HTTPS"]=="on"?"s":"")
                  ."://".$_SERVER["HTTP_HOST"]
                  .strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"))
                  ."psw/"
                  ."notAuthorized.php";
    #echo '$pathToPage: '.$pathToPage.'<br>';
    header("Location: $pathToPage");
    exit;
  }

  /**
  * Redirects it to the page you want.
  * @public
  * @returns boolean
  */
  function GotoThisPage( $page, $url=null )
  {
    $_SESSION['loginPrompting'] = "1";

    $pathToPage = "http".($_SERVER["HTTPS"]=="on"?"s":"")
                  ."://".$_SERVER["HTTP_HOST"]
                  .strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"))
                  .$page;

    if ($url)
      $pathToPage .= "?$url";

    header("Location: $pathToPage");
    exit;
  }

  /**
  * Method to return reverse value of isPermittedTo.
  * @public
  * @returns boolean
  */
  function isNotAllowedTo($actionName,$hastaaction=0)
  {
    if ($this-> isPermittedTo($actionName,$hastaaction))
      return false;
    else
      return true;
  }

  /**
  * synonyms of isPermittedTo.
  * @public
  * @returns boolean
  */
  function isAllowedTo($actionName,$hastaaction=0)
  {
    return $this-> isPermittedTo($actionName,$hastaaction);
  }

  /**
  * Method to check if the person can do the action.
  * Pablo 21/08/09 que actue sobre tabla de permisos por account 
  * y que el parametro se compare con el actionid
  * y que se compare con el actionid raiz (100,200,300,400)
  * @public
  * @returns boolean
  */
  function isPermittedTo($actionName,$hastaaction=0)
  {
    $_SESSION['http_referer'] = &$_SERVER['PHP_SELF'];

    if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount']))
    {
      $this-> GoToLoginPage( );
    }

    //chequea validez del account
    $sql = "select count(*) c 
    from  accounts a
    WHERE a.accountid=".$_SESSION['myAccount']."
and 
    (validez_desde is null or validez_desde < '2000-00-00 00:00:00' or validez_desde <= now())
    and (validez_hasta is null or validez_hasta < '2000-00-00 00:00:00' or validez_hasta >= now())
   "; 
            
    $this->MyDatabase();
    $result = $this->gDB->Execute($sql);
    
    $c=0;
    if( $result->fields("c")) {
        $c=$result->fields("c");
    }
    
    if ($c == 0) { 
        unset($_SESSION['myAccount']);
        $this-> GoToLoginPage( );
    }
    
    #$sql = "SELECT count(*) as count FROM groupaccounts ag, groupactions ga "
    #     ." WHERE ag.accountid=".$_SESSION['myAccount']
    #     ." AND ag.groupid=ga.groupid"
    #     ." AND ga.actionid=(SELECT actionid FROM actions"
    #     ."          WHERE upper(actionname)='".strtoupper($actionName)."')";

	$raiz=0;
	if (intval($actionName) > 100) {$raiz = floor(intval($actionName)/100)*100;}
		
	
    //Busca si hay un account que equivale Pablo 250714
    //baja s{olo un nivel sobre el accountcliente cuando el clienteid=0
    /*
     $sql="select if(b.accountid_equivale > 0,b.accountid_equivale,b.accountid) equivale
    from  accountcliente a,accountcliente b
    WHERE a.clienteid=0 and a.accountid=".$_SESSION['myAccount']."
    and a.accountid_equivale <> 0
    and a.accountid_equivale = b.accountid
    and b.clienteid = ".$_SESSION['cliente_id']."
    UNION
    select accountid_equivale equivale
    from  accountcliente
    WHERE clienteid = ".$_SESSION['cliente_id']." and accountid=".$_SESSION['myAccount']."
    and accountid_equivale > 0
    limit 1";
    */
    $sql="select a.accountid_equivale equivale
    from  accountcliente a,accounts b
    WHERE a.clienteid=0 and a.accountid=".$_SESSION['myAccount']."
    and a.accountid_equivale <> 0
    and a.accountid_equivale = b.accountid
    and b.cliente_id = '".$_SESSION['cliente_id']."'
    UNION
    select accountid_equivale equivale
    from  accountcliente
    WHERE clienteid = '".$_SESSION['cliente_id']."' and accountid=".$_SESSION['myAccount']."
    and accountid_equivale > 0
    limit 1";
    
    
    
    
    $this->MyDatabase();
    $result = $this->gDB->Execute($sql);
    
    if ($result === false) {
    	echo $this->gDB->ErrorMsg( );
    	//echo "<br><h3>SQL</h3>=$sql";
    }
    
    $account=$_SESSION['myAccount'];
  	if( $result->fields("equivale")) {
  		$account=$result->fields("equivale");
  	}
  		
    
    //donde dice $account decia $_SESSION['myAccount'] Pablo 250714
		

    $sql = "SELECT ag.groupid FROM groupaccounts ag "
          ." LEFT JOIN groupactions ga ON ag.groupid=ga.groupid "
          ." LEFT JOIN actions a ON ga.actionid = a.actionid "
          ." WHERE ag.accountid=".$account
          ."          AND (upper(a.actionname)='".strtoupper($actionName)."'";
	if ($hastaaction > 0 )	 {
          $sql .= "   or (ga.actionid between ".$actionName." and ".$hastaaction.") ";
	} else {
		  $sql .="    or ga.actionid = '".$actionName."'";
		  $sql .="    or ga.actionid = ".$raiz;
	}
		  
	$sql .= ")";

	$this->MyDatabase();
    $result = $this->gDB->Execute($sql);

    if ($result === false) {
      echo $this->gDB->ErrorMsg( );
      echo "<br><h3>SQL</h3>=$sql";
    }

    if($result->fields("groupid"))
      return true;

    $sql = "SELECT ag.accountid FROM accountaction ag "
          ." LEFT JOIN actions a ON ag.actionid = a.actionid "
          ." WHERE ag.accountid=".$account
          ."          AND (upper(a.actionname)='".strtoupper($actionName)."'";
//          ."          or ag.actionid = '".$actionName."'"  ;

	if ($hastaaction > 0 )	 {
          $sql .= "   or (ag.actionid between ".$actionName." and ".$hastaaction.") ";
	} else {
		  $sql .="    or ag.actionid = '".$actionName."'";
		  $sql .="    or ag.actionid = ".$raiz;
	}
		  
	$sql .= ")";

    $result = $this->gDB->Execute($sql);

    if ($result === false) {
      echo $this->gDB->ErrorMsg( );
      echo "<br><h3>SQL</h3>=$sql";
    }

    if($result->fields("accountid"))
      return true;
    else
      return false;

  }

  /**
  * Method to check if the person can access to an empresa 
  * 
  * Pablo 21/08/09
  * @public
  * 
  * retorna true si no tiene permiso especifico para ninguna empresa ( o sea que permite todas)
  * o tiene permiso para la empresa solicitada
  * @returns boolean
  */
  function isAllowedEmpresa($empresaid)
  {
    $_SESSION['http_referer'] = &$_SERVER['PHP_SELF'];

    if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount']))
    {
      $this-> GoToLoginPage( );
    }

    //Busca si hay un account que equivale
    //baja s{olo un nivel sobre el accountcliente cuando el clienteid=0
    /*
    $sql="select if(b.accountid_equivale > 0,b.accountid_equivale,b.accountid) equivale
    from  accountcliente a,accountcliente b
    WHERE a.clienteid=0 and a.accountid=".$_SESSION['myAccount']."
    and a.accountid_equivale <> 0
    and a.accountid_equivale = b.accountid
    and b.clienteid = ".$_SESSION['cliente_id']."
    UNION
    select accountid_equivale equivale
    from  accountcliente
    WHERE clienteid = ".$_SESSION['cliente_id']." and accountid=".$_SESSION['myAccount']."
    and accountid_equivale > 0		
    limit 1";
    */
    $sql="select a.accountid_equivale equivale
    from  accountcliente a,accounts b
    WHERE a.clienteid=0 and a.accountid=".$_SESSION['myAccount']."
    and a.accountid_equivale <> 0
    and a.accountid_equivale = b.accountid
    and b.cliente_id = '".$_SESSION['cliente_id']."'
    UNION
    select accountid_equivale equivale
    from  accountcliente
    WHERE clienteid = '".$_SESSION['cliente_id']."' and accountid=".$_SESSION['myAccount']."
    and accountid_equivale > 0
    limit 1";
    
    $this->MyDatabase();
    $result = $this->gDB->Execute($sql);
    
    if ($result === false) {
    	echo $this->gDB->ErrorMsg( );
    	//echo "<br><h3>SQL</h3>=$sql";
    }
    
    $account=$_SESSION['myAccount'];
  	if( $result->fields("equivale")) {
  		$account=$result->fields("equivale");
  	}
  		
    
    //donde dice $account decia $_SESSION['myAccount'] Pablo 250714
    
    //Si un account no tiene accountempresa, asume todos y continua por lo grupos . Pablo 280809
    $sql = "SELECT accountid FROM accountempresa  "
          ." WHERE accountid=".$account
		  ." limit 1";

	$this->MyDatabase();
	 $result = $this->gDB->Execute($sql);

    if ($result === false) {
    	echo $this->gDB->ErrorMsg( );
    	//echo "<br><h3>SQL</h3>=$sql";
    }

    if( $result->fields("accountid"))
    {
    	//Si un account tiene accountempresa=empresaid, devuelve true Pablo 280809

   	$sql = "SELECT accountid FROM accountempresa "
    	." WHERE accountid=".$account
    	."   AND empresaid=".$empresaid;

     $result = $this->gDB->Execute($sql);

     if ($result === false) {
      echo $this->gDB->ErrorMsg( );
      echo "<br><h3>SQL</h3>=$sql";
     }

     if($result->fields("accountid"))
	     return true;
     else
    	 return false;

    }
    
    
    //Si el grupo de un account no tiene groupempresa, asume todos y devuelve true Pablo 210809
    $sql = "SELECT ag.groupid FROM groupaccounts ag,groupempresa ga  "
          ." WHERE ag.accountid=".$account
		  ."          AND ag.groupid=ga.groupid limit 1";

    $result = $this->gDB->Execute($sql);

    if ($result === false) {
      echo $this->gDB->ErrorMsg( );
      echo "<br><h3>SQL</h3>=$sql";
    }

    if( $result->fields("groupid"))
	{

	}else {
	      return true;
	}

    //Si el grupo de un account tiene groupempresa=empresaid, devuelve true Pablo 210809
	
    $sql = "SELECT ag.groupid FROM groupaccounts ag "
          ." LEFT JOIN groupempresa ga ON ag.groupid=ga.groupid "
          ." WHERE ag.accountid=".$account
          ."          AND ga.empresaid=".$empresaid;
		
	

    $result = $this->gDB->Execute($sql);

    if ($result === false) {
      echo $this->gDB->ErrorMsg( );
      echo "<br><h3>SQL</h3>=$sql";
    }

    if($result->fields("groupid"))
      return true;
    else
      return false;

  }


  /**
   * Method to check if the person can access to an jurisdiccion
   *
   * Pablo 23/07/14
   * @public
   *
   * retorna true si no tiene permiso especifico para ninguna jurisdiccion ( o sea que permite todas)
   * o tiene permiso para la jur solicitada
   * @returns boolean
   */
  function isAllowedJurisdiccion($id)
  {
  	$_SESSION['http_referer'] = &$_SERVER['PHP_SELF'];
  
  	if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount']))
  	{
  		$this-> GoToLoginPage( );
  	}

  	//Busca si hay un account que equivale
  	

    //Busca si hay un account que equivale
    //baja s{olo un nivel sobre el accountcliente cuando el clienteid=0
    /*
     $sql="select if(b.accountid_equivale > 0,b.accountid_equivale,b.accountid) equivale
    from  accountcliente a,accountcliente b
    WHERE a.clienteid=0 and a.accountid=".$_SESSION['myAccount']."
    and a.accountid_equivale <> 0
    and a.accountid_equivale = b.accountid
    and b.clienteid = ".$_SESSION['cliente_id']."
    UNION
    select accountid_equivale equivale
    from  accountcliente
    WHERE clienteid = ".$_SESSION['cliente_id']." and accountid=".$_SESSION['myAccount']."
    and accountid_equivale > 0
    limit 1";
    */
    $sql="select a.accountid_equivale equivale
    from  accountcliente a,accounts b
    WHERE a.clienteid=0 and a.accountid=".$_SESSION['myAccount']."
    and a.accountid_equivale <> 0
    and a.accountid_equivale = b.accountid
    and b.cliente_id = '".$_SESSION['cliente_id']."'
    UNION
    select accountid_equivale equivale
    from  accountcliente
    WHERE clienteid = '".$_SESSION['cliente_id']."' and accountid=".$_SESSION['myAccount']."
    and accountid_equivale > 0
    limit 1";
    
  	$this->MyDatabase();
  	$result = $this->gDB->Execute($sql);
  	
  	if ($result === false) {
  		echo $this->gDB->ErrorMsg( );
  		//echo "<br><h3>SQL</h3>=$sql";
  	}
  	
  	$account=$_SESSION['myAccount'];
  	if( $result->fields("equivale")) {
  		$account=$result->fields("equivale");
  	}
  	 
  	
  	
  	//Si un account no tiene accountjurisdiccion, asume todos . Pablo 240714
  	$sql = "SELECT accountid FROM accountjurisdiccion  "
  			." WHERE accountid=".$account
  			." limit 1";
  
  	$this->MyDatabase();
  	$result = $this->gDB->Execute($sql);
  
  	if ($result === false) {
  		echo $this->gDB->ErrorMsg( );
  		//echo "<br><h3>SQL</h3>=$sql";
  	}
  
  	if( $result->fields("accountid"))
    {
  			//Si un account tiene accountjurisdiccion=id, devuelve true Pablo 240714
  
  		$sql = "SELECT accountid FROM accountjurisdiccion "
    	." WHERE accountid=".$account
    	."   AND jurisdiccionid=".$id;
  
  		$result = $this->gDB->Execute($sql);
  
  		if ($result === false) {
  		echo $this->gDB->ErrorMsg( );
  		//echo "<br><h3>SQL</h3>=$sql";
  		}
  
  		if($result->fields("accountid"))
  		return true;
  		else
  				return false;
  
  	}
  
  RETURN true;
  
  }
  
  /**
   * Method to check if the person can access to an vehiculo
   *
   * Pablo 11/10/18
   * @public
   *
   * retorna true si no tiene permiso especifico para ninguna vehiculo ( o sea que permite todas)
   * o tiene permiso para el vehiculo solicitada
   * @returns boolean
   */
  function isAllowedVehiculo($id)
  {
      $_SESSION['http_referer'] = &$_SERVER['PHP_SELF'];
      
      if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount']))
      {
          $this-> GoToLoginPage( );
      }
      
      //Busca si hay un account que equivale
      
      
      //Busca si hay un account que equivale
      //baja s{olo un nivel sobre el accountcliente cuando el clienteid=0
      /*
       $sql="select if(b.accountid_equivale > 0,b.accountid_equivale,b.accountid) equivale
       from  accountcliente a,accountcliente b
       WHERE a.clienteid=0 and a.accountid=".$_SESSION['myAccount']."
       and a.accountid_equivale <> 0
       and a.accountid_equivale = b.accountid
       and b.clienteid = ".$_SESSION['cliente_id']."
       UNION
       select accountid_equivale equivale
       from  accountcliente
       WHERE clienteid = ".$_SESSION['cliente_id']." and accountid=".$_SESSION['myAccount']."
       and accountid_equivale > 0
       limit 1";
       */
      $sql="select a.accountid_equivale equivale
    from  accountcliente a,accounts b
    WHERE a.clienteid=0 and a.accountid=".$_SESSION['myAccount']."
    and a.accountid_equivale <> 0
    and a.accountid_equivale = b.accountid
    and b.cliente_id = '".$_SESSION['cliente_id']."'
    UNION
    select accountid_equivale equivale
    from  accountcliente
    WHERE clienteid = '".$_SESSION['cliente_id']."' and accountid=".$_SESSION['myAccount']."
    and accountid_equivale > 0
    limit 1";
      
      $this->MyDatabase();
      $result = $this->gDB->Execute($sql);
      
      if ($result === false) {
          echo $this->gDB->ErrorMsg( );
          //echo "<br><h3>SQL</h3>=$sql";
      }
      
      $account=$_SESSION['myAccount'];
      if( $result->fields("equivale")) {
          $account=$result->fields("equivale");
      }
      
      
      
      //Si un account no tiene accountjurisdiccion, asume todos . Pablo 240714
      $sql = "SELECT accountid FROM accountvehiculo  "
          ." WHERE accountid=".$account
          ." limit 1";
          
          $this->MyDatabase();
          $result = $this->gDB->Execute($sql);
          
          if ($result === false) {
              echo $this->gDB->ErrorMsg( );
              //echo "<br><h3>SQL</h3>=$sql";
          }
          
          if( $result->fields("accountid"))
          {
              //Si un account tiene accountjurisdiccion=id, devuelve true Pablo 240714
              
              $sql = "SELECT accountid FROM accountvehiculo "
                  ." WHERE accountid=".$account
                  ."   AND vehiculo_id=".$id;
                  
                  $result = $this->gDB->Execute($sql);
                  
                  if ($result === false) {
                      echo $this->gDB->ErrorMsg( );
                      //echo "<br><h3>SQL</h3>=$sql";
                  }
                  
                  if($result->fields("accountid"))
                      return true;
                      else
                          return false;
                          
          } 
          
          RETURN 999;
          
  }
  
  
  /**
  * Method to check the username and password.
  * @public
  */
  function VerifyUser($username, $password,$md5=USE_MD5)
  {
    #echo '$username: '.$username.'<br>';
    #echo '$password: '.$password.'<br>';
    $this->mUsername = ToSQL($value=htmlspecialchars($username),$type='text');
    #echo 'USE_MD5: '.USE_MD5."<br>";
    if ($md5)
      $password = ToSQL($value=md5(htmlspecialchars($password)),$type='text');
    else
      $password = ToSQL($value=htmlspecialchars($password),$type='text');
    #echo '$password: '.$password.'<br>';

    #
    # The reason we read the username first is, if the bad_attempts
    # is on, than we will increase the counter by one for each try,
    # for that user.
    #
    $sql = "SELECT * FROM accounts WHERE username = $this->mUsername";
    #echo '$sql: '.$sql.'<br>';
	$this->MyDatabase();
    $result = $this->gDB->Execute($sql);

    if ($result === false) {
      $this->SetErrorMessage('error reading: '.$this->gDB->ErrorMsg( ),$sql);
      if ($_SESSION['IS_ERROR_REPORTING'])
        $this-> EchoError(  $this->gDB->ErrorMsg(),$sql  );
      return false;
    }

    if($result->fields("accountid") > 0) {
      $this->mAccountID = (int)$result->fields("accountid");
      $this->mAccountTries = (int)$result->fields("tries");
      $this->mLastTriedDate = (int)$result->fields("lasttrieddate");
      $this->mNroCliente = (int)$result->fields("cliente_id");
      $this->FechaDesdeReporte = $result->fields("fecha_desde_reporte");
      #echo '$this->mAccountID: '.$this->mAccountID.'<br>';
      #echo '$this->mNroCliente: '.$this->mNroCliente.'<br>';
      $sql = "SELECT password FROM accounts "
            ."WHERE accountid = $this->mAccountID "
            ."AND password=$password";
      #echo '$sql: '.$sql.'<br>';

      $result = $this->gDB->Execute($sql);

      if ($result===false) {
        $this-> SetErrorMessage($this->gDB->ErrorMsg( ),$sql);
        if ($_SESSION['IS_ERROR_REPORTING'])
          $this-> EchoError(  $this->gDB->ErrorMsg(),$sql  );
        return false;
      }

      if($result-> RecordCount() <= 0) {
        $this->BadAttempt( );
        $this->mAccountID = null;
        $this->mHierarchy = null;
        $this->mDbCliente = null;
        $this->ClienteID = null;
        $this->mHostCliente = null;
        $this->FechaDesdeReporte = null;
        $this-> SetErrorMessage("Usuario o contraseña inválidos.");
        return false;
      }
      else {
        $sql = "SELECT * FROM cliente "
              ."WHERE cliente_id = '$this->mNroCliente'";
        #echo '$sql: '.$sql.'<br>';
        $result = $this->gDB->Execute($sql);

        if ($result === false) {
          $this->SetErrorMessage('error reading: '
                                .$this->gDB->ErrorMsg( ),$sql);
          if ($_SESSION['IS_ERROR_REPORTING'])
            $this-> EchoError(  $this->gDB->ErrorMsg(),$sql  );
          return false;
        }
        else {
          $this->mDbCliente = $result->fields("cliente_db");
          $this->ClienteId = $result->fields("cliente_id");
          $this->mHostCliente = $result->fields("cliente_host");
        }
      }

      if ($this->mAccountTries > 0)
        $this-> ResetAccountTries($this->mAccountID);
      #
      # We will get the highest level of hierarchy s/he has. If the person
      # is defined in multiple level of groups, we will give the highest,
      # which is the lowest number.
      #
      #$sql = "SELECT MIN(hierarchy) as MIN FROM groups "
      #     ."    WHERE groupid IN (SELECT groupid FROM groupaccounts "
      #     ."                        WHERE accountid=$this->mAccountID)";

      $sql = "SELECT MIN(hierarchy) as min FROM groups g"
            ." LEFT JOIN groupaccounts ga ON g.groupid = ga.groupid "
            ." WHERE ga.accountid=$this->mAccountID";

      $result = $this->gDB->Execute($sql);

      if ($result===false) {
        $this-> SetErrorMessage($this->gDB->ErrorMsg( ),$sql);
        if ($_SESSION['IS_ERROR_REPORTING'])
          $this-> EchoError(  $this->gDB->ErrorMsg(),$sql  );
        return false;
      }
      $this->mHierarchy = $result-> fields("min");
      return true;
    }
    else {
      #if ($_SESSION['LOG_ACTIVITIES']) {
        $WriteLog = new WriteLog( );
        $WriteLog-> SetAccountId( 0 );
        $WriteLog-> SetUserName($this->mUsername);
        $WriteLog-> SetActivityId( 1 );

        $WriteLog-> WriteToLog( );
        unset ($WriteLog);
      #}
      $this-> SetErrorMessage("Usuario o contraseña inválidos.");
      return false;
    }
  }

  /**
  * Method to get the accountid. You have to verify the the account to get it.
  * @public
  * @returns integer
  */
  function GetAccountID( )
  {
    return $this->mAccountID;
  }

  /**
  * Method to get the hierarch level for the user.
  * You have to verify the account to get it.
  * @public
  * @returns integer
  */
  function GetHierarchy( )
  {
    return $this->mHierarchy;
  }

  function GetDbCliente( )
  {
    return $this->mDbCliente;
  }
  function GetHostCliente( )
  {
    return $this->mHostCliente;
  }
  
  function GetClienteId( )
  {
    return $this->ClienteId;
  }
  function GetFechaDesdeReporte( )
  {
    return $this->FechaDesdeReporte;
  }
  /**
  * Method to check the bad attempt number of tries from the Accounts class.
  * @public
  * @returns integer
  */
  function BadAttempt( )
  {
    #echo 'function BadAttempt: <br>';
    #if ($_SESSION['LOG_ACTIVITIES']) {
      $WriteLog = new WriteLog( );
      $WriteLog-> SetAccountId($this->mAccountID);
      $WriteLog-> SetUserName($this->mUsername);
      $WriteLog-> SetActivityId( 1 );

      $WriteLog-> WriteToLog( );
      unset ($WriteLog);
    #}

    $Accounts = new Accounts( );

    if (!$Accounts->BadAttempt( $this->mAccountID,$this->mAccountTries,
                                $this->mLastTriedDate) )
      $this->SetErrorMessage($Accounts->GetErrorMessage( ));
    unset ($Accounts);
  }

  /**
  * Method to write to the log for successful Logins.
  * @public
  * @returns void
  */
  function SuccessfulLogin( )
  {
    global $cant_usuarios;

    #if ($_SESSION['LOG_ACTIVITIES'])
    #{
      $WriteLog = new WriteLog( );
      $WriteLog-> SetAccountId($this->mAccountID);
      $WriteLog-> SetUserName($this->mUsername);
      $WriteLog-> SetActivityId( 3 );

      $WriteLog-> WriteToLog( );
      unset ($WriteLog);
    #}
  }

  /**
  * Method to reset the tries counter from the table after a successful login.
  * @public
  * @returns void
  */
  function ResetAccountTries($accountid)
  {
    $Accounts = new Accounts( );
    $Accounts->Field($Accounts->mKeyName,$accountid);
    $Accounts->Field("tries",0);
    $Accounts->Field("lasttrieddate",0);

    $Accounts-> Update( );

    unset ($Accounts);
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
  * Method to print any error message generated by ADODB to the screen.
  * @public
  * @returns void
  */
  function EchoError($message,$sql="")
  {
//  	die();
    $myForm = new Form("dummy");

    $myForm-> SetNumberOfColumns( 1 );
    $myForm-> SetCellSpacing( 1 );
    $myForm-> SetCellPadding( 5 );
    $myForm-> SetBorder ( 0 );
    $myForm-> SetAlign ("center");
    $myForm-> SetTableWidth ("60%");
    $myForm-> SetTableHeight (null);
    $myForm-> SetCSS ( $_SESSION["CSS"] );
    $myForm-> SetEmptyCells (true);
    $myForm-> SetFormTagRequired (false);
    $myForm-> SetFormHeader("Error Ocurrido");

    $myForm-> AddFormElementToNewLine(new Label($name="lb1",$value="Nombre del Script"));
    $Label = new Label("lbl2",$_SERVER['SCRIPT_FILENAME']);
    $Label-> SetClass("DataTD");
    $myForm-> AddFormElementToNewLine($Label);

    $Label-> SetColSpan( $myForm->GetNumberOfColumns( ) );
    $Label-> SetClass("");
    $Label-> SetValue("Message");
    $myForm-> AddFormElementToNewLine($Label);

    $Label-> SetColSpan( $myForm->GetNumberOfColumns( ) );
    $Label-> SetClass("DataTD");
    $Label-> SetValue($message);
    $myForm-> AddFormElementToNewLine($Label);

    if ($sql)
    {
      $Label-> SetColSpan( $myForm->GetNumberOfColumns( ) );
      $Label-> SetClass("");
      $Label-> SetValue("SQL");
      $myForm-> AddFormElementToNewLine($Label);
      $Label-> SetClass("DataTD");
      $Label-> SetValue($sql);
      $myForm-> AddFormElementToNewLine($Label);
    }


    echo $myForm-> GetFormInTable( );
    die;
  }

  /**
  * Method to send a Login screen.
  * @public
  */
  function PromptLogin($FormElements)
  {
    $myForm = new Form("login");
    $myForm-> SetNumberOfColumns( 2 );
    $myForm-> SetCellSpacing( 1 );
    $myForm-> SetCellPadding( 5 );
    $myForm-> SetBorder ( 0 );
    $myForm-> SetAlign ("center");
    $myForm-> SetTableWidth ("400");
    $myForm-> SetTableHeight (null);
    $myForm-> SetCSS ( $_SESSION["CSS"] );
    $myForm-> SetEmptyCells (false);
    $myForm-> SetFormHeader("Login");

    $self = basename($_SERVER['PHP_SELF']);

    $myForm-> SetAction($self);

    $myForm-> SetErrorMessage($FormElements['__error']);

    $myForm-> AddFormElement(new Label($name="lbl1",$value="Usuario :"));

    $myForm-> AddFormElement(new TextField($name="username",$value=$FormElements['username'],$size=20,$maxLength=35,$displayonly=false));

    $myForm-> AddFormElement(new Label($name="lbl2",$value="Contraseña :"));

    $myForm-> AddFormElement(new Password($name="password",$value="",$size=10,$maxlength=10,$extra=""));

    # lets add some buttons to our form

    $buttons = new ObjectArray("buttons");
    $buttons->AddObject(new SubmitButton( $name="Submit",$value="Seguir"));
    $buttons->AddObject(new ResetButton( $name="Reset",$value="Restablecer"));

    $buttons->SetCellAttributes(array("align"=>"middle"));
    $buttons->SetColSpan( $myForm-> GetNumberOfColumns() );

    $myForm-> AddFormElementToNewLine ($buttons);


    echo $myForm-> GetFormInTable();
  }

  /**
  * Method to create MD5 password for initial admin use.
  * @public
  */
  function PromptMD5Password($FormElements)
  {
    $myForm = new Form("md5");
    $myForm-> SetNumberOfColumns( 2 );
    $myForm-> SetCellSpacing( 1 );
    $myForm-> SetCellPadding( 4 );
    $myForm-> SetBorder ( 0 );
    $myForm-> SetAlign ("center");
    $myForm-> SetTableWidth ("400");
    $myForm-> SetTableHeight (null);
    $myForm-> SetCSS ( $_SESSION["CSS"] );
    $myForm-> SetEmptyCells (true);
    $myForm-> SetFormHeader("Creación de Contraseña MD5");

    $self = basename($_SERVER['PHP_SELF']);

    $myForm-> SetAction($self);

    $myForm-> SetErrorMessage($FormElements['__error']);

    $myForm-> AddFormElement(new Label($name="lbl2",$value="Contraseña :"));

    $myForm-> AddFormElement  (new TextField($name="password",$value=$FormElements['password'],$size=20,$maxLength=20,$displayonly=false));

    $myForm-> AddFormElement(new Label($name="lbl1",$value="Contraseña MD5 :"));

    $myForm-> AddFormElement  (new TextField($name="md5",$value=$FormElements['md5'],$size=50,$maxLength=50,$displayonly=false));

    # lets add some buttons to our form

    $buttons = new ObjectArray("buttons");
    $buttons->AddObject(new SubmitButton( $name="Submit",$value="Crear MD5"));

    $buttons->SetCellAttributes(array("align"=>"middle"));
    $buttons->SetColSpan( $myForm-> GetNumberOfColumns() );
    $buttons->SetClass("FieldCaptionTD");

    $myForm-> AddFormElementToNewLine ($buttons);


    $value = "<a class=\"" .$_SESSION["CSS"] ."LinkButton\" href=\"adminmenu.php\"><-- Volver al Menú Seguridad</a>";
    $passtru = new PassTru($value);
    $passtru->SetColSpan(2);
    #$passtru->SetClass("FieldCaptionTD");

    $myForm-> AddFormElement  ($passtru);


    echo $myForm-> GetFormInTable();
  }
}

?>