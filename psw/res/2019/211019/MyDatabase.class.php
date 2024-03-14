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
    $this->gDB = NewADOConnection(DATABASE_SOFTWARE);
    if(!@$this->gDB->PConnect(DB_LOCATION, DB_ACCOUNT, DB_PASSWORD, DB_DATABASE)) {
      $this-> dbFailure( );
    }
    if ($_SESSION[host_cli] > ""){
    $this->gDBcli = NewADOConnection(DATABASE_SOFTWARE);
    if(!@$this->gDBcli->PConnect($_SESSION[host_cli], DB_ACCOUNT, DB_PASSWORD, DB_DATABASE)) {
      $this-> dbFailure( );
    }
    }
    
    $this->pDB = new PDO("mysql:host=".DB_LOCATION.";dbname=".DB_DATABASE.";charset=utf8", DB_ACCOUNT, DB_PASSWORD);
    $this->pDB ->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
       if ($_SESSION[host_cli] > ""){
           $this->pDBcli = new PDO("mysql:host=".$_SESSION[host_cli].";dbname=".DB_DATABASE.";charset=utf8",DB_ACCOUNT, DB_PASSWORD);
           $this->pDBcli ->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );       }
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
    foreach ($this->mTableFields as $key =>$value) {
      if ($key <> $this->mKeyName) {
        $FFieldValue =  ToSQL(htmlspecialchars($value['Value']),$this->mTableFields['type']);
        $TableFieldNames .= ",$key";
        $TableFieldValues .= "," . $FFieldValue;

        if ($value['unique']) {
          if ($UniqueQuery)
            $UniqueQuery .= " or $key=$FFieldValue";
          else
            $UniqueQuery = "SELECT * FROM $this->mTableName WHERE $key = $FFieldValue";
        }
      }
    }

    //      print "$UniqueQuery";
    $result = $this->gDB->Execute($UniqueQuery);

    if ($result === false) {
      $this->mErrorMessage = 'error inserting: '
                          .$this->gDB->ErrorMsg( );

      if ($_SESSION['IS_ERROR_REPORTING'])
        $mySecurity-> EchoError( $this->gDB->ErrorMsg() );

      return false;
    }
    else {
      # if there are any rows returned by the unique query
      # then the addition cannot be made.

      if ($result-> RecordCount() > 0)
        return false;
    }

    $NewID = $this->gDB->GenID($seqName = $this->mKeyName.'_seq',$startID=1);

    if (!$NewID) {
      $this->mErrorMessage = 'error on get nextval: '
                          .$this->gDB->ErrorMsg( );

      if ($_SESSION['IS_ERROR_REPORTING'])
        $mySecurity-> EchoError( $this->gDB->ErrorMsg() );

      return false;
    }
    else {
      $sql = "INSERT INTO $this->mTableName ($this->mKeyName" . $TableFieldNames .") values ($NewID" . $TableFieldValues .") ";

      if ($extra != "")
        $sql .= $extra;

      if ($result = $this->gDB->Execute($sql) === false) {
        $this->mErrorMessage = 'error on insert: '
                            .$this->gDB->ErrorMsg( );

        if ($_SESSION['IS_ERROR_REPORTING'])
          $mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

        return false;
      }
      else
        return $NewID;
    }
    return false;
  }

  /**
  * Method to Update data from a table.
  * @public
  * @returns bool
  */
  function Update($haltOnError = 1)
  {
    $mySecurity = new Security( );

    foreach ($this->mTableFields as $key =>$value)
    {
      if ($key <> $this->mKeyName) {
        if (isset($value['Value'])) {
          if ($UpdateValues)
            $UpdateValues .= ", ";
          $UpdateValues .= "$key="
          .ToSQL(htmlspecialchars($value['Value']),$value['type']);
        }
      }
    }

    $sql = "UPDATE $this->mTableName SET $UpdateValues ";

    if ($this->mKeyName != "")
      $sql .= "WHERE $this->mKeyName = "
          .$this->mTableFields[$this->mKeyName]['Value'] ;

     #print_r ($sql);
     #exit;


    if ($this->gDB->Execute($sql) === false) {
      $this->mErrorMessage = 'error actualizando: '.$this->gDB->ErrorMsg( )."<br>".$sql;

      if ($_SESSION['IS_ERROR_REPORTING'])
        $mySecurity-> EchoError(  $this->gDB->ErrorMsg(),$sql  );

      return false;
    }
    else {
      $this->mErrorMessage = "$this->mTableName correctamente modificada.";
      return true;
    }
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
    echo $this->gDB->ErrorMsg( );

    return true;
  }

}


?>