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

class PrivacypolicyAdmController extends AdmController {

	protected $db = null;
	protected $model = null;
	protected $json = null;
	protected $permissions;
	public $link = 'adm/privacypolicy';

	public function init() {
		parent::init();
		require_once(_base_.'/lib/lib.json.php');
		$this->db = DbConn::getInstance();
		$this->model = new PrivacypolicyAdm();
		$this->json = new Services_JSON();
		$this->permissions = array(
			'view'	=> checkPerm('view', true, 'privacypolicy'),					//view module
			'add'	=> checkPerm('mod', true, 'privacypolicy'),						//create policies
			'mod'	=> checkPerm('mod', true, 'privacypolicy'),						//edit policies
			'del'	=> checkPerm('del', true, 'privacypolicy')						//delete policies
		);
	}


	protected function _getErrorMessage($code) {
		$message = "";

		switch ($code) {
			case "no permission": $message = "You don't have permission to do this."; break;
			case "success": $message = Lang::t('_OPERATION_SUCCESSFUL', 'standard'); break;
			case "failure": $message = Lang::t('_OPERATION_FAILURE', 'standard'); break;
			default: $message = Lang::t('_OPERATION_FAILURE', 'standard'); break;
		}

		return $message;
	}

	/*
	 * load the groups management page
	 */
	public function show() {
		Yuilib::load('tabview,selector');
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);

		$this->render('show', array(
			'permissions' => $this->permissions,
			'result_message' => "",
			'filter_text' => ""
		));
	}

	/**
	 * extract the data to load into policiess table
	 */
	public function gettabledataTask() {
		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem'));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter = Get::req('filter', DOTY_STRING, "");

		//get total from database and validate the results count
		$total = $this->model->getPoliciesTotal($filter);
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

		$list = $this->model->getPoliciesList($pagination, $filter);

		//format models' data
		$records = array();
		if (is_array($list)) {
			foreach ($list as $record) {
				$records[] = array(
					'id' => (int)$record->id_policy,
					'name' => highlightText($record->name, $filter),
					'is_assigned' => $record->is_assigned,
					//'mod' => 'ajax.adm_server.php?r=adm/privacypolicy/mod&id='.(int)$record->id_policy,
                    'mod' => '<a href="index.php?r=adm/privacypolicy/mod&id='.(int)$record->id_policy.'">'.Get::sprite('subs_mod', Lang::t('_MOD', 'standard')).'</a>',
					'del' => 'ajax.adm_server.php?r=adm/privacypolicy/del&id='.(int)$record->id_policy
				);         
			}
		}

		if (is_array($records)) {
			$output = array(
				'startIndex' => $startIndex,
				'recordsReturned' => count($records),
				'sort' => $sort,
				'dir' => $dir,
				'totalRecords' => $total,//$this->model->getTotalGroups($filter),
				'pageSize' => $rowsPerPage,
				'records' => $records
			);
		} else {
			$output['success'] = false;
		}

		echo $this->json->encode($output);
	}

	/**
	 * delete a policy
	 */
	public function delTask() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_policy = Get::req('id', DOTY_INT, -1);
		$output['success'] = ($id_policy > 0 ? $this->model->deletePolicy($id_policy) : false);
		echo $this->json->encode($output);
	}


	public function addTask() {
		//check permissions
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}
                 
		$this->render('policy_editmask', array());
        
        /*  
		$params = array(
			'success' => true,
			'header' => Lang::t('_ADD', 'standard'),
			'body' =>  ob_get_clean()
		);
		
        @ob_start();
		echo $this->json->encode($params);    
	      */
    }


	/**
	 * insert in DB the data submitted from the add dialog
	 */
	public function add_actionTask() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$name = Get::req('name', DOTY_STRING, "");
		$translations = Get::req('translation', DOTY_MIXED, FALSE);

		$res = $this->model->createPolicy($name, $translations);
		$output = array('success' => $res ? TRUE : FALSE);

		echo $this->json->encode($output);
        
        
     //   $this->render('show', $output);
        
        
        Util::jump_to("index.php?r=adm/privacypolicy/show&res=".$res);
	}



	/**
	 * produces the form to load into modify dialog
	 */
	public function modTask() {
		//check permissions
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_policy = Get::req('id', DOTY_INT, -1);
		if ($id_policy <= 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getErrorMessage("failure"))
			);
			echo $this->json->encode($output);
			return;
		}


        
		//retrieve category info (name and translations
		$info = $this->model->getPolicyInfo($id_policy);
        
        
		$this->render('policy_editmask', array(
			'id_policy' => $id_policy,
			'name' => $info->name,
            'is_default' => $info->is_default,
			'translations' => $info->translations
		));


          /*         
		$params = array(
			'success' => true,
			'header' => Lang::t('_MOD', 'standard'),
			'body' =>  ob_get_clean()
		);
		@ob_start();
		echo $this->json->encode($params);  
        */
        
	}

	/**
	 * modify the data submitted from modify dialog
	 */
	public function mod_action() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$id_policy = Get::req('id_policy', DOTY_INT, -1);
		$name = Get::req('name', DOTY_STRING, "");
        $is_default = Get::req('is_default', DOTY_INT, 0);
        $reset_policy = Get::req('reset_policy', DOTY_INT, 0);        
		$translations = Get::req('translation', DOTY_MIXED, FALSE);  
        
//        var_dump(Get::req('translation', DOTY_MIXED, FALSE));
      //  die();        
        
        
 
		$res = $this->model->updatePolicy($id_policy,  $name,$is_default, $reset_policy,  $translations);
		$output = array('success' => $res ? TRUE : FALSE);

		echo $this->json->encode($output);
        
        Util::jump_to("index.php?r=adm/privacypolicy/show&res=".$res );
	}


	public function assignTask() {
		$id_policy = Get::req('id', DOTY_INT, 0);
		if ($id_policy <= 0) {
			//...
		}

		$selection = $this->model->getSelectedOrgchart($id_policy);
		$already_assigned = $this->model->getAlreadyAssignedOrgchart();

		$this->render('assign_dialog', array(
			'id_policy' => $id_policy,
			'selection' => $selection,
			'already_assigned' => array_values(array_diff($already_assigned, $selection))
		));

		$body = ob_get_clean();
		@ob_start();

		$output = array(
			'success' => TRUE,
			'header' => Lang::t('_ASSIGN_POLICY', 'privacypolicy'),
			'body' => $body,
			'selection' => $selection,
			'disabled' => array_values(array_diff($already_assigned, $selection))
		);
		echo $this->json->encode($output);
	}


	public function assign_actionTask() {
		$id_policy = Get::req('id_policy', DOTY_INT, 0);
		$selection = Get::req('selection', DOTY_STRING, "");
		$old_selection = Get::req('old_selection', DOTY_STRING, "");

		$output = array();
		
		$selection = str_replace(",,", ",", $selection);
		if ($selection{0} == ",") $selection = substr($selection, 1);

		if ($selection != "") {
			$list = explode(",", $selection);
			if (!empty($list)) {
				$res = $this->model->setOrgchartAssignment($id_policy, $list);
				$output['success'] = $res ? TRUE : FALSE;
			}
		}
		else if (!empty($old_selection)) {
			// if we are unselecting an item we doesn't return the
			// error in case the (new) selection is empty:
			$res = $this->model->resetOrgchartAssignment($id_policy, $list);
			$output['success'] = true;
		}
		echo $this->json->encode($output);
	}


}

