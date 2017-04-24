<?php

// *******************COPYRIGHT NOTICE*******************
// THIS CODE IS COPYRIGHT, RESPECTIVE E-MAIL APPLICATION, 2000-2009.
// THIS CODE MAY NOT BE USED IN ANY APPLICATION OTHER THAN
// THE E-MAIL SERVICE FROM WHICH IT WAS ORIGINALLY RETRIEVED.
//
// 
// *******************INSTRUCTIONS*******************
// THIS FILE SHOULD BE PLACED IN THE ROOT DIRECTORY OF
// YOUR WEB SERVER.
// 
// *****************VERSION INFORMATION***************
// LAST UPDATED 2017-04-24

// Number of seconds to allow for execution of this script
set_time_limit(1200);

// Define constants for output formatting
const ROW_DELIMITER = "WG0ROWWG0";
const COL_DELIMITER = "WG0COLWG0";
const FIELDNAME_DELIMITER = "___ASDF---BREAK";
const EOF = "WANGO-ENDOFDATASTREAM";

// Get POST data
$strAction = $_POST["action"];
$strMachineName = $_POST['machinename'];
$strDBName = $_POST["dbname"];
$strUsername = $_POST["username"];
$strPassword = $_POST["password"];
$strQueryString = $_POST["querystring"];
$strDebug = $_POST["debug"];
$strMethod = $_POST["method"];

// Remove quoting
if (get_magic_quotes_gpc())
{
    $strAction = stripslashes($strAction);
    $strDBName = stripslashes($strDBName);
    $strUsername = stripslashes($strUsername);
    $strPassword = stripslashes($strPassword);
    $strQueryString = stripslashes($strQueryString);
    $strMachineName = stripslashes($strMachineName);
}

// Internal replacements for provided data
$strAction = str_replace("ABC-WANGOMAIL-ABC", chr(0), $strAction);
$strDBName = str_replace("ABC-WANGOMAIL-ABC", chr(0), $strDBName);
$strUsername = str_replace("ABC-WANGOMAIL-ABC", chr(0), $strUsername);
$strPassword = str_replace("ABC-WANGOMAIL-ABC", chr(0), $strPassword);
$strQueryString = str_replace("ABC-WANGOMAIL-ABC", chr(0), $strQueryString);
$strMachineName = str_replace("ABC-WANGOMAIL-ABC", chr(0), $strMachineName);


class DatabaseHelper {
    private $method = NULL;

    private $machineName = NULL;
    private $dbName = NULL;
    private $username = NULL;
    private $password = NULL;

    private $debug = false;

    private $conn = NULL;

    function __construct($_machineName, $_dbName, $_username, $_password, $_debug = false, $_method = NULL) {
        // Determine which method to use.  Prefer PDO, mysqli, then mysql.
        if (defined('PDO::MYSQL_ATTR_LOCAL_INFILE')) {
            $this->method = 'pdo';
        }
        else if (function_exists('mysqli_connect'))
        {
            $this->method = 'mysqli';
        }
        else if (function_exists('mysql_connect')) {
            $this->method = 'mysql';
        }
        else
        {
            throw new Exception('No suitable database connection method found!');
        }

        if($_method != NULL){
            $this->method = $_method;
        }

        if ($_debug == "true") {
            $debug = true;
        }

        if ($debug) {
            echo("Connection method: $this->method\n");
        }

        $this->machineName = $_machineName;
        $this->dbName = $_dbName;
        $this->username = $_username;
        $this->password = $_password;
    }

