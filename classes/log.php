<?php

include_once ('db.php');

class Log {
	
	private $MYSQL_ERROR = 'mysql';
	
	public function __construct() {
		
	}
	
	/**
	 * log an error to the database
	 * 
	 * @param string $msg (usually contains mysql error)
	 * @return boolean result of operation
	 */
	static  public function error ($msg) {
		return Log::saveLog ('error', $msg);
	}
	
	/**
	 * save a log given different types
	 * 
	 * @param string $type
	 * @param string $msg
	 * @return boolean result of operation
	 */
	private function saveLog ($type, $msg) {
		return DB::executeInsert('logs', array ('type'=>$type, 'message'=>$msg));
	}
	
}

?>