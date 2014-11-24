<?php

/**
 * @version 1.0
 * 
 * DataGrid - simple PHP implementation of .net style datagrid/gridview.
 * 
 * Used with JQuery's TableSorter plugin.
 * 
 * Render a table given an multi-dimension array of data.
 * 
 * Example Usage 1 (simple): 
 * 
 * $data = array ();
 * $data[] = array ('Name'=>'Alice', 'Age'=>16);
 * $data[] = array ('Name'=>'Bob', 	'Age'=>17);
 * $dg = new DataGrid($data, 'Name');
 * $dg->addAttr('table', 'class', 'tablesorter');
 * $dg->render();
 * 
 * Example Usage 2 (advanced):
 * 
 * $data = get_all_capabilities();	
 * $dg = new DataGrid($data, 'CapabilityID');
 * $dg->addDisplayField ('name', 'Name');
 * $dg->addDisplayField ('identifier', 'identifier');
 * $dg->addAttr('table', 'cellpadding', '10');
 * $dg->addAttr('table', 'class', 'tablesorter');
 * $dg->addAttr('table', 'id', 'tester');
 * $dg->addAttr('td', 'bgcolor', 'red');
 * $dg->addAttr('tr', 'align', 'center');
 * $dg->addHTMLCol('<input name="capas[]" value="%s" type="checkbox" />', 'Assign');
 * $dg->render();
 * 
 *
 */
global $CFG;

class DataGrid {
	
	private $data = array ();
	private $requiredfields = array ();
	private $fieldtitles = array ();
	private $keyfield = '';
	private $atts = array ();
	private $paginate = true;
	
	private $add_cols = array ();
	
	private $addFieldNameAsClass = true;
	private $maxRowsForPrint = 15;
	
	private $defaultPaginationSize = 20;
	
	/**
	 * Constructor.
	 * 
	 * @param array $data multi-dimensional array of assoc data
	 * @param string $keyfield the name of the unique id field
	 */
	public function __construct ($data, $keyfield='') {
		global $CFG;
		
		$this->data = $data;
		$this->keyfield = $keyfield;

		if (!empty ($this->data)) {
			$this->setDisplayFields ( array_keys ($this->data[0]));
		}
		$this->defaultPaginationSize = $CFG->defaultPaginationSize;
	}
	
	/**
	 * Use this to disable pagination - pagination is true by default.
	 * 
	 * @param bool $status you require pagination? true or false.
	 */
	public function setPagination ($status) {
		$this->paginate = $status;
	}
	
	/**
	 * Whether to render the field name as a class in a td element.
	 * 
	 * @param boolean $status
	 */
	public function setOutputFieldNameAsClass ($status) {
		$this->addFieldNameAsClass = $status;
	}
	
	/**
	 * Sets maximum rows for outputting to browser on a single page.
	 * 
	 * Set by default to 15 - this displays 15 rows nicely on a printed browser page.
	 * 
	 * 
	 * Set this as very very high for xls output as it is not required.
	 * 
	 * @param int $newval
	 * @usedby reports.php
	 */
	public function setMaxRowsForPrint ($newval) {
		$this->maxRowsForPrint = $newval;
	}
	
	/**
	 * Adds a display field to output in the datagrid.
	 * 
	 * By default, all fields are outputted unless removed by removeDisplayField() or removeDisplayFields().
	 * So this is superfluous. Would only really be useful in the unlikely event that you wiped out all the fields
	 * using removeDisplayFields() !
	 * 
	 * @param string $field db field to add to query
	 * @param string $title title for column for this field
	 * @deprecated use setFieldTitle() to change the displayed column title of the field
	 */
	public function addDisplayField ($field, $title='') {
		array_push ($this->requiredfields, $field);
		if (!empty ($title)) {
			$this->addFieldTitle ($field, $title);
		}
	}
		
	/**
	 * Locate specific column and remove it from the display list,
	 * so it is not displayed in the datagrid.
	 * 
	 * This is most likely to be used to remove id fields (e.g. userid) which
	 * are irrelevant to users, but required for interaction with the datagrid.
	 * 
	 * It could also be used to remove fields which are being used elsewhere
	 * with methods like addHTMLCol() or addConditionalField().
	 * 
	 * @param string $field fieldname to remove from datagrid.
	 * @return boolean success of operation
	 * @usedby removeDisplayFields()
	 */
	public function removeDisplayField ($field) {
		$foundflag = false;
		for ($i=0; $i<count ($this->requiredfields); $i++) {
			if ( $this->requiredfields[$i] == $field) {
				array_splice ($this->requiredfields, $i, 1);
				$foundflag = true;
				break;
			}
		}
		return $foundflag;
	}
	
