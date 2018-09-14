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

    public function getMenu() {

        require_once _lms_ . '/lib/lib.middlearea.php';

        $ma = new Man_MiddleArea();

        $query =
<<<SQL
SELECT mo.idModule, mo.module_name, mo.default_op, mo.mvc_path, mo.default_name, mo.token_associated, mo.module_info, under.sequence
FROM learning_module AS mo
INNER JOIN learning_menucourse_under AS under ON ( mo.idModule = under.idModule)
WHERE module_info IN ('all', 'user') AND mo.idModule NOT IN(7, 34)
ORDER BY module_info, under.sequence;
SQL;

        $res = sql_query($query);

        $menu = array();
        while($row = sql_fetch_object($res)) {
            if($ma->currentCanAccessObj('mo_' . $row->idModule) && checkPerm($row->token_associated, true, $row->module_name, true)) {
                // ??? -->
                if($row->module_name === 'course' && !$ma->currentCanAccessObj('tb_elearning')) {
                    $row->mvc_path = 'lms/classroom/show';
                }
                // <-- ???
                $item = new stdClass();
                $item->href = "index.php?" . ($row->mvc_path ? "r=$row->mvc_path" : "modname=$row->module_name&op=$row->def_op") . "&sop=unregistercourse";
                $item->title = Lang::t($row->default_name, 'menu_over');
                if($_GET['r'] === $row->mvc_path) {
                    $item->li_class = ' active ';
                }
                $menu[$row->sequence] = $item;
            }
        }

        // TODO: help desk

        if(in_array(Docebo::user()->getUserLevelId(), array(ADMIN_GROUP_GODADMIN, ADMIN_GROUP_ADMIN))) {
            $item = new stdClass();
            $item->href = Get::rel_path('adm');
            $item->title = '<span class="glyphicon glyphicon-cog top-menu__label">' . Lang::t('_GO_TO_FRAMEWORK', 'menu_over') . '</span>';
            $item->active = false;
            $item->a_class = ' no-border-right no-before ';
            $item->li_class = ' green_menu ';
            $menu[] = $item;
        }

        return $menu;
    }

    public function getUser() {

        return $this->user;
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
}