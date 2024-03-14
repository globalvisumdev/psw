<?php
session_start();

include_once "securityConfig.inc.php";
include_once(ADODB_DIRECTORY . "/adodb.inc.php");
include_once('theme.inc.php');
include_once "commonFunctions.php";

require_once "Accounts.class.php";
require_once "MyDatabase.class.php";
require_once "WriteLog.class.php";
require_once "Form.class.php";



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
    function Security()
    {
        // Set up database connection

        $this->MyDatabase();

        if ($_SESSION['TIMEOUT_SECONDS'] > 0) {
            if (
                !isset($_SESSION['timestamp']) and isset($_SESSION['myAccount']) and
                $_SESSION['myAccount'] != null
            ) {
                $_SESSION['timestamp'] = time();
            } elseif (isset($_SESSION['timestamp']) and isset($_SESSION['myAccount'])) {
                $timeout = $_SESSION['timestamp'] + $_SESSION['TIMEOUT_SECONDS'];
                if ($timeout < time()) {
                    session_destroy();
                    session_start();
                    include "securityConfig.inc.php";
                } else
                    $_SESSION['timestamp'] = time();
            }
        }
    }

    /**
     * Determines the login page's path and redirects it to there.
     * @public
     * @returns boolean
     */
    function GoToLoginPage()
    {
        $_SESSION['loginPrompting'] = "1";

   $protocol='http';
    if ($_SERVER['HTTPS']=='on') $protocol='https';
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) $protocol='https'; //si viene por proy no trae HTTPS=on, asumo que es https
    $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $_SERVER['SERVER_NAME'] . $port;
    $uri = $protocol . '://' . $host ;
    
    $pathToLogin = $uri.strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"));
    
//     $pathToLogin = "http".($_SERVER["HTTPS"]=="on"?"s":"")
//                   ."://".$_SERVER["HTTP_HOST"]
//                   #.dirname($_SERVER['PHP_SELF'])
//                   .strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"));
    
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
    function GotoNotAuthorized()
    {
        $_SESSION['loginPrompting'] = "1";

 $protocol='http';
    if ($_SERVER['HTTPS']=='on') $protocol='https';
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) $protocol='https'; //si viene por proy no trae HTTPS=on, asumo que es https
    $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $_SERVER['SERVER_NAME'] . $port;
    $uri = $protocol . '://' . $host ;
    
    $pathToPage = $uri
    .strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"))
    ."psw/"
        ."notAuthorized.php";
        
//     $pathToPage = "http".($_SERVER["HTTPS"]=="on"?"s":"")
//                   ."://".$_SERVER["HTTP_HOST"]
//                   .strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"))
//                   ."psw/"
//                   ."notAuthorized.php";
        #echo '$pathToPage: '.$pathToPage.'<br>';
        header("Location: $pathToPage");
        exit;
    }

    /**
     * Redirects it to the page you want.
     * @public
     * @returns boolean
     */

    function GotoThisPageOriginal($page = 'login', $url = null){
        $_SESSION['loginPrompting'] = "1";
 
    $protocol='http';
    if ($_SERVER['HTTPS']=='on') $protocol='https';
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) $protocol='https'; //si viene por proy no trae HTTPS=on, asumo que es https
    $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $_SERVER['SERVER_NAME'] . $port;
    $uri = $protocol . '://' . $host ;
    
    $pathToPage = $uri
    .strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"))
    .$page;
    
//     $pathToPage = "http".($_SERVER["HTTPS"]=="on"?"s":"")
//                   ."://".$_SERVER["HTTP_HOST"]
//                   .strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"))
//                   .$page;

        if ($page == 'login') {
         $pathToPage = $uri
                      .substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],'/',1))
                      ."/psw/login.php";
                      //                       $pathToPage = "http".($_SERVER["HTTPS"]=="on"?"s":"")
