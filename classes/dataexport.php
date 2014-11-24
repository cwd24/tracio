<?php

/**
 * @version 1.0
 * 
 * @desc 
 * 
 * DataGrid - php implementation of .net style datagrid
 * (foolishly written by scratch).
 * 
 * Pretty decent used with JQuery's TableSorter plugin - must be attached separately at the mo'
 * 
 * Render a table given an array of data.
 * 
 * Example Usage: 
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
class DataExport extends DataGrid {
	
public function __construct() { 
        parent::__construct(); 
} 




}?>
