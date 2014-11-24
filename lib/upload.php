<?php

/**
 * library for CSV uploads
 * @usedby uploads.php
 * 
 */
//error_reporting (E_ALL);
//ini_set ('display_errors', '1');

/**
 * Check that we have a suitable file in the $_FILES.
 * Called directly from admin/upload_users.php.
 * 
 * $errors global used for storing errors or problems.
 * 
 * @return boolean|string returns false for failure of operation or the files name and location if successful.
 * @usedby admin/upload_users.php
 */
function upload_file () {
	/**
	 * stores error strings for output by admin/upload_users.php page
	 * @var array
	 */
	global $errors;
	global $CFG;						// uses $CFG->tmploc (temporary upload location)
	global $_FILES;
	
	if((!empty($_FILES["uploaded_file"])) && ($_FILES['uploaded_file']['error'] == 0)) {
	  // Check if the file is JPEG image and it's size is less than 350Kb
	  $filename = basename($_FILES['uploaded_file']['name']);
	  $ext = substr($filename, strrpos($filename, '.') + 1);
	  if (($ext == "csv") && ($_FILES["uploaded_file"]["type"] == "text/csv") && ($_FILES["uploaded_file"]["size"] < 350000)) {
	      // Determine the path to which we want to save this file
	      $newname = $CFG->tmploc . '/' . uniqid() . '_' . $filename ;
	      // Check if the file with the same name is already exists on the server
	      if (!file_exists($newname)) {
	        // Attempt to move the uploaded file to it's new place
	        if ((move_uploaded_file($_FILES['uploaded_file']['tmp_name'],$newname))) {
	           return $newname;
	        } else {
	           $errors[] = "A problem occurred during file upload!";
	           return false;
	        }
	      } else {
	         $errors[] = "File " . $_FILES["uploaded_file"]["name"] . " already exists";
	         return false;
	      }
	  } else {
	  	// detect actual error to show user
	  	if ($ext != "csv") {
	  		$errors[] = "File extension is $ext. It must be a .csv file for upload.";
	  	}
	  	if (($_FILES["uploaded_file"]["type"] != "text/csv")) {
	  		$errors[] = "File uploaded has the type " . $_FILES['uploaded_file']['type'] . ". Should be a text/csv file.";
	  	}
	  	if (($_FILES["uploaded_file"]["size"] < 350000)) {
	  		$errors[] = "File exceeds upload limit size. Only files under 350Kb are accepted for upload.";
	  	}
	    return false;
	  }
	} else {
	 	$errors[] = "No file uploaded";
	 	return false;
	}
	return false;
}

/**
 * Check if fields in uploaded CSV match the ones we are expecting (ie as detailed in the csv template)
 * 
 * @param array $array1
 * @param array $array2
 * @return boolean match true/false
 * @usedby parse_csv()
 */
function diff_arrays ($array1, $array2) {
	// check length of two arrays in first instance
	if (count ($array1) != count ($array2)) {
		// array lengths don't match so return false straight away
		return false;
	}
	// secondly compare field names against each other
	for ($i=0; $i<count ($array1); $i++) {
		if ($array1[$i] != $array2[$i]) {
			return false;	
		}
	}
	// all good and matched
	return true;
}

/**
 * Parse uploaded CSV to ensure it is structurally sound in first instance,
 * and then grab the data and store it for further checks (against db) and, if all good,
 * pass the info on to the next part of the CSV import process.
 * 
 * $errors global used for storing errors or problems.
 * 
 * @param unknown $fileloc
 * @return boolean|array false for failure or array of data
 * @uses diff_arrays()
 * @usedby admin/upload_users.php
 */
function parse_csv ($fileloc) {
	/**
	 * stores error strings for output by admin/upload_users.php page
	 * @var array
	 */
	global $errors;
	
	/**
	 * Stores the required fields of the uploaded CSV file in the anticipated order.
	 * 
	 * @var array
	 */
	$expectedfields = array ('loginid', 'fname', 'sname', 'email', 'dob', 'ethnicityid', 'gender', 'ageid', 'centreid');
	$storage = array ();	
	$fields = array ();
	$row = 0;

	// setting this to deal with awkward line endings (including mac excel line) and other inconsistencies...!
	// without this some csv files created can't be read by fgetcsv correctly.
	ini_set ("auto_detect_line_endings", "1");

	if (($handle = fopen($fileloc, "r")) !== FALSE) {
    	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    	
    	$temparray = array ();
    	
        $num = count($data);
        if ($row >=1 ) {
        	if ($num != $fieldscount) {
        		$errors [] =  "Number of columns in row #$row ($num columns) does not match the number of required fields ($fieldscount fields).";
        		//uunlink ($fileloc);
        		return false;
        	}
        }
   
        $row++;
        for ($c=0; $c < $num; $c++) {
        	if ($row == 1) {
        		// get field names
        		$fields [] = $data[$c];
        	} else {
        		$temparray[$fields[$c]] = $data[$c];
        	}

        }
        if ($row > 1) {
        	$storage [] = $temparray;
        } else {
        	// compare array against expected
        	if (diff_arrays ($expectedfields, $fields)) {
        		$fieldscount = count($fields);
        	} else {
        		$errors[] = "CSV columns mismatch! Columns in CSV don't match required columns.";
        		//uunlink ($fileloc);
        		return false;
        	}
        }
    }
    fclose($handle);
    return ($storage);
	}
}

/**
 * check the csv file array for duplicate values, e.g. loginid, email (used on csv import).
 * 
 * This is is used to check that the user hasn't put in the same information in the CSV file twice,
 * such as a repeat row. Just checking the human aspects first, as the db will throw an error
 * later on anyway if it finds duplicates, so we're just giving them a heads up.
 * 
 * @param array $array the array in which to check for the duplicates... followed by...
 * @param string $assocfield the field in the associate array to check for uniquness on (e.g. loginid, email)
 * @usedby admin/upload_users.php
 * @returns boolean
 */
function noDuplicatesInCSVForField ($array, $assocfield) {
	$temparray = array ();
	// create an array with all the field values in 
	foreach ($array as $row) {
		$temparray[] = strtolower ($row[$assocfield]);
	}
	// if there is data in the array...
	if (count($temparray) > 0) {		
		// check lengths of uniqueified array and original arrays to see if there is any inconsistency.
		if (count (array_unique($temparray)) == count ($temparray)) {
			// arrays are the same length, so that means that there are no dupes
			return true;
		}
	}
	// array lengths are inconsistent (i.e. array_unique has removed some dupes) OR array is empty!
	return false;
}

/**
 * Does unlink($file) but reports errors.
 * 
 * @param string $file filename and location.
 */
function uunlink ($file) {
	$res = unlink ($file);
	// log failures?
//	if ($res) { 
	  //echo $file . " REMOVED";
//	} else {
//		echo "Can't remove " . $file;
//	}  
}
?>
