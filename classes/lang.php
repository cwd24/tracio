<?php

include_once ("../strings/en.php");

class Lang {

	function __construct() {
	}
	/**
	 * Returns a phrase in given language ('en', 'cy').
	 * 
	 * @param $id identifier of the language string (e.g. "PROVIDER" for "Provider")
	 * @param $lang required language ("cy" or "en"). Defaults to 'en'
	 * @param $file not sure if we need this
	 * @return (string)
	 */
	function get_string ($id, $lang='en', $file='') {
		return ($strings);
	}
}
?>
