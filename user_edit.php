<?php
include_once ('./config.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/classes/db.php');
include_once ($CFG->apploc  . '/lib/validation.php');
include_once ($CFG->apploc  . '/lib/funcs.php');
include_once ($CFG->apploc  . '/lib/roles.php');

//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('question', 'general', 'user'));

require_once($CFG->apploc . '/external/recaptchalib/recaptchalib.php');

// Get a key from http://recaptcha.net/api/getkey
$publickey = $CFG->recaptcha_public;
$privatekey = $CFG->recaptcha_private;

$usecaptcha = false;
$captchaOK = false;

$displayform = true;

$edituserid = isset ($_GET['userid']) ? $_GET['userid'] : $_POST['uid'];

$userInfo = DB::executeSelect ('users_info', '*', array ('UserID'=>$edituserid), '', 1 );

?>
<?php

$action = 'edituser';

// default signup type is learner
$signuprole = $CFG->learnerroleid;
$res = false;
                // check for advisor type registration
                
             
                		// check capabilities...
                		Sessions::checkUserLogIn ();
                		
						if (has_capability(Sessions::getID(), 'users:edit_profile')) {
							has_access_to_user (Sessions::getID (), $edituserid );
						} else {
							die (return_string ('ACCESS_DENIED'));
						}
						
						$usecaptcha = false;
						$captchaOK = true;
                
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo_string ('APP_NAME'); ?>: Edit a User Profile</title>
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="-1" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="<?php echo $CFG->cssfile; ?>" rel="stylesheet" type="text/css" />
<style>
label {
	width: 10em;
	float: left;
}
label.error {
	float: none;
	color: red;
	padding-left: .5em;
	vertical-align: top;
}
</style>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="js/resizer.js"></script>
<script type="text/javascript">
 var RecaptchaOptions = {
    theme : 'white'//'clean'
 };
 </script>