//                       ."://".$_SERVER["HTTP_HOST"]
//                       .substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],'/',1))
//                       ."/psw/login.php";
                      
        }


        if ($url)
            $pathToPage .= "?$url";

        header("Location: $pathToPage");
        exit;
    }

    function GotoThisPage($page){
		echo json_encode( array( 
			"allowed" => false,
			"redirectPage" =>  $page
        ));
    }

    /**
     * Method to return reverse value of isPermittedTo.
     * @public
     * @returns boolean
     */
    function isNotAllowedTo($actionName, $hastaaction = 0)
    {
        if ($this->isPermittedTo($actionName, $hastaaction))
            return false;
        else
            return true;
    }

    /**
     * synonyms of isPermittedTo.
     * @public
     * @returns boolean
     */

    function isAllowedTo($actionName, $hastaaction = 0)
    {
        return $this->isPermittedTo($actionName, $hastaaction);
    }

    //   function isAllowedTo($actionName,$hastaaction=0)
    //   {
    //     $a = $this-> isPermittedTo($actionName,$hastaaction);
    //     if ($a == "loginPage") {
    //         $this-> GoToLoginPage( );
    //         return false;
    //     }
    //     return $a;
    //   }

    function isAllowedCmd($actionName, $hastaaction = 0)
    {
        $a = $this->isPermittedTo($actionName, $hastaaction);
        if ($a == "loginPage") {
            return false;
        }
        return $a;
    }

    /**
     * Method to check if the person can do the action.
     * Pablo 21/08/09 que actue sobre tabla de permisos por account 
     * y que el parametro se compare con el actionid
     * y que se compare con el actionid raiz (100,200,300,400)
     * @public
     * @returns boolean
     */
    function isPermittedTo($actionName, $hastaaction = 0){
        $_SESSION['http_referer'] = &$_SERVER['PHP_SELF'];

        if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])) {

            // return "loginPage";
            $this->GoToLoginPage();
        }
        $account = intval($_SESSION['myAccount']);
        $hastaaction = (int) $hastaaction;

        //chequea validez del account

        $sql = "select cliente_nivel,a.cliente_id
            ,(select a.accountid_equivale
            from  accountcliente a,accounts b
            WHERE a.clienteid=0 and a.accountid=" . $account . "
            and a.accountid_equivale <> 0
            and a.accountid_equivale = b.accountid
            and b.cliente_id = '" . intval($_SESSION['cliente_id']) . "'
            UNION
            select accountid_equivale equivale
            from  accountcliente
            WHERE clienteid = '" . intval($_SESSION['cliente_id']) . "' and accountid=" . $account . "
            and accountid_equivale > 0
            limit 1) equivale

            from  accounts a,cliente c
            WHERE a.accountid=" . $account . "
            and c.cliente_id = " . intval($_SESSION['cliente_id']) . "
            and 
            (validez_desde is null or validez_desde < '2000-00-00 00:00:00' or validez_desde <= now())
            and (validez_hasta is null or validez_hasta < '2000-00-00 00:00:00' or validez_hasta >= now())";

        if (substr($_SESSION['cliente_id'], 0, 1) == 'E') { //cliente externo
            $sql = "select 0 cliente_nivel,0 cliente_id, null equivale
                from  accounts a
                WHERE a.accountid=" . $account . "
                and
                (validez_desde is null or validez_desde < '2000-00-00 00:00:00' or validez_desde <= now())
                and (validez_hasta is null or validez_hasta < '2000-00-00 00:00:00' or validez_hasta >= now())";
        }

        $c = 0;
        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                $c = 1; //$result->fields("c");
                $nivel_cliente = $row->cliente_nivel;
                $cliente_ac = $row->cliente_id;
                $equivale = $row->equivale;
            }
        } catch (PDOException  $e) {
        }

        if ($c == 0) {
            unset($_SESSION['myAccount']);

            // return "loginPage";
            $this->GoToLoginPage();
        }

        //Me aseguro que actioname es un codigo, no usa cadena
        $q1 = "select actionid,actionclase,actionraiz from actions where upper(actionname)= :actname or actionid= :actid";

        //    $this->MyDatabase();
        $actionraiz = "";
        try {
            $sth = $this->pDBisla->prepare($q1, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':actname' => strtoupper($actionName), ':actid' => intval($actionName)));
            $row = $sth->fetchAll();

            $actionName = $row[0]['actionid'];
            $actionclase = $row[0]['actionclase'];
            $actionraiz = $row[0]['actionraiz'];
        } catch (PDOException  $e) {
            //echo $e;

            return false;


        }

        if (intval($actionName) == 0) {
            return false;


        }

        $raiz = 0;
        if (intval($actionName) > 100) {
            $raiz = floor(intval($actionName) / 100) * 100;
        }

        if ($actionraiz > '') $raiz = $actionraiz;

        $ljdenied = "";
        $whdenied = "";

        if ($_SESSION['myHierarchy'] != 1) {

            $ljdenied = " left join actionclientedenied acd on acd.actionid=$actionName and acd.cliente_id= " . intval($_SESSION['cliente_id']);
            $whdenied = " and acd.actionid is null";

        }

        //si es de sisca y el permiso es publico (no es interno de sisca)
        if ($cliente_ac == 1 and $actionclase == 0 and $nivel_cliente > 0) {

            $account = intval($_SESSION['myAccount']);

            //devuelve el mejor nivel de un usuario
            $sql = "select gr_nivel,g.groupid from groupaccounts ag ,groups g
                where accountid = $account
                and g.groupid = ag.groupid
                and gr_nivel > 0
                order by gr_nivel limit 1
                ";

            $nivel = 0;
            try {
                $resultp = $this->pDBisla->query($sql);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    $nivel = $row->gr_nivel;
                }
            } catch (PDOException  $e) {
                return false;
                echo "Error de lectura de nivel.Reintente.";
                die();
            }

            $nivel = max($nivel, $nivel_cliente); //toma el peor nivel (1=mejor)

            //me aseguro que exista el nivel
            $q1 = "select max(gr_nivel) m from groups where gr_nivel <=" . $nivel;
            try {
                $resultp = $this->pDBisla->query($q1);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    $nivel = $row->m;
                }
            } catch (PDOException  $e) {

                return false;
                echo "Error de lectura de nivel.Reintente.";
                die();
            }

            $sql = "SELECT distinct a.actionid FROM groups g
                LEFT JOIN groupactions ga ON g.groupid=ga.groupid 
                LEFT JOIN actions a ON ga.actionid = a.actionid
                $ljdenied 
                    ";
            $sql .= "   WHERE (gr_nivel = '$nivel')
            $whdenied
            AND (";
            if ($hastaaction > 0) {
                $sql .= "   (ga.actionid between " . $actionName . " and " . $hastaaction . ") ";
            } else {
                $sql .= "    ga.actionid = '" . $actionName . "'";
                $sql .= "    or find_in_set(ga.actionid,'" . $raiz . "') > 0";
            }
            $sql .= ")";

            $sql .= " union SELECT aae_actionid FROM accountaction_eventual "
                . " LEFT JOIN actions a ON aae_actionid = a.actionid 
                $ljdenied
                WHERE aae_accountid=" . $account
                . " and aae_fecha_desde < now() and aae_fecha_hasta > now()
            $whdenied
            AND (";

            if ($hastaaction > 0) {
                $sql .= "   (aae_actionid between " . $actionName . " and " . $hastaaction . ") ";
            } else {
                $sql .= "    aae_actionid = '" . $actionName . "'";
                $sql .= "    or find_in_set(aae_actionid,'" . $raiz . "') > 0";
            }
            $sql .= ")";

            $sql .= " union SELECT gae_actionid FROM groupaccounts ag
            left join accounts on accounts.accountid = ag.accountid
            ,groupaction_eventual ge
            LEFT JOIN actions a ON gae_actionid = a.actionid
            $ljdenied
            WHERE ag.accountid=" . $account . "
            and gae_groupid = ag.groupid
            and find_in_set(accounts.cliente_id,gae_clientes) > 0
            and gae_fecha_desde < now() and gae_fecha_hasta > now()
            $whdenied
            AND (";
            if ($hastaaction > 0) {
                $sql .= "   (gae_actionid between " . $actionName . " and " . $hastaaction . ") ";
            } else {
                $sql .= "    gae_actionid = '" . $actionName . "'";
                $sql .= "    or find_in_set(gae_actionid,'" . $raiz . "') > 0";
            }
            $sql .= ")";


            try {
                $resultp = $this->pDBisla->query($sql);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {

                    return true;
                }
            } catch (PDOException  $e) {
            }

            return false;
        }

        $account = intval($_SESSION['myAccount']);
        if ($equivale > 0) {
            $account = $equivale;
        }

        $modfiltra = "";
        if ($_SESSION['myHierarchy'] != 1) {
            if ($raiz == 800) {
                $modTAV = 0;
                $q1 = "select cm_modulo, if (cm_fecha_vto is null,'0000-00-00',cm_fecha_vto) cm_fecha_vto,date(now()) hoy from cliente_modulo
                where cm_cliente= " . intval($_SESSION['cliente_id']) . "
                and cm_modulo='TAV'
                and cm_serie=0
                order by cm_fecha desc limit 1";

                $cm_modulo = '';
                try {
                    $resultp = $this->pDBisla->query($q1);
                    while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                        $cm_modulo = $row->cm_modulo;
                        $cm_fecha_vto = $row->cm_fecha_vto;
                        $hoy = $row->hoy;
                    }
                } catch (PDOException  $e) {
                }

                if ($cm_modulo > '' and ($cm_fecha_vto == '0000-00-00' or $cm_fecha_vto >= $hoy)) {
                    $modTAV = 1;
                }

                if ($modTAV == 0) {
                    $modfiltra .= "801,803,806,808,809,810"; //Anulo tambien la opcion de todos los permisos de manejo de tarjeta
                    if (intval($actionName) == 800) {
                        $modfiltra .= ',800';
                    }
                }
            }
            if ($modfiltra > "") $modfiltra = " and '" . $actionName . "' not in (" . $modfiltra . ")";
        }

        //donde dice $account decia $_SESSION['myAccount'] Pablo 250714

        $sql = "SELECT ag.groupid FROM groupaccounts ag 
            LEFT JOIN groupactions ga ON ag.groupid=ga.groupid 
                $ljdenied
                LEFT JOIN actions a ON ga.actionid = a.actionid 
                WHERE ag.accountid=" . $account
            . "  $whdenied
                AND (";
        if ($hastaaction > 0) {
            $sql .= "   (ga.actionid between " . $actionName . " and " . $hastaaction . ") ";
        } else {
            $sql .= "    ga.actionid = '" . $actionName . "'";
            $sql .= "    or find_in_set(ga.actionid,'" . $raiz . "') > 0";
        }

        $sql .= ") " . $modfiltra;

        $sql .= " union SELECT gae_groupid FROM groupaccounts ag 
        left join accounts on accounts.accountid = ag.accountid
        ,groupaction_eventual ge
        LEFT JOIN actions a ON gae_actionid = a.actionid
        $ljdenied
        WHERE ag.accountid=" . $account . "
        and gae_groupid = ag.groupid
        and find_in_set(accounts.cliente_id,gae_clientes) > 0
        and gae_fecha_desde < now() and gae_fecha_hasta > now()
        $whdenied
            AND (";

        if ($hastaaction > 0) {
            $sql .= "   (gae_actionid between " . $actionName . " and " . $hastaaction . ") ";
        } else {
            $sql .= "    gae_actionid = '" . $actionName . "'";
            $sql .= "    or find_in_set(gae_actionid,'" . $raiz . "') > 0";
        }
        $sql .= ")" . $modfiltra;



        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                return true;
            }
        } catch (PDOException  $e) {
        }

        $sql = "SELECT ag.accountid FROM accountaction ag 
        LEFT JOIN actions a ON ag.actionid = a.actionid 
            $ljdenied
        WHERE ag.accountid=" . $account
            . "  $whdenied
            AND (";

        if ($hastaaction > 0) {
            $sql .= "   (ag.actionid between " . $actionName . " and " . $hastaaction . ") ";
        } else {
            $sql .= "    ag.actionid = '" . $actionName . "'";
            $sql .= "    or find_in_set(ag.actionid,'" . $raiz . "') > 0";
        }

        $sql .= ") " . $modfiltra;

        $sql .= " union SELECT aae_accountid FROM accountaction_eventual "
            . " LEFT JOIN actions a ON aae_actionid = a.actionid 
            $ljdenied
            WHERE aae_accountid=" . $account
            . " and aae_fecha_desde < now() and aae_fecha_hasta > now()
            $whdenied
                    AND (";
        if ($hastaaction > 0) {
            $sql .= "   (aae_actionid between " . $actionName . " and " . $hastaaction . ") ";
        } else {
            $sql .= "    aae_actionid = '" . $actionName . "'";
            $sql .= "    or find_in_set(aae_actionid,'" . $raiz . "') > 0";
        }

        $sql .= ") " . $modfiltra;

        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                return true;
            }
        } catch (PDOException  $e) {
        }

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
    function isAllowedEmpresa($empresaid, $cliente_id = null)
    {
        $_SESSION['http_referer'] = &$_SERVER['PHP_SELF'];

        if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])) {
            $this->GoToLoginPage();
        }

        $empresaid = intval($empresaid);


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
        //     $sql="select a.accountid_equivale equivale
        //     from  accountcliente a,accounts b
        //     WHERE a.clienteid=0 and a.accountid=".intval($_SESSION['myAccount'])."
        //     and a.accountid_equivale <> 0
        //     and a.accountid_equivale = b.accountid
        //     and b.cliente_id = '".intval($_SESSION['cliente_id'])."'
        //     UNION
        //     select accountid_equivale equivale
        //     from  accountcliente
        //     WHERE clienteid = '".intval($_SESSION['cliente_id'])."' and accountid=".intval($_SESSION['myAccount'])."
        //     and accountid_equivale > 0
        //     limit 1";

        //     $this->MyDatabase();

        //     $account=$_SESSION['myAccount'];

        //     try {
        //         $resultp = $this->pDB->query($sql);
        //         while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
        //             $account = $row->equivale;
        //         }
        //     }catch(PDOException  $e ){
        //         echo "Error al validar empresa.";
        //         die();
        //     }

        $account = $this->RetornaAccount($cliente_id);
        if (!$account) return false;

        //     $result = $this->gDB->Execute($sql);

        //     if ($result === false) {
        //     	echo $this->gDB->ErrorMsg( );
        //     	//echo "<br><h3>SQL</h3>=$sql";
        //     }

        //     $account=$_SESSION['myAccount'];
        //   	if( $result->fields("equivale")) {
        //   		$account=$result->fields("equivale");
        //   	}


        //donde dice $account decia $_SESSION['myAccount'] Pablo 250714

        //Si un account no tiene accountempresa, asume todos y continua por lo grupos . Pablo 280809
        $sql = "SELECT accountid FROM accountempresa  WHERE accountid=" . $account . " limit 1";
        $accountid = 0;
        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                $accountid = $row->accountid;
            }
        } catch (PDOException  $e) {
        }

        // //	$this->MyDatabase();
        // 	 $result = $this->gDB->Execute($sql);

        //     if ($result === false) {
        //     	echo $this->gDB->ErrorMsg( );
        //     	//echo "<br><h3>SQL</h3>=$sql";
        //     }

        //     if( $result->fields("accountid"))
        if ($accountid > 0) {
            //Si un account tiene accountempresa=empresaid, devuelve true Pablo 280809

            $sql = "SELECT accountid FROM accountempresa WHERE accountid=" . $account . " AND empresaid=" . $empresaid;
            try {
                $resultp = $this->pDBisla->query($sql);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    return true;
                }
            } catch (PDOException  $e) {
            }

            return false;
            //      $result = $this->gDB->Execute($sql);

            //      if ($result === false) {
            //       echo $this->gDB->ErrorMsg( );
            //       echo "<br><h3>SQL</h3>=$sql";
            //      }

            //      if($result->fields("accountid"))
            // 	     return true;
            //      else
            //     	 return false;

        }


        //Si el grupo de un account no tiene groupempresa, asume todos y devuelve true Pablo 210809
        $sql = "SELECT ag.groupid FROM groupaccounts ag,groupempresa ga  "
            . " WHERE ag.accountid=" . $account
            . "          AND ag.groupid=ga.groupid limit 1";

        $b = 0;
        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                $b = 1;
            }
        } catch (PDOException  $e) {
        }
        if ($b == 0) return true;

        //     $result = $this->gDB->Execute($sql);

        //     if ($result === false) {
        //       echo $this->gDB->ErrorMsg( );
        //       echo "<br><h3>SQL</h3>=$sql";
        //     }

        //     if( $result->fields("groupid"))
        // 	{

        // 	}else {
        // 	      return true;
        // 	}

        //Si el grupo de un account tiene groupempresa=empresaid, devuelve true Pablo 210809

        $sql = "SELECT ag.groupid FROM groupaccounts ag "
            . " LEFT JOIN groupempresa ga ON ag.groupid=ga.groupid "
            . " WHERE ag.accountid=" . $account
            . "          AND ga.empresaid=" . $empresaid;

        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                return true;
            }
        } catch (PDOException  $e) {
        }

        return false;
        //     $result = $this->gDB->Execute($sql);

        //     if ($result === false) {
        //       echo $this->gDB->ErrorMsg( );
        //       echo "<br><h3>SQL</h3>=$sql";
        //     }

        //     if($result->fields("groupid"))
        //       return true;
        //     else
        //       return false;

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

        if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])) {
            $this->GoToLoginPage();
        }

        $id = intval($id);
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
        //     $sql="select a.accountid_equivale equivale
        //     from  accountcliente a,accounts b
        //     WHERE a.clienteid=0 and a.accountid=".$_SESSION['myAccount']."
        //     and a.accountid_equivale <> 0
        //     and a.accountid_equivale = b.accountid
        //     and b.cliente_id = '".$_SESSION['cliente_id']."'
        //     UNION
        //     select accountid_equivale equivale
        //     from  accountcliente
        //     WHERE clienteid = '".$_SESSION['cliente_id']."' and accountid=".$_SESSION['myAccount']."
        //     and accountid_equivale > 0
        //     limit 1";

        //   	$this->MyDatabase();
        //   	$account=$_SESSION['myAccount'];

        //   	try {
        //   	    $resultp = $this->pDB->query($sql);
        //   	    while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
        //   	        $account=$row->equivale;
        //   	    }
        //   	}catch(PDOException  $e ){
        //   	}

        $account = $this->RetornaAccount();
        if (!$account) return false;


        //   	$result = $this->gDB->Execute($sql);

        //   	if ($result === false) {
        //   		echo $this->gDB->ErrorMsg( );
        //   		//echo "<br><h3>SQL</h3>=$sql";
        //   	}

        //   	$account=$_SESSION['myAccount'];
        //   	if( $result->fields("equivale")) {
        //   		$account=$result->fields("equivale");
        //   	}

        //si viene por una llamada de una reporte desde ws, pdbcli no iene creada, entonces las crea
        if (!isset($this->pDBcli)) {
            $this->pDBcli = null;

            if ($_SESSION['host_cli'] > "") {
                try {
                    $this->pDBcli = new PDO("mysql:host=" . $_SESSION['host_cli'] . ";dbname=" . $_SESSION['db_cli'] . ";charset=latin1", DB_ACCOUNT, DB_PASSWORD);
                    $this->pDBcli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException  $e) {
                    unset($this->pDBcli);
                }
            }
        }

        if (isset($this->pDBcli)) {

            //si tiene par_eq_provincia (es de tpte de la provincia), verifica su validez
            //uso cliente_id <> :cli para que no verifique las jurisdicones si son del mismo cliente( ej p/q tpte cba ves sus propias jurisdcciones)
            $sql = "SELECT par_eq_provincia,jur_provincia_id FROM megacontrol.accounts,megacontrol.cliente_parametro_equipo,jurisdiccion
where accountid = :acc and cliente_id <> :cli
and cliente_id = par_eq_cliente_id
and jur_id=:id
";
            try {
                $resultp = $this->pDBcli->prepare($sql);
                $resultp->execute(array(':id' => $id, ':acc' => $_SESSION['myAccount'], ':cli' => $_SESSION['cliente_id']));
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    if ($row->par_eq_provincia > 0) {
                        if ($row->jur_provincia_id > 0 and $row->jur_provincia_id == $row->par_eq_provincia) {
                            return true;
                        }
                        return false;
                    }
                }
            } catch (PDOException  $e) {
                echo 'Error' . __LINE__; //$e->getMessage();
            }
        }


        //Si un account no tiene accountjurisdiccion, asume todos . Pablo 240714
        $sql = "SELECT accountid FROM accountjurisdiccion  "
            . " WHERE accountid=" . $account
            . " limit 1";
        $accountid = 0;
        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                $accountid = $row->accountid;
            }
        } catch (PDOException  $e) {
        }


        // //  	$this->MyDatabase();
        //   	$result = $this->gDB->Execute($sql);

        //   	if ($result === false) {
        //   		echo $this->gDB->ErrorMsg( );
        //   		//echo "<br><h3>SQL</h3>=$sql";
        //   	}

        //   	if( $result->fields("accountid"))
        if ($accountid > 0) {
            //Si un account tiene accountjurisdiccion=id, devuelve true Pablo 240714

            $sql = "SELECT accountid FROM accountjurisdiccion "
                . " WHERE accountid=" . $account
                . "   AND jurisdiccionid=" . $id;

            try {
                $resultp = $this->pDBisla->query($sql);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    return true;
                }
            } catch (PDOException  $e) {
            }

            return false;
            //   		$result = $this->gDB->Execute($sql);

            //   		if ($result === false) {
            //   		echo $this->gDB->ErrorMsg( );
            //   		//echo "<br><h3>SQL</h3>=$sql";
            //   		}

            //   		if($result->fields("accountid"))
            //   		return true;
            //   		else
            //   				return false;

        }

        return true;
    }

    /*
   * RetornaAccount
   * Devuelve el id de usuario, false si no es valido
   * 
   * 
   */
    function RetornaAccount($cliente_id = null)
    {

        $sql = "select cliente_nivel,a.cliente_id
,(select a.accountid_equivale
    from  accountcliente a,accounts b
    WHERE a.clienteid=0 and a.accountid=" . intval($_SESSION['myAccount']) . "
    and a.accountid_equivale <> 0
    and a.accountid_equivale = b.accountid
    and b.cliente_id = '" . intval($_SESSION['cliente_id']) . "'
    UNION
    select accountid_equivale equivale
    from  accountcliente
    WHERE clienteid = '" . intval($_SESSION['cliente_id']) . "' and accountid=" . intval($_SESSION['myAccount']) . "
    and accountid_equivale > 0
    limit 1) equivale
        
    from  accounts a,cliente c
    WHERE a.accountid=" . intval($_SESSION['myAccount']) . "
and c.cliente_id = " . intval($_SESSION['cliente_id']) . "
and
    (validez_desde is null or validez_desde < '2000-00-00 00:00:00' or validez_desde <= now())
    and (validez_hasta is null or validez_hasta < '2000-00-00 00:00:00' or validez_hasta >= now())
   ";


        //       $sql="select a.accountid_equivale equivale
        //         from  accountcliente a,accounts b
        //         WHERE a.clienteid=0 and a.accountid=".c."
        //         and a.accountid_equivale <> 0
        //         and a.accountid_equivale = b.accountid
        //         and b.cliente_id = '".intval($_SESSION['cliente_id'])."'
        //         UNION
        //         select accountid_equivale equivale
        //         from  accountcliente
        //         WHERE clienteid = '".intval($_SESSION['cliente_id'])."' and accountid=".intval($_SESSION['myAccount'])."
        //         and accountid_equivale > 0
        //         limit 1";

        $account = intval($_SESSION['myAccount']);

        //      $this->MyDatabase();
        $ban = 0;
        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                if ($row->equivale > 0) $account = $row->equivale;
                $ban = 1;
            }
        } catch (PDOException  $e) {
            return false;
        }

        if ($ban == 0) return false;

        if (is_null($cliente_id)) {
            return $account;
        }

        $sql = "select a.cliente_id
