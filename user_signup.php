<?php
include_once ('./config.php');
include_once ($CFG->apploc . '/db_connect.php');
include_once ($CFG->apploc . '/classes/db.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
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

$usecaptcha = true;
$captchaOK = false;

$displayform = true;
?>
<?php

$action = 0;

// default signup type is learner
$signuprole = $CFG->learnerroleid;
$res = false;
                // check for advisor type registration
                if (isset ($_GET['ac'])) {
                		// we have an activation code in querystring, now to check if it is live.
                		$res = DB::executeSelect('activations', '*', array('activationcode'=>$_GET['ac'], 'activated'=>0), '', 1);
                		
                		if ($res) {
                			$valid_ac_code = true;
                		}
                		
                			
                } 
                
                if (isset ($_GET['action'])) {
                	$action = $_GET['action'];
                	if ($action == 'adduser') {
                		// check capabilities...
                		Sessions::checkUserLogIn ();
                		
						if (!has_capability(Sessions::getID(), 'users:add_user')) {
							die (return_string ('ACCESS_DENIED'));
						}
						
						$usecaptcha = false;
						$captchaOK = true;
                	}
                }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo_string ('APP_NAME'); ?>: <?php echo_string ('ADD_USER'); ?></title>
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
	   		if (!$("#pass_1").attr ('value')) {
	   			$("#pass_1").css({"background-color":"#FFCCCC"});
	   			res = false;
	   		} else {
	   			$("#pass_1").css({"background-color":"#FFFFFF"});
	   		}
	   		if (!$("#pass_2").attr ('value')) {
	   			$("#pass_2").css({"background-color":"#FFCCCC"});
	   			res = false;
	   		} else {
	   			$("#pass_2").css({"background-color":"#FFFFFF"});
	   		}
	   		if (!$('#l_dob').attr ('value')) {
	   			$("#l_dob").css({"background-color":"#FFCCCC"});
	   			res = false;
	   		} else {
	   			$("#l_dob").css({"background-color":"#FFFFFF"});
	   		}
	   		if (dropdownsMandatory) {
				var dropdowns = $('#form1 select');
				for (i=0; i<dropdowns.length; i++) {				
					if ($(dropdowns[i]).attr ('value') == "0") {
						$(dropdowns[i]).css ({"color":"red"});
						//TODO - provide text colouring for safari/chrome.
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

		$('#form1').submit (function () {
			return validateForm();
		}); 
		$('#provider').change (function (e) {
			// get centres/advisors given provider
			if ($(this).val () > 0) {
				
					$.ajax({
					  url: 'ajax/getdata.php',
					  type: "POST",
					  data: {provider: $(this).val (), q:'centres', newuser:'1'},
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

		$('#ctr').hide ();
		$('#ctr_label').hide ();
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
					
		checkemail ();

$('#provider').trigger('change');

		
});//jquery end
</script>
</head>

<body>  

<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>
<h2><?php echo_string ('USER_SIGNUP_TITLE'); ?></h2>



<?php 

if (!Sessions::checkUserLogIn (false) || $action == 'adduser') { ?>

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
		if ($_POST['action'] == 'create') {
		
			$emailMandatory = false;
			$dropdownsMandatory = true;
			$genderMandatory = true;
		
			if (!empty ($_POST['adv_reg'])) {
				$activ_check = DB::executeSelect('activations', '*', array ('activationcode'=>$_POST['ac_code'], 'issuedate'=>$_POST['ts']), '', 1);
				if ($activ_check) {
					//activation success
					$dropdownsMandatory = false;
					$genderMandatory = false;
				} else {
				//activation check failed!
				}
			} //if (!empty ($_POST['adv_reg'])) {
		
			$captchaOn = $usecaptcha;
			// check captcha!
			if ($_POST["recaptcha_response_field"] && $captchaOn) {
	
	        	$resp = recaptcha_check_answer ($privatekey,
	                                        $_SERVER["REMOTE_ADDR"],
	                                        $_POST["recaptcha_challenge_field"],
	                                        $_POST["recaptcha_response_field"]);
	
	        	if ($resp->is_valid) {
	        		$captchaOK = true;
	        	
	        	} else {
	        		$captchaOK = false;
	        		
	        	}
			} else if (!$usecaptcha) {
				$captchaOK = true;	
			}
			
			$formaction = 'create';
			
			// do some validation checks
			$v_fname = validateName ($_POST['f_name'], 'f');
			$v_sname = validateName ($_POST['s_name'], 's');
			$v_username = validateUsername($_POST['l_id']);
			$v_dob = validateDOB ($_POST['l_dob']);

			$v_usernametaken = false;
			if ($v_username) {
			
				$v_usernametaken = DB::executeSelect('users_info', '*', array ('loginid'=>$_POST['l_id']));
				
			}
			
			if (!$emailMandatory) {
				$v_email = true;	
				$v_emailtaken = false;
			} else {
				$v_email = validateEmail($_POST['l_email']);
				$v_emailtaken = false;
				if ($v_email) {
					$v_emailtaken = DB::executeSelect('users_info', '*', array ('email'=>$_POST['l_email']));
				
				}
			}
			
			$v_pass = validatePasswords($_POST['pass_1'], $_POST['pass_2']);
			if ($genderMandatory) {
				$v_gender = !empty ($_POST['gender']);
			} else {
				$v_gender = true;
			}
			$v_sdate = true; //validateDate ($_POST['start_m'], $_POST['start_y']);
			$v_edate = true; //validateDate ($_POST['end_m'], $_POST['end_y']);
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
				if (!$v_dob) $errorstr.= '<li>Date of birth invalid</li>';
				if (!$v_sdate) $errorstr.= '<li>No start date</li>';
				if (!$v_edate) $errorstr.= '<li>No end date</li>';
				if (!$v_dropdowns) $errorstr.= '<li>One or more dropdowns incomplete</li>';
				$errorstr.= '</ul>';
			}
		
			if ($saveflag) {
				// - does user already exist? check first
				if (!DB::executeSelect('users_info', '*', array ('loginid'=>$_POST['l_id']))) {
					// creating a new user
					
					// update 2012-12-18 learnerrole id is pushed from cfg file, rather than hardcoded in 'roleid' below
					$q = DB::executeInsert('users_info',
						array (
						'providerid'	=> $_POST['provider'],
						'loginid' 		=> strtolower ($_POST['l_id']),
						'fname' 		=> ucname ($_POST['f_name']),
						'sname' 		=> ucname ($_POST['s_name']),
						'roleid' 		=> !empty ($_POST['role']) ? $_POST['role'] : $CFG->learnerroleid,
						'password' 		=> md5($_POST['pass_1']),
						'advisorid'		=> 0,
						'email'			=> $_POST['l_email'],
						'ethnicityid'	=> $_POST['ethnicity'],
						'gender'		=> $_POST['gender'],
						'ageid'   		=> $_POST['agegroup'],
						'groupid'		=> 1,
						'programmeid'	=> $_POST['prg'],
						'completed'		=> 0,
						'centreid'		=> $_POST['ctr'],
						'dob'			=> ukdate2mysql( $_POST['l_dob'])
						));
					if ($q) {
						echo 'New user "' . $_POST['l_id'] . '" added.';
						if (isset ($_POST['adduser'])) {
							// email info to user?
							if (isset ($_POST['emaildetails'])) {
								
								
								$headers = 'MIME-Version: 1.0' . "\r\n";
								$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
								$headers .= 'From: ' . $CFG->emailSender . "\r\n";
								
								$body = '<html><head><title>TRaCIO Invitation</title></head>';
								$body .= '<body style="background-color: #FFFFFF;">';
								$body .= '		<h2>TRaCIO Invitation</h2>';
								$body .= '		<p>' . $_POST['f_name'] . ' ' . $_POST['s_name'] . ',</p>';
								$body .= '		<p>A user account has been created for you on TRaCIO.</p>';
								$body .= '		<p>You may access TRaCIO <a href="' . $CFG->fullhttp . '">here</a>.</p>';
								$body .= '		<p>You may login using your username or email address.</p>';
								$body .= '		<p>Your username is: ' . strtolower ($_POST['l_id']) . '</p>';
								$body .= '		<p>Your password is: ' . $_POST['pass_1'] . '</p>';
								$body .= '		<p>You may change your password once you log in.</p>';
								$body .= '		</body>';
								$body .= '	</html>';
					
								if ($CFG->emailsEnabled) {
									$emailsuccess = mail ($_POST['l_email'], 'TRaCIO Invitation', $body, $headers);
									
								} else {
									echo 'EMAIL PREVIEW:<br/><br/>';
									echo $body;	
								}
							}
						} else {
							// if the user has created themselves, give them login link...
							echo '<a href="login.php?nu='. $_POST['l_id'] . '">Click here to login.</a>';
						}
						$displayform = false;
						// if user has used activation code, change it's status!
						if (!empty ($_POST['adv_reg'])) {
							// change status!
							$chg = DB::executeUpdate('activations', array ('activated'=>1), array ('activationcode'=>$_POST['ac_code']));
							if ($chg) {
							//	echo "success";
							} else {
							//	echo 'not successful';	
							}
						}
						
					} else {
						
						echo 'User not added. MySQL Error: ' . mysql_error();
					}
				}  else {
					echo 'A user with the loginid "' . $_POST['l_id'] . '" already exists. Click the <strong>Back</strong> button to select another login id.';
				}
			} else { // if ($saveflag)
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
            <div>
                <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; echo !empty ($_GET['ac']) ? '?ac=' . $_GET['ac']:''; echo !empty ($_GET['action']) ? '?action=' . $_GET['action']:''; ?>">
             <?php
             if (!empty ($valid_ac_code)) {
             	?>
             	<input type="hidden" name="adv_reg" value="1" />
             	<input type="hidden" name="ac_code" value="<?php echo $res['activationcode']; ?>" />
             	<input type="hidden" name="ts" value="<?php echo $res['issuedate']; ?>" />
             	<input type="hidden" name="role" value="<?php echo $res['roleid']; ?>" />
             	<?php 
             	
             }	// if (!empty ($valid_ac_code))
             
             if ($action == 'adduser') {
             ?>
             	<input type="hidden" name="adduser" value="1" />
             <?php	
             }
            
             ?>
                	<input type="hidden" name="prg" value="1" />
                	<input type="hidden" name="action" value="create" />
                    <table cellpadding="5" cellspacing="0" border="0">
                        <tr>
                            <td>
                                <label for="f_name"><?php echo_string ('FN'); ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="f_name" id="f_name" value="<?php echo !empty ($_POST['f_name']) ? $_POST['f_name'] : ''; ?>"/>
                                <span class="red">*</span>
                            </td>
                             <td>
                                <label for="s_name"><?php echo_string ('SN'); ?>:</label>
                            </td>
                            <td>
                                <input type="text" name="s_name" id="s_name" value="<?php echo !empty ($_POST['s_name']) ? $_POST['s_name'] : ''; ?>" />
                                <span class="red">*</span>
                            </td>
                         </tr>
                         <tr>
                        	<td>
                        		<label for="l_email"><?php echo_string ('EMAIL'); ?>:</label>
                        	</td>
                        	<td>
                        		<input type="text" name="l_email" id="l_email" value="<?php echo !empty ($_POST['l_email']) ? $_POST['l_email'] : ''; ?>"/>
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
                                <input type="text" name="l_id" id="l_id" value="<?php echo !empty ($_POST['l_id']) ? $_POST['l_id'] : ''; ?>"/>
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
                                <input type="password" name="pass_1" id="pass_1" />
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
                                <input type="password" name="pass_2" id="pass_2" />
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
                            		$providers = DB::runSelectQuery ('SELECT name, ProviderID from sb_providers where visible=1 and ProviderID=' . $res['providerid'] );	
                                ?>
                                	<input type="text" disabled="disabled" value="<?php echo $providers['name']; ?>" />
                                	<input type="hidden" name="provider" id="provider" value="<?php echo $providers['ProviderID']; ?>" />
                                <?php 
                            	} else {
                            		
                            	?>
                                <select <?php echo !empty ($valid_ac_code) ? 'disabled="disabled"': ''; ?>name="provider" id="provider">
                                    
                                    <?php 
                                        
                                        //
                                        if ($action == 'adduser' && has_capability(Sessions::getID(), 'providers:control_all') && Sessions::checkUserLogIn (false)) {
                                        	$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID from sb_providers where visible=1 group by name ORDER BY name asc', true);
                                        } else if ($action == 'adduser' && has_capability(Sessions::getID(), 'providers:control_subcontractors') && Sessions::checkUserLogIn (false)) {
                                    		$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID from sb_providers where visible=1 and superproviderid=2 group by name ORDER BY name asc', true);
                                        } else if ($action == 'adduser' && !has_capability(Sessions::getID(), 'providers:control_subcontractors') && Sessions::checkUserLogIn (false)) {
                                       		// can add user below current level, but only in their own institution (e.g. advisor, provider admin, etc).
                                       		$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID from sb_providers where visible=1 and ProviderID=' . Sessions::getUserInfo ('providerid') . ' group by name ORDER BY name asc', true);
                                        } else {
                                    		// new user account creation
                                       		$providers = DB::runSelectQuery('SELECT DISTINCT(name), ProviderID from sb_providers where visible=1 group by name ORDER BY name asc;', true);
                            			}
                            		?>
                            		
                            		<?php if (count ($providers) > 1) {?>
                            		<option value="0">
                                        <?php echo_string ('COMBO_SELECT'); ?>
                                    </option>
                                    <?php } ?>
                                    <?php 
                                       	$selected = $_POST['provider'];
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
                            
                            <td style="visibility: false;">
                            	 <label id="ctr_label" for="centre"><?php echo_string ('CENTRE'); ?>:</label>
                            </td>
                            <td id="blahblah">
                            	<!--  <select name="ctr" id="ctr">
                                    <option value="0">
                                        <?php echo_string ('COMBO_SELECT'); ?>
                                    </option>
                                     </select>
                                -->
                                 
                               
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
                                <input type="radio" name="gender" value="m" <?php if (isset ($_POST['gender'])) { if ($_POST['gender'] == 'm') { ?>checked="checked"<?php }}?> />
                                <?php echo_string ('FEMALE'); ?>
                                <input type="radio" name="gender" value="f" <?php if (isset ($_POST['gender'])) { if ($_POST['gender'] == 'f') { ?>checked="checked"<?php }}?> />
                                <span class="red">*</span>
                            </td>
                            <td>
                                <label for="ethnicity"><?php echo_string ('ETHNICITY'); ?>:</label>
                            </td>
                            <td>
                                <select name="ethnicity">
                                    <option value="0">
                                        <?php echo_string ('COMBO_SELECT'); ?>
                                    </option>
                                    <?php 
                                       
                                        $ethns = DB::executeSelect('ethnicity');
                                        $selected = $_POST['ethnicity'];
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
                                <select name="agegroup">
                                     <option value="0">
                                        <?php echo_string ('COMBO_SELECT'); ?>
                                     </option>
                                    <?php 
                                        //TODO - getProviders func?
                                        $ages = DB::executeSelect('age_groups');
                                        $selected = $_POST['agegroup'];
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
                        		<input type="text" placeholder="dd/mm/yyyy" name="l_dob" id="l_dob" value="<?php echo !empty ($_POST['l_dob']) ? $_POST['l_dob'] : ''; ?>"/>
                        		<span class="red">*</span>
                        	</td>
                        <td colspan="2">
                            	<span id="dob_resp"><!--  <?php echo "Invalid date format! dd/mm/yyyy required."; ?> --></span>
                            </td>
                        </tr>
                    </table>
                   
                   <?php if (has_capability(Sessions::getID(), 'users:add_user')) {   
                   	
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
                   		<select name="role">
                                  
                                    <?php 
                                        
                                     
                                        $selected = $_POST['role'];
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
                 <tr>
                 <td>
                 	Check box to send email notification to new user:
                 </td>
                 <td>
                
                 <input type="checkbox" <?php if (isset ($_POST['emaildetails'])) { ?> checked="checked"<?php }?>name="emaildetails" />
                 </td>
                 </tr>
                 </table>
                  <?php } ?>
                    <?php if ($usecaptcha) { ?>
                    <hr/>
                    <h3><?php echo_string ('CAPTCHA_HEADER'); ?></h3>
                    <p><?php echo_string ('CAPTCHA_STATEMENT'); ?>.</p>
                    <?php echo recaptcha_get_html($publickey,null,true); ?>
					<?php } // if ($usecaptcha) ?>
                    <p>
                        <input type="submit" name="button" id="submitbutton" value="<?php echo_string ('SUBMIT'); ?>" />
                    </p>
                </form>
            </div>
<?php } // displayform if?>
<?php } else { //!Sessions::checkUserLogIn (false)) {
         echo_string ('LOGGED_IN');
      } ?>
<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>

</body>
</html>
