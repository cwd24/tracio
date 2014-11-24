<?php

include_once ('config.php');
include_once ($CFG->apploc  . '/lib/activity.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/lib/validation.php');

//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('general', 'user'));

if (!empty ($_GET['logout'])) {
	Sessions::logout ();
	// send user back to splashscreen
	header('Location: index.php?logout=true');
}

$loggedin = Sessions::getID ();

if (!$loggedin) {
	if (!empty ($_POST)) {
		if (!empty ($_POST['uname']) && !empty ($_POST['pword'])) {
			// check if they have sent an email address OR a username
			// assume default as 'loginid'/username
			$fieldtocheck = 'loginid';
			// check if it is email format...
			if (validateEmail($_POST['uname'])) {
				$fieldtocheck = 'email';
			}
			$attempt = DB::executeSelect('users_info', array ('UserID'), array (
			$fieldtocheck => $_POST['uname'],
			'password'=> md5 ($_POST['pword'])	
			));
		
		if ($attempt) {
			Sessions::setID($attempt['UserID']);
		
			$loggedin = true;
			
			header('Location: home.php?login=true');
		} else {
			//
			
			$loginfailed = true;
			
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
<title><?php echo_string ('APP_NAME'); ?> : <?php echo_string ('LOGIN_TITLE'); ?></title>

<link href="<?php echo ($CFG->cssfile); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo ($CFG->printcssfile); ?>" rel="stylesheet" type="text/css" media="print"/>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/<?php echo $CFG->jquery_version; ?>/jquery.min.js"></script>
<script type="text/javascript" src="js/resizer.js"></script>
</head>
<body>
<?php include_once ($CFG->apploc  . '/templates/header.php'); ?>

<?php if (!$loggedin) { ?>
<h2><?php echo_string ('LOGIN_TITLE'); ?></h2>
<div class="annot">
<p>Enter your username or email address along with your password, then click the 'Login' button to confirm.</p>
</div>
<?php if (!empty ($loginfailed)) { ?>

<div class="alert warning"><?php echo_string ('LOGIN_FAIL'); ?></div><div style="clear: both;"></div><br/>

<?php } ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table>
	<tr>
		<td><label for="uname"><?php echo_string ('USERNAME_OR_EMAIL'); ?> :</label></td>
		<td><input type="text" id="uname" name="uname" value="<?php if (!empty ($loginfailed)) { echo $_POST['uname']; } else if (!empty ($_GET['nu'])) { echo $_GET['nu']; } ?>" /></td>
	</tr>
	<tr>
		<td><label for="pword"><?php echo_string ('PASSWORD'); ?>:</label></td>
		<td><input type="password" name="pword" id="pword" /></td>
	</tr>
</table>
 <br/>

<div class="subannot_two"></div>
<div class="subannot">
<p><?php echo sprint_string('USER_SIGNUP', 'user_signup.php'); ?>.</p>
</div>


<input type="hidden" name="redirect" value="<?php echo urldecode ($_GET['return']); ?>" />
<input type="submit" value="<?php echo_string ('LOGIN_BTN'); ?>"/>

</form>
<?php } else { 
	// if user logged in and redirect set in query string, then provide link to originally required page
 if (!empty ($_GET['return'])) {
	 echo 'You are logged in. <a href="' . $CFG->apphttp  .  ($_GET['return']) . '">Click here to continue to your destination...</a>';
 } else {
echo_string ('LOGIN_SUCCESS'); 
}?>

<?php } ?>

<?php include_once ($CFG->apploc  . '/templates/footer.php'); ?>
</body>
</html>


