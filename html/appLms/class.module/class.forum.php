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

class Module_Forum extends LmsModule {
	
	function useExtraMenu() {
		return false;
	}
	
	function loadExtraMenu() {
		$lang =& DoceboLanguage::createInstance('forum');
		$line = '<div class="legend_line">';
		echo $line.'<img src="'.getPathImage().'standard/add.png" /> '.$lang->def('_REPLY').'</div>'
			.$line.'<img src="'.getPathImage().'standard/edit.png" /> '.$lang->def('_MOD').'</div>'
			.$line.'<img src="'.getPathImage().'/forum/free.gif" /> '.$lang->def('_FORUMOPEN').'</div>'
			.$line.'<img src="'.getPathImage().'/forum/locked.gif" /> '.$lang->def('_FORUMCLOSED').'</div>';
		if(checkPerm('mod', true)) {
			$line.'<img src="'.getPathImage().'forum/erase.gif" /> '.$lang->def('_DEL').'</div>';
			$line.'<img src="'.getPathImage().'forum/unerase.gif" /> '.$lang->def('_RESTOREINSERT').'</div>';
		}
		if(checkPerm('del', true)) {
			$line.'<img src="'.getPathImage().'standard/delete.png" /> '.$lang->def('_DEL').'</div>';
		}
	}
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		forumDispatch($GLOBALS['op']);
	}
	
	function getAllToken($op) {
		return array( 
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'),
			'write' => array( 	'code' => 'write',
								'name' => '_REPLY',
								'image' => 'standard/reply.png'),
			'upload' => array(	'code' => 'upload',
								'name' => '_UPLOAD',
								'image' => 'standard/download.png'),
			'add' => array( 	'code' => 'add',
								'name' => '_ADD',
								'image' => 'standard/add.png'),
			'mod' => array( 	'code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png'),
			'del' => array( 	'code' => 'del',
								'name' => '_DEL',
								'image' => 'standard/delete.png'),
			'moderate' => array('code' => 'moderate',
								'name' => '_MODERATE')
		);
	}


	function getPermissionsForMenu($op) {
		return array(
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view,write,upload'),
			4 => $this->selectPerm($op, 'view,write,upload'),
			5 => $this->selectPerm($op, 'view,write,upload,add,mod,del,moderate'),
			6 => $this->selectPerm($op, 'view,write,upload,add,mod,del,moderate'),
			7 => $this->selectPerm($op, 'view,write,upload,add,mod,del,moderate')
		);
	}

}

?>
