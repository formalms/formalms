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
 * @version 	$Id: lib.sessionsave.php 323 2006-05-10 16:35:25Z fabio $
 */

require_once( dirname(__FILE__).'/lib.generalsave.php' );

class Session_Save extends General_Save {
	/**
	 * initial max random value for mt_rand	
	 **/
	var $max_ini_rand = 10;
	/**
	 * multiplicative factor in case of fail
	 **/
	var $factor = 5;
	/**
	 * maximum set var try number
	 **/
	var $max_try = 4;
	
	function Session_Save() {
		parent::General_Save();
	}
	
	function getName( $basename = 'basename', $unique = false ) {
		
		if($unique !== false) {
			$_SESSION[$basename] = '';
			return $basename;
		}
		$basename .= '_'.time();
		
		$num_try = 0;
		$max = $this->max_ini_rand;
		
		$name = $basename.'_'.mt_rand(0, $max);
		
		while( ( $num_try < $this->max_try )) {
			if( !isset($_SESSION[$name]) ) {
				$_SESSION[$name] = '';
				return $name;
			}
			else {
				$num_try++;
				$max *= $this->factor;
				$name = $basename.'_'.mt_rand(0, $max);
			}
		}
		return false;
	}
	
	
	function nameExists( $var_name ) {
		return isset($_SESSION[$var_name]);
	}
	
	function save( $var_name, &$content, $serialize_for_me = true ) {

		if( $this->nameExists($var_name) ) {
			if( $serialize_for_me ) $_SESSION[$var_name] = addslashes(serialize($content));
			else $_SESSION[$var_name] = $content;
			
			return true;
		}
		return false;
	}
	
	function &load( $var_name, $deserialize_for_me = true ) {
		
		if( $this->nameExists($var_name) ) {
			
			if( $deserialize_for_me ) {
				$temp = unserialize(stripslashes($_SESSION[$var_name]));
				return $temp;
			} else return $_SESSION[$var_name];
		}
		$false_var = false;
		return $false_var;
	}
	
	/**
	 * function del( $name ) 
	 * @param string $var_name the name of the variable to delete
	 **/
	function delete( $var_name ) {
		if( $this->nameExists($var_name) )
			unset( $_SESSION[$var_name] );
	}
} 

?>