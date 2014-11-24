<?php
include_once ('./config.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo ($CFG->appname); ?></title>
<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->printcssfile); ?>" rel="stylesheet" type="text/css" media="print"/>
<script language="javascript">
var formAction = "";

function setAction (loc) {
	formAction = loc;
}
function getAction (form) {
	if (formAction == "learner") {
		loc = "activity_learner.php";
	} else {
		loc = "activity_advisor.php";
	}
	form.action = loc;
}
</script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="js/resizer.js"></script>
</head>
<body>
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>

<h2>Pre-Activity Screen</h2>
Which of the following intervention activities have you completed since the last sitting?

<form id="form1" name="form1" method="post" action="" onsubmit="getAction(this); return true;">
<?php 

 	$interventions = DB::executeSelect('intervention_types');
 	foreach ($interventions as $intervention) {
 ?>
 	<!-- OLD STYLEEE <label><?php echo $intervention['name']; ?><input name="i_<?php echo $intervention['TypeID']; ?>" type="checkbox" value="1" /></label> -->
 	<label><?php echo $intervention['name']; ?><input name="interventions[]" type="checkbox" value="<?php echo $intervention['TypeID']; ?>" /></label>
 <?php     			
 	}
 ?> 
 <br/>
 <br/>
 Which activity would you like to sit?
 <input type="submit" name="activity" value="Learner" onclick="setAction('learner');"/>
 <input type="submit" name="activity" value="Advisor" onclick="setAction('advisor');"/>
  </form>


<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>
