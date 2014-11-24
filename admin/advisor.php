<?php

include_once ('../config.php');
include_once ($CFG->apploc  . '/lib/activity.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/classes/datagrid.php');
include_once ($CFG->apploc  . '/lib/roles.php');
include_once ($CFG->apploc  . '/classes/filtermanager.php');
include_once ($CFG->apploc  . '/classes/filterfield.php');

Sessions::checkUserLogIn ();



//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('general', 'question'));

$filtersMgr = new FilterManager ('POST');

if (!has_capability(Sessions::getID(), 'admin:advisor_page')) {
	die (return_string ('ACCESS_DENIED'));
}


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
<script src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="../js/resizer.js"></script>
<script type="text/javascript" src="../external/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../external/tablesorter/addons/pager/jquery.tablesorter.pager.js"></script>

<link href="../external/tablesorter/themes/blue/style.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
  $(document).ready(function(){
	  
	  $('#learners').tablesorter()
	  .tablesorterPager({container: $("#pager"), size: <?php echo $CFG->defaultPaginationSize; ?>});

	  	// should probably implement it like this instead?
	 // $('.tablesorter').tablesorter();

	  <?php 
	  // if first load, check the 'Hide Archived' checkbox and reload screen
	  if (empty ($_POST)) {
	  ?>
		$('#archived').attr('checked', 'checked');
		$('#form1').submit ();
	  <?php 
		} 
    	?>
		
  });
</script>

</head>
<body class="advadmin">
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<div id="homepage">

<h2>All Learners for your Organisation</h2>

<?php
if (!empty ($_POST)) {
	// got any users?
	if (!empty ($_POST['users'])) {
	switch ($_POST['action']) {
		case "Assign" :			
					$res = assignLearnersToAdvisor ($_POST['users'], Sessions::getID());
					
					if ($res) {
						?>
						<div class="alert sallgood"><?php echo 'Selected users have been added to you.'; ?></div><div style="clear: both;"></div>
					<?php 
					} else {
					?>
						<div class="alert warning"><?php echo 'Unable to add one or more selected users.'; ?></div><div style="clear: both;"></div>
					<?php
					}
			
			break;
		case "Unassign" :
			
				$res = revokeLearnersFromAdvisor ($_POST['users'], Sessions::getID());
					
				if ($res) {
					?>
									<div class="alert sallgood"><?php echo 'Selected users have been removed from you.'; ?></div><div style="clear: both;"></div>
								<?php 
								} else {
								?>
									<div class="alert warning"><?php echo 'Unable to remove one or more selected users.'; ?></div><div style="clear: both;"></div>
								<?php
								}
						
						break;
		case "Archive" :
			
			$res = archiveLearners ($_POST['users']);
			
			if ($res) {?>
					<div class="alert sallgood"><?php echo 'Selected users have been archived.'; ?></div><div style="clear: both;"></div>
								<?php 
								} else {
								?>
									<div class="alert warning"><?php echo 'Unable to archive one or more users.'; ?></div><div style="clear: both;"></div>
								<?php
								}
								
						
			break;
			
		case "Unarchive" :
					
				$res = unarchiveLearners ($_POST['users']);
					
						if ($res) {?>
								<div class="alert sallgood"><?php echo 'Selected users have been retrieved from the archive.'; ?></div><div style="clear: both;"></div>
											<?php 
											} else {
											?>
												<div class="alert warning"><?php echo 'Unable to retrieve one or more users from the archive.'; ?></div><div style="clear: both;"></div>
											<?php
											}
											
									
						break;
	}
			
}
}

?>
<div class="annot">
<p>Use this screen to control your learners from the full list available to your provider.</p>
<p>On this screen you can:</p>
<ul>
<li>Assign/unassign users to appear on your 'Your Learners' screen.</li>
</ul>
</div>
<div class="subannot_first"></div>
<div class="subannot">
<p>Learners visible on your 'Your Learners' list will appear with a <img style="vertical-align: middle" src="../images/hide.gif"/> icon.</p><p>Learners with a <img style="vertical-align: middle" src="../images/show.gif"/> icon will not appear on your list of learners.</p>
<p><em>To make any edits, simply select/unselect the checkbox alongside your learner and select the required action below.</em></p>
</div>



<?php $currentcentre = Sessions::getUserInfo('centreid'); ?>