	/**
	 * Locate specific columns and remove them from the display list,
	 * so they are not displayed in the datagrid.
	 * 
	 * See removeDisplayField() for more.
	 * 
	 * @param array $fields list of fields to remove from the query
	 * @return boolean success of operation
	 * @uses removeDisplayField()
	 */
	public function removeDisplayFields ($fields) {
		if (is_array ($fields)) {
			foreach ($fields as $field) {
				$this->removeDisplayField ($field);
			}
		} else if (is_string ($fields)) {
			// user has only passed a string - so simply remove that
			return $this->removeDisplayField ($fields);
		} else {
			return false;
		}
		return true;
	}
	
	/**
	 * Set all the fields to be outputted to the datagrid. 
	 * 
	 * Example usage (only render 'identifier' and 'name' to datagrid)
	 * 
	 * $dg->setDisplayFields (array ('identifier', 'name'));
	 *  
	 * @param array $fields
	 */
	public function setDisplayFields ($fields=array()) {
		$this->requiredfields = $fields;
	}
	
	/**
	 * Set a nice column title for the given field, to replace the (ugly) fieldname.
	 * For example, you could turn 'fname' into 'First name'.
	 * 
	 * @param string $field fieldname from query
	 * @param string $title new nice name for the column
	 */
	public function setFieldTitle ($field, $title) {
		$this->fieldtitles[$field] = $title;
	}
	
	/**
	 * @deprecated use setFieldTitle
	 */
	public function addFieldTitle ($field, $title) {
		$this->setFieldTitle ($field, $title);
	}
	
	/**
	 * Set nice field titles for multiple columns.
	 * 
	 * @param array $arr
	 * @deprecated not in use. Could be redone to utilise setFieldTitle() with a given array
	 */
	public function setFieldTitles ($arr=array ()) {
		$this->fieldtitles = $arr;
	}
	
	/**
	 * @deprecated
	 */
	public function dataBind () {
		if (!empty ($this->data)) {
			$this->render($this->data);
		} else {
			$this->renderEmpty ();
		}
	}
	
	/**
	 * Run a logic check on a conditional datagrid field and return true or false.
	 *  
	 * Example (to find out if user is archived):
	 * 
	 * checkConditional ('archived', '=1');
	 *  
	 * @param string $check field value to check value of in conditional. e.g. field "archived" returns a 1.
	 * @param string $conditional operand and value to check. e.g. "!= 0"
	 * @return boolean if result was true or false
	 */
	private function checkConditional ($check, $conditional) {
		//work out conditional
		$spliced = explode (' ', $conditional);
		$operator = $spliced[0]; 
		$checkvalue = $spliced[1];
		
		switch ($operator) {
			case "=":
				if ($check == $checkvalue) return true;
				break;
			case ">":
				if ($check > $checkvalue) return true;
				break;
			case "<":
				if ($check < $checkvalue) return true;
				break;
			case "!":
				if ($check != $checkvalue) return true;
				break;
		}
		
		return false;
	}
	
	/**
	 * Add a field which will display alternative content depending on a conditional check on the field data.
	 * 
	 * Example:
	 * 
	 * <pre>
	 * $dg->addConditionalField (
	 * 		'UserID', 			
	 * 		'<center><img title= "Learner is archived" alt="Learner is Archived" src="../images/lock.gif" /></center>',
	 * 		'Archive Status',
	 * 		'archived',
	 * 		'= 1',
	 * 		'<center><img title="Learner is live" alt="Learner is live" src="../images/unlock.png" /></center>');
	 * </pre>
	 * 
	 * @param string $boundfield key field (usually userid) in datagrid (set in constructor)
	 * @param string $colhtml HTML output if conditional is true.
	 * @param string $header Title of column header.
	 * @param string $conditionalfield field to check against (e.g. 'archived' above)
	 * @param string $conditional HTML output if conditional is false.
	 * @param string $emptydisplay [optional] Empty display html.
	 * @uses addHTMLCol()
	 */
	public function addConditionalField ($boundfield, $colhtml='', $header='Select',  $conditionalfield, $conditional, $emptydisplay='') {
		$this->addHTMLCol ($colhtml, $header, $boundfield, $conditional, $conditionalfield, $emptydisplay);
	}
	
