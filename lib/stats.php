<?php

include_once ($CFG->apploc . '/classes/db.php');

/**
 * Get all sitting scores/answers for an individual question ONLY.
 * 
 * @param int $userid
 * @param int $disciplinenum - the question required from 1-8 (with the exception of 3 which is not quantifiable)
 * @param string $assessmenttype 'l' (learner) or 'a' (advisor/learner)
 * @return array
 */
function getScoresForDiscipline ($userid, $disciplinenum, $assessmenttype) {
	$query = 'SELECT sb_users_attempt_answers.q' . $disciplinenum . ' as stat,
			  sb_users_attempt.assessmenttype,
		      sb_users_attempt.sitting
			  FROM sb_users_attempt INNER JOIN sb_users_attempt_answers ON sb_users_attempt.AttemptID = sb_users_attempt_answers.attemptid
			  WHERE sb_users_attempt.userid = ' . $userid . ' and assessmenttype="' . $assessmenttype . '"
			  order by sitting, assessmenttype';
	$res = DB::runSelectQuery($query, true);
	return $res;
}

/**
 * Return answers and combined scores for a sitting or sittings. These figures are used 
 * 
 * @param int $userid
 * @param int|string $sitting sitting number or 'all'
 * @param string $sittingtype 'l' (learner) or 'a' (advisor/learner)
 * @return array
 * 
 * @usedby dashboard.php
 */
