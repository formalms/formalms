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

class DApcCache extends DCache {
	
	public $compressed = false;
	
	/**
	 * Initialize the caching mechanism
	 */
	public function init() {
		parent::init();
	}
	
	/**
	 * Check if there is a valid cache entry for the key required
	 * @param string $key the key of the data that you want to be checked
	 * @return bool true if the key exists, false otherwise
	 */
	public function exist($key) {
		
		return apc_exists($key);
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
		
		return apc_add($key,$value,( isset($options['expire']) ? $options['expire'] : self::DEFAULT_EXPIRE ));;
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
		
		return apc_store($key,$value,( isset($options['expire']) ? $options['expire'] : self::DEFAULT_EXPIRE ));
	}
	
	/**
	 * Retrive a previously saved value from the cache
	 * @param string $key the key of the value you want to retrive
	 * @return mixed the retrive value or the boolean value false 
	 */
	public function get($key) {
		
		$data = apc_fetch($key);
		Log::add("Reading $key from apc.".($data !== false ? ' Success.' : ' Not found.' ));
		return $data;
	}
	
	/**
	 * Retrive a multiple combination of keys and datas, this will help you in saving
	 * the effort in order to save te data one by one
	 * @param array $keys the keys for the values
	 * @return bool the retrive value or the boolean value false
	 */
	public function mget($keys) {
		
		return apc_fetch($keys);
	}
	
	/**
	 * Delete cache entries
	 * @param array $keys delete one or more cache entries from keys
	 * @return bool true if the value was deleted correctly, false otherwise
	 */
	public function delete($keys) {
		
		Log::add("Deleting $keys from apc.");
		return apc_delete($keys);
	}
	
	/**
	 * Completly delete all the cache stored data
	 * @return type true if the value was deleted correctly, false otherwise
	 */
	public function flush() {
		
		Log::add("Flushing apc.");
		return apc_clear_cache('user');
	}
	
}
