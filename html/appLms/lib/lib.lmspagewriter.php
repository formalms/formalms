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
 * this class is the default page for docebolms
 */
class LmsPageWriter extends PageWriter {
	
	function LmsPageWriter() {
		$this->addZone( 'page_head' );
		$this->addZone( 'blind_navigation' );
		$this->addZone( 'feedback' );
		$this->addZone( 'header' );
		$this->addZone( 'quickbar' );
		$this->addZone( 'menu_over' );
		$this->addZone( 'menu', true );
		$this->addZone( 'content', true );
		$this->addZone( 'footer' );
		$this->addZone( 'scripts' );
		$this->addZone( 'debug' );
		$this->_zones['def_lang'] = new PageZoneLang( 'def_lang', false );

		$this->addStart( '<ul id="blind_avigation" class="container-blindnav">', 'blind_navigation' );
		$this->addEnd( '</ul>'."\n", 'blind_navigation' );
		/*$browser_code = Docebo::langManager()->getLanguageBrowsercode(getLanguage());
		$pos = strpos($browser_code, ';');
		if($pos !== false) $browser_code = substr($browser_code, 0, $pos);
		
		$browser = getBrowserInfo();


		if($browser["browser"] !== 'msie') {

			
			//header("Content-Type: application/xhtml+xml; charset=".getUnicode()."");
			header("Content-Type: text/html; charset=".getUnicode()."");
			//$this->addStart('<?xml version="1.0" encoding="'.getUnicode().'"?'.'>'."\n", 'page_head' );
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
		
		$this->addStart( '<ul id="blind_avigation" class="container-blindnav">', 'blind_navigation' );
		$this->addEnd( '</ul>'."\n", 'blind_navigation' );
		
		$this->addStart( '<div id="quickbar" class="quickbar">', 'quickbar' );
		$this->addEnd( '</div>'."\n", 'quickbar' );
		
		$this->addStart( '<div id="header" class="layout_header">'."\n",
			'header' );
		$this->addEnd( '</div>'."\n", 
			'header' );
		
		$this->addStart('<div id="menu_over" class="layout_menu_over">'."\n", 'menu_over');
		$this->addEnd('</div>'."\n", 'menu_over');
			
		$this->addStart('<div class="layout_colum_container">'."\n"
					   .'<div id="menu" class="layout_colum_left">'."\n", 
					   'menu');
		$this->addEnd('</div>'."\n", 'menu');
		
		$this->addStart('<div id="content" class="layout_colum_right">'."\n",
						'content');
		$this->addEnd('</div>'."\n"
						.'<div class="nofloat"></div>'."\n"
						.'</div>'."\n", 
						'content');

		$this->addStart( '<div id="footer" class="layout_footer">'."\n", 'footer');
		$this->addEnd( '</div>'."\n"
						.'</body>'."\n"
						.'</html>',
						'footer' );*/
	}
	
	/**
	 * Create an instance of LmsPageWriter
	 * @static
	 *
	 * @return an istance of LmsPageWriter
	 *
	 * @access public
	 */
	function &createInstance() {
		if($GLOBALS['page'] === null) {
			$GLOBALS['page'] = new LmsPageWriter();
		}
		return $GLOBALS['page'];
	}
	
}

?>