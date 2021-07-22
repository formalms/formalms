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

class AdminrulesAdmController extends AdmController
{
	var $model;
	var $json;
	var $acl_man;
	
	public function init()
	{
		parent::init();
		require_once(_base_.'/lib/lib.json.php');

		$this->model = new AdminrulesAdm();
		$this->json = new Services_JSON();
		$this->acl_man =& Docebo::user()->getAclManager();
	}

	public function show()
	{
		$res = Get::req('res', DOTY_STRING, '');
		$result_message = "";

		switch ($res) {
			case 'ok': $result_message = UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'standard')); break;
			case 'err': $result_message = UIFeedback::error (Lang::t('_OPERATION_FAILURE', 'standard')); break;
		}

		$this->render('show', array(
			'result_message' => $result_message
		));
	}

	public function getGroups()
	{
		$start_index	= Get::req('startIndex', DOTY_INT, 0);
		$results		= Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort			= Get::req('sort', DOTY_MIXED, 'userid');
		$dir			= Get::req('dir', DOTY_MIXED, 'asc');

		$total_group = $this->model->totalGroup();
		$array_group = $this->model->loadGroup($start_index, $results, $sort, $dir);

		$res = array(
			'totalRecords' => (int)$total_group,
			'startIndex' => $start_index,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $results,
			'results' => count($array_group),
			'records' => $array_group
		);
		
		echo $this->json->encode($res);
	}

	public function addGroup()
	{
		if(Get::req('confirm', DOTY_INT, 0) == 1)
		{
			$name = Get::req('name', DOTY_MIXED, '');
			$output = array();

			if($name == '')
				$name = Lang::t('_UNDEFINED', 'adminrules');

			$result = $this->model->addGroup($name);

			$output['success'] = $result;

			echo $this->json->encode($output);
		}
		else
		{
			$output = array();
		
			$body =	Form::openForm('add_rules_form', 'ajax.adm_server.php?r=adm/adminrules/addGroup&confirm=1')
					.Form::openElementSpace()
					.Form::getTextfield(Lang::t('_NAME', 'adminrules'), 'name', 'name', '255', '')
					.Form::closeElementSpace()
					.Form::closeForm();

			$output['success'] = true;
			$output['header'] = Lang::t('_ADD', 'adminrules').'&nbsp;';
			$output['body'] = $body;

			echo $this->json->encode($output);
		}
	}

	public function delGroup()
	{
		$idst = Get::req('idst', DOTY_INT, 0);
		$output = array();

		$result = $this->model->delGroup($idst);

		$output['success'] = $result;

		echo $this->json->encode($output);
	}

	public function special()
	{
		require_once(_base_.'/lib/lib.preference.php');
		$preference = new AdminPreference();

		$idst = Get::req('idst', DOTY_INT, 0);
		$output = array();

		if(Get::req('confirm', DOTY_INT, 0) == 1)
		{
			$rules = $preference->getRules();
			$new_rules = array();

			foreach($rules as $path => $info)
			{
				switch($info['type'])
				{
					case 'enum':
						$new_rules[$path] = Get::req(str_replace('.', '_', $path), DOTY_MIXED, 'off');
					break;

					case 'integer':
						$new_rules[$path] = Get::req(str_replace('.', '_', $path), DOTY_INT, 0);
					break;
				}
			}

			$output['success'] = $preference->saveSpecialAdminRules($idst, $new_rules);

			echo $this->json->encode($output);
		}
		else
		{
			$body =	Form::openForm('add_rules_form', 'ajax.adm_server.php?r=adm/adminrules/special&confirm=1')
					.Form::openElementSpace()
					.$preference->getSpecialModifyMask($idst, 'adminrules')
					.Form::closeElementSpace()
					.Form::closeForm();

			$output['success'] = true;
			$output['header'] = Lang::t('_MOD', 'adminrules').'&nbsp;';
			$output['body'] = $body;

			echo $this->json->encode($output);
		}
	}

	public function lang()
	{
		require_once(_base_.'/lib/lib.preference.php');
		$preference = new AdminPreference();

		$idst = Get::req('idst', DOTY_INT, 0);
		$output = array();

		if(Get::req('confirm', DOTY_INT, 0) == 1)
		{
			$new_lang = array();
			
			if(isset($_POST['admin_lang']))
				$new_lang = array_keys($_POST['admin_lang']);

			$new_lang = $this->json->encode($new_lang);

			$output['success'] = $preference->saveLangAdminRules($idst, $new_lang);

			echo $this->json->encode($output);
		}
		else
		{
			$body =	Form::openForm('add_rules_form', 'ajax.adm_server.php?r=adm/adminrules/lang&confirm=1')
					.Form::openElementSpace()
					.Form::getHidden('idst', 'idst', $idst)
					.$preference->getLangModifyMask($idst)
					.Form::closeElementSpace()
					.Form::closeForm();

			$output['success'] = true;
			$output['header'] = Lang::t('_MOD', 'adminrules').'&nbsp;';
			$output['body'] = $body;

			echo $this->json->encode($output);
		}
	}

	public function menu()
	{
		if(isset($_POST['back'])){
			Util::jump_to('index.php?r=adm/adminrules/show');
                }

		$idst = Get::req('idst', DOTY_INT, 0);
        $active_tab = Get::req('active_tab', DOTY_INT, 0);
        
        $menu = CoreMenu::getList(array('framework', 'alms'));
        
        $result_message = "";
		if(isset($_POST['save']))
		{
			$adm_perm = array();
			if(isset($_POST['adm_perm'])){
				$adm_perm = array_keys($_POST['adm_perm']);
                        }

			$res = $this->model->saveAdminPerm($idst, $adm_perm);
			$result_message = $res
				? UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'standard'))
				: UIFeedback::error (Lang::t('_OPERATION_FAILURE', 'standard'));

			Util::jump_to('index.php?r=adm/adminrules/show&res='.($res ? 'ok' : 'err'));
		}

		$this->render('menu', array(
				'idst' => $idst,
				'menu' => $menu,
				'active_tab' => $active_tab,
				'model' => $this->model,
				'save_res' => $result_message
		));
	}

	public function admin_manage()
	{
		$idst = Get::req('idst', DOTY_INT, 0);
                
		$this->render('admin_manage', 
                        array(
                            'idst' => $idst,
                            'back_link' => getBackUi('index.php?r=adm/adminrules/show', Lang::t('_BACK', 'standard')),
                            'model' => $this->model));
	}

	public function getAdmins()
	{
		$idst = Get::req('idst', DOTY_INT, 0);

		$start_index	= Get::req('startIndex', DOTY_INT, 0);
		$results		= Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort			= Get::req('sort', DOTY_MIXED, 'userid');
		$dir			= Get::req('dir', DOTY_MIXED, 'asc');

		$total_group = $this->model->totalAdmin($idst);
		$array_group = $this->model->loadAdmin($idst, $start_index, $results, $sort, $dir);

		$res = array(	'totalRecords' => $total_group,
						'startIndex' => $start_index,
						'sort' => $sort,
						'dir' => $dir,
						'rowsPerPage' => $results,
						'results' => count($array_group),
						'records' => $array_group);

		echo $this->json->encode($res);
	}

	public function add_admin()
	{
		$idst = Get::req('idst', DOTY_INT, 0);

		require_once(_base_.'/lib/lib.form.php');
		require_once(_base_.'/lib/lib.userselector.php');

		$user_selector = new UserSelector();

		if(isset($_POST['cancelselector']))
			Util::jump_to('index.php?r=adm/adminrules/admin_manage&idst='.$idst);

		if(isset($_POST['okselector']))
		{
			$user_selected = $user_selector->getSelection($_POST);

			$user_alredy_subscribed = $this->model->loadUserSelectorSelection($idst);
			$user_selected = array_diff($user_selected, $user_alredy_subscribed);

			if(count($user_selected) == 0)
				Util::jump_to('index.php?r=adm/adminrules/add_admin&load=1&idst='.$idst);

			if($this->model->saveNewAdmin($idst, $user_selected))
				Util::jump_to('index.php?r=adm/adminrules/admin_manage&idst='.$idst.'&amp;res=ok_ins');
			Util::jump_to('index.php?r=adm/adminrules/admin_manage&idst='.$idst.'&amp;res=err_ins');
		}
		else
		{
			$user_selector->show_user_selector = TRUE;
			$user_selector->show_group_selector = FALSE;
			$user_selector->show_orgchart_selector = FALSE;

			if(isset($_GET['load']))
			{
				$user_selector->requested_tab = PEOPLEVIEW_TAB;

				$user_alredy_subscribed = $this->model->loadUserSelectorSelection($idst);

				$user_selector->resetSelection($user_alredy_subscribed);
			}

			$user_selector->setUserFilter('exclude', array($this->acl_man->getAnonymousId()));
			$arr_idst = $this->acl_man->getGroupsIdstFromBasePath('/framework/level/admin');
			$user_selector->setUserFilter('group', $arr_idst);

			$this->render('add_admin', array(	'idst' => $idst,
												'user_selector' => $user_selector,
												'model' => $this->model));
		}
	}



	public function saveDataTask() {

		$idst = Get::req('idst', DOTY_INT, 0);
		$old_value = Get::req('old_value', DOTY_STRING, "");
		$new_value = Get::req('new_value', DOTY_STRING, "");

		$result = $this->model->renameProfile($idst, $new_value);

		$output = array(
			'success' => $result,
			'new_value' => $new_value,
			'old_value' => $old_value
		);

		echo $this->json->encode($output);
	}



	public function delAdmin() {
		$idst = Get::req('idst', DOTY_INT, 0);
		$idst_member = Get::req('idstMember', DOTY_INT, 0);

		$res = FALSE;
		if ($idst > 0 && $idst_member > 0) {
			$res = $this->acl_man->removeFromGroup($idst, $idst_member);
		}

		$output = array('success' => $res);
		echo $this->json->encode($output);
	}


}
?>