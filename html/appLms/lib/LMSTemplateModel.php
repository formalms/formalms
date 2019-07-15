<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   forma.lms - The E-Learning Suite                                        |
|                                                                           |
|   Copyright (c) 2013-2023 (forma.lms)                                     |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class LMSTemplateModel {

    private $user;

    public function __construct() {

        $this->user = Docebo::user();
    }

    public function selectLayout() {

        if(!empty($_SESSION['layoutToRender'])) {
            return $_SESSION['layoutToRender'];
        } elseif(isset($_SESSION['idCourse'])) {
            return 'lms';
        } else {
            return 'lms_user';
        }
    }

    public function getLogo() {

        return Layout::path() . "/images/company_logo.png";
    }

    public function getMenu() {

        $menu = CoreMenu::getList('lms');
        return $menu;
    }

    public function getUser() {

        return $this->user;
    }

    public function getUserDetails() {

        return $this->user->getAclManager()->getUser($this->user->getIdst(), false);
    }

    public function getLogoutUrl() {

        return Get::rel_path('base') . '/index.php?r=' . _logout_;
    }

    public function getCart() {

        require_once _lms_ . '/lib/lib.cart.php';

        Learning_Cart::init();
        $cart = Learning_Cart::cartItemCount();

        return $cart;
    }

    public function getProfile() {

        require_once _lms_ . '/lib/lib.middlearea.php';

        $ma = new Man_MiddleArea();

        $profile = null;
        if($ma->currentCanAccessObj('user_details_full')) {
            require_once(Forma::inc(_lib_ . '/lib.user_profile.php'));
            $profile = new UserProfile(getLogUserId());
            $profile->init('profile', 'framework', 'index.php?' . Get::home_page_query(), 'ap');
        }

        return $profile;
    }

    public function getCredits() {

        require_once _lms_ . '/lib/lib.middlearea.php';
        require_once '../widget/lms_block/lib.lms_block_menu.php';            
        $widget = new Lms_BlockWidget_menu();        

        $ma = new Man_MiddleArea();
        $credits = null;
        if($ma->currentCanAccessObj('credits')) {

            $credits = $widget->credits();
        }

        return $credits;
    }

    public function getCareer() {

        require_once '../widget/lms_block/lib.lms_block_menu.php';

        $widget = new Lms_BlockWidget_menu();
        $career = $widget->career();

        return $career;
    }

    public function getSubscribeCourse() {

        require_once '../widget/lms_block/lib.lms_block_menu.php';

        $widget = new Lms_BlockWidget_menu();
        $sc = $widget->subscribe_course();

        return $sc;
    }

    public function getNews() {

        require_once '../widget/lms_block/lib.lms_block_menu.php';

        $widget = new Lms_BlockWidget_menu();
        $news = $widget->news();

        return $news;
    }

    public function getLanguages() {

        $lm = new LangAdm();
        $languages = $lm->getLangListNoStat(false, false, 'lang_description', 'ASC');

        return $languages;
    }

    public function getHelpDeskEmail() {

        return trim(Get::sett('customer_help_email', ''));
    }

    public function getCurrentPage() {

        $current_page = new stdClass();
        if(!empty($GLOBALS['req'])) {
            $current_page->isMVC    = true;
            $current_page->MVC      = $GLOBALS['req'];
        } else {
            $current_page->isMVC    = false;
            $current_page->modname  = $GLOBALS['modname'];
            $current_page->op       = $GLOBALS['op'];
        }
        return $current_page;
    }

    public function getHomePage() {

        return Get::home_page_abs_path();
    }
}