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
 * This is a singleton class for page rendering, at now the application mustn't use the echo.
 * A module must append the text to display in the proper area
 * @package		library
 * @subpackage	layout
 * @author 		Fabio Pirovano <fabio@docebo.com>
 * @version 	$Id: lib.pagewriter.php 852 2006-12-16 14:04:44Z giovanni $
 */

/**
 * Global unique instance of the PageWriter
 */
if(!isset($GLOBALS['page'])) $GLOBALS['page'] = null;

/**
 * class for zone management
 * @author 		Emanuele Sandri <esandri@tiscali.it>
 **/
class PageZone {
	/** name ho the zone */
	public $_name;
	/** start block */
	public $_startOut = array();
	/** content block */
	public $_contentOut = array();
	/** end block */
	public $_endOut = array();

	public $print_if_empty;

	public function  __construct( $name, $print_if_empty = false ) {
		$this->name = $name;
		$this->print_if_empty = $print_if_empty;
	}

	/**
	 * Prepend one element to the beginning of start block
	 * @access public
	 * @param string $text the text to insert
	 */
	public function insertStart( $text ) {
		array_unshift($this->_startOut, $text);
	}
	/**
	 * Append one element to the end of start block
	 * @access public
	 * @param string $text the text to append
	 */
	public function appendStart( $text ) {
		array_push($this->_startOut, $text );
	}

	/**
	 * Prepend one element to the beginning of content block
	 * @access public
	 * @param string $text the text to insert
	 */
	public function insertContent( $text ) {
		array_unshift($this->_contentOut, $text);
	}
	/**
	 * Append one element to the end of contet block
	 * @access public
	 * @param string $text the text to append
	 */
	public function appendContent( $text ) {
		array_push($this->_contentOut, $text );
	}

	/**
	 * Prepend one element to the beginning of end block
	 * @access public
	 * @param string $text the text to insert
	 */
	public function insertEnd( $text ) {
		array_unshift($this->_endOut, $text);
	}
	/**
	 * Append one element to the end of content block
	 * @access public
	 * @param string $text the text to append
	 */
	public function appendEnd( $text ) {
		array_push($this->_endOut, $text );
	}

	/**
	 * Default operation for start block is append
	 * @access public
	 * @param string $text the text to append
	 */
	public function addStart( $text ) {
		$this->appendStart( $text );
	}

	/**
	 * Default operation for content block is append
	 * @access public
	 * @param string $text the text to append
	 */
	public function addContent( $text ) {
		$this->appendContent( $text );
	}

	/**
	 * Default operation for end block is insert
	 * @access public
	 * @param string $text the text to append
	 */
	public function addEnd( $text ) {
		$this->insertEnd( $text );
	}

	/**
	 * Default operation for a zone is add in content
	 * @access public
	 * @param string $text the text to append
	 */
	public function add( $text ) {
		$this->addContent( $text );
	}

	public function replace( $needle, $text ) {

		foreach($this->_contentOut as $k => $value) {

			if(strpos($value, $needle) !== false) {
				$this->_contentOut[$k] = $text;
			}
		}

	}

	/**
	 * remove all the output generated
	 * @param bool $full if true clean the start and end alos, otherwise only the content
	 */
	public function clean($full = true) {

		if($full) $this->_startOut = array();
		$this->_contentOut = array();
		if($full) $this->_endOut = array();
	}

	/**
	 * to get output
	 */
	public function getContent() {
		$out = '';

		if(empty($this->_contentOut) && $this->print_if_empty === false) return $out;

		$out.=implode($this->_startOut);
		$out.=implode($this->_contentOut);
		$out.=implode($this->_endOut);

		$out = fillSiteBaseUrlTag($out);

		return $out;
	}
}

class PageZoneLang extends PageZone {

	public function  __construct( $name, $print_if_empty = false ) {
		$this->name = $name;
		$this->print_if_empty = $print_if_empty;

	}
		
	public function getContent() {
		$out = '';

		if(Docebo::user()->getUserLevelId() == ADMIN_GROUP_USER) return $out;

		if(empty($this->_contentOut) && $this->print_if_empty === false) return $out;

		
		$out .= Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true,false)
				.Util::get_js(Get::rel_path('base').'/widget/dialog/dialog.js', true,false)
				.'<div id="def_lang" class="def_lang">'."\n"
				.implode($this->_startOut)

				.'<div id="link_container">'
				.implode($this->_contentOut)
				.'</div>'

