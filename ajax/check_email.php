<?php

include_once ('../config.php');
include_once ('../classes/sessions.php');
include_once ('../lib/validation.php');
try {
include_once ('../db_connect.php');
} catch (Exception $e) {
	
}
include_once ('../classes/db.php');

include_once ('../lib/strings.php');
include_strfiles(array ('question', 'general', 'user'));

if (validateEmail($_POST['email'])) {
	$res = DB::executeSelect('users_info', 'UserID', array ('email'=>$_POST['email']));
	if ($res) {
		echo 'taken';
	} else {
		echo 'avail';
	}
} else {
	echo 'invalid';
}
?>
