<?php

/**
 * The FilterField class is used to search filters to a page, 
 * in order to fine tune the amount of data returned in datagrids throughout the system.
 * 
 * For example, filters on the Users Admin screen include Firstname, Surname and Role.
 * 
 * The FilterManager works by wrapping a SELECT query around the original query with the filter clauses.
 * 
 * Example usages (from admin/advisor.php):
 * 
 * #1: 	Generate a simple text input box for filtering on 'firstname' (aliased) field in sb_users_info
 * 
 * $filter = new FilterField ('firstname', 'firstname', 'text', 'Firstname');
 * $filter->render ();
 * $filtersMgr->addFilterField ($filter);
 * 
 * #2: 	Add a checkbox for filtering on 'archived' field in database.
 * 		If checked, archived users will be hidden. (SQL: "WHERE archived != 1")
 * 		If unchecked, archived clause will be omitted from the query (last param is false).
 * 		
 * $filter = new FilterField ('archived', 'archived', 'checkbox', 'Hide Archived Users', false);
 * $filter->setSearchType('not');
 * $filter->bindData(array ("value"=>1, "checked"=>true));
 * $filter->render ();
 * $filtersMgr->addFilterField ($filter);
 * 
 * @version 1.0
 *
 */
class FilterField {
	
	private $id='';
	private $dbfield = '';
	private $type = '';
	private $label = '';
	private $initdata = 0;
	private $searchtype = 'contains';
	
	// vars for drop-downs - to set value and label
	private $valuefield = 'data';
	private $labelfield = 'label';
	
	// for first line (<option>) of drop-down
	private $firstrow = 'Any';
	
	private $currentvalue;
	
	/**
	 * Create a new filter field, giving indication of type, db field, label text and so forth.
	 * 
	 * @param string $id id and name attributes for input/select element.
	 * @param string $dbfield the actual field to interrogate in the database.
	 * @param string $type [optional] type of form element to add to page (options are text, checkbox, dropdown). defaults to 'text'.
	 * @param string $label [optional] text to present alongside the filter form element.
	 * @param string $alwaysInclude [optional] whether to push this form field into the query regardless of result (i.e. if set to zero or empty!). defaults to false.
	 */
	function __construct ( $id, $dbfield, $type='text', $label='', $alwaysInclude=false) {
		$this->id = strtolower($id) ;
		$this->dbfield = strtolower ($dbfield) ;
		$this->type = $type;
		$this->label = $label;
		$this->force = $alwaysInclude;
		
		// get existing value for this filter field from $_GET or $_POST arrays
		$this->currentvalue = $this->getCurrentValueFromForm();
	}
	
	/**
	 * Used for binding drop-downs to an assoc array.
	 * 
	 * e.g. $postcodes = array ( 
	 * 							array ('data'=>'SA', 'label'=>'Swansea'),
	 * 							array ('data'=>'CF', 'label'=>'Cardiff')
	 * 					);
	 * 
	 * Example 1: Drop down displays 'Any', 'Swansea', 'Cardiff' in drop-down.
	 * 
	 * $filter = new FilterField ('userlocation1', 'location', 'dropdown', 'Nearest City', false);
 	 * $filter->bindData($postcodes);
 	 * $filter->render ();
 	 * $filtersMgr->addFilterField ($filter);
 	 * 
 	 * Example 2: Drop down displays 'Swansea', 'Cardiff' in drop-down.
 	 * 
 	 * $filter = new FilterField ('userlocation2', 'location', 'dropdown', 'Nearest City');
 	 * $filter->bindData($postcodes, 'data', 'label', false);
 	 * $filter->render ();
 	 * $filtersMgr->addFilterField ($filter);
	 * 
	 * @param array $initdata array of data to output to combobox. See above for simple example (e.g. $postcodes using 'data' and 'label' keys) or set to more complex assoc array. If you do the latter, you will need to set the next two params to specify the keys to use for data/value and label/display. 
	 * @param string $valuefield [optional] which field from the bound array to grab the value/data from. defaults to 'data'.
	 * @param string $labelfield [optional] which field from the bound array to grab the label from. defaults to 'label'.
	 * @param string|boolean $firstrow [optional] whether to add a first row to the drop-down (e.g. 'Please Select' or 'Any'). Set as falsy for none.
	 */
	public function bindData ($initdata, $valuefield='data', $labelfield='label', $firstrow='Any') {
		$this->initdata = $initdata;
		$this->valuefield = $valuefield;
		$this->labelfield = $labelfield;
		$this->firstrow = $firstrow;
	}
	