,(select ac.accountid_equivale
    from  accountcliente ac,accounts b
    WHERE ac.clienteid=0 and ac.accountid=" . $account . "
    and ac.accountid_equivale <> 0
    and ac.accountid_equivale = b.accountid
    and b.cliente_id = " . $cliente_id . "
    UNION
    select accountid_equivale equivale
    from  accountcliente
    WHERE clienteid = " . $cliente_id . " and accountid=" . $account . "
    and accountid_equivale > 0
    limit 1) equivale
        
    from  accounts a
    WHERE a.accountid=" . $account . "
and
    (validez_desde is null or validez_desde < '2000-00-00 00:00:00' or validez_desde <= now())
    and (validez_hasta is null or validez_hasta < '2000-00-00 00:00:00' or validez_hasta >= now())
   ";
        //echo $cliente_id.$sql;
        $ban = 0;
        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                if ($row->equivale > 0) $account = $row->equivale;
                $ban = 1;
            }
        } catch (PDOException  $e) {
            return false;
        }

        return $account;
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

        if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])) {
            $this->GoToLoginPage();
        }
        $id = intval($id);

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

        //       $sql="select a.accountid_equivale equivale
        //     from  accountcliente a,accounts b
        //     WHERE a.clienteid=0 and a.accountid=".intval($_SESSION['myAccount'])."
        //     and a.accountid_equivale <> 0
        //     and a.accountid_equivale = b.accountid
        //     and b.cliente_id = '".intval($_SESSION['cliente_id'])."'
        //     UNION
        //     select accountid_equivale equivale
        //     from  accountcliente
        //     WHERE clienteid = '".intval($_SESSION['cliente_id'])."' and accountid=".intval($_SESSION['myAccount'])."
        //     and accountid_equivale > 0
        //     limit 1";

        //       $account=intval($_SESSION['myAccount']);

        //       $this->MyDatabase();
        //       try {
        //           $resultp = $this->pDB->query($sql);
        //           while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
        //               $account = $row->equivale;
        //           }
        //       }catch(PDOException  $e ){
        //           return false;
        //       }

        $account = $this->RetornaAccount();
        if (!$account) return false;

        //       $result = $this->gDB->Execute($sql);

        //       if ($result === false) {
        //           echo $this->gDB->ErrorMsg( );
        //           //echo "<br><h3>SQL</h3>=$sql";
        //       }

        //       $account=$_SESSION['myAccount'];
        //       if( $result->fields("equivale")) {
        //           $account=$result->fields("equivale");
        //       }



        //Si un account no tiene accountjurisdiccion, asume todos . Pablo 240714
        $sql = "SELECT accountid FROM accountvehiculo  "
            . " WHERE accountid=" . $account
            . " limit 1";

        //        $this->MyDatabase();
        $acid = 0;
        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                $acid = $row->accountid;
            }
        } catch (PDOException  $e) {
            return false;
        }

        // //          $this->MyDatabase();
        //           $result = $this->gDB->Execute($sql);

        //           if ($result === false) {
        //               echo $this->gDB->ErrorMsg( );
        //               //echo "<br><h3>SQL</h3>=$sql";
        //           }

        //          if( $result->fields("accountid"))
        if ($acid > 0) {
            //Si un account tiene accountjurisdiccion=id, devuelve true Pablo 240714

            $sql = "SELECT accountid FROM accountvehiculo "
                . " WHERE accountid=" . $account
                . "   AND vehiculo_id=" . $id;

            try {
                $resultp = $this->pDBisla->query($sql);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    return true;
                }
            } catch (PDOException  $e) {
            }

            return false;
            //                   $result = $this->gDB->Execute($sql);

            //                   if ($result === false) {
            //                       echo $this->gDB->ErrorMsg( );
            //                       //echo "<br><h3>SQL</h3>=$sql";
            //                   }

            //                   if($result->fields("accountid"))
            //                       return true;
            //                       else
            //                           return false;

        }

        return 999;
    }

    /**
     * Method to check if the person can access to an servicio
     *
     * Pablo 22/05/19
     * @public
     *
     * retorna true si no tiene permiso especifico para ningun servicio ( o sea que permite todas)
     * o tiene permiso para el servicio solicitada
     * @returns boolean
     */
    function isAllowedServicio($id)
    {
        $_SESSION['http_referer'] = &$_SERVER['PHP_SELF'];

        if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])) {
            $this->GoToLoginPage();
        }

        $id = intval($id);

        //       $sql="select a.accountid_equivale equivale
        //     from  accountcliente a,accounts b
        //     WHERE a.clienteid=0 and a.accountid=".$_SESSION['myAccount']."
        //     and a.accountid_equivale <> 0
        //     and a.accountid_equivale = b.accountid
        //     and b.cliente_id = '".$_SESSION['cliente_id']."'
        //     UNION
        //     select accountid_equivale equivale
        //     from  accountcliente
        //     WHERE clienteid = '".$_SESSION['cliente_id']."' and accountid=".$_SESSION['myAccount']."
        //     and accountid_equivale > 0
        //     limit 1";

        //       $this->MyDatabase();
        //       $result = $this->gDB->Execute($sql);

        //       if ($result === false) {
        //           echo $this->gDB->ErrorMsg( );
        //           //echo "<br><h3>SQL</h3>=$sql";
        //       }

        //       $account=$_SESSION['myAccount'];
        //       if( $result->fields("equivale")) {
        //           $account=$result->fields("equivale");
        //       }


        $account = $this->RetornaAccount();
        if (!$account) return false;

        //Si un account no tiene accountjurisdiccion, asume todos . Pablo 240714
        $sql = "SELECT accountid FROM accountservicio  "
            . " WHERE accountid=" . $account
            . " limit 1";

        //          $this->MyDatabase();

        $acid = 0;
        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                $acid = $row->accountid;
            }
        } catch (PDOException  $e) {
            return false;
        }

        //           $this->MyDatabase();
        //           $result = $this->gDB->Execute($sql);

        //           if ($result === false) {
        //               echo $this->gDB->ErrorMsg( );
        //               //echo "<br><h3>SQL</h3>=$sql";
        //           }

        //           if( $result->fields("accountid"))
        if ($acid > 0) {
            //Si un account tiene accountjurisdiccion=id, devuelve true Pablo 240714

            $sql = "SELECT accountid FROM accountservicio "
                . " WHERE accountid=" . $account
                . "   AND servicio_id=" . $id;

            try {
                $resultp = $this->pDBisla->query($sql);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    return true;
                }
            } catch (PDOException  $e) {
            }

            return false;

            //                   $result = $this->gDB->Execute($sql);

            //                   if ($result === false) {
            //                       echo $this->gDB->ErrorMsg( );
            //                       //echo "<br><h3>SQL</h3>=$sql";
            //                   }

            //                   if($result->fields("accountid"))
            //                       return true;
            //                       else
            //                           return false;

        }

        return 999;
    }

    /**
     * Method to check the username and password.
     * @public
     */
    function VerifyUser($username, $password, $md5 = USE_MD5)
    {

        $this->mUsername = $username;
        #echo 'USE_MD5: '.USE_MD5."<br>";
        if ($md5) $password = md5(htmlspecialchars($password));

        $this->mAccountID = null;
        $this->mHierarchy = null;
        $this->mDbCliente = null;
        $this->mGobProvincia = null;
        $this->ClienteID = null;
        $this->mHostCliente = null;
        $this->FechaDesdeReporte = null;

        $accountid = 0;
        $sql = "SELECT accountid,tries,lasttrieddate,accounts.cliente_id,fecha_desde_reporte,cliente_db,cliente_host 
                    ,(select count(*) from accountmac where accountmac.accountid=accounts.accountid) c
                    ,par_eq_provincia
                FROM accounts,cliente
                left join cliente_parametro_equipo on par_eq_cliente_id = cliente.cliente_id
                WHERE username = :user  AND password=:pass and cliente.cliente_id = accounts.cliente_id
                ";
        try {
            $resultp = $this->pDBisla->prepare($sql);
            $datos = array(':user' => $this->mUsername, ':pass' => $password);
            $resultp->execute($datos);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                $c = (int)$row->c;
                $accountid = (int)$row->accountid;
                $this->mAccountID = (int)$row->accountid;
                $this->mAccountTries = (int)$row->tries;
                $this->mLastTriedDate = (int)$row->lasttrieddate;
                $this->mNroCliente = (int)$row->cliente_id;
                $this->FechaDesdeReporte = $row->fecha_desde_reporte;
                $this->mDbCliente = $row->cliente_db;
                $this->mGobProvincia = $row->par_eq_provincia;
                $this->ClienteId = (int) $row->cliente_id;
                $this->mHostCliente = $row->cliente_host;
            }
        } catch (PDOException  $e) {
            $this->SetErrorMessage('Error de lectura', '');
            if ($_SESSION['IS_ERROR_REPORTING']) $this->EchoError('Error de lectura', '');
            return false;
        }


        if ($accountid == 0) {
            $this->BadAttempt();
            $this->mAccountID = null;
            $this->mHierarchy = null;
            $this->mDbCliente = null;
            $this->mGobProvincia = null;
            $this->ClienteID = null;
            $this->mHostCliente = null;
            $this->FechaDesdeReporte = null;
            $this->SetErrorMessage("Usuario o contraseña inválidos.");
            return false;
        }

        //si el usuario esta en accountmac no lo deja loguear
        if ($c > 0) {
            $this->BadAttempt();
            $this->mAccountID = null;
            $this->mHierarchy = null;
            $this->mDbCliente = null;
            $this->mGobProvincia = null;
            $this->ClienteID = null;
            $this->mHostCliente = null;
            $this->FechaDesdeReporte = null;
            $this->SetErrorMessage("Usuario no habilitado para login en el sistema.");
            return false;
        }


        if ($accountid > 0) {

            if ($this->mAccountTries > 0)
                $this->ResetAccountTries($this->mAccountID);
            #
            # We will get the highest level of hierarchy s/he has. If the person
            # is defined in multiple level of groups, we will give the highest,
            # which is the lowest number.
            #
            #$sql = "SELECT MIN(hierarchy) as MIN FROM groups "
            #     ."    WHERE groupid IN (SELECT groupid FROM groupaccounts "
            #     ."                        WHERE accountid=$this->mAccountID)";

            $sql = "SELECT MIN(hierarchy) as min FROM groups g"
                . " LEFT JOIN groupaccounts ga ON g.groupid = ga.groupid "
                . " WHERE ga.accountid=:id";
            try {
                $resultp = $this->pDBisla->prepare($sql);
                $datos = array(':id' => $this->mAccountID);
                $resultp->execute($datos);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    $this->mHierarchy = $row->min;
                    return true;
                }
            } catch (PDOException  $e) {
                $this->SetErrorMessage('Error de lectura', '');
                if ($_SESSION['IS_ERROR_REPORTING']) $this->EchoError('Error de lectura', '');
                return false;
            }

            return false;
        } else {
            #if ($_SESSION['LOG_ACTIVITIES']) {
            $WriteLog = new WriteLog();
            $WriteLog->SetAccountId(0);
            $WriteLog->SetUserName($this->mUsername);
            $WriteLog->SetActivityId(1);

            $WriteLog->WriteToLog();
            unset($WriteLog);
            #}
            $this->SetErrorMessage("Usuario o contraseña inválidos.");
            return false;
        }
    }

    /**
     * Method to check the username and password.
     * @public
     */
    function VerifyUserWs($username, $password, $md5 = USE_MD5)
    {

        $this->mUsername = $username;
        if ($md5) $password = md5(htmlspecialchars($password));

        $this->mAccountID = null;
        $this->mHierarchy = null;
        $this->mDbCliente = null;
        $this->mGobProvincia = null;
        $this->ClienteID = null;
        $this->mHostCliente = null;
        $this->FechaDesdeReporte = null;

        $accountid = 0;
        $sql = "SELECT accountid,tries,lasttrieddate,accounts.cliente_id,fecha_desde_reporte,cliente_db,cliente_host
    ,(select count(*) from accountmac where accountmac.accountid=accounts.accountid) c
    ,par_eq_provincia
FROM accounts,cliente
left join cliente_parametro_equipo on par_eq_cliente_id = cliente.cliente_id
WHERE username = :user  and cliente.cliente_id = accounts.cliente_id ";
        try {
            $resultp = $this->pDBisla->prepare($sql);
            $datos = array(':user' => $this->mUsername);
            $resultp->execute($datos);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                $c = (int)$row->c;
                $accountid = (int)$row->accountid;
                $this->mAccountID = (int)$row->accountid;
                $this->mAccountTries = (int)$row->tries;
                $this->mLastTriedDate = (int)$row->lasttrieddate;
                $this->mNroCliente = (int)$row->cliente_id;
                $this->FechaDesdeReporte = $row->fecha_desde_reporte;
                $this->mDbCliente = $row->cliente_db;
                $this->mGobProvincia = $row->par_eq_provincia;
                $this->ClienteId = (int) $row->cliente_id;
                $this->mHostCliente = $row->cliente_host;
            }
        } catch (PDOException  $e) {
            $this->SetErrorMessage('Error de lectura', '');
            if ($_SESSION['IS_ERROR_REPORTING']) $this->EchoError('Error de lectura', '');
            return false;
        }


        if ($accountid == 0) {
            $this->BadAttempt();
            $this->mAccountID = null;
            $this->mHierarchy = null;
            $this->mDbCliente = null;
            $this->mGobProvincia = null;
            $this->ClienteID = null;
            $this->mHostCliente = null;
            $this->FechaDesdeReporte = null;
            $this->SetErrorMessage("Usuario o contraseña inválidos.");
            return false;
        }

        //si el usuario esta en accountmac no lo deja loguear
        if ($c > 0) {
            $this->BadAttempt();
            $this->mAccountID = null;
            $this->mHierarchy = null;
            $this->mDbCliente = null;
            $this->mGobProvincia = null;
            $this->ClienteID = null;
            $this->mHostCliente = null;
            $this->FechaDesdeReporte = null;
            $this->SetErrorMessage("Usuario no habilitado para login en el sistema.");
            return false;
        }


        if ($accountid > 0) {

            if ($this->mAccountTries > 0)
                $this->ResetAccountTries($this->mAccountID);
            #
            # We will get the highest level of hierarchy s/he has. If the person
            # is defined in multiple level of groups, we will give the highest,
            # which is the lowest number.
            #
            #$sql = "SELECT MIN(hierarchy) as MIN FROM groups "
            #     ."    WHERE groupid IN (SELECT groupid FROM groupaccounts "
            #     ."                        WHERE accountid=$this->mAccountID)";

            $sql = "SELECT MIN(hierarchy) as min FROM groups g"
                . " LEFT JOIN groupaccounts ga ON g.groupid = ga.groupid "
                . " WHERE ga.accountid=:id";
            try {
                $resultp = $this->pDBisla->prepare($sql);
                $datos = array(':id' => $this->mAccountID);
                $resultp->execute($datos);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    $this->mHierarchy = $row->min;
                    return true;
                }
            } catch (PDOException  $e) {
                $this->SetErrorMessage('Error de lectura', '');
                if ($_SESSION['IS_ERROR_REPORTING']) $this->EchoError('Error de lectura', '');
                return false;
            }

            return false;
        } else {
            #if ($_SESSION['LOG_ACTIVITIES']) {
            $WriteLog = new WriteLog();
            $WriteLog->SetAccountId(0);
            $WriteLog->SetUserName($this->mUsername);
            $WriteLog->SetActivityId(1);

            $WriteLog->WriteToLog();
            unset($WriteLog);
            #}
            $this->SetErrorMessage("Usuario o contraseña inválidos.");
            return false;
        }
    }

    /**
     * Method to validar la password
     * @public
     */
    function ValidarPassword($username, $clave, &$error_clave = '', $clave_rep = "")
    {
        if (strlen($clave) < 8) {
            $error_clave = "La clave debe tener al menos 8 caracteres";
            return false;
        }
        if (strlen($clave) > 16) {
            $error_clave = "La clave no puede tener m�s de 16 caracteres";
            return false;
        }
        if (!preg_match('`[a-z]`', $clave)) {
            $error_clave = "La clave debe tener al menos una letra min�scula";
            return false;
        }
        if (!preg_match('`[A-Z]`', $clave)) {
            $error_clave = "La clave debe tener al menos una letra may�scula";
            return false;
        }
        if (!preg_match('`[0-9]`', $clave)) {
            $error_clave = "La clave debe tener al menos un caracter num�rico";
            return false;
        }

        if ($clave != $clave_rep and $clave_rep > "") {
            $error_clave = 'La repeticion de la contrase�a nueva no coincide';
            return false;
        }

        if ($username == "") return true;

        $this->mUsername = $username; //ToSQL($value=htmlspecialchars($username),$type='text_seguro');
        #echo 'USE_MD5: '.USE_MD5."<br>";
        if ($md5)
            $password = md5($clave); //ToSQL($value=md5(htmlspecialchars($clave)),$type='text');
        else
            $password = $clave; //ToSQL($value=htmlspecialchars($clave),$type='text');
        #echo '$password: '.$password.'<br>';

        #
        # The reason we read the username first is, if the bad_attempts
        # is on, than we will increase the counter by one for each try,
        # for that user.
        #
        $sql = "SELECT password FROM accounts WHERE username = :user";
        #echo '$sql: '.$sql.'<br>';
        try {
            $resultp = $this->pDBisla->prepare($sql);
            $datos = array(':user' => $this->mUsername);
            $resultp->execute($datos);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                if ($row->password == $password) {
                    $error_clave = 'La contrase�a nueva no puede ser igual a la actual';
                    return false;
                }
            }
        } catch (PDOException  $e) {
            $this->SetErrorMessage('Error de lectura', '');
            if ($_SESSION['IS_ERROR_REPORTING']) $this->EchoError('Error de lectura', '');
            $error_clave = "Problema en el acceso a los datos. Reintente.";
            return false;
        }


        //               $this->MyDatabase();
        //               $result = $this->gDB->Execute($sql);

        //               if ($result === false) {
        //                   $this->SetErrorMessage('error reading: '.$this->gDB->ErrorMsg( ),$sql);
        //                   if ($_SESSION['IS_ERROR_REPORTING'])
        //                       $this-> EchoError(  $this->gDB->ErrorMsg(),$sql  );
        //                   $error_clave= "Problema en el acceso a los datos. Reintente.";
        //                   return false;
        //               }

        //               if($result->fields("accountid") > 0) {
        //                   if ($result->fields("password") == $password) {
        //                       $error_clave = 'La contrase�a nueva no puede ser igual a la actual';
        //                       return false;
        //                   }
        //               }

        $error_clave = "";
        return true;
    }

    /**
     * Method que devuelve un array cuyas claves son los modulos habilitados segun las actions 
     * @public
     */

    function ModulesAllowedToAccount($account = false)
    {

        $_SESSION['http_referer'] = &$_SERVER['PHP_SELF'];

        if (!isset($_SESSION['myAccount']) or is_null($_SESSION['myAccount'])) {
            $this->GoToLoginPage();
        }
        if ($account == false) $account = intval($_SESSION['myAccount']);

        $modulos = array();

        //chequea validez del account
        $sql = "select cliente_nivel,a.cliente_id
,(select a.accountid_equivale
    from  accountcliente a,accounts b
    WHERE a.clienteid=0 and a.accountid=" . $account . "
    and a.accountid_equivale <> 0
    and a.accountid_equivale = b.accountid
    and b.cliente_id = '" . intval($_SESSION['cliente_id']) . "'
    UNION
    select accountid_equivale equivale
    from  accountcliente
    WHERE clienteid = '" . intval($_SESSION['cliente_id']) . "' and accountid=" . $account . "
    and accountid_equivale > 0
    limit 1) equivale
        
        
    from  accounts a,cliente c
    WHERE a.accountid=" . $account . "
and c.cliente_id = " . intval($_SESSION['cliente_id']) . "
and
    (validez_desde is null or validez_desde < '2000-00-00 00:00:00' or validez_desde <= now())
    and (validez_hasta is null or validez_hasta < '2000-00-00 00:00:00' or validez_hasta >= now())
   ";
        if (substr($_SESSION['cliente_id'], 0, 1) == 'E') { //cliente externo
            $sql = "select 0 cliente_nivel,0 cliente_id, null equivale
    from  accounts a
    WHERE a.accountid=" . $account . "
and
    (validez_desde is null or validez_desde < '2000-00-00 00:00:00' or validez_desde <= now())
    and (validez_hasta is null or validez_hasta < '2000-00-00 00:00:00' or validez_hasta >= now())
   ";
        }

        $c = 0;
        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                $c = 1; //$result->fields("c");
                $nivel_cliente = $row->cliente_nivel;
                $cliente_ac = $row->cliente_id;
                $equivale = $row->equivale;
            }
        } catch (PDOException  $e) {
            //echo $e;die();
        }

        if ($c == 0) {
            unset($_SESSION['myAccount']);
            $this->GoToLoginPage();
        }


        //       $ljdenied="";
        //       $whdenied="";
        //       if ($_SESSION [myHierarchy] != 1 ) {
        //           $ljdenied=" left join actionclientedenied acd on acd.actionid=$actionName and acd.cliente_id= ".intval($_SESSION['cliente_id']);
        //           $whdenied=" and acd.actionid is null";
        //       }


        //si es de sisca y el permiso es publico (no es interno de sisca)
        //if ($cliente_ac == 1 and $actionclase == 0 and $nivel_cliente > 0) {
        if ($cliente_ac == 1 and $nivel_cliente > 0) {
            $account = intval($_SESSION['myAccount']);

            //devuelve el mejor nivel de un usuario
            $sql = "select gr_nivel,g.groupid from groupaccounts ag ,groups g
where accountid = $account
and g.groupid = ag.groupid
and gr_nivel > 0
order by gr_nivel limit 1
";

            $nivel = 0;
            try {
                $resultp = $this->pDBisla->query($sql);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    $nivel = $row->gr_nivel;
                }
            } catch (PDOException  $e) {
                echo "Error de lectura de nivel.Reintente.";
                die();
                //echo $e;die();
            }


            //       //  $this->MyDatabase();
            //         $result = $this->gDB->Execute($sql);

            //         if ($result === false) {
            //             echo $this->gDB->ErrorMsg( );
            //             //echo "<br><h3>SQL</h3>=$sql";
            //         }
            //         $nivel=0;
            //         if( $result->fields("groupid")) {
            //             $nivel=$result->fields("gr_nivel");
            //         }
            // //         else {
            // //             //Me aseguro que actioname es un codigo, no usa cadena
            // //             $q1="select max(gr_nivel) m from groups";
            // //            // $this->MyDatabase();
            // //             $resultq1 = $this->gDB->Execute ( $q1 );
            // //             $nivel = $resultq1->fields("m");
            // //             //$nivel=0; //sacar cuando todo ande ok.
            // //         }

            $nivel = max($nivel, $nivel_cliente); //toma el peor nivel (1=mejor)

            //me aseguro que exista el nivel
            $q1 = "select max(gr_nivel) m from groups where gr_nivel <=" . $nivel;
            try {
                $resultp = $this->pDBisla->query($q1);
                while ($row = $resultp->fetch(PDO::FETCH_OBJ)) {
                    $nivel = $row->m;
                }
            } catch (PDOException  $e) {
                echo "Error de lectura de nivel.Reintente.";
                die();
            }


            $sql = "SELECT a.actionmodulos FROM groups g
                 LEFT JOIN groupactions ga ON g.groupid=ga.groupid
                     LEFT JOIN actions a ON ga.actionid = a.actionid";
            if ($_SESSION[myHierarchy] != 1) {
                $sql .= " left join actionclientedenied acd on acd.actionid=ga.actionid and acd.cliente_id= " . intval($_SESSION['cliente_id']);
            }
            $sql .= " LEFT JOIN actions ah ON find_in_set(a.actionid,ah.actionraiz) > 0";
            $sql .= "   WHERE (gr_nivel = '$nivel')";
            if ($_SESSION[myHierarchy] != 1) {
                $sql .= " and acd.actionid is null";
            }
            //                               AND (";
            //                 if ($hastaaction > 0 )	 {
            //                     $sql .= "   (ga.actionid between ".$actionName." and ".$hastaaction.") ";
            //                 } else {
            //                     $sql .="    ga.actionid = '".$actionName."'";
            //                     //                        $sql .="    or ga.actionid = ".$raiz;
            //                     $sql .="    or ((ah.actionraiz is null or ah.actionraiz = '') and ga.actionid = ".$raiz.")";
            //                     $sql .="    or (ah.actionraiz > '' and ah.actionid = '".$actionName."')";
            //                 }
            //                 $sql .=")";

            $sql .= " union SELECT a.actionmodulos FROM accountaction_eventual "
                . " LEFT JOIN actions a ON aae_actionid = a.actionid
                   LEFT JOIN actions ah ON find_in_set(a.actionid,ah.actionraiz) > 0";
            if ($_SESSION[myHierarchy] != 1) {
                $sql .= " left join actionclientedenied acd on acd.actionid=aae_actionid and acd.cliente_id= " . intval($_SESSION['cliente_id']);
            }
            $sql .= " WHERE aae_accountid=" . $account
                . " and aae_fecha_desde < now() and aae_fecha_hasta > now()";
            if ($_SESSION[myHierarchy] != 1) {
                $sql .= " and acd.actionid is null";
            }

            //                         AND (";

            //                     if ($hastaaction > 0 )	 {
            //                         $sql .= "   (aae_actionid between ".$actionName." and ".$hastaaction.") ";
            //                     } else {
            //                         $sql .="    aae_actionid = '".$actionName."'";
            //                         //                       $sql .="    or aae_actionid = ".$raiz;
            //                         $sql .="    or ((ah.actionraiz is null or ah.actionraiz = '') and aae_actionid = ".$raiz.")";
            //                         $sql .="    or (ah.actionraiz > '' and ah.actionid = '".$actionName."')";
            //                     }
            //                     $sql .=")";

            $sql .= " union SELECT a.actionmodulos FROM groupaccounts ag
                left join accounts on accounts.accountid = ag.accountid
