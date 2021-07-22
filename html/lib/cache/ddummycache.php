<?php defined("IN_FORMA") or die("You cannot access this file directly");

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
 * This is the standard cache class that will be the base
 * for all the specific implementation on the various possible 
 * instruments that you can use in order to implements the 
 * caching mechanism.
 */
class DDummyCache extends DCache {
	
	public function is_dummy() {
		
		return true;
	}
	
	/**
	 * Check if there is a valid cache entry for the key required
	 * @param string $key the key of the data that you want to be checked
	 * @return bool true if the key exists, false otherwise
	 */
	public function exist($key) {
		
		return false;
	}
	
	/**
	 * Save a data value into the cache
	 * @param string $key the key for the value
	 * @param mixed $value the value that you want to save
	 * @param type $options an array of options related to the value , for example array(
	 *	'expire' => 2000, // will save the data 
	 * )
	 * @return bool true if the data was saved successfully, false otherwise
	 */
	public function set($key, $data, $options = array()) {
		
		return true;
	}
	
	/**
	 * Retrive a previously saved value from the cache
	 * @param string $key the key of the value you want to retrive
	 * @return mixed the retrive value or the boolean value false 
	 */
	public function get($key) {
		
		return false;
	}
	
	/**
	 * Set a multiple combination of keys and datas, this will help you in saving
	 * the effort in order to save te data one by one
	 * @param array $keys the keys for the values
	 * @param array $values the values that you want to save
	 * @param type $options an array of options related to the value , for example array(
	 *	'expire' => 2000, // will save the data 
	 * )
	 * @return bool true if the data was saved successfully, false otherwise
	 */
	public function mset($keys, $values, $options = array()) {
		
		return true;
	}
	
	/**
	 * Retrive a multiple combination of keys and datas, this will help you in saving
	 * the effort in order to save te data one by one
	 * @param array $keys the keys for the values
	 * @return bool the retrive value or the boolean value false
	 */
	public function mget($keys) {
		
		return false;
	}
	
}
