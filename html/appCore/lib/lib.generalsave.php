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
 * @package admin-library
 * @subpackage utility
 * @version  $Id:$
 */

class General_Save {
	
	/**
	 * function General_Save()
	 * class constructor
	 **/
	function General_Save() {
	
	}
	
	/**
	 * function getName()
	 * @return string a string with a valid name used for save information 
	 * reserve and return a unic name
	 **/
	function getName($basename = 'basename') {
		
	}
	
	/**
	 * function nameExists()
	 * control if the passed name is valid
	 **/
	function nameExists( $var_name ) {
	
	}
	
	/**
	 * function save( $name, $content )
	 * @param string $var_name the name of the variable to read
	 * @param mixed $content the content to save
	 * @return bool true if $var_name is valid else false 
	 **/
	function save( $var_name, &$content, $serialize_for_me = true ) {
		return true;
	}
	
	/**
	 * function load( $name )
	 * @param string $var_name the name of the variable to read
	 * @return mixed content of $var_name if $var_name is valid else false 
	 **/
	function &load( $var_name, $deserialize_for_me = true ) {
		$false_var = false;
		return $false_var;
	}
	
	/**
	 * function del( $name ) 
	 * @param string $var_name the name of the variable to delete
	 **/
	function delete( $var_name ) {
		return true;
	}
}

?>
