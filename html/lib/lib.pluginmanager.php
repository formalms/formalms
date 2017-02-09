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

class PluginManager {
	protected static $plugin_list = array();
    public $category;
	/**
	 * Class constructor, this is a static class, don't call this
	 */
	public function __construct($category) {
        $this->category=$category;
    }

    public function load_lib(){
        include(_lib_."/plugins/".$this->category."/loader.php");
    }

    private function get_all_plugins(){
        if (empty(self::$plugin_list)) {
            require_once _adm_ . '/models/PluginAdm.php';
            $pluginAdm = new PluginAdm();
            self::$plugin_list = $pluginAdm->getPlugins(true);
        }
        return self::$plugin_list;
    }

    public function run_plugin($plugin,$method,$parameter=array()){
        $category=$this->category;
        if ($category!="") {
            $plugin_list = $this->get_all_plugins(true);
            if (in_array($plugin,array_column($plugin_list, 'name'))){
                $class_name=$plugin_list[$plugin];
                $this->load_lib();
                if (file_exists(_plugins_ . '/' . $class_name['name'] . '/Plugin.php')) {
                    include_once(_plugins_ . '/' . $class_name['name'] . '/Plugin.php');
                    if (file_exists(_plugins_ . '/' . $class_name['name'] . '/' . $category . '.php')) {
                        include_once(_plugins_ . '/' . $class_name['name'] . '/' . $category . '.php');
                        $namespace_class = "Plugin\\" . $class_name['name'] . "\\" . $category;
                        if (method_exists($namespace_class, $method)) {
                            return call_user_func_array(array($namespace_class, $method), $parameter);
                        }
                    }
                }
            }
        }
        return false;
    }

    public function run($method,$parameter=array()){
        $return=array();
        $category=$this->category;
        if ($category!="") {
            $plugin_list = $this->get_all_plugins(true);
            $this->load_lib();
            foreach ($plugin_list as $class_name) {
                if (file_exists(_plugins_ . '/' . $class_name['name'] . '/Plugin.php')) {
                    include_once(_plugins_ . '/' . $class_name['name'] . '/Plugin.php');
                    if (file_exists(_plugins_ . '/' . $class_name['name'] . '/' . $category . '.php')) {
                        include_once(_plugins_ . '/' . $class_name['name'] . '/' . $category . '.php');
                        $namespace_class = "Plugin\\" . $class_name['name'] . "\\" . $category;
                        if (method_exists($namespace_class, $method)) {
                            $return[] = call_user_func_array(array($namespace_class, $method), $parameter);
                        }
                    }
                }
            }
            return $return;
        }
        return false;
    }

    public function get_plugin($plugin,$parameter=array()){
        $category=$this->category;
        if ($category!="") {
            $plugin_list = $this->get_all_plugins(true);
            $this->load_lib();
            if (in_array($plugin,array_column($plugin_list, 'name'))){
                $class_name=$plugin_list[$plugin];
                $this->load_lib();
                if (file_exists(_plugins_ . '/' . $class_name['name'] . '/Plugin.php')) {
                    include_once(_plugins_ . '/' . $class_name['name'] . '/Plugin.php');
                    if (file_exists(_plugins_ . '/' . $class_name['name'] . '/' . $category . '.php')) {
                        include_once(_plugins_ . '/' . $class_name['name'] . '/' . $category . '.php');
                        $namespace_class = "Plugin\\" . $class_name['name'] . "\\" . $category;
                        return new $namespace_class($parameter);
                    }
                }
            }
        }
        return false;
    }
}

class PluginManagerException extends Exception {}

