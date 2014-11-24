<?php
// 
include_once ('../config.php');
include_once ($CFG->apploc . '/db_connect.php');
include_once ($CFG->apploc . '/classes/db.php');
include_once ($CFG->apploc . '/lib/roles.php');
include_once ($CFG->apploc . '/classes/datagrid.php');
include_once ($CFG->apploc . '/lib/funcs.php');
include_once ($CFG->apploc . '/lib/stats.php');
include_once ($CFG->apploc . '/classes/sessions.php');
include_once ($CFG->apploc  . '/classes/filtermanager.php');
include_once ($CFG->apploc  . '/classes/filterfield.php');

Sessions::checkUserLogIn ();

//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('question', 'general', 'user'));

if (!has_capability(Sessions::getID(), 'admin:reset_user_passwords')) {
	die (return_string ('ACCESS_DENIED'));
}

$filtersMgr = new FilterManager ('POST');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php if (Sessions::getUserInfo('roleid') == $CFG->advisorroleid) { ?>Reset Passwords<?php } else { ?>User Admin<?php } ?></title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js" type="text/javascript"></script>

<script type="text/javascript" src="../external/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../external/tablesorter/addons/pager/jquery.tablesorter.pager.js"></script>
<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->printcssfile); ?>" rel="stylesheet" type="text/css" media="print"/>
<link href="admin.css" rel="stylesheet" type="text/css" />

<link href="../external/tablesorter/themes/blue/style.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
$(document).ready(function(){
	$("#centrestable")
		.tablesorter()
		.tablesorterPager({container: $("#pager"), size: <?php echo $CFG->defaultPaginationSize; ?>});
	$("#advisorstable")
		.tablesorter()
		.tablesorterPager({container: $("#pager"), size: <?php echo $CFG->defaultPaginationSize; ?>});

<?php 
// if first load, check the 'Hide Archived' checkbox and reload screen for filtering to have effect
if (empty ($_POST)) { ?>
	$('#archived').attr('checked', 'checked');
	$('#form2').submit ();
<?php } ?>
		    	
});
</script>

</head>
<body class="password_reset">
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<h2><?php if (Sessions::getUserInfo('roleid') == $CFG->advisorroleid) { ?>Reset Passwords<?php } else { ?>User Admin<?php } ?></h2>
<?php 
// find out what users provider is

// if user is super_admin, bring up last provider selection to make it easier
if (has_capability(Sessions::getID(), 'providers:control_all')) {
    if (Sessions::getLastProviderWorkedOn ()) {
		$myprovider = Sessions::getLastProviderWorkedOn ();
	} else {
		$myprovider = 0;
	}
} else {
	$myprovider = Sessions::getUserInfo('providerid');
}

$myrole = Sessions::getUserInfo('roleid');

if (!empty ($_POST['provider'])) {
	if (has_capability(Sessions::getID(), 'providers:control_all') || has_capability(Sessions::getID(), 'providers:control_subcontractors')) {
		if (has_access_to_provider (Sessions::getID (), $_POST['provider'])) {
			$myprovider = $_POST['provider'];
			Sessions::setLastProviderWorkedOn ($_POST['provider']);
		}
	}
}

$roles = getRolesBelow ($myrole);

$hashedpword = md5 ($CFG->defaultpassword);
		