<form id="form1" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<div class="ff">
<label for="ctr"><?php echo_string ('CENTRE'); ?>:</label>
<select name="ctr" id="ctr" <?php if ($currentcentre && false) {  ?>disabled="disabled"<?php } ?>>                              
<option value="0">
                           
                                      <?php echo_string ('COMBO_ALL'); ?>
                                    </option>
                                    <?php 
                                       
                                        $ctrs = DB::executeSelect('centres', '*', array ('providerid'=>Sessions::getUserInfo('providerid')));
                                                                      
										$selected = $_POST ? $_POST['ctr'] : $currentcentre;
                                        foreach ($ctrs as $ctr) {
                                    ?>
                                    <option value="<?php echo $ctr['CentreID']; ?>" <?php if ($ctr['CentreID'] == $selected) {?>selected="selected"<?php } ?>>
                                        <?php print $ctr['name']; ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
                                </div>
                                
                              

                                <div class="ff user-details"><?php
$filter = new FilterField ('firstname', 'firstname', 'text', 'Firstname');
$filter->render ();
$filtersMgr->addFilterField ($filter);
?> <?php
$filter = new FilterField ('surname', 'surname', 'text', 'Surname');
$filter->render ();
$filtersMgr->addFilterField ($filter);
?>
</div>
<div class="ff">
<?php
$filter = new FilterField ('archived', 'archived', 'checkbox', 'Hide Archived Users', false);
$filter->setSearchType('not');
$filter->bindData(array ("value"=>1, "checked"=>true));
$filter->render ();
$filtersMgr->addFilterField ($filter);
?>
</div>
<div class="ff">
<?php
$filter = new FilterField ('enabled', 'enabled', 'dropdown', 'Show Assigned Users Only', false);
$filter->setSearchType('exact');

$filter->render ();
$filtersMgr->addFilterField ($filter);
?>
</div>

                                <!--  <input type="text" name="txtSearch" value ="" />  -->
                                <input type="hidden" name="action" id="action" value="filter_by_centre" />
                                <input type="submit" value="Filter" />
                             
                                
                                   
                                   
<?php 


//filtering the learners to make it easier to find individuals!
$additionalfilter = '';

// check if we are applying any filtering
if (isset ($_POST) || $currentcentre) {
	// are we filtering and is the selected centre not ANY (0)
	
	// update 2012-01-07 next line removed as it wasn't playing nice with the new filtering.
	//if ($_POST['action'] == 'filter_by_centre' && $_POST['ctr']){
	if ($_POST['ctr']) {
		$additionalfilter = ' and sb_users_info.centreid = ' . $_POST['ctr'];
	} else if ($currentcentre === 0) {
		// disabled following because of centre/subcontractor changes in code.
		$additionalfilter = ' and sb_users_info.centreid = ' . $currentcentre;
	}
	// more filters here
}


$origquery = 'SELECT sb_users_info.UserID, sb_users_info.archived, 
	sb_users_info.fname AS Firstname, 
	sb_users_info.sname AS Surname,
	DATE_FORMAT (sb_users_info.dob, \'%d/%m/%Y\') as DOB,
	sb_users_learner_assignment.enabled
FROM sb_users_info LEFT JOIN sb_users_learner_assignment ON sb_users_learner_assignment.advisorid = ' . Sessions::getID () . ' and sb_users_info.UserID = sb_users_learner_assignment.learnerid
WHERE sb_users_info.providerid = ' . Sessions::getUserInfo('providerid') . ' AND sb_users_info.roleid = ' .   $CFG->learnerroleid . $additionalfilter . '
ORDER BY Surname ASC, FirstName ASC';



$finalquery = $filtersMgr->generateQuery($origquery);



$blah = DB::runSelectQuery ($finalquery, true);






	$dg = new DataGrid ($blah, 'UserID');
	$dg->addAttr('table', 'id', 'learners');
	$dg->addAttr('table', 'class', 'tablesorter');
	$dg->addAttr('table', 'width', '100%');
	//
	$dg->addAttr('td', 'class', 'blah');
	
	
	$dg->removeDisplayFields(array ('UserID', 'enabled', 'archived'));
	$dg->addConditionalField ('UserID', '<center><img title="Learner is assigned to you" alt="Learner is assigned to you" src="../images/hide.gif" /></center>', 'Learner Assigned', 'enabled', '= 1', '<center><img title="Learner is not assigned to you" alt="Learner is not assigned to you" src="../images/show.gif" /></center>');
	$dg->addConditionalField ('UserID', '<center><img title= "Learner is archived" alt="Learner is Archived" src="../images/lock.gif" /></center>', 'Archive Status', 'archived', '= 1', '<center><img title="Learner is live" alt="Learner is live" src="../images/unlock.png" /></center>');
	$dg->setFieldTitle('DOB', 'Date of Birth');
	$dg->addHTMLCol('<center><input name="users[]" value="%s" type="checkbox" /></center>', 'Select');
	$dg->render ();
    
	
	
?>
<input type="Submit" name="action" id="remove" value="Unassign" title="Remove user(s) from my advisors list" />
<input type="Submit" name="action" id="assign" value="Assign" title="Assign this user(s) to my advisors list" />
<?php if (has_capability(Sessions::getID(), 'users:archive_user')) {?>
<input type="Submit" name="action" id="archive" value="Archive" title="Archive the user(s)" />
<input type="Submit" name="action" id="unarchive" value="Unarchive" title="Retrieve user(s) from the archive" />
<?php } ?>
</form>
</div>
</body>
</html>


