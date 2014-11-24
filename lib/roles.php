<?php

/**
 * roles library.
 * 
 * functions to deal with capabilities and access throughout tracio
 * 
 * @version 1.0
 */

/**
 * 
 * Return true or false if user has a particular capability given a string id.
 * 
 * @param int $userid
 * @param string $capabilityid string id of capability (e.g. 'sit_learner_activity').
 * @return boolean
 */
function has_capability ($userid, $capabilityid) {
	$res = DB::runSelectQuery('	SELECT sb_roles_capabilities_assigment.allow
								FROM sb_users_info
								INNER JOIN sb_roles_capabilities_assigment
									ON sb_users_info.roleid = sb_roles_capabilities_assigment.roleid
								INNER JOIN sb_roles_capabilities
									ON sb_roles_capabilities_assigment.capabilityid = sb_roles_capabilities.CapabilityID
								WHERE userid =' . $userid . ' and identifier="' . $capabilityid . '" limit 1;');
	if ($res) {
 		return $res['allow'];
	} else {
		// if no result is returned - assume no access
		return false;
	}
 }

 /**
  * Check if logged in user has authorisation to view, edit or interact with a number of users, 
  * i.e. are the learners in the same provider as the user who wants to interact with them?
  * 
  * @param int $loggedinuserid
  * @param array|int $userarray list of userids
  * @param string $die if they don't have access do we kill the page?
  * @uses has_access_to_user()
  * @return boolean
  */
 function has_access_to_users ($loggedinuserid, $userarray, $die=false) {
 	// if they've passed in one element as a string, stick it in an array
 	if (!is_array ($userarray)) {
 		$userarray = array ($userarray);
 	}
 	foreach ($userarray as $user) {
 		if (! has_access_to_user ($loggedinuserid, $user, $die)) {
 			return false;
 		}
 	}
 	return true;
 }
 
 /**
  * Check if logged in user has authorisation to view, edit or interact with a user, 
  * i.e. is the learners in the same provider as the user who wants to interact with them?
  * 
  * @param int $loggedinuserid
  * @param int $userid
  * @param string $die if they don't have access do we kill the page?
  * @usedby has_access_to_users()
  * @return boolean
  */
 function has_access_to_user ($loggedinuserid, $userid, $die=true ) {
 	if ($loggedinuserid == $userid) {
 		// the user is trying to access themselves, so that's okay!
 		return true;
 	}
 	if (has_capability ($loggedinuserid, 'reports:view_student_results')) {
 		//passed the first line of defence, now to perform more checks...
 		if (has_capability($loggedinuserid, 'providers:control_all')) {
 			// super_admin
 			return true;
 		} else if (has_capability($loggedinuserid, 'providers:control_subcontractors')) {
 			// superprovider (super_admin)
 			$userprovider = DB::executeSelect ('users_info', 'providerid', array ('UserID'=>$userid));
 			$access = has_access_to_provider ($loggedinuserid, $userprovider['providerid'], false);
 			if ($access)
 				return true;
 		} else {
 			// you're only allowed access within your own institution (e.g. provider admin or advisor)
 			$userprovider = DB::executeSelect ('users_info', 'providerid', array ('UserID'=>$userid));
 			$yourprovider = Sessions::getUserInfo('providerid');
 			if ($userprovider['providerid'] == $yourprovider) 
 				return true;
 		}
 	} 
 	if ($die) {
 		die (return_string ('ACCESS_DENIED'));
 	}
 	return false;
 }
 
 /**
  * Check is logged in user has authorisation to view/edit or interact with multiple centres
  * i.e. are the centres belonging to the same provider as the user who wants to interact with them?
  * 
  * @param int $loggedinuserid
  * @param array|int $centrearray list of centres to check
  * @param string $die if they don't have access do we kill the page?
  * @uses has_access_to_centre()
  */
 function has_access_to_centres ($loggedinuserid, $centrearray, $die=false) {
 	// if they've passed in one element as a string, stick it in an array
 	if (!is_array ($centrearray)) {
 		$centrearray = array ($centrearray);
 	}
 	foreach ($centrearray as $centreid) {
 		if (! has_access_to_centre ($loggedinuserid, $centreid, $die)) {
 			return false;
 		}
 	}
 	return true;
 }
 /**
  * Check is logged in user has authorisation to view/edit or interact with a centre
  * i.e. is the centre belonging to the same provider as the user who wants to interact with it?
  * 
  * @param int $loggedinuserid
  * @param int $centreid
  * @param string $die if they don't have access do we kill the page?
  * @used-by has_access_to_centres()
  */
 function has_access_to_centre ($loggedinuserid, $centreid, $die=true) {
 	// if the user is super_admin...
 	if (has_capability($loggedinuserid, 'providers:control_all')) {
 		return true;
 	} else if (has_capability($loggedinuserid, 'providers:control_subcontractors')) {
 		// if user is a super provider admin...
 		$centreprovider = DB::executeSelect('centres', 'providerid', array ('centreid'=>$centreid));
 		if (has_access_to_provider ($loggedinuserid, $centreprovider)) {
 			return true;
 		}
 	} else {
 		// check if user has access to the centre (via the provider id)
 		$centreprovider = DB::executeSelect('centres', 'providerid', array ('centreid'=>$centreid));
 		if ($centreprovider['providerid'] == Sessions::getUserInfo('providerid')) {
 			return true;
 		}
 	}

 	if ($die) {
 		die (return_string ('ACCESS_DENIED'));
 	}
 	return false;
 }
 
 /**
  * check if current user is allowed to view/edit the given provider
  * 
  * @param int $loggedinuserid
  * @param int $providerid
  * @param string $die if they don't have access do we kill the page?
  * @return boolean
  */
 function has_access_to_provider ($loggedinuserid, $providerid, $die=true) {
 	// check for super_admin...
 	if (has_capability(Sessions::getID(), 'providers:control_all')) {
 		return true;
 	} else if (has_capability(Sessions::getID(), 'providers:control_subcontractors')) {
 		// get subcontractors for this user
 		$myprovider = Sessions::getUserInfo('providerid');
 		$subcontractors = DB::executeContainedSelect ('providers', '*', array ('superproviderid'=>Sessions::getSuperProviderID($myprovider), 'ProviderID'=>$providerid));
 		if ($subcontractors) {
 			return true;
 		} 
 	} 
 	if ($die) {
 		die (return_string ('ACCESS_DENIED'));
 	}
 	return false;
 }
 
 /**
  * Dump out a list of all capabilities
  * 
  * @return array
  */
 function get_all_capabilities () {
 	$res = DB::executeSelect('roles_capabilities', '*');
 	return $res;
 }
 
 /**
  * Dump out all role types
  * 
  * @return array
  */
 function get_all_role_types () {
 	$res = DB::executeSelect('roles_types', '*');
 	return $res;
 }
 
 /**
  * Add a new capability assignment for a given role
  * 
  * @param int $capabilityid
  * @param int $roleid
  * @return boolean success of procedure
  */
 function assignCapabilityToRole ($capabilityid, $roleid) {
 	$res = DB::executeInsert('roles_capabilities_assigment', array ('roleid'=>$roleid, 'capabilityid'=>$capabilityid, 'allow'=>1));
 	return $res;
 }
 
 /**
  * Remove a capability from a role type
  * 
  * @param string|int $capabilityid
  * @param int $roleid
  * @return boolean success of removal
  */
 function revokeCapabilityFromRole ($capabilityid, $roleid) {
 	if ($capabilityid=="all") {
 		// revoke all priviledges for this role type! serious stuff...
 		$res = DB::executeDelete('roles_capabilities_assigment', array ('roleid'=>$roleid));
 	} else {
 		$res = DB::executeDelete('roles_capabilities_assigment', array ('roleid'=>$roleid, 'capabilityid'=>$capabilityid), 1); //limit 1
 	}
 	return $res;
 }
 
 /**
  * Assign a learner to an advisor given both ids. 
  * 
  * @param int $learnerid
  * @param int $advisorid
  * @return boolean success
  * @usedby assignLearnersToAdvisor()
  */
 function assignLearnerToAdvisor ($learnerid, $advisorid) {
 	// check for existing record to prevent duplicates
 	$dup = DB::executeSelect('users_learner_assignment', '*', array ('advisorid'=>$advisorid, 'learnerid'=>$learnerid, 'enabled'=>1));
 	$res = true;
 	if (!$dup) {
 		$res = DB::executeInsert('users_learner_assignment', array ('advisorid'=>$advisorid, 'learnerid'=>$learnerid, 'enabled'=>1));
 	}
 	return $res;
 }
 
 /**
  * Assign a number of learners to an advisor.
  * 
  * @param array $learnerids
  * @param int $advisorid
  * @return boolean success. whether all users have been assigned.
  * @uses assignLearnerToAdvisor()
  */
 function assignLearnersToAdvisor ($learnerids, $advisorid) {
 	$successcount = 0;
 	foreach ($learnerids as $learnerid) {
 		if (assignLearnerToAdvisor ($learnerid, $advisorid)) {
 			$successcount++;
 		}
 	}
 	return $successcount == count($learnerids);
 }
 
 /**
  * Remove a learner from an advisor.
  * 
  * @param int $learnerid
  * @param int $advisorid
  * @return boolean success
  * @usedby revokeLearnersFromAdvisor()
  */
 function revokeLearnerFromAdvisor ($learnerid, $advisorid) {
 	$res = DB::executeDelete  ('users_learner_assignment', array ('advisorid'=>$advisorid, 'learnerid'=>$learnerid, 'enabled'=>1));
 	return $res;
 }
 
 /**
  * Remove learners from an advisor.
  * 
  * @param array $learnerids
  * @param int $advisorid
  * @return boolean success. whether all users have been assigned.
  */
 function revokeLearnersFromAdvisor ($learnerids, $advisorid) {
 	$successcount = 0;
 	foreach ($learnerids as $learnerid) {
 		if (revokeLearnerFromAdvisor ($learnerid, $advisorid)) {
 			$successcount++;
 		}
 	}
 	return $successcount == count($learnerids);
 }
 
 /**
  * Archive a learner.
  * 
  * @param int $learnerid
  * @param string $reason
  * @return boolean success
  * @usedby archiveLearners()
  */
 function archiveLearner ($learnerid, $reason='') {
 	$res = DB::executeUpdate('users_info', array ('archived'=>1, 'archivereason'=>$reason), array ('UserID'=>$learnerid), 1);
 	return $res;
 }
 
 /**
  * Archive a number of learners.
  * 
  * @param array $learnerids (ints)
  * @param array $reasons (strings)
  * @return boolean. success of whether all were archived.
  */
 function archiveLearners ($learnerids, $reasons) {
 	$successcount = 0;
 	for ($i = 0; $i < count ($learnerids); $i++ ) {
 		if (archiveLearner ($learnerids[$i], isset ($reasons[$i]) ? $reasons[$i] : '')) {
 			$successcount ++;
 		}
 	}
 	return $successcount == count($learnerids);
 }
 
 /**
  * Revive a learner from the archive.
  * 
  * @param int $learnerid
  * @return boolean. success.
  * @usedby unarchiveLearners()
  */
 function unarchiveLearner ($learnerid) {
 	$res = DB::executeUpdate('users_info', array ('archived'=>0, 'archivereason'=>''), array ('UserID'=>$learnerid), 1);
 	return $res;
 }
 
 /**
  * Revive a number of learners from the archive.
  * 
  * @param array $learnerids
  * @return boolean success of whether all were revived
  * @uses unarchiveLearner()
  */
 function unarchiveLearners ($learnerids) {
 	$successcount = 0;
 	foreach ($learnerids as $learnerid) {
 		if (unarchiveLearner ($learnerid)) {
 			$successcount++;
 		}
 	}
 	return $successcount == count($learnerids);
 }
 
 /**
  * Return a list of all roles below the role of the current logged in user...
  * 
  * e.g. If user is logged as advisor (roleid of 40), it will throw out learner (50).
  * e.g. If user is logged in as a provider admin (role id of 3), it will throw out advisor plus (35), advisor (40) and learner (50).
  * 
  * If $equalto is passed in true, it will return roles equal to the users role and below.
  * 
  * e.g. If user is logged in as a provider admin (role id of 3), it will throw out provider admin (3), advisor plus (35), advisor (40) and learner (50).
  * 
  * @param int $roleid
  * @param boolean $equalto - whether to check equal to the users current role too (e.g. role id 2 would return *2*, 3, 35, 40, 50)
  * @return array
  */
 function getRolesBelow ($roleid, $equalto=false) {
 	if ($equalto) {
 		$res = DB::executeContainedSelect('roles_types', '*', 'RoleID>='. $roleid, 'RoleID asc');
 	} else {
 		$res = DB::executeContainedSelect('roles_types', '*', 'RoleID>'. $roleid, 'RoleID asc');
 	}
 	return $res;
 }
 
 ?>
