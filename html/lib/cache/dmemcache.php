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

class DMemcache extends DCache {
	
	public $compressed = false;
	
	/**
	 * Initialize the caching mechanism
	 */
	public function init() {
		
		Log::add("Initializing memcache.");
		$cfg = Get::cfg('cache');
		$this->_cache = new Memcache();
		foreach($cfg['servers'] as $server) {
			$this->_cache->addServer($server['host'], $server['port'], $server['persistent'], $server['weight']);
		}
		if($cfg['compressed']) $this->compressed = true;
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
		//try add first because is more efficent
		$result = $this->_cache->add(
			$this->combineKey($key), 
			$data, 
			( isset($options['compressed']) || $this->compressed ? MEMCACHE_COMPRESSED : false ),	
			( isset($options['expire']) ? $options['expire'] : self::DEFAULT_EXPIRE )
		);
		if(!$result) $result = $this->_cache->add(
			$this->combineKey($key), 
			$data, 
			( isset($options['compressed']) || $this->compressed ? MEMCACHE_COMPRESSED : false ),	
			( isset($options['expire']) ? $options['expire'] : self::DEFAULT_EXPIRE )
		);
		Log::add("Saving $key into memcache. ".($result !== false ? ' Success.' : ' Unable to save.' ));
		return $result;
	}
	
	/**
	 * Retrive a previously saved value from the cache
	 * @param string $key the key of the value you want to retrive
	 * @return mixed the retrive value or the boolean value false 
	 */
	public function get($key) {
		
		$data = $this->_cache->get( $this->combineKey($key) );
		Log::add("Reading $key from memcache.".($data !== false ? ' Success.' : ' Not found.' ));
		return $data;
	}
		
	/**
	 * Delete cache entries
	 * @param array $keys delete one or more cache entries from keys
	 * @return bool true if the value was deleted correctly, false otherwise
	 */
	public function delete($keys) {
		
		Log::add("Deleting $keys from memcache.");
		return $this->_cache->delete($this->combineKey($keys));
	}
	
	/**
	 * Completly delete all the cache stored data
	 * @return type true if the value was deleted correctly, false otherwise
	 */
	public function flush() {
		
		Log::add("Flushing memcache.");
		return $this->_cache->flush();
	}
	
	/**
	 * Close open connection 
	 * @return type true if all is ok, false otherwise
	 */
	public function close() {
		
		return $this->_cache->close();
	}
}
