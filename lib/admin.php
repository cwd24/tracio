<?php

function getUsers ($roleid='5', $providerid='') {
	if (!empty ($providerid)) {
		$res = DB::executeSelect('users_info', array ('UserID', 'fname', 'sname', 'email'), array ('roleid'=>$roleid));
	} else {
		$res = DB::executeSelect('users_info', array ('UserID', 'fname', 'sname', 'email'), array ('roleid'=>$roleid), array ('providerid'=>$providerid));
	}
	return $res;
}

?>