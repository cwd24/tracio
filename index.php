<?php

include_once ('config.php');
include_once ($CFG->apploc  . '/lib/activity.php');
include_once ($CFG->apploc  . '/classes/sessions.php');
include_once ($CFG->apploc  . '/lib/roles.php');



//strings code
include_once ($CFG->apploc  . '/lib/strings.php');
include_strfiles(array ('general', 'question'));

if (Sessions::checkUserLogIn (false)) {
	header ('Location: home.php');
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

<link href="external/tablesorter/themes/blue/style.css" rel="stylesheet" type="text/css" />


</head>
<body>

<div style="width:900px; margin:50px auto; text-align:right">
    <img src="<?php echo ($CFG->apphttp); ?>/images/splash_screen1.png" alt="TRaCIO Splash" />
    <a href="about.php"><img src="<?php echo ($CFG->apphttp); ?>/images/about.png" style="vertical-align: middle" alt="About TRaCIO" /></a>
    <a href="login.php"><img src="<?php echo ($CFG->apphttp); ?>/images/login.png" style="vertical-align: middle" alt="Login to TRaCIO" /></a>
    <a href="user_signup.php"><img src="<?php echo ($CFG->apphttp); ?>/images/register.png" style="vertical-align: middle" alt="Register as a new TRaCIO user" /></a>
  
</div>

</body>
</html>
    
    
    
