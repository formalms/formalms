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

class UserselectorWidgetController extends Controller {

	protected $model = null;
	protected $json = null;

	function init() {
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();
		$this->user_model = new UsermanagementAdm();
		$this->group_model = new GroupmanagementAdm();
		$this->fncrole_model = new FunctionalrolesAdm();
	}


	//--- USER SELECTOR FUNCTIONS ------------------------------------------------

	/*
	 * list of all selected users by their idst
	 */
	protected function _selectAllUsers() {
		$idOrg = 0;//Get::req('id_org', DOTY_INT, 0);
		$descendants = false;//(Get::req('descendants', DOTY_INT, 0) > 0 ? true : false);
		$filter_text = Get::req('filter_text', DOTY_STRING, '');
		$learning_filter = Get::req('learning_filter', DOTY_STRING, 'none');
		$searchFilter = array(
			'text' => $filter_text,
			'suspended' => (Get::req('suspended', DOTY_INT, 1)>0 ? true : false)
		);
		$dyn_filter = $this->_getDynamicFilter(Get::req('dyn_filter', DOTY_STRING, ''));
		if ($dyn_filter !== false) {
			$searchFilter['dyn_filter'] = $dyn_filter;
		}
		$output = $this->user_model->getAllUsers($idOrg, $descendants, $searchFilter, true, $learning_filter);
		echo $this->json->encode($output);
	}

	/*
	 * return an instance of dynamic filter for users
	 */
	protected function _getDynamicFilter($input) {
		$output = false;
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		if (is_string($input) && $input != "") {
			$dyn_data = $json->decode(urldecode(stripslashes($input))); //decode the filter json string
			//die($input."\n\n".urldecode(stripslashes($input))."\n\n".'<pre>'.print_r($dyn_data, true).'</pre>');
			if (isset($dyn_data['exclusive']) && isset($dyn_data['filters'])) //required fields
				if (count($dyn_data['filters']) > 0) //there must be any filter selected
					$output = $dyn_data;
		}
		return $output;
	}

