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

    class Layout {

    static protected $_lang = false;

    public static function templateList() {
        $templ_array = array();
        $templ = dir(_base_.'/templates/');
        while($elem = $templ->read()) {

            if((is_dir(_base_.'/templates/'.$elem)) && $elem{0} != "." && $elem{0} != '_' ) {
                $templ_array[] = $elem;
            }
        }
        closedir($templ->handle);

        sort($templ_array);
        return $templ_array;
    }

    public static function charset() {

        return 'utf-8';
    }

    public static function lang_code() {

        if(!self::$_lang) self::$_lang = Docebo::langManager()->getLanguageInfo(Lang::get());
        if(!isset(self::$_lang->lang_browsercode)) return 'en';

        $browser_code = self::$_lang->lang_browsercode;
        $pos = strpos($browser_code, ';');
        if($pos !== false) $browser_code = substr($browser_code, 0, $pos);
        return $browser_code;
    }

    public static function title() {

        if(isset($GLOBALS['page_title'])) return $GLOBALS['page_title'];
        return Get::sett('page_title', 'No title');
    }

    public static function description() { return ''; }

    public static function keyword() { return ''; }

    public static function path() {

        return Get::tmpl_path('base');
    }

    public static function meta() {

        return '<meta http-equiv="Content-Type" content="text/html; charset='.self::charset().'" />'."\n"
        ."\t\t".'<meta name="Copyright" content="'. Get::sett('owned_by', 'Copyright &copy; forma.lms') .'" />'."\n"
        ."\t\t".'<meta name="Generator" content="www.formalms.org '. Get::sett('core_version', ''). '" />'."\n"
        ."\t\t".'<link rel="Copyright" href="http://www.formalms.org/copyright" title="Copyright Notice" />'."\n"
        ."\t\t".'<link rel="Author" href="http://www.formalms.org/about" title="About" />'."\n";

    }

    public static function resetter() {

        echo '<link rel="stylesheet" type="text/css" href="'.Layout::path().'style/reset-fonts-grids.css" />';
        if(!self::$_lang) self::$_lang = Docebo::langManager()->getLanguageInfo(Lang::get());
        if(isset(self::$_lang->lang_direction) && self::$_lang->lang_direction == 'rtl') echo '<link rel="stylesheet" type="text/css" href="'.Layout::path().'style/reset-fonts-grids-rtl.css" />';
    }

    public static function GetResetter() {

        $retval =  '<link rel="stylesheet" type="text/css" href="'.Layout::path().'style/reset-fonts-grids.css" />'."\n";
        if(!self::$_lang) self::$_lang = Docebo::langManager()->getLanguageInfo(Lang::get());
        if(isset(self::$_lang->lang_direction) && self::$_lang->lang_direction == 'rtl') $retval .= '<link rel="stylesheet" type="text/css" href="'.Layout::path().'style/reset-fonts-grids-rtl.css" />'."\n";
        return $retval;
    }



    public static function rtl() {

        if(!self::$_lang) self::$_lang = Docebo::langManager()->getLanguageInfo(Lang::get());
        if(isset(self::$_lang->lang_direction) && self::$_lang->lang_direction == 'rtl') {
            echo '<link rel="stylesheet" type="text/css" href="'.Layout::path().'style/base-rtl.css" />';
        }
    }

    public static function accessibility() {

        if(getAccessibilityStatus() === false) {

            return '<style type="text/css">'."\n"
            .'.access-only {display: none;}'."\n"
            .'</style>'."\n";
        }
    }

    public static function zone($zone_name) {

        return $GLOBALS['page']->getContent($zone_name);
    }

    public static function lang_flag() {

        $lang_sel = Lang::get();
        $langs_var = Docebo::langManager()->getAllLanguages();
        if(count($langs_var) <= 1) return '';

        $html = '<ul id="language_selection">';
        foreach($langs_var as $k => $v) {

            $html .= '<li><a '.($v[0] == $lang_sel ? 'class="current" ' : '' )
            .'href="'.( isset($args['redirect_on']) ? $args['redirect_on'] : 'index.php' )
            .'?special=changelang&amp;new_lang='.rawurlencode($v[0]).'" title="'.$v[1].'">'
            .Get::img('language/'.rawurlencode($v[0]).'.png', $v[0])
            .'</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }
    /**
    * Return the complete code for the lms cart
    * @return <string>
    */
    public static function cart()
    {
        require_once(_lms_.'/lib/lib.cart.php');
        Learning_Cart::init();

        $html ='<div id="cart_box" class="cart_box" style="display:'.(Learning_Cart::cartItemCount() == 0 ? 'none' : 'inline').';">';
        $html.=	'<a id="cart_action" href="index.php?r=cart/show"><span>'.Lang::t('_SHOPPING_CART', 'catalogue')
        .' <sub class="num_notify" id="cart_element">'.Learning_Cart::cartItemCount().'</sub></span></a></div>';

        return $html;
    }

    /**
     * Build the restyled layout for lang selection
     * @return <string>
     */
    public static function buildLanguages() {

        $r = Get::req('r', DOTY_MIXED, '');
        $lang_sel = Lang::get();
        $lang_model = new LangAdm();
        $lang_list = $lang_model->getLangListNoStat(false,false,'lang_description','ASC');

        $server_query_string = $_SERVER['QUERY_STRING'];
        $pos = strpos($server_query_string, 'special=changelang&new_lang=');
        if ($pos !== FALSE) {
            if($pos == 0) $pos = 1;
            if ($server_query_string{$pos - 1} === '&') $pos = $pos - 1;
            $server_query_string = substr($server_query_string, 0, $pos);
        }

        $html = '<ul class="list-inline" id="language_selection">';
        foreach($lang_list as $lang) {

            $html .= '<li><a class="lang-sprite lang_'.strtolower( str_replace(' ', '_', $lang->lang_code) ).($lang->lang_code == $lang_sel ? ' current' : '' ).'"'
                .'href="'.( isset($args['redirect_on']) ? $args['redirect_on'] : 'index.php' )
                .'?'//.($r !== '' ? '?r='.$r.'&amp;' : '?')
                .($server_query_string !== "" ? str_replace('&', '&amp;', $server_query_string).'&amp;' : "")
                .'special=changelang&new_lang='.rawurlencode($lang->lang_code).'" title="'.$lang->lang_description.'">'
                .'</a></li>';

        }
        $html .= '</ul>';

        return $html;
    }

    /**
    * Return the complete code for change lang dropdown
    * @return <string>
    */
    public static function change_lang() {
        $lang_sel = Lang::get();
        $lang_model = new LangAdm();
        $lang_list = $lang_model->getLangListNoStat(false,false,'lang_description','ASC');

        $server_query_string = $_SERVER['QUERY_STRING'];
        $pos = strpos($server_query_string, 'special=changelang&new_lang=');

        if ($pos !== FALSE) {
            if ($pos == 0) {
                $pos = 1;
            }
            if ($server_query_string[$pos - 1] === '&') {
                $pos = $pos - 1;
            }
            $server_query_string = substr($server_query_string, 0, $pos);
        }

		$html = '<div class="dropdown">';
		$html .= '<a id="change_language" class="dropdown-toggle" href="#" data-toggle="dropdown">'.Lang::get().'<i class="fa fa-chevron-down"></i></a>';
		$html .= '<div class="dropdown-menu" aria-labelledby="change_language">';

		foreach($lang_list as $keyLang => $lang) {
		    if($keyLang !== $lang_sel) {
                $href = isset($args['redirect_on']) ? $args['redirect_on'] : 'index.php';
//			$href .= '?' . $server_query_string !== '' ? str_replace('&', '&amp;', $server_query_string).'&amp;' : '';
                $href .= '?s&special=changelang&amp;new_lang=' . rawurlencode($lang->lang_code);
                $html .= '<a class="dropdown-item" href="' . $href . '" title="' . $lang->lang_description . '">' . $lang->lang_description . '</a>';
            }
		}

//		echo var_dump($href);
//        die();

		$html .= '</div>';
		$html .= '</div>';

        return $html;
    }

    /**
    * Return the code for the catalogue link
    * @return <string>
    */
    public static function get_catalogue()
    {
        $html = '<a href="index.php?r=homecatalogue/show">'.Lang::t('_CATALOGUE', 'standard').'</a>';

        return $html;
    }

    public static function lang_dropdown() {

        $lang_sel = Lang::get();
        $langs_var = Docebo::langManager()->getAllLanguages();

        $html = Form::openForm('language_selection', '?special=changelang')
        .'<select id="new_lang" name="new_lang" onchange="submit();">';
        foreach($langs_var as $k => $v) {

            $html .= '<option value="'.rawurlencode($v[0]).'"'.($v[0] == $lang_sel ? ' selected="selected"' : '' ).'>'
            .$v[0]
            .'</option>';
        }
        $html .= '</select>'
        .Form::closeForm();

        return $html;
    }

    public static function render($layout) {

        $browser = Get::user_agent();
        header("Content-Type: text/html; charset=".self::charset()."");
        if($browser["browser"] !== 'msie') {
            $intest = '<?xml version="1.0" encoding="'.self::charset().'"?'.'>'."\n";
        }
        if (file_exists(_base_.'/templates/'.getTemplate().'/layout/'.$layout.'.html.twig')){
            $dataforview = self::PrepareInclude($layout);
            echo \appCore\Template\TwigManager::getInstance()->render($layout.'.html.twig', $dataforview, _base_.'/templates/'.getTemplate().'/layout/');
        } else {
            include(_base_.'/templates/'.getTemplate().'/layout/'.$layout.'.php');
        }
    }

    public static function PrepareInclude($whichLayout){
        
        $minimized = ($GLOBALS['cfg']['do_debug'] ? '': '.min');
        $retArray = [];
        // base.html.twig
        $retArray['layout_path'] = self::path();
        $retArray['layout_zone_meta'] = self::zone('meta');
        $retArray['layout_meta'] = self::meta();
        $retArray['layout_title'] = self::title();
        $retArray['yuiLib'] =  YuiLib::load('base');
        $retArray['accessibility'] = Layout::accessibility();
        $retArray['layout_zone_page_head'] = self::zone('page_head');
        $retArray['layout_rtl'] = self::zone('rtl');
        $retArray['layout_analytics'] = self::analytics();
        $retArray['jqueryLib'] = JQueryLib::loadJQuery($minimized);
        $retArray['boostrap'] = JQueryLib::loadBootstrap($minimized);
        $retArray['jqueryAddons'] = JQueryLib::loadJQueryAddons($minimized);
        $retArray['cssAddons'] = JQueryLib::loadCssAddons($minimized);

        if (file_exists(_base_.'/templates/'.getTemplate().'/style/custom.css')){
            $customCssPath = Get::rel_path('base').'/templates/'.getTemplate().'/style/custom.css';
            $retArray['custom_css_path'] = str_replace('/./', '/', $customCssPath);
        }
        switch($whichLayout){
            case 'home':
                $retArray['jqueryAddons'] = '';
                $retArray['cssAddons'] = '';
            
                $retArray['copyright'] = self::copyright();
                $retArray['external_page'] = LoginLayout::external_page();
                $retArray['homepage_text'] = Lang::t('_HOMEPAGE', 'login');
                $retArray['intro_text'] = Lang::t('_INTRO_STD_TEXT', 'login');
                
                if  ($GLOBALS['maintenance'] != "on" ) {
                    $retArray['changeLanguage_text'] = Lang::t('_CHANGELANG', 'register');
                    $retArray['changeLanguageBox'] =  self::change_lang();
                    $retArray['social_login'] = LoginLayout:: social_login();
                    $retArray['login_form'] = LoginLayout:: login_form();
                    $retArray['service_msg'] = LoginLayout:: service_msg();  
                    $retArray['layout_zone_footer'] = self::zone('footer');
                    $retArray['login_links'] = LoginLayout::links();
                } else  {
                    $retArray['configuration_maintenance_text']= Lang::t('_MAINTENANCE', 'configuration');
                    $retArray['login_maintenance_text']= Lang::t('_MAINTENANCE', 'login');
                }
                
                if (LoginLayout::isSocialActive()) {
                    $retArray['login_box_css'] = 'login-box-social';
                }  else {
                    $retArray['login_box_css'] = 'login-box';
                }

                if ($GLOBALS['framework']['course_block'] == "on" and $GLOBALS['maintenance'] != "on" ) {
                    $retArray['catalogue'] =  self::get_catalogue();
                }
                break;    
            case 'lms_user':
                $retArray['resetter'] = self::GetResetter();
                break;
             case 'adm':
                $exclude_widget[] = 'swipe';   // do not need swipe on admin menu
                $retArray['jqueryAddons'] = JQueryLib::loadJQueryAddons($minimized, $exclude_widget);
                $retArray['cssAddons'] = JQueryLib::loadCssAddons($minimized, $exclude_widget);
                break;
             case 'lms':
                if(!isset($_SESSION['direct_play'])) {
                    $retArray['direct_play'] =  '<div class="yui-b">'.Layout::zone('content').'</div>';
                } else {
                    $retArray['direct_play'] =  Layout::zone('content');
                }                    
                break;                                
                
        }
        return $retArray;
    }


        public static function copyright() {

            $html = "";
            $html .= '<p class="powered_by">';
            $html .= '<span class="ownedby">';
            $html .= Get::sett('owned_by', 'Copyright (c) forma.lms');
            $html .= '</span>';
            $html .= '<br />';
            $html .= '<span class="poweredby">';
            $html .= '<a href="http://www.formalms.org/" target="_blank">Powered'.' by ' . 'forma.lms CE</a>';
            $html .= '</span>';
            $html .= '</p>';
            return $html;
        }


        /**
        * function highlight
        *	Highlight parts of text strings with HTML tags
        *
        *  @param $string the text that will be checked for parts to highlight
        *  @param $key the text to be highlighted
        *  @param $classname class of the highlight <span> tag, "highlight" by default
        *
        *	@return the highlighted text
        **/
        public static function highlight($string, $key, $classname = "highlight") {
            if($key == false) return $string;
            return preg_replace("/".$key."/i", "<span class=\"highlight\">$0</span>", $string);
        }

        public static function analytics() {
            if(Get::sett('google_stat_in_lms', '0') == '1' && Get::sett('google_stat_code', '') != '') {

                echo Get::sett('google_stat_code');
            }
        }

    }