,groupaction_eventual ge
				LEFT JOIN actions a ON gae_actionid = a.actionid
                LEFT JOIN actions ah ON find_in_set(a.actionid,ah.actionraiz) > 0";
            if ($_SESSION[myHierarchy] != 1) {
                $sql .= " left join actionclientedenied acd on acd.actionid=gae_actionid and acd.cliente_id= " . intval($_SESSION['cliente_id']);
            }

            $sql .= " WHERE ag.accountid=" . $account . "
                    and gae_groupid = ag.groupid
                    and find_in_set(accounts.cliente_id,gae_clientes) > 0
                    and gae_fecha_desde < now() and gae_fecha_hasta > now()";
            if ($_SESSION[myHierarchy] != 1) {
                $sql .= " and acd.actionid is null";
            }

            //                         AND (";
            //                     //          ."          AND (upper(a.actionname)='".strtoupper($actionName)."'";
            //                     //          ."          or ag.actionid = '".$actionName."'"  ;

            //                     if ($hastaaction > 0 )	 {
            //                         $sql .= "   (gae_actionid between ".$actionName." and ".$hastaaction.") ";
            //                     } else {
            //                         $sql .="    gae_actionid = '".$actionName."'";
            //                         //                       $sql .="    or aae_actionid = ".$raiz;
            //                         $sql .="    or ((ah.actionraiz is null or ah.actionraiz = '') and gae_actionid = ".$raiz.")";
            //                         $sql .="    or (ah.actionraiz > '' and ah.actionid = '".$actionName."')";
            //                     }
            //                     $sql .=")";


            try {
                $resultp = $this->pDBisla->query($sql);
                while ($row = $resultp->fetch(PDO::FETCH_NUM)) {
                    foreach (explode(",", $row[0]) as $v) $modulos[$v] = 1;
                }
            } catch (PDOException  $e) {
            }

            return $modulos;
            //           //          $this->MyDatabase();
            //                     $result = $this->gDB->Execute($sql);

            //                     if ($result === false) {
            //                         return false;
            // //                         echo $this->gDB->ErrorMsg( );
            // //                        echo "<br><h3>SQL</h3>=$sql";
            //                     }

            //                     if($result->fields("actionid")) return true;

            //                     return false;


        }


        //$account=intval($_SESSION['myAccount']);
        if ($equivale > 0) {
            $account = $equivale;
        }


        $modfiltra = "";
        //       if ($_SESSION [myHierarchy] != 1 ) {
        //           if ($raiz == 800) {
        //               $modTAV=0;
        //               //              	$q1="select cm_modulo from cliente_modulo where cm_cliente = ".$_SESSION['cliente_id']." and cm_modulo='TAV' and cm_inactivo=0";
        //               $q1="select cm_modulo, if (cm_fecha_vto is null,'0000-00-00',cm_fecha_vto) cm_fecha_vto,date(now()) hoy from cliente_modulo
        // 	     where cm_cliente= ".intval($_SESSION['cliente_id'])."
        // 		and cm_modulo='TAV'
        // 	        and cm_serie=0
        // 	order by cm_fecha desc limit 1";

        //               $cm_modulo = '';
        //               try {
        //                   $resultp = $this->pDB->query($q1);
        //                   while($row=$resultp->fetch(PDO::FETCH_OBJ)) {
        //                       $cm_modulo = $row->cm_modulo;
        //                       $cm_fecha_vto = $row->cm_fecha_vto;
        //                       $hoy = $row->hoy;
        //                   }
        //               }catch(PDOException  $e ){
        //               }

        //               if ($cm_modulo > '' and ($cm_fecha_vto == '0000-00-00' or $cm_fecha_vto >= $hoy )) {$modTAV=1;}


        //               //               	//$this->MyDatabase();
        //               //               	$resultq1 = $this->gDB->Execute ( $q1 );
        //               //               	if($resultq1->fields("cm_modulo")) {
        //               //               	    if ($resultq1->fields("cm_fecha_vto") == '0000-00-00' or $resultq1->fields("cm_fecha_vto") >= $resultq1->fields("hoy") ) {
        //               //                   	    $modTAV=1;
        //               //               	    }
        //               //               	}
        //               //       	while ( ! $resultq1->EOF ) {
        //               //       	    $modTAV=1;
        //               //       	}
        //               if ($modTAV == 0) {
        //                   $modfiltra .="801,803,806,808,809,810"; //Anulo tambien la opcion de todos los permisos de manejo de tarjeta
        //                   if (intval($actionName) == 800) {$modfiltra.=',800';}
        //               }
        //           }
        //           if ($modfiltra > "") $modfiltra= " and '".$actionName."' not in (".$modfiltra.")";

        //      }

        //donde dice $account decia $_SESSION['myAccount'] Pablo 250714

        $sql = "SELECT a.actionmodulos FROM groupaccounts ag
               LEFT JOIN groupactions ga ON ag.groupid=ga.groupid";
        if ($_SESSION[myHierarchy] != 1) {
            $sql .= " left join actionclientedenied acd on acd.actionid=ga.actionid and acd.cliente_id= " . intval($_SESSION['cliente_id']);
        }

        $sql .= " LEFT JOIN actions a ON ga.actionid = a.actionid
                  LEFT JOIN actions ah ON find_in_set(a.actionid,ah.actionraiz) > 0
                   WHERE ag.accountid=" . $account;
        if ($_SESSION[myHierarchy] != 1) {
            $sql .= " and acd.actionid is null";
        }


        //                 AND (";
        //                    //."          AND (upper(a.actionname)='".strtoupper($actionName)."'";
        //                    if ($hastaaction > 0 )	 {
        //                        $sql .= "   (ga.actionid between ".$actionName." and ".$hastaaction.") ";
        //                    } else {
        //                        $sql .="    ga.actionid = '".$actionName."'";
        //                        //	  $sql .="    or ga.actionid = ".$raiz;
        //                        $sql .="    or ((ah.actionraiz is null or ah.actionraiz = '') and ga.actionid = ".$raiz.")";
        //                        $sql .="    or (ah.actionraiz > '' and ah.actionid = '".$actionName."')";
        //                    }

        //                    $sql .= ")";

        $sql .= " " . $modfiltra;

        $sql .= " union SELECT a.actionmodulos FROM groupaccounts ag
                left join accounts on accounts.accountid = ag.accountid
