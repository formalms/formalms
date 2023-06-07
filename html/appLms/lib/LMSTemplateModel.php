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

class LMSTemplateModel
{
    private $user;

    public function __construct()
    {
        $this->user = \FormaLms\lib\FormaUser::getCurrentUser();
    }

    public function selectLayout()
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        if ($session->has('layoutToRender') && !empty($session->get('layoutToRender'))) {
            return $session->get('layoutToRender');
        } elseif ($session->has('idCourse') && !empty($session->get('idCourse'))) {
            return 'lms';
        } else {
            return 'lms_user';
        }
    }

    public function getLogo()
    {
        return Layout::path() . '/images/company_logo.png';
    }

    public function getMenu()
    {
        $menu = CoreMenu::getList('lms');

        return $menu;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getUserDetails()
    {
        return \FormaLms\lib\Forma::getAclManager()->getUser($this->user->getIdst(), false);
    }

    public function getLogoutUrl()
    {
        return FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _logout_;
    }

    public function getCart()
    {
        require_once _lms_ . '/lib/lib.cart.php';

        Learning_Cart::init();
        $cart = Learning_Cart::cartItemCount();

        return $cart;
    }

    public function getProfile()
    {
        require_once _lms_ . '/lib/lib.middlearea.php';

        $ma = new Man_MiddleArea();

        $profile = null;
        if ($ma->currentCanAccessObj('user_details_full')) {
            require_once \FormaLms\lib\Forma::inc(_lib_ . '/lib.user_profile.php');
            $profile = new UserProfile(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());
            $profile->init('profile', 'framework', 'index.php?' . FormaLms\lib\Get::home_page_query(), 'ap');
        }

        return $profile;
    }

    public function getCredits()
    {
        require_once _lms_ . '/lib/lib.middlearea.php';
        require_once '../widget/lms_block/lib.lms_block_menu.php';
        $widget = new Lms_BlockWidget_menu();

        $ma = new Man_MiddleArea();
        $credits = null;
        if ($ma->currentCanAccessObj('credits')) {
            $credits = $widget->credits();
        }

        return $credits;
    }

    public function getCareer()
    {
        require_once '../widget/lms_block/lib.lms_block_menu.php';

        $widget = new Lms_BlockWidget_menu();
        $career = $widget->career();

        return $career;
    }

    public function getSubscribeCourse()
    {
        require_once '../widget/lms_block/lib.lms_block_menu.php';

        $widget = new Lms_BlockWidget_menu();
        $sc = $widget->subscribe_course();

        return $sc;
    }

    public function getNews()
    {
        require_once '../widget/lms_block/lib.lms_block_menu.php';

        $widget = new Lms_BlockWidget_menu();
        $news = $widget->news();

        return $news;
    }

    public function getLanguages()
    {
        $lm = new LangAdm();
        $languages = $lm->getLangListNoStat(false, false, 'lang_description', 'ASC');

        return $languages;
    }

    public function getHelpDeskEmail()
    {


        return trim(\FormaLms\lib\Mailer\FormaMailer::getInstance()->getHandler()->getHelperDeskMail());
    }

    public function getCurrentPage()
    {
        $current_page = new stdClass();
        if (!empty($GLOBALS['req'])) {
            $current_page->isMVC = true;
            $current_page->MVC = $GLOBALS['req'];
        } else {
            $current_page->isMVC = false;
            $current_page->modname = $GLOBALS['modname'];
            $current_page->op = $GLOBALS['op'];
        }

        return $current_page;
    }

    public function getHomePage()
    {
        return FormaLms\lib\Get::home_page_abs_path();
    }
}
