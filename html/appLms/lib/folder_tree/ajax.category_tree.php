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

require_once(_lms_.'/lib/category/class.categorytree.php');
$treecat = new Categorytree();

require_once(_base_.'/lib/lib.json.php');
$json = new Services_JSON();

//checkPerm('view', true, 'course', 'lms');

require_once(_lms_.'/lib/folder_tree/lib.category_tree.php');

// Resolve the requested action
$command = Get::req('command', DOTY_STRING, false);
switch ($command) {

	case "expand": {

		$lang =& DoceboLanguage::CreateInstance('course', 'lms');
		$node_id = Get::req('node_id', DOTY_INT, 0);
		$initial = Get::req('initial', DOTY_INT, 0);

//$initial = 0;
		$result = array();
		if ($initial==1) {

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

		} else {


			$re = $treecat->getChildrensById($node_id);
			while (list($id_category, $idParent, $path, $lev, $left, $right) = sql_fetch_row($re)) {

				$is_leaf = ($right-$left) == 1;

				$node_options = getNodeOptions($id_category, $is_leaf);

				$result[] = array(
					'id' => $id_category,
					'label' => end(explode('/', $path)),
					'is_leaf' => $is_leaf,
					'count_content' => (int)(($right-$left-1)/2),
					'options' => $node_options
				); //change this
			}

		}


		$output = array('success'=>true, 'nodes'=>$result, 'initial'=>($initial==1));
		aout( $json->encode($output) );
	};break;

	case "modify": {
		
		$node_id = Get::req('node_id', DOTY_INT, 0);
		$new_name = Get::req('name', DOTY_STRING, false);

		$result = array('success'=>false);
		if ($new_name !== false) $result['success'] = $treecat->renameFolderById($node_id, $new_name);
		if ($result['success']) $result['new_name'] = stripslashes($new_name);
		aout( $json->encode($result) );
	};break;


	case "create": {
		$node_id = Get::req('node_id', DOTY_INT, false);
		$node_name = Get::req('name', DOTY_STRING, false); //no multilang required for categories

		$result = array();
		if ($node_id === false) {
			$result['success'] = false;
		} else {
			$success = false;
			$new_node_id = $treecat->addFolderById($node_id, $node_name);
			if ($new_node_id != false && $new_node_id>0) $success = true;

			$result['success'] = $success;
			if ($success) {
				$result['node'] = array(
					'id' => $new_node_id,
					'label' => $node_name,
					'is_leaf' => true,
					'count_content' => 0,
					'options' => getNodeOptions($new_node_id, true)
				);
			}
		}
		aout( $json->encode($result) );
	};break;

	case "delete": {

		$node_id = Get::req('node_id', DOTY_INT, 0);

		$result = array('success'=>$treecat->deleteTreeById($node_id));
		aout( $json->encode($result) );
	};break;

	case "movefolder": {

		$src = Get::req('src', DOTY_INT, 0);
		$dest = Get::req('dest', DOTY_INT, 0);

		$result = array('success'=>$treecat->move($src, $dest));
		aout( $json->encode($result) );
	};break;

	case "options": {

		$node_id = Get::req('node_id', DOTY_INT, 0);
		//get properties from DB

		$count = $treecat->getChildrenCount($node_id);
		$is_leaf = true;
		if ($count>0) $is_leaf = false;
		$node_options = getNodeOptions($node_id, $is_leaf);

		$result = array('success'=>true, 'options'=>$node_options, '_debug'=>$count);
		aout( $json->encode($result) );
	};break;

	//invalid command
	default: {}

}

?>