    // Connects to the database
    function connect() {
        switch($this->method) {
            case "pdo":
                try {
                    $this->conn = new PDO("mysql:host=$this->machineName;dbname=$this->dbName", $this->username, $this->password);
                    
                    // set the PDO error mode to exception
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                catch(PDOException $e)
                {
                    throw new Exception("Connection failed: $e->getMessage()");
                }
                break;
            case "mysqli";
                $this->conn = mysqli_connect($this->machineName, $this->username, $this->password, $this->dbName);

                if (mysqli_connect_errno())
                {
                    throw new Exception("Connection failed: $mysqli_connect_error()");
                }
                break;
            case "mysql":
                $this->conn = mysql_connect($this->machineName, $this->username, $this->password) or die ("Connection failed: " . mysql_error());
                mysql_select_db($this->dbName, $this->conn) or die("Could not select database");
                break;
        }
    }

    // Runs a non-query
    function executeNonQuery($query, $successMessage, $failureMessage) {
        switch($this->method) {
            case "pdo":
                if ($this->conn->query($query)) {
                    return $successMessage;
                }
                else
                {
                    return $failureMessage;
                }
                break;
            case "mysqli";
                if (mysqli_query($this->conn, $query)) {
                    return $successMessage;
                }
                else
                {
                    return $failureMessage;
                }
                break;
            case "mysql":
                if (mysql_query($query)) {
                    return $successMessage;
                }
                else
                {
                    return $failureMessage;
                }
                break;
        }
    }

    // Retrieves records from the database
    function getRecords($query) {
        switch($this->method) {
            case "pdo":
                if ($rs = $this->conn->query($query)) {
                    $fieldNames = array();
                    $rows = array();

                    while($row = $rs->fetch(PDO::FETCH_ASSOC)) {
                        // Get field names if we don't have them yet
                        if(sizeof($fieldNames) == 0) {
                            $fieldNames = array_keys($row);
                        }

                        // Get rows
                        $rows[] = $row;
                    }

                    return array($fieldNames, $rows);
                }
                else {
                    throw new Exception("Error getting data.");
                }
                break;
            case "mysqli";
                if ($rs = mysqli_query($this->conn, $query)) {
                    $fieldNames = array();
                    $rows = array();

                    // Get field names
                    foreach(mysqli_fetch_fields($rs) as &$field) {
                        $fieldNames[] = $field->name;
                    }

                    // Get rows
                    while ($row = $rs->fetch_row()) {
                        $rows[] = $row;
                    }

                    return array($fieldNames, $rows);
                }
                else {
                    throw new Exception("Error getting data.");
                }
                break;
            case "mysql":
                if ($rs = mysql_query($query)) {
                    $fieldNames = array();
                    $rows = array();

                    // Get field names
                    for ($i = 0; $i < mysql_num_fields($rs); $i++) {
                        $fieldInfo = mysql_fetch_field($rs, $i);

                        $fieldNames[$i] = $fieldInfo->name;
                    }

                    // Get rows
                    while ($row = mysql_fetch_row($rs)) {
                        $rows[] = $row;
                    }

                    return array($fieldNames, $rows);
                }
                else {
                    throw new Exception("Error getting data.");
                }
                break;
        }
    }

    function close() {
        switch($this->method) {
            case "pdo":
                $this->conn = null;
                break;
            case "mysqli";
                mysqli_close($this->conn);
                break;
            case "mysql":
                mysql_close($this->conn);
                break;
        }
    }
}

$databaseHelper = new DatabaseHelper($strMachineName, $strDBName, $strUsername, $strPassword, $strDebug, $strMethod);
$databaseHelper->connect();

switch($strAction) {
    case "massmail":
        // Get the data using our chosen method
        $response = $databaseHelper->getRecords($strQueryString);

        // Build field names
        $fieldNameString = @implode(",", $response[0]);
        $fieldNameString .= FIELDNAME_DELIMITER;

        // Build rows
        $rowsString = '';

        foreach ($response[1] as &$row) {
            $rowsString .= @implode(COL_DELIMITER, $row);
            $rowsString .= ROW_DELIMITER;
        }

        $rowsString .= EOF;

        // Final response
        echo( $fieldNameString . $rowsString);
        break;
    case "unsubscribe":
        echo($databaseHelper->executeNonQuery($strQueryString, 'unsubscribe-sync-success', 'unsubscribe-sync-failure'));
        break;
    case "bounce":
        echo($databaseHelper->executeNonQuery($strQueryString, 'bounce-sync-success', 'bounce-sync-failure'));
        break;
    case "view":
        echo($databaseHelper->executeNonQuery($strQueryString, 'view-sync-success', 'view-sync-failure'));
        break;
    case "click":
        echo($databaseHelper->executeNonQuery($strQueryString, 'click-sync-success', 'click-sync-failure'));
        break;
    case "sent":
        echo($databaseHelper->executeNonQuery($strQueryString, 'sent-sync-success', 'sent-sync-failure'));
        break;
    case "change":
        echo($databaseHelper->executeNonQuery($strQueryString, 'change-sync-success', 'change-sync-failure'));
        break;
    case "job":
        echo($databaseHelper->executeNonQuery($strQueryString, 'job-sync-success', 'job-sync-failure'));
        break;
    case "action":
        echo($databaseHelper->executeNonQuery($strQueryString, 'action-sync-success', 'action-sync-failure'));
        break;
    case "forward":
        echo($databaseHelper->executeNonQuery($strQueryString, 'forward-sync-success', 'forward-sync-failure'));
        break;
    case "test":
        echo($databaseHelper->executeNonQuery($strQueryString, 'test-sync-success', 'test-sync-failure'));
        break;
}

// Close the database connection
$databaseHelper->close();

?>