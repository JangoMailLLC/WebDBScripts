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
// LAST UPDATED 1/19/2009

set_time_limit(1200);

$strRowDelimiter="WG0ROWWG0";
$strColDelimiter="WG0COLWG0";
$strEOF="WANGO-ENDOFDATASTREAM";

$strFieldNameDelimiter = "___ASDF---BREAK";

// $straction      = $HTTP_POST_VARS["action"];
// $strdbname      = $HTTP_POST_VARS["dbname"];
// $strusername    = $HTTP_POST_VARS["username"];
// $strpassword    = $HTTP_POST_VARS["password"];
// $strquerystring = $HTTP_POST_VARS["querystring"];
// $strmachinename = $HTTP_POST_VARS['machinename'];


if ( phpversion() >= '4.1.0' )  {
	$straction      = $_POST["action"];
	$strdbname      = $_POST["dbname"];
	$strusername    = $_POST["username"];
	$strpassword    = $_POST["password"];
	$strquerystring = $_POST["querystring"];
	$strmachinename = $_POST['machinename'];
}

else

{
	$straction      = $HTTP_POST_VARS["action"];
	$strdbname      = $HTTP_POST_VARS["dbname"];
	$strusername    = $HTTP_POST_VARS["username"];
	$strpassword    = $HTTP_POST_VARS["password"];
	$strquerystring = $HTTP_POST_VARS["querystring"];
	$strmachinename = $HTTP_POST_VARS['machinename'];
}

if (get_magic_quotes_gpc())

{
     $straction      = stripslashes($straction);
     $strdbname      = stripslashes($strdbname);
     $strusername    = stripslashes($strusername);
     $strpassword    = stripslashes($strpassword);
     $strquerystring = stripslashes($strquerystring);
     $strmachinename = stripslashes($strmachinename);
}


$straction=str_replace("ABC-WANGOMAIL-ABC",chr(0),$straction);
$strdbname=str_replace("ABC-WANGOMAIL-ABC",chr(0),$strdbname);
$strusername=str_replace("ABC-WANGOMAIL-ABC",chr(0),$strusername);
$strpassword=str_replace("ABC-WANGOMAIL-ABC",chr(0),$strpassword);
$strquerystring=str_replace("ABC-WANGOMAIL-ABC",chr(0),$strquerystring);
$strmachinename=str_replace("ABC-WANGOMAIL-ABC",chr(0),$strmachinename);

// Connect to a mysql database

        mysql_connect($strmachinename, $strusername, $strpassword) or die ("Could not connect : " . mysql_error());
 
        mysql_select_db($strdbname) or die("Could not select database");



switch ($straction):

        case("massmail"):
                if($rs = mysql_query($strquerystring)):
                        // output comma-delimited list of fieldnames followed by $strFieldNameDelimiter
                                
                                $fieldnames = array();
                                $resultstring = "";
                                
                                for ($i = 0; $i < mysql_num_fields($rs); $i++) 
                                {
                                        $fieldInfo = mysql_fetch_field($rs, $i);
                                        
                                        $fieldnames[$i] = $fieldInfo->name;
                                }
                                
                                $resultstring = @implode(",", $fieldnames);
                        
                                $resultstring .= $strFieldNameDelimiter;
                
                        // output each row
                                while ($arr_row = mysql_fetch_row($rs))
                                {
                                        $resultstring .= @implode($strColDelimiter, $arr_row);
                                        
                                        $resultstring .= $strRowDelimiter;
                                        print $resultstring;
                                        $resultstring = "";
                                }
                                                
                        // write EOF
                                print $strEOF;
                endif;
                break;
        
        case("unsubscribe"):
                print ($rs = mysql_query($strquerystring)) ? "unsubscribe-sync-success" : "unsubscribe-sync-failure";
                break;
                
        case("bounce"):
                print ($rs = mysql_query($strquerystring)) ? "bounce-sync-success" : "bounce-sync-failure";
                break;
        
        case("view"):
                print ($rs = mysql_query($strquerystring)) ? "view-sync-success" : "view-sync-failure";
                break;
        
        case("click"):
                print ($rs = mysql_query($strquerystring)) ? "click-sync-success" : "click-sync-failure";
                break;
        
        case("sent"):
                print ($rs = mysql_query($strquerystring)) ? "sent-sync-success" : "sent-sync-failure";
                break;
        
        case("change"):
                print ($rs = mysql_query($strquerystring)) ? "change-sync-success" : "change-sync-failure";
                break;
        
        case("job"):
                print ($rs = mysql_query($strquerystring)) ? "job-sync-success" : "job-sync-failure";
                break;

        case("action"):
                print ($rs = mysql_query($strquerystring)) ? "action-sync-success" : "action-sync-failure";
                break;

        case("forward"):
                print ($rs = mysql_query($strquerystring)) ? "forward-sync-success" : "forward-sync-failure";
                break;
        
        case("test"):
                print "test-success";
                break;
                
        default:
                break;
                
endswitch;

?>