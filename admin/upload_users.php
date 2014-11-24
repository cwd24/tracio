<?php



require_once ('../config.php');
 require_once ($CFG->apploc . '/lib/upload.php');
 include_once ($CFG->apploc . '/classes/db.php');
 include_once ($CFG->apploc . '/lib/funcs.php');
 include_once ($CFG->apploc . '/lib/roles.php');
 include_once ($CFG->apploc  . '/lib/validation.php');
 include_once ($CFG->apploc . '/classes/datagrid.php');
 include_once ($CFG->apploc . '/classes/sessions.php');
 
 Sessions::checkUserLogIn ();
 
 if (!empty ($_GET['action'])) {
 	if ($_GET['action'] == 'cancel') {
 		// if cancelled an import, clear session var which holds csvdata...
 		$_SESSION['csvdata'] = false;
 		unset($_SESSION['csvdata']);
 	}
 }
 //strings code
 include_once ($CFG->apploc  . '/lib/strings.php');
 include_strfiles(array ('question', 'general', 'user'));
 
 if (!has_capability(Sessions::getID(), 'users:import')) {
 	die (return_string ('ACCESS_DENIED'));
 	
 }
 
 $provider = Sessions::getUserInfo('providerid');
 
 $step = 0;
 if (!empty ($_POST)) {
 	if (!empty ($_POST['step'])) {
 		$step = $_POST['step'];
 		// step 0 - clean sheet, on load of empty document
 		// step 1 - file has been uploaded, csv strcture is checked, basic checks for duplicate usernames and emails
 		// step 2 - csv data is checked/sanitised and written to db 
 	}
 }


 ?>
<?php 
	if ($step == 1) {
	// deal with uploaded csv
		if ( !empty ($_FILES)) {
			$errors = array ();
			$uploadedfile = upload_file ();
	
		
			
			
			
			if ($uploadedfile) {
				$csv_array = parse_csv ($uploadedfile);
				if ($csv_array ) {
					
					// okay, now we need to check the data for various things, sanitize the data as an array, and then write them in
					// 1. check for duplicate records in the spreadsheet (by username, email). DISTINCT?
					// 3. existing email addresses checks x
					// 4. existing usernames checks x
					
					// do each validation step by step for easier flagging of errors to user
					if (!noDuplicatesInCSVForField ($csv_array, 'loginid')) {
						$errors[] = "A duplicate record was found in the CSV file for 'loginid'. Please check for duplicates in the CSV file and retry.";
					}
					if (!noDuplicatesInCSVForField ($csv_array, 'email')) {
					//	$errors[]  =  "A duplicate record was found in the CSV file for 'email'. Please check for duplicates in the CSV file and retry.";
					}
					// check for duplicates against existing records in the database
					foreach ($csv_array as $row) { 
					// check against database - check username/loginid
						$dbdupes = DB::executeSelect('users_info', 'loginid', array ('loginid'=>$row['loginid']));
						if ($dbdupes) {
							$errors[] = 'A user with the username "' . $dbdupes['loginid'] . '" already exists on the TRaCIO system.';
						}
					}
					foreach ($csv_array as $row) {
						// check against database - check email
						// is email not empty?
						if ($row['email']) {
							$dbdupes = DB::executeSelect('users_info', 'email', array ('email'=>$row['email']));
							if 	($dbdupes) {
								$errors[] = 'A user with the email "' . $dbdupes['email'] . '" already exists on the TRaCIO system.';
							}
						}
					}
					
				} else {
					$errors[] = 'File structure of CSV file is not correct. Check the number of fields in the document and row data.';
				}
			}
		}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="../external/tablesorter/themes/blue/style.css" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->printcssfile); ?>" rel="stylesheet" type="text/css" media="print"/>
<link href="admin.css" rel="stylesheet" type="text/css" />
<title>Import Users | <?php echo $CFG->appname; ?></title>
</head>