if (!empty ($_POST)) {
	// got any users?
	if (!empty ($_POST['users'])) {
		// process action...!
		
		switch ($_POST['action']) {
			case "Complete Archiving" :
				
				if (has_access_to_users (Sessions::getID(), $_POST['users']) && has_capability(Sessions::getID(), 'users:archive_user')) {
					//run thru all archivees and archive them
					$successflag = true;
					foreach ($_POST['users'] as $userid) {
						$res = archiveLearner ($userid, $_POST['reasons_' . $userid]);
						// if failed, set flag to false to report to user
						if (!$res) {
							$successflag = false;
						}
					}
					
				
					if ($successflag) {?>
							<div class="alert sallgood"><?php echo 'Selected users have been archived.'; ?></div><div style="clear: both;"></div>
										<?php 
					} else {
										?>
											<div class="alert warning"><?php echo 'Unable to archive one or more users.'; ?></div><div style="clear: both;"></div>
										<?php
					}
										
				}
				break;
			case "Archive" :
				if (has_access_to_users (Sessions::getID(), $_POST['users']) && has_capability(Sessions::getID(), 'users:archive_user')) {
					// get usernames for display below
					$usernames = array ();
					foreach ($_POST['users'] as $userid) {
						$usernames [] = DB::executeSelect('users_info', array ('fname', 'sname', 'userid'), array ('userid'=>$userid));
					}
					
				}
				break;
				
			case "_Archive" :
				
					if (has_access_to_users (Sessions::getID(), $_POST['users']) && has_capability(Sessions::getID(), 'users:archive_user')) {
						$res = archiveLearners ($_POST['users']);
				
									if ($res) {?>
											<div class="alert sallgood"><?php echo 'Selected users have been archived.'; ?></div><div style="clear: both;"></div>
														<?php 
									} else {
														?>
															<div class="alert warning"><?php echo 'Unable to archive one or more users.'; ?></div><div style="clear: both;"></div>
														<?php
									}
														
								}
								break;
								
			case "Unarchive" :
				
				if (has_access_to_users (Sessions::getID(), $_POST['users']) && has_capability(Sessions::getID(), 'users:archive_user')) {
						$res = unarchiveLearners ($_POST['users']);
							
						if ($res) {?>
										<div class="alert sallgood"><?php echo 'Selected users have been retrieved from the archive.'; ?></div><div style="clear: both;"></div>
													<?php 
						} else {
													?>
														<div class="alert warning"><?php echo 'Unable to retrieve one or more users from the archive.'; ?></div><div style="clear: both;"></div>
													<?php
						}
													
				}	
						break;
						
			case "Reset Password" :
				$hashedpword = md5 ($CFG->defaultpassword);
				foreach ($_POST['users'] as $user) {
					// check if logged in user has access to change passwords of selected...
					if (has_access_to_user (Sessions::getID(), $user) && has_capability(Sessions::getID(), 'user:change_password')) {
						$reset = DB::executeUpdate('users_info', array ('password'=>$hashedpword), array ('UserID'=>$user), 1);
						$username = DB::executeSelect('users_info', 'loginid', array ('UserID'=>$user));
				
						if ($reset) {
							?>
									<div class="alert sallgood"><?php echo 'Password reset for user "' .  $username['loginid'] . '" (id #' . $user . ')'; ?></div><div style="clear: both;"></div>
								<?php 
								} else {
								?>
									<div class="alert warning"><?php echo 'Password not changed for "' .  $username['loginid'] . '" (id #' . $user . ')';  ?></div><div style="clear: both;"></div>
								<?php
								}
							} else {
								echo "Access denied.";
							} 
						}
				break;
				
		case "Delete" :
	
			foreach ($_POST['users'] as $user) {
				if (has_capability(Sessions::getID(), 'users:delete_user') && has_access_to_user (Sessions::getID (), $user )) {
					// do deletion
						$deleteq = DB::executeDelete('users_info', array('UserID'=>$user), 1);
						if ($deleteq) {
							$displayform = false;
							?><div class="alert sallgood">User Deleted.</div><div style="clear: both;"></div>
							<?php 							
						} else {
							?>
						<div class="alert warning">There was an error removing user <?php echo $user; ?>. Please contact admin.</div><div style="clear: both;"></div>
						<?php 
						}
				} else {
					die ('Access failed.');
				}
			}
			break;
			
		case "Disable Emails" :
			
			foreach ($_POST['users'] as $user) {
				if (has_capability(Sessions::getID(), 'profile:set_email_notifications') && has_access_to_user (Sessions::getID (), $user )) {
					// do deletion
					$emailq = DB::executeUpdate('users_info', array('enableemails'=>0), array('UserID'=>$user), 1);
					echo mysql_error ();
					if ($emailq) {
						$displayform = false;
					?><div class="alert sallgood">Email disabled for user <?php echo $user; ?>.</div><div style="clear: both;"></div>
					<?php 							
					} else {
					?>
					<div class="alert warning">There was an error disabling email for user <?php echo $user; ?>. Please contact admin.</div><div style="clear: both;"></div>
					<?php 
					}
				} else {
					die ('Access failed.');
				}
			}
			
			break;
	
		case "Enable Emails" :
			foreach ($_POST['users'] as $user) {
				if (has_capability(Sessions::getID(), 'profile:set_email_notifications') && has_access_to_user (Sessions::getID (), $user )) {
					// do deletion
					$emailq = DB::executeUpdate('users_info', array('enableemails'=>1), array('UserID'=>$user), 1);
					if ($emailq) {
						$displayform = false;
						?><div class="alert sallgood">Email enabled for user <?php echo $user; ?>.</div><div style="clear: both;"></div>
								<?php 							
								} else {
								?>
								<div class="alert warning">There was an error enabling email for user <?php echo $user; ?>. Please contact admin.</div><div style="clear: both;"></div>
								<?php 
								}
							} else {
								die ('Access failed.');
							}
						}
			break;
				
			}
	

		
	
	}
}
?>
<?php if (Sessions::getUserInfo('roleid') == $CFG->advisorroleid) { ?>
	<div class="annot">
	<p>Use this screen to reset passwords for users in your organisation.
	</p>
	</div>
	<div class="subannot_first"></div>
	<div class="subannot">
	<p>Note: you may only change passwords for users below your current role. For example:</p><ul><li>an advisor may only reset passwords for learners,</li><li>a provider admin may reset passwords for advisors and learners</li></ul><p></p>
	</div>
	
	<div id="advisors" class="section" style="width: 100%;">
	<div class="sectionheader">Users</div>
	<div class="sectioncontent">
	
	<div class="subsection">
	<h3>All Users</h3>
	<div class="subannot_two"></div>
	<div class="subannot">
	<p>To reset other users' passwords, select the relevant checkboxes in the 'Select' column and click on the 'Reset Password(s)' button below.</p>
	<p>Archived learners will appear with a <img style="vertical-align: middle" src="../images/lock.gif"/> icon. Live learners will display a <img style="vertical-align: middle" src="../images/unlock.png"/> icon.
	<p>All passwords will be reset to <strong><?php echo $CFG->defaultpassword; ?></strong>. Please advise users to change their password when they next log in.</p>
	</div>
	</div>
<?php } else { ?>
	<div class="annot">
	<p>Use this screen to administer users in your organisation.
	</p>
	</div>
	<div class="subannot_first"></div>
	<div class="subannot">
	<p>Note: you may only administer users below your current role. For example:</p><ul><li>an advisor may only reset passwords for learners,</li><li>a provider admin may reset passwords for advisors and learners</li></ul><p></p>
	</div>
	
	<div id="advisors" class="section" style="width: 100%;">
	<div class="sectionheader">Users</div>
	<div class="sectioncontent">
	
	<div class="subsection">
	<h3>All Users</h3>
	<div class="subannot_two"></div>
	<div class="subannot">
	<p>To modify or affect a user, select the relevant checkboxes in the 'Select' column and click on the relevant button below.</p>
	<p>Archived users will appear with a <img style="vertical-align: middle" src="../images/lock.gif"/> icon. Live users will display a <img style="vertical-align: middle" src="../images/unlock.png"/> icon.
	<p>Users with email notifications enabled will appear with a <img style="vertical-align: middle" src="../images/email.png"/> icon. Users with email notifications disabled will display an <img style="vertical-align: middle" src="../images/email-x.png"/> icon.
	<p>All passwords will be reset to <strong><?php echo $CFG->defaultpassword; ?></strong>. Please advise users to change their password when they next log in.</p>
	</div>
	</div>
<?php } ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="form2">

