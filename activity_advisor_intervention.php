<?php


include_once ('./config.php');
include_once ($CFG->apploc . '/db_connect.php');
include_once ($CFG->apploc . '/classes/db.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
Sessions::checkUserLogIn ();
//
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('general', 'interventions'));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo return_string ('APP_NAME') . ': ' . return_string ('IV'); ?></title>

<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="-1" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="js/resizer.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	if ($("#otheriv").is(':checked') == false) {
  		$("#otheriv_text").hide();
  		$('#otheriv_text').attr('disabled','disabled');
	}

	$('#otheriv').change(function() {
		  if ($(this).is(':checked')) {
			$("#otheriv_text").show();
			$('#otheriv_text').removeAttr('disabled');
		  } else {
		  	$("#otheriv_text").hide();
		  	$('#otheriv_text').attr('disabled','disabled');
		  } 
	});	

	jQuery.fn.extend({
		checkForm: function () {
			// check for an empty value in the other field if the checkbox is checked
			if ($('#otheriv').is(':checked')) {
				// validate entry in other textbox (ie is it empty?)
				if ($('#otheriv_text').val () != "") {
					return true;
				} else {
					alert ('Please detail an "Other" intervention to proceed.');
					return false;
				}
			}
	    return true;
	    }
	});
});
</script>
</head>
<body>
<?php include_once ($CFG->apploc . '/templates/header.php'); ?>

<?php
// check how many sittings user has done
$userid = !empty ($_GET['userid']) ? $_GET['userid'] :  false;
$editing = !empty ($_GET['edit']) ? $_GET['edit'] : false;

if ($editing) {
	// advisor is editing...
	// 
	// get sitting and userid from $_GET?
	// get interventions too and display these on-screen
	$ivs = DB::executeContainedSelect ('user_interventions', '*', array ('userid'=>$userid, 'sitting'=>$_GET['sitting']));
	$res = DB::runSelectQuery ('select CONCAT(sb_users_info.fname, " ", sb_users_info.sname) as fullname from sb_users_info where userid=' . $userid . ';');
	$res['sittings'] = $_GET['sitting'];
} else {
	$res = DB::runSelectQuery('select count(*) as sittings,
							   (select CONCAT(sb_users_info.fname, " ", sb_users_info.sname) from sb_users_info where userid=' . $userid . ') as fullname
							   from sb_users_attempt where userid=' . $userid  . ' and assessmenttype="a"', false);
	
	$learnersittings = DB::runSelectQuery('select count(*) as sittings from sb_users_attempt where userid=' . $userid  . ' and assessmenttype="l"', false);
	
	if ($res) {
		if ($res['sittings'] >= 3) {
			die (sprint_string ('A_ALL_COMPLETE', $res['fullname']));
		} else if ($res['sittings'] >= $learnersittings['sittings']) {
			die ("You may not complete your next advisor activity until the learner has completed theirs.");
		} else {
			$res['sittings']++;
		}
	} else {
		die ("Error: unable to determine how many sittings user has undertaken or no user specified.");
	}
}

?>

<div class="question_area"><img src="images/t_top.gif" alt="Table Top" style="vertical-align: middle" />
	
	<form id="form1" name="form1" method="post" action="activity_advisor.php" onsubmit="return $(this).checkForm()">
	<input type="hidden" value="<?php echo $userid; ?>" name="userid" id="userid" />
	<?php if ($editing) { ?>
		<input type="hidden" value="1" name="edit" id="edit" />
	<?php } ?>
		<input type="hidden" value="<?php echo $res['sittings']; ?>" name="sittings" id="sittings" />
	
	<table class="q_table" cellpadding="0" cellspacing="12" border="0">
	<tr>
		<td>
		<h2><?php echo_string ('IV'); ?></h2>
		
		<div class="annot">
<p>Please select any interventions, if any, which the learner has undertaken since the last sitting.</p>
</div>

     <div id="a"> 
        
		<?php echo sprint_string ('IV_SINCE_LAST', $res['fullname']); ?> <br />
	</div>
		<br />
		</td>
		</tr>
			<?php
			// get interventions (except for the one with id 100 which is 'other')
			$interventions = DB::executeSelect('intervention_types', '*', 'Typeid != 1000');
			foreach ($interventions as $intervention) {
			?>
				<tr>
					<td ><label style="vertical-align:top;" for="interventions"><?php echo $intervention['name']; ?>
					<?php 
					if ($intervention['TypeID'] == 100) { ?>
					
					<textarea style="margin-left: 20px;" name="otheriv_text" id="otheriv_text" rows="4" cols="90" placeholder="Please enter intervention"><?php 
					// display text in 'other' text box if it exists in the db
					if ($editing) {
						if (!empty ($ivs)) {

							foreach ($ivs as $currentiv) {

								if ($currentiv['typeid'] == $intervention['TypeID']) {

									echo $currentiv['other'];

								}

							}
						}
					}
					?></textarea>
					<!-- 
					<input type="text" style="width: 500px; margin-left: 20px;" id="otheriv_text" name="otheriv_text" placeholder="Please enter intervention" <?php 
					// display text in 'other' text box if it exists in the db
					if ($editing) {
						if (!empty ($ivs)) {

							foreach ($ivs as $currentiv) {

								if ($currentiv['typeid'] == $intervention['TypeID']) {

									echo 'value="' . $currentiv['other'] .'"';

								}

							}
						}
					}
					?>/>
					
					-->
					<?php } ?>
					</label></td>
					<td><input name="interventions[]" type="checkbox" <?php
						if ($editing) {
							// is checkbox already selected?
							// do we have any interventions?
							if (!empty ($ivs)) {
								foreach ($ivs as $currentiv) {
									if ($currentiv['typeid'] == $intervention['TypeID']) {
										echo ' checked="checked"';
									}
								}
							}
						}
						
						// check for other (id of 100)
						if ($intervention['TypeID'] == 100) {
							echo ' id="otheriv"';
						}
						
					?> value="<?php echo $intervention['TypeID']; ?>" /></td>
				</tr>
			<?php
			}
			?>
			<tr>
				<td></td>
			</tr>
			<tr>
				<td><input type="submit" name="activity" value="<?php echo_string ('IV_PROCEED'); ?>" /></td>
			</tr>
</table>


		</form>
		</div>

			<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>
