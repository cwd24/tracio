<?php

/**
 * The FilterManager class handles all the FilterFields instances on a page and generates the filter query.
 * 
 * It works by wrapping a SELECT query around the original query with the filter clauses (instances of FilterField).
 * 
 * @version 1.0
 *
 */
class FilterManager {
	
	private $filtersArray = array ();
	
	private $filters = array ();
	
	private $defaultPerPage = 50;
	private $perPage = 50;
	
	private $method = 'GET';
	
	/** 
	 * Constructor. Usually called in the top section of php code.
	 * 
	 * Usage: $filtersMgr = new FilterManager ('POST');
	 * 
	 * @param string $method Form sending method ('GET' or 'POST'). GET is assumed as default.
	 */
	function __construct($method) {
		if ($method == 'POST') {
			$this->method = "POST";
		} else if ($method == 'GET' || empty ($method)) {
			$this->method = "GET";
		}
	}
	
	/**
	 * This is called to add a FilterField instance to the FilterManager
	 * for it to manage.
	 * 
	 * Example: 
	 * $filter = new FilterField ('surname', 'surname', 'text', 'Surname');
	 * $filter->render ();
	 * $filtersMgr->addFilterField ($filter);
	 * 
	 * @param FilterField $filterfieldObj
	 */
	public function addFilterField ($filterfieldObj) {
		$this->filters[] = $filterfieldObj;
	}
	
	/**
	 * Add new search parameter type, starts with.
	 * 
	 * @param str $field
	 * @param str $needle
	 */
	public function fieldStartsWith ($field, $needle) {
		$this->addNewFilter ($field, $needle, 'sw');
	}
	
	/**
	 * Add new search parameter type, ends with.
	 *
	 * @param str $field
	 * @param str $needle
	 */
	public function fieldEndsWith ($field, $needle) {
		$this->addNewFilter ($field, $needle, 'ew');
	}
	
	/**
	 * Add new search parameter type, contains.
	 * 
	 * This is the default search type.
	 *
	 * @param str $field
	 * @param str $needle
	 */
	public function fieldContains ($field, $needle) {
		$this->addNewFilter ($field, $needle, 'contains');
	}
	
	/**
	 * Add new search parameter type, equals (exact).
	 *
	 * @param str $field
	 * @param str $needle
	 */
	public function fieldEquals ($field, $needle) {
		$this->addNewFilter ($field, $needle, 'equals');
	}
	
	/**
	 * Add a new filter to the query with db field, search term, search type (e.g. contains, starts with, not)
	 * and whether filter should ALWAYS be added to the query ($force).
	 * 
	 * @param string $field database field to query against.
	 * @param string $needle the search term that we are looking for.
	 * @param string $direction the search type (can be starts with, ends with, contains, exact or not)
	 * @param boolean $force whether this should be in the query everytime, regardless if it is set
	 */
	private function addNewFilter ($field, $needle, $direction='contains', $force) {
		$this->filtersArray [] = array ('field'=>$field, 'needle'=>$needle, 'searchtype'=>$direction, 'force'=>$force);
	}
	
	/**
	 * @param int $numPerPage
	 * @deprecated was used on admin/advisor.php, but has been incorporated into datagrid
	 */
	public function setPageCount ($numPerPage) {
		if (is_int($numPerPage)) {
			$this->perPage = $numPerPage;
		} else {
			$this->perPage = $this->defaultPerPage;
		}
	}
		
	/**
	 * @deprecated
	 */
	public function paginate () {
		
	}
	
	/**
	 * Generate the SQL where clause for an individual filter.
	 * 
	 * @param FilterField $filterObject
	 * @return string sql where query code for the filterfield
	 */
	private function createSqlForField ($filterObject) {
		// if not empty search (needle)
		
		if (empty ($filterObject['needle']) && $filterObject['force'] == false) {
		 return '';
		}

		
		switch ($filterObject['searchtype']) {
			case "sw":
				$str = ' ' . $filterObject['field'] . ' like "' . $filterObject['needle'] . '%" ';
				break;
			case "ew":
				$str = ' ' . $filterObject['field'] . ' like "%' . $filterObject['needle'] . '" ';
				break;
			case "contains":
				$str = ' ' . $filterObject['field'] . ' like "%' . $filterObject['needle'] . '%" ';
				break;
			case "exact":
				$str = ' ' . $filterObject['field'] . ' = "' . $filterObject['needle'] . '" ';
				break;
			case "not":
				$str = ' ' . $filterObject['field'] . ' != "' . $filterObject['needle'] . '" ';
				break;				
		}
		return $str;
	}
	
	/**
	 * Last thing to call from user perspective to generate the filtered query sql.
	 * 
	 * Takes in the original query and wraps the filter code around it.
	 * 
	 * @param string $originalQuery the query before any filtering was applied.
	 * @return string the full filtered query for execution.
	 */
	public function generateQuery ($originalQuery) {
		
		// go fetch all the filters
		$this->checkForAppliedFilters ();
		
		$str = '';
		$counter = 0;
				
		if (!empty ($this->filtersArray)) {
			foreach ($this->filtersArray as $filter) {
				// is there any input in this form element?
				if (!empty ($filter['needle']) || $filter['force'] == true) {
					if ($counter ++ > 0) {
						$str .= ' and ';
					}
					$str .= $this->createSqlForField ($filter);
				}
			}
			
			// if there are filters to be applied, wrap our sql around the original query
			if (!empty ($str)) {
				$str = 'select * from (' . $originalQuery . ') as x where ' . $str;
			} else {
				// no filters found, so just return original query.
				$str = $originalQuery;
			}
		} else {
			$str = $originalQuery;
		}
		
		
		return $str;
	}
	
	/**
	 * Go get the filterfield instances and add them to the filtersArray.
	 * @usedby generateQuery()
	 */
	public function checkForAppliedFilters () {
		
		if ($this->method == 'GET') {
			$input = $_GET;
		} else {
			$input = $_POST;
		}
		
		if (!empty ($this->filters)) {
			foreach ($this->filters as $filterfield) {
				$vars = ($filterfield->get ());
				if (isset ($input[$vars['name']])) {
					$this->addNewFilter($vars['name'], $input[$vars['name']], $vars['searchtype'], $vars['force']);
				}
								
			}
		}
	}
	
}
