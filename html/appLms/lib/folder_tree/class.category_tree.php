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

require_once(_base_.'/lib/folder_tree/class.client_tree.php');
require_once(_lms_.'/lib/folder_tree/lib.category_tree.php');


class CategoryFolderTree extends ClientTree {

	public $initFromSession = true;

	public function __construct($id, $initFromSession = false) {
		parent::__construct($id);
		$this->jsClassName = 'CourseFolderTree';
		$this->serverUrl = 'ajax.adm_server.php?plf=lms&file=category_tree&sf=folder_tree';

		Util::get_js(Get::rel_path('base').'/widget/dialog/dialog.js', true, true);
		require_once(_base_.'/lib/lib.dialog.php');
		initDialogs();

		$initialShowedNodes = array();
		if ($initFromSession) {
			if (isset($_SESSION['course_category']['tree_status'])) {
				$tree_status =& $_SESSION['course_category']['tree_status'];
			//if (isset($tree_status['showed_nodes'])) {}
			}

			if (isset($_SESSION['course_category']['filter_status'])) {
				$filter_status =& $_SESSION['course_category']['filter_status'];
				if (isset($filter_status['c_category'])) $this->setOption('initialSelectedNode', $filter_status['c_category']);
			}
		}

		$lang =& DoceboLanguage::CreateInstance('course', 'lms');
		//$this->setOption('langs', array('_ROOT'=>def('_CATEGORY', 'course', 'lms')));
		$this->addLangKey('_ROOT', $lang->def('_CATEGORY'));
		$this->addLangKey('_YES', $lang->def('_CONFIRM'));
		$this->addLangKey('_NO', $lang->def('_UNDO'));
		$this->addLangKey('_NEW_FOLDER_NAME', $lang->def('_NEW_CATEGORY'));
		$this->addLangKey('_MOD', $lang->def('_MOD'));
		$this->addLangKey('_AREYOUSURE', $lang->def('_AREYOUSURE'));
		$this->addLangKey('_NAME', $lang->def('_NAME'));
		$this->addLangKey('_MOD', $lang->def('_MOD'));
		$this->addLangKey('_DEL', $lang->def('_DEL'));
		//$this->addLangKey('_', $lang->def(''));

		//$selected_node = (isset($_SESSION['course_category']['filter_status']) ? $_SESSION['course_category']['filter_status']['c_category'] : 0); //0 = root node
		$tree_status = $this->_getCourseTreeStatus(); //0 = root node

		$this->setOption('iconPath', Get::tmpl_path().'images/');
		$this->setOption('dragdrop', true);
		//$this->setOption('initNodes', $initialShowedNodes);
		$this->setOption('useCheckboxes', false);
		$this->setOption('addFolderButton', 'add_folder_button');
		//$this->setOption('initialTreeStatus', $tree_status);
		//$this->setOption('options', '');
		//$this->setOption('options', '');
	}

	public function saveTreeState($state) {
		//...
	}

	function _getCourseTreeStatus() {
		require_once(_lms_.'/lib/category/class.categorytree.php');
		$treecat = new Categorytree();

		if (!isset($_SESSION['course_category']['filter_status']['c_category']))
			$_SESSION['course_category']['filter_status']['c_category'] = 0;
		$treestatus =& $_SESSION['course_category']['filter_status']['c_category'];

		$result = array();
		$folders = $treecat->getOpenedFolders( $treestatus );

		$ref =& $result;
		foreach ($folders as $folder) {

			if ($folder > 0) {
				for ($i=0; $i<count($ref); $i++) {
					if ($ref[$i]['id'] == $folder) {
						$ref[$i]['expanded'] = true;
						$ref[$i]['children'] = array();
						$ref =& $ref[$i]['children'];
						break;
					}
				}
			}

			$childrens = $treecat->getChildrensById($folder);
			while (list($id_category, $idParent, $path, $lev, $left, $right) = sql_fetch_row($childrens)) {
				$is_leaf = ($right-$left) == 1;
				$node_options = getNodeOptions($id_category, $is_leaf);
				$ref[] = array(
					'type' => 'FolderNode',
					'id' => $id_category,
					'label' => end(explode('/', $path)),
					'html' => end(explode('/', $path)),
					'is_leaf' => $is_leaf,
					'count_content' => (int)(($right-$left-1)/2),
					'options' => $node_options
				);
			}

		}

		return $result;
	}

	/*
	function _getCourseTreeStatus() {
		require_once(_lms_.'/lib/category/class.categorytree.php');
		$treecat = new Categorytree();

		if (!isset($_SESSION['course_category']['filter_status']['c_category']))
			$_SESSION['course_category']['filter_status']['c_category'] = 0;
		$treestatus =& $_SESSION['course_category']['filter_status']['c_category'];
		
		$result = array();
		$folders = $treecat->getOpenedFolders( $treestatus );

		$ref =& $result;
		foreach ($folders as $folder) {

			if ($folder > 0) {
				for ($i=0; $i<count($ref); $i++) {
					if ($ref[$i]['node']['id'] == $folder) {
						$ref[$i]['children'] = array();
						$ref =& $ref[$i]['children'];
						break;
					}
				}
			}

			$childrens = $treecat->getChildrensById($folder);
			while (list($id_category, $idParent, $path, $lev, $left, $right) = sql_fetch_row($childrens)) {
				$is_leaf = ($right-$left) == 1;
				$node_options = getNodeOptions($id_category, $is_leaf);
				$ref[] = array(
					'node' => array(
						'id' => $id_category,
						'label' => end(explode('/', $path)),
						'is_leaf' => $is_leaf,
						'count_content' => (int)(($right-$left-1)/2),
						'options' => $node_options
					)
				);
			}

		}

		return $result;
	}
	*/
}

?>