function getSittingScores ($userid, $sitting, $sittingtype) {
	// if all 
	if ($sitting == 'all') {
		$res = DB::runSelectQuery ('SELECT q1, 	q2, 	q4, 	q5, 	q6, 	q7, q8, 	sum(q1) + sum(q2) + sum(q4) + sum(q5) + sum(q6) + sum(q7) + sum(q8) as total, sitting
									FROM sb_users_attempt INNER JOIN sb_users_attempt_answers ON sb_users_attempt.AttemptID = sb_users_attempt_answers.attemptid
									WHERE userid=' . $userid . ' and assessmenttype = "' . $sittingtype . '"
									group by sitting
									order by assessmenttype, sitting asc;', true);
		
	
		
	} else {
		$attid = getAttemptId ($userid, $sitting, $sittingtype);
		$res = DB::runSelectQuery ('select q1, q2, q4, q5, q6, q7, q8,
									SUM(q1) + SUM(q2) + SUM(q4) + SUM(q5) + SUM(q6) + SUM(q7) + SUM(q8) as total, attemptid
									from sb_users_attempt_answers
									where attemptID=' . $attid . '
									group by attemptID;', true);
	}
	return $res;
}

/**
 * Return the attempt id given the userid, the sitting number and the activity type (learner or advisor)
 * 
 * @param int $userid
 * @param int $sitting
 * @param string $sittingtype 'l' (learner) or 'a' (advisor/learner)
 * @return int
 */
function getAttemptId ($userid, $sitting, $sittingtype) {
	$res = DB::executeSelect('users_attempt', array('AttemptID'), array ('sitting'=>$sitting, 'userid'=>$userid, 'assessmenttype'=>$sittingtype));
	return $res['AttemptID'];
}

/**
 * Get combined total of all answers for a sitting.
 * 
 * @param int $userid
 * @param string $sitting 'l' (learner) or 'a' (advisor/learner)
 * @return int
 * @deprecated previously used by getDistanceTravelled() below, which is also deprecated
 */
function getSittingTotal ($userid, $sitting, $sittingtype) {
	$attid = getAttemptId ($userid, $sitting, $sittingtype);
	$res = DB::runSelectQuery ('select SUM(q1) + SUM(q2) + SUM(q4) + SUM(q5) + SUM(q6) + SUM(q7) + sum(q8) as score from sb_users_attempt_answers where attemptID=' . $attid . ';');
	return $res['score'];
}

/**
 * Get amount of distance travelled for a user given the learner/advisor or learner assessment type.
 *
 * @param int $userid
 * @param string $sittingtype ('l' or 'a')
 * @param string $returntype 'basic' (default) or 'full'
 * @return mixed - distance travelled as percentage (int) or array of full info (array). see $returntype for more.
 * @deprecated
 */
function getDistanceTravelled ($userid, $sittingtype, $returntype='basic') {
	$firstscore = getSittingTotal ($userid, 1, $sittingtype);
	$midscore = getSittingTotal ($userid, 2, $sittingtype);
	$lastscore = getSittingTotal ($userid, 3, $sittingtype);
	$distancescore = $lastscore - $firstscore;
	$totalscore = 30; // total score available for each sitting (5pts * 6 questions)
	try {
		$firstperc = round ((($firstscore / $totalscore) * 100), 2);
		$lastperc = round ((($lastscore / $totalscore) * 100), 2);
		$distanceperc = round ($lastperc - $firstperc, 2);
	} catch (Exception $e) {
		echo "Error. Exception = ", $e->getMessage (), "\n";
	}
	if ($returntype == 'full') {
		$response = array ();
		$response['initialscore'] = $firstscore;
		$response['midscore'] = $midscore;
		$response['finalscore'] = $lastscore;
		$response['initialperc'] = $firstperc;
		$response['finalperc'] = $lastperc;
		$response['distancescore'] = $distancescore;
		$response['distanceperc'] = $distanceperc; 
		$response['totalscore'] = $totalscore;
		// calc is: (last - middle) - (middle - first)
		// e.g. (5-2) - (2-3) aka (+3) - (-1) = +2
		$response['advanceddistance'] = ($lastscore - $midscore) - ($midscore - $firstscore);
	} else {
		$response = $distanceperc;
	}
	return $response;
}

/**
 * Calculate a score for an individual question.
 * 
 * Full calculation (ie if user has sat three activities) is:
 * 
 * 			(last	- middle) - (middle	-  first)
 * e.g.		(	5	-	2	) - (	2	-	3	)
 *     aka  (		+3		) - (		-1		) = +2
 * 
 * @param int $comm score for commencement sitting
 * @param int $mid score for midpoint sitting
 * @param int $comp score for completion sitting
 * @return float
 * @usedby getDistanceTravelledAdvanced()
 */
function calcDistance ($comm, $mid, $comp) {
	// determine how many sittings they have sat and calculate given that
	if ($comp > 0) {
		// full equation (all sittings complete)
		return (float) (($mid - $comm) + ($comp - $mid));
	} else if ($mid > 0) {
		// part equation (comm and mid sat)
		return (float) ($mid - $comm);
	} else {
		// they have only sat one sitting or less... so, return zero!
		return (float) 0;
	}
}

/**
 * Get distance travelled for two sets of results combines (both advisor sitting and learner sittings)
 * for the reports screen.
 * 
 * @param array $data 
 * @return array
 * @usedby reports.php
 */
function getDistanceTravelledAdvancedCollated ($data=array ()) {
	return getDistanceTravelledAdvanced (0, '', $data);
}

/**
 * This returns a dist travelled using a formula.
 * 
 * Calculation involving all sittings per question (as calcDistance () above)
 * 
 * 			(last	- middle) - (middle	-  first)
 * e.g.		(	5	-	2	) - (	2	-	3	)
 *     aka  (		+3		) - (		-1		) = +2
 *     
 * All question scores (NOTE: above example is a single question score) are then added together
 * 
 * @version 2.0
 * @param int $userid
 * @param string $sittingtype ('l' or 'a') learner or advisor
 * @param array $data data to use for calculation - was added for collated reports
 * @return array with text percentage and graphic percentage (for gauge)
 * @uses calcDistance()
 */
function getDistanceTravelledAdvanced ($userid, $sittingtype, $data=array()) {
	/**
	 * number of decimal places to show in final distance travelled score
	 * @var int 
	 */
	$decimalPlaces = 0;
	
	// where is data coming from?
	if (!empty ($data)) {
		// data has been passed in as array
		$res = $data;
	} else {
		// fetch results and data from function
		$res = getSittingScores ($userid, 'all', $sittingtype);
	}
	
	/** 
	 * keeps the total tally of all the individual question scores
	 * @var int
	 */
	$overallscore = 0;
	
	/// with every question, get commencement, mid-point and completion scores of said question
	for ($i=1; $i<=8; $i++) {
		// question 3 is not quantifiable, therefore is ignored
		if ($i != 3) {
			// fetch question scores for the different sittings
			// if no result is found, set to 0.
			$comm = (float) !empty ($res[0]['q' . $i]) ? $res[0]['q' . $i] : 0;
			$mid = (float) !empty ($res[1]['q' . $i]) ? $res[1]['q' . $i] : 0;
			$comp = (float) !empty ($res[2]['q' . $i]) ? $res[2]['q' . $i] : 0;
			// fetch the distance for this individual question and add to total
			$overallscore += calcDistance ($comm, $mid, $comp);
		}
	}
	
	// get data ready for returning
	$ret = array ();
	
	/*
	 * Calculate a score between -100% and 100% for Text Percentage
	 */
	/**
	 * Maximum possible score for all questions totalled.
	 * 
	 * Calculated by +4 max score on each question multiplied by 7 questions
	 * 
	 * (4 * 7) = 28
	 * 
	 * @var int
	 */
	$maxscore = 28;				//	(4 * 7 ) + (4 * 7); //+4 max score on each question, 7 questions
	
	/**
	 * Minimum possible score for all questions totalled.
	 * 
	 * Calculated by -4 min score on each question, multiplied by 7 questions
	 * 
	 * (4 * 7) + (-4 * 7) = 0
	 * 
	 * @var int
	 * @deprecated not being used.
	 */
	$minscore = 0;
		
	// get score between -100% and 100%...
	$ret['textperc'] = round ($overallscore / $maxscore * 100, $decimalPlaces) ;
	
	/*
	 * Now calculate a score between 0% and 100% for distance travelled graph display (gauge)
	 */
	// double maxscore
	$maxscore = 56;
	// add 28 to make it into a positive value between 0 and 100% as opposed to -100% to 100%.
	$overallscore += 28; 
	// get score between 0% and 100%
	$ret['graphperc'] = round ($overallscore / $maxscore * 100, $decimalPlaces);
	return $ret;
}

/**
 * Draw a graph for each question with the different sitting scores (shown on right sidebar of dashboard.php)
 * 
 * @param array $advisordata list of different sitting scores for a question
 * 			(e.g. question 1 answer from sitting 1, sitting 2 and sitting 3)
 * @param array $learnerdata as $advisordata
 * @return string html code
 */
function drawDisciplineGraph ($advisordata, $learnerdata) {
	$exp = '';
	$exp .= '<img src="http://chart.apis.google.com/chart?chxl=0:|'. return_string ('SITTING') . ' 1|'. return_string ('SITTING') . ' 2|'. return_string ('SITTING') . ' 3&chdl=' . return_string('ADVISOR') . '|' . return_string('LEARNER') . '&cht=bvg&chxt=x,y&chds=0,5&chxr=0,0,5|1,0,5&chdlp=b&chbh=20,1&chs=175x125&chco=4d89f9,c6d9fd&chd=t:'; //"/>';
	for ($i=0; $i<3; $i++) {
		if ($i > 0) {
			$exp .= ',';
		}
		$exp .= !empty ($advisordata[$i]['stat']) ? $advisordata[$i]['stat'] : 0 ;
	}
	$exp .= '|';
	for ($i=0; $i<3; $i++) {
		if ($i > 0) {
			$exp .= ',';
		}
		$exp .= !empty ($learnerdata[$i]['stat']) ? $learnerdata[$i]['stat'] : 0;
	}
	$exp .= '"/>';
	return $exp;
}

/**
 * Return the number of sittings a learner (and their advisor) has completed.
 * 
 * @param int $userid
 * @param string $assessmenttype (optional) 'l' learner or 'a' advisor. Leave blank for all.
 * @return int
 */
function getNumberOfSittings ($userid, $assessmenttype='') {
	if (!empty ($assessmenttype)) {
		$res = DB::executeSelect('users_attempt', 'count(*) as totalsittings', array ('userid'=>$userid, 'assessmenttype'=>$assessmenttype));
	} else {
		$res = DB::executeSelect('users_attempt', 'count(*) as totalsittings', array ('userid'=>$userid));
	}
	return $res['totalsittings'];
}

/**
 * Return an average of the sittings distance travelled scores.
 * 
 * @param array $data
 * @param int $decimalplaces
 * @return array
 */
function getAverageOfDistanceTravelled ($data, $decimalplaces = 1) {
	/* if $data is empty then return 0% distance travelled obviously */
	$texttot = 0;
	$graphtot = 0;
	foreach ($data as $row ) {
		$texttot += $row['textperc'];
		$graphtot += $row['graphperc'];
	}	
	$textavg = round($texttot / count ($data), $decimalplaces);
	// if $textavg is 0 for any reason (such as there are no results/learners) return 50% for graph (pointing north).
	if ($textavg == 0) {
		$graphavg = 50;
	} else {
		$graphavg = round($graphtot / count ($data), $decimalplaces);
	}
	return array ('textperc'=>$textavg, 'graphperc'=>$graphavg);
}

/**
 * return dates of sittings for a user
 * 
 * @param int $userid
 * @param string $assessmenttype (optional) 'l' learner or 'a' activity. blank for all.
 * @return array
 */
function getSittingsDates ($userid, $assessmenttype='') {
	if (!empty ($assessmenttype)) {
		$res = DB::executeContainedSelect('users_attempt', 'date_format(date,"%d/%m/%Y") as date', array ('userid'=>$userid, 'assessmenttype'=>$assessmenttype), 'sitting');
	} else {
		$res = DB::executeContainedSelect('users_attempt', 'date_format(date,"%d/%m/%Y") as date', array ('userid'=>$userid), 'assessmenttype, sitting');
	}	
	return $res;
}

?>
