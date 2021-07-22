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

class TemplatelayoutAdmController extends AdmController {

	protected $model;
	protected $json;

	public function __construct() {
		parent::__construct('templatelayout');
		$this->model = new TemplateLayoutAdm();
		$this->json = new Services_JSON();
	}

	protected function _getTableStatus($paramName, $default = false) {
		if (!isset($_SESSION['template_status'][$paramName])) $_SESSION['template_status'][$paramName] = $default;
		return $_SESSION['template_status'][$paramName];
	}

	protected function _setTableStatus($paramName, $value) {
		$_SESSION['template_status'][$paramName] = $value;
	}


	public function showTask() {
		$this->render('show', array(
			'sort' => $this->_getTableStatus("sort", "name"),
			'dir' => $this->_getTableStatus("dir", "asc"),
			'filter_text' => $this->_getTableStatus("filter", "")
		));
	}

	public function tabledataTask() {
		$this->_setTableStatus("filter", Get::req("filter", DOTY_STRING, ""));

		$startIndex = $this->_getTableStatus("startIndex", 0);
		$page_size = Get::sett('visuItem', 25);
		$sort = $this->_getTableStatus("sort", "name");
		$dir = $this->_getTableStatus("dir", "asc");
		$filter = $this->_getTableStatus("filter");

		$params = new stdClass();
		$params->startIndex = $startIndex;
		$params->results = $page_size;
		$params->sort = $sort;
		$params->dir = $dir;
		$params->filter = $filter;

		$templates = $this->model->getTemplates($params);
		$template_rows = array();
		if (is_array($templates)) {
			foreach ($templates as $template) {
				$template_rows[] = array(
					'id' => $template->id_template,
					'name' => highlightText($template->name, $filter),
					'date_creation' => Format::date($template->date_creation, "datetime"),
					'last_modify' => Format::date($template->last_modify, "datetime"),
					'del' => 'ajax.adm_server.php?r=adm/templatelayout/delete&id='.(int)$template->id_template,
				);
			}
		}

		$output = array(
			'startIndex' => $startIndex,
			'recordsReturned' => count($templates),
			'sort' => $sort,
			'dir' => $dir,
			'totalRecords' => $this->model->getTotalTemplates($filter),
			'pageSize' => $page_size,
			'records' => $template_rows
		);

		echo $this->json->encode($output);
	}


	public function deleteTask() {
		$output = array('success'=>false);
		$id = Get::req('id', DOTY_INT, -1);
		if ($id > 0) {
			$output['success'] = $this->model->deleteTemplate($id);
		}
		echo $this->json->encode($output);
	}


	public function createTask() {
		YuiLib::load('colorpicker');
		$this->render("edit");
	}


	public function editTask() {
		YuiLib::load('colorpicker');
		$id = Get::req('id', DOTY_INT, -1);
		if ($id>0) {
			$params = array(
				'id' => $id,
				'data' => $this->model->getTemplateData($id)
			);
		} else {
			$params = array('error' => Lang::t('_INVALID_TEMPLATE', 'template'));
		}
		$this->render("edit", $params);
	}


	public function saveTask() {
		Util::jump_to('index.php?r=adm/templatelayout/show');
	}


	public function updateTask() {
		Util::jump_to('index.php?r=adm/templatelayout/show');
	}

}