<script type="text/javascript">
$(document).ready(function() {

		emailMandatory = false;
		dropdownsMandatory = true;
		
		function validateForm (e) {
			var res = true;
			
	   		if (!$("#s_name").attr ('value')) {
	   			$("#s_name").css({"background-color":"#FFCCCC"});
	   			
	   			res = false;
	   		} else {
	   			$("#s_name").css({"background-color":"#FFFFFF"});
	   		}
	   		if (!$("#f_name").attr ('value')) {
	   			$("#f_name").css({"background-color":"#FFCCCC"});
	   			res = false;
	   		
	   		} else {
	   			$("#f_name").css({"background-color":"#FFFFFF"});
	   		}
	   		if (!$("#l_id").attr ('value')) {
	   			$("#l_id").css({"background-color":"#FFCCCC"});
	   	
	   			res = false;
	   		} else {
	   			$("#l_id").css({"background-color":"#FFFFFF"});
	   		}
	   		if (emailMandatory) {
		   		if (!$("#l_email").attr ('value')) {
		   			$("#l_email").css({"background-color":"#FFCCCC"});
		   			res = false;
		   		} else {
		   			$("#l_email").css({"background-color":"#FFFFFF"});
		   		}
	   		}
	   		if (!$('#l_dob').attr ('value')) {
	   	
	   			$("#l_dob").css({"background-color":"#FFCCCC"});
	   			res = false;
	   		} else {
	   			$("#l_dob").css({"background-color":"#FFFFFF"});
	   		}
	   		
	   		if (dropdownsMandatory) {
				var dropdowns = $('#form1 select.reqd');
				for (i=0; i<dropdowns.length; i++) {				
					if ($(dropdowns[i]).attr ('value') == "0") {
						$(dropdowns[i]).css ({"color":"red"});
						//TODO - provide text colouring for safari/chrome.
						$(dropdowns[i]).css ({"font-family":"Times New Roman"});
						res = false;
					} else {
						$(dropdowns[i]).css ({"color":"black"});
					}
				}
	   		}
			if (!res) {
				alert ("<?php echo_string('FORM_INCOMPLETE'); ?>.");
			}
	   		return res;
	   	}

		/* on form submit, check if we need to validate the form
		   delete action prevents validation obviously */
		 $("form").submit(function() { 
		
			    var val = $("input[type=submit][clicked=true]").val()
				if (val != 'Delete User' && val != 'Archive User' && val != 'Unarchive User') {
					return validateForm();
				} else {
					return true;
				}
			});

			$("form input[type=submit]").click(function() {
			    $("input[type=submit]", $(this).parents("form")).removeAttr("clicked");
			    $(this).attr("clicked", "true");
			});
			
		$('#provider').change (function (e) {
			// do ajaxin' and get centres/advisors given provider
			if ($(this).val () > 0) {
				
					$.ajax({
					  url: 'ajax/getdata.php',
					  type: "POST",
					  data: {provider: $(this).val (), q:'centres'},
					  success: function(data) {
						  if (data != 'false') {
						  	$('#ctr').show ();
						  	$('#ctr_label').show ();
						  	$('#blahblah').html (data + "<span class='red'>*</span>");
						  } else {
							$('#ctr').hide ();
							$('#ctr_label').hide ();
							$('#blahblah').html ('<input type="hidden" name="ctr" value="0" />');
						  }
					  }
					});

					$.ajax({
						  url: 'ajax/getdata.php',
						  type: "POST",
						  data: {provider: $(this).val (), q:'advisors'},
						  success: function(data) {
							  if (data != 'false') {
							  	$('#advisor').show ();
							  	$('#adv_label').show ();
							  	$('#blahblah2').html (data + "<span class='red'>*</span>");
							  } else {
								$('#advisor').hide ();
								$('#adv_label').hide ();
								$('#blahblah2').html ('<input type="hidden" name="advisor" value="0" />');
							  }
						  }
						});
					
			} else {
				$('#ctr').hide ();
				$('#ctr_label').hide ();
				$('#advisor').hide ();
				$('#adv_label').hide ();
				
			}
			//}
		});

	

		$("#l_id").blur(function() { 
			var username_length; 
			username_length = $('#l_id').val().length; 
			$("#ajaxresp").empty(); 
			 
			if (username_length < 6 && username_length != 0) {
				$('#ajaxresp').html('<div class="alert warning"><?php echo sprint_string ('FIELD_LENGTH_SHORT', 'USERNAME', 'id'); echo '. ' . sprint_string ('LENGTH_CRITERIA_MIN', '6'); ?></div>');
			} else if (username_length > 20 && username_length != 0) {
				$('#ajaxresp').html('<div class="alert warning"><?php echo sprint_string ('FIELD_LENGTH_LONG', 'USERNAME', 'id'); echo '. ' . sprint_string ('LENGTH_CRITERIA_MAX', '20'); ?></div>');
			} else {
				if ($('#l_id').val() != '') {
					$.ajax({
					  url: 'ajax/check_username.php',
					  type: "POST",
					  data: {username: $('#l_id').val()},
					  success: function(data) {
						  if (data == 'avail') {
					  	 	  $('#ajaxresp').html('<div class="alert sallgood"><?php echo_string ('USERNAME_AVAIL'); ?></div>');
						  } else if (data == 'invalid') {
							  $('#ajaxresp').html('<div class="alert warning"><?php echo_string ('USERNAME_INVALID_FORMAT'); ?></div>');	
						  } else {
							  $('#ajaxresp').html('<div class="alert warning"><?php echo_string ('USERNAME_AVAIL_FAIL'); ?></div>');	
						  }
					  }
					});
				}
			}
	    });

		// correctly validate UK fmt here in js
		function checkDate (datestring) {
			if (Date.parse(datestring)) {
				return true;
			} else {
				return false;
			}
		}
		

		function checkemail () {
			//var mandatory = false;
			
			var username_length; 
			username_length = $('#l_email').val().length; 
			$("#ajaxresp2").empty(); 
			 
			if (username_length < 6 && username_length != 0) {
				$('#ajaxresp2').html('<div class="alert warning">Please enter a valid email address.</div>');
			} else {
				if ($('#l_email').val() != '') {
					$.ajax({
					  url: 'ajax/check_email.php',
					  type: "POST",
					  data: {email: $('#l_email').val()},
					  success: function(data) {
						  if (data == 'avail') {
					  	 	  $('#ajaxresp2').empty ();
						  } else if (data == 'invalid') {
							  $('#ajaxresp2').html('<div class="alert warning"><?php echo_string ('EMAIL_INVALID_FORMAT'); ?></div>');	
						  } else {
							  $('#ajaxresp2').html('<div class="alert warning"><?php echo_string ('EMAIL_UNAVAIL'); ?></div>');	
						  }
					  }
					});
				}
			}
		}

		
		$('#l_dob').blur (function () {
			checkDate ( $('#l_dob').val ());
		});
		
		$("#l_email").blur(checkemail);

	    $('#pass_1').blur(function () {
			if ($(this).val().length < 6 && $(this).val () != '') {
				$('#pass1_resp').html('<div class="alert warning"><?php echo sprint_string ('FIELD_LENGTH_SHORT', 'PASSWORD', 'id') . '. '; echo ' '. sprint_string ('LENGTH_CRITERIA_MIN', '6'); ?></div>');
			} else {
				$('#pass1_resp').html('');
			}
	    });

	    

	    $('#pass_2').blur(function () {
			if ($(this).val () != $('#pass_1').val () && $(this).val () != '') {
				$('#pass2_resp').html('<div class="alert warning"><?php echo_string ('PASSWORD_MATCH_FAIL'); ?></div>');
			} else {
				$('#pass2_resp').html('');
			}
	    });

		$('#advisor').hide ();
		$('#adv_label').hide ();

		<?php 
		if (!empty ($valid_ac_code)) {
		?>
			dropdownsMandatory = false;
			$('#age_row').hide ();
			$('#gender_eth_row').hide ();
			$('#f_name').val ('<?php echo $res['fname']; ?>');
			$('#s_name').val ('<?php echo $res['sname']; ?>');
			$('#l_email').val ('<?php echo $res['email']; ?>');
		<?php 
		}
		?>


		
});//jquery end
</script>
</head>

