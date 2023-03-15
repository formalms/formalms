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

use FormaLms\appCore\Template\TwigManager;

abstract class TemplateController
{
    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new static();
        }

        return $instance;
    }

    public static function init()
    {
        ob_start();
    }

    public static function flush()
    {
        ob_end_flush();
    }

    private $layout;
    protected $templateFolder;

    protected function __construct()
    {
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public function show()
    {
        cout(ob_get_contents(), 'debug');
        ob_clean();
        Layout::render($this->layout);
    }

    protected function render($view, $zone, $data = [])
    {
        $GLOBALS['page']->addZone($zone);
        cout(TwigManager::getInstance()->render("$view.html.twig", $data, _templates_ . '/' . getTemplate() . '/layout/' . $this->templateFolder), $zone);
    }
}
