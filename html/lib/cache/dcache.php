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
abstract class DCache implements ICache {
	
	/**
	 * Here you can store your main cache object
	 * @var stdClass
	 */
	protected $_cache = null;
	
	/**
	 * Key prefix used to mix up the saved key
	 * @var string
	 */
	protected $_key_prefix = 'chacing_';
	
	/**
	 * DEfault expire time for the saved keys, in seconds
	 */
	const DEFAULT_EXPIRE = 3600;
	
	/**
	 * Standard class constructors
	 */
	public function __construct() {
		$this->_cache = null;
	}
	
	/**
	 * Initialize the caching mechanism
	 */
	public function init() {
		
	}
	
	/**
	 * Return false if the system setted is a true caching system or true if it's a dummy
	 * @return bool
	 */
	public function is_dummy() {
		
		return false;
	}
	
	/**
	 * Combine a key and hash the combination of the prefix and the specific key
	 * @param string $key the key to combine
	 * @return string the combined key
	 */
	protected function combineKey($key) {
		
		return md5($this->_key_prefix.$key);
	}
	
	/**
	 * Check if there is a valid cache entry for the key required
	 * @param string $key the key of the data that you want to be checked
	 * @return bool true if the key exists, false otherwise
	 */
	public function exist($key) {
		
		return ($this->_cache->get($this->combineKey($key)) !== false);
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
		
		return false;
	}
	
	/**
	 * Save a data value into the cache, only if the data is not saved
	 * @param string $key the key for the value
	 * @param mixed $value the value that you want to save
	 * @param type $options an array of options related to the value , for example array(
	 *	'expire' => 2000, // will save the data 
	 * )
	 * @return bool true if the data was saved successfully, false otherwise 
	 */
	public function add($key, $value, $options = array()) {
		
		if(!$this->exist($key)) {
			
			return $this->set($key, $data, $options);
		}
		return false;
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
		
		foreach($keys as $i => $key) {
			
			$this->set($key, $values[$i], $options);
		}
		return true;
	}
	
	/**
	 * Retrive a multiple combination of keys and datas, this will help you in saving
	 * the effort in order to save te data one by one
	 * @param array $keys the keys for the values
	 * @return bool the retrive value or the boolean value false
	 */
	public function mget($keys) {
		
		$values = array();
		foreach($keys as $i => $key) {
			
			$values[$key] = $this->get($key);
		}
		return $values;
	}
	
	/**
	 * Delete cache entries
	 * @param array $keys delete one or more cache entries from keys
	 * @return bool true if the value was deleted correctly, false otherwise
	 */
	public function delete($keys) {
		
		return true;
	}
	
	/**
	 * Completly delete all the cache stored data
	 * @return type true if the value was deleted correctly, false otherwise
	 */
	public function flush() {
		
		return true;
	}
	
}