<?php if ($_POST['action'] == 'Archive' && $usernames) { ?>
<div id="archiveusers">
Please provide a reason for each of the learners you wish to archive.
<table border="0" id="archivetable">
<?php foreach ($usernames as $username) { 
// discover potential reason for archival in order to check radiobutton for the user...
if (getNumberOfSittings ($username['userid']) == 6) {
	$potentialreason = 'Completer';
} else {
	$potentialreason = 'Early Leaver';	
}
?>
<tr>
<td><?php echo $username['fname'] . ' ' . $username['sname']; ?></td>
<td>
	<input type="radio" name="reasons_<?php echo $username['userid']; ?>" value="Early Leaver" <?php echo $potentialreason == 'Early Leaver' ? 'checked="checked"' : ''; ?>>Early Leaver
	<input type="radio" name="reasons_<?php echo $username['userid']; ?>" value="Completer" <?php echo $potentialreason == 'Completer' ? 'checked="checked"' : ''; ?>>Completer
	<input type="hidden" name="users[]" value="<?php echo $username['userid']; ?>"/>
</td>
</tr>
<?php } // end for each?>

</table>
</div>

<input type="submit" id="archivecomplete" value="Complete Archiving" title="Go Archive" name="action" />
<input type="submit" id="cancelarchive" value="Cancel" title="Cancel" name="action" />
<?php } // end archiving ?>