<body>  

<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<h2><?php echo 'Edit a User Profile'; ?></h2>



<?php 

if (!Sessions::checkUserLogIn (false) || $action == 'edituser') { ?>

<?php 
	if (!empty ($_GET['ac'])) {
		if ($res) {
?>
                
<?php
     	} else if (!$res) { 
                			// dead or incorrect activation code.
                		?>
                			<div class="alert warning">
                				<?php echo_string ('ACTIVATION_FAIL'); ?>
                			</div>
                			<div style="clear:both;"></div>
<?php   }  // else if (!$res)
	}    // if (!empty ($_GET['ac'])) {
?>
<?php

	if (!empty ($_POST)) {
		if ($_POST['button'] == 'Delete User') {
			// check access rights
			if (has_capability(Sessions::getID(), 'users:delete_user') && has_access_to_user (Sessions::getID (), $_POST['uid'] )) {   
				// do deletion	
				$deleteq = DB::executeDelete('users_info', array('UserID'=>$_POST['uid']), 1);
				if ($deleteq) {
					$displayform = false;
					echo 'User deleted.';
				} else {
					echo 'There was an error removing user ' . $_POST['uid'] . '. Please contact admin.';	
				}
			} else {
				die ('Access failed.');
			}
		} else if ($_POST['button'] == 'Archive User') {
			if (has_capability(Sessions::getID(), 'users:archive_user') && has_access_to_user (Sessions::getID (), $_POST['uid'] )) {
				// do archiving
				$archiving = archiveLearner ($_POST['uid']);
				if ($archiving) {
					$displayform = false;
					echo 'User archived.';
				} else {
					echo 'There was an error archiving user ' . $_POST['uid'] . '. Please contact admin.';
				}
			} else {
				die ('Access failed.');
			}
		} else if ($_POST['button'] == 'Unarchive User') {
				if (has_capability(Sessions::getID(), 'users:archive_user') && has_access_to_user (Sessions::getID (), $_POST['uid'] )) {
					// do archiving
					$archiving = unarchiveLearner ($_POST['uid']);
					if ($archiving) {
						$displayform = false;
						echo 'User unarchived.';
					} else {
						echo 'There was an error unarchiving user ' . $_POST['uid'] . '. Please contact admin.';
					}
				} else {
					die ('Access failed.');
				}
				
		} else if ($_POST['action'] == 'edituser') {
		
			$emailMandatory = false;
			$dropdownsMandatory = true;
			$genderMandatory = true;
		
			
		
			$captchaOn = $usecaptcha;
			// check captcha!
			if ($_POST["recaptcha_response_field"] && $captchaOn) {
	
	        	$resp = recaptcha_check_answer ($privatekey,
	                                        $_SERVER["REMOTE_ADDR"],
	                                        $_POST["recaptcha_challenge_field"],
	                                        $_POST["recaptcha_response_field"]);
	
	        	if ($resp->is_valid) {
	        		$captchaOK = true;
	        		//echo "captcha valid";
	        	} else {
	        		$captchaOK = false;
	        		echo "Security check (captcha) was invalid.";
	        	}
			} else if (!$usecaptcha) {
				$captchaOK = true;	
			}
			
			$formaction = 'create';
			
			// do some validation checks
			$v_fname = validateName ($_POST['f_name'], 'f');
			$v_sname = validateName ($_POST['s_name'], 's');
			$v_username = true;
		
			$v_dob = validateDOB ($_POST['l_dob']);
			
			$v_email = true;
			
			$v_pass = true;
			if ($genderMandatory) {
				$v_gender = !empty ($_POST['gender']);
			} else {
				$v_gender = true;
			}
			$v_sdate = true;
			$v_edate = true;
			if ($dropdownsMandatory) {
				$v_dropdowns = validateDropDowns (array (
												$_POST['ethnicity'],
												$_POST['agegroup'],
												$_POST['prg'],
												$_POST['provider']
											 	 ));
			} else {
				$v_dropdowns = true;	
			}
											 
		
			$saveflag = false;
			if ($captchaOK && $v_dob && $v_fname && $v_sname && $v_username && $v_email && $v_pass && $v_gender && $v_dropdowns && $v_edate && $v_sdate && !$v_emailtaken && !$v_usernametaken) {
				$saveflag = true;
			} else {
				$errorstr = '';
				$errorstr .= '<ul style="color: red">';
				if (!$captchaOK) $errorstr .= '<li>Security check (Recaptcha) incorrect</li>';
				if (!$v_fname) $errorstr.= '<li>No first name</li>';
				if (!$v_sname) $errorstr.= '<li>No surname</li>';
				if (!$v_username) $errorstr.= '<li>Invalid username</li>';
				if ($v_usernametaken) $errorstr.= '<li>Username unavailable</li>';
				if (!$v_email) $errorstr.= '<li>Email format invalid</li>';
				if ($v_emailtaken) $errorstr.= '<li>Email address already registered</li>';
				if (!$v_pass) $errorstr.= '<li>Passwords do not match or are too short</li>';
				if (!$v_gender) $errorstr.= '<li>Gender not specified</li>';
				if (!$v_sdate) $errorstr.= '<li>No start date</li>';
				if (!$v_dob) $errorstr.= '<li>Date of birth invalid</li>';
				if (!$v_edate) $errorstr.= '<li>No end date</li>';
				if (!$v_dropdowns) $errorstr.= '<li>One or more dropdowns incomplete</li>';
				$errorstr.= '</ul>';
			}
		
		if ($saveflag) {
			// - does user already exist? check first
			if (DB::executeSelect('users_info', '*', array ('UserID'=>$_POST['uid']))) {
				// creating a new user
			
				$queryArray = array (
					'providerid'	=> $_POST['provider'],
					//'loginid' 		=> strtolower ($_POST['l_id']),
					'fname' 		=> ucname ($_POST['f_name']),
					'sname' 		=> ucname ($_POST['s_name']),
					'roleid' 		=> $_POST['role'],
					//'password' 		=> md5($_POST['pass_1']),
					//'email'			=> $_POST['l_email'],
					'ethnicityid'	=> $_POST['ethnicity'],
					'gender'		=> $_POST['gender'],
					'ageid'   		=> $_POST['agegroup'],
					'groupid'		=> 1,
					'programmeid'	=> $_POST['prg'],
					'centreid'      => $_POST['ctr'],
					//'startdate'		=> date("Y-m-d", mktime(0, 0, 0, $_POST['start_m'], 1, $_POST['start_y'])),
					//'enddate'		=> date("Y-m-d", mktime(0, 0, 0, $_POST['end_m'], 1, $_POST['end_y'])),
					'completed'		=> 0,
					'dob'			=> ukdate2mysql( $_POST['l_dob'])
				);
			
				if (isset ($_POST['enableemails']) && has_capability(Sessions::getID(), 'profile:set_email_notifications')) {
					$queryArray ['enableemails'] = $_POST['enableemails'];
				}
			
				$q = DB::executeUpdate ('users_info', $queryArray,
						array ('UserID'=>$_POST['uid']),
					1);

				if ($q) {
					echo 'User profile updated. <a href="index.php">Click here to return to the main menu.</a>';
					
					$displayform = false;
				} else {
					
					echo 'User profile not saved. Error.' . mysql_error();
				}
			}  else {
				echo 'A user with the loginid "' . $_POST['l_id'] . '" does not exist.';
			}
			} else {
				echo "Form is incomplete. Please click the <strong>Back</strong> button to complete it.";
			}
		} else { 
				?>
				<div class="alert warning">
	                	<?php 
	                	echo "Form is incomplete. Please check the following fields and resubmit.";
						
						?>
	            </div>
	            <div style="clear:both;"></div>
	            <div class="subannot_two"></div>
				<div class="subannot">
				<p><?php echo $errorstr;?></p>
			
				</div>
	            
	            
	            <?php     			
				
			} // if ($saveflag)
		} // if ($_POST['action'] == 'create')
} //if (!empty ($_POST))