	/*
	 * retrieve table records
	 */
	public function getusertabledataTask() {
		$op = Get::req('op', DOTY_MIXED, false);
		switch ($op) {
			case "selectall": {
				$this->_selectAllUsers();
				return;                            
			} break;
		}

		$idOrg = Get::req('id_org', DOTY_INT, 0);
		$descendants = false;//(Get::req('descendants', DOTY_INT, 0) > 0 ? true : false);
		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$learning_filter = Get::req('learning_filter', DOTY_STRING, 'none');

		$var_fields = Get::req('_dyn_field', DOTY_MIXED, array());
		if (stristr($sort, '_dyn_field_') !== false) {
			$index = str_replace('_dyn_field_', '', $sort);
			$sort = $var_fields[(int)$index];
		}

		$filter_text = Get::req('filter_text', DOTY_STRING, '');

		$searchFilter = array(
			'text' => $filter_text,
			'suspended' => (Get::req('suspended', DOTY_INT, 1)>0 ? true : false)
		);

		$dyn_filter = $this->_getDynamicFilter(Get::req('dyn_filter', DOTY_STRING, ''));
		if ($dyn_filter !== false) {
			$searchFilter['dyn_filter'] = $dyn_filter;
		}

		$total = $this->user_model->getTotalUsers(0, $descendants, $searchFilter, true, $learning_filter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		$list = $this->user_model->getUsersList(0, $descendants, $pagination, $searchFilter, true, $learning_filter);

		//prepare the data for sending

		require_once(_adm_.'/lib/lib.field.php');
		$fman = new FieldList();
		$date_fields = $fman->getFieldsByType("date");

		$acl_man = Docebo::user()->getAclManager();
		$idst_org = $acl_man->getGroupST('/oc_'.(int)$idOrg);
		$output_results = array();
		if (is_array($list) && count($list)>0) {
			foreach ($list as $idst=>$record) {
				$query =	"SELECT params"
						." FROM %lms_organization_access WHERE idOrgAccess = '".$idOrg."' AND kind='user' AND value='".$record['idst']."'";
				$relation = sql_fetch_row(sql_query($query));

				$record_row = array(
					'id'		=> (int)$record['idst'],
					'userid'	=> Layout::highlight($acl_man->relativeId( $record['userid']), $filter_text),
					'firstname' => Layout::highlight($record['firstname'], $filter_text),
					'lastname'	=> Layout::highlight($record['lastname'], $filter_text),
					'relation'  => isset($relation[0])?$relation[0]:'',
					'email'		=> Layout::highlight($record['email'], $filter_text),
					'register_date' => Format::date($record['register_date'], "datetime"),
					'lastenter' => Format::date($record['lastenter'], "datetime"),
					'unassoc' => $idOrg > 0 ? ($record['is_descendant'] ? 0 : 1) : -1,
					'valid'		=> $record['valid'],
					'mod'		=> 'ajax.adm_server.php?r=adm/usermanagement/moduser&id='.(int)$idst,
					'del'		=> 'ajax.adm_server.php?r=adm/usermanagement/deluser&id='.(int)$idst,
				);

				foreach ($var_fields as $i=>$value) {
					if (is_numeric($value)) $name = '_custom_'.$value; else $name = $value;

					//check if we must perform some post-format on retrieved field values
					$content = (isset($record[$name]) ? $record[$name] : '');
					if ($name == 'register_date') $content = Format::date($content, 'datetime');
					if ($name == 'lastenter') $content = Format::date($content, 'datetime');
					if ($name == 'level' && $content != '') $content = Lang::t('_DIRECTORY_'.$content, 'admin_directory');
					if (!empty($date_fields) && in_array($value, $date_fields)) $content = Format::date(substr($content, 0, 10), 'date');

					$record_row['_dyn_field_'.$i] = $content;
				}

				$output_results[] = $record_row;
			}
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $rowsPerPage,
			'results' => count($output_results),
			'records' => $output_results
		);

		echo $this->json->encode($output);
	}

	//--- GROUP SELECTOR FUNCTIONS -----------------------------------------------


	protected function _selectAllGroups() {
		$filter = Get::req('filter_text', DOTY_STRING, "");
		$output = $this->group_model->getAllGroups($filter, true);
		echo $this->json->encode($output);
	}

	public function getgrouptabledataTask() {
		$op = Get::req('op', DOTY_MIXED, false);
		switch ($op) {
			case "selectall": {
				$this->_selectAllGroups();
				return;
			} break;
		}

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem'));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter = Get::req('filter', DOTY_STRING, "");
		$learning_filter = Get::req('learning_filter', DOTY_STRING, 'none');

		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		$list = $this->group_model->getGroupsList($pagination, $filter, $learning_filter);

		//format models' data
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			foreach ($list as $record) {
				$_groupid = $acl_man->relativeId($record->groupid);
				$_description = strip_tags($record->description);
				if (strlen($_description)>100) $_description = substr($_description, 0, 97).'...';
				$records[] = array(
					'id' => (int)$record->idst,
					'groupid' => highlightText($_groupid, $filter),
					'description' => highlightText($_description, $filter),
					'usercount' => $record->usercount,
				);
			}
		}

		$output = array(
			'startIndex' => $startIndex,
			'recordsReturned' => count($records),
			'sort' => $sort,
			'dir' => $dir,
			'totalRecords' => $this->group_model->getTotalGroups($filter, $learning_filter),
			'pageSize' => $rowsPerPage,
			'records' => $records
		);

		echo $this->json->encode($output);
	}

	//--- ORGCHART SELECTOR FUNCTIONS --------------------------------------------

	protected function _getSelectedNode() {
		return 0;
	}

	protected function _setSelectedNode($idOrg) {
		
	}

	protected function _assignActions(&$nodes, &$conversion_table) {
		if (!is_array($nodes)) return;
		for ($i=0; $i<count($nodes); $i++) {
			$index = $nodes[$i]['node']['id'];
			$nodes[$i]['node']['id'] = $conversion_table[0][$index].'_'.$conversion_table[1][$index];
			$nodes[$i]['node']['options'] = $this->_getNodeActions($nodes[$i]['node']);
			if (isset($nodes[$i]['children']) && count($nodes[$i]['children']) > 0) {
				$this->_assignActions($nodes[$i]['children'], $conversion_table);
			}
		}
	}

