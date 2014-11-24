<?php

//ini_set ("display_errors", "on");
//error_reporting (E_ALL);

try {
	include_once ($CFG->apploc . '/db_connect.php');
} catch (Exception $e) {}

/**
 * Database query helper class which allows easier (and quicker) working with the db
 * 
 * Uses hard-coded value in $CFG->tblprefix throughout (stored as 'sb_')
 * 
 * @version 1.1
 *
 */
class DB {
	
	/**
	 * Change this to 1 if you want queries outputted to screen.
	 * 
	 * @var boolean
	 */
	static $debug = 0;

	/**
	 * Construct for php compat
	 */
	function __construct() {
	}
	
	/**
	 * Enable MySQL to run queries with a larger number of joins (e.g. TRaCIO reports).
	 * Added for certain hosts which generally prevent decent size queries.
	 * 
	 * Could look at using SET SESSION SQL_BIG_SELECTS=1 to prevent this being
	 * executed for every select query...
	 * 
	 * @version 1.0
	 */
    public static function setBigSelects () {
	    $res = mysql_query ('SET SQL_BIG_SELECTS=1;');
    }

    /**
	 * Run a literal string select query and return assoc array.
	 * 
	 * $alwayscontain param can force a single row of results into a container array, so it can be accessed
	 * in the same way as multiple results (e.g. using a foreach).
	 *
	 * example usage db::runSelectQuery ('select * from sb_users_info;');
	 *
	 * @param string $query full query to run
	 * @param bool $alwayscontain [optional] defaults to false. whether to force returning a multi-dim array (true) or not (false).
	 * @return array
	 * @usedby DB::executeContainedSelect()
	 * @usedby DB::executeSelect()
	 * @version 1.1
	 */
	public static function runSelectQuery ($query, $alwayscontain=false) {
		$temp = array ();
        DB::setBigSelects ();
        if (DB::$debug) {
			echo $query;
		}
		$res = mysql_query ($query);
		if ($res) {			
			if (mysql_num_rows ($res) > 0) {
				// update 2013-01-26 to speed up DB ever so slightly.. replacing the next line with following line 
				//while ($row = mysql_fetch_array ($res, MYSQL_ASSOC)) { 		// v1.0
				while ($row = mysql_fetch_assoc ($res)) { 						// v1.1
					array_push ($temp, $row);
				}
				$ret = $temp;
			} else {
				// return false, no rows returned from db
				$ret = false;
			}
			mysql_free_result($res);
		} else {
			$ret = false;
		}
		if (count($ret) == 1 && !$alwayscontain) {
			$ret = $ret[0];
		}
		return $ret;
	}

	/**
	 * Run a select query and always return a multi-dim array (even if one result is returned).
	 * Identical to DB::executeSelect() except the result is contained in multi-dim array.
	 * 
	 * Usage(s): see DB::executeSelect() documentation
	 * 
	 * @param string $table database table to run query on (without the prefix)
	 * @param array|string $data required information to return (fields in array or as string or '*')
	 * @param array|string $where field name as key with req'd value as search
	 * @param array|string $order field name as key with order as value, e.g. array('id'=>'desc', 'name'=>'asc')
	 * @param string $limit number of records to return and where to start (e.g. '10' or '10,20')
	 * @return array|boolean array of data or false
	 * 
	 */
	public static function executeContainedSelect ($table, $data=array(), $where=array(), $order=array(), $limit='') {
		global $CFG;
        DB::setBigSelects ();
		$query = DB::createSelectQueryString ($CFG->tblprefix . $table, $data, $where, $order, $limit);
		if (DB::$debug) {
			echo $query;
		}
		return DB::runSelectQuery ($query, true);
	}
	
	/**
	 * Run a select query and return assoc array
	 *
	 * Example usage(s):
	 * 
	 * DB::executeSelect('providers', array('DISTINCT(name)'), '', array('name'=>'asc', 'sector'=>'desc'));
	 * DB::executeContainedSelect('users_info', 'loginid, fname', 'fname like "Provider%"', '', '5,20');
	 * DB::executeContainedSelect('providers', array('DISTINCT(name)'), array ('ProviderID'=>'20'), array('name'=>'asc', 'sector'=>'desc',));
	 * DB::executeSelect('providers', array('DISTINCT(name)'), array ('ProviderID'=>'20'), array('name'=>'asc', 'sector'=>'desc',));
	 * 
	 * @param string $table database table to run query on (without the prefix)
	 * @param array|string $data required information to return (fields in array or as string or '*')
	 * @param array|string $where field name as key with req'd value as search
	 * @param array|string $order field name as key with order as value, e.g. array('id'=>'desc', 'name'=>'asc')
	 * @param string $limit number of records to return and where to start (e.g. '10' or '10,20')
	 * @return array|boolean array of data or false	
	 */
	public static function executeSelect ($table, $data=array(), $where=array(), $order=array(), $limit='') {
		global $CFG;
		$query = DB::createSelectQueryString ($CFG->tblprefix . $table, $data, $where, $order, $limit);
		
		if (DB::$debug) {
			echo $query;
		}
		return DB::runSelectQuery ($query);
	}

