<?php

/**
 * Library for outputting language strings.
 * 
 * System was mooted to be in both English and Cymraeg, so this was originally programmed in.
 */
/**
 * Debug variable for dumping out information about missing strings on screen
 * All missing strings displayed in double square brackets, e.g. [[I_AM_MISSING]]
 * @var boolean
 */
$stringsrevealmode = false;

function echo_string ($id, $lang='en') {
	global $strings;
	global $stringsrevealmode;
	if (!empty ($strings[$id])) {
		echo $strings[$id];
	} else {
		if ($stringsrevealmode)  {
			echo '[[ ' . $id . ' ]]';
		}
	}
}

/**
 * Replaces a placeholder in a string using sprintf to allow longer and continued strings
 * 
 * Usage:
 * 
 * echo sprint_string ('APP_WELCOME', 'APP_NAME', 'id');
 * echo sprint_string ('APP_WELCOME', 'Soft Skills');
 * 
 * @param string $strid e.g. 'APP_WELCOME'
 * @param string $arg string id or raw string e.g. 'APP_NAME'(id) or 'One Two'(raw string)
 * @param string $type type of replacement - defaults to 'str'. also avail 'id'
 * @return string
 */
function sprint_string ($strid, $arg, $type='str') {
	global $strings;
	global $stringsrevealmode;
	
	// get string (e.g. Welcome to the %s Tool.)
	$org = return_string ($strid);
	
	// check if we are replacing a %s with a custom string or another id
	if ($type=='id') {
		$rep = return_string ($arg);
	} else {
		$rep = $arg;
	}
	return sprintf ($org, $rep);
}

/**
 * Throw out an image.
 * 
 * @param string $id
 * @param string $lang (optional) defaults to 'en' (English)
 */
function echo_image ($id, $lang='en') {
	echo_string ('IMG_' . $id);
}

/**
 * @param string $id
 * @param string $lang (optional) defaults to 'en' (English)
 * @return string
 */
function return_string ($id, $lang='en') {
	global $strings;
	global $stringsrevealmode;
	
	if (!empty ($strings[$id])) {
		return $strings[$id];
	} else {
		if ($stringsrevealmode)  return '[[ ' . $id . ' ]]';
	}
}

/**
 * Throw out a string in caps.
 * 
 * @param string $id
 * @param string $lang (optional) defaults to 'en' (English)
 * @return string
 */
function return_string_upper ($id, $lang='en') {
	global $strings;
	global $stringsrevealmode;
	
	$idtoup = strtoupper ($id);
	return return_string ($idtoup, $lang);
}

/**
 * Which files to include on the page (the files are located in lang/en/ or lang/cy/).
 * Exclude '.php' from filenames at end.
 * 
 * Example: include_strfiles (array ('general', 'questions'));
 * 
 * @param array $cats
 */
function include_strfiles ($cats) {
	
	global $CFG;
	try {
		$lang = Sessions::getLang();
	} catch (Exception $e) {
		$lang = 'en';
	}
	foreach ($cats as $cat) {
		include_once ($CFG->apploc  . '/lang/' . Sessions::getLang() . '/' . $cat . '.php');		
	}
}

//$lang = Sessions::getLang();
$strings = array ();

?>