	/**
	 * Same as addHTMLCol()
	 * 
	 * @param unknown_type $boundfield
	 * @param unknown_type $colhtml
	 * @param unknown_type $header
	 * @return unknown_type
	 * @deprecated
	 */
	public function addBoundField ($boundfield, $colhtml='', $header='') {
		$this->addHTMLCol ($colhtml, $header, $boundfield);
	}
	
	/**
	 * Add a html column to the datagrid which the keyfield data can be used in.
	 * 
	 * Example:
	 * 
	 * $dg = new DataGrid ($thedata, 'UserID');										// UserID set as keyfield
	 * $dg->addHTMLCol('<a href="user_edit.php?userid=%s">Edit</a>', 'Profile'); 	// %s replaced by keyfield value
	 * 
	 * Outputs: <a href="user_edit.php?userid=20">Edit</a> with a column headed 'Profile'.
	 * 
	 * @param string $colhtml HTML to output with %s (string), %d (numeric) placeholders
	 * @param string $header Header title for column.
	 * @param string $boundfield [optional] db field to use. if not set, keyfield of datagrid is used.
	 * @param string $conditional HTML output if conditional is false.
	 * @param string $conditionalfield field to check against.
	 * @param string $emptydisplay [optional] Empty display html.
	 * @usedby addConditionalField()
	 */
	public function addHTMLCol ($colhtml, $header='Select', $boundfield='', $conditional='', $conditionalfield='', $emptydisplay='') {
		array_push ($this->add_cols, array ('content'=>$colhtml, 'header'=>$header, 'boundfield'=>$boundfield, 'conditional'=>$conditional, 'conditionalfield'=>$conditionalfield, 'emptydisplay'=>$emptydisplay));
	
	}
	
	/**
	 * Generate code for additional tr, td or th for the table
	 * can use key/unique id data field for editing if passed in.
	 * 
	 * @param string $type The element type: tr, td or th
	 * @param string $keydata unique data id for checkboxes, dropdowns etc
	 * @return string
	 */
	private function createAdditionalColsHTML ($type='td', $keydata='', $rowdata=array()) {
		$str = '';
		$counter = 0;
		
		foreach ($this->add_cols as $col) {

			if ($type=='th') {
				$str .=  $this->createTagStringWithAtts ('th') . $col['header'] . '</th>';

			} else {
				/* is there a keyfield id to pass thru to string? */
				if (!empty ($col ['boundfield'])) {
					
					// if there is no conditional on this boundfield, or if the conditonal is met, display cell
					if ($this->checkConditional ($rowdata[$col['conditionalfield']], $col['conditional']) || empty ($col['conditional'])) {
						$str .= $this->createTagStringWithAtts ('td') . sprintf ($col['content'], $rowdata[$col['boundfield']]) . '</td>';
					}  else {
						// conditional is not met, so display empty cell with data if need be (uses same boundfield as prev).
						$str .= $this->createTagStringWithAtts ('td') . sprintf ($col['emptydisplay'], $rowdata[$col['boundfield']]) . '</td>';
					}
				} else if (!empty ($keydata)) {
					$str .= $this->createTagStringWithAtts ('td') . sprintf ($col['content'], $keydata) . '</td>';
				
				} else {
				
					$str .=  $this->createTagStringWithAtts ('td') . $col['content'] . '</td>';
				}

			}
			$str .= chr(13);
		}
		return $str;
	}
	
	/**
	 * Add an attribute to a html tag/element.
	 * 
	 * Example 1:
	 * 
	 * $dg->addAttr ('table', 'cellspacing', '10');
	 * 
	 * Example 2:
	 * 
	 * $dg->addAttr ('tr', 'class', 'alert');
	 * 
	 * Arrays are dumped using createTagStringWithAtts().
	 * 
	 * @param string $tag td, th, tr, table
	 * @param string $attr html element attribute, e.g. class, cellpadding
	 * @param string $val value to add to the element
	 */
	public function addAttr ($tag, $attr, $val) {
		if (empty ($this->atts[$tag])) {
			$this->atts[$tag] = array ();
		}
		$this->atts[$tag][$attr] = $val;
	}
	
