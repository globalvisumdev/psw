<?php
include_once "commonFunctions.php";
require_once "Security.class.php";
/**
* A class that handles adding, modifying and deleting the groups
* from the table.
* @author Bulent Tezcan. bulent@greenpepper.ca
*/
class MyDatabase
{

  /**
  * Constructor of the class MyDatabase.
  * @public
  */
  function MyDatabase( )
  {
//     $this->gDB = NewADOConnection(DATABASE_SOFTWARE);
//     if(!@$this->gDB->PConnect(DB_LOCATION, DB_ACCOUNT, DB_PASSWORD, DB_DATABASE)) {
//       $this-> dbFailure( );
//     }
//     if ($_SESSION['host_cli'] > ""){
//     $this->gDBcli = NewADOConnection(DATABASE_SOFTWARE);
//     if(!@$this->gDBcli->PConnect($_SESSION['host_cli'], DB_ACCOUNT, DB_PASSWORD, DB_DATABASE)) {
//       $this-> dbFailure( );
//     }
//     }
global $ezMap;    
    $this->pDB = null;
    $this->pDBisla = null;
    $this->pDBcli = null;
    
try {
    $this->pDB = new PDO("mysql:host=".DB_LOCATION.";dbname=".DB_DATABASE.";charset=latin1", DB_ACCOUNT, DB_PASSWORD);
    $this->pDB ->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

    $this->pDBisla = new PDO("mysql:host=".$ezMap['isla_host'].";dbname=".DB_DATABASE.";charset=latin1", $ezMap['isla_user_admin'], $ezMap['isla_pass_admin']);
    $this->pDBisla ->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    }catch(PDOException  $e ){
        die();
    }
    
       if ($_SESSION[host_cli] > ""){
           try {
               $this->pDBcli = new PDO("mysql:host=".$_SESSION[host_cli].";dbname=".$_SESSION['db_cli'].";charset=latin1",DB_ACCOUNT, DB_PASSWORD);
               $this->pDBcli ->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
           }catch(PDOException  $e ){
               unset($this->pDBcli );
           }
       }
  }

  /**
  * Method to Insert data to a table by using the nextval for the key. You have to
  * supply table name, keyname, field names and values. It will return the key of
  * the table on success. False on error.
  * @public
  * @returns newid
  */
  function InsertNew($extra="", $haltOnError = 1)
  {
    // Before we can add anything new, we first have to determine if the new values are going
    // to conflict with any values already in the DB. We'll go through the fields looking for
    // unique fields and if there are any, then we'll query the db to determine if any of those
    // unique fields already have the requested value for that field.
    $mySecurity = new Security( );
    
    $datadb=array();
    foreach ($this->mTableFields as $key =>$value) {
        if ($key <> $this->mKeyName) {
//             $FFieldValue =  ToSQL(htmlspecialchars($value['Value']),$this->mTableFields['type']);
             $TableFieldNames .= ",$key";
             $TableFieldValues .= ",:".$key;

            $datadb[':'.$key] = htmlspecialchars($value['Value']);
        }
    }

    $sql = "INSERT INTO $this->mTableName ($this->mKeyName" . $TableFieldNames .") values (null" . $TableFieldValues .") ";
    
    try {
        $resultp = $this->pDB->prepare($sql);
        $resultp->execute($datadb);
        $NewID = $this->pDB->lastInsertId();
    }catch(PDOException  $e ){
        $this->mErrorMessage = 'error inserting: ';
        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error insertando','');
        return false;
    }
        
    return $NewID;
    
  }

  /**
  * Method to Update data from a table.
  * @public
  * @returns bool
  */
  function Update($haltOnError = 1)
  {
    $mySecurity = new Security( );

    $datadb=array();
    foreach ($this->mTableFields as $key =>$value){
        if ($key <> $this->mKeyName) {
            if (isset($value['Value'])) {
                
                $datadb[":".$key] = htmlspecialchars($value['Value']);
                if ($UpdateValues) 
                $UpdateValues .= ", ";
                $UpdateValues .= "$key=:".$key;
            }
        }
    }
    
    $sql = "UPDATE $this->mTableName SET $UpdateValues ";
    if ($this->mKeyName != "") {
        $sql .= " WHERE $this->mKeyName = :k".$this->mKeyName;
        $datadb[":k".$this->mKeyName] = htmlspecialchars($this->mTableFields[$this->mKeyName]['Value']);
    }
    
    try {
        $resultp = $this->pDB->prepare($sql);
        $resultp->execute($datadb);
    }catch(PDOException  $e ){
        $this->mErrorMessage = 'error actualizando: ';
        if ($_SESSION['IS_ERROR_REPORTING']) $this-> EchoError(  'Error actualizando','');
        return false;
    }
    
        
    $this->mErrorMessage = "Correctamente modificada.";
    return true;
        
    
  }

  /**
  * Method to get/set the field value by a given field name.
  * If you want to set a value, the second parameter should be set to the new
  * value you want. If you want to initialize a field, you MUST PASS String
  * 'null'. This will force the method to set the value based on it's type
  * definition in the class.
  * I mean like : $this->mTableFields[$FieldName]['type']= integer will be
  * initialized to zero, because it is an integer, null to a string.
  * @public
  * @returns string
  */
  function Field($FieldName, $Value=null)
  {
    if (strlen($Value))
      $this->mTableFields[$FieldName]['Value'] = $Value;
    else
      return $this->mTableFields[$FieldName]['Value'];
  }

  /**
  * Method to send a message when a Database Failure occurs.
  * @public
  */
  function dbFailure( )
  {
    $warning ="<html><head><body bgcolor=\"red\">
    <font face=\"Arial, Helvetica, sans-serif\" color=\"#FFFFFF\">
      <font size=\"5\">
      database failure!
      </font><p>
      <font size=\"3\">
      there has been a database failure in Security.
      please contact your system adminstrator <a href=\"mailto:".ADMIN_EMAIL."\">here</a>
      and include the error message below:<br>"
      .$this->gDB->ErrorMsg( )
      ."<br>
      </font>
      <p>
    </font>";


    echo $warning;
    //echo $this->gDB->ErrorMsg( );

    return true;
  }

}