	protected function _getNodeActions($node) {
		$output = array();
		return $output;
	}

	protected function _getIdOrgByNodeId($node_id) {
		if (!$node_id) return 0;
		$arr = explode('_', $node_id);
		$acl_man = Docebo::user()->getACLManager();
		$groupid = $acl_man->getGroupId((int)$arr[0]);
		return (int)str_replace('/oc_', '', $groupid);
	}

	public function getorgcharttreedataTask() {
		$command = Get::req('command', DOTY_ALPHANUM, "");

		switch ($command) {

			case "expand": {
				$node_id = Get::req('node_id', DOTY_STRING, "");
				$idOrg = $this->_getIdOrgByNodeId($node_id);
				$initial = (Get::req('initial', DOTY_INT, 0) > 0 ? true : false);
				$_conversion_table = $this->user_model->getOrgchartIdstConversionTable();

				if ($initial) {
					//get selected node from session and set the expanded tree
					$idOrg = $this->_getSelectedNode();
					$nodes = $this->user_model->getOrgChartInitialNodes($idOrg, true);
					//create actions for every node
					$this->_assignActions($nodes, $_conversion_table);
					//set output
					if (is_array($nodes)) {
						$output = array(
							'success' => true,
							'nodes' => $nodes,
							'initial' => $initial
						);
					} else {
						$output = array('success' => false);
					}
				} else {
					//extract node data
					$nodes = $this->user_model->getOrgChartNodes($idOrg, false, false, true);
					//create actions for every node
					for ($i=0; $i<count($nodes); $i++) {
						$index = $nodes[$i]['id'];
						$nodes[$i]['id'] = $_conversion_table[0][$index].'_'.$_conversion_table[1][$index];
						$nodes[$i]['options'] = $this->_getNodeActions($nodes[$i]);
					}
					//set output
					$output = array(
						'success' => true,
						'nodes' => $nodes,
						'initial' => $initial
					);
				}
				echo $this->json->encode($output);
			} break;

			case "set_selected_node": {
				$node_id = Get::req('node_id', DOTY_STRING, "");
				$idOrg = $this->_getIdOrgByNodeId($node_id);
				$this->_setSelectedNode($idOrg);
			} break;
		}
		/*$node_id = Get::req('id', DOTY_INT, -1);
		if ($node_id >= 0) {
			$output = $this->user_model->getNodesById($node_id);
			echo $this->json->encode($output);
		}*/
	}

	//--- FNCROLE SELECTOR FUNCTIONS ---------------------------------------------

	protected function _selectAllFncroles() {
		$filter = Get::req('filter_text', DOTY_STRING, "");
		$output = $this->fncrole_model->getAllFunctionalRoles($filter, true);
		echo $this->json->encode($output);
	}

	public function getfncroletabledataTask() {
		$op = Get::req('op', DOTY_MIXED, false);
		switch ($op) {
			case "selectall": {
				$this->_selectAllFncroles();
				return;
			} break;
		}

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem'));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter = Get::req('filter', DOTY_STRING, "");

		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		$list = $this->fncrole_model->getFunctionalRolesList($pagination, array('text' => $filter));

		//format models' data
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			foreach ($list as $record) {
				$_description = strip_tags($record->description);
				if (strlen($_description)>100) $_description = substr($_description, 0, 97).'...';
				$records[] = array(
					'id' => (int)$record->id_fncrole,
					'name' => highlightText($record->name, $filter),
					'group' => highlightText($record->group_name, $filter),
					'description' => highlightText($_description, $filter),
					'users' => $record->users,
				);
			}
		}

		$output = array(
			'startIndex' => $startIndex,
			'recordsReturned' => count($records),
			'sort' => $sort,
			'dir' => $dir,
			'totalRecords' => $this->fncrole_model->getFunctionalRolesTotal($filter),
			'pageSize' => $rowsPerPage,
			'records' => $records
		);

		echo $this->json->encode($output);
	}

}

?>