	/**
	 * Set a html id for the table element.
	 * 
	 * Shortcut to addAttr ('table', 'id', $str)
	 * 
	 * @param string $str datagrid table id
	 * @uses addAttr()
	 */
	public function setTableID ($str) {
		$this->addAttr ('table', 'id', $str);
	}
	
	/**
	 * Set a class for the table element.
	 * 
	 * Shortcut to addAttr ('table', 'class', $str)
	 *  
	 * @param string $str class name for the datagrid table
	 */
	public function setTableClass ($str='tablesorter') {
		$this->addAttr ('table', 'class', $str);
	}
	
	/**
	 * Method called if there is no data to display.
	 */
	private function renderEmpty () {
		echo $this->createTagStringWithAtts ('table');
		echo $this->createTagStringWithAtts ('tr');
		echo $this->createTagStringWithAtts ('td');
		echo 'No data to display.';
		echo '</td>';
		echo '</tr>';
		echo '</table>';	
	}
		
	/**
	 * Creates a string for a tag with all required attributes (e.g. those set with addAttr())
	 * 
	 * @param string $tag html attribute: 'table', 'tr', 'td', 'th'
	 * @param boolean $close defaults to true. whether to close the tag (true) or leave open (false)
	 * @param string $additionalClasses additional classes to add to html element (for forcing in classes for use with renderRows())
	 * @version 1.1 updated 2013-01-26 added $additionalClasses param for forcing in classes (for use in $this->renderRows)
	 * @return string html markup of element with all the added attributes e.g. <table border="1" cellspacing="10">
	 */
	public function createTagStringWithAtts ($tag, $close=true, $additionalClasses='') {
		$str = '<' . $tag;
		if (!empty ($this->atts[$tag])) {
			foreach ($this->atts[$tag] as $att=>$val) {
				if ($att == 'class') {
					// need to add additionalClass to the field
					$str .= ' ' . $att . '="' . $val . ' ' . $additionalClasses . '"';
				} else {
					$str .= ' ' . $att . '="' . $val . '"';
				}
			}	
		} else if ($additionalClasses) {
			// add classes
			$str .= ' class="' . $additionalClasses . '"';
		}
		if ($close) {
			$str .= '>';
		}
		return $str;
	}
	
	/**
	 * Return title of this field if it has been set with setFieldTitle()
	 * 
	 * @param string $field fieldname
	 * @return string nice title
	 */
	private function getTitle ($field='') {
		if (!empty ($this->fieldtitles)) {
			if (array_key_exists ($field, $this->fieldtitles)) {
				return $this->fieldtitles[$field];
			}
		}
		return $field;
	}
	
	/**
	 * Show pagination controls on page. 
	 * 
	 * Called below the datagrid in render() to show pagination controls.
	 * @usedby render()
	 */
	public function displayPagination () {
		global $CFG;
	?>
	<div id="pager" class="pager">
		<img src="<?php echo $CFG->fullhttp; ?>/external/tablesorter/addons/pager/icons/first.png" class="first"/>
		<img src="<?php echo $CFG->fullhttp; ?>/external/tablesorter/addons/pager/icons/prev.png" class="prev"/>
		<input type="text" class="pagedisplay"/>
		<img src="<?php echo $CFG->fullhttp; ?>/external/tablesorter/addons/pager/icons/next.png" class="next"/>
		<img src="<?php echo $CFG->fullhttp; ?>/external/tablesorter/addons/pager/icons/last.png" class="last"/>
		<label for="pagesize">Display: </label>
		<select class="pagesize">
	
			<option 1<?php if ($this->defaultPaginationSize=== 10) {?>selected="selected"<?php } ?> value="10">10</option>
			<option <?php if ($this->defaultPaginationSize=== 20) {?>selected="selected"<?php } ?> value="20">20</option>
			<option <?php if ($this->defaultPaginationSize=== 30) {?>selected="selected"<?php } ?> value="30">30</option>
			<option <?php if ($this->defaultPaginationSize=== 40) {?>selected="selected"<?php } ?> value="40">40</option>
		</select>
	</div>
	<?php 
	}
	
