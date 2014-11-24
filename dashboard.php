<?php
include_once ('./config.php');
include_once ($CFG->apploc  . '/lib/activity.php');
include_once ($CFG->apploc  . '/lib/stats.php');
include_once ($CFG->apploc  . '/classes/datagrid.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/lib/funcs.php');
include_once ($CFG->apploc  . '/lib/roles.php');

//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('question', 'general', 'interventions', 'reports'));

Sessions::checkUserLogIn ();



if (!empty ($_GET['uid'])) {
	$userid = $_GET['uid'];
	// need to check capability to ensure they are allowed to see data for this user...
	has_access_to_user (Sessions::getID (), $_GET['uid'] );
} else {
	$userid = Sessions::getID ();
	if (!has_capability($userid, 'reports:view_my_student_results')) {
		die ("No access");
	}
}

ob_start();

?>
<?php echo '<' . '!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'; ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo_string ('APP_NAME'); ?> : <?php echo_string ('MENU_RESULTS'); ?></title>

<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />

<link href="<?php echo ($CFG->printcssfile); ?>" rel="stylesheet" type="text/css" media="print"/>
<link href="styles/dashboard/dashboard.css" rel="stylesheet" type="text/css" />
<!--[if IE]>
  <link rel="stylesheet" type="text/css" href="styles/dashboard/dashboard_ie.css" />
