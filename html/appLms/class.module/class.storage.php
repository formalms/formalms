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

require_once($GLOBALS['where_lms'].'/lib/lib.levels.php');

class Module_Storage extends LmsModule {
	
	//class constructor
	function __construct($module_name = '') {
		//EFFECTS: if a module_name is passed use it else use global reference
		global $modname;
		
		parent::LmsModule();
	}
	
	function loadHeader() {
		//EFFECTS: write in standard output extra header information
		$GLOBALS['page']->setWorkingZone( 'page_head' );
		$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/base-old-treeview.css" rel="stylesheet" type="text/css" />' );
		return;
	}
	
	function useExtraMenu() {
		return false;
	}
	
	function loadExtraMenu() {}
	
	function getAllToken() {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'home' => ['code' => 'home',
								'name' => '_HOME'],
			'lesson' => ['code' => 'lesson',
								'name' => '_LESSON'],
			'public' => ['code' => 'public',
								'name' => '_PUBLIC']
        ];
	}

	function getPermissionsForMenu($op) {
		return [
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view'),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view,home'),
			6 => $this->selectPerm($op, 'view,home,lesson,public'),
			7 => $this->selectPerm($op, 'view,home,lesson,public')
        ];
	}
	
	function getPermissionUi( $form_name, $perm ) {
		
		require_once(_base_.'/lib/lib.table.php');
		
		$lang =& DoceboLanguage::createInstance('manmenu');
		$lang_perm =& DoceboLanguage::createInstance('permission');
		
		$tokens = $this->getAllToken();
		$levels = CourseLevel::getTranslatedLevels();
		$tb = new Table(0, $lang->def('_VIEW_PERMISSION'), $lang->def('_EDIT_SETTINGS'));
		
		$c_head = [$lang->def('_LEVELS')];
		$t_head = [''];
		foreach($tokens as $k => $token) {
			if($token['code'] != 'view') {
				if(isset($token['image'])) {
					$c_head[] =  '<img src="'.getPathImage().$token['image'].'" alt="'.$lang_perm->def($token['name']).'"'
								.' title="'.$lang_perm->def($token['name']).'" />';
				} else {
					$c_head[] =  $lang_perm->def($token['name']);
				}
				$t_head[] = 'image';
			}
		}
		if(count($tokens) > 1) {
			$c_head[] = '<img src="'.getPathImage().'standard/checkall.png" alt="'.$lang->def('_CHECKALL').'" />';
			$c_head[] = '<img src="'.getPathImage().'standard/uncheckall.png" alt="'.$lang->def('_UNCHECKALL').'" />';
			$t_head[] = 'image';
			$t_head[] = 'image';
		}
		$tb->setColsStyle($t_head);
		$tb->addHead($c_head);
		foreach($levels as $lv => $levelname )
    {
			
			$c_body = [$levelname];
			
			foreach($tokens as $k => $token) {
				if($token['code'] != 'view') {
					$c_body[] =  '<input class="check" type="checkbox" '
								.'id="perm_'.$lv.'_'.$token['code'].'" '
								.'name="perm['.$lv.']['.$token['code'].']" value="1"'
								.( isset($perm[$lv][$token['code']]) ? ' checked="checked"' : '' ).' />'
							.'<label class="access-only" for="perm_'.$lv.'_'.$token['code'].'">'
							.$lang_perm->def($token['name']).'</label>'."\n";
				}
			}
			if(count($tokens) > 1) {
				
				$c_body[] = '<img class="handover"'
					.' onclick="checkall(\''.$form_name.'\', \'perm['.$lv.']\', true); return false;"'
					.' src="'.getPathImage().'standard/checkall.png" alt="'.$lang->def('_CHECKALL').'" />';
				$c_body[] = '<img class="handover"'
					.' onclick="checkall(\''.$form_name.'\', \'perm['.$lv.']\', false); return false;"'
					.' src="'.getPathImage().'standard/uncheckall.png" alt="'.$lang->def('_UNCHECKALL').'" />';
			}
			$tb->addBody($c_body);
		}
		$c_select_all = [''];
		foreach($tokens as $k => $token) {
			if($token['code'] != 'view') {
				$c_select_all[] = '<img class="handover"'
						.' onclick="checkall_fromback(\''.$form_name.'\', \'['.$token['code'].']\', true); return false;"'
						.' src="'.getPathImage().'standard/checkall.png" alt="'.$lang->def('_CHECKALL').'" />'
					.'<img class="handover"'
						.' onclick="checkall_fromback(\''.$form_name.'\', \'['.$token['code'].']\', false); return false;"'
						.' src="'.getPathImage().'standard/uncheckall.png" alt="'.$lang->def('_UNCHECKALL').'" />';
			}
		}
		if(count($tokens) > 1) {
			$c_select_all[] = '';
			$c_select_all[] = '';
		}
		$tb->addBody($c_select_all);
		return $tb->getTable();
	}
	
	function getSelectedPermission() {
		
		$tokens 	= $this->getAllToken();
		$levels 	= CourseLevel::getTranslatedLevels();
		$perm 		= [];
		
		foreach($levels as $lv => $levelname ) 
    {
			$perm[$lv] = [];
			foreach($tokens as $k => $token) {
				
				if(isset($_POST['perm'][$lv][$token['code']])) {
					$perm[$lv]['view'] = 1;
					$perm[$lv][$token['code']] = 1;
				}
			}
		}
		return $perm;
	}
}


?>