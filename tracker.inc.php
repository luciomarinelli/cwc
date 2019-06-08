<?php
//Cookieless Web Counter - tracker code starts here
//Include this tracked in each page you want to count.

//CONFIGURATION

//MySQL database host
$dbhost="sql.yourhost.com";
//Database name
$dbname="dbname_here";
//Database user name
$dbuser="dbuser_here";
//Database password
$dbpass="password_here";
//Table name (default is "contatore")
$tablename="contatore";

//-------CONFIGURATION ENDS HERE-----

$php_self=$_SERVER['PHP_SELF'];
$remote_addr=$_SERVER['HTTP_X_FORWARDED_FOR'];
$http_referer=$_SERVER['HTTP_REFERER'];
$http_user_agent=$_SERVER['HTTP_USER_AGENT'];

$conn = mysql_connect($dbhost,$dbuser,$dbpass);
mysql_select_db($dbname,$conn);
$query = ("INSERT INTO $tablename (php_self,remote_addr,http_referer,http_user_agent) VALUES ('$php_self','$remote_addr','$http_referer','$http_user_agent')");
$result = mysql_query ($query);

//Cookieless Web Counter - tracker code ends here
?>

