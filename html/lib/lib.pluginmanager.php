<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class PluginManager
{
    protected static $plugin_list = [];
    public $category;

    /**
     * PluginManager constructor.
     *
     * @param $category
     */
    public function __construct($category)
    {
        $this->category = $category;
    }

    /**
     *  Load library of specified category.
     */
    public function load_lib()
    {
        include _lib_ . '/plugins/' . $this->category . '/loader.php';
    }

    /**
     * Returns all plugins.
     *
     * @return array
     */
    public static function get_all_plugins()
    {
        if (empty(self::$plugin_list)) {
            require_once _adm_ . '/models/PluginmanagerAdm.php';
            $PluginmanagerAdm = new PluginmanagerAdm();
            self::$plugin_list = $PluginmanagerAdm->getActivePlugins();
        }

        return self::$plugin_list;
    }

    /**
     * Given a plugin name $plugin and a method $method it runs the static method of the
     * specified plugin of the category set into the constructor. You can specify
     * parameters passing it through $parameters.
     *
     * @param $plugin
     * @param $method
     * @param array $parameter
     *
     * @return bool|mixed
     */
    public function run_plugin($plugin, $method, $parameter = [])
    {
        $category = $this->category;
        if ($category != '') {
            if (self::is_plugin_active($plugin)) {
                $this->load_lib();
                if (self::include_plugin_file($plugin, 'Plugin.php')) {
                    if (self::include_plugin_file($plugin, $category . '.php')) {
                        $namespace_class = 'Plugin\\' . $plugin . '\\' . $category;
                        if (method_exists($namespace_class, $method)) {
                            return call_user_func_array([$namespace_class, $method], $parameter);
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Given a method $method it runs all the static method of the category set into the
     * constructor. You can specify parameters passing it through $parameters.
     *
     * @param $method
     * @param array $parameter
     *
     * @return array|bool
     */
    public function run($method, $parameter = [])
    {
        $return = [];
        $category = $this->category;
        if ($category != '') {
            $plugin_list = self::get_all_plugins(true);
            $this->load_lib();
            foreach ($plugin_list as $class_name) {
                if (array_key_exists('name', $class_name) && self::include_plugin_file($class_name['name'], 'Plugin.php')) {
                    if (self::include_plugin_file($class_name['name'], $category . '.php')) {
                        $namespace_class = 'Plugin\\' . $class_name['name'] . '\\' . $category;
                        if (method_exists($namespace_class, $method)) {
                            $return[$class_name['name']] = call_user_func_array([$namespace_class, $method], $parameter);
                        }
                    }
                }
            }

            return $return;
        }

        return false;
    }

    /**
     * Given a plugin $plugin it returns an instance of the specified plugin of the category
     * set into the constructor passing $parameters into its constructor.
     *
     * @param $plugin
     * @param array $parameter
     *
     * @return bool
     */
    public function get_plugin($plugin, $parameter = [])
    {
        $category = $this->category;
        if ($category != '') {
            if (self::is_plugin_active($plugin)) {
                $this->load_lib();
                if (self::include_plugin_file($plugin, 'Plugin.php')) {
                    if (self::include_plugin_file($plugin, $category . '.php')) {
                        $namespace_class = 'Plugin\\' . $plugin . '\\' . $category;

                        return new $namespace_class($parameter);
                    }
                }
            }
        }

        return false;
    }

    public static function is_plugin_active($plugin)
    { 

        return in_array($plugin, array_keys(self::get_all_plugins(true)));
    }

    private static function include_plugin_file($plugin, $file)
    {
        $path = _plugins_ . '/' . $plugin . '/' . $file;
        if (file_exists($path)) {
            include_once $path;

            return true;
        } else {
            return false;
        }
    }

    private static function get_plugin_by_request($mvc_app, $mvc_name)
    {
        $query = ' SELECT p.name, r.controller, r.model '
                . ' FROM %adm_requests r'
                . ' INNER JOIN %adm_plugin p'
                . '     ON r.plugin = p.plugin_id'
                . ' WHERE 1 = 1'
                . "     AND r.app = '$mvc_app'"
                . "     AND r.name = '$mvc_name'"
                . '     AND p.active = 1'
                . ' ORDER BY p.priority ASC'; // TODO: valutare se usare invece funzione is_plugin_active"

        $r = sql_query($query);
        list($plugin, $controller, $model) = sql_fetch_row($r);

        return [$plugin, $controller, $model];
    }

    /**
     * Given a mvc app (for example lms or adm) $mvc_app and an mvc name $mvc_name, it returns
     * an instance of the controller linked to the specified mvc app and name searching in the
     * table core_requests.
     *
     * @param $mvc_app
     * @param $mvc_name
     *
     * @return bool
     */
    public static function get_feature($mvc_app, $mvc_name)
    {
        list($plugin, $controller, $model) = self::get_plugin_by_request($mvc_app, $mvc_name);
        if (!isset($plugin) || !isset($controller) || !isset($model)) {
            return false;
        }

        if (!self::include_plugin_file($plugin, 'Plugin.php')) {
            return false;
        }

        switch (strtolower($mvc_app)) {
            case 'adm':
                $path_controller = _folder_adm_ . '/controllers/';
                $path_model = _folder_adm_ . '/models/';
                break;
            case 'alms':
                $path_controller = _folder_lms_ . '/admin/controllers/';
                $path_model = _folder_lms_ . '/admin/models/';
                break;
            case 'lms':
            case 'lobj':
                $path_controller = _folder_lms_ . '/controllers/';
                $path_model = _folder_lms_ . '/models/';
                break;
            default: return false;
        }

        if (!self::include_plugin_file($plugin, 'Features/' . $path_controller . $controller . '.php')) {
            return false;
        }

        self::include_plugin_file($plugin, 'Features/' . $path_model . $model . '.php');

        return new $controller($mvc_name);
    }

    public static function hook()
    {
        $plugin_list = self::get_all_plugins();
        foreach ($plugin_list as $plugin) {
            if(array_key_exists('name',$plugin)) {
                self::include_plugin_file($plugin['name'], 'Event.php');
            }
        }
    }

    public static function initialize()
    {
        $plugin_list = self::get_all_plugins();
        foreach ($plugin_list as $plugin) {
            if(array_key_exists('name',$plugin)) {
                self::include_plugin_file($plugin['name'], $plugin['name'] . '.php');
            }
            
        }
    }
}

class PluginManagerException extends Exception
{
}
