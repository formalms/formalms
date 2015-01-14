<?php

defined("IN_FORMA") or die('Direct access is forbidden.');

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

class TwigManager {

    private static $instance = null;
    private $twig = null;

    /**
     * Singleton class, the constructor is private
     */
    private function __construct() {
        $loader = new Twig_Loader_Filesystem();
        $this->twig = new Twig_Environment($loader, array(
            'cache' => _files_ . '/tmp',
            'debug' => Get::cfg('twig_debug', false)
        ));
        $this->twig->addFunction('translate', new Twig_Function_Function(function ($key, $module = false, $substitution = array(), $lang_code = false, $default = false) {
            return Lang::t($key, $module, $substitution, $lang_code, $default);
        }));
        $this->twig->addFunction('pluginUrl', new Twig_Function_Function(function ($resource) {
            $plugin_files = PluginManager::find_files();
            return '/' . _folder_plugins_ . '/' . $plugin_files[$resource] . '/' . $resource;
        }));
    }

    /**
     * Get the TwigManager instance
     * 
     * @param string $mvc_name
     * @return TwigManager
     * @throws Exception
     */
    public static function getInstance() {
        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    public function render($view_name, $data_for_view, $view_path = null) {
        if ($view_path == null) {
            throw new Exception('mvc_name cannot be null!');
        }

        if (!$this->twig->getLoader()->exists($view_name)) {
            $this->twig->getLoader()->addPath($view_path);
        }

        return $this->twig->render($view_name, $data_for_view);
    }

}
