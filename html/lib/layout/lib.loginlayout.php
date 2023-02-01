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

class LoginLayout
{
    /**
     * Return the menu for the pre-login pages.
     *
     * @return <string>
     */
    public static function external_page()
    {
        $db = DbConn::getInstance();

        $query = "
		SELECT idPages, title
		FROM %lms_webpages
		WHERE publish = '1' AND in_home='0' AND language = '" . getLanguage() . "'
		ORDER BY sequence ";
        $result = $db->query($query);

        $numof = $db->num_rows($result);
        --$numof;

        $li = '';
        $ul = '<ul id="main_menu">';

        $i = 0;
        while (list($id_pages, $title) = sql_fetch_row($result)) {
            $li .= '<li' . ($i == $numof ? ' class="last"' : '') . '><a href="index.php?modname=login&amp;op=readwebpages&amp;idPages=' . $id_pages . '">'
                . $title . '</a></li>';
            ++$i;
        }

        return $li != '' ? $ul . $li . '</ul>' : '';
    }

    /**
     * Return the links for auto-register and lost password.
     *
     * @return <html>
     */
    public static function links()
    {
        $user_manager = new UserManager();
        $html = '<div id="link">';
        //add self_optin to enable registration
        if ($user_manager->_option->getOption('register_type') == 'self_optin' || $user_manager->_option->getOption('register_type') == 'self' || $user_manager->_option->getOption('register_type') == 'moderate') {
            $html .= '<a class="first" href="' . FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _register_ . '">' . Lang::t('_REGISTER', 'login') . '</a> ';
        }
        $html .= '<a href="index.php?modname=login&amp;op=lostpwd">' . Lang::t('_LOG_LOSTPWD', 'login') . '</a>';
        $html .= '</div>';

        return $html;
    }

    /**
     * The news link for the home pages.
     *
     * @return <html>
     */
    public static function news($hnumber = 2)
    {
        $html = '<div id="news">';

        $textQuery = '
		SELECT idNews, publish_date, title, short_desc
		FROM ' . $GLOBALS['prefix_lms'] . "_news
		WHERE language = '" . getLanguage() . "'
		ORDER BY important DESC, publish_date DESC
		LIMIT 0," . FormaLms\lib\Get::sett('visuNewsHomePage');

        //do query
        $result = sql_query($textQuery);
        if (sql_num_rows($hnumber)) {
            $html .= '<p>' . Lang::set('_NO_CONTENT', 'login') . '</p>';
        }
        while (list($idNews, $publish_date, $title, $short_desc) = sql_fetch_row($result)) {
            $html .= '<h' . $hnumber . '>'
                . '<a href="index.php?modname=login&amp;op=readnews&amp;idNews=' . $idNews . '">' . $title . '</a>'
                . '</h' . $hnumber . '>'
                . '<p class="news_textof">'
                . '<span class="news_data">'
                    . Format::date($publish_date) . ' - </span>'
                    . $short_desc
                . '</p>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Service message for logout, and wrong password.
     *
     * @return <html>
     */
    public static function service_msg()
    {
        $html = '';
        if (isset($_GET['logout']) || isset($_GET['msg'])) {
            $html .= '<div id="service_msg">';
            if (isset($_GET['logout'])) {
                $html .= '<b class="logout">' . Lang::t('_UNLOGGED', 'login') . '</b>';
            }
            if (isset($_GET['msg'])) {
                $class = 'login_failed';
                $msg = '';
                switch ((int) $_GET['msg']) {
                    case 101:  // Security issue, the request seem invalid ! (failed checkSignature)
                        $msg = Lang::t('_INVALID_REQUEST', 'login');
                     break;
                    case 102:  // Two user logged at the same time with the same username
                        $msg = Lang::t('_TWO_USERS_LOGGED_WITH_SAME_USERNAME', 'login');
                     break;
                    case 103:  // Session expired
                        $msg = Lang::t('_SESSION_EXPIRED', 'login');
                     break;
                    case 104:  // Change ip durinc connection
                        $msg = Lang::t('_INCORRECT_IP', 'login');
                     break;
                }
                $html .= '<b class="' . $class . '">' . $msg . '</b>';
            }
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Return the number of the installed languages in the platform.
     *
     * @return <int>
     */
    public static function lang_number()
    {
        $lang_sel = Lang::get();

        $lang_model = new LangAdm();
        $lang_list = $lang_model->getLangListNoStat(false, false, 'lang_description', 'ASC');

        return count($lang_list);
    }
}
