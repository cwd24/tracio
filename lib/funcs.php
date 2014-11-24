<?php
/**
 * general helper and useful functions for strings, arrays, etc.
 * 
 * @version 1.0
 * 
 */

/**
 * upper case first letter of all names including those separated by - (hyphen) or ' (apostrophe)
 * e.g. "d'arcy renow-clarke" becomes "D'Arcy Renow-Clarke"
 * 
 * @param string $string
 * @return string
 */
function ucname($string) {
    $string =ucwords(strtolower($string));

    foreach (array('-', '\'') as $delimiter) {
      if (strpos($string, $delimiter)!==false) {
        $string =implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
      }
    }
    return $string;
}

/**
 * If number is less than 10, add a zero before it.
 *
 * @param int $num
 * @return string padded number (starting with a 0 if need be)
 */
function padNumber ($num) {
	if ($num < 10) {
		return '0' . $num;
	}
	return $num;
}

/**
 * Convert date from string format DD/MM/YYYY to YYYY-MM-DD for MySQL.
 * TODO - simplify this.
 * 
 * @param mixed $date uk date format (DD/MM/YYYY)
 * @return string mysql date format (YYYY-MM-DD)
 */
function ukdate2mysql($date) {
	$date = explode("/",$date);
	$date[0] = (int) $date[0];
	$date[1] = (int) $date[1];
	$date[2] = (int) $date[2];
	if ($date[0]<10) { $date[0]="0" . $date[0]; }
	if ($date[1]<10) { $date[1]="0" . $date[1]; }
	$date = array($date[2], $date[1], $date[0]);
	return $n_date=implode("-", $date);
}

/**
 * Convert Mysql Date to UK Date format (reverse of above)
 * 
 * @param mixed $mysqldate
 * @return string|boolean
 */
function mysql2ukdate ($mysqldate) {
	if (!empty ($mysqldate)) {
		$mysqldate = date($mysqldate);
		return date('d/m/Y', strtotime($mysqldate));
	}
	return false;
}

/**
 * Return a drop-down menu given certain query criteria.
 * 
 * Example usages:
 * 
 * 1. Returns a list of centres for a provider id
 * echo drawCombo ('ctr', 'centres', array ('providerid'=>$_POST['provider']), 'name', 'providerid');
 * 
 * 2. Returns a list of learners for a provider.
 * echo drawCombo ('ctr', 'users_info', array ('providerid'=>$_POST['provider'], 'roleid'=>50), array ('fname', 'sname'), 'UserID');
 * 
 * @param string $elementid id/name attributes of combo box for <select>
 * @param string $tbl database table to extract data from (use short name minus prefix)
 * @param array $wherearray select query criteria WHERE clause
 * @param array|string $showfield (optional) field to use as label in combo. goes in as string field name ('loginid') or array list of field names (e.g. array ('fname', 'sname')
 * @param string $valuefield (optional) the db field to insert into the combo value attribute
 * @param boolean $anyrow (optional) whether to output an 'Any' row first (i.e. <option value="0">Any</option>).
 * @return string produced html including <select> container
 */
function drawCombo ($elementid, $tbl, $wherearray=array (), $showfield='name', $valuefield='id', $anyrow=false) {
	$ret = '';
	$res = DB::executeContainedSelect($tbl, '*', $wherearray, $showfield);
	if ($res) {
		if (count ($res) > 1) {
			$ret .= '<select name="'. $elementid . '" id="' . $elementid . '">';
			
			if ($anyrow) {
				$ret .= '<option value="0">Any</option>';
			}
			foreach ($res as $row) {
				$show = '';
				if (is_array ( $showfield)) {
					foreach ($showfield as $field) {
						$show .= $row[$field] . ' ';
					}
				} else {
					$show = $row[$showfield];
				}
				$ret .= '<option value="' . $row[$valuefield] . '">' . $show . '</option>';
			}
			$ret .= '</select>';
		} else {
			$show = '';
			// if there is only one row, enter name in textbox instead of using a drop-down
			$ret .= '<input type="hidden" name="' . $elementid . '" value="' .  $res[0][$valuefield] . '" />';
			if (is_array ( $showfield)) {
				foreach ($showfield as $field) {
					$show .= $res[0][$field] . ' ';
				}
			} else {
				$show = $res[0][$showfield];
			}
			$ret .= '<input type="text" readonly="readonly" value="' . $show . '" />';
		}
	} else {
		$ret .= 'false';
	}
	return $ret;
} 

/**
 * Do a print_r dump with a <pre> container for nice layout
 * 
 * @param mixed $obj
 */
function pre_r ($obj) {
	echo '<pre>';
	print_r ($obj);
	echo '</pre>';
}

?>
