<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class Controller
{
    protected $_mvc_name = 'controller';

    protected $_default_action = 'show';

    protected \Symfony\Component\HttpFoundation\Request $request;

    protected $session;

    public function __construct($mvc_name = '')
    {
        $this->_mvc_name = $mvc_name;
        $this->request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();
        $this->session = $this->request->getSession();
        $this->init();
    }

    /**
     * Initialize the istance of the controller, this method will be called from the constructor.
     */
    public function init()
    {
    }

    /**
     * This method can be used in order to modify the standard task requested to the mvc.
     *
     * @param string $task the task requested
     *
     * @return string the resulted task
     */
    public function parseTask($task)
    {
        return $task;
    }

    /**
     * This method will be automatically called the index.php or ajax server and will try to call the requested action that need to be performed.
     * Before this method call the main script will empty the php bugger (ob_*) and will retrive all the information echoed reading it with
     * a ob_get_clean() call, so if you need to reuse or reset the content printed you can read or clean the ob_* buffer.
     *
     * @param string $task the task that must be performed
     */
    public function request($task = false)
    {
        $task = $this->parseTask($task);

        if ($task != false && method_exists($this, $task)) {
            $this->$task();
        } elseif ($task != false && method_exists($this, $task . 'Task')) {
            $task = $task . 'Task';
            $this->$task();
        } elseif (method_exists($this, $this->_default_action)) {
            $method = $this->_default_action;
            $this->$method();
        }
    }

    /**
     * This method will return the absolute path for the view inclusions.
     *
     * @return string the absolute path to the view's files
     */
    public function viewPath()
    {
        return _base_ . '/views';
    }

    /**
     * This method will render a specific view for this mvc.
     *
     * @param string $view_name     the name of the view, must be equal to a php file inside the view folder for this mvc without the .php extension
     * @param array  $data_for_view an array of data that will be passed to the view
     * @param bool   $return        if true the rendering will be returned instead of printed as an echo
     *                              The view php scope will be the controller ($this) but you can pass data that will be extracted into the view scope. For example if you pass
     *                              array(
     *                              'bar' => 'foo'
     *                              ) you will have a $bar var inside the view with the value setted to 'foo'
     */
    public function render($view_name, $data_for_view = false, $return = false, $morePaths = [])
    {
        if (is_array($data_for_view)) {
            extract($data_for_view, EXTR_SKIP);
        }

        $paths = $morePaths;
        $extensions = [];
        if (method_exists($this, 'templatePath')) {
            $paths[] = $this->templatePath();
        }
        $paths[] = $this->viewPath();

        $tplengine = FormaLms\lib\Get::cfg('template_engine', []);

        foreach ($tplengine as $tplkey => $tpleng) {
            if (isset($tpleng['ext']) && !is_array($tpleng['ext'])) {
                $tplengine[$tplkey]['ext'] = [$tpleng['ext']];
            }
            $extensions[$tplkey] = $tplengine[$tplkey]['ext'];
        }

        if (isset($extensions['twig']) && !in_array('.html.twig', $extensions['twig'])) {
            $extensions['twig'][] = '.html.twig';
        } else {
            $extensions['twig'] = ['.html.twig'];
        }

        $extensions['php'] = ['.php'];

        $extension = '';
        $path = '';
        $tplkey = '';
        foreach ($paths as $p) {
            foreach ($extensions as $k => $e) {
                foreach ($e as $ext_string) {
                    $fullpath = $p . '/' . $this->_mvc_name . '/' . $view_name . $ext_string;
                    if (file_exists($fullpath)) {
                        $extension = $ext_string;
                        $path = $p;
                        $tplkey = $k;
                        break;
                    }
                }
                if ($extension != '') {
                    break;
                }
            }
            if ($extension != '') {
                break;
            }
        }

        switch ($tplkey) {
            case 'php':
                include \FormaLms\lib\Forma::inc($path . '/' . $this->_mvc_name . '/' . $view_name . $extension);
                break;
            case 'twig':
                $path = \FormaLms\lib\Forma::inc($path . '/' . $this->_mvc_name . '/' . $view_name . $extension);
                $view_path = dirname($path);

                foreach ($paths as $path) {
                    FormaLms\appCore\Template\TwigManager::getInstance()->addPathInLoader($path);
                }
                echo FormaLms\appCore\Template\TwigManager::getInstance()->render($view_name . $extension, $data_for_view, $view_path);
                break;
            default:
                //die( 'FILENOTFOUND');
                include \FormaLms\lib\Forma::inc($this->viewPath() . '/' . $this->_mvc_name . '/' . $view_name . $extension);
                break;
        }

        if ($return) {
            $content = ob_get_contents();
            @ob_clean();

            return $content;
        }
    }

    /**
     * This method will manage a widget (find them in the widget/ folder), the $widget_name must ber a valid widget name
     *  (a folder name inside the widget/ folder for example), the widget class will be automatically istanced with the params setted.
     *
     * @param string $widget_name the widget name
     * @param array  $params      the params that wil be passed to the widget
     *
     * @return Widget the widget instance
     */
    public static function widget($widget_name, $params = null)
    {
        $widget_name = strtolower($widget_name);
        require_once _base_ . '/widget/' . $widget_name . '/lib.' . $widget_name . '.php';

        $widget_class = ucfirst($widget_name . 'Widget');
        // Instantiate the widget class
        $widget_obj = new $widget_class();
        // Set the params for the widget in the class properties
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $widget_obj->$key = $value;
            }
        }
        // Initialize the widget
        $widget_obj->init();
        // Run the the widget (will print the view)
        $widget_obj->run();
        // Return the widget for further use
        return $widget_obj;
    }

    public function beginWidget($widget_name, $params = null)
    {
        $widget_name = strtolower($widget_name);
        require_once _base_ . '/widget/' . $widget_name . '/lib.' . $widget_name . '.php';

        $widget_class = ucfirst($widget_name . 'Widget');
        // Instantiate the widget class
        $widget_obj = new $widget_class();
        // Set the params for the widget in the class properties
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $widget_obj->$key = $value;
            }
        }
        // Initialize the widget
        $widget_obj->init();
        // Return the widget for further use
        return $widget_obj;
    }
}
