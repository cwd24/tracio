<?php

/**
 * Validate the proposed username according to TRaCIO rules.
 * 
 * 6-20 chars length
 * a-Z, A-Z, 0-9 and underscores permitted
 * 
 * @param string $str
 * @return boolean if the loginid conforms to rules]
 * 
 * @version 1.1 ereg has been deprecated in php5.3, so preg_match used instead
 */
function validateUsername($str) {
	if (empty ($str)) return false;
	//return ereg ("^[a-zA-Z_0-9]{6,20}$", $str);
	return preg_match ("/^[a-zA-Z_0-9]{6,20}$/", $str);
}

/**
 * Validate DOB in UK Format (dd/mm/yyyy)
 * 
 * @param mixed $dob Date of birth
 * @return boolean
 */
function validateDOB ($dob) {
	if (empty ($dob)) return false;
	// would possibly be an idea to check the users potential age.
	if (strptime($dob, '%d/%m/%Y')) {
		return true;
	}
	return false;
}

/*
 * For validating firstnames and surnames individually
 * 
 * Allows spaces, hyphens, apostrophes and alphabetical
 * 
 * @param string $name name to validate
 * @param string $type [optional] either 'f' (firstname) or 's' (surname). defaults to 'f'
 * @return boolean  
 * @version 1.1 ereg has been deprecated in php5.3, so preg_match used instead
 */
function validateName ($name, $type='f') {
	if (empty ($name)) return false;
	
	/* 
	 * surname and firstname are now performing same checks
	 * (surname was originally most flexible, but some firstnames have been pointed out
	 * that would have been originally invalid, e.g. D'Arcy.)
	 * but going to keep this structure here in case of changes later on. 
	 */
	
	if ($type=='s') {
		// allow spaces, hyphens and apos
		//return ereg ("^[-a-zA-Z\ \']+$", $name);
		return preg_match ("/^[-a-zA-Z\ \']+$/", $name);
	} else {
		// firstname - allow spaces and hyphens
		//return ereg ("^[-a-zA-Z\ \']+$", $name);
		return preg_match ("/^[-a-zA-Z\ \']+$/", $name);
	}
}

/**
 * Validate string length.
 * 
 * @param string $str string to validate
 * @param int $len required length for validation
 * @return boolean
 */
function validateLength($str, $len){  
    if(strlen($str) < $len) {
         return false;  
    } else { 
         return true;
    }
}  

/**
 * Validate email.
 * 
 * @param string $email email to check.
 * @return boolean if email validates
 * @version 1.1 ereg has been deprecated in php5.3, so preg_match used instead
 * 
 */
function validateEmail($email){
	//return ereg("^[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[@]{1}[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[.]{1}[A-Za-z]{2,5}$", $email);   //depreceated in php 5.3
	return preg_match("/^[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[@]{1}[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[.]{1}[A-Za-z]{2,5}$/", $email);  
} 

/**
 * Validate password for length (greater than 6 characters) and against the second password field.
 * 
 * @param string $pass1 first password field value
 * @param string $pass2 second password field value
 * @return boolean if the passwords are the same and both validate
 */
function validatePasswords($pass1, $pass2) {  
    if(strpos($pass1, ' ') !== false) { 
        return false;  
    } else {
        return $pass1 == $pass2 && validateLength ($pass1, 6);
    }
}  

/**
 * Validate a drop-down selection.
 * 
 * Checks if the drop down isn't on the 'Please Select' option.
 * 
 * @param int $field
 * @return boolean
 * @usedby validateDropDowns()
 */
function validateDropDown ($field) {
	if ($field > 0)	{
		return true;
	}
	return false;
}

/**
 * Validate a number of drop-downs
 * 
 * @param unknown $dropdowns
 * @return boolean
 * @uses validateDropDown()
 */
function validateDropDowns ($dropdowns=array()) {
	foreach ($dropdowns as $dropdown) {
		if (!validateDropDown ($dropdown)) return false;
	}
	return true;
}

/**
 * @param string $message
 * @return boolean
 * @deprecated
 */
function validateMessage ($message){  
    if(strlen($message) < 10) {  
        return false;
    } else {
        return true;  
    }
}

/**
 * @param unknown $m
 * @param unknown $y
 * @return boolean
 * @deprecated
 */
function validateDate ($m, $y) {
	// fmt: - m, d, y
	return checkdate ($m, 1, $y);
}


?>