<?php

include_once ('./config.php');
include_once ($CFG->apploc  . '/lib/activity.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/lib/stats.php');
include_once ($CFG->apploc  . '/lib/funcs.php');
Sessions::checkUserLogIn ();

//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('general', 'question'));

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
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<?php



$userid = $_POST['userid'];
$sitting = $_POST['sitting'];

$editing = !empty ($_POST['edit']) ? $_POST['edit'] : false;


if (!$editing && userAlreadySat ($userid, $sitting, $_POST ['formtype'])) {
	// has user sat this already? if so, stop page. this is assuming we are not editing an activity obviously.
	echo_string ('ACTIVITY_ALREADY_COMPLETE');
	die;
}

// are they editing this activity - is it an advisor doing it?
if ($editing) {
	$attemptid = getAttemptId($userid, $sitting, $_POST['formtype']);

	// update sb_users_attempt and change delegate
	
	// update all relevant fields!
	$answers = array ();

	$answers ['q1'] = $_POST ['q1'];
	$answers ['q2'] = $_POST ['q2'];
	$answers ['q3'] = '0'; // leave blank - not quantifiable
	$answers ['q4'] = $_POST ['q4'];
	$answers ['q5'] = $_POST ['q5'];
	$answers ['q6'] = $_POST ['q6'];
	$answers ['q7'] = $_POST ['q7'];
	$answers ['q8'] = $_POST ['q8'];
	
	// compare both sittings to see and notify for differences between last sitting and this resit
	$changes = compareActivitySittings ($attemptid, $answers);
	
	if (!empty ($changes )) {
		// has the advisor changed any answers?
		foreach ($changes as $change) {
		?>
			<div class="alert sallgood"><?php echo $change; ?></div>
		<?php 	
		}
		// TO DO - do we need to write here?
		$res = DB::executeUpdate('users_attempt_answers', $answers, array ('attemptid' => $attemptid), 1);
	} else {
		?>
		<div class="alert warning">You did not change any responses.</div>
		<?php 
	}	

} 

