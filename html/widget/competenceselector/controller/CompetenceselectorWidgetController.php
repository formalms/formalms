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

class CompetenceselectorWidgetController extends Controller {

	protected $model = null;
	protected $competences_model = null;
	protected $json = null;

	function init() {
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();
		//$this->model = new CompetenceselectorWidgetModel();
		$this->competences_model = new CompetencesAdm();
	}


	//--- Tasks ------------------------------------------------------------------

	function gettreedataTask() {
		$command = Get::req('command', DOTY_ALPHANUM, "");

		switch ($command) {
			case "expand": {
				$node_id = Get::req('node_id', DOTY_INT, 0);
				$initial = (Get::req('initial', DOTY_INT, 0) > 0 ? true : false);

				/*if ($initial) {
					//get selected category from session and set the expanded tree
					$node_id = $this->_getSelectedCategory();
					$nodes = $this->model->getInitialCategories($id_node, );
					//create actions for every node
					$this->_assignActions($nodes);
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
				} else {*/
					//extract node data
					$nodes = $this->competences_model->getCategories($node_id);

					//if request is invalid, return error message ...
					if (!is_array($nodes)) {
						echo $this->json->encode(array('success' => false));
						return;
					}

					//create actions for every node
					for ($i=0; $i<count($nodes); $i++) {
						$nodes[$i]['options'] = false;
					}
					//set output
					$output = array(
						'success' => true,
						'nodes' => $nodes,
						'initial' => $initial
					);
				//}
				echo $this->json->encode($output);
			} break;
		}
	}


	function gettabledataTask() {
		//read from input and prepare filter and pagination variables
		$id_category = Get::req('id_category', DOTY_INT, 0);
		$descendants = (Get::req('descendants', DOTY_INT, 0) > 0 ? true : false);
		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter_text = Get::req('filter_text', DOTY_STRING, '');

		$searchFilter = array(
			'text' => $filter_text
		);

		//get total from database and validate the results count
		$total = $this->competences_model->getCompetencesTotal($id_category, $descendants, $searchFilter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		//set pagination argument
		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		//read records from database
		$list = $this->competences_model->getCompetencesList($id_category, $descendants, $pagination, $searchFilter);

		//prepare the data for sending
		$output_results = array();
		if (is_array($list) && count($list)>0) {
			foreach ($list as $idst=>$record) {
				//format description field
				$description = strip_tags($record->description);
				if (strlen($description) > 200) {
					$description = substr($description, 0, 197).'...';
				}

				//prepare output record
				$output_results[] = array(
					'id' => $record->id_competence,
					'name' => Layout::highlight($record->name, $filter_text),
					'description' => Layout::highlight($description, $filter_text),
					'typology' => $record->typology,
					'type' => $record->type,
					//'expiration' => $record->expiration,
					//'score' => ($record->type == 'score' ? $record->score : '-'),
				);
			}
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $rowsPerPage,
			'results' => count($list),
			'records' => $output_results
		);

		echo $this->json->encode($output);
	}


}

?>