	/**
	 * workhorse method which generates and returns select query from variety of data given to it by other methods.
	 *
	 * @param string $table db table
	 * @param array|string $data required information to return (fields in array or as string or '*')
	 * @param array|string $where field name as key with req'd value as search
	 * @param array|string $order field name as key with order as value, e.g. array('id'=>'desc', 'name'=>'asc')
	 * @param string $limit number of records to return and where to start (e.g. '10' or '10,20')
	 * @return string the full query!
	 * @usedby DB::executeSelect()
	 * @usedby DB::executeContainedSelect()
	 */
	private function createSelectQueryString ($table, $data=array(), $where=array (), $order=array(), $limit='') {
		// check if they want all fields (*)
		if (is_array ($data)) {
			if (count ($data) == 0) {
				$query = 'SELECT *';
			} else {
				$query = 'SELECT ';
				$firstflag = true;
	
				/* generate fields */
				foreach ($data as $field) {
						
					if ( $firstflag ) {
						$firstflag = false;
						$query .= $field;
					} else {
						$query .= ', ';
						$query .= $field;
					}
				}
			}
		} else if ($data == '*') {
			$query = 'SELECT * ';
		} else {
			$query = 'SELECT ' . $data;
		} 

		$query .= ' from ' . $table;

		if (is_array ($where)) {
			/* generate where query */
			if (count($where) > 0) {
				$query .= ' WHERE ';
				$firstflag = true;
				/* generate fields */
				foreach ($where as $key=>$value) {
					if ( $firstflag ) {
						$firstflag = false;
						$query .= '' . $key .'="' . mysql_escape_string($value) . '"';
					} else {
						$query .= ' AND ';
						$query .= '' . $key .'="' . mysql_escape_string($value) . '"';
					}
				}
			}			
			} else if ($where != '') {
			$query .= ' WHERE ' . $where;
		}

		if (is_array ($order)) {
			if (count($order) > 0) {
				$query .= ' ORDER BY ';
				$firstflag = true;
				foreach ($order as $key=>$value) {
						
					if ( $firstflag ) {
						$firstflag = false;
						$query .= '' . $key .' ' . $value . ' ';
					} else {
						$query .= ', ';
						$query .= '' . $key .' ' . $value . ' ';
					}
				}
			}
		} else if ($order != '') {
			$query .= ' ORDER BY ' . $order;
		}

		if ($limit != '') {
			$query .= ' LIMIT ' . $limit;
		}
		
		$query .= ';';
		return $query;

	}

	/**
	 * Run an update on the db.
	 * 
	 * Example usage: DB::executeUpdate ('users_info', array ('emailsenabled'=>1), array ('userid'=>20));
	 * 
	 * @param string $table tablename which requires an update
	 * @param mixed $data name/value pairs of data to be updated
	 * @param string|array $where [optional] where clause for update. Beware! This should be set, even though it is optional...
	 * @param string|int $limit [optional] number of rows to update. Beware! If in doubt, set to 1.
	 * @return boolean success of operation
	 */
	public static function executeUpdate ($table, $data, $where=array (), $limit='') {
		global $CFG;
		$query = DB::createUpdateQueryString ($CFG->tblprefix . $table, $data, $where, $limit);
		$res = mysql_query ($query);
		return $res;
	}

