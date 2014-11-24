<?php

include_once ('../config.php');
include_once ($CFG->apploc . '/classes/sessions.php');
include_once ($CFG->apploc . '/lib/validation.php');
include_once ($CFG->apploc . '/lib/funcs.php');
try {
include_once ($CFG->apploc . '/db_connect.php');
} catch (Exception $e) {
	
}
include_once ($CFG->apploc . '/classes/db.php');

include_once ($CFG->apploc . '/lib/strings.php');
include_strfiles(array ('question', 'general', 'user'));



// ajax query type
if (!empty ($_POST)) {
	if ($_POST['q'] == 'centres') {
		// do we need to add a 0 option as first dropdown element?
		$anyrow = isset ($_POST['anyrow']) ? $_POST['anyrow'] : false;
		// if new user, hide archived/deleted centres from selection drop-down
		if ($_POST['newuser']) {
			echo drawCombo ('ctr', 'centres', array ('providerid'=>$_POST['provider'], 'visible'=>1), 'name', 'CentreID', $anyrow);
		} else {
			echo drawCombo ('ctr', 'centres', array ('providerid'=>$_POST['provider']), 'name', 'CentreID', $anyrow);
		}
	} else if ($_POST['q'] == 'advisors') {
		// update 2012-12-18 advisorroleid id is pushed from cfg file, rather than hardcoded in 'roleid' below
		echo drawCombo ('advisor', 'users_info', array ('providerid'=>$_POST['provider'], 'roleid'=>$CFG->advisorroleid), array ('fname', 'sname'), 'UserID');
	}
}
?>
