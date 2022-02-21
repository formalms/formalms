<?php defined("IN_FORMA") or die('Direct access is forbidden.');



use \appCore\Template\TwigManager;

abstract class TemplateController {

    public static function getInstance() {

        static $instance;

        if(!isset($instance)) {
            $instance = new static();
        }

        return $instance;
    }

    public static function init() {
        
        ob_start();
    }
    
    public static function flush() {

        ob_end_flush();
    }

    private $layout;
    protected $templateFolder;

    protected function __construct() { }

    public function setLayout($layout) {

        $this->layout = $layout;
    }

    public function show() {

        cout(ob_get_contents(), 'debug');
        ob_clean();
        Layout::render($this->layout);
    }

    protected function render($view, $zone, $data = []) {
        
        $GLOBALS['page']->addZone($zone);
        cout(TwigManager::getInstance()->render("$view.html.twig", $data, _templates_ . "/" . getTemplate() . "/layout/" . $this->templateFolder), $zone);
    }
}