<body class="upload admin">
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<h1>Bulk User Import</h1>
<div class="annot">
<p>Use this screen to import learners into your organisation.</p>
</div>
<div class="subannot_first"></div>
<div class="subannot">
<p>The 'Upload CSV File' section will allow you to 'browse' for a .csv file from your local filesystem. Once a file has been selected click the 'Upload' button to begin import.</p>
<p>An example CSV file along with guidelines on how to modify it, is provided <a href="#info">below</a>.</p>
<p></p>
</div>
<?php if ($step == 0) { ?>
<div class="section"><div class="sectionheader">Upload CSV File</div>
<div class="sectioncontent">
	
	<form id="form1" name="form1" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	  <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
	  <label for="fileField"></label>
	  <input type="hidden" value="1" name="step" />
	  <input type="file" name="uploaded_file" id="uploaded_file" />
	  <input type="submit" value="Upload" />
	</form>
</div>
</div>
	<div class="section"><div class="sectionheader">CSV File Info</div>
<div class="sectioncontent">
		<div id="info">
			
			<p>To learn about how to create a CSV in the correct format for importing users into TRaCIO, <a href="../doc/tracio_csv_guide.pdf" target="_blank">there is a downloadable guide</a> (PDF format).</p>
			<p>If you require the example structure document (CSV format) you can <a target="_blank" href="../doc/import_example.csv">download it here</a>. A blank template file, containing only the fields, is <a target="_blank" href="../doc/import_template.csv">available for download here</a>. This document can be edited using Microsoft Excel or alternative office applications.</p>
			<h2>New User Passwords</h2>
			<p>All new users of TRaCIO will be given the password <em><?php echo $CFG->defaultpassword; ?></em>. Please recommend that users change this upon entry to TRaCIO.</p>
			<h3>CSV Fields</h3>
			<p>The following fields in the csv document need to be cross-referenced to the tables below (this information is provided in the downloadable guide in detail).</p>
			<h3>Gender</h3>
			<?php 
			$temp = array ();
			$temp[] = array ( 'Gender'=>'m', 'Represents'=>'Male');
			$temp[] = array ( 'Gender'=>'f', 'Represents'=>'Female');
			
			$dg = new DataGrid ($temp, 'Gender');
			$dg->setTableClass ();
			$dg->render ();
			?>
			<h3>'EthnicityID'</h3>
			<?php $eths = DB::executeSelect('ethnicity', '*', 'EthnicityID > 0') ;
			
			$dg = new DataGrid ($eths, 'EthnicityID');
			$dg->setPagination(false);
			$dg->setTableClass ();
			$dg->addFieldTitle('name', 'Ethnicity');
			$dg->render ();
			?>
			
			<h3>'CentreID'</h3>
			<p>The following centres are available for you as a provider:</p>
			<?php 
			$centres = DB::executeContainedSelect('centres', array ('CentreID', 'Name'), array('visible'=>1, 'providerid'=>$provider)); 
			
			$dg = new DataGrid ($centres, 'CentreID');
			$dg->setPagination(false);
			$dg->setTableClass ();
			$dg->addFieldTitle('name', 'Centre Name');
			$dg->render ();
			?>
			<p>If you are unsure of centre, enter 0. This will then prompt the user to select a centre on login.
			<h3>'AgeID'</h3>
			<?php
			$ages = DB::executeSelect('age_groups', '*');
			$dg = new DataGrid ($ages, 'AgeID');
			$dg->setPagination(false);
			$dg->setTableClass ();
			$dg->addFieldTitle('name', 'Age Range');
			$dg->render ();
			?>
		</div>
</div>
	</div>
<?php } // end step 0 ?>
<?php
if ($step == 1) {
	if (empty ($errors)) {
			// assuming that we're good to import... (no errors reported)
			?>
<div class="section">
<div class="sectionheader">Found Users</div>
<div class="sectioncontent">
			<p>The structure of the CSV document appears to be sound. The following users were found in the CSV file:</p>
			<?php
			$dg = new DataGrid ($csv_array, 'loginid');
			$dg->setPagination(false);
			$dg->setTableClass ();
			$dg->render ();
			
			// store users array for usage on next screen and checking of data fields etc
			$_SESSION['csvdata'] = $csv_array;
			$_SESSION['uploadedfile'] = $uploadedfile;
						
			?>
			<p>Click the 'Proceed' button below in order to import the users into TRaCIO.</p>
			<form id="form2" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<input type="hidden" value="2" name="step" />
			<input type="submit" value="Proceed" />
			<a href="<?php echo $_SERVER['PHP_SELF'] . '?action=cancel&r=' . rand (0, 10000); ?>">Cancel Import</a>
			</form>
</div>
</div>
			<?php
	} else {
			// errors reported, so dump them to screen and reset step counter
			$step = 0;
?>
			<div class="errors alert warning">
			The import of users from your CSV file has been aborted due to the following issue(s):
			<ul>
			<?php foreach ($errors as $error) { ?>
			<li><?php echo $error; ?></li>
			<?php } ?>
			</ul>
			Please correct these issues in the CSV file, save and <a href='<?php echo $_SERVER['PHP_SELF']; ?>'>re-upload the file</a>.
			</div>
			<div style="clear:both;"></div>
			<?php 
			// delete file
			uunlink ($uploadedfile);
	
	}
} // end step 1 ?>

