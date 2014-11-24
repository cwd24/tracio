<?php

include_once ("db_config.php");

$username = $CFG->dbuser;
$password = $CFG->dbpass;
$hostname = $CFG->dbhost;	
$dbh = mysql_connect($hostname, $username, $password) 
	or die("Error. Unable to connect to MySQL. Please contact the system administrator.");

$selected = mysql_select_db("tracio",$dbh) or die("Error. Could not select the TRaCIO database.");



?>