<input type="hidden" value="adv" name="form" />
<div class="filters">
<?php if (has_capability(Sessions::getID(), 'providers:control_all') || has_capability(Sessions::getID(), 'providers:control_subcontractors')) {
	?>

	
<label class="ff" for="provider"><?php echo_string ('PROVIDER'); ?>:</label>
	
	
		 <select class="ff" name="provider" id="provider">
         
		<?php 
           
           
           

                                        if (has_capability(Sessions::getID(), 'providers:control_all')) {
                                        	$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID, visible from sb_providers  group by name ORDER BY visible desc, name asc', true);
                                        } else if (has_capability(Sessions::getID(), 'providers:control_subcontractors')) {
                                    		$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID, visible from sb_providers where superproviderid=' . Sessions::getSuperProviderID (Sessions::getUserInfo ('providerid')) . ' group by name ORDER BY visible desc, name asc', true);
                                       	}
                                       
                            		?>
                            		<?php if (count ($providers) > 1) {
                            			
                            			?>
                            		 <option value="">Please Select</option>
                                    <?php } ?>
                                    
                                    <?php 
                                    	if (Sessions::getLastProviderWorkedOn ()) {
											$selected = Sessions::getLastProviderWorkedOn ();
										} else {
                                       		$selected = $_POST['provider'] ? $_POST['provider'] : Sessions::getUserInfo('providerid'); // get users actual provider
                                        }
                                       
                                       	// if (!empty ($valid_ac_code)) {
                                       // 	$selected = $res['providerid'];
                                       // }	
                                       
                                        foreach ($providers as $provider) {
                                    ?>
                                    <option value="<?php echo $provider['ProviderID']; ?>" <?php if ($provider['ProviderID'] == $selected) {?>selected="selected"<?php } ?>>
                                        <?php print $provider['name']; ?> <?php if (!$provider['visible']) { echo "(Archived)"; } ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
   
	
	
	
<?php } // end of providers capability check ?>

<div class="ff">
<?php
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
// create a filter to allow filtering by roles (only allow below users role for security purposes)
$filter = new FilterField ('roleid', 'roleid', 'dropdown', 'Role');
// grab roles below users current role
$roles = getRolesBelow ($myrole, false);
// bind drop-down with roles data giving 'roleid' field as the value and 'name' field as label from drop-down
$filter->bindData($roles, 'RoleID', 'name', 'Any');
$filter->setSearchType('exact');
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

  

 



<input type="submit" value="Filter" name="filter" />
	</div>	<!--  .filters -->
		

			
<?php

// update 2013-01-25 added 	sb_users_info.roleid as RoleID in query below for filtering
// $users = DB::executeContainedSelect('users_info', array ('UserID', 'loginid as Username', 'fname as Firstname', 'sname as Surname', 'email as Email', 'roleid'), 'roleid>' . $myrole . ' and providerid=' . $myprovider, 'Username');
$origquery = ('SELECT sb_users_info.UserID,
	sb_users_info.archived,
	sb_users_info.loginid AS Username,
	DATE_FORMAT (sb_users_info.dob, \'%d/%m/%Y\') as DOB,
	sb_users_info.fname AS Firstname,
	sb_users_info.sname AS Surname,
	sb_users_info.email AS Email,
	
	sb_roles_types.name AS Role,
	sb_users_info.roleid as RoleID,
sb_users_info.enableemails AS EmailsEnabled
	FROM sb_users_info INNER JOIN sb_roles_types ON sb_users_info.roleid = sb_roles_types.RoleID
	WHERE sb_users_info.roleid>' . $myrole . ' and sb_users_info.providerid=' . $myprovider . ' ORDER BY Username');

$finalquery = $filtersMgr->generateQuery($origquery);



$users = DB::runSelectQuery ($finalquery, true);

$dg = new DataGrid ($users, 'UserID');
$dg->setTableID ('advisorstable');
$dg->setTableClass ();

$dg->addConditionalField ('UserID', '<center><img title= "Learner is archived" alt="Learner is Archived" src="../images/lock.gif" /></center>', 'Archive Status', 'archived', '= 1', '<center><img title="Learner is live" alt="Learner is live" src="../images/unlock.png" /></center>');

if ((has_capability(Sessions::getID(), 'profile:set_email_notifications'))) {
	$dg->addConditionalField ('UserID', '<center><img title= "User has email notifications disabled" alt="User has email notifications disabled" src="../images/email-x.png" /></center>', 'Notifications', 'EmailsEnabled', '= 0', '<center><img title="User has email notifications enabled" alt="User has email notifications enabled" src="../images/email.png" /></center>');
}


if ((has_capability(Sessions::getID(), 'users:delete_user') || has_capability(Sessions::getID(), 'users:edit_profile'))) {
	$dg->addHTMLCol('<center><a href="../user_edit.php?userid=%s">Edit</a></center>', 'Edit Profile');
}
if ((has_capability(Sessions::getID(), 'reports:view_advisor_results') || has_capability(Sessions::getID(), 'reports:view_student_results'))) {
	$dg->addHTMLCol('<center><a href="../dashboard.php?uid=%s">View</a></center>', 'View Results');
}
$dg->addHTMLCol('<center><input name="users[]" value="%s" type="checkbox" /></center>', 'Select');
$dg->removeDisplayFields(array ('UserID', 'RoleID', 'archived', 'EmailsEnabled'));
// if advisor, remove the 'Role' column as it will only ever display 'Learner'.
if (Sessions::getUserInfo('roleid') == $CFG->advisorroleid) {
	$dg->removeDisplayField ('Role');
}
$dg->setFieldTitle('DOB', 'DOB');
$dg->addAttr('th', 'align', 'left');
$dg->render ();


?>

<?php if (has_capability(Sessions::getID(), 'user:change_password')) {?>
<input type="submit" id="password" value="Reset Password" title="Reset Password(s)" name="action" />
<?php } ?>
<?php if (has_capability(Sessions::getID(), 'users:archive_user')) {?>
<input type="Submit" name="action" id="archive" value="Archive" title="Archive the user(s)" />
<input type="Submit" name="action" id="unarchive" value="Unarchive" title="Unarchive the user(s)" />
<?php } ?>
<?php if (has_capability(Sessions::getID(), 'profile:set_email_notifications')) { ?>
<input type="Submit" name="action" id="emailon" value="Disable Emails" title="Disable email notifications for user(s)" />
<input type="Submit" name="action" id="emailoff" value="Enable Emails" title="Enable email notifications for user(s)" />
<?php } ?>
<?php if (has_capability(Sessions::getID(), 'users:delete_user')) { ?>
<input type="submit" id="delete" value="Delete" title="Delete User(s)" onclick="return confirm ('Are you absolutely sure you wish to delete the selected users?');" name="action" />
<?php } ?>


                        


</form>
</div>
</div>
<?php  include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>