				.'<a id="command" href="#" onclick="YAHOO.Animation.BlindToggle(\'link_container\');" >'
					. Lang::t('_NOT_TRANSLATED', 'standard', 'framework').' ('.count($this->_contentOut).')'
				.'</a>'
				.'<script type="text/javascript">'
					.'YAHOO.util.Dom.get(\'link_container\').style.display = \'none\';'
				.'</script>'
				.'<script type="text/javascript">
				var translation = { module: "", key: "" }
				YAHOO.util.Event.onDOMReady(function() {

					var dialogEvent = function(e) {
						var oConfig = {
							modal: true,
							close: true,
							visible: false,
							fixedcenter: true,
							constraintoviewport: true,
							draggable: true,
							hideaftersubmit: false,
							isDynamic: true,
							ajaxUrl: "ajax.adm_server.php?r=adm/lang/translatemask",
							confirmOnly: false,
							directSubmit: false
						};
						oConfig.renderEvent = function() {
							new YAHOO.widget.TabView("translation_tab");
							YAHOO.util.Event.onAvailable(\'lang_module\', function() {
								YAHOO.util.Dom.get(\'lang_module\').value = translation.module;
								YAHOO.util.Dom.get(\'lang_key\').value = translation.key;
							}, this);
						};
						oConfig.callback = function() { this.destroy(); };

						var info = this.id.split("-");
						translation.module = info[1];
						translation.key = info[2];
						CreateDialog("translation_add", oConfig).call(this, e);
					}
					YAHOO.util.Event.addListener(YAHOO.util.Selector.query(\'a[id^=totranslate]\'), "click", dialogEvent);
				});
				</script>'
				.implode($this->_endOut)
				. '</div>'."\n";
		//ob_clean();
		$out = fillSiteBaseUrlTag($out);

		return $out;
	}
}

class PageWriter {

	/**
	 * indicate the current working zone if setted
	 *
	 * @access private
	 */
	public $_current_work_zone = null;

	/**
	 * array of zones
	 **/
	public $_zones = array();

	/**
	 * PageWriter constructor
	 *
	 * @access private
	 */
	public function _constructor() {}

	/**
	 * Add a zone
	 **/
	public function addZone( $zone, $print_if_empty = false ) {
		$this->_zones[$zone] = new PageZone( $zone, $print_if_empty );
	}

	public function getWorkingZone() {
		return $this->_current_work_zone;
	}

	public function setWorkingZone($zone) {
		return $this->_current_work_zone = $zone;
	}

	public function _getZone( $zone ) {
		return ($zone==null)?($this->_current_work_zone):$zone;
	}

	/**
	 * Write the passed string into a zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function add($content, $zone = null) {
		if(!isset($this->_zones[$this->_getZone($zone)])) {
			Log::add('Warning: you are trying to write in a zone that doesn\'t exist ('.$this->_getZone($zone).')');
		}
		else $this->_zones[$this->_getZone($zone)]->add($content);
	}

	/**
	 * Write the passed string into a zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function replace($needle, $content, $zone = null) {

		if(!isset($this->_zones[$this->_getZone($zone)])) {
			Log::add('Warning: you are trying to write in a zone that doesn\'t exist ('.$this->_getZone($zone).')');
		}
		else $this->_zones[$this->_getZone($zone)]->replace($needle, $content);
	}

	/**
	 * Write the passed string into the starting block of a zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function addStart($content, $zone = null) {
		if(!isset($this->_zones[$this->_getZone($zone)])) return;
		$this->_zones[$this->_getZone($zone)]->addStart($content);
	}

	/**
	 * Write the passed string into the content block of a zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function addContent($content, $zone = null) {
		if(!isset($this->_zones[$this->_getZone($zone)])) return;
		$this->_zones[$this->_getZone($zone)]->addContent($content);
	}

	/**
	 * Write the passed string into the end block of a zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function addEnd($content, $zone = null) {
		if(!isset($this->_zones[$this->_getZone($zone)])) return;
		$this->_zones[$this->_getZone($zone)]->addEnd($content);
	}

	/**
	 * Write the passed string at the end of start block in zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function appendStart($content, $zone = null) {
		if(!isset($this->_zones[$this->_getZone($zone)])) return;
		$this->_zones[$this->_getZone($zone)]->appendStart($content);
	}

	/**
	 * Write the passed string at the end of content block in zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function appendContent($content, $zone = null) {
		if(!isset($this->_zones[$this->_getZone($zone)])) return;
		$this->_zones[$this->_getZone($zone)]->appendContent($content);
	}

	/**
	 * Write the passed string at the end of end block in zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function appendEnd($content, $zone = null) {
		if(!isset($this->_zones[$this->_getZone($zone)])) return;
		$this->_zones[$this->_getZone($zone)]->appendEnd($content);
	}

	/**
	 * Write the passed string at the beginning of start block in zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function insertStart($content, $zone = null) {
		$this->_zones[$this->_getZone($zone)]->insertStart($content);
	}

	/**
	 * Write the passed string at the beginning of content block in zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function insertContent($content, $zone = null) {
		$this->_zones[$this->_getZone($zone)]->insertContent($content);
	}

	/**
	 * Write the passed string at the beginning of end block in zone
	 * @param string $content
	 * @param string $zone optional zone id
	 * @return nothing
	 * @access public
	 */
	public function insertEnd($content, $zone = null) {
		$this->_zones[$this->_getZone($zone)]->insertEnd($content);
	}

	/**
	 * remove all the output generated
	 * @param bool $full if true clean the start and end alos, otherwise only the content
	 */
	public function clean($zone = null, $full = true) {

		$this->_zones[$this->_getZone($zone)]->clean($full);
	}

	/**
	 * public function to get output for page
	 */
	 public function getContent($zone = false) {

		if($zone === false) {

			 $out = '';
			 $pz = current($this->_zones);
			 while( $pz !== FALSE ) {
				 $out .= $pz->getContent();
				 $pz = next($this->_zones);
			 }
			 reset( $this->_zones );
			 return $out;
		} else {
			if(!isset($this->_zones[$zone])){
				if(!isset($GLOBALS['pw_temp'][$zone])) return '';
				return implode('', $GLOBALS['pw_temp'][$zone]);
			}
			return $this->_zones[$zone]->getContent();
		}
	 }
}

/**
 * This class is the default page for forma
 */
class StdPageWriter extends PageWriter {

	public function __construct() {
		$this->addZone( 'page_head' );
		$this->addZone( 'blind_navigation' );
		$this->addZone( 'feedback' );
		$this->addZone( 'header' );
		$this->addZone( 'menu_over' );
		$this->addZone( 'menu', true );
		$this->addZone( 'content', true );
		$this->addZone( 'footer' );
		$this->addZone( 'scripts' );
		$this->addZone( 'debug' );
		//$this->addZone( 'def_lang' );
		$this->_zones['def_lang'] = new PageZoneLang( 'def_lang', false );

		$this->addStart( '<ul id="blind_navigation" class="container-blindnav">', 'blind_navigation' );
		$this->addEnd( '</ul>'."\n", 'blind_navigation' );
	}

	/**
	 * Create an instance of StdPageWriter
	 * @static
	 *
	 * @return an istance of StdPageWriter
	 *
	 * @access public
	 */
	public function &createInstance() {
		if($GLOBALS['page'] === null) {
			$GLOBALS['page'] = new StdPageWriter();
		}
		return $GLOBALS['page'];
	}

}

/**
 * this class is the default page for the public area for the cms
 */
class onecolPageWriter extends PageWriter {

	public function __construct() {

		$this->addZone( 'page_head' );
		$this->addZone( 'blind_navigation' );
		$this->addZone( 'feedback', false );
		$this->addZone( 'body-start' );
		$this->addZone( 'header' );
		$this->addZone( 'menu_over' );
		$this->addZone( 'content' );
		$this->addZone( 'footer' );
		$this->addZone( 'body-end' );
		$this->addZone( 'scripts' );
		$this->addZone( 'debug' );
		$this->_zones['def_lang'] = new PageZoneLang( 'def_lang', false );

		$this->addStart( '<ul id="blind_navigation" class="container-blindnav">', 'blind_navigation' );
		$this->addEnd( '</ul>'."\n", 'blind_navigation' );

		$this->addStart( '<div id="feedback" class="container-feedback">', 'feedback' );
		$this->addEnd( '</div>'."\n", 'feedback' );
		/*
		$browser_code = Docebo::langManager()->getLanguageBrowsercode(getLanguage());
		$pos = strpos($browser_code, ';');
		if($pos !== false) $browser_code = substr($browser_code, 0, $pos);

		$browser = getBrowserInfo();
		if($browser["browser"] !== 'msie') {

			// The world is not ready for this right now, all the xml not valid will not be interpretated form the serious borwsers
			//header("Content-Type: application/xhtml+xml; charset=".getUnicode()."");
			header("Content-Type: text/html; charset=".getUnicode()."");
			$this->addStart('<?xml version="1.0" encoding="'.getUnicode().'"?'.'>'."\n", 'page_head' );
		} else {
			header("Content-Type: text/html; charset=".getUnicode()."");
		}

		$this->addStart( ''
			.'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"'."\n"
			.'	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'."\n"
			.'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$browser_code.'">'."\n"
			.'<head>',
			'page_head' );
		$this->addContent( ''
			.'	<meta http-equiv="Content-Type" content="text/html; charset='.getUnicode().'" />'."\n"
			.'	<meta name="Copyright" content="Forma srl" />'."\n"
			.'	<link rel="Copyright" href="http://www.formalms.org" title="Copyright Notice" />'."\n"
			.'	<link href="'.getPathTemplate().'images/favicon.ico" rel="shortcut icon" />'."\n",
			'page_head' );
		$this->addEnd( '</head>'."\n"
			.'<body class="yui-skin-docebo yui-skin-sam">'."\n",
			'page_head');

		$this->addStart( '<ul id="blind_navigation" class="container-blindnav">', 'blind_navigation' );
		$this->addEnd( '</ul>'."\n", 'blind_navigation' );

		$this->addStart( '<div id="header" class="layout_header">'."\n",
			'header' );
		$this->addEnd( '</div>'."\n",
			'header' );

		$this->addStart('<div id="menu_over" class="layout_menu_over">'."\n", 'menu_over');
		$this->addEnd('</div>'."\n", 'menu_over');

		$this->addStart('<div class="layout_colum_container">'."\n",
						'content');

		$this->addEnd('<div class="nofloat"></div>'."\n"
						.'</div>'."\n",
						'content');

		$this->addStart( '<div id="footer" class="layout_footer">'."\n", 'footer');
		$this->addEnd( '</div>'."\n"
						.'</body>'."\n"
						.'</html>',
						'footer' );
		*/
	}

	/**
	 * Create an instance of StdPageWriter
	 * @static
	 *
	 * @return an istance of StdPageWriter
	 *
	 * @access public
	 */
	public function &createInstance() {
		if($GLOBALS['page'] === null) {
			$GLOBALS['page'] = new onecolPageWriter();
		}
		return $GLOBALS['page'];
	}

}

class emptyPageWriter extends PageWriter {

	public function __construct() {
		$this->addZone( 'page_head' );
		$this->addZone( 'blind_navigation' );
		$this->addZone( 'feedback' );
		$this->addZone( 'body-start' );
		$this->addZone( 'body-end' );
		$this->addZone( 'header' );
		$this->addZone( 'menu_over' );
		$this->addZone( 'content' );
		$this->addZone( 'footer' );
		$this->addZone( 'scripts' );
		$this->addZone( 'debug' );
		$this->_zones['def_lang'] = new PageZoneLang( 'def_lang', false );


		$this->addStart( '', 'page_head' );
		$this->addContent( '', 'page_head' );
		$this->addEnd( '', 'page_head');

		$this->addStart( '<ul id="blind_navigation" class="container-blindnav">', 'blind_navigation' );
		$this->addEnd( '</ul>'."\n", 'blind_navigation' );

		$this->addStart( '','header' );
		$this->addEnd( '','header' );

		$this->addStart('', 'menu_over');
		$this->addEnd('', 'menu_over');

		$this->addStart('','content');

		$this->addEnd('',
						'content');

		$this->addStart( '', 'footer');
		$this->addEnd( '', 'footer' );
	}

	/**
	 * Create an instance of StdPageWriter
	 * @static
	 *
	 * @return an istance of StdPageWriter
	 *
	 * @access public
	 */
	public function &createInstance() {
		if($GLOBALS['page'] === null) {
			$GLOBALS['page'] = new emptyPageWriter();
		}
		return $GLOBALS['page'];
	}

}

/**
 * Quick alias of $GLOBALS['page']->add() static method
 * @param <string> $text the text to add in the page
 * @param <string> $zone the identifier of the platform (content selected by default)
 */
function cout($text, $zone = false) {
	if(isset($GLOBALS['page'])) $GLOBALS['page']->add($text, $zone);
	else {
		if(!isset($GLOBALS['pw_temp'][$zone])) $GLOBALS['pw_temp'][$zone] = array();
		$GLOBALS['pw_temp'][$zone][] = $text;
	}
}

?>