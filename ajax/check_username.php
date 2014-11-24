<?php

include_once ('../config.php');
include_once ('../classes/sessions.php');
try {
include_once ('../db_connect.php');
} catch (Exception $e) {
	
}
include_once ('../classes/db.php');
include_once ('../lib/validation.php');

include_once ('../lib/strings.php');
include_strfiles(array ('question', 'general', 'user'));

if (validateUsername($_POST['username'])) {

	$res = DB::executeSelect('users_info', '*', array ('loginid'=>$_POST['username']));
	
	if (!$res) {
		echo 'avail';
	} else {
		echo 'taken';
	}

} else {
	echo 'invalid';
}

?>
