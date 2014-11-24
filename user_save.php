<?php
include_once ('./config.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/lib/validation.php');
include_once ($CFG->apploc  . '/lib/funcs.php');

//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('general'));
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="-1" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo_string ('APP_NAME'); ?></title>
<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->printcssfile); ?>" rel="stylesheet" type="text/css" media="print"/>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="js/resizer.js"></script>
</head>
<body>
<?php 

include_once ($CFG->apploc  . '/templates/header.php'); ?>

<?php

if ($_POST['action'] == 'edit') {


	// do some validation checks
	$v_fname = validateName ($_POST['f_name'], 'f');
	$v_sname = validateName ($_POST['s_name'], 's');
	$v_username = validateUsername($_POST['l_id']);
	$v_email = validateEmail($_POST['l_email']);
	
	$v_pass =  true; //validatePasswords($_POST['pass_1'], $_POST['pass_2']);
	$v_gender = !empty ($_POST['gender']);
	$v_sdate = true; //validateDate ($_POST['start_m'], $_POST['start_y']);
	$v_edate = true; //validateDate ($_POST['end_m'], $_POST['end_y']);
	$v_dropdowns = validateDropDowns (array (
										$_POST['ethnicity'],
										$_POST['agegroup'],
										$_POST['prg'],
										$_POST['provider']
									 ));
	
	
	$saveflag = false;
	if ($v_fname && $v_sname && $v_username && $v_email && $v_pass && $v_gender && $v_dropdowns && $v_edate && $v_sdate) {
		$saveflag = true;
	} else {
		echo '<ul>';
		if (!$v_fname) echo '<li>No first name</li>';
		if (!$v_sname) echo '<li>No surname</li>';
		if (!$v_username) echo '<li>No username</li>';
		if (!$v_email) echo '<li>Email invalid or taken</li>';
		if (!$v_pass) echo '<li>Passwords do not match or are too short</li>';
		if (!$v_gender) echo '<li>Gender not specified</li>';
		if (!$v_sdate) echo '<li>No start date</li>';
		if (!$v_edate) echo '<li>No  end date</li>';
		if (!$v_dropdowns) echo '<li>One or more dropdowns incomplete</li>';
		
		
		echo '</ul>';
	}

	if ($saveflag) {
		// - does user already exist? check first
		if (DB::executeSelect('users_info', '*', array ('UserID'=>$_POST['uid']))) {
			// creating a new user
			$q = DB::executeUpdate ('users_info',
			array (
			'providerid'	=> $_POST['provider'],
			//'loginid' 		=> strtolower ($_POST['l_id']),
			'fname' 		=> ucname ($_POST['f_name']),
			'sname' 		=> ucname ($_POST['s_name']),
			'roleid' 		=> $_POST['role'],
			//'password' 		=> md5($_POST['pass_1']),
			//'email'			=> $_POST['l_email'],
			'ethnicityid'	=> $_POST['ethnicity'],
			'gender'		=> $_POST['gender'],
			'ageid'   		=> $_POST['agegroup'],
			'groupid'		=> 1,
			'programmeid'	=> $_POST['prg'],
			//'startdate'		=> date("Y-m-d", mktime(0, 0, 0, $_POST['start_m'], 1, $_POST['start_y'])),
			//'enddate'		=> date("Y-m-d", mktime(0, 0, 0, $_POST['end_m'], 1, $_POST['end_y'])),
			'completed'		=> 0
			));
			if ($q) {
				echo 'User profile updated. <a href="index.php">Click here to return to the main menu.</a>';
				
			} else {
				
				echo 'User profile not saved. Error.' . mysql_error();
			}
		}  else {
			echo 'A user with the loginid "' . $_POST['l_id'] . '" does not exist.';
		}
	} else {
		echo "Form is incomplete. Please click the <strong>Back</strong> button to complete it.";
	}
	
} else {
	
	// do some validation checks
	$v_fname = validateName ($_POST['f_name'], 'f');
	$v_sname = validateName ($_POST['s_name'], 's');
	$v_username = validateUsername($_POST['l_id']);
	$v_email = validateEmail($_POST['l_email']);
	//TODO - need to check not dupe email
	$v_pass = validatePasswords($_POST['pass_1'], $_POST['pass_2']);
	$v_gender = !empty ($_POST['gender']);
	$v_sdate = validateDate ($_POST['start_m'], $_POST['start_y']);
	$v_edate = validateDate ($_POST['end_m'], $_POST['end_y']);
	$v_dropdowns = validateDropDowns (array (
										$_POST['ethnicity'],
										$_POST['agegroup'],
										$_POST['prg'],
										$_POST['provider']
									 ));
	
	
	$saveflag = false;
	if ($v_fname && $v_sname && $v_username && $v_email && $v_pass && $v_gender && $v_dropdowns && $v_edate && $v_sdate) {
		$saveflag = true;
	} else {
		echo '<ul>';
		if (!$v_fname) echo '<li>No first name</li>';
		if (!$v_sname) echo '<li>No surname</li>';
		if (!$v_username) echo '<li>No username</li>';
		if (!$v_email) echo '<li>Email invalid or taken</li>';
		if (!$v_pass) echo '<li>Passwords do not match or are too short</li>';
		if (!$v_gender) echo '<li>Gender not specified</li>';
		if (!$v_sdate) echo '<li>No start date</li>';
		if (!$v_edate) echo '<li>No  end date</li>';
		if (!$v_dropdowns) echo '<li>One or more dropdowns incomplete</li>';
		echo '</ul>';
	}

	if ($saveflag) {
		// update 2012-12-18 learnerrole id is pushed from cfg file, rather than hardcoded in 'roleid' below
		if (!DB::executeSelect('users_info', '*', array ('loginid'=>$_POST['l_id']))) {
			// creating a new user
			$q = DB::executeInsert('users_info',
			array (
			'providerid'	=> $_POST['provider'],
			'loginid' 		=> strtolower ($_POST['l_id']),
			'fname' 		=> ucname ($_POST['f_name']),
			'sname' 		=> ucname ($_POST['s_name']),
			'roleid' 		=> $CFG->learnerroleid,
			'password' 		=> md5($_POST['pass_1']),
			'email'			=> $_POST['l_email'],
			'ethnicityid'	=> $_POST['ethnicity'],
			'gender'		=> $_POST['gender'],
			'ageid'   		=> $_POST['agegroup'],
			'groupid'		=> 1,
			'programmeid'	=> $_POST['prg'],
			'startdate'		=> date("Y-m-d", mktime(0, 0, 0, $_POST['start_m'], 1, $_POST['start_y'])),
			'enddate'		=> date("Y-m-d", mktime(0, 0, 0, $_POST['end_m'], 1, $_POST['end_y'])),
			'completed'		=> 0
			));
			if ($q) {
				echo 'New user "' . $_POST['l_id'] . '" added. <a href="index.php">Click here to return to the main menu.</a>';
				
			} else {
				
				echo 'User not added. Error.' . mysql_error();
			}
		}  else {
			echo 'A user with the loginid "' . $_POST['l_id'] . '" already exists. Click the <strong>Back</strong> button to select another login id.';
		}
	} else {
		echo "Form is incomplete. Please click the <strong>Back</strong> button to complete it.";
	}

}

?>

<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>
