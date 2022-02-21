<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_Public_Forum extends LmsModule {
	
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
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'write' => ['code' => 'write',
								'name' => '_REPLY',
								'image' => 'forum/write.gif'],
			'upload' => ['code' => 'upload',
								'name' => '_UPPLOAD',
								'image' => 'forum/upload.gif'],
			'add' => ['code' => 'add',
								'name' => '_ADD',
								'image' => 'standard/add.png'],
			'mod' => ['code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png'],
			'del' => ['code' => 'del',
								'name' => '_DEL',
								'image' => 'standard/delete.png'],
			'moderate' => ['code' => 'moderate',
								'name' => '_MODERATE',
								'image' => 'forum/moderate.gif']/*,
			'sema' => array(	'code' => 'sema',
								'name' => '_SEMA',
								'image' => 'forum/sema.gif')*/
        ];
	}
}

?>
