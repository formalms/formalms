<?php defined("IN_FORMA") or die('Direct access is forbidden.');


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

/**
 * Jquery handling library
 */
class JQueryLib
{

    const _jquery_version = '1.12.3';
    const _jquery_ui_version = '1.11.4';
    const _bootstrap_version = '3.3.6';
    const _path = 'jquery';

    static $array_js_addons = ['html5support',  //  HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries
        'helpdesk',
        'fancybox',
        'swipe',
        'select',
         'datepicker',
         'datatables',
         'moment',
         'table-edits',
         'malihu-custom-scrollbar-plugin',
         'cookie']; // malihu custom scrollbar

    static $array_css_addons = ['table',    //  media query for table formatting
        'helpdesk',
        'fancybox',
        'swipe',
        'select',
        'datepicker',
        'datatables',
        'malihu-custom-scrollbar-plugin' ]; // malihu custom scrollbar
        


    public static function loadJQuery($which_version = '')
    {
        $jquery_core_lib = "/addons/" . self::_path . "/core/jquery-" . self::_jquery_version . $which_version . ".js";
        $local_link = Util::get_js($jquery_core_lib);

        $jquery_core_lib = "/addons/" . self::_path . "/core/ui/js/jquery-ui-" . self::_jquery_ui_version . $which_version . ".js";
        $local_link .= Util::get_js($jquery_core_lib);
        $local_link .= self::initJQueryAjax();
        
        $lang = DoceboLangManager::getInstance()->getLanguageBrowsercode(Lang::get());
        $jquery_core_lang = "/addons/" . self::_path . "/core/ui/js/i18n/datepicker-$lang.js";
        $local_link .= Util::get_js($jquery_core_lang);

        // load css
        $jquery_ui_css = "/addons/" . self::_path . "/core/ui/css/jquery-ui-" . self::_jquery_ui_version . $which_version . ".css";
        $local_link .= Util::get_css( Get::rel_path('base'). $jquery_ui_css, true);

        return $local_link;
    }

    public static function loadBootstrap($which_version = '')
    {
        $bootstrap_core_lib = "/addons/" . self::_path . "/bootstrap/js/bootstrap-" . self::_bootstrap_version . $which_version . ".js";
        $local_link = Util::get_js($bootstrap_core_lib);

        // load css
        $bootstrap_core_css = "/addons/" . self::_path . "/bootstrap/css/bootstrap-" . self::_bootstrap_version . $which_version . ".css";
        $local_link .= Util::get_css(Get::rel_path('base') . $bootstrap_core_css, true);       


        $bootstrap_core_css = "/addons/" . self::_path . "/bootstrap/css/bootstrap-theme-" . self::_bootstrap_version . $which_version . ".css";
        $local_link .= Util::get_css(Get::rel_path('base') . $bootstrap_core_css, true);

        return $local_link;
    }


    public static function loadJsAddons($which_version, $exclude_addons = null, $single_addon = null)
    {

        if (!is_null($single_addon)) {
             self::$array_js_addons =  [$single_addon];
        }
            
        
        $local_link = "\n\t\t";
        foreach (self::$array_js_addons as $a_addon_path) {
            if (!in_array($a_addon_path, $exclude_addons)) {
                $full_path = "/addons/" . self::_path . "/" . $a_addon_path . "/";
                $addon_files = self::select_file($full_path, $which_version . '.js');
                if (count($addon_files) > 0) {
                    foreach ($addon_files as $js_file) {
                        $js_file = $full_path . $js_file;
                        $local_link .= Util::get_js($js_file);
                    }
                }
            }  
        }

        return $local_link;
    }

    public static function loadCssAddons($which_version, $exclude_addons = null, $single_addon = null)
    {
        if (!is_null($single_addon)){ 
            self::$array_css_addons =  [$single_addon];
        }            
        
        $local_link = "\n\t\t";
        foreach (self::$array_css_addons as $a_addon_path) {
            if (!in_array($a_addon_path, $exclude_addons)) {
                $full_path = "/addons/" . self::_path . "/" . $a_addon_path . "/";
                $addon_files = self::select_file($full_path, $which_version . '.css');
                if (count($addon_files) > 0) {
                    foreach ($addon_files as $css_file) {
                        $css_file = $full_path . $css_file;
                        $local_link .= Util::get_css(Get::rel_path('base'). $css_file, true);

                    }
                }
            }    
        }
        return $local_link;
    }
    
    // init locale for calendar widget
    // if locale does not exist or http header fails, default to en-us
    public static function loadCalenderLocal(){
        
        
        $_lang = Docebo::user()->getPreference('ui.lang_code'); 
        $locale_calender_path = "/addons/" . self::_path . "/datepicker/locales/";
        if (!is_null($_lang)){
                    $local_js = $locale_calender_path."bootstrap-datepicker.".$_lang.".min.js";
                    $complete_js_path = Get::rel_path('base').$local_js; 
                    if (file_exists($complete_js_path)) {
                            return Util::get_js($local_js);   
                    }        
        }
        return '';
    }


    private static function select_file($which_path, $which_extension)
    {

        $dircontents = scandir(dirname(__FILE__) . "/.." . $which_path);
        $ret_array = [];
        foreach ($dircontents as $file) {
            if (strpos($which_extension, 'min.') && strpos($file, 'min.')) {
                if (strpos($which_extension, pathinfo($file, PATHINFO_EXTENSION))) {
                    $ret_array[] = $file;
                }
            }
            if (!strpos($which_extension, 'min.') && !strpos($file, 'min.')) {
                if (strpos($which_extension, pathinfo($file, PATHINFO_EXTENSION))) {
                    $ret_array[] = $file;
                }
            }
        }
        return $ret_array;
    }


    public static function initJQueryAjax()
    {
        $retval = "\n" . '<script type=\'text/javascript\'>
                             $.ajaxSetup(
                                {
                                    headers: {"X-Signature":"' . Util::getSignature() . '"}
                                }
                             )
                            </script>';
        return $retval;

    }


}

?>