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

if(Docebo::user()->isAnonymous()) die( "You can't access to oragnization");

require_once(_base_.'/lib/lib.tab.php' );

$_tab_op_map = array( 	'homerepo' => 'storage_home',
						'organization' => 'storage_course',
						'pubrepo' => 'storage_pubrepo'
					);

function save_state( &$data ) {
	$_SESSION['storage'] = serialize($data);
}

function &load_state() {
	
	$readed = array();
	if( isset($_SESSION['storage']) ) 
		 $readed = unserialize( $_SESSION['storage'] );
	return $readed;
}

function &create_TabView( $op ) {
	global $_tab_op_map;
	$tv = new TabView( 'storage', 'index.php?modname=storage&op=display' );
	$lang =& DoceboLanguage::createInstance('storage', 'lms');
	
	if( checkPerm('home', true, 'storage') )
		$tv->addTab( new TabElemDefault( 'storage_home', $lang->def('_HOMEREPOROOTNAME'), getPathImage().'area_title/homerepo.gif' ) );
	if( checkPerm('lesson', true, 'storage') )
		$tv->addTab( new TabElemDefault( 'storage_course', $lang->def('_ORGROOTNAME'), getPathImage().'area_title/organizations.gif' ) );
	if( checkPerm('public', true, 'storage') )
		$tv->addTab( new TabElemDefault( 'storage_pubrepo', $lang->def('_PUBREPOROOTNAME'), getPathImage().'area_title/pubrepo.gif' ) );
	
	$extra_data = load_state();
	
	$tv->parseInput( $_POST, $extra_data );
	if( isset( $_tab_op_map[$op] ) )
		$tv->setActiveTab($_tab_op_map[$op]);
		
	return $tv;
}

function destroy_TabView( &$tv ) {
	save_state( $tv->getState() );
}

function &create_activeTab( &$tv ){
	switch( $tv->getActiveTab() ) {
		case 'storage_home':
			if( checkPerm('home', true, 'storage') )
				$repo = createModule( 'homerepo' );
		break;
		case 'storage_pubrepo':
			if( checkPerm('public', true, 'storage') )
				$repo = createModule( 'pubrepo' );
		break;
		case 'storage_course':
		default:
			if ( checkPerm('lesson', true, 'storage') ) {
				$tv->setActiveTab( 'storage_course' );
				$repo = createModule( 'organization' );
			} elseif( checkPerm('home', true, 'storage') ) {
				$tv->setActiveTab( 'storage_home' );
				$repo = createModule( 'homerepo' );
			} elseif ( checkPerm('public', true, 'storage') ) {
				$tv->setActiveTab( 'storage_pubrepo' );
				$repo = createModule( 'pubrepo' );
			} else {
				$tv->setActiveTab( 'storage_course' );
				$repo = createModule( 'organization' );
			}
		break;
	}
	return $repo;
}

function storage_display() {
	$tv = create_TabView( $GLOBALS['op'] );
	
	$repo =& create_activeTab($tv);
        
	$lang =& DoceboLanguage::createInstance('storage', 'lms');
	
	$repo->initialize();
	$GLOBALS['page']->setWorkingZone('content');
        $GLOBALS['page']->add(
                getTitleArea(lang::t('_STORAGE', 'menu_course'))
                    .'<div class="std_block">'
                );
	
	if( !$repo->hideTab() ) {
		$GLOBALS['page']->add( $tv->printTabView_Begin($repo->getUrlParams()) );
		$GLOBALS['page']->addEnd( $tv->printTabView_End() );
	}

	if( $repo->isFindingDestination() ) {
		$repo->setOptions( TRUE );
	}	

	$GLOBALS['page']->add( $repo->getExtraTop() );
	
	$repo->loadBody();
	
	$GLOBALS['page']->add( $repo->getExtraBottom() );
	
	//setup dialog popups
	$lang =& DoceboLanguage::CreateInstance('standard', 'framework');
	require_once(_base_.'/lib/lib.dialog.php');
	switch ( $tv->getActiveTab() ) {
	    case 'storage_course': { 
	      setupFormDialogBox(
		       'orgshow',
		       'index.php?modname=storage&op=organization',
		       'input[name*=treeview_opdeletefolder_organization]',
		       $lang->def('_AREYOUSURE'),
		       $lang->def('_CONFIRM'),
		       $lang->def('_UNDO'),
		       'function(o) { return o.title; }',
		       'organization_treeview_opdeletefolder_organization_',
		       'treeview_selected_organization',
		       'treeview_delete_folder_organization');
	     } break;
	    case 'storage_home': {
	      setupFormDialogBox(
		       'homereposhow',
		       'index.php?modname=storage&op=homerepo',
		       'input[name*=treeview_opdeletefolder_homerepo]',
		       $lang->def('_AREYOUSURE'),
		       $lang->def('_CONFIRM'),
		       $lang->def('_UNDO'),
		       'function(o) { return o.title; }',
		       'homerepo_treeview_opdeletefolder_homerepo_',
		       'treeview_selected_homerepo',
		       'treeview_delete_folder_homerepo');
	    } break;
	    case 'storage_pubrepo': {
	      setupFormDialogBox(
		       'pubreposhow',
		       'index.php?modname=storage&op=pubrepo',
		       'input[name*=treeview_opdeletefolder_pubrepo]',
		       $lang->def('_AREYOUSURE'),
		       $lang->def('_CONFIRM'),
		       $lang->def('_UNDO'),
		       'function(o) { return o.title; }',
		       'pubrepo_treeview_opdeletefolder_pubrepo_',
		       'treeview_selected_pubrepo',
		       'treeview_delete_folder_pubrepo');
	    } break;
	  }
	$GLOBALS['page']->add('</div>');
	//if( !$repo->hideTab() ) 	
	//	$GLOBALS['page']->add( $tv->printTabView_End() );
	
	destroy_TabView( $tv );
}

switch( $GLOBALS['op'] ) {
	case "display":
	case "homerepo":
	case "organization":
	case "pubrepo":
	default:
 		storage_display();
	break;
}

?>