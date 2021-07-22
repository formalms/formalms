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

class DbupgradesAdmController extends AdmController {

        protected $model;
        protected $json;
        
        public $link = 'adm/dbupgrades';
    
	public function init() {
		parent::init();
		require_once(_base_.'/lib/lib.json.php');
		$this->model = new DbupgradesAdm();
		$this->json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
	}
        
	public function showTask() {
            
            $this->render('show', array(
                    'filter_text' => ""
            ));

	}


	public function getdbupgradestabledataTask() {

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter = Get::req('filter', DOTY_STRING, "");

		$total = $this->model->getDbUpgradesTotal($filter);
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

		$list = $this->model->getDbUpgradesList($pagination, $filter);

		//format models' data
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			foreach ($list as $record) {
				$records[] = array(
					'script_id' => Layout::highlight($record->script_id, $filter),
					'script_name' => Layout::highlight($record->script_name, $filter),
					'script_description' => Layout::highlight($record->script_description, $filter),
					'script_version' => Layout::highlight($record->script_version, $filter),
					'core_version' => Layout::highlight($record->core_version, $filter),
					'creation_date' => Format::date($record->creation_date, 'datetime'),
					'execution_date' => Format::date($record->execution_date, 'datetime')
				);
			}
		}

		$output = array(
			'startIndex' => $startIndex,
			'recordsReturned' => count($records),
			'sort' => $sort,
			'dir' => $dir,
			'totalRecords' => $total,
			'pageSize' => $rowsPerPage,
			'records' => $records
		);

		echo $this->json->encode($output);
	}


        
}

?>