?>
<?php if ($displayform) { // will only be false if user has been added ?>
<div class="annot">
<p><span class="red">*</span> denotes a required field.</p>
</div>
<?php if ($userInfo['archived']) { ?>
<div  style="margin-bottom: 20px;"  class="alert warning ">
	Please note: This user is currently archived. Given reason: <br/>
	 <blockquote>"<?php echo $userInfo['archivereason']; ?>"</blockquote>
</div>
<?php } ?>
            <div style="clear: both; ">
                <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?><?php echo !empty ($_GET['ac']) ? '?ac=' . $_GET['ac']:''; ?><?php echo !empty ($_GET['action']) ? '?action=' . $_GET['action']:''; ?>">
             <?php
             
             if ($action == 'edituser') {
             ?>
             	<input type="hidden" name="action" value="edituser" />
             	
             	<input type="hidden" name="uid" value="<?php echo $_GET['userid']; ?>" />
             <?php	
             }
            
             ?>
                	<input type="hidden" name="prg" value="1" />
                    <table cellpadding="5" cellspacing="0" border="0">
                        <tr>
                            <td>
                                <label for="f_name"><?php echo_string ('FN'); ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="f_name" id="f_name" value="<?php echo !empty ($userInfo['fname']) ? $userInfo['fname'] : ''; ?>"/>
                                <span class="red">*</span>
                            </td>
                             <td>
                                <label for="s_name"><?php echo_string ('SN'); ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="s_name" id="s_name" value="<?php echo !empty ($userInfo['sname']) ? $userInfo['sname'] : ''; ?>" />
                                <span class="red">*</span>
                            </td>
                         </tr>
                         <tr>
                        	<td>
                        		<label for="l_email"><?php echo_string ('EMAIL'); ?>:</label>
                        	</td>
                        	<td>
                        		<input disabled="disabled" type="text" name="l_email" id="l_email" value="<?php echo !empty ($userInfo['email']) ? $userInfo['email'] : ''; ?>"/>
                        	</td>
                        	<td colspan="2">
                        		<span id="ajaxresp2"></span>
                        	</td>
                         </tr>
                         <tr>
                            <td>
                                <label for="l_id"><?php echo_string ('USERNAME'); ?>:</label>
                            </td>
                            <td>
                                <input disabled="disabled"  type="text" name="l_id" id="l_id" value="<?php echo !empty ($userInfo['loginid']) ? $userInfo['loginid'] : ''; ?>"/>
                                <span class="red">*</span>
                            </td>
                            <td colspan="2">
                            	<span id="ajaxresp"><!--  <?php echo sprint_string ('LENGTH_CRITERIA', '6'); ?> --></span>
                            </td>
                        </tr>
                         <tr>
                            <td>
                                <label for="pass_1"><?php echo_string ('PASSWORD_CHOOSE'); ?>:</label>
                            </td>
                            <td>
                                <input disabled="disabled" type="password" name="pass_1" id="pass_1" />
                                <span class="red">*</span>
                            </td>
                            <td colspan="2">
                            	<span id="pass1_resp"><!--  <?php echo sprint_string ('LENGTH_CRITERIA', '6'); ?> --></span>
                            </td>
                        </tr>
                         <tr>
                            <td>
                                <label for="pass_2"><?php echo_string ('PASSWORD_RETYPE'); ?>:</label>
                            </td>
                            <td>
                                <input disabled="disabled"  type="password" name="pass_2" id="pass_2" />
                                <span class="red">*</span>
                            </td>
                            <td colspan="2">
                            	<span id="pass2_resp"></span>
                            </td>
                        </tr>
                        <tr id="provider_row">
                            
                            <td>
                                <label for="provider"><?php echo_string ('PROVIDER'); ?>:</label>
                            </td>
                            <td>
                            	<?php
                            	
                            	if (!empty ($valid_ac_code)) {
                            		$providers = DB::runSelectQuery ('SELECT name, ProviderID from sb_providers where ProviderID=' . $res['providerid']);	
                                ?>
                                	<input type="text" disabled="disabled" value="<?php echo $providers['name']; ?>" />
                                	<input type="hidden" name="provider" id="provider" value="<?php echo $providers['ProviderID']; ?>" />
                                <?php 
                            	} else {
                            		
                            	?>
                                <select class="reqd" <?php echo !empty ($valid_ac_code) ? 'disabled="disabled"': ''; ?>name="provider" id="provider">
                                
                                <?php 
                                 //TODO - getProviders func?
                                        if ( has_capability(Sessions::getID(), 'providers:control_all')) {
                                        	$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID from sb_providers where visible=1 group by name ORDER BY name asc', true);
                                        } else if (has_capability(Sessions::getID(), 'providers:control_subcontractors')) {
                                    		$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID from sb_providers where visible=1 and superproviderid=2 group by name ORDER BY name asc', true);
                                       	} else if (!has_capability(Sessions::getID(), 'providers:control_subcontractors')) {
                                       		// can add user below current level, but only in their own institution (e.g. advisor, provider admin, etc).
                                       		$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID from sb_providers where visible=1 and ProviderID=' . Sessions::getUserInfo ('providerid') . ' group by name ORDER BY name asc', true);
                            			} else {
                                    		// new user account creation or super admin...
                                       		$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID from sb_providers where visible=1 group by name ORDER BY name asc', true);
                            			}
                            		?>
                            		<?php if (count ($providers) > 1) {?>
                            		<option value="0">
                                        <?php echo_string ('COMBO_SELECT'); ?>
                                    </option>
                                    <?php } ?>
                                    
                                    <?php 
                                       	$selected = $userInfo['providerid'];
                                       // if (!empty ($valid_ac_code)) {
                                       // 	$selected = $res['providerid'];
                                       // }	
                                        foreach ($providers as $provider) {
                                    ?>
                                    <option value="<?php echo $provider['ProviderID']; ?>" <?php if ($provider['ProviderID'] == $selected) {?>selected="selected"<?php } ?>>
                                        <?php print $provider['name']; ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
                                <span class="red">*</span>
                                <?php } ?>
                            </td>
                            
                            <td >
                            	 <label id="ctr_label" for="centre"><?php echo_string ('CENTRE'); ?>:</label>
                            </td>
                            <td id="blahblah">
                            	<select class="reqd" <?php echo !empty ($valid_ac_code) ? 'disabled="disabled"': ''; ?>name="ctr" id="ctr">
                        
                                    <?php 
                                        
                                       	$centres = DB::runSelectQuery('SELECT name, CentreID from sb_centres where providerid=' . $userInfo['providerid'] . ';');

                                       	$selected = $userInfo['centreid'];
                                       // if (!empty ($valid_ac_code)) {
                                       // 	$selected = $res['providerid'];
                                       // }	
                                        foreach ($centres as $centre) {
                                    ?>
                                    <option value="<?php echo $centre['CentreID']; ?>" <?php if ($centre['CentreID'] == $selected) {?>selected="selected"<?php } ?>>
                                        <?php print $centre['name']; ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
                                 
                               
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <hr />
                            </td>
                        </tr>
                        <tr id="gender_eth_row">
                            <td>
                                <label for="gender"><?php echo_string ('GENDER'); ?>:</label>
                            </td> 
                            <td>
                           
                                <?php echo_string ('MALE'); ?>
                                <input type="radio" name="gender" value="m" <?php if (isset ($userInfo['gender'])) { if ($userInfo['gender'] == 'm') { ?>checked="checked"<?php }}?> />
                                <?php echo_string ('FEMALE'); ?>
                                <input type="radio" name="gender" value="f" <?php if (isset ($userInfo['gender'])) { if ($userInfo['gender'] == 'f') { ?>checked="checked"<?php }}?> />
                                <span class="red">*</span>
                            </td>
                            <td>
                                <label for="ethnicity"><?php echo_string ('ETHNICITY'); ?>:</label>
                            </td>
                            <td>
                                <select class="reqd"  name="ethnicity">
                                    <option value="0">
                                        <?php echo_string ('COMBO_SELECT'); ?>
                                    </option>
                                    <?php 
                                        
                                        $ethns = DB::executeSelect('ethnicity');
                                        $selected = $userInfo['ethnicityid'];
                                        foreach ($ethns as $ethn) {
                                    ?>
                                    <option value="<?php echo $ethn['EthnicityID']; ?>" <?php if ($ethn['EthnicityID'] == $selected) {?>selected="selected"<?php } ?>>
                                        <?php print $ethn['name']; ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
                                <span class="red">*</span>
                            </td>
                        </tr>
                        <tr id="age_row">
                            <td>
                                <label for="agegroup"><?php echo_string ('AGE_GROUP'); ?>:</label>
                            </td>
                            <td>
                                <select class="reqd"  name="agegroup">
                                     <option value="0">
                                        <?php echo_string ('COMBO_SELECT'); ?>
                                     </option>
                                    <?php 
                                      
                                        $ages = DB::executeSelect('age_groups');
                                        $selected = $userInfo['ageid'];
                                        foreach ($ages as $age) {
                                    ?>
                                    <option value="<?php echo $age['AgeID']; ?>" <?php if ($age['AgeID'] == $selected) {?>selected="selected"<?php } ?>>
                                    	<?php print $age['name']; ?>
                                    </option>
                                    <?php     			
                                    }
                                    ?>
                                </select>
                                <span class="red">*</span>
                            </td>
                          
                        </tr>
                        <tr>
                          	<td>
                          
                        		<label for="l_dob">Date of Birth:<br/><span style="font-size: 0.8em;">(dd/mm/yyyy)</span></label>
                        	</td>
                        	<td>
                        		<input type="text" placeholder="dd/mm/yyyy" name="l_dob" id="l_dob" value="<?php echo !empty ($userInfo['dob']) ? mysql2ukdate ($userInfo['dob']) : ''; ?>" />
                        		<span class="red">*</span>
                        	</td>
                        <td colspan="2">
                            	<span id="dob_resp"><!--  <?php echo "Invalid date format! dd/mm/yyyy required."; ?> --></span>
                            </td>
                        </tr>
                    </table>
                   
                   <?php if (has_capability(Sessions::getID(), 'users:edit_profile')) {   
                   	
                   	$roles = getRolesBelow (Sessions::getUserInfo ('roleid'), true);	
                   ?>
                   <hr/>
                   
                   <table cellpadding="5" cellspacing="0" border="0">
                   <tr id="role_row">
                            <td>
                                <label for="role"><?php echo 'Role'; ?>:</label>
                            </td>
                            <td>
                   <?php 
                   			
                   	?>
                   		<select class="reqd" name="role">
                                  
                                    <?php 
                                        
                                     
                                        $selected = $userInfo['roleid'];
                                        foreach ($roles as $role) {
                                    ?>
                                    <option value="<?php echo $role['RoleID']; ?>" <?php if ($role['RoleID'] == $selected) {?>selected="selected"<?php } ?>>
                                    	<?php print $role['name']; ?>
                                    </option>
                                    <?php     			
                                   		 } //foreach
                                    ?>
                        </select>
				  
                 </td>
                 </tr>
                 <?php if (has_capability(Sessions::getID(), 'profile:set_email_notifications')) { ?>
                 <tr>
                 	<td>Email Notifications:</td>
                 	<td><select name="enableemails" id="enableemails">
		<?php 
		$selected = $userInfo['enableemails'];
		?>
				<option value="1" <?php if ($selected == 1) { ?>selected="selected"<?php } ?>>Send emails</option>
				<option value="0" <?php if ($selected == 0) { ?>selected="selected"<?php } ?>>Don't send emails</option>
		</select></td>
                 </tr>
                 <?php } // end profile:set_email_notifications ?>
                 </table>
                  <?php } ?>
                    <?php if ($usecaptcha) { ?>
                    <hr/>
                    <h3><?php echo_string ('CAPTCHA_HEADER'); ?></h3>
                    <p><?php echo_string ('CAPTCHA_STATEMENT'); ?>.</p>
                    <?php echo recaptcha_get_html($publickey,null,true); ?>
					<?php } // if ($usecaptcha) ?>
                    <p>
                        <input type="submit" name="button" id="submitbutton" value="Save" />
                        <?php
                        if (has_capability(Sessions::getID(), 'users:archive_user')) {
                         	if ( ! $userInfo['archived']) {
                        ?>
                       <!-- <input type="submit" onclick="return confirm ('Are you sure you wish to archive this user?');" name="button" id="archivebutton" value="<?php echo 'Archive User'; ?>" />  --> 
                        <?php
							} else {
						?>
                        <!--  <input type="submit" onclick="return confirm ('Are you sure you wish to unarchive this user?');" name="button" id="unarchivebutton" value="<?php echo 'Unarchive User'; ?>" />  --> 	
                        <?php 
							}
                        }
                        ?>
                        <?php if (has_capability(Sessions::getID(), 'users:delete_user')) { ?>
                        <input type="submit" onclick="return confirm ('Are you sure you wish to delete this user?');" name="button" id="deletebutton" value="<?php echo 'Delete User'; ?>" />
                        <?php } ?>
                    </p>
                </form>
          
            </div>
<?php  // displayform if?>
<?php } else { //!Sessions::checkUserLogIn (false)) {
         //echo_string ('LOGGED_IN');
      } ?>
<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>

</body>
</html>
