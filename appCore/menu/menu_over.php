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

if(Docebo::user()->isLoggedIn()) {
	
	$lang 	=& DoceboLanguage::createInstance('menu', 'framework');
	$p_man 	=& PlatformManager::createInstance();
	$platforms 	= $p_man->getPlatformList();

	cout('<li><a href="#main_menu">'.$lang->def('_BLIND_MAIN_MENU').'</a></li>', 'blind_navigation');

	cout('<div id="main_menu_container" class="layout_menu_over yuimenubar yuimenubarnav">', 'menu_over');
	cout('<div class="bd"><ul class="first-of-type">', 'menu_over');

	foreach($platforms as $p_code => $p_name) {

		$menu_man =& $p_man->getPlatofmMenuInstance($p_code);

		if($menu_man !== false) {

			$main_voice = $menu_man->getLevelOne();

			if(!empty($main_voice)) {

				cout('<li class="yuimenuitem">'
					.'<a class="yuimenuitemlabel" href="#submenu_'.$p_code.'">'
					//.Get::img('menu/'.$p_code.'.png', '.:', 'icon')
					.'<span class="admmenu_'.$p_code.'">'.$lang->def('_FIRST_LINE_'.$p_code).'</span>'
					.'</a>'
					.'<div id="submenu_'.$p_code.'" class="yuimenu"><div class="bd"><ul>',
				'menu_over');

				foreach($main_voice as $id_m => $v_main) {

					$under_voice = $menu_man->getLevelTwo($id_m);
					if(!isset($v_main['collapse']) || $v_main['collapse'] === false) {

						cout('<li class="yuimenuitem">'
							.'<a class="yuimenuitemlabel" href="#submenu_'.$p_code.'_'.$id_m.'">'
							.$v_main['name']
							.'</a>',
						'menu_over');
						if(!empty($under_voice)) cout('<div id="submenu_'.$p_code.'_'.$id_m.'" class="yuimenu"><div class="bd"><ul>', 'menu_over');
					}
					foreach($under_voice as $id_m => $voice) {

						cout('<li class="yuimenuitem">'.
							'<a class="yuimenuitemlabel" href="'.Util::str_replace_once('&', '&amp;',  $voice['link']).'">'.
								$voice['name'].
							'</a>'.
							'</li>'
						, 'menu_over');
					}
					
					if(!isset($v_main['collapse']) || $v_main['collapse'] === false) {

						if(!empty($under_voice)) cout('</ul></div></div>', 'menu_over');
						cout('</li>',
						'menu_over');
					}
				}
				cout('</ul></div></div>'.
					'</li>'
				, 'menu_over');
			}
		}
	}

	// quick jump
	cout('<li class="yuimenuitem">'.
		'<a href="'.$GLOBALS['where_lms_relative'].'">'.
			'<span class="admmenu_goto">'.$lang->def('_JUMP_TO_PLATFORM', 'menu', 'framework')
			.' '.$lang->def('_LMS', 'platform').'</span>'.
		'</a>',
	'menu_over');
	/*
	cout('<div id="jumpto" class="yuimenu"><div class="bd"><ul>', 'menu_over');
	foreach($platforms as $p_code => $p_name) {

		if($p_code != 'scs' && $p_code != 'ecom' && $p_code != 'framework') {
			
			cout('<li class="yuimenuitem">'.
				'<a href="'.$GLOBALS['where_'.$p_code.'_relative'].'">'.
					$p_name.
				'</a>'.
				'</li>'
			, 'menu_over');
		}
	}
	cout('</ul></div></div>', 'menu_over');*/
	cout('</li>', 'menu_over');

	// add logout voice
	/*
	cout('<li class="yuimenuitem">'.
		'<a href="index.php?modname=login&amp;op=logout">'.
			//Get::img('menu/logout.png', '.:', 'icon').' '.
			'<span class="admmenu_logout">'. Lang::t('_LOGOUT', 'menu', 'framework').'</span>'.
		'</a>'.
		'</li>',
	'menu_over');*/
	cout('</ul></div></div>', 'menu_over');

	// script needed in order to render the menu
	cout('<script type="text/javascript">'."\n".
	"// Initialize and render the MenuBar when it is available in the page"."\n".
	"YAHOO.util.Event.onContentReady('main_menu_container', function () {"."\n".
	"	var oMenuBar = new YAHOO.widget.".( Lang::direction() == 'rtl' ? 'MenuBarRtl' : 'MenuBar' )."('main_menu_container', {"."\n".
	"		maxheight : 600,minscrollheight : 550,lazyload : true"."\n".
	"	});"."\n".
	"oMenuBar.render();"."\n".
	"});"."\n".
	"".
	'</script>', 'scripts');

}

?>