<![endif]-->
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/<?php echo $CFG->jquery_ui_version; ?>/themes/ui-lightness/jquery-ui.css" /> 

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/<?php echo $CFG->jquery_ui_version; ?>/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/resizer.js"></script>
<script type="text/javascript" src="external/jgcharts/jgcharts.js"></script>
<script type="text/javascript">
$(document).ready(function(){

	
	function switchArrow (mytabbox) {
		newstatus = (mytabbox.css('display'));

		if (newstatus == 'block') {
		 	mytabbox.siblings('.thetop').removeClass ('tabtop').addClass ('tabtopopen');
		} else {
			mytabbox.siblings('.thetop').removeClass ('tabtopopen').addClass ('tabtop');
		}
			
	}
	
	$('.tabtop').click (function () {
		
		current = $(this).siblings('.tabbox').toggle();
		newstatus = ($(this).siblings('.tabbox').css('display'));
		
		if (newstatus == 'block') {
			$(this).removeClass ('tabtop').addClass ('tabtopopen');
		} else {
			$(this).removeClass ('tabtopopen').addClass ('tabtop');
		}
	
	});

	$('.collapseall').click (function () {
		if ($(this).attr ('src') == 'images/expand.png') {
			$(this).attr ('src', 'images/collapse.png');
			$(this).parent ().parent().children ().each (
					function () {
						$(this).children('.tabbox').show ();
						$(this).children('.tabbox').parent().removeClass( 'noprint' );
						switchArrow ($(this).children('.tabbox'));
					}
			);
		} else {
			$(this).attr ('src', 'images/expand.png');
			$(this).parent ().parent().children ().each (
					function () {
						$(this).children('.tabbox').hide ();
						
						switchArrow ($(this).children('.tabbox'));
					}
			);
		}
	});

	<?php 
		// get individual score results for big graph
		$scores = getSittingScores ($userid, 'all', 'l');
		$learnerdatastr = '';
		for ($i=1; $i<=8; $i++) {
			if ($i != 1 && $i != 3) {
				$learnerdatastr .= ', ';
			}
			if ($i != 3) {
				$learnerdatastr .= '[';
				for ($j=0; $j<3; $j++) {
					if ($j > 0) {
						$learnerdatastr .= ',';
					}
					$learnerdatastr .= !empty ($scores[$j]['q' . $i]) ? $scores[$j]['q' . $i] : 0;
				}
				$learnerdatastr .= ']';
			}
		}
	?>
	dataArr = new Array (<?php echo $learnerdatastr; ?>);
	// append themes
	axis = ['<?php echo_string ('L1_THEME'); ?>',
	     	'<?php echo_string ('L2_THEME'); ?>',
	     	'<?php echo_string ('L4_THEME'); ?>',
	     	'<?php echo_string ('L5_THEME'); ?>',
	     	'<?php echo_string ('L6_THEME'); ?>',
	     	'<?php echo substr ( return_string ('L7_THEME'), 0, 6) . '...'; ?>',
	     	'<?php echo_string ('L8_THEME'); ?>'];
 	//
 	legends = ['<?php echo_string ('COMM'); ?>','<?php echo_string ('MID'); ?>','<?php echo_string ('COMP'); ?>'];
  	
	var api = new jGCharts.Api(); 
	$('#lgraph').attr('src', api.make({
		data : dataArr,
		axis_labels : axis,
		size : '538x200',
		legend : legends,
		custom : 'chdlp=b&chds=0,5&chxr=0,0,5|1,0,5&',
		//colors: ['BFCFC0', 'a5bca7', '8ca98d']
		colors: ['CC4C01', 'E6CE74', '667F58']
	}));
	//.appendTo("#bar1");

	<?php 
		// get individual score results for big advisor/learner graph
		$scores = getSittingScores ($userid, 'all', 'a');
		$advisordatastr = '';
		for ($i=1; $i<=8; $i++) {
			if ($i != 1 && $i != 3) {
				$advisordatastr .= ', ';
			}
		if ($i != 3) {
				$advisordatastr .= '[';
				for ($j=0; $j<3; $j++) {
					if ($j > 0) {
						$advisordatastr .= ',';
					}
					$advisordatastr .= !empty ($scores[$j]['q' . $i]) ? $scores[$j]['q' . $i] : 0;
				}
				$advisordatastr .= ']';
			}
		}
	?>
	dataArr = new Array (<?php echo $advisordatastr; ?>);
	// append themes
	axis = ['<?php echo_string ('L1_THEME'); ?>',
	     	'<?php echo_string ('L2_THEME'); ?>',
	     	'<?php echo_string ('L4_THEME'); ?>',
	     	'<?php echo_string ('L5_THEME'); ?>',
	     	'<?php echo_string ('L6_THEME'); ?>',
	     	'<?php echo substr ( return_string ('L7_THEME'), 0, 6) . '...'; ?>',
	     	'<?php echo_string ('L8_THEME'); ?>'];
 	//
 	legends = ['<?php echo_string ('COMM'); ?>','<?php echo_string ('MID'); ?>','<?php echo_string ('COMP'); ?>'];
  	
	var api = new jGCharts.Api(); 
	$('#agraph').attr('src', api.make({
		data : dataArr,
		axis_labels : axis,
		size : '538x200',
		legend : legends,
		custom : 'chdlp=b&chds=0,5&chxr=0,0,5|1,0,5&',
		colors: ['CC4C01', 'E6CE74', '667F58']
	}));
	
	
	
	$('#container-1').tabs();

	$("#right").children ().each (
			function () {
				
				if ($(this).attr ('id') != 'activityhistory') {
					$(this).children('.tabbox').hide ();
					
					switchArrow ($(this).children('.tabbox'));
				}
			}
	);

	// if there is only one tab on the right (activity history), the learner is logged in
	// so don't change the collapse/expand button (it is the only tab, which we leave open).
	if ($('#right').children ().length > 2) {
		$("#right .collapseall").attr ('src', 'images/expand.png');
	}

	$("#left").children ().each (
			function () {
				
				$(this).children('.tabbox').show ();
				switchArrow ($(this).children('.tabbox'));
			}
	);

	$("#centre").children ().each (
			function () {
				
				$(this).children('.tabbox').show ();
				switchArrow ($(this).children('.tabbox'));
			}
	);
	
	$("#useranswers .tabbox").hide ();
	
	switchArrow ($('#useranswers .tabbox'));
	
	
	

});
</script>
</head>
<body>
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<noscript><?php echo_string ('JS_DISABLED'); ?></noscript>
<?php
				
	$userdata = DB::runSelectQuery('SELECT sb_providers.name AS providername, 
	sb_users_info.loginid, 
	sb_users_info.fname,
	sb_users_info.sname, 
	sb_groups.groupname, 
	sb_groups.assessorid,
	sb_users_info.userid,
	sb_programmes.name as prgname, 
	sb_age_groups.name as agegroup,
	sb_centres.name as centre,
	(select CONCAT(sb_users_info.fname, " ", sb_users_info.sname) from sb_users_info where sb_groups.assessorid = sb_users_info.Userid) as assessorname
FROM sb_users_info INNER JOIN sb_providers ON sb_users_info.providerid = sb_providers.ProviderID
	 INNER JOIN sb_groups ON sb_users_info.groupid = sb_groups.GroupID
	 INNER JOIN sb_programmes ON sb_users_info.programmeid = sb_programmes.ProgrammeID
	 INNER JOIN sb_age_groups ON sb_users_info.ageid = sb_age_groups.AgeID
	 LEFT OUTER JOIN sb_centres ON sb_users_info.centreid = sb_centres.CentreID
WHERE sb_users_info.UserID =' . $userid);
				
				if (!$userdata) {
					die ("Couldn't access user data."  . mysql_error());
				}
								
?>

<div id="shell">

	<div id="controls">
	</div>
	
	<div id="left">
	
		<div class="tab">
			<img class="collapseall" alt="test" src="images/collapse.png" style="vertical-align: middle" />
		</div>
		
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('USER_INFO'); ?></div>
			<div class="tabbox">
				<p><?php echo $userdata['fname'] . ' ' . $userdata['sname']; ?></p>
					<p>
                    	<strong><?php echo_string ('INSTITUTION'); ?>:</strong> <?php echo $userdata['providername']; ?><br/>
                    	<strong><?php echo_string ('CENTRE'); ?>:</strong> <?php echo $userdata['centre']; ?><br/>
                    	<strong><?php echo_string ('AGE_GROUP'); ?>:</strong> <?php echo $userdata['agegroup']; ?>
                    	
                    </p>
                    <?php if  (has_capability(Sessions::getID(), 'users:edit_profile')) {
						echo ('<a href="user_edit.php?userid=' . $userid . '">Edit Profile</a>');
					}?>
			</div>
		</div>
		
		
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('DB_DISTANCE'); ?></div>
			<div class="tabbox">
			<?php echo_string ('LEARNER'); ?>: <?php $dist = getDistanceTravelledAdvanced ($userid, 'l'); $ldist = $dist; echo $dist['textperc']; ?>%<br/>
			<img src="http://chart.apis.google.com/chart?chs=170x100&cht=gom&chd=t:<?php echo $dist['graphperc'];?>" /><br/>
			<?php if (has_capability (Sessions::getID (), 'reports:view_advisor_results')) { ?>
				<?php echo_string ('ADVISOR'); ?>: <?php $dist = getDistanceTravelledAdvanced ($userid, 'a'); $adist = $dist; echo $dist['textperc']?>%<br/>
				<img src="http://chart.apis.google.com/chart?chs=170x100&cht=gom&chd=t:<?php echo $dist['graphperc'];?>" /><br/>
			<?php } ?>
			
			</div>
		</div>
		
		
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('IV_ALL'); ?></div>
			<div class="tabbox">
				<?php 
					$interventions = getInterventionsByUserID ($userid);
										
					if ($interventions) {
						foreach ($interventions as $int) {
							echo $int['name'];
							if (!empty ($int['other']) ){
								echo ': ' . $int['other'];
							}
							
							echo ' (' . return_string ('SITTING') . ' ' . $int['sitting'] . ')';
							echo '<br/>';
						}
					} else {
						// No interventions taken
						echo_string ('IV_NONE');
					}
					?>
			</div>
		</div>
		
		<!--  
		<div class="tab">
			<div class="thetop tabtop">Your Advisors</div>
			<div class="tabbox">
				<?php 
					$advisors = getAdvisorsForActivities ($userid);
										
					if ($advisors) {
						foreach ($advisors as $adv) {
							echo $adv['fname'] . ' ' . $adv['sname'] . ' ';
							echo ' (' . return_string ('SITTING') . ' ' . $adv['sitting'] . ')';
							echo '<br/>';
						}
					} else {
						// No interventions taken
						echo "No data is available.";
					}
					?>
			</div>
		</div>
		 -->
		 
		<?php
		
		$legends = array (return_string ('COMM'), return_string ('MID'),  return_string ('COMP'));
		?>
		
		
		
		
		<?php 
		
		$check = DB::runSelectQuery ('SELECT count(*) as activitiescount FROM sb_users_attempt where userid=' . $userid . ';');
		if ($check['activitiescount'] >= 6) {
			if (has_capability(Sessions::getID(), 'reports:view_my_student_results')) {
				
			?>
		
			<div class="tab noprint">
				<div class="thetop tabtop">TRaCIO Certificate</div>
				<div class="tabbox">
					<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
						<p>Congratulations! You have now completed the TRaCIO programme.</p>
						<p>You may now print out a certification to show your completion.</p>
						<input type="submit" value="Print Completion Certificate" />
						<input type="hidden" name="uid" value="<?php echo $userid; ?>" id="uid"/>
						<input type="hidden" name="cert" value="1" />
						
					</form>
				</div>
			</div>
		<?php } else if (has_capability(Sessions::getID(), 'reports:view_student_results')) { ?>
			<div class="tab noprint">
				<div class="thetop tabtop">TRaCIO Certificate</div>
				<div class="tabbox">
					<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
						<p>The user has now completed the TRaCIO programme.</p>
						<p>You may now print out a completion certificate for the user or your files.</p>
						<input type="submit" name="advisorscreen" value="Print Advisor" />
						<input type="submit" value="Print Learner" />
						<input type="hidden" name="uid" value="<?php echo $userid; ?>" id="uid"/>
						<input type="hidden" name="cert" value="1" />
						
					</form>
				</div>
			</div>
		
		<?php } // if capability
		} // if $check['activitescount']
		?>
	</div>
	
	<div id="centre">
		
		<div class="tab">
			<img class="collapseall" alt="test" src="images/collapse.png" style="vertical-align: middle" />
		</div>
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('LEARNER'); ?></div>
			<div class="tabbox">
				<div id="bar1"><img id="lgraph" border="0"/></div>
			</div>
		</div>
		
		<?php if (has_capability (Sessions::getID (), 'reports:view_advisor_results')) { ?>
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('ADVISOR'); ?></div>
			<div class="tabbox">
				<div id="bar1"><img id="agraph" border="0"/></div>
			</div>
		</div>
		<?php } ?>
		
		
		
		<?php if (has_capability (Sessions::getID (), 'reports:view_comparative_results') ) { ?>
		<div class="tab page-break" id="useranswers">
			<div class="thetop tabtop"><?php echo_string ('USER_ANSWERS'); ?></div>
			<div class="tabbox">
				<div id="container-1">
		            <ul>
		                <li><a href="#fragment-1"><span><?php echo_string ('SITTING'); ?> 1</span></a></li>
		                <li><a href="#fragment-2"><span><?php echo_string ('SITTING'); ?> 2</span></a></li>
		                <li><a href="#fragment-3"><span><?php echo_string ('SITTING'); ?> 3</span></a></li>
		            </ul>
		            <div id="fragment-1">
				
					
					<?php 
					// get textual answers and report
					$sitting = 1;
					 $answers = DB::runSelectQuery('SELECT sb_users_attempt.userid, 
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
								WHERE sb_users_attempt.userid=' . $userid . ' and sitting = ' . $sitting . ' order by sitting, assessmenttype;');
					
					  
					  $advisorres = '';
					  $learnerres = '';
					  
					 if ($answers) {
					 	// count returns 
						if (count ($answers) > 2) {
						
							// determine which result we can see
							if ($answers['assessmenttype'] == 'l') {
								$learnerres = $answers;
							} else if ($answers['assessmenttype'] == 'a') {
								$advisorres = $answers;	
							}
						} else {
							$advisorres = $answers[0];
							$learnerres = $answers[1];
						}
					 }
					 
					 
					 ?>
						 <h2><?php echo_string ('SITTING'); ?> 1</h2>
						  <table cellpadding="0" cellspacing="0" id="responsetable" width="510">
					 <tr>
					 
					 	<th><?php echo_string ('ADVISOR'); ?> <span style="background-color: red;">&nbsp;&nbsp;</span></th>
				
					 	<th></th>
					 	<th><?php echo_string ('LEARNER'); ?> <span style="background-color: #FFCC00;">&nbsp;&nbsp;</span></th>
					 </tr>
					 <?php 
			for ($i=1; $i<=8; $i++) {
					 	if ($i != 3) {
					 	?>
					 		<tr class="txtresponse">
					 		
					 			<td>
					 				<?php echo_string ('A' . $i . '_' . (!empty ($advisorres['q' . $i]) ? $advisorres['q' . $i] : '0')); ?>
					 			</td>
					 			
					 			<td class="centregraph"><strong><?php echo_string ('A' . $i . '_THEME'); ?></strong>
					 				<img src="http://chart.apis.google.com/chart?cht=bvg&chs=70x110&chbh=20,1&chxt=y&chd=t:<?php echo (!empty ($advisorres['q' . $i]) ? $advisorres['q' . $i] : 0); ?>|<?php echo (!empty ($learnerres['q' . $i]) ? $learnerres['q' . $i] : 0) ?>&chds=0,5&chxr=0,0,5|1,0,5&&chf=&chco=FF0000,FFCC00&&chdlp=b&agent=jgcharts" id="response_graph<?php  echo $i; ?>"/>
					 			</td>
					 			<td class="txtresponse">
					 			
					 				<?php echo_string ('L' . $i . '_' . (!empty ($learnerres['q' . $i]) ? $learnerres['q' . $i] : '0')); ?>
					 			</td>
				  <?php } else { // $i = 3 ?>
					 		<tr class="txtresponse">
					 			<td>
					 				<ul>
					 				<?php 
					 				$noneflag = true;
					 				for ($j=1; $j<=7; $j++) {
					 					if (!empty ($advisorres ['chk' . $j])) {
					 						$noneflag = false;
					 						echo '<li>';
					 						echo_string ('A' . $i . '_' . $j);
					 						echo '</li>';
					 					}
					 				}
					 				if ($noneflag) {
					 					echo '<li>' . echo_string ('NONE_SPEC') . '</li>';
					 				}
					 				?>
					 				</ul>
					 			</td>
					 			<td class="centregraph"><strong><?php echo_string ('A' . $i . '_THEME'); ?></strong></td>
					 			<td class="txtresponse">
					 				<ul>
					 				<?php 
					 				$noneflag = true;
					 				for ($j=1; $j<=7; $j++) {
					 					if (!empty ($advisorres ['chk' . $j])) {
					 						$noneflag = false;
					 						echo '<li>';
					 						echo_string ('L' . $i . '_' . $j);
					 						echo '</li>';
					 					}
					 				}
				  					if ($noneflag) {
					 					echo '<li>' . echo_string ('NONE_SPEC') . '</li>';
					 				}
					 				?>
					 				</ul>
					 			</td>
				  <?php } // end ifs ?>
					 	   </tr>
		<?php } // end for ?>
					<?php  if ($answers) {?>
						  <tr class="revisiondata">
						  		
					 	   		<td>
					 	   		<?php if ($advisorres) { 
					 	   			
					 	   			 
					
								?>
				
					 	   		<p>Sitting was completed on <?php echo date_format (date_create ($advisorres['date']), $CFG->globalDateFmt); ?>.</p>
					 	   		<p>There have been <?php echo checkForRevisionsByAttemptID($advisorres ['AttemptID']); ?> revisions.</p>
					 	   		<p><a href="activity_advisor_intervention.php?userid=<?php echo $userid; ?>&edit=1&sitting=<?php echo $sitting; ?>">Edit this sitting</a></p>
					
					 	   		<?php } ?></td>
					 			
					 			<td></td>
					 			<td>
					 			<?php if ($learnerres) {?>
					 			<p>Sitting was completed on <?php echo date_format (date_create ($learnerres['date']), $CFG->globalDateFmt); ?>.</p>
					 			<p>There have been <?php echo checkForRevisionsByAttemptID($learnerres ['AttemptID']); ?> revisions.</p>
					 			<p><a href="activity_learner_preedit.php?userid=<?php echo $userid; ?>&edit=1&sitting=<?php echo $sitting; ?>">Edit this sitting</a></p>
					 			<?php } ?>
					 			</td>
					 			
						</tr>
					<?php } // if answers?>
					<tr>
					 
					 	<th><?php echo_string ('ADVISOR'); ?> <span style="background-color: red;">&nbsp;&nbsp;</span></th>
				
					 	<th></th>
					 	<th><?php echo_string ('LEARNER'); ?> <span style="background-color: #FFCC00;">&nbsp;&nbsp;</span></th>
					 </tr>
					</table>
					</div>
					
					<div id="fragment-2">
				
					
					<?php 
					// get textual answers and report
					$sitting = 2;
					 $answers = DB::runSelectQuery('SELECT sb_users_attempt.userid, 
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
								WHERE sb_users_attempt.userid=' . $userid . ' and sitting = ' . $sitting . ' order by sitting, assessmenttype;');
					
					   $advisorres = '';
					  $learnerres = '';
					  
					 if ($answers) {
					 	// count returns 
						if (count ($answers) > 2) {
					
							// determine which result we can see
							if ($answers['assessmenttype'] == 'l') {
								$learnerres = $answers;
							} else if ($answers['assessmenttype'] == 'a') {
								$advisorres = $answers;	
							}
						} else {
							$advisorres = $answers[0];
							$learnerres = $answers[1];
						}
					 }
					 
					 ?>
					 <h2><?php echo_string ('SITTING'); ?> 2</h2>
					 <table cellpadding="0" cellspacing="0" id="responsetable" width="510">
					 <tr>		
					 	<th><?php echo_string ('ADVISOR'); ?> <span style="background-color: red;">&nbsp;&nbsp;</span></th>
					 	<th></th>
					 	<th><?php echo_string ('LEARNER'); ?> <span style="background-color: #FFCC00;">&nbsp;&nbsp;</span></th>
					 </tr>
					 <?php 
			for ($i=1; $i<=8; $i++) {
					 	if ($i != 3) {
					 	?>
					 		<tr class="txtresponse">
					 			<td>
					 				<?php echo_string ('A' . $i . '_' . (!empty ($advisorres['q' . $i]) ? $advisorres['q' . $i] : '0')); ?>
					 			</td>
					 			<td class="centregraph"><strong><?php echo_string ('A' . $i . '_THEME'); ?></strong>
					 				<img src="http://chart.apis.google.com/chart?cht=bvg&chs=70x110&chbh=20,1&chxt=y&chd=t:<?php echo (!empty ($advisorres['q' . $i]) ? $advisorres['q' . $i] : 0); ?>|<?php echo (!empty ($learnerres['q' . $i]) ? $learnerres['q' . $i] : 0) ?>&chds=0,5&chxr=0,0,5|1,0,5&&chf=&chco=FF0000,FFCC00&&chdlp=b&agent=jgcharts" id="response_graph<?php  echo $i; ?>"/>
					 			</td>
					 			<td class="txtresponse">
					 				<?php echo_string ('L' . $i . '_' . (!empty ($learnerres['q' . $i]) ? $learnerres['q' . $i] : '0')); ?>
					 			</td>
				  <?php } else { // $i = 3 ?>
					 		<tr class="txtresponse">
					 			<td>
					 				<ul>
					 				<?php 
					 				$noneflag = true;
					 				for ($j=1; $j<=7; $j++) {
					 					if (!empty ($advisorres ['chk' . $j])) {
					 						$noneflag = false;
					 						echo '<li>';
					 						echo_string ('A' . $i . '_' . $j);
					 						echo '</li>';
					 					}
					 				}
					 				if ($noneflag) {
					 					echo '<li>' . echo_string ('NONE_SPEC') . '</li>';
					 				}
					 				?>
					 				</ul>
					 			</td>
					 			<td class="centregraph"><strong><?php echo_string ('A' . $i . '_THEME'); ?></strong></td>
					 			<td class="txtresponse">
					 				<ul>
					 				<?php 
					 				$noneflag = true;
					 				for ($j=1; $j<=7; $j++) {
					 					if ($learnerres ['chk' . $j]) {
					 						$noneflag = false;
					 						echo '<li>';
					 						echo_string ('L' . $i . '_' . $j);
					 						echo '</li>';
					 					}
					 				}
				  					if ($noneflag) {
					 					echo '<li>' . echo_string ('NONE_SPEC') . '</li>';
					 				}
					 				?>
					 				</ul>
					 			</td>
				  <?php } // end ifs ?>
					 	   </tr>
					 	   
		<?php } // end for ?>
							<?php  if ($answers) {?>
						  <tr class="revisiondata">
						  		
					 	   		<td>
					 	   		<?php if ($advisorres) {?>
					 	   		<p>Sitting was completed on <?php echo date_format (date_create ($advisorres['date']), $CFG->globalDateFmt); ?>.</p>
					 	   		<p>There have been <?php echo checkForRevisionsByAttemptID($advisorres ['AttemptID']); ?> revisions.</p>
					 	   		<p><a href="activity_advisor_intervention.php?userid=<?php echo $userid; ?>&edit=1&sitting=<?php echo $sitting; ?>">Edit this sitting</a></p>
					
					 	   		<?php } ?></td>
					 			
					 			<td></td>
					 			<td>
					 			<?php if ($learnerres) {?>
					 			<p>Sitting was completed on <?php echo date_format (date_create ($learnerres['date']), $CFG->globalDateFmt); ?>.</p>
					 			<p>There have been <?php echo checkForRevisionsByAttemptID($learnerres ['AttemptID']); ?> revisions.</p>
					 			<p><a href="activity_learner_preedit.php?userid=<?php echo $userid; ?>&edit=1&sitting=<?php echo $sitting; ?>">Edit this sitting</a></p>
					 				<?php } ?>
					 			</td>
					 	    </tr>
					<?php } // if answers?>
					<tr>
					 
					 	<th><?php echo_string ('ADVISOR'); ?> <span style="background-color: red;">&nbsp;&nbsp;</span></th>
				
					 	<th></th>
					 	<th><?php echo_string ('LEARNER'); ?> <span style="background-color: #FFCC00;">&nbsp;&nbsp;</span></th>
					 </tr>
					</table>
					</div>
					
					<div id="fragment-3">
				
					
					<?php 
					// get textual answers and report
					$sitting = 3;
					 $answers = DB::runSelectQuery('SELECT sb_users_attempt.userid, 
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
								WHERE sb_users_attempt.userid=' . $userid . ' and sitting = ' . $sitting . ' order by sitting, assessmenttype;');
					
					 
					 
					  $advisorres = '';
					  $learnerres = '';
					  
					 if ($answers) {
					 	// count returns 
						if (count ($answers) > 2) {
						
							// determine which result we can see
						
							if ($answers['assessmenttype'] == 'l') {
								$learnerres = $answers;
							} else if ($answers['assessmenttype'] == 'a') {
								$advisorres = $answers;	
							}
						} else {
							$advisorres = $answers[0];
							$learnerres = $answers[1];
						}
					 }
					 
					 ?>
					 
					  <h2><?php echo_string ('SITTING'); ?> 3</h2>
					 <table cellpadding="0" cellspacing="0" id="responsetable" width="510">
					 <tr>		
					 	<th><?php echo_string ('ADVISOR'); ?> <span style="background-color: red;">&nbsp;&nbsp;</span></th>
					 	<th></th>
					 	<th><?php echo_string ('LEARNER'); ?> <span style="background-color: #FFCC00;">&nbsp;&nbsp;</span></th>
					 </tr>
					 <?php 
			for ($i=1; $i<=8; $i++) {
					 	if ($i != 3) {
					 	?>
					 		<tr class="txtresponse">
					 			<td>
					 				<?php echo_string ('A' . $i . '_' . (!empty ($advisorres['q' . $i]) ? $advisorres['q' . $i] : '0')); ?>
					 			</td>
					 			<td class="centregraph"><strong><?php echo_string ('A' . $i . '_THEME'); ?></strong>
					 				<img src="http://chart.apis.google.com/chart?cht=bvg&chs=70x110&chbh=20,1&chxt=y&chd=t:<?php echo (!empty ($advisorres['q' . $i]) ? $advisorres['q' . $i] : 0); ?>|<?php echo (!empty ($learnerres['q' . $i]) ? $learnerres['q' . $i] : 0) ?>&chds=0,5&chxr=0,0,5|1,0,5&&chf=&chco=FF0000,FFCC00&&chdlp=b&agent=jgcharts" id="response_graph<?php  echo $i; ?>"/>
					 			</td>
					 			<td class="txtresponse">
					 				<?php echo_string ('L' . $i . '_' . (!empty ($learnerres['q' . $i]) ? $learnerres['q' . $i] : '0')); ?>
					 			</td>
				  <?php } else { // $i = 3 ?>
					 		<tr class="txtresponse">
					 			<td>
					 				<ul>
					 				<?php 
					 				$noneflag = true;
					 				for ($j=1; $j<=7; $j++) {
					 					if (!empty ($advisorres ['chk' . $j])) {
					 						$noneflag = false;
					 						echo '<li>';
					 						echo_string ('A' . $i . '_' . $j);
					 						echo '</li>';
					 					}
					 				}
					 				if ($noneflag) {
					 					echo '<li>' . echo_string ('NONE_SPEC') . '</li>';
					 				}
					 				?>
					 				</ul>
					 			</td>
					 			<td class="centregraph"><strong><?php echo_string ('A' . $i . '_THEME'); ?></strong></td>
					 			<td class="txtresponse">
					 				<ul>
					 				<?php 
					 				$noneflag = true;
					 				for ($j=1; $j<=7; $j++) {
					 					if (!empty ($learnerres ['chk' . $j])) {
					 						$noneflag = false;
					 						echo '<li>';
					 						echo_string ('L' . $i . '_' . $j);
					 						echo '</li>';
					 					}
					 				}
				  					if ($noneflag) {
					 					echo '<li>' . echo_string ('NONE_SPEC') . '</li>';
					 				}
					 				?>
					 				</ul>
					 			</td>
					 			
				  <?php } // end ifs ?>
					 	   </tr>
					 	   
		<?php } // end for ?>
					<?php  if ($answers) {?>
						  <tr class="revisiondata">
						  		
					 	   		<td>
					 	   		<?php if ($advisorres) {?>
					 	   		<p>Sitting was completed on <?php echo date_format (date_create ($advisorres['date']), $CFG->globalDateFmt); ?>.</p>
					 	   		<p>There have been <?php echo checkForRevisionsByAttemptID($advisorres ['AttemptID']); ?> revisions.</p>
					 	   		<p><a href="activity_advisor_intervention.php?userid=<?php echo $userid; ?>&edit=1&sitting=<?php echo $sitting; ?>">Edit this sitting</a></p>
					
					 	   		<?php } ?></td>
					 			
					 			<td></td>
					 			<td>
					 			<?php if ($learnerres) {?>
					 			<p>Sitting was completed on <?php echo date_format (date_create ($learnerres['date']), $CFG->globalDateFmt); ?>.</p>
					 			<p>There have been <?php echo checkForRevisionsByAttemptID($learnerres ['AttemptID']); ?> revisions.</p>
					 			<p><a href="activity_learner_preedit.php?userid=<?php echo $userid; ?>&edit=1&sitting=<?php echo $sitting; ?>">Edit this sitting</a></p>
					 				<?php } ?>
					 				</td>
					 		
						</tr>
					<?php } // if answers?>
					<tr>
					 
					 	<th><?php echo_string ('ADVISOR'); ?> <span style="background-color: red;">&nbsp;&nbsp;</span></th>
				
					 	<th></th>
					 	<th><?php echo_string ('LEARNER'); ?> <span style="background-color: #FFCC00;">&nbsp;&nbsp;</span></th>
					 </tr>
					 	   
					</table>
					</div>
	             </div>
			</div>
		</div>
			<?php } // end of comparative_results capability check ?>
		
	</div>
	
	<div id="right" class="page-break">
	
	<div class="tab">
			<img class="collapseall" alt="test" src="images/collapse.png" style="vertical-align: middle" />
		</div>
		
	<?php if (has_capability (Sessions::getID (), 'reports:view_advisor_results')) { ?>
	
		
		
		
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('A1_THEME'); ?></div>
			<div class="tabbox">
				<?php 
					$learnerscores = getScoresForDiscipline ($userid, 1, 'l');
					$advisorscores = getScoresForDiscipline ($userid, 1, 'a');
					echo drawDisciplineGraph ($advisorscores, $learnerscores);
				?>
			</div>
		</div>
		
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('A2_THEME'); ?></div>
			<div class="tabbox">
				<?php 
					$learnerscores = getScoresForDiscipline ($userid, 2, 'l');
					$advisorscores = getScoresForDiscipline ($userid, 2, 'a');
					echo drawDisciplineGraph ($advisorscores, $learnerscores);
				?>
			</div>
		</div>
		
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('A4_THEME'); ?></div>
			<div class="tabbox">
				<?php 
					$learnerscores = getScoresForDiscipline ($userid, 4, 'l');
					$advisorscores = getScoresForDiscipline ($userid, 4, 'a');
					echo drawDisciplineGraph ($advisorscores, $learnerscores);
				?>
			</div>
		</div>
		
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('A5_THEME'); ?></div>
			<div class="tabbox">
				<?php 
					$learnerscores = getScoresForDiscipline ($userid, 5, 'l');
					$advisorscores = getScoresForDiscipline ($userid, 5, 'a');
					echo drawDisciplineGraph ($advisorscores, $learnerscores);
				?>
			</div>
		</div>
		
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('A6_THEME'); ?></div>
			<div class="tabbox">
				<?php 
					$learnerscores = getScoresForDiscipline ($userid, 6, 'l');
					$advisorscores = getScoresForDiscipline ($userid, 6, 'a');
					echo drawDisciplineGraph ($advisorscores, $learnerscores);
				?>
			</div>
		</div>
		
		<div class="tab">
			<div class="thetop tabtop"><?php echo_string ('A7_THEME'); ?></div>
			<div class="tabbox">
				<?php 
					$learnerscores = getScoresForDiscipline ($userid, 7, 'l');
					$advisorscores = getScoresForDiscipline ($userid, 7, 'a');
					echo drawDisciplineGraph ($advisorscores, $learnerscores);
				?>
			</div>
		</div>
		
		<div class="tab">
			<div class="thetop tabtop"><?php //echo_string ('A7_THEME'); ?>Beliefs</div>
			<div class="tabbox">
				<?php 
					$learnerscores = getScoresForDiscipline ($userid, 8, 'l');
					$advisorscores = getScoresForDiscipline ($userid, 8, 'a');
					echo drawDisciplineGraph ($advisorscores, $learnerscores);
				?>
			</div>
		</div>
		
		
		<?php } // reports:view_advisor_results ?>
		<?php 
			// get all activity history for sidebar.
			// this includes dates and advisor names
		?>
		<div class="tab" id="activityhistory">
			<div class="thetop tabtop">Activity History</div>
			<div class="tabbox">
				<?php 
					// work out who is viewing these results - advisor or learner?
					$currentrole = Sessions::getUserInfo ('roleid') ;
					
					if ($currentrole == $CFG->learnerroleid) { //learner
						$context = 'You';
					} else {
						$context = 'Learner';
					}
					
					$dates = getActivityHistoryOverview ($userid);
										
					if ($dates) {
						foreach ($dates as $date) {
							echo mysql2ukdate ($date['date']);
							echo '<ul>';
							echo '<li>';
							if ($date['assessmenttype'] == 'l') {
								echo $context . ' sat activity ' . $date['sitting'] . ' (' . $legends[($date['sitting']) - 1] . ')';
							} else {
								echo 'Advisor ' . $date['fname'] . ' ' . $date['sname'] . ' sat activity ' . $date['sitting'] . ' (' . $legends[($date['sitting']) - 1] . ')';
							}
							echo '</li>';
							echo '</ul>';				
						}
					} else {
						// No interventions taken
						echo "No data is available.";
					}
					?>
			</div>
		</div> <!--  #activityhistory -->
		
	</div>
	
	
	
		
</div>




<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>
<?php 

// do they require a certificate and have they completed?
if ($_GET['cert'] == 1 && $check['activitiescount'] >= 6) {
	
	ob_clean ();
	$advisorscreen = (isset ($_GET['advisorscreen']) && has_capability(Sessions::getID (), 'reports:view_student_results')) ? true : false ;
	$activitytype = $advisorscreen ? 'a' : 'l';
	


	
?>
<?php echo '<' . '!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'; ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>TRaCIO</title>
<link href="styles.css" rel="stylesheet" type="text/css" />
<link href="print.css" rel="stylesheet" type="text/css" media="print" />
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="js/jgcharts/jgcharts.js"></script>

<script type="text/javascript">
$(document).ready(function(){
<?php 


		// get individual score results for big graph
		$scores = getSittingScores ($userid, 'all', $activitytype);
		$learnerdatastr = '';
		for ($i=1; $i<=8; $i++) {
			if ($i != 1 && $i != 3) {
				$learnerdatastr .= ', ';
			}
			if ($i != 3) {
				$learnerdatastr .= '[';
				for ($j=0; $j<3; $j++) {
					if ($j > 0) {
						$learnerdatastr .= ',';
					}
					$learnerdatastr .= !empty ($scores[$j]['q' . $i]) ? $scores[$j]['q' . $i] : 0;
				}
				$learnerdatastr .= ']';
			}
		}
	?>
	dataArr = new Array (<?php echo $learnerdatastr; ?>);
	// append themes
	axis = ['<?php echo_string ('L1_THEME'); ?>',
	     	'<?php echo_string ('L2_THEME'); ?>',
	     	'<?php echo_string ('L4_THEME'); ?>',
	     	'<?php echo_string ('L5_THEME'); ?>',
	     	'<?php echo_string ('L6_THEME'); ?>',
	     	'<?php echo substr ( return_string ('L7_THEME'), 0, 6) . '...'; ?>',
	     	'<?php echo_string ('L8_THEME'); ?>'];
 	//
 	legends = ['<?php echo_string ('COMM'); ?>','<?php echo_string ('MID'); ?>','<?php echo_string ('COMP'); ?>'];
  	
	var api = new jGCharts.Api(); 
	$('#lgraph').attr('src', api.make({
		data : dataArr,
		axis_labels : axis,
		size : '500x170',
		legend : legends,
		custom : 'chdlp=b&chds=0,5&chxr=0,0,5|1,0,5&',
		//colors: ['BFCFC0', 'a5bca7', '8ca98d']
		colors: ['CC4C01', 'E6CE74', '667F58']
	}));

	window.setTimeout(function() {
		    window.print ();
	}, 2000);

});
</script>


</head>

<body>
<div style="text-align:right; position:relative; left:0px; top:0px;">
<a title="TRaCIO Home" href="/home.php"><img src="images/tracio.png" alt="TRaCIO Home" align="left" /></a>
<img src="images/logo_small.gif" alt="Small Logo" style="vertical-align: middle" />
</div>
<div class="redbar"></div>

<div id="page_pad">
<div id="name"><?php echo $userdata['fname'] . ' ' . $userdata['sname']; ?></div>
<div id="comp_text">
<?php if ($advisorscreen) {?>
has completed the TRaCIO program with
<?php } else { ?>
You have completed the TRaCIO program with
<?php } ?>
</div>

<div id="tracio_centre"><?php echo $userdata['providername']; ?></div>
<div id="dist_moved">
<?php if ($advisorscreen) {?>
	Their distance travelled was <strong><?php echo $adist['textperc']; ?>%</strong>
	<img alt="chart" style="vertical-align: middle" src="http://chart.apis.google.com/chart?chs=170x100&cht=gom&chd=t:<?php echo $adist['graphperc'];?>" />
<?php } else { ?>
    Your distance travelled is <strong><?php echo $ldist['textperc']; ?>%</strong>
    <img alt="chart" style="vertical-align: middle" src="http://chart.apis.google.com/chart?chs=170x100&cht=gom&chd=t:<?php echo $ldist['graphperc'];?>" />
<?php } ?>
    <p><img id="lgraph" border="0"/></p>

</div>
<div id="interventions">
    
    
    <?php 
 
    
    if ($interventions) { ?>
    <?php if ($advisorscreen) {?>
    	<p>During the TRaCIO program they undertook the following interventions:</p>
    <?php } else { ?>
    	<p>During the TRaCIO program you undertook the following interventions:</p>
    <?php } ?>
    	<ul>
	<?php 
	foreach ($interventions as $int) {
		?><li><?php echo $int['name']; ?></li>
	<?php }
    ?>
    </ul>
    <?php }?>
	
    
</div>
</div>

</body>
</html>




<?php 
}  else {
	ob_end_flush();
}?>



