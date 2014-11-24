<?php

include_once ('config.php');
include_once ($CFG->apploc  . '/lib/activity.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/lib/funcs.php');
include_once ($CFG->apploc  . '/lib/validation.php');
include_once ($CFG->apploc  . '/lib/roles.php');

Sessions::checkUserLogIn ();

//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('general', 'user'));

$error = false;
$pwd_change = false;
$ctr_change = false;

if (!empty ($_POST)) {
	if ($_POST['action'] == 'chgpass') {
		if (!empty ($_POST['current']) && !empty ($_POST['new']) && !empty ($_POST['verify'])) {
			// if we have all relevant fields, start the process
			
			$current_pwd_check = DB::executeSelect('users_info', array ('UserID'), array (
					'UserID'=>Sessions::getID(),
					'password'=> md5 ($_POST['current'])
			));
			
			$pwd_change = false;
			$error = 'Fine.';
			
			if ($current_pwd_check) {
				// password is correct
				// check current and verified are identical
				$v_pass = validatePasswords($_POST['new'], $_POST['verify']);
				// passwords match
				if ($v_pass) {
					$pwd_change = DB::executeUpdate('users_info', array ('password'=>md5($_POST['new'])), array ('UserID'=>Sessions::getID ()));
					if (!$pwd_change) $error = return_string ('PASSWORD_CHG_ERROR');
				} else {
					$error = return_string ('PASSWORD_CHG_VERIFY_FAIL');
				}
			} else {
				$error = return_string ('PASSWORD_CURRENT_INCORRECT');
			}
		} else {
			$error = return_string ('PASSWORD_CHG_FILL_ALL');
		}
	} else if ($_POST['action'] == 'chgcentre') {
		$centrechange = DB::executeUpdate('users_info', array ('centreid'=>$_POST['ctr']), array ('UserID'=>Sessions::getID ()), 1);
		$ctr_change = true;
		//save new centre for user
	} else if ($_POST['action'] == 'chgdob') {
		
		if ( validateDOB ($_POST['dob'])) {
		
			$dobchange = DB::executeUpdate ('users_info', array ('dob'=>ukdate2mysql ($_POST['dob'])), array ('UserID'=>Sessions::getID ()), 1);
		} else {
			
			$error = 'Unable to set date of birth: incorrect format entered.<br/>Please use dd/mm/yyyy format (e.g. 25/12/1985)';
		}
		
	} else if ($_POST['action'] == 'disableemails') {
		if (has_capability(Sessions::getID(), 'profile:set_email_notifications')) {
			$chgemail = DB::executeUpdate ('users_info', array ('enableemails'=>$_POST['enableemails']), array('UserID'=>Sessions::getID ()), 1);
			if (! $chgemail) {
				$error = 'Unable to change email notifications preference.';
			}
		}
	}
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="-1" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo_string ('APP_NAME'); ?> : <?php echo_string ('PROFILE'); ?></title>
<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->printcssfile); ?>" rel="stylesheet" type="text/css" media="print"/>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="js/resizer.js"></script>
</head>
<body>
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>

<h2><?php echo_string ('PROFILE'); ?></h2>

<?php if ($pwd_change) { ?>
<div class="alert sallgood"><?php echo_string ('PASSWORD_CHG_SUCCESS');// 'Your password has been changed.'; ?></div><div style="clear: both;"></div><br/>
<?php } else if ($ctr_change) { ?>
<div class="alert sallgood"><?php echo 'Your centre has been changed.'; ?></div><div style="clear: both;"></div><br/>
<?php } else if ($error) { ?>
<div class="alert warning"><?php echo $error; ?></div><div style="clear: both;"></div><br/>
<?php } ?>

<div class="annot">
<p>You may change your password and edit information about yourself on this screen.</p>
</div>



<h2>Change Password</h2>
<div class="subannot_first"></div>
<div class="subannot">
<p>To change your password, enter:
	<ul>
		<li>your current password, and</li>
		<li>your new password twice.</li>
	</ul>