,groupaction_eventual ge
				LEFT JOIN actions a ON gae_actionid = a.actionid
                LEFT JOIN actions ah ON find_in_set(a.actionid,ah.actionraiz) > 0";
        if ($_SESSION[myHierarchy] != 1) {
            $sql .= " left join actionclientedenied acd on acd.actionid=gae_actionid and acd.cliente_id= " . intval($_SESSION['cliente_id']);
        }

        $sql .= " WHERE ag.accountid=" . $account . "
                    and gae_groupid = ag.groupid
                    and find_in_set(accounts.cliente_id,gae_clientes) > 0
                    and gae_fecha_desde < now() and gae_fecha_hasta > now()";
        if ($_SESSION[myHierarchy] != 1) {
            $sql .= " and acd.actionid is null";
        }

        //                         AND (";
        //                     //          ."          AND (upper(a.actionname)='".strtoupper($actionName)."'";
        //                     //          ."          or ag.actionid = '".$actionName."'"  ;

        //                     if ($hastaaction > 0 )	 {
        //                         $sql .= "   (gae_actionid between ".$actionName." and ".$hastaaction.") ";
        //                     } else {
        //                         $sql .="    gae_actionid = '".$actionName."'";
        //                         //                       $sql .="    or aae_actionid = ".$raiz;
        //                         $sql .="    or ((ah.actionraiz is null or ah.actionraiz = '') and gae_actionid = ".$raiz.")";
        //                         $sql .="    or (ah.actionraiz > '' and ah.actionid = '".$actionName."')";
        //                     }
        //                     $sql .=")".$modfiltra;
        $sql .= " " . $modfiltra;


        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_NUM)) {
                foreach (explode(",", $row[0]) as $v) $modulos[$v] = 1;
            }
        } catch (PDOException  $e) {
        }


        $sql = "SELECT a.actionmodulos FROM accountaction ag
               LEFT JOIN actions a ON ag.actionid = a.actionid";
        if ($_SESSION[myHierarchy] != 1) {
            $sql .= " left join actionclientedenied acd on acd.actionid=ag.actionid and acd.cliente_id= " . intval($_SESSION['cliente_id']);
        }

        $sql .= " LEFT JOIN actions ah ON find_in_set(a.actionid,ah.actionraiz) > 0
               WHERE ag.accountid=" . $account;
        if ($_SESSION[myHierarchy] != 1) {
            $sql .= " and acd.actionid is null";
        }


        $sql .= " union SELECT a.actionmodulos FROM accountaction_eventual "
            . " LEFT JOIN actions a ON aae_actionid = a.actionid";
        if ($_SESSION[myHierarchy] != 1) {
            $sql .= " left join actionclientedenied acd on acd.actionid=aae_actionid and acd.cliente_id= " . intval($_SESSION['cliente_id']);
        }

        $sql .= " LEFT JOIN actions ah ON find_in_set(a.actionid,ah.actionraiz) > 0
    	         WHERE aae_accountid=" . $account
            . " and aae_fecha_desde < now() and aae_fecha_hasta > now()";
        if ($_SESSION[myHierarchy] != 1) {
            $sql .= " and acd.actionid is null";
        }


        try {
            $resultp = $this->pDBisla->query($sql);
            while ($row = $resultp->fetch(PDO::FETCH_NUM)) {
                foreach (explode(",", $row[0]) as $v) $modulos[$v] = 1;
            }
        } catch (PDOException  $e) {
        }

        return $modulos;
    }


    /**
     * Method to get the accountid. You have to verify the the account to get it.
     * @public
     * @returns integer
     */
    function GetAccountID()
    {
        return $this->mAccountID;
    }

    /**
     * Method to get the hierarch level for the user.
     * You have to verify the account to get it.
     * @public
     * @returns integer
     */
    function GetHierarchy()
    {
        return $this->mHierarchy;
    }

    function GetDbCliente()
    {
        return $this->mDbCliente;
    }
    function GetGobProvincia()
    {
        return $this->mGobProvincia;
    }
    function GetHostCliente()
    {
        return $this->mHostCliente;
    }

    function GetClienteId()
    {
        return $this->ClienteId;
    }
    function GetFechaDesdeReporte()
    {
        return $this->FechaDesdeReporte;
    }
    /**
     * Method to check the bad attempt number of tries from the Accounts class.
     * @public
     * @returns integer
     */
    function BadAttempt()
    {
        #echo 'function BadAttempt: <br>';
        #if ($_SESSION['LOG_ACTIVITIES']) {
        $WriteLog = new WriteLog();
        $WriteLog->SetAccountId($this->mAccountID);
        $WriteLog->SetUserName($this->mUsername);
        $WriteLog->SetActivityId(1);

        $WriteLog->WriteToLog();
        unset($WriteLog);
        #}

        $Accounts = new Accounts();

        if (!$Accounts->BadAttempt(
            $this->mAccountID,
            $this->mAccountTries,
            $this->mLastTriedDate
        ))
            $this->SetErrorMessage($Accounts->GetErrorMessage());
        unset($Accounts);
    }

    /**
     * Method to write to the log for successful Logins.
     * @public
     * @returns void
     */
    function SuccessfulLogin()
    {
        global $cant_usuarios;

        #if ($_SESSION['LOG_ACTIVITIES'])
        #{
        $WriteLog = new WriteLog();
        $WriteLog->SetAccountId($this->mAccountID);
        $WriteLog->SetUserName($this->mUsername);
        $WriteLog->SetActivityId(3);

        $WriteLog->WriteToLog();
        unset($WriteLog);
        #}
    }

    /**
     * Method to reset the tries counter from the table after a successful login.
     * @public
     * @returns void
     */
    function ResetAccountTries($accountid)
    {
        $Accounts = new Accounts();
        $Accounts->Field($Accounts->mKeyName, $accountid);
        $Accounts->Field("tries", 0);
        $Accounts->Field("lasttrieddate", 0);

        $Accounts->Update();

        unset($Accounts);
    }

    /**
     * Method to set the error message.
     * @public
     * @returns bool
     */
    function SetErrorMessage($message)
    {
        if (is_string($message))
            $this->mErrorMessage = $message;
        return true;
    }

    /**
     * Method to get the error message.
     * @public
     * @returns string
     */
    function GetErrorMessage()
    {
        return $this->mErrorMessage;
    }

    /**
     * Method to print any error message generated by ADODB to the screen.
     * @public
     * @returns void
     */
    function EchoError($message, $sql = "")
    {
        //  	die();
        $myForm = new Form("dummy");

        $myForm->SetNumberOfColumns(1);
        $myForm->SetCellSpacing(1);
        $myForm->SetCellPadding(5);
        $myForm->SetBorder(0);
        $myForm->SetAlign("center");
        $myForm->SetTableWidth("60%");
        $myForm->SetTableHeight(null);
        $myForm->SetCSS($_SESSION["CSS"]);
        $myForm->SetEmptyCells(true);
        $myForm->SetFormTagRequired(false);
        $myForm->SetFormHeader("Error Ocurrido");

        $myForm->AddFormElementToNewLine(new Label($name = "lb1", $value = "Nombre del Script"));
        $Label = new Label("lbl2", $_SERVER['SCRIPT_FILENAME']);
        $Label->SetClass("DataTD");
        $myForm->AddFormElementToNewLine($Label);

        $Label->SetColSpan($myForm->GetNumberOfColumns());
        $Label->SetClass("");
        $Label->SetValue("Message");
        $myForm->AddFormElementToNewLine($Label);

        $Label->SetColSpan($myForm->GetNumberOfColumns());
        $Label->SetClass("DataTD");
        $Label->SetValue($message);
        $myForm->AddFormElementToNewLine($Label);

        if ($sql) {
            $Label->SetColSpan($myForm->GetNumberOfColumns());
            $Label->SetClass("");
            $Label->SetValue("SQL");
            $myForm->AddFormElementToNewLine($Label);
            $Label->SetClass("DataTD");
            $Label->SetValue($sql);
            $myForm->AddFormElementToNewLine($Label);
        }


        echo $myForm->GetFormInTable();
        die;
    }

    /**
     * Method to send a Login screen.
     * @public
     */
    function PromptLogin($FormElements)
    {
        $myForm = new Form("login");
        $myForm->SetNumberOfColumns(2);
        $myForm->SetCellSpacing(1);
        $myForm->SetCellPadding(5);
        $myForm->SetBorder(0);
        $myForm->SetAlign("center");
        $myForm->SetTableWidth("400");
        $myForm->SetTableHeight(null);
        $myForm->SetCSS($_SESSION["CSS"]);
        $myForm->SetEmptyCells(false);
        $myForm->SetFormHeader("Login");

        $self = basename($_SERVER['PHP_SELF']);

        $myForm->SetAction($self);

        $myForm->SetErrorMessage($FormElements['__error']);

        $myForm->AddFormElement(new Label($name = "lbl1", $value = "Usuario :"));

        $myForm->AddFormElement(new TextField($name = "username", $value = $FormElements['username'], $size = 20, $maxLength = 35, $displayonly = false));

        $myForm->AddFormElement(new Label($name = "lbl2", $value = "Contrase�a :"));

        $myForm->AddFormElement(new Password($name = "password", $value = "", $size = 16, $maxlength = 16, $extra = ""));

        # lets add some buttons to our form

        $buttons = new ObjectArray("buttons");
        $buttons->AddObject(new SubmitButton($name = "Submit", $value = "Seguir"));
        $buttons->AddObject(new ResetButton($name = "Reset", $value = "Restablecer"));

        $buttons->SetCellAttributes(array("align" => "middle"));
        $buttons->SetColSpan($myForm->GetNumberOfColumns());

        $myForm->AddFormElementToNewLine($buttons);


        echo $myForm->GetFormInTable();
    }

    /**
     * Method to create MD5 password for initial admin use.
     * @public
     */
    function PromptMD5Password($FormElements)
    {
        $myForm = new Form("md5");
        $myForm->SetNumberOfColumns(2);
        $myForm->SetCellSpacing(1);
        $myForm->SetCellPadding(4);
        $myForm->SetBorder(0);
        $myForm->SetAlign("center");
        $myForm->SetTableWidth("400");
        $myForm->SetTableHeight(null);
        $myForm->SetCSS($_SESSION["CSS"]);
        $myForm->SetEmptyCells(true);
        $myForm->SetFormHeader("Creaci�n de Contrase�a MD5");

        $self = basename($_SERVER['PHP_SELF']);

        $myForm->SetAction($self);

        $myForm->SetErrorMessage($FormElements['__error']);

        $myForm->AddFormElement(new Label($name = "lbl2", $value = "Contrase�a :"));

        $myForm->AddFormElement(new TextField($name = "password", $value = $FormElements['password'], $size = 16, $maxLength = 16, $displayonly = false));

        $myForm->AddFormElement(new Label($name = "lbl1", $value = "Contrase�a MD5 :"));

        $myForm->AddFormElement(new TextField($name = "md5", $value = $FormElements['md5'], $size = 50, $maxLength = 50, $displayonly = false));

        # lets add some buttons to our form

        $buttons = new ObjectArray("buttons");
        $buttons->AddObject(new SubmitButton($name = "Submit", $value = "Crear MD5"));

        $buttons->SetCellAttributes(array("align" => "middle"));
        $buttons->SetColSpan($myForm->GetNumberOfColumns());
        $buttons->SetClass("FieldCaptionTD");

        $myForm->AddFormElementToNewLine($buttons);


        $value = "<a class=\"" . $_SESSION["CSS"] . "LinkButton\" href=\"adminmenu.php\"><-- Volver al Men� Seguridad</a>";
        $passtru = new PassTru($value);
        $passtru->SetColSpan(2);
        #$passtru->SetClass("FieldCaptionTD");

        $myForm->AddFormElement($passtru);


        echo $myForm->GetFormInTable();
    }
}
