<?php

require_once ($CFG->apploc . '/classes/db.php');

/**
 * Check if user has actually sat this assessment already.
 *
 * example usage: checkUserSitting (20, 1, 'l'));
 *
 * @version 1.0
 * 
 * @param int $userid
 * @param int $sitting number of sitting (1,2 or 3)
 * @param string $assesstype whether learner ('l') or advisor ('a')
 * @return boolean
 */
function userAlreadySat ($userid, $sitting, $assesstype) {
	if (DB::executeSelect('users_attempt', '*', array ('userid'=>$userid, 'sitting'=>$sitting, 'assessmenttype'=>$assesstype)) != false) {
		return true;
	}
	return false;
}

/**
 * 
 * Return all information about a users activity/sitting
 * 
 * @param int $userid
 * @param int $sitting number of sitting (1,2 or 3)
 * @param string $assesstype whether learner ('l') or advisor ('a')
 * @return mixed
 */
function getAttemptResults ($userid, $sitting, $assesstype) {
	$results = DB::runSelectQuery ('SELECT sb_users_attempt.userid, 
	sb_users_attempt.date, 
	sb_users_attempt.sitting, 
	sb_users_attempt.assessmenttype, 
	sb_users_attempt_answers.q1, 
	sb_users_attempt_answers.q2, 
	sb_users_attempt_answers.q3, 
	sb_users_attempt_answers.q4, 
	sb_users_attempt_answers.q5, 
	sb_users_attempt_answers.q6, 
	sb_users_attempt_answers.q7,
	sb_users_attempt_answers.q8,
	sb_users_attempt_answers_attendances.chk1, 
	sb_users_attempt_answers_attendances.chk2, 
	sb_users_attempt_answers_attendances.chk4, 
	sb_users_attempt_answers_attendances.chk3, 
	sb_users_attempt_answers_attendances.chk5, 
	sb_users_attempt_answers_attendances.chk6, 
	sb_users_attempt_answers_attendances.chk7, 
	sb_users_attempt_answers_attendances.other, 
	sb_users_attempt.AttemptID, 
	sb_users_attempt_answers_attendances.AttendancesID, 
	sb_users_attempt_answers.answersID
FROM sb_users_attempt LEFT OUTER JOIN sb_users_attempt_answers ON sb_users_attempt.AttemptID = sb_users_attempt_answers.attemptid
	 LEFT OUTER JOIN sb_users_attempt_answers_attendances ON sb_users_attempt_answers_attendances.answersid = sb_users_attempt_answers.answersID
WHERE sb_users_attempt.userid=' . $userid . ' and sb_users_attempt.sitting=' . $sitting . ' and sb_users_attempt.assessmenttype="' . $assesstype . '"');
	return $results;
}
/**
 * Return all information about a users activity/sitting
 * 
 * @param int $attemptid The AttemptID of the sitting of which to return info from
 * @return mixed
 */
function getAttemptResultsByID ($attemptid) {
	$results = DB::runSelectQuery ('SELECT sb_users_attempt.userid, 
	sb_users_attempt.date, 
	sb_users_attempt.sitting, 
	sb_users_attempt.assessmenttype, 
	sb_users_attempt_answers.q1, 
	sb_users_attempt_answers.q2, 
	sb_users_attempt_answers.q3, 
	sb_users_attempt_answers.q4, 
	sb_users_attempt_answers.q5, 
	sb_users_attempt_answers.q6, 
	sb_users_attempt_answers.q7,
	sb_users_attempt_answers.q8,
	sb_users_attempt_answers_attendances.chk1, 
	sb_users_attempt_answers_attendances.chk2, 
	sb_users_attempt_answers_attendances.chk4, 
	sb_users_attempt_answers_attendances.chk3, 
	sb_users_attempt_answers_attendances.chk5, 
	sb_users_attempt_answers_attendances.chk6, 
	sb_users_attempt_answers_attendances.chk7, 
	sb_users_attempt_answers_attendances.other, 
	sb_users_attempt.AttemptID, 
	sb_users_attempt_answers_attendances.AttendancesID, 
	sb_users_attempt_answers.answersID
FROM sb_users_attempt LEFT OUTER JOIN sb_users_attempt_answers ON sb_users_attempt.AttemptID = sb_users_attempt_answers.attemptid
	 LEFT OUTER JOIN sb_users_attempt_answers_attendances ON sb_users_attempt_answers_attendances.answersid = sb_users_attempt_answers.answersID
WHERE sb_users_attempt.attemptid=' . $attemptid);
	return $results;
} 

/**
 * Return interventions for the given user. If no $sitting number is specified, all are assumed.
 * 
 * @param unknown $userid
 * @param string $sitting (optional) specific sitting number (1, 2 or 3) of which to return interventions
 * @return array
 */
function getInterventionsByUserID ($userid, $sitting='') {
	// update 2012-24-01 added 'other' field to the query for the dashboard screen
	$query = 'SELECT sb_intervention_types.name, sb_user_interventions.other';
	
	if (!$sitting) {
		$query .= ', sb_user_interventions.sitting';
	}
	$query .= '	FROM sb_user_interventions INNER JOIN sb_intervention_types ON sb_user_interventions.typeid = sb_intervention_types.TypeID
			WHERE userid=' . $userid;
	
	if ($sitting) {
		$query .= ' AND sb_user_interventions.sitting =' . $sitting;
	}
	
	$query .= ' order by sitting, name';
	$interventions = DB::runSelectQuery($query, true);
	
	return $interventions;
}

/**
 * Return interventions for the specific attempt id.
 * 
 * @param int $attemptid
 * @return array
 */
function getInterventionsByAttempt ($attemptid) {
	$interventions = DB::runSelectQuery('SELECT sb_intervention_types.name
		FROM sb_user_interventions INNER JOIN sb_intervention_types ON sb_user_interventions.typeid = sb_intervention_types.TypeID
		WHERE attemptid=' . $attemptid, true);
	return $interventions;
}

/**
 * Get dates of all activities undertaken by learners and advisors.
 * 
 * @param int $learnerid userid of learner
 * @return array
 */
function getActivityHistoryOverview ($learnerid) {
	$history = DB::runSelectQuery ("SELECT sb_users_attempt.sitting, 
		sb_users_attempt.date, 
		sb_users_attempt.assessmenttype, 
		sb_users_info.fname, 
		sb_users_info.sname
		FROM sb_users_attempt LEFT OUTER JOIN sb_users_info ON sb_users_attempt.delegate = sb_users_info.UserID
		WHERE sb_users_attempt.userid = $learnerid
		ORDER BY sb_users_attempt.date ASC, sb_users_attempt.assessmenttype ASC", true);
	return $history;
}

/**
 * Get advisor(s) name(s) for the learner results screen, so that learner knows who their contact is.
 * 
 * @param int $learnerid userid of learner
 * @return array
 */
function getAdvisorsForActivities ($learnerid) {
	$advisors = DB::runSelectQuery ("SELECT sb_users_info.fname,
		sb_users_info.sname,
		sb_users_attempt.sitting
		FROM sb_users_attempt LEFT OUTER JOIN sb_users_info ON sb_users_attempt.delegate = sb_users_info.UserID
		WHERE sb_users_attempt.userid = $learnerid and assessmenttype='a';", true);
	return $advisors;
}

/**
 * 
 * @param unknown $assesstype
 * @param unknown $userid
 * @deprecated 
 */
function displayStatsNew ($assesstype, $userid) {
	$results = DB::runSelectQuery ('SELECT sb_users_attempt.userid,
		sb_users_attempt.date, 
		sb_users_attempt.sitting, 
		sb_users_attempt.assessmenttype, 
		sb_users_attempt_answers.q1, 
		sb_users_attempt_answers.q2, 
		sb_users_attempt_answers.q3, 
		sb_users_attempt_answers.q4, 
		sb_users_attempt_answers.q5, 
		sb_users_attempt_answers.q6, 
		sb_users_attempt_answers.q7, 
		sb_users_attempt_answers.q8, 
		sb_users_attempt_answers_attendances.chk1, 
		sb_users_attempt_answers_attendances.chk2, 
		sb_users_attempt_answers_attendances.chk4, 
		sb_users_attempt_answers_attendances.chk3, 
		sb_users_attempt_answers_attendances.chk5, 
		sb_users_attempt_answers_attendances.chk6, 
		sb_users_attempt_answers_attendances.chk7, 
		sb_users_attempt_answers_attendances.other, 
		sb_users_attempt.AttemptID, 
		sb_users_attempt_answers_attendances.AttendancesID, 
		sb_users_attempt_answers.answersID
		FROM sb_users_attempt LEFT OUTER JOIN sb_users_attempt_answers ON sb_users_attempt.AttemptID = sb_users_attempt_answers.attemptid
		LEFT OUTER JOIN sb_users_attempt_answers_attendances ON sb_users_attempt_answers_attendances.answersid = sb_users_attempt_answers.answersID
		WHERE userid = ' . $userid . ' and assessmenttype = "' . $assesstype . '" 
		order by sitting;');
	
	if ($results) {
		// if there is only one result, don't display a graph!
		if (count ($results[0]) > 1) {
			$fieldscount = count($results[0]);
			// manipulate data to format for graphs
			$dataseries = array ();
			$interventions = array ();
	
			// get count of rows
			$rowcount = count ($results[0]);
			$questions = array ("q1", "q2", "q4", "q5", "q6", "q7", "q8");
			$series = array ();
			$series["q1"] = array ();
			$series["q8"] = array ();
			$series["q7"] = array ();
			$series["q2"] = array ();
			$series["q4"] = array ();
			$series["q5"] = array ();
			$series["q6"] = array ();
			foreach ($results as $rows) {
				array_push ($series["q1"], $rows['q1']);
				array_push ($series["q2"], $rows['q2']);
				array_push ($series["q4"], $rows['q4']);
				array_push ($series["q5"], $rows['q5']);
				array_push ($series["q6"], $rows['q6']);
				array_push ($series["q7"], $rows['q7']);
				array_push ($series["q8"], $rows['q8']);
	
				$seriesinterventions = array ();
				
				$iv = getInterventionsByUserID ($userid, $rows['sitting']);
								
				if (!empty ($iv)) {
					foreach ($iv as $intervention) {
						array_push ($seriesinterventions, $intervention['name']);
					}
				} else {
					array_push ($seriesinterventions, false);
				}
				array_push ($interventions, $seriesinterventions);
	
			}
	
			$linegraph = new Graph ('b');
			$linegraph->seriesnames = array ();
			
			$series['labels'] = array ();
			
			$counter = 0;
			foreach ($interventions as $intervention) {
				// are there any interventions?
				if ($intervention[0]) {
					array_push ($series['labels'], $linegraph->origseriesnames[$counter++] . ' (' . implode (', ', $intervention) . ')');
				} else {
					array_push ($series['labels'], $linegraph->origseriesnames[$counter++]);
				}
			}
			$dataseries = $series;
			// display graph
			$linegraph->title = $linegraph->longstrings[$assesstype] . ' Activity Results';
			$linegraph->displayGraph ($dataseries);
	
		}
	}
}

/**
 * 
 * @param unknown $assesstype
 * @param unknown $userid
 * @deprecated
 */
function displayStats ($assesstype, $userid) {
	$results = (DB::runSelectQuery ('SELECT sb_users_attempt.userid,
	sb_users_attempt.date, 
	sb_users_attempt.sitting, 
	sb_users_attempt.assessmenttype, 
	sb_users_attempt_answers.q1, 
	sb_users_attempt_answers.q2, 
	sb_users_attempt_answers.q3, 
	sb_users_attempt_answers.q4, 
	sb_users_attempt_answers.q5, 
	sb_users_attempt_answers.q6, 
	sb_users_attempt_answers.q7, 
	sb_users_attempt_answers.q8, 
	sb_users_attempt_answers_attendances.chk1, 
	sb_users_attempt_answers_attendances.chk2, 
	sb_users_attempt_answers_attendances.chk4, 
	sb_users_attempt_answers_attendances.chk3, 
	sb_users_attempt_answers_attendances.chk5, 
	sb_users_attempt_answers_attendances.chk6, 
	sb_users_attempt_answers_attendances.chk7, 
	sb_users_attempt_answers_attendances.other, 
	sb_users_attempt.AttemptID, 
	sb_users_attempt_answers_attendances.AttendancesID, 
	sb_users_attempt_answers.answersID
FROM sb_users_attempt LEFT OUTER JOIN sb_users_attempt_answers ON sb_users_attempt.AttemptID = sb_users_attempt_answers.attemptid
	 LEFT OUTER JOIN sb_users_attempt_answers_attendances ON sb_users_attempt_answers_attendances.answersid = sb_users_attempt_answers.answersID
WHERE userid = ' . $userid . ' and assessmenttype = "' . $assesstype . '"'));

	if ($results) {
		$fieldscount = count($results[0]);
		?>
User ID:
		<?php echo $results[0]['userid'];?>
<br />
Activity Type:
		<?php echo $results[0]['assessmenttype'];?>
<br />
		<?php
		// manipulate data to format for graphs
		$dataseries = array ();
		// create array to store intervention (for labels)
		$interventions = array ();
		foreach ($results as $rows) {
			$series = array ($rows['q1'], $rows['q2'], $rows['q4'], $rows['q5'], $rows['q6'], $rows['q7'], $rows['q8']);
			array_push ($dataseries, $series);

			// push interventions for this sitting
			$seriesinterventions = array ();
			$iv = getInterventionsByAttempt ($rows['AttemptID']);
			if (!empty ($iv)) {
				foreach ($iv as $intervention) {
					array_push ($seriesinterventions, $intervention['name']);
				}
			} else {
				array_push ($seriesinterventions, false);
			}
			array_push ($interventions, $seriesinterventions);
		}

		// instantiate graph
		$linegraph = new Graph ();
		$linegraph->title = 'Activity Results';
		$linegraph->seriesnames = array ();
		// add labels for each series.
		$counter = 0;
		foreach ($interventions as $intervention) {
			// are there any interventions?
			if ($intervention[0]) {
				array_push ($linegraph->seriesnames, $linegraph->origseriesnames[$counter++] . ' (' . implode (', ', $intervention) . ')');
			} else {
				array_push ($linegraph->seriesnames, $linegraph->origseriesnames[$counter++]);
			}
		}
		// display graph
		$linegraph->displayGraph ($dataseries);

	} else {
		// no data returned or error
		if (mysql_error () != "") {
			die;
		} else {
			echo "No results were returned.";
		}
	}
}

/**
 * Return users full name.
 * 
 * @param int $userid
 * @return string
 */
function getUserName ($userid) {
	$res = DB::executeSelect('users_info', array ('fname', 'sname'), array ('userid'=>$userid));
	return $res['fname'] . ' ' . $res['sname'];
}

/**
 * @param int $userid
 * @return mixed
 * @deprecated replaced by Sessions::getUserInfo('roleid').
 */
function getUserRole ($userid) {
	$res = DB::executeSelect('users_info', array ('roleid'), array ('userid'=>$userid));
	return $res['roleid'];
}

/**
 * Get user information from the users_info table.
 * 
 * @param int $userid
 * @param string $field
 * @return mixed
 */
function getUserInfo ($userid, $field='*') {
	if ($field == '*') {
		$res = DB::executeSelect('users_info', '*', array ('UserID'=>$userid));
		return $res;
	} else if (is_array ($field)) {
		$res = DB::executeSelect('users_info', $field, array ('UserID'=>$userid));
		return ($res);
	} else {
		$res = DB::executeSelect('users_info', array ($field), array ('UserID'=>$userid));
		return ($res[$field]);
	}
	
}

/**
 * Following a learner activity being completed, send an email to the learner's advisor
 * informing them so they can login to tracio to complete their advisor activity.
 * 
 * @param string $formtype activity type. whether learner ('l') or advisor ('a')
 * @param int $userid
 * @param int $sitting number of sitting (1, 2 or 3)
 */
function sendActivityCompleteNotifications ($formtype, $userid, $sitting) {
	global $CFG;
	
	$headers = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	$headers .= 'From: ' . $CFG->emailSender . "\r\n";
		
	// function to do emailing following completion of an activity by either advisors or learners.
	if ($formtype=='l') { // learner
		// get advisors for this learner
		$advisors = getLastAdvisorsForLearner ($userid);
	
		$advisorsemails = array ();
		//if user has an advisor!
	
		foreach ($advisors as $advisor) {
			if ($advisor['email'] && $advisor['enableemails']) {
				$advisorsemails[] = $advisor['email'];
			}
		}
		if ($advisorsemails) {
			$advisorsemails = implode (',', $advisorsemails);
			
			// get user info for email body
			$userinfo = getUserInfo ($userid, array ('fname', 'sname', 'loginid'));
			$ebody = $userinfo['fname'] . ' ' . $userinfo['sname'] . ' (' . $userinfo['loginid'] . ') has completed sitting ' . $sitting . ' of TRaCIO.';
			$ebody .= "To access TRaCIO <a href=\"$CFG->fullhttp\">click here</a>.";
			
			// if emails should be sent out...
			if ($CFG->emailsEnabled) {
				$emailsuccess = mail ($advisorsemails, 'TRaCIO Notification', $ebody, $headers);
				if ($emailsuccess) {
					echo "Email notifications sent.";
				}
			} else {
				// emails are switched off (usually during development or testing), so output to browser to show the output!
				echo "<hr/>EMAIL OUTPUT:<br/>" . $ebody;
				echo "<br/>SENT TO: " . $advisorsemails;
			}
		}
		
	} 
}

/*
 * Find out which advisor last sat an advisor activity for this learner.
 * 
 * Function steps thru a few processes to locate an advisor for this learner until it finds one.
 * 
 * #1: Find the advisor who last interacted with this learner (via an advisor activity)
 * #2: Find an advisor who has selected this learner (ie learner is assigned to the advisor)
 * #3: Return all advisors for the provider and they will get a notification.
 * #4: In the unlikely event that no advisors exist, silence.
 * 
 * @used-by sendActivityCompleteNotifications () in lib/activity.php
 * 
 * @param $userid int
 * @version 1.1 added 'enableemails' to queries
 */
function getLastAdvisorsForLearner ($userid) {
	// check if there are any advisor sittings for this learner in the first instance
	// get email of advisor who sat last activity for this learner
	$query1 = 	'SELECT sb_users_info.email, sb_users_info.enableemails
				FROM sb_users_attempt INNER JOIN sb_users_info ON sb_users_attempt.delegate = sb_users_info.UserID
				WHERE sb_users_attempt.userid = ' . $userid . ' and sb_users_attempt.assessmenttype = "a"				
				order by sitting desc 
				limit 1;';
	
	$res = DB::runSelectQuery ($query1, true);
	
	if (!empty ($res)) {
		return $res;
	} 
	
	// previous query failed, so get all advisors who have selected this user.
	$query2 = 	'SELECT sb_users_learner_assignment.AssignmentID, 
				sb_users_learner_assignment.advisorid, 
				sb_users_learner_assignment.learnerid, 
				sb_users_learner_assignment.enabled, 
				sb_users_info.fname, 
				sb_users_info.sname, 
				sb_users_info.loginid,
				sb_users_info.email,
				sb_users_info.enableemails
				FROM sb_users_learner_assignment INNER JOIN sb_users_info ON sb_users_learner_assignment.advisorid = sb_users_info.UserID
				WHERE sb_users_learner_assignment.learnerid = ' . $userid . ' and enabled=1;';
	
	$res = DB::runSelectQuery ($query2, true);
	
	if (!empty ($res)) {
		return $res;
	}
	
	// get all advisors for the learners institution - they'll all have an email!
	$myprovider = Sessions::getUserInfo('providerid');
	$query3 = 'SELECT email, enableemails FROM sb_users_info WHERE providerid=' . $myprovider . ' and roleid=' .  $CFG->advisorroleid . ';';
	$res = DB::runSelectQuery ($query3, true);
	
	if (!empty ($res)) {
		return $res;
	}
	
	// if there are no advisors for this learner then a falling tree makes no sound.
	return false;
}

/**
 * 
 * @param unknown $orig_answer
 * @param unknown $new_answer
 * @return boolean
 * 
 * @deprecated
 */
function compareActivityQuestion ($orig_answer, $new_answer) {
	if ($orig_answer != $new_answer) {
		return false;
	}
	return true;
}

/**
 * Compare the resat activity with the original, write any changes to the database and return changes.
 * 
 * @param int $orig_attempt_id AttemptID of original sitting/activity
 * @param array $new_attempt_data Answers from the new sitting, e.g. array ('q1'=>1, 'q2'=>2, 'q4'=>3, 'q5'=>4, 'q6'=>5, 'q7'=>3, 'q8'=>2)
 * @return array 
 */
function compareActivitySittings ($orig_attempt_id, $new_attempt_data) {
	// call original data from db
	$user_response = array ();
	$orig_attempt_data = getAttemptResultsByID ($orig_attempt_id);
	//$orig_attempt_data = array ('q1'=>1, 'q2'=>3, 'q4'=>3, 'q5'=>5, 'q6'=>5, 'q7'=>3, 'q8'=>2);
	foreach ($new_attempt_data as $key=>$value) {
		if ($new_attempt_data[$key] != $orig_attempt_data[$key]) {
			DB::executeInsert ('activity_revisions', 
				array (
					'userid'=>$_POST['userid'],
					'delegate'=>Sessions::getID(),
					'assessmenttype'=>$_POST['formtype'],
					'attemptid'=>$orig_attempt_id,
					'question'=>$key,
					'orig_answer'=>$orig_attempt_data[$key],
					'new_answer'=>$new_attempt_data[$key],
					'additional'=>''
				));
				$qnum = str_replace ('q', '', $key);
				$user_response[] =  'You changed a value for the question:<br/><strong>' . return_string_upper ($_POST['formtype'] . $qnum . '_THEME') . '</strong> from <br/><em>' . return_string_upper ($_POST['formtype'] . $qnum . '_' . $orig_attempt_data[$key]) . '</em> to <em>' . return_string_upper ($_POST['formtype'] . $qnum . '_' . $new_attempt_data[$key]) . '</em><br/>';
		}
		
	}	
	return $user_response;
}

/**
 * Return number of revisions for attemptid. Used on dashboard/user results screen.
 * 
 * @used-by dashboard.php
 * @param int $attemptid
 * @return int
 */
function checkForRevisionsByAttemptID ($attemptid) {
	$num_of_revisions = DB::runSelectQuery ('select count(*) as num_revisions from sb_activity_revisions where attemptid=' . $attemptid . ';');
	return $num_of_revisions['num_revisions'];
}

?>