	/**
	 * Actually draw the table and echo it to browser.
	 * 
	 * This MUST be called last by the user in order to render the datagrid with all the set attributes etc, e.g. $dg->render ();
	 */
	public function render () {
		if (!empty ($this->data)) {
			echo $this->createTagStringWithAtts ('table');
			echo chr(13);
			echo $this->renderHeaderRow ($this->data);
			echo $this->renderRows ($this->data);
			echo '</table>';
			// display pagination if it is enabled, and there are the minimum required rows for pagination!
			if ($this->paginate && count($this->data) > 10) {
				$this->displayPagination ();
			}
		} else {
			$this->renderEmpty ();
		}
	}
	
	/**
	 * Render datagrid as appropriate html for CSV (without pagination)
	 */
	public function renderCSV () {
		$csv = '';
		if (!empty ($this->data)) {
			$csv .= $this->createTagStringWithAtts ('table');
			$csv .= chr(13);
			$csv .= $this->renderHeaderRow ($this->data);
			$csv .= $this->renderRows ($this->data);
			$csv .= '</table>';
			return $csv;
		} else {
			$this->renderEmpty ();
		}
		
	}

	/**
	 * Generate the header row for the datagrid.
	 * 
	 * @usedby render()
	 * @return string
	 */
	private function renderHeaderRow () {
		$str = '';
		$str .= '<thead>';
		$str .= chr(13);
		$str .= $this->createTagStringWithAtts ('tr');
		$str .= chr(13);
		if (empty  ($this->requiredfields)) {
			foreach ($this->data[0] as $key=>$value) {
				$str .= $this->createTagStringWithAtts ('th') . $this->getTitle ($key) . '</th>';
				$str .= chr(13);
			}
		} else {
			foreach ($this->requiredfields as $key=>$value) {
				$str .= $this->createTagStringWithAtts ('th') . $this->getTitle( $value) . '</th>';
				$str .= chr(13);
			}
		}

		$str .= $this->createAdditionalColsHTML ('th');
		$str .= chr(13);
		$str .= '</tr>';
		$str .= chr(13);
		$str .= '</thead>';
		$str .= chr(13);
		return $str;
	}

	/**
	 * Render rows for datagrid (including all TDs)
	 * 
	 * @return string
	 * @version 1.1 - updated 2013-01-26 to force fieldnames as a class attribute into the td for manipulation by js or css
	 */
	public function renderRows () {
		$str = ''; //'ROWS';
		$str .= '<tbody>';
		
		// numerically counting to add a pagebreak
		$rowCounter = 0;
		
		// draw data rows
		if (empty  ($this->requiredfields))  {
			foreach ($this->data as $rows) {
				$this->addAttr ('tr', 'class', 'alert');
				$str .=  $this->createTagStringWithAtts ('tr');
				$str .= chr(13);
				// write out columns as tds
				foreach ($rows as $key=>$row) {
					if ($this->addFieldNameAsClass) {
						$str .=  $this->createTagStringWithAtts ('td', true, 'field_' . $key) . $row . '</td>';
					} else {
						$str .=  $this->createTagStringWithAtts ('td') . $row . '</td>';
					}
					$str .= chr(13);
				}
				// any additional cols to add?
				$str .= $this->createAdditionalColsHTML ('', $rows[$this->keyfield], $row);
				$str .= '</tr>';
				$str .= chr(13);
			}
		} else {
			// user has specified a set number of display fields
			foreach ($this->data as $row) {
				// for every set amount of rows, insert an empty row with a print page break on it.
				if (++ $rowCounter == $this->maxRowsForPrint) {
					$str .=  '<tr class="table-page-break"></tr>';
					$rowCounter = 0;
				} 
				$str .=  $this->createTagStringWithAtts ('tr');
				$str .= chr(13);
				foreach ($row as $key=>$value) {
					if ( in_array( $key, $this->requiredfields )) {
						if ($this->addFieldNameAsClass) {
							$str .=  $this->createTagStringWithAtts ('td', true, 'field_' . $key) . $value . '</td>';
						} else {
							$str .=  $this->createTagStringWithAtts ('td') . $value . '</td>';
						}
						$str .= chr(13);
					}
				}
				// any additional cols to add?
				$str .=  $this->createAdditionalColsHTML ('', $row[$this->keyfield], $row);
				$str .= '</tr>';
				$str .= chr(13);
			}
		}
		$str .= '</tbody>';
		return $str;
	}


}?>
