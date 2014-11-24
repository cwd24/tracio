<?php
include_once ('./config.php');
include_once ($CFG->apploc  . '/lib/activity.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/lib/stats.php');
include_once ($CFG->apploc . '/classes/datagrid.php');
//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('question', 'general'));

// work out if user has filled this form already
Sessions::checkUserLogIn ();

$userid = Sessions::getID();
$sitting = !empty ($_POST['sittings']) ? $_POST['sittings'] : false;
$assesstype = 'l';

if (isset ($_POST['edit'])) {
	$userid = $_POST['userid'];
}


$assesstype = 'l';

$editing = !empty ($_POST['edit']) ? $_POST['edit'] : false;
$mode = !empty ($_GET['mode']) ? $_GET['mode'] : 'new';

if ($editing) { $mode = 'edit'; }

if (userAlreadySat ($userid, $sitting, $assesstype) || $mode=='display' || $mode=='edit')  {
	//$mode = 'display';
	// get results from previous sitting to display below
	$results = getAttemptResults ($userid, $sitting, $assesstype);
	// create radioarray
	$radioarray = '"q1_' . $results["q1"] . '","q2_' . $results["q2"] .'","q4_' . $results["q4"] .'", "q5_' . $results["q5"] . '", "q6_' . $results["q6"] . '", "q7_' . $results["q7"] . '", "q8_' . $results["q8"] . '"';
	// create list of checkboxes for jquery
	$checkboxesarray = array ();
	foreach (array ("chk1", "chk2", "chk3", "chk4", "chk5", "chk6", "chk7") as $key) {
		if ($results[$key]) {
			array_push ($checkboxesarray, '"'. $key . '"');
		}
	}
	if (count ($checkboxesarray) >0 ) {
		$checkboxesarray = implode(',', $checkboxesarray);
	} else {
		$checkboxesarray = "";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="-1" />
<title><?php echo_string ('APP_NAME'); ?>: <?php echo_string ('L_ACTIVITY'); ?></title>
<link href="styles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/assessment_validators.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="js/resizer.js"></script>

<script type="text/javascript">
	$(document).ready(function(){
		mode = '';
		
		function disableRadioButtons () {
			$("input:radio").attr('disabled',true);
		}
		function disableCheckBoxes () {
			$("input:checkbox").attr('disabled',true);
		}
		function hideAllRadiosCheckboxes () {
			$("input:checkbox").hide ();
			$("input:radio").hide ();
		}
		function setRadioButtons (radioArray) {
			for (i=0; i<radioArray.length; i++) {
				$("#" + radioArray[i]).attr("checked", "checked");
				$("#" + radioArray[i]).parent().parent().children("td").css({"fontWeight":"bold"});
			}
		}
		function setCheckBoxes (checkedArray) {
			for (i=0;i<checkedArray.length; i++) {
				$("#" + checkedArray[i]).attr("checked", "checked");
				$("#" + checkedArray[i]).parent().parent().children("td").css({"fontWeight":"bold"});
			}
		}
		<?php if ($mode == 'display' || $mode == 'edit') {?>
		// disable and set radio buttons
		mode = "<?php echo $mode; ?>";
		<?php 		if ($mode == 'display') { ?>
		// disable and set radio buttons
	   	disableRadioButtons ();
	   	disableCheckBoxes ();
	    $('#q3_8').attr ('disabled', true);
		$("#submitbutt").hide ();
		<?php 		} ?>
	   	setRadioButtons(Array (<?php echo $radioarray; ?>));
	   	setCheckBoxes (Array (<?php echo $checkboxesarray; ?>));
	    $('#q3_8').text ('<?php echo $results['other']; ?>');
	   	<?php } ?>
	   	var qCounter = 1;
	   	var qTotal = 8; //number of questions
	   	function displayNext () {
	   		if (qCounter < qTotal) {
	   			qCounter ++;
				displayQuestion (qCounter);
	   		}
	   		return false;
	   	}
	   	function displayPrev () {
		   	if (qCounter > 1) {
				qCounter --;
				displayQuestion (qCounter);
		   	}	
		   	return false;
		}
		
	   	function displayQuestion (q) {
	   		hideQuestions ();
	   		$('#progress').attr ("src", "images/" + q + ".gif");
	   		if (qCounter == 1) {
				$('#buttprev').hide ();
		   	} else {
		   		$('#buttprev').show ();
		   	}
	   		if (qCounter == qTotal) {
				$('#buttnext').hide ();
				if (mode != 'display') {
					$("#submitbutt").show ();
				}
		   	} else {
		   		$('#buttnext').show ();
		   		$("#submitbutt").hide ();
		   	}
	   		$('#question_' + q).show ();
	   	}
	   	function hideQuestions () {
		   	for (var i=1; i<=qTotal; i++) {
				$('#question_' + i).hide ();
		   	}
	   	}
	   	displayQuestion (qCounter);
	   	
	   	$('#buttprev').css ('visibility', 'visible');
	   	$('#buttnext').css ('visibility', 'visible');
	   	$('#buttnext').click (displayNext);
		$('#buttprev').click (displayPrev);

	
		
	});
</script>


</head>
<body>
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>

<?php 
if ($editing) {
		// advisor is editing...
		// 
		// get sitting and userid from $_GET?
		
		$res = DB::runSelectQuery ('select CONCAT(sb_users_info.fname, " ", sb_users_info.sname) as fullname from sb_users_info where userid=' . $userid . ';');
		$res['sittings'] = $_POST['sittings'];
} else {
	// check how many sittings user has done
	$res = DB::runSelectQuery('select count(*) as sittings,
							   (select CONCAT(sb_users_info.fname, " ", sb_users_info.sname) from sb_users_info where userid=' . $userid . ') as fullname
							   from sb_users_attempt where userid=' . $userid  . ' and assessmenttype="l"', false);
	
	$advisorsittings = DB::runSelectQuery('select count(*) as sittings from sb_users_attempt where userid=' . $userid  . ' and assessmenttype="a"', false);
	
	if ($res) {
		if ($res['sittings'] >= 3) {
			die (sprint_string ('L_ALL_COMPLETE', $res['fullname']));
		} else if ($res['sittings'] > $advisorsittings['sittings']) {
			die ("You may not complete your next activity until your advisor has completed theirs.");
		} else {
			$res['sittings']++;
			$sitting = $res['sittings'];
		}
	} else {
		die ("Error: unable to determine how many sittings user has undertaken or no user specified.");
	}
}
?>

<!--  <h1><?php echo_string ('L_ACTIVITY'); ?></h1> -->
<div id="topImage">
	<img src="images/<?php echo_image('Q1'); ?>" alt="progress" id="progress" />
</div>
<form action="activity_save.php" method="post" onsubmit="return validate_learner(this);">
	<input type="hidden" name="formtype" value="l" />
	<input type="hidden" name="userid" value="<?php echo $userid; ?>" />
	<input type="hidden" name="sitting" value="<?php echo $sitting; ?>" />
	<?php if ($editing) { ?>
			<input type="hidden" value="1" name="edit" id="edit" />
	<?php } ?>
<div class="question_area">
<div id="question_1">

	<p class="question"><?php echo_string ('L1_THEME'); ?></p>
	<p><?php echo_string ('L1_QUESTION'); ?></p>
    <img src="images/t_top.gif" alt="Table Top" style="vertical-align: middle"/>	
    <table border="0" cellspacing="0" cellpadding="0" class="q_table">
		<tr class="q_line">
			<td><?php echo_string ('L1_1'); ?></td>
			<td class="radio"><input id="q1_1" name="q1" type="radio" value="1" /></td>
		</tr>
		<tr class="q_line">
			<td><?php echo_string ('L1_2'); ?></td>
			<td class="radio"><input id="q1_2" name="q1" type="radio" value="2" /></td>
		</tr>
		<tr class="q_line">
			<td><?php echo_string ('L1_3'); ?></td>
			<td class="radio"><input id="q1_3" name="q1" type="radio" value="3" /></td>
		</tr>
		<tr class="q_line">
			<td><?php echo_string ('L1_4'); ?></td>
			<td class="radio"><input id="q1_4" name="q1" type="radio" value="4" /></td>
		</tr>
		<tr class="q_line">
			<td><?php echo_string ('L1_5'); ?></td>
			<td class="radio"><input id="q1_5" name="q1" type="radio" value="5" /></td>
		</tr>
	</table>
</div>

<div id="question_2">
	<p class="question"><?php echo_string ('L2_THEME'); ?></p>
	<p><?php echo_string ('L2_QUESTION'); ?></p>
    <img src="images/t_top.gif" alt="Table Top" style="vertical-align: middle"/>	
    <table border="0" cellspacing="0" cellpadding="0" class="q_table">
		<tr class="q_line">
			<td><?php echo_string ('L2_1'); ?></td>
			<td class="radio"><input id="q2_1" name="q2" type="radio" value="1" /></td>
		</tr>
		<tr class="q_line">
			<td><?php echo_string ('L2_2'); ?></td>
			<td class="radio"><input id="q2_2" name="q2" type="radio" value="2" /></td>
		</tr>
		<tr class="q_line">
			<td><?php echo_string ('L2_3'); ?></td>
			<td class="radio"><input id="q2_3" name="q2" type="radio" value="3" /></td>
		</tr>
		<tr class="q_line">
			<td><?php echo_string ('L2_4'); ?></td>
			<td class="radio"><input id="q2_4" name="q2" type="radio" value="4" /></td>
		</tr>
		<tr class="q_line">
			<td><?php echo_string ('L2_5'); ?></td>
			<td class="radio"><input id="q2_5" name="q2" type="radio" value="5" /></td>
		</tr>
	</table>
</div>
                
<div id="question_3">
<p class="question"><?php echo_string ('L3_THEME'); ?></p>
<p><?php echo_string ('L3_QUESTION'); ?></p>
<img src="images/t_top.gif" alt="Table Top" style="vertical-align: middle"/>	
    <table border="0" cellspacing="0" cellpadding="0" class="q_table">
	<tr class="q_line_q3">
		<td>&bull; <?php echo_string ('L3_1'); ?></td>
		<td class="radio"><input id="chk1" name="chk1" type="checkbox" value="1" /></td>
	</tr>
	<tr class="q_line_q3">
		<td>&bull; <?php echo_string ('L3_2'); ?></td>
		<td class="radio"><input id="chk2" name="chk2" type="checkbox" value="1" /></td>
	</tr>
	<tr class="q_line_q3">
		<td>&bull; <?php echo_string ('L3_3'); ?></td>
		<td class="radio"><input id="chk3" name="chk3" type="checkbox" value="1" /></td>
	</tr>
	<tr class="q_line_q3">
		<td>&bull; <?php echo_string ('L3_4'); ?></td>
		<td class="radio"><input id="chk4" name="chk4" type="checkbox" value="1" /></td>
	</tr>
	<tr class="q_line_q3">
		<td>&bull; <?php echo_string ('L3_5'); ?></td>
		<td class="radio"><input id="chk5" name="chk5" type="checkbox" value="1" /></td>
	</tr>
	<tr class="q_line_q3">
		<td>&bull; <?php echo_string ('L3_6'); ?></td>
		<td class="radio"><input id="chk6" name="chk6" type="checkbox" value="1" /></td>
	</tr>
	<tr class="q_line_q3">
		<td>&bull; <?php echo_string ('L3_7'); ?></td>
		<td class="radio"><input id="chk7" name="chk7" type="checkbox" value="1" /></td>
	</tr>
	<tr class="q_line_q3">
		<td colspan="2"><?php echo_string ('L3_8'); ?>:<br />
		<textarea rows="2" name="q3_8" id="q3_8" cols="90"></textarea>
		<br/>
		<br/>
	</td>
	</tr>
</table>
</div>
<div id="question_4">
<p class="question"><?php echo_string ('L4_THEME'); ?></p>
<p><?php echo_string ('L4_QUESTION'); ?></p>
<img src="images/t_top.gif" alt="Table Top" style="vertical-align: middle"/>	
    <table border="0" cellspacing="12" cellpadding="0" class="q_table">
	<tr class="q_line">
		<td><?php echo_string ('L4_1'); ?></td>
		<td class="radio"><input id="q4_1" name="q4" type="radio" value="1" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L4_2'); ?></td>
		<td class="radio"><input id="q4_2" name="q4" type="radio" value="2" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L4_3'); ?></td>
		<td class="radio"><input id="q4_3" name="q4" type="radio" value="3" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L4_4'); ?></td>
		<td class="radio"><input id="q4_4" name="q4" type="radio" value="4" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L4_5'); ?></td>
		<td class="radio"><input id="q4_5" name="q4" type="radio" value="5" /></td>
	</tr>
</table>
</div>
<div id="question_5">
<p class="question"><?php echo_string ('L5_THEME'); ?></p>
<p><?php echo_string ('L5_QUESTION'); ?></p>
<img src="images/t_top.gif" alt="Table Top" style="vertical-align: middle"/>	
    <table border="0" cellspacing="12" cellpadding="0" class="q_table">
	<tr class="q_line">
		<td><?php echo_string ('L5_1'); ?></td>
		<td class="radio"><input id="q5_1" name="q5" type="radio" value="1" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L5_2'); ?></td>
		<td class="radio"><input id="q5_2" name="q5" type="radio" value="2" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L5_3'); ?></td>
		<td class="radio"><input id="q5_3" name="q5" type="radio" value="3" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L5_4'); ?></td>
		<td class="radio"><input id="q5_4" name="q5" type="radio" value="4" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L5_5'); ?></td>
		<td class="radio"><input id="q5_5" name="q5" type="radio" value="5" /></td>
	</tr>
</table>
</div>
<div id="question_6">
<p class="question"><?php echo_string ('L6_THEME'); ?></p>
<p><?php echo_string ('L6_QUESTION'); ?></p>
<img src="images/t_top.gif" alt="Table Top" style="vertical-align: middle"/>	
    <table border="0" cellspacing="12" cellpadding="0" class="q_table">
	<tr class="q_line">
		<td><?php echo_string ('L6_1'); ?></td>
		<td class="radio"><input id="q6_1" name="q6" type="radio" value="1" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L6_2'); ?></td>
		<td class="radio"><input id="q6_2" name="q6" type="radio" value="2" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L6_3'); ?></td>
		<td class="radio"><input id="q6_3" name="q6" type="radio" value="3" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L6_4'); ?></td>
		<td class="radio"><input id="q6_4" name="q6" type="radio" value="4" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L6_5'); ?></td>
		<td class="radio"><input id="q6_5" name="q6" type="radio" value="5" /></td>
	</tr>
</table>
</div>
<div id="question_7">
<p class="question"><?php echo_string ('L7_THEME'); ?></p>
<p><?php echo_string ('L7_QUESTION'); ?></p>
<img src="images/t_top.gif" alt="Table Top" style="vertical-align: middle"/>	
    <table border="0" cellspacing="12" cellpadding="0" class="q_table">
	<tr class="q_line">
		<td><?php echo_string ('L7_1'); ?></td>
		<td class="radio"><input id="q7_1" name="q7" type="radio" value="1" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L7_2'); ?></td>
		<td class="radio"><input id="q7_2" name="q7" type="radio" value="2" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L7_3'); ?></td>
		<td class="radio"><input id="q7_3" name="q7" type="radio" value="3" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L7_4'); ?></td>
		<td class="radio"><input id="q7_4" name="q7" type="radio" value="4" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L7_5'); ?></td>
		<td class="radio"><input id="q7_5" name="q7" type="radio" value="5" /></td>
	</tr>
</table>
</div>
<div id="question_8">
<p class="question"><?php echo_string ('L8_THEME'); ?></p>
<p><?php echo_string ('L8_QUESTION'); ?></p>
<img src="images/t_top.gif" alt="Table Top" style="vertical-align: middle"/>	
    <table border="0" cellspacing="12" cellpadding="0" class="q_table">
	<tr class="q_line">
		<td><?php echo_string ('L8_1'); ?></td>
		<td class="radio"><input id="q8_1" name="q8" type="radio" value="1" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L8_2'); ?></td>
		<td class="radio"><input id="q8_2" name="q8" type="radio" value="2" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L8_3'); ?></td>
		<td class="radio"><input id="q8_3" name="q8" type="radio" value="3" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L8_4'); ?></td>
		<td class="radio"><input id="q8_4" name="q8" type="radio" value="4" /></td>
	</tr>
	<tr class="q_line">
		<td><?php echo_string ('L8_5'); ?></td>
		<td class="radio"><input id="q8_5" name="q8" type="radio" value="5" /></td>
	</tr>
</table>
</div>

<input type="image" id="buttprev" value="Prev" style="visibility: hidden;" src="images/back_btn.gif" />
<input type="image" id="buttnext" style="visibility: hidden;" value="Next" src="images/next_btn.gif"/>
<input type="image" value="Submit" id="submitbutt" src="images/submit.gif" />
<?php if ($mode == 'edit') {?>
            	<input type="hidden" id="advisorupdate" name="advisorupdate" value="1" />
            	<input type="hidden" id="attemptid" name="attemptid" value="<?php echo getAttemptId ($userid, $sitting, $assesstype); ?>" />
<?php }?>
            
</div>
</form>

	<?php if (false && has_capability (Sessions::getID (), 'activity:view_revisions')) { ?>
<div>Revisions</div>
        <?php
$revisions = DB::executeContainedSelect ('activity_revisions', '*', array ('attemptid' =>  getAttemptId ($userid, $sitting, $assesstype) ), 'date desc');
       
        
        $dg = new DataGrid ($revisions, 'RevisionID');
$dg->setTableID ('revisionstable');
$dg->setTableClass ();

$dg->addAttr('th', 'align', 'left');

$dg->render ();

        
        ?>
        <?php } ?>
        
<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>
