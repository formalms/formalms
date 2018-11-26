<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   forma.lms - The E-Learning Suite                                        |
|                                                                           |
|   Copyright (c) 2013-2023 (forma.lms)                                     |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

use \appCore\Template\TwigManager;

class LMSTemplateModel {

    private $user;

    public function __construct() {

        $this->user = Docebo::user();
    }

    private function buildMenuArray($menu, $parent = 0) {

        $_menu = array();
        foreach($menu as &$item) {
            if((int)$item->idParent === $parent) {
                $subMenu = $this->buildMenuArray($menu, (int)$item->idMenu);
                if(!is_null($item->idUnder) && checkPerm($item->token_associated, true, $item->module_name, true)) {
                    $href = "index.php?" . ($item->mvc_path ? "r=$item->mvc_path" : "modname=$item->module_name&op=$item->default_op") . "&sop=unregistercourse";
                } else {
                    $href = null;
                }
                if(count($subMenu) || $href) {
                    $_item = new stdClass();
                    $_item->title   = Lang::t($item->name, 'menu_over');
                    $_item->html    = $item->image ? $item->image : Lang::t($item->name, 'menu_over');
                    $_item->href    = $href;
                    $_item->subMenu = $subMenu;
                    $_item->active  = ($_GET['r'] === $item->mvc_path || $_GET['modname'] === $item->module_name); // TODO: migliorare
                    $_menu[$item->sequence] = $_item;
                }
            }
        }
        return $_menu;
    }

    public function getLogo() {

        return Layout::path() . "/images/company_logo.png";
    }

    public function getMenu() {

        // TODO: usare libreria

        $query =
<<<SQL
SELECT m.idMenu, m.idParent, m.name, m.image, m.sequence, mu.idUnder, mu.module_name,
    mu.default_name, mu.default_op, mu.associated_token, mu.class_file, mu.class_name, mu.mvc_path
FROM %adm_menu AS m
    LEFT JOIN %adm_menu_under AS mu ON (m.idMenu = mu.idMenu)
WHERE 1 = 1
    AND m.of_platform IN ('lms')
    AND m.is_active = true
ORDER BY m.sequence
SQL;

        $res = sql_query($query);

        $menu = array();
        while($row = sql_fetch_object($res)) {
            $menu[] = $row;
        }

        $menu = $this->buildMenuArray($menu);

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
            require_once(_lib_ . '/lib.user_profile.php');
            $profile = new UserProfile(getLogUserId());
            $profile->init('profile', 'framework', 'index.php?r=' . _lms_home_, 'ap');
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
}