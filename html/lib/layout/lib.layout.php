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
	 * Return the complete code for change lang dropdown
	 * @return <string>
	 */
	public static function change_lang() {

		$r = Get::req('r', DOTY_MIXED, '');
		$lang_sel = Lang::get();
		$lang_model = new LangAdm();
		$lang_list = $lang_model->getLangListNoStat(false,false,'lang_description','ASC');

		$server_query_string = $_SERVER['QUERY_STRING'];
		$pos = strpos($server_query_string, 'special=changelang&new_lang=');
		if ($pos !== FALSE) {
			if($pos == 0) $pos = 1;
			if ($server_query_string{$pos - 1} == '&') $pos = $pos - 1;
			$server_query_string = substr($server_query_string, 0, $pos);
		}

		$js = '<ul class=\"link_list_inline\" id=\"language_selection\">';
		foreach($lang_list as $lang) {

			$js .= '<li><a class=\"lang-sprite lang_'.strtolower( str_replace(' ', '_', $lang->lang_code) ).($lang->lang_code == $lang_sel ? ' current' : '' ).'\"'
				.'href=\"'.( isset($args['redirect_on']) ? $args['redirect_on'] : 'index.php' )
					.'?'//.($r !== '' ? '?r='.$r.'&amp;' : '?')
					.($server_query_string !== "" ? str_replace('&', '&amp;', $server_query_string).'&amp;' : "")
					.'special=changelang&amp;new_lang='.rawurlencode($lang->lang_code).'\" title=\"'.$lang->lang_description.'\">'
					.'<span>'.$lang->lang_description.'</span>'
				.'</a></li>';
			// ('.$lang->lang_browsercode.')
		}
		// lang_code, lang_description, lang_direction, lang_browsercode
		$js .= '</ul><div class=\"nofloat\"></div>';

		$html = '<a id="change_language" href="#">'.Lang::get().'</a>'

		.'<script type="text/javascript">
		var lang_setup = new YAHOO.widget.Panel("language_setup", {
			context:["change_language","tr","br", ["beforeShow", "windowResize"]],
			constraintoviewport: true,
			width: \'600px\',
			modal: true,
			close: true,
			visible: false,
			draggable: false
		} );
		lang_setup.setBody("'.$js.'");
		lang_setup.render(document.body);
		YAHOO.util.Event.addListener("change_language", "click", lang_setup.show, lang_setup, true);
		</script>';
		return $html;
	}

	/**
	 * Return the code for the catalogue link
	 * @return <string>
	 */
	public static function get_catalogue()
	{
		$html = '<a href="index.php?r=homecatalogue/show">'.Lang::t('_CATALOGUE', 'register').'</a>';

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
		include(_base_.'/templates/'.getTemplate().'/layout/'.$layout.'.php');
	}


	public static function copyright() {

		$html = "";
		$html .= '<p class="powered_by">';
		$html .= '<span class="ownedby">';
		$html .= Get::sett('owned_by', 'Copyright (c) forma.lms');
		$html .= '</span>';
		$html .= ' - ';
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