</div>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="action" value="chgpass" />
<table>
	<tr>
		<td><label for="current"><?php echo_string ('PASSWORD_CURRENT'); ?>:</label></td>
		<td><input autocomplete="off" tabindex="1" type="password" id="current" name="current" /></td>
	</tr>
	<tr>
		<td><label for="new"><?php echo_string ('PASSWORD_NEW'); ?>:</label></td>
		<td><input autocomplete="off" tabindex="2" type="password" id="new" name="new" /></td>
	</tr>
	<tr>
		<td><label for="verify"><?php echo_string ('PASSWORD_VERIFY'); ?>:</label></td>
		<td><input autocomplete="off" tabindex="2" type="password" id="verify" name="verify" /></td>
	</tr>
</table>

 <br/>

<input type="submit" value="<?php echo_string ('PASSWORD_CHANGE_BTN'); ?>"/>

</form>

<?php if (has_capability(Sessions::getID(), 'profile:set_email_notifications')) { ?>
	<h2>Email Notifications</h2>
	<div class="subannot_first"></div>
	<div class="subannot">
	<p>To disable all email notifications from TRaCIO, simply select below.</p>
	</div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<input type="hidden" name="action" value="disableemails" />
	<table>
		<tr>
			<td><label for="emailson">Email notifications:</label></td>
			<td><select name="enableemails" id="enableemails">
			<?php 
			$selected = Sessions::getUserInfo ('enableemails');
			?>
					<option value="1" <?php if ($selected == 1) { ?>selected="selected"<?php } ?>>Send emails</option>
					<option value="0" <?php if ($selected == 0) { ?>selected="selected"<?php } ?>>Don't send emails</option>
			</select></td>
		</tr>
		
	</table>
	
	 <br/>
	
	<input type="submit" value="Save Email Setting"/>
	</form>
<?php } // end profile:set_email_notifications ?>

<?php if (has_capability(Sessions::getID(), 'users:change_centre') &&  DB::executeSelect ('centres', '*', array ('providerid'=>Sessions::getUserInfo ('providerid')))) { ?>
<hr/>
<div>
<h2>Change Centre</h2>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="action" value="chgcentre" />
<label for="ctr"><?php echo_string ('CENTRE'); ?>:</label>
<select name="ctr" id="ctr">             
<?php if (has_capability(Sessions::getID(), 'users:change_centre_to_any')) { ?>                 
<option value="0"><?php echo_string ('COMBO_ALL'); ?></option>
<?php } ?>

<?php 
                                    
                                   
                                    $ctrs = DB::executeContainedSelect('centres', '*', array ('providerid'=>Sessions::getUserInfo('providerid')));

                                   
                                    // get current centre (if any)
									$selected = Sessions::getUserInfo ('centreid');
									
									
									foreach ($ctrs as $ctr) {
                                    ?>
                                    <option value="<?php echo $ctr['CentreID']; ?>" <?php if ($ctr['CentreID'] == $selected) {?>selected="selected"<?php } ?>>
                                        <?php print $ctr['name']; ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
</select>
<input type="submit" value="Select Centre"/>
</form>
</div>
<?php } // has_capability change centre ?>

<?php

// check if learner users have a date of birth set and prompt them if not.
// update 2012-12-18 learnerrole id is pushed from cfg file, rather than hardcoded in 'roleid' below
if (Sessions::getUserInfo ('roleid') == $CFG->learnerroleid) {
$res = DB::executeSelect('users_info', 'dob', array ('UserID'=>Sessions::getID()));
if (!$res['dob']) {
?>
<br/>
<hr/>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="action" value="chgdob" />
<h2>Set Date of Birth</h2>
<div>
<table>
 <tr>
                          	<td>
                          
                        		<label for="dob">Date of Birth:<br/><span style="font-size: 0.8em;">(dd/mm/yyyy)</span></label>
                        	</td>
                        	<td>
                        		<input type="text" placeholder="dd/mm/yyyy" name="dob" id="dob" value="" />
                        		<span class="red">*</span>
                        	</td>
                        <td colspan="2">
                            	<span id="dob_resp"><!--  <?php echo "Invalid date format! dd/mm/yyyy required."; ?> --></span>
                            </td>
                        </tr>
                        </table>
</div>
<input type="submit" value="Set Date of Birth"/>
</form>
<?php }
}
 ?>
<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>


