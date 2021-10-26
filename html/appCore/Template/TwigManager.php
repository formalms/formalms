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

use appCore\Template\Extensions\FormExtension;
use appCore\Template\Extensions\GetExtension;
use appCore\Template\Extensions\LangExtension;
use appCore\Template\Extensions\LayoutExtension;
use appCore\Template\Extensions\UiFeedbackExtension;
use appCore\Template\Extensions\UtilExtension;
use appCore\Template\Extensions\YuiExtension;
use appCore\Template\Services\ClientService;
use Twig\Extension\OptimizerExtension;
use Twig\Extensions\ArrayExtension;
use Twig\Extensions\DateExtension;
use Twig\Extensions\I18nExtension;
use Twig\Extensions\IntlExtension;
use Twig\Extensions\TextExtension;

class TwigManager
{

    private static $instance = null;
    private $twig = null;

    /**
     * Singleton class, the constructor is private
     */
    private function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader();
        $debug = \Get::cfg('twig_debug', false);
        $this->twig = new \Twig\Environment($loader, array(
            'cache' => $debug ? false : _files_ . '/cache/twig',
            'debug' => $debug
        ));
        $this->addDefaultPaths();
        $this->twig->addExtension(new ArrayExtension());
        $this->twig->addExtension(new DateExtension());
        $this->twig->addExtension(new FormExtension());
        $this->twig->addExtension(new GetExtension());
        //$this->twig->addExtension(new IntlExtension());
        $this->twig->addExtension(new I18nExtension());
        $this->twig->addExtension(new LangExtension());
        $this->twig->addExtension(new LayoutExtension());
        $this->twig->addExtension(new UiFeedbackExtension());
        $this->twig->addExtension(new UtilExtension());
        $this->twig->addExtension(new YuiExtension());
        $this->twig->addExtension(new TextExtension());

        $this->twig->addGlobal('clientConfig', addslashes(json_encode(ClientService::getInstance()->getConfig())));
        $this->twig->addGlobal('GLOBALS', $GLOBALS);
        if ($debug) {
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        }
    }

    /**
     * @return \Twig\Environment
     */
    private function getTwig(): \Twig\Environment
    {
        return $this->twig;
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

    private function addDefaultPaths()
    {
        $defaultPaths = [
            _adm_ . '/views',
            _lms_ . '/views',
            _lms_ . '/admin/views',
            _templates_ . '/' . getTemplate() . '/layout'
        ];

        foreach ($defaultPaths as $path) {
            $this->addPathInLoader($path);
        }
    }

    public function addPathInLoader($view_path)
    {
        $this->twig->getLoader()->addPath($view_path);
    }

    public function render($view_name, $data_for_view, $view_path = null)
    {
        if (!empty($view_path) && !$this->twig->getLoader()->exists($view_name)) {
            $this->twig->getLoader()->addPath($view_path);
        }

        return $this->twig->render($view_name, $data_for_view);
    }

    public function addExtention(\Twig\Extension\AbstractExtension $twigExtention) {
        $this->twig->addExtension($twigExtention);
    }

    public static function getCacheDir()
    {
        return _files_ . '/cache/twig';
    }

}
