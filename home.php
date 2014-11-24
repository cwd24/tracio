<?php

include_once ('config.php');
include_once ($CFG->apploc  . '/lib/activity.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/classes/datagrid.php');
include_once ($CFG->apploc  . '/lib/roles.php');

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
<script type="text/javascript" src="external/tablesorter/jquery.tablesorter.min.js"></script>

<link href="external/tablesorter/themes/blue/style.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
  $(document).ready(function(){
	  
	  $('#learners').tablesorter();

  });
</script>

</head>
<body>
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<div id="homepage">
    <br/>
    <div id="loggedin">
        <h2><?php echo_string ('WELCOME'); ?> <?php echo stripslashes (getUserName (Sessions::getID())); ?></h2>
    
    <?php 
    // check if institution has any centres before displaying the change of centre dialog
    if (DB::executeSelect ('centres', '*', array ('providerid'=>Sessions::getUserInfo ('providerid')))) {
	    if (Sessions::getUserInfo('centreid') == 0 && Sessions::getUserInfo('roleid') == $CFG->learnerroleid) {?>
	    	<div style="margin-bottom: 20px;" class="alert warning">Note: your centre is not selected, please <a href="profile.php">click here</a> to set it.
	    	This message will continue to appear until you select a centre.</div>
	    	<div style="clear:both;"></div>
    <?php }
    }?>
        
        
       <?php 
       if (has_capability(Sessions::getID(), 'activity:sit_learner')) {
// learner screen...
       ?>
<?php 
$res = DB::executeSelect('users_info', 'dob', array ('UserID'=>Sessions::getID()));
if (!$res['dob']) {
?>
<div style="margin-bottom: 20px;" class="alert warning">Note: your date of birth is not set, please <a href="profile.php">click here</a> to set it.
	    	This message will continue to appear until you set it.</div>
	    	<div style="clear:both;"></div>
<?php } ?>

<div class="annot">
<p>Below you can see which TRaCIO activities you have undertaken so far.</p>
</div>
<div class="subannot_first"></div>
<div class="subannot">
<p></p>
<p>To sit an activity click on the available link below (e.g. 'Commencement').</p>
</div>
        <div id="a">
            <fieldset>
                <legend><?php echo_string ('L_ACTIVITY'); ?></legend>
                <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <img src="images/user.gif" alt="user" />
                        </td>
                        <td>
                       
                        <?php // for loop thru three sittings
                        	$nextflag = 1;
                        	$statuses = array ("", "off", "off", "off");
                        	for ($i=1; $i<=3; $i++) {
                        		if (userAlreadySat (Sessions::getID (), $i, 'l')) {
                        			$statuses[$i] = "done";
                        			$nextflag = $i;
                        		} else {
                        			$statuses[$i] = "on";
                        			break;
                        		}
                        	}
                        	
                        ?>
                            <div><?php if ($statuses[1] == "on") {?><a href="activity_learner.php?sitting=1"><?php echo_string ('COMM'); } else { echo_string ('COMM'); } ?><img src="images/box_<?php echo $statuses[1]; ?>.gif" style="vertical-align: middle" alt="Done" /><?php if ($statuses[1] == "on") {?></a><?php } ?></div>  
                             <div><?php if ($statuses[2] == "on") {?><a href="activity_learner.php?sitting=2"><?php echo_string ('MID'); } else { echo_string ('MID'); } ?><img src="images/box_<?php echo $statuses[2]; ?>.gif" style="vertical-align: middle" alt="Done" /><?php if ($statuses[2] == "on") {?></a><?php } ?></div>
                             <div><?php if ($statuses[3] == "on") {?><a href="activity_learner.php?sitting=3"><?php echo_string ('COMP'); } else { echo_string ('COMP'); } ?> <img src="images/box_<?php echo $statuses[3]; ?>.gif" style="vertical-align: middle" alt="Done" /><?php if ($statuses[3] == "on") {?></a><?php } ?></div>
                            <br/>
                            
                        </td>
                    </tr>
                </table>
                
                <div class="subannot_two"></div>
<div class="subannot">
<p></p>
<p>To view your results from previous sittings, <a href="dashboard.php">click here</a> or select the 'My Results' button in the top menu.</p>
</div>
            </fieldset>
            <br />
        </div>
       <?php } ?>
       
         <?php 
      if (has_capability(Sessions::getID(), 'activity:sit_advisor')) {
      	?>
      	<div class="annot">
<p>The table below shows your selected learners along with the number of activities undertaken.</p>
</div>
<div class="subannot_first"></div>
<div class="subannot">
<p>Click on the 'View' link in the 'Results' column to view reports for that learner.</p>
<p>To sit an advisor activity for a learner, click on the 'Sit Next Activity' button.</p>
</div>
       <div id="a">
            <fieldset>
                <legend><?php echo_string ('YOUR_LEARNERS'); ?></legend>
               

     
                            
                       
<?php 
  

	// query based on assigned learners
	$blah = DB::runSelectQuery ('SELECT
	sb_users_info.fname AS Firstname, 
	sb_users_info.sname AS Surname, 
	sb_users_info.UserID,
(select count(*) from sb_users_attempt where sb_users_attempt.userid = sb_users_info.UserID and sb_users_attempt.assessmenttype="a") as Sittings,
(select count(*) from sb_users_attempt where sb_users_attempt.userid = sb_users_info.UserID and sb_users_attempt.assessmenttype="l") as LearnerSittings
FROM sb_users_learner_assignment INNER JOIN sb_users_info ON sb_users_learner_assignment.learnerid = sb_users_info.UserID
WHERE sb_users_learner_assignment.advisorid=' . Sessions::getID () . ' and archived=0 and sb_users_info.roleid=' . $CFG->learnerroleid . '
order by Surname, Firstname, Sittings, LearnerSittings', true);

	


	$dg = new DataGrid ($blah, 'UserID');
	$dg->addAttr('table', 'id', 'learners');
	$dg->addAttr('table', 'class', 'tablesorter');
	$dg->addAttr('table', 'width', '100%');
	$dg->removeDisplayField('UserID');
	$dg->addFieldTitle('Sittings', 'Advisor Sittings');
	$dg->addFieldTitle('LearnerSittings', 'Learner Sittings');
    if  (has_capability(Sessions::getID(), 'users:edit_profile')) {
		$dg->addHTMLCol('<a href="user_edit.php?userid=%s">Edit</a>', 'Profile');
	}
	$dg->addHTMLCol('<a href="dashboard.php?uid=%s">View</a>', 'Results');
	$dg->addConditionalField ('UserID', '<a href="activity_advisor_intervention.php?userid=%s">Sit Next Activity</a>', 'Sit Advisor Activity', 'Sittings', '< 3', 'All Complete');
   
	$dg->render();

?>

<div class="subannot_two"></div>
<div class="subannot">
<p>To add, remove or manage your learners <a href="admin/advisor.php">use the Advisor admin</a> screen.</p>
<p></p>
</div>

</fieldset>
            <br />
            
        </div>
        
        <?php } ?>
        <!--   <div id="la">
            <fieldset>
                <legend><div style="font-weight: bold;"><?php echo_string ('A_ACTIVITY'); ?></div></legend>
                <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <img src="images/user.gif" alt="users" />
                        </td>
                         <?php // for loop thru three sittings
                        	$nextflag = 1;
                        	$statuses = array ("", "off", "off", "off");
                        	for ($i=1; $i<=3; $i++) {
                        		if (userAlreadySat (Sessions::getID (), $i, 'a')) {
                        			$statuses[$i] = "done";
                        			$nextflag = $i;
                        		} else {
                        			$statuses[$i] = "on";
                        			break;
                        		}
                        	}
                        	
                        ?>
                        <td>
                        
                         <div><?php if ($statuses[1] == "on") {?><a href="activity_advisor_intervention.php?sitting=1"><?php echo_string ('COMM'); ?><?php } else { echo_string ('COMM'); } ?><img src="images/box_<?php echo $statuses[1]; ?>.gif" style="vertical-align: middle" alt="Done" /><?php if ($statuses[1] == "on") {?></a><?php } ?></div>  
                         <div><?php if ($statuses[2] == "on") {?><a href="activity_advisor_intervention.php?sitting=2"><?php echo_string ('MID'); ?><?php } else { echo_string ('MID'); } ?><img src="images/box_<?php echo $statuses[2]; ?>.gif" style="vertical-align: middle" alt="Done" /><?php if ($statuses[2] == "on") {?></a><?php } ?></div>
                         <div><?php if ($statuses[3] == "on") {?><a href="activity_advisor_intervention.php?sitting=3"><?php echo_string ('COMP'); ?></a><?php } else { echo_string ('COMP'); } ?><img src="images/box_<?php echo $statuses[3]; ?>.gif" style="vertical-align: middle" alt="Done" /><?php if ($statuses[3] == "on") {?></a><?php } ?></div>
                         <br/>
                            
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>-->
        <?php //} ?>
    </div>
   
   <?php
   $role = (Sessions::getUserInfo ('roleid')) ;
   
   // if user is an admin user....
   // update 2012-12-18 changed check here to non-advisor and non-learner.
   if ($role !=  $CFG->advisorroleid  &&  $role !=  $CFG->learnerroleid) {
   ?>
   <?php echo_string ('ADMIN_HOME_TEXT'); ?>
   <?php } ?>
      <br/>

</div>
<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>

</body>
</html>
    
    
    
