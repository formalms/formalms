<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

/**
 * @package admin-core
 * @subpackage io-operation
 * @version 	$id$
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
**/

/** base directory for search io files **/
define( 'DOCEBOIMPORT_BASEDIR', $GLOBALS['where_files_relative'].'/common/iofiles/');

/** Index for column name */
define( 'DOCEBOIMPORT_COLNAME', 0 );
/** Index for column id */
define( 'DOCEBOIMPORT_COLID', 1 );
/** Index for column data type */
define( 'DOCEBOIMPORT_DATATYPE', 2 );
/** Index for column mandatory flag */
define( 'DOCEBOIMPORT_COLMANDATORY', 3 );
/** Index for column default value */
define( 'DOCEBOIMPORT_DEFAULT', 4 );

/** Unknown data type */
define( 'DOCEBOIMPORT_DATATYPE_UNKNOWN', -1);
/** This field should be ignored */
define( 'DOCEBOIMPORT_IGNORE', 'ignorefield' );
/** Indicate that a connection don't have data to process **/
define( 'DOCEBO_IMPORT_NOTHINGTOPROCESS', 1);

/** 
 * abstract class for define connection to data source.
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	1.1
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnector {
	
	var $cache_inserted = FALSE;
	
	/**
	 * constructor
	 * @param array params	 
	 **/
	function DoceboConnector( $params ) {} 	 	

	/**
	 * set configuration
	 * @param array $params
	 **/	 		
	function set_config( $params ) {}
	
	/**
	 * get configuration
	 * @return array 	 
	 **/	 	
	function get_config() {}
	
	/**
	 * get configuration UI
	 * @return  DoceboConnectorUI	 
	 **/	 	
	function get_configUI() {}
	
	/**
	 * execute the connection to source
	**/
	function connect() {}
	
	/**
	 * execute the close of the connection 
	**/
	function close() {}

	/**
	 * Return the type of the connector
	 **/
	function get_type_name() {}
	
	/**
	 * Return the description of the connector
	 **/
	function get_type_description() {}
	
	
	/**
	 * Return the name of the connection 
	 **/
	function get_name() {}	 	

	/**
	 * Return the description of the connection 
	 **/
	function get_description() {}	 	
	
	/**
	 * indicate if this connection is read only
	 * @return boolean TRUE is connector is read only	 
	 **/	 	
	function is_readonly() {}

	/**
	 * indicate if this connection is write only
	 * @return boolean TRUE is connector is read only	 
	 **/	 	
	function is_writeonly() {}
	
	/**
	 * indicate if this source produce raw rows.
	 * raw rows producer don't need map
	**/
	function is_raw_producer() { return FALSE; }
	
	/**
	 * Return the number of columns	
	 * @return integer the number of columns in the source
	**/
	function get_tot_cols() {}

	/**
	 * @return array the array of columns descriptor
	 *				- DOCEBOIMPORT_COLNAME => string the name of the column
	 *				- DOCEBOIMPORT_COLID => string the id of the column (optional,
	 *										 same as COLNAME if not given)
	 *				- DOCEBOIMPORT_COLMANDATORY => bool TRUE if col is mandatory
	 *				- DOCEBOIMPORT_DATATYPE => the data type of the column
	 *				- DOCEBOIMPORT_DEFAULT => the default value for the column (Optional)
	 * For readonly connectos only 	DOCEBOIMPORT_COLNAME and DOCEBOIMPORT_DATATYPE
	 * are required	 				 
	**/
	function get_cols_descripor() {}
	
	/**
	 * @return array fist row of data in source
	**/
	function get_first_row() {}
	
	/**
	 * @return array next row of data in source (the field must be in the same order of the get_cols_descripor)
	**/
	function get_next_row() {}
	
	/**
	 * @return bool TRUE if the source is at EOF
	**/
	function is_eof() {}
	
	/**
	 * @return int the actual position in source. Base index = 0
	**/
	function get_row_index() {}
	
	/** 
	 * @return integer the number of mandatory columns to import
	**/
	function get_tot_mandatory_cols() {}

	/**
	 * This function perform the data insertion in to the source
	 * @param array $row data to insert; is an array with keys the names of cols and
	 *				values the data (must contain the pk)
	 * @param array $pk set of primary keys (col_name => value pairs)
	 * @return TRUE if the row was succesfully inserted, FALSE otherwise
	**/
	function add_row( $row, $pk ) {
		$row = $row;
	}
	
	/**
	 * This method delete a record based on a set of keys
	 * @param array $pk an array of col_name => value pairs
	 * @return TRUE if successfully deleted, FALSE otherwise
	 **/
	function delete_bypk( $pk ) {
		$pk = $pk;
	}
	
	/**
	 * This method delete all records not present in array of keys
	 * @param array $arr_pk an array of pk of col_name => value pairs
	 * @return TRUE if successfully deleted, FALSE otherwise
	 **/
	function delete_all_filtered( $pk ) {
		$pk = $pk;
	}
	
	/**
	 * The delete_all_notinserted remove all records not inserted
	 * during this connection
	 * @return int number of removed items
	 */
	function delete_all_notinserted() {}
	
	
	/**
	 * Use this method to set/unset the cache of inserted row 
	 * in this connection.
	 * @param bool $set
	 */
	function enable_cache_inserted( $set ) { $this->cache_inserted = $set; } 
	
	/**
	 * Return the last generated error 
	 * @return the description of the last error
	 **/	  	 	 	
	function get_error() {}
		
}

/**
 * abstract class for define connector's UI configurator.
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	1.1
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorUI {
	var $form = NULL;
	var $lang = NULL;
	
	/**
	 * This method is to set the $lang object
	 * @param DoceboLang $lang
	 **/
	function set_lang( &$lang ) {
		$this->lang = $lang;
	}
	
	/**
	 * This method is to set the $form object
	 * @param Form $form
	 **/
	function set_form( &$form ) {
		$this->form = $form;
	}
	
	
	/** 
	 * This function is colled by connection manager before any other function.
	 * After this function the connection manager can call all other function,
	 * @param array $get the url get parameters
	 * @param array $post the post paramteres 	 	 
	 **/ 
	function parse_input( $get, $post ) {}
	
	/** 
	 * With this function we can test if the configurator want a next button
	 * @return boolean TRUE if we must produce a next button
	 **/
	function show_next() {}

	/** 
	 * With this function we can test if the configurator want a prev button
	 * @return boolean TRUE if we must produce a prev button
	 **/
	function show_prev() {}

	/** 
	 * With this function we can test if the configurator want a finish button
	 * @return boolean TRUE if we must produce a finish button
	 **/
	function show_finish() {}	

	/**
	 * With this function the container can tell connector configurator that
	 *	the user as pressed next button
	 **/
	function go_next() {}

	/**
	 * With this function the container can tell connector configurator that
	 *	the user as pressed prev button
	 **/
	function go_prev() {}

	/**
	 * With this function the container can tell connector configurator that
	 *	the user as pressed finish button
	 **/
	function go_finish() {}
	
	/**
	 * return a string for set header for UI configurator
	 * @return string the html to put in HTML header 	  
	 **/	 	
	function get_htmlheader() {}
	
	
	/** return the html that draw interface of configurator
	 *  @return string html that draw interface of configurator
	 * */	 
	function get_html( $get, $post ) {}
	
	
	public function filterParams(& $params) {
		$filter_input  = new FilterInput();
		$filter_input->tool = Get::cfg('filter_tool', 'htmlpurifier');
		$params =$filter_input->clean($params);
	}
	
}

?>
