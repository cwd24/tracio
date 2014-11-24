<?php

/**
 * Handles session and login info for users.
 * 
 * Few helper functions in here too.
 * @version 1.1
 *
 */
class Sessions {

	static $userid = '';
	
	/**
	 * Empty construct for php compatibility
	 */
	function __construct () {

	}
	
	/**
	 * Start sessions for page if not already running
	 */
	static public function startSessions () {
		if (!session_id()) {
			session_start ();
		}
	}
	
	/**
	 * Helper function which returns all $_GET variables in urlencoded format.
	 * 
	 * Used in redirect following login
	 * 
	 * (e.g. if a user attempts to load a URL with querystring or something and they aren't logged in).
	 * 
	 * @param boolean $first do we need to attach a ? character to the start of the query string output
	 * @return string urlencoded $_GET array
	 * @usedby checkUserLogIn()
	 */
	private function implode_get($first=false) {
		// turns $_GET array into name value pairs string
		// used in header redirect
		$output = '';

		if (!empty ($_GET)) {
			foreach ($_GET as $key => $value) {
				// ignore 'return' value as we don't need to send it
				if ($key != "return") {
					if ($first) {
						$output = '?'. $key . '=' . $value;
						$first = false;
					} else {
						$output .= '&' . $key . '=' . $value;
					}
				}
			}
		}
		return urlencode ($output);
	}
	
	/**
	 * Is user logged in?
	 * 
	 * @param boolean $redirect whether to send user to login screen if they aren't logged in
	 * @return boolean
	 * @uses implode_get()
	 */
	static public function checkUserLogIn ($redirect=true) {
		global $CFG;
		
		// check for maintenance mode as a priority and prevent access
		if ($CFG->inMaintenanceMode) {
			Sessions::logout ();
			echo "TRaCIO is currently under maintenance and is unavailable.";
			die ();
		}
		
		if (Sessions::getID()) {
			return true;
		}
		
		// if we want them to log in, we can send them to the login screen with all relevant
		// querystrings maintained
		if ($redirect) {
		    header('Location: ' . $CFG->apphttp  . '/login.php?return='. $_SERVER['PHP_SELF'] . Sessions::implode_get (true));
		}
		// if we just wanted to check, but not redirect them to login screen, return false
		return false;
	}
	
	/**
	 * Return user id of logged in user
	 * 
	 * @return int|boolean userid as an int or false if not set
	 */
	static public function getID () {
		Sessions::startSessions();
		if (!empty ($_SESSION['userid'])) {
			return $_SESSION['userid'];
		} else {
			// user is not logged in so they should be forced to redirect
			// Sessions::checkUserLogIn (false);
			return false;
		}
	}

	/**
	 * Store the users ID as a session variable.
	 * 
	 * @param int $userid
	 */
	static public function setID ($userid) {
		Sessions::startSessions();
		$_SESSION['userid'] = $userid;
	}
	
	/**
	 * Fetch field(s) from the users_info table and return them
	 * 
	 * e.g. Sessions::getUserInfo('providerid');
	 * e.g. Sessions::getUserInfo('roleid');
	 * 
	 * @param mixed $field array of fields, or singular string
	 * @return mixed|string array of fields or singular string
	 */
	static function getUserInfo ($field) {
		$userid = Sessions::getID ();
		$res = DB::executeSelect('users_info', array ($field), array ('UserID'=>$userid));
		return ($res[$field]);
	}
	
	/**
	 * Logout user and remove session info.
	 */
	static function logout () {
		Sessions::startSessions();
		unset ($_SESSION['userid']);
	}
	
	/**
	 * Return language of logged in user for multi-lingual interface.
	 * 
	 * This function is a stub which currently just returns 'en',
	 * until the bilingual development of TRaCIO begins.
	 * 
	 * @return string
	 * @usedby lib/strings.php
	 */
	static function getLang () {
		Sessions::startSessions();
		$_SESSION['lang'] = 'en';
		return 'en';
	}
	
	/**
	 * 
	 * @return bool - true if user logged in, false if not
	 * @deprecated
	 */
	static function loginNotRequired () {
		Sessions::startSessions ();
		// use english as default and don't force a login
		if (Sessions::checkUserLogIn (false)) {
			return true;
		} else {
			$_SESSION['lang'] = 'en';
			$_SESSION['userid'] = '';
			return false;
		}
	}
	
	/**
	 * Return the super provider for a given provider
	 * 
	 * @param int $providerid
	 * @return int|boolean id of superprovider or false for failure if not found
	 * @usedby admin/users.php
	 * @usedby lib/roles.php
	 * @usedby reports.php
	 */
	static function getSuperProviderID ($providerid) {
		Sessions::startSessions ();
		$res = DB::executeSelect ('super_providers', 'SuperProviderID', array ('providerid'=>$providerid));
		return $res['SuperProviderID'];
	}
	
	/**
	 * Store which provider a user has been interacting with on drop-downs and filtering.
	 * 
	 * This enables us to later retrieve this info and set drop-downs back to this.
	 * 
	 * @param unknown $providerid
	 * @usedby users.php
	 */
	static function setLastProviderWorkedOn ($providerid) {
		Sessions::startSessions ();
		$_SESSION['lastprovider'] = $providerid;
	}
	
	/**
	 * Retrieve which provider a user has been interacting with on drop-downs and filtering.
	 *
	 * This enables us to set drop-downs back to this provider.
	 * 
	 * @usedby users.php
	 */
	static function getLastProviderWorkedOn () {
		Sessions::startSessions();
		return $_SESSION['lastprovider'];
	}

}

Sessions::startSessions ();
?>
