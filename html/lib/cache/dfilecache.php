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

class DFileCache extends DCache {
	
	public $compressed = false;
	
	protected $prefix = 'dcache_';
		
	/**
	 * Initialize the caching mechanism
	 */
	public function init() {}
	
	protected function fname($key) {
		
		return _files_.'/tmp/'.$this->prefix.$this->combineKey($key);
	}
	
	/**
	 * Check if there is a valid cache entry for the key required
	 * @param string $key the key of the data that you want to be checked
	 * @return bool true if the key exists, false otherwise
	 */
	public function exist($key) {
		
		$cache_file = $this->fname($key);
		if(file_exists($cache_file)) {
			if(@filemtime($cache_file) < time() - self::DEFAULT_EXPIRE) return false;
			return true;
		}
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
		
		// create a file and then save in it the cache
		$cache_file = $this->fname($key);
		if(is_file($cache_file)) @unlink($cache_file);
		$result = @file_put_contents($cache_file, serialize($data) ); 
		
		Log::add("Saving $key into file. ".($result !== false ? ' Success.' : ' Unable to save.' ));
		return $result;
	}
	
	/**
	 * Retrive a previously saved value from the cache
	 * @param string $key the key of the value you want to retrive
	 * @return mixed the retrive value or the boolean value false 
	 */
	public function get($key) {
		
		$cache_file = $this->fname($key);
		if(@filemtime($cache_file) < time() - self::DEFAULT_EXPIRE) return false;
		
		$data = @unserialize(file_get_contents($cache_file));
		Log::add("Reading $key from file cache.".($data !== false ? ' Success.' : ' Not found.' ));
		return $data;
	}
		
	/**
	 * Delete cache entries
	 * @param array $keys delete one or more cache entries from keys
	 * @return bool true if the value was deleted correctly, false otherwise
	 */
	public function delete($key) {
		
		Log::add("Deleting $key from file cache.");
		$cache_file = $this->fname($key);
		if(is_file($cache_file)) return @unlink($cache_file);
	}
	
	/**
	 * Completly delete all the cache stored data
	 * @return type true if the value was deleted correctly, false otherwise
	 */
	public function flush() {
		
		Log::add("Flushing memcache.");
		//retrive files with my prefix and delete them
		$files = glob(_files_.'/tmp/'.$this->prefix.'*');
		foreach ($files as $file) {
			
			@unlink($file);
		}
		return true;
	}
	
	/**
	 * Close open connection 
	 * @return type true if all is ok, false otherwise
	 */
	public function close() {}
	
}
