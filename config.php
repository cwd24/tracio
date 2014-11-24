<?php

unset ($CFG);
$CFG = new stdClass ();

if (!isset ($CFG->debug)) {
	$CFG->debug = 1;
}

	if ($CFG->debug == 1) {
		ini_set ("display_errors", 1);
		error_reporting (E_ERROR); // & ~E_NOTICE); // E_ALL
		//error_reporting (E_ALL);
	} else {
		error_reporting (0);
	}
	
	$CFG->jquery_ui_version = '1.9.2';
	$CFG->jquery_version = '1.8.3';
	
	$CFG->inMaintenanceMode = false;
	$CFG->emailsEnabled = true;
	$CFG->emailSender = 'TRaCIO <tracio@rsc-wales.ac.uk>';
	// generic vars
  	$CFG->apploc =  $_SERVER["DOCUMENT_ROOT"];
  	
  	
  	// waiting for sub domain
    $CFG->apphttp = '';
    $CFG->fullhttp = 'https://tracio.rsc-wales.ac.uk' . $CFG->apphttp;
    
    
   	// pChart configs
   	$CFG->pchart = 'external/pChart/';
    $CFG->pchartloc = $CFG->apploc . $CFG->pchart;
    $CFG->pchartsrc = $CFG->pchartloc . 'src/';
    $CFG->pcharttmp = $CFG->apploc . '/tmp';
    $CFG->pcharttmphttp = $CFG->apphttp . '/tmp';
    
    // recaptcha
    // Get a key from http://recaptcha.net/api/getkey
	$CFG->recaptcha_public = 'CHANGEME';
	$CFG->recaptcha_private = 'CHANGEME';
	
	// var for development only - for internal vs external (rsc) use and email handling
	$CFG->useSMTP = true;
	
	// styles vars
	$CFG->imagefolder = $CFG->apphttp . '/images';
	$CFG->cssfile = $CFG->apphttp . '/styles.css';
	$CFG->printcssfile = $CFG->apphttp . '/print.css';
	
	// BETA VARS
	$CFG->allowCentreRemoval = true;
	//// LEGACY VARS - all below are deprecated ////
	
	/// following added for legacy - deprecated (use $CFG->cssfile instead)
	$CFG->cssfile = $CFG->cssfile;
	

	// legacy variable - now retrieved from strings.
    $CFG->appname = 'TRaCIO v2';
    
    $CFG->relhttploc =  $CFG->apphttp;
	$CFG->globalDateFmt = "F j, Y, g:i a";   
		
    //include_once ($CFG->apploc . '/templates/config.php');
    //TODO - remove include for templates on other pages.
    
	// update 2012-12-18 learnerroleid changed
     $CFG->learnerroleid = 50;
     // update 2012-12-18 advisorroleid added
     $CFG->advisorroleid = 40;
     // update 2013-01-07 added
     $CFG->defaultPaginationSize = 20;
     
     $CFG->tmploc = $CFG->apploc . '/../tmp';

     $CFG->defaultpassword = '3 red cars';
     
     // set default timezone
     ini_set ("date.timezone", "Europe/London");
     
?>
