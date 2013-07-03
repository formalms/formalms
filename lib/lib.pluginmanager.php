<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
|	DOCEBO - The E-Learning Suite											|
| 																			|
|	Copyright (c) 2011 (Docebo)												|
|	http://www.docebo.com													|
|	License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

class PluginManager {

	protected static $files = false;

	/**
	 * Class constructor, this is a static class, don't call this
	 */
	public function __construct() {}

	public static function find_files() {
		
		$files	= false;
		$files = Docebo::cache()->get('plugin_files');
		if($files === false) {

			$files  = self::file_substituton(_plugins_);
			Docebo::cache()->set('plugin_files', $files);
		}
		return $files;
	}

	public static function file_substituton($path) {

		// user SPL iterator to recursive list all the plugins files
		$dir_iterator = new RecursiveDirectoryIterator($path);
		$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

		$plugins = array();
		foreach ($iterator as $file) {
			
			if ($file->isFile()/* && (strpos($file->getPathname(), '.svn') === false) */) {

				// this will be the path of the php file inside the plugin with the plugin name
				$file_path = ltrim(str_replace(array($path, '\\'), array('', '/'), $file->getPathname()), '/');

				// separate plugin name and the path of the file in order to have an array key with the
				// file that must be included instead of the original one
				$plugin_name = substr($file_path, 0, strpos($file_path, '/'));
				$file_path = substr($file_path, strlen($plugin_name.'/'));

				$plugins[$file_path] = $plugin_name;
			}
		}
		return $plugins;
    }

	public static function autoload() {
		
		// user SPL iterator to recursive list all the plugins files
		$paths = glob( _plugins_.'/*' );
		
		foreach($paths as $folder) {
			
			if(file_exists($folder.'/autoload.php')) include($folder.'/autoload.php');
		}
    }
	
	public static function config() {
		
		// user SPL iterator to recursive list all the plugins files
		$paths = glob( _plugins_.'/*' );
		
		$plugin_cfg = array();
		foreach($paths as $folder) {
			
			$cfg = array();
			if(file_exists($folder.'/config.php')) {
				include( $folder.'/config.php');
				$plugin_cfg = array_merge($plugin_cfg, $cfg);
			} 
		}
		return $plugin_cfg;
    }
}

class PluginManagerException extends Exception {}