	/**
	 * create an update query from assoc arrays.
	 *
	 * example: db::createUpdateQueryString ('test', array ('adams'=>'alice', 'bevan'=>'bob'), array ('id'=>10, 'visibility'=>1)));
	 *
	 * @param string $table tablename which requires an update
	 * @param mixed $data name/value pairs of data to be updated
	 * @param string|array $where [optional] where clause for update. Beware! This should be set, even though it is optional...
	 * @param string|int $limit [optional] number of rows to update. Beware! If in doubt, set to 1.
	 * @return string generated query as a string 
	 */
	private function createUpdateQueryString ($table, $data, $where=array (), $limit) {
		/* run thru $data keys and insert data */
		$query = 'UPDATE `' . $table . '` SET ';
		$firstflag = true;

		/* generate fields */
		foreach ($data as $key=>$value) {

			if ( $firstflag ) {
				$firstflag = false;
				$query .= '`' . $key .'`="' . mysql_escape_string ($value) . '"';
			} else {
				$query .= ',';
				$query .= '`' . $key .'`="' . mysql_escape_string ($value) . '"';
			}

		}

		/* generate where query */
		if (count($where) > 0) {
			$query .= ' WHERE ';
			$firstflag = true;
			/* generate fields */
			foreach ($where as $key=>$value) {
					
				if ( $firstflag ) {
					$firstflag = false;
					$query .= '`' . $key .'`="' . mysql_escape_string ($value) . '"';
				} else {
					$query .= ' AND ';
					$query .= '`' . $key .'`="' . mysql_escape_string ($value) . '"';
				}
			}
		}

		if (!empty ($limit)) {
			$query .= ' limit ' . $limit;	
		}
		
		$query .= ';';
		return $query;
	}

	/**
	 * do insert and return insert id of last action
	 *
	 * @param string $table table to insert data into
	 * @param array|string $data data to insert as an array or string
	 * @param string $limit 
	 * @return int|boolean insert if of last operation if successful or false if insert failed
	 */
	public static function executeInsert ($table, $data=array(), $limit='') {
		global $CFG;
		$query = DB::createInsertQueryString ($CFG->tblprefix . $table, $data, $limit);
		$res = mysql_query ($query);
		if ($res) return mysql_insert_id ();
		return $res;
	}

	/**
	 * Generate query string from given data.
	 * 
	 * @param string $table table to insert data into
	 * @param array|string $data data to insert as an array or string
	 * @param string $limit 
	 * @return string query
	 */
	private function createInsertQueryString ($table, $data=array(), $limit='') {
		/* run thru $data keys and insert data */
		$query = 'INSERT into ' . $table . ' (';
		$firstflag = true;

		/* generate fields */
		foreach ($data as $key=>$value) {

			if ( $firstflag ) {
				$firstflag = false;
				$query .= '`' . $key .'`';
			} else {
				$query .= ',';
				$query .= '`' . $key .'`';
			}

		}

		$query .= ') values (';
		/* generate values */
		$firstflag = true;
		foreach ($data as $key=>$value) {

			if ( $firstflag ) {
				$firstflag = false;
				$query .= '"' . mysql_escape_string($value) .'"';
			} else {
				$query .= ',';
				$query .= '"' . mysql_escape_string($value) .'"';
			}
		}
		$query .= ');';
		return $query;
	}
	
	/**
	 * Delete a record from the database.
	 * 
	 * @param string $table table to delete data from
 	 * @param string|array $where [optional] where clause for delete. Beware! This should be set, even though it is optional...
	 * @param string|int $limit [optional] number of rows to delete. Beware! If in doubt, set to 1.
	 * @return boolean success of operation
	 */
	public static function executeDelete ($table, $where=array (), $limit='') {
		global $CFG;
		$query = DB::createDeleteQueryString ($CFG->tblprefix . $table, $where, $limit);
		$res = mysql_query ($query);
		
		if ($res) return true;
		return $res;
	}
	
	/**
	 * Generate delete query from given data.
	 * 
 	 * @param string $table table to delete data from
 	 * @param string|array $where [optional] where clause for delete. Beware! This should be set, even though it is optional...
	 * @param string|int $limit [optional] number of rows to delete. Beware! If in doubt, set to 1.
	 * @return string delete query
	 */
	private function createDeleteQueryString ($table, $where=array(), $limit='') {
		$query = 'DELETE FROM ' . $table;
		
		if (is_array ($where)) {
			/* generate where query */
			if (count($where) > 0) {
				$query .= ' WHERE ';
				$firstflag = true;
				/* generate fields */
				foreach ($where as $key=>$value) {
					if ( $firstflag ) {
						$firstflag = false;
						$query .= '' . $key .'="' . mysql_escape_string($value) . '"';
					} else {
						$query .= ' AND ';
						$query .= '' . $key .'="' . mysql_escape_string($value) . '"';
					}
				}
			}			
		} else if ($where != '') {
			$query .= ' WHERE ' . $where;
		}
		
		if ($limit != '') {
			$query .= ' LIMIT ' . $limit;
		}
		
		$query .= ';';
		return $query;
	}
		
}

?>