// edit or save code...
 if (true) {
	

	/* update 2013-01-24 major changes to this php file, rerouted code to 
	*  run here with checks for editing or new record.
	*
	* important to note that in old tracio db structure (sb_users_attempt_answers_attendances), there was no
	* attemptid field to tie to other tables, so the answersid field was used. below is a fix to check for a missing
	* attemptid in sb_users_attempt_answers_attendances table, if it is missing, a query is used to retrieve the answersid
	* from a different table. this is just there to deal with legacy users of the system.
	*/
	
	//save user data
	if ($editing) {
		$res = DB::executeUpdate('users_attempt', array ('delegate'=>Sessions::getID ()), array ('AttemptID'=>$attemptid), 1);
	} else {
		$res = DB::executeInsert('users_attempt', array ('userid'=>$userid, 'sitting'=>$sitting, 'assessmenttype'=>$_POST ['formtype'], 'delegate'=>Sessions::getID ()));
	}
	
	// RES for new save gets id from database row
	// for update, need to use attemptid.
	/* variables clarification for below:
	 * 		$res used for new records as it stores the new attemptid
	 * 		$attemptid used for old
	 * 	TODO: neutralise above to be one variable!
	 */
	
	if ($res) {
		?>
		<div style="margin-left: auto; margin-right: auto; text-align:center;"><img style="vertical-align: middle" src="<?php echo ($CFG->apphttp); ?>/images/activity.png" alt="Activity" />
		<?php // who has sat the activity? 
		if ($_POST['formtype'] == 'l' ) {
				?>
						<br/>
				<?php //echo_string ('ACTIVITY_COMPLETE'); ?>
				<a href="home.php"><img src="<?php echo ($CFG->apphttp); ?>/images/home.png" alt="Home"/></a>
				<a href="dashboard.php"><img src="<?php echo ($CFG->apphttp); ?>/images/results.png" alt="Results"/></a>
				<a href="login.php?logout=true"><img src="<?php echo ($CFG->apphttp); ?>/images/logout.png" alt="Logout"/></a>
				<?php } else { // advisor type ?>
				<br/>
				<a href="home.php"><img src="<?php echo ($CFG->apphttp); ?>/images/home.png" alt="Home"/></a>
		<?php } ?>
		</div>
		<?php
		// save the individual answers
		$answers = array ();
		if (!$editing) {
			$answers ['attemptid'] = $res;
		}
		//// 
		$answers ['q1'] = $_POST ['q1'];
		$answers ['q2'] = $_POST ['q2'];
		$answers ['q3'] = '0'; // leave blank - not quantifiable
		$answers ['q4'] = $_POST ['q4'];
		$answers ['q5'] = $_POST ['q5'];
		$answers ['q6'] = $_POST ['q6'];
		$answers ['q7'] = $_POST ['q7'];
		$answers ['q8'] = $_POST ['q8'];
		if ($editing) {
			$res2 = DB::executeUpdate ('users_attempt_answers', $answers, array ('attemptid'=>$attemptid), 1);
		} else {
			$res2 = DB::executeInsert('users_attempt_answers', $answers);
		}
		
		if (!$res2) {
			echo mysql_error();
		}
		
		if ($res2) {
			echo 'Answers saved.<br/>';
			// save attendances
			$attendances = array ();
			if (!$editing) {
				$attendances ['answersid'] = $res2;
				$attendances ['attemptid'] = $res;
			}
			// added phase2:
			//$attendances ['attemptid'] = $res;
			//
			$attendances ['chk1'] = $_POST['chk1'];
			$attendances ['chk2'] = $_POST['chk2'];
			$attendances ['chk3'] = $_POST['chk3'];
			$attendances ['chk4'] = $_POST['chk4'];
			$attendances ['chk5'] = $_POST['chk5'];
			$attendances ['chk6'] = $_POST['chk6'];
			$attendances ['chk7'] = $_POST['chk7'];
			$attendances ['other'] = $_POST['q3_8'];
			if ($editing) {	
				// LEGACY CODE
				// check table to see if it has an attemptid in it, old user results used 'answersid' to tie up,
				// so check if this is a new or old user (ie does the attemptid exist).
				$hasattemptid = DB::executeSelect('users_attempt_answers_attendances', 'attemptid', array ('attemptid'=>$attemptid));
				if (!$hasattemptid) {
					// old tracio user with old db structure :(
					// need to retrieve answersid for WHERE update
					$old_answers_id = DB::executeSelect('users_attempt_answers', 'answersid', array ('attemptid'=>$attemptid));
					$res3 = DB::executeUpdate('users_attempt_answers_attendances', $attendances, array('answersid'=>$old_answers_id['answersid']), 1);
				} else {
					// it's a newer user, so we can carry on and use the attemptid
					$res3 = DB::executeUpdate('users_attempt_answers_attendances', $attendances, array('attemptid'=>$attemptid), 1);
				}
			
			} else {
				$res3 = DB::executeInsert('users_attempt_answers_attendances', $attendances);
			}
			
			if (!$res3) {
				echo mysql_error();
			}
			
			if ($res3) {
				// insert interventions - insert record for each intervention
				if ($editing) {
					// remove all previous interventions for this user
					$del = DB::executeDelete ('user_interventions', array ('userid'=>$userid, 'sitting'=>$sitting), 5);
				}
				if (!empty ($_POST['interventions'])) {
					// write interventions to database
					foreach ($_POST['interventions'] as $intervention) {
						$iv_array = array (
								'userid'	=>	$userid,
								'typeid'	=>	$intervention,
								'sitting'	=>	$sitting
						);
						if ($intervention == 100) { // other
							$iv_array['other'] = $_POST['otheriv_text'];
						}						
						$res4 = DB::executeInsert('user_interventions', $iv_array);
							
					if ($res4) echo "Intervention(s) saved.";
					}
				}
				// if save of answers was successful, inform whoever of completion
				if ($res3 && !$editing) {
					$emailsucc = sendActivityCompleteNotifications ($_POST['formtype'], $userid, $sitting);
					
				}
			} else {
				$errorid = Log::error ('users_attempt_answers_attendances: ' . mysql_error());
				echo 'There was an error saving the attempt. Log #' . $errorid;
				die;
			}
		} else {
			$errorid = Log::error ('users_attempt_answers: ' . mysql_error());
			echo "There was an error saving the attempt. Log #" . $errorid;
			die;
		}
	} else {
		$errorid = Log::error ('users_attempt: ' . mysql_error());
		echo 'There was an error saving the attempt. Log #' . $errorid;
		die;
	}

}



?>

</body>
</html>
