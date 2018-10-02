<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   forma.lms - The E-Learning Suite                                        |
|                                                                           |
|   Copyright (c) 2013-2023 (forma.lms)                                     |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

use \appCore\Template\TwigManager;

class LMSTemplateController {

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

    private $model;
    private $layout;

    private function __construct() {

        require_once 'LMSTemplateModel.php';
        $this->model = new LMSTemplateModel();

        if(!empty($_SESSION['layoutToRender'])) {
            $this->setLayout($_SESSION['layoutToRender']);
        } elseif(isset($_SESSION['idCourse'])) {
            $this->setLayout('lms');
        } else {
            $this->setLayout('lms_user');
        }
    }

    public function setLayout($layout) {

        $this->layout = $layout;
    }

    public function show() {

        $this->showLogo();
        $this->showMenu();
        $this->showCart();
        $this->showProfile();
        cout(ob_get_contents(), 'debug');
        ob_clean();
        Layout::render($this->layout);
    }

    private function showLogo() {
        
        $this->render('logo', 'logo', array(
            'user' => $this->model->getUser()
          , 'logo' => $this->model->getLogo()
        ));
    }

    private function showMenu() {
        
        $this->render('menu', 'menu', array(
            'user' => $this->model->getUser()
          , 'menu' => $this->model->getMenu()
        ));
    }

    private function showCart() {
        
        $this->render('cart', 'cart', array(
            'user' => $this->model->getUser()
          , 'cart' => $this->model->getCart()
        ));
    }

    private function showProfile() {
        
        $this->render('profile', 'profile', array(
            'user'              => $this->model->getUser()
          , 'profile'           => $this->model->getProfile()
          , 'credits'           => $this->model->getCredits()
          , 'career'            => $this->model->getCareer()
          , 'subscribeCourse'   => $this->model->getSubscribeCourse()
          , 'news'              => $this->model->getNews()
          , 'languages'         => $this->model->getLanguages()
        ));
    }

    private function render($view, $zone, $data = array()) {
        
        $GLOBALS['page']->addZone($zone);
        cout(TwigManager::getInstance()->render("$view.html.twig", $data, _base_ . "/templates/" . getTemplate() . "/layout/" . _folder_lms_), $zone);
    }
}
