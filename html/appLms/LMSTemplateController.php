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

    private $model;

    public function __construct() {

        require_once 'LMSTemplateModel.php';
        $this->model = new LMSTemplateModel();
    }

    public function show() {

        $this->showMenuOver();
    }

    private function showMenuOver() {

        // TODO: manca helpdesk.

        $this->render('menu_over', 'menu_over', array(
            'user'              => $this->model->getUser()
          , 'menu'              => $this->model->getMenu()
          , 'cart'              => $this->model->getCart()
          , 'profile'           => $this->model->getProfile()
          , 'credits'           => $this->model->getCredits()
          , 'career'            => $this->model->getCareer()
          , 'subscribeCourse'   => $this->model->getSubscribeCourse()
          , 'news'              => $this->model->getNews()
          , 'languages'         => $this->model->getLanguages()
        ));
    }

    private function render($view, $zone, $data = array()) {
        
        cout(TwigManager::getInstance()->render("$view.html.twig", $data, _base_ . "/templates/" . getTemplate() . "/layout/"), $zone);
    }
}
