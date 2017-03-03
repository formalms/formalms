<?php

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

/**
 * @package course management
 * @subpackage course catalogue
 * @category ajax server
 * @version $Id:$
 *
 */

if(!defined("IN_FORMA") && !defined("IN_AJAX")) die('You can\'t access directly');
if($GLOBALS['current_user']->isAnonymous()) die('You can\'t access');

$sortable = array('type_quest', 'title_quest', 'difficult', 'sequence');

$op = Get::gReq('op', DOTY_ALPHANUM, '');
switch($op) {
	case "getselected" : {

		require_once(_lms_.'/lib/lib.quest_bank.php');
		$qbm = new QuestBankMan();

		$quest_category 	= Get::req('quest_category', DOTY_INT);
		$quest_difficult 	= Get::req('quest_difficult', DOTY_INT);
		$quest_type 		= Get::req('quest_type', DOTY_ALPHANUM);
		$params_extracat=array();
		$all_categories = $qbm->getExtraCategoriesList();
		foreach ($all_categories as $key => $value) {
		    $quest_extracategory= Get::req('quest_extracategory_'.$key, DOTY_INT);
		    $params_extracat[$key]=$quest_extracategory;
		}
		
		$sort		= Get::req('sort', DOTY_STRING, "");
		$dir		= Get::req('dir', DOTY_STRING, "asc");
		if(!in_array($sort, $sortable)) $sort = 'idQuest';
		switch ($dir) {
			case "desc" : { $dir = 'desc'; };break;
			default : { $dir = 'asc'; };break;
		}

		$startIndex 		= 0;
		$results 			= 999999999999;

		$re_quest = $qbm->resQuestList($quest_category, $quest_difficult, $quest_type, $params_extracat, $startIndex, $results, $sort, $dir);

		$value = array();
		while(list($id_q) = $qbm->fetch($re_quest)) {

			$value[] = (int)$id_q;
		}

		$json = new Services_JSON();
		$output = $json->encode($value);
  		aout($output);
	};break;
	case "delquest" : {
		//require_once($GLOBALS['where_framework'].'/lib/lib.json.php');

		$id_quest = Get::pReq('id_quest', DOTY_INT);
		$row_quest = Get::pReq('row_quest', DOTY_ALPHANUM);

		require_once(_lms_.'/lib/lib.quest_bank.php');
		$qman = new QuestBankMan();
		$result = $qman->delQuest($id_quest);

		$value = array("result"=>$result, "id_quest"=>$id_quest, "row_quest"=>$row_quest, "error"=>$qman->last_error);

		$json = new Services_JSON();
		$output = $json->encode($value);
		aout($output);
	};break;
	default : {

		require_once(_lms_.'/lib/lib.quest_bank.php');
		$qbm = new QuestBankMan();

		$quest_category 	= Get::pReq('quest_category', DOTY_INT);
		$quest_difficult 	= Get::pReq('quest_difficult', DOTY_INT);
		$quest_type 		= Get::pReq('quest_type', DOTY_ALPHANUM);
		$params_extracat=array();
		$all_categories = $qbm->getExtraCategoriesList();
		foreach ($all_categories as $key => $value) {
		    $quest_extracategory= Get::pReq('quest_extracategory_'.$key, DOTY_INT);
		    $params_extracat[$key]=$quest_extracategory;
		}
		
		$sort		= Get::req('sort', DOTY_STRING, "");
		$dir		= Get::req('dir', DOTY_STRING, "asc");
		if(!in_array($sort, $sortable)) $sort = 'sequence';
		switch ($dir) {
			case "desc" : { $dir = 'desc'; };break;
			default : { $dir = 'asc'; };break;
		}

		$startIndex 		= Get::pReq('startIndex', DOTY_INT, 0);
		$results 			= Get::pReq('results', DOTY_INT, 30);

		$totalRecords = $qbm->totalQuestList($quest_category, $quest_difficult, $quest_type, $params_extracat);
		$re_quest = $qbm->resQuestList($quest_category, $quest_difficult, $quest_type, $params_extracat, $startIndex, $results, $sort, $dir);
			
			/*
			'totalRecords' => $tot_courses,
			'startIndex' => $start_index,
			'sort' => 'date',
			'dir' => 'asc',
			'rowsPerPage' => $rows_per_page,
			'results' => count($courses),
			'records' => $courses_html
			*/
		$value = array(
			"totalRecords" => (int)$totalRecords,
			"startIndex" => (int)$startIndex,
			'sort' => 'category_quest',
			'dir' => 'asc',
			'rowsPerPage' => $results,
			"results" => (int)$qbm->num_rows($re_quest),
			"records" => array(),
			
			"qc" => $quest_category,
			"qd" => $quest_difficult,
			"qt" => $quest_type,
			"si" => $startIndex,
			"re" => $results
		);

		while(list($id_q, $id_c, $type, $title, $difficult, $time_assigned, $sequence, $extra_fields, $extra_values) = $qbm->fetch($re_quest)) {

			$value['records'][] = array(
				"id_quest" => $id_q,
				"category_quest" => $id_c,
				"type_quest" => $type,
				"title_quest" => $title,
				"difficult" => $difficult,
				"sequence" => $sequence
				, "extra_fields" => $extra_fields
				, "extra_values" => $extra_values
			);
		}

		//require_once($GLOBALS['where_framework'].'/lib/lib.json.php');

		$json = new Services_JSON();
		$output = $json->encode($value);
		aout($output);
	};break;
}

?>