<?php if ($step == 2) { 
	
	// regather data from session...
	$users = !empty ($_SESSION['csvdata']) ? $_SESSION['csvdata'] : false;
	
	if (! $users ) {
		die ("No data sent.");
	} 
	$fullyvalidated = true;
	
	foreach ($users as $user) {
		$errors = array ();
		// reset validation flags to false
		$v_fname = $v_sname = $v_username = $v_email = $v_gender = $v_eth = $v_age = $v_dob = false;
		
		$v_fname = validateName ($user['fname'], 'f');
		if (!$v_fname) {
			$errors[] = 'Firstname invalid. Only letters, apostrophes (\') and hyphens (-) allowed.';
		}
		
		$v_sname = validateName ($user['sname'], 's');
		if (!$v_sname) {
			$errors[] = 'Surname invalid. Only letters, apostrophes (\') and hyphens (-) allowed.';
		}
		
		$v_username = validateUsername($user['loginid']);
		if (!$v_username) {
			$errors[] = 'Invalid format for username. Please use only alphabetical, numeric and underscore characters only.
						 Length between 6 and 20 characters.';
		} else {
			// check db for duplicate username...
			$checkdb = DB::executeSelect('users_info', '*', array ('loginid'=>$user['loginid']));
			if ($checkdb) {
				// a user with the loginid already exists! :(
				$errors[] = 'A user with the username "'  . $user['loginid'] . '" already exists.';
				$v_username = false;
			}
		}
		
		// if email is entered, check validity
		if ($user['email']) {
			$v_email = validateEmail($user['email']);
			if (!$v_email) {
				$errors[] = 'Email address "' . $user['email'] . '" is not a valid format.';
			}
		} else {
			// allow empty email field
			$v_email = true;
		}
		
		$user['gender'] = strtolower($user['gender']);
		if (!empty ($user['gender']) && ( $user['gender'] == 'm' || $user['gender'] == 'f')) {
			$v_gender = true;	
		} else {
			$errors [] = 'Gender not set to "m" (male) or "f" (female).';
		}
				
		if (!empty ($user['ethnicityid']) && ($user['ethnicityid'] >= 1 && $user['ethnicityid'] <= 16)) {
			$v_eth = true;
		} else {
			$errors [] = 'Ethnicity id of ' . $user['ethnicityid'] . ' is invalid.';
		}
		
		if (!empty ($user['ageid']) && preg_match ("/^[1-3]$/", $user['ageid'])) {
			$v_age = true;
		} else {
			$errors [] = 'Age id of ' . $user['ageid'] . ' is invalid.';
		}
		
		//dob validation
		if (!empty ($user['dob']) && validateDOB ($user['dob'])) {
			$v_dob = !empty ($user['dob']);
		} else {
			$errors [] = 'Date of birth ' . $user['dob'] . ' is not a correct format (please use dd/mm/yyyy).';
		}
		
		// allow 0 or blank for centre.
		if (empty ($user['centreid']) || $user['centreid'] == 0) {
			$v_centre = true;
		// check centreid is not empty and belongs to provider of logged in user...!
		} else if (!empty ($user['centreid']) && has_access_to_centres (Sessions::getID(), $user['centreid'], false)) {
			$v_centre = true;
		} else {
			$errors [] = 'The centre with the identifier ' . $user['centreid'] . ' is not one of your centres.';
			$v_centre = false;
		}
		
		if ($v_fname && $v_sname && $v_username && $v_email && $v_gender && $v_eth && $v_age && $v_dob ) {
			// all good
		} else {
			$fullyvalidated = false;
			// one or more fields did not validate, so return error
			//$errors[] = 'User "' . $user['loginid'] . '" could not be imported as one or more fields did not validate.';
			//uunlink ($newname);
		?>
			<div class="errors alert warning">
			The user "<?php echo $user['loginid']; ?>" could not be imported because:			
			<ul>
			<?php foreach ($errors as $error) { ?>
			<li><?php echo $error; ?></li>
			<?php } ?>
			</ul>
			</div>
			<div style="clear:both;"></div>
		<?php 
		uunlink ($_SESSION['uploadedfile']);
		}
		
	}
	if ($fullyvalidated) {
		foreach ($users as $user) {
			// all validations done, now import the user to the db
				// creating a new user
				$q = DB::executeInsert('users_info',
					array (
						'providerid'	=> $provider,
						'loginid' 		=> strtolower ($user['loginid']),
						'fname' 		=> ucname ($user['fname']),
						'sname' 		=> ucname ($user['sname']),
						'roleid' 		=> $CFG->learnerroleid,
						'password' 		=> md5($CFG->defaultpassword),
						'email'			=> $user['email'],
						'ethnicityid'	=> $user['ethnicityid'],
						'gender'		=> $user['gender'],
						'ageid'   		=> $user['ageid'],
						'groupid'		=> 1,
						'programmeid'	=> 1,
						'centreid'		=> $user['centreid'],
						'dob'			=> ukdate2mysql( $user['dob']),
						//'startdate'		=> date("Y-m-d", mktime(0, 0, 0, $_POST['start_m'], 1, $_POST['start_y'])),
						//'enddate'		=> date("Y-m-d", mktime(0, 0, 0, $_POST['end_m'], 1, $_POST['end_y'])),
						'completed'		=> 0
				));
				if ($q) {
					echo 'New user "' . $user['loginid'] . '" added.<br/>';
				} else {
					
					echo 'User ' . $user['loginid'] . ' not added.<br/>';
				}
		} // foreach
		// finish csv import, clear session variable
		$_SESSION['csvdata'] = false;
 		unset($_SESSION['csvdata']);
 		uunlink ($_SESSION['uploadedfile']);
 		echo '<h3>Import complete.</h3>';
 		//
	} else { ?>
		<p>Unable to add the users to TRaCIO for the above reasons.</p>
		<p>Please correct these issues in the CSV file, save and <a href='<?php echo $_SERVER['PHP_SELF']; ?>'>re-upload the file</a>.</p>
<?php }
	
	//if ()
?>
<?php } // end step 2 ?>


</body>
</html>
