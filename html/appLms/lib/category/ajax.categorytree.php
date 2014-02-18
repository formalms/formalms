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

require_once(_base_.'/lib/lib.json.php');
require_once(_lms_.'/lib/category/class.categorytree.php');

$op = Get::req("op", DOTY_ALPHANUM);


switch($op) {
  

	case "expand" : {
		$json = new Services_JSON();
		$node_id = Get::req('query', DOTY_INT, 0);

		$result = array();
		$treecat = new Categorytree();
		$re = $treecat->getChildrensById($node_id);
		while (list($idCategory, $idParent, $path, $lev, $left, $right) = sql_fetch_row($re)) {
			$result[] = array(
				'id' => $idCategory,
				'label' => end(explode('/', $path)),
				'is_leaf' => ($right-$left)==1,
				'count_content' => ''
			); //change this
		}
		
		aout( $json->encode($result) );
	} break;




	case "getaddnodeform": {
    $url = Get::req('server_url', DOTY_ALPHANUM, false);
    $parent_id = Get::req('parent_id', DOTY_ALPHANUM, false);
    $output = array();
    $output['body'] = '<form name="tree_addfolder_form" method="POST" action="'.$url.'">'.
		'<input type="hidden" id="authentic_request_addfolder" name="authentic_request" value="'.Util::getSignature().'" />'.
      '<input type="hidden" name="op" value="add_folder" />'.
      '<input type="hidden" name="parent_id" value="'.$parent_id.'" />'.
      '<label for="newname">'."lang._NEW_FOLDER".':</label><input type="text" name="newname" /></form>';
    $json = new Services_JSON();
    aout( $json->encode($output) );
  } break;


	case "add_folder": {

		$output = array();

		$output['success'] = true;
		$output['folder_id'] = 666;
		$output['label'] = 'label';
		$output['is_leaf'] = false;

		$json = new Services_JSON();
		aout( $json->encode($output) );
	} break;

	case "add_element": {

	} break;


	case "del_folder": {
		$output = array();

		$output['success'] = true;
		$json = new Services_JSON();
		aout( $json->encode($output) );
	} break;

	case "del_element": {

	} break;

	case "rename_folder": {

	} break;

	case "rename_element": {

	} break;
}

?>