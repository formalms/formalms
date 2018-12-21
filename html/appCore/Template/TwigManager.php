<?php

namespace appCore\Template;

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

class TwigManager
{

    private static $instance = null;
    private $twig = null;

    /**
     * Singleton class, the constructor is private
     */
    private function __construct()
    {
        $loader = new \Twig_Loader_Filesystem();
        $debug = \Get::cfg('twig_debug', false);
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => $debug ? false : _files_ . '/cache/twig',
            'debug' => $debug
        ));

        $this->twig->addFunction(new \Twig_SimpleFunction('translate', function ($key, $module = false, $substitution = array(), $lang_code = false, $default = false) {
            return \Lang::t($key, $module, $substitution, $lang_code, $default);
        }));
        $this->twig->addFunction(new \Twig_SimpleFunction('evalPhp', function ($phpCode, $args = array()) {
            return call_user_func_array($phpCode, $args);
        }, array(
            'is_safe' => array('html')
        )));
        $this->twig->addGlobal('GLOBALS', $GLOBALS);
        $this->twig->addGlobal('Docebo', Docebo);
        if ($debug) {
            $this->twig->addExtension(new \Twig_Extension_Debug());
        }
    }

    /**
     * Get the TwigManager instance
     *
     * @param string $mvc_name
     * @return TwigManager
     * @throws Exception
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    public function addPathInLoader($view_path){

        $this->twig->getLoader()->addPath($view_path);
    }

    public function render($view_name, $data_for_view, $view_path = null)
    {
        if ($view_path == null) {
            throw new \Exception('mvc_name cannot be null!');
        }

        if (!$this->twig->getLoader()->exists($view_name)) {
            $this->twig->getLoader()->addPath($view_path);
        }

        return $this->twig->render($view_name, $data_for_view);
    }

    public static function getCacheDir()
    {
        return _files_ . '/cache/twig';
    }

}