	/**
	 * Inform filter field of which where type operator to use for this field.
	 * 
	 * This is used by the FilterManager class when generating the query.
	 * 
	 * @param string $newsearchtype
	 * @usedby FilterManager class.
	 */
	public function setSearchType ($newsearchtype) {
		$this->searchtype = $newsearchtype;
	}
	
	/**
	 * Dump out all relevant information about this FilterField instance to the FilterManager for query generation.
	 * 
	 * @return array
	 * @usedby FilterManager class.
	 */
	public function get () {
		return array ('name'=>$this->id, 'field'=>$this->dbfield, 'searchtype'=>$this->searchtype, 'force'=>$this->force);
	}
	
	/**
	 * Find out if this filter field has been set already in the $_POST or $_GET arrays.
	 * 
	 * This is for passing data back to the input fields following a page reload.
	 * 
	 * @return string|boolean field value if already set or false for empty.
	 */
	private function getCurrentValueFromForm () {	
		if (!empty ($_GET)) {
			return !empty ($_GET[$this->id]) ? $_GET[$this->id] : '' ;
		}
		if (!empty ($_POST)) {
			return !empty ($_POST[$this->id]) ? $_POST[$this->id] : '' ;
		}
		return false;
	}
	
	/**
	 * Find out which type of form element they want and call the appropriate method.
	 */
	private function renderField () {
		switch ($this->type) {
			case "text":
				return $this->renderText ();
				break;
			case "checkbox":
				return $this->renderCheckBox ();
				break;
			case "dropdown":
				return $this->renderDropDown ();
				break;
		}
		return false;
	}
	
	/**
	 * Render a text input box filterfield.
	 * 
	 * @return string html code.
	 */
	private function renderText () {
		return "<input type='$this->type' name='$this->id' id='$this->id' value='$this->currentvalue' />";
	}
	
	/**
	 * Render a drop-down from bound data or a simple true/false drop down.
	 * 
	 * @param boolean $simple if true, create a true/false dropdown and ignore databind.
	 * @return html code of dropdown
	 */
	private function renderDropDown ($simple=true) {
		$ret = "<select id='$this->id' name='$this->id'>";
		if (!empty ($this->initdata)) {
			if (!empty ($this->firstrow)) {
				$ret .= '<option value="">Any</option>';	
			}
			foreach ($this->initdata as $row) {
				$ret .= '<option ' . ($this->currentvalue == $row[$this->valuefield] ? 'selected="selected"' : '') . ' value="' . $row[$this->valuefield] . '">' . $row[$this->labelfield] . '</option>';
				
			}
		} else if ($simple) {
			$ret .= '<option . ' . ($this->currentvalue == 0 ? 'selected="selected"' : '') . ' value="0">No</option>';
			$ret .= '<option . ' . ($this->currentvalue == 1 ? 'selected="selected"' : '') . ' value="1">Yes</option>';
		} 
		$ret .= '</select>';
		return $ret;
	}
	
	/**
	 * Render a checkbox.
	 *
	 * @return string html code.
	 */
	private function renderCheckBox () {
		$ret = "<input type='$this->type' name='$this->id' id='$this->id' value='" . $this->initdata['value'] . "' ";
		// if first time the page has been loaded and the checkbox is meant to be checked as default ...
		if (empty ($_POST) && $this->initdata['checked'] === true) {
			$ret .= " checked='checked'";
		} else if (!empty ($this->currentvalue))  {
			$ret .= " checked='checked'";
		}
		$ret .= " />";
		return $ret;
	}
	
	/**
	 * Actually pump out the filter field to the page. 
	 * This HAS to be called to generate and render the filterfield.
	 */
	public function render () {
		if ($this->label) {
			echo "<label for='$this->id'>$this->label:</label>";
		}
		echo $this->renderField ();
	}
	
	
}
