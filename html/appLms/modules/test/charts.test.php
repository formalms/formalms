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

if(!defined('IN_FORMA')) die('You cannot access this file directly');

require_once($GLOBALS['where_framework']."/addons/pchart/pChart/pData.class");
require_once($GLOBALS['where_framework']."/addons/pchart/pChart/pChart.class");
require_once($GLOBALS['where_framework'].'/lib/lib.newtypeone.php');

define("_STACKED_CHART", 'stacked');
define("_BAR_CHART", 'bar');
define("_RADAR_CHART", 'radar');
define("_COLUMN_CHART", 'column');


define("_RED", 0);
define("_GREEN", 1);
define("_BLUE", 2);

class Test_Charts {

	var $idTest = 0;
	var $idUser = 0;

	var $dataSet = null;
	var $chart = null;
	var $table = null;
	var $testInfo = null;
	var $lang = null;

	var $fontPath = '';
	var $imagePath = '';
	var $valid = false;
	var $values = array();

	var $settings = null;

	function Test_Charts($idTest, $idUser) {
		//check ids
		if ((int)$idTest<=0 || (int)$idUser<=0)	return false;
		$this->idTest = (int)$idTest;
		$this->idUser = (int)$idUser;

		//set params
		$this->fontPath = $GLOBALS['where_framework_relative'].'/addons/pchart/Fonts/';
		$this->imagePath = $GLOBALS['where_files_relative'].'/tmp/';
		$this->valid = true;

		$this->lang =& DoceboLanguage::createInstance('test', 'lms');
		$this->_setTestInfo();
		$this->table = new TypeOne();
		
		//set rendering properties
		$this->settings = new stdClass();
		$this->settings->width = 800;
		$this->settings->height = 400;
		
		$this->settings->roundThreshold = 2;
		$this->settings->roundRadius = 10;
		$this->settings->horizontalDistance = 50;
		$this->settings->verticalDistance = 30;
		$this->settings->lineWidth = 4;
		$this->settings->legendWidth = 200;

		$this->settings->bgColor = array(230, 230, 230); //R, G, B
		$this->settings->graphColor = array(255, 255, 255);
		$this->settings->scaleColor = array(150, 150, 150);
		$this->settings->lineColor = array(230, 230, 230);
		$this->settings->tresholdColor = array(143, 55, 72);
		$this->settings->legendColor = array(255, 255, 255);
		$this->settings->titleColor = array(50, 50, 50);

		return true;
	}

	function _setTestInfo() {
		$json = new Services_JSON();
		list($info) = sql_fetch_row(sql_query("SELECT chart_options FROM ".$GLOBALS['prefix_lms']."_test WHERE idTest=".$this->idTest));
		if ($info != "") {
			$this->testInfo = $json->decode($info);
		} else {
			$this->testInfo = new stdClass();
		}
		if (!property_exists($this->testInfo, 'use_charts')) $this->testInfo->use_charts = false;
		if (!property_exists($this->testInfo, 'selected_chart')) $this->testInfo->selected_chart = 'column';
		if (!property_exists($this->testInfo, 'show_chart')) $this->testInfo->show_chart = 'teacher';
	}

	function setSetting($name, $value) {
		$this->settings->$name = $value;
	}

	function getSetting($name) {
		if (property_exists($this->settings, $name)) return $this->settings->$name; else return false;
	}

	//--- managemente functions --------------------------------------------------

	function _openDataSet() {
		$this->dataSet = new pData;
	}

	function _openChart() {
		$this->chart = new pChart($this->settings->width,	$this->settings->height);
	}

	function _setChartFont($font, $size) {
		$this->chart->setFontProperties($this->fontPath.$font.".ttf", $size);
	}

	function _setChartScale($min, $max, $div_size = 10) {
		$diff = $max - $min;

		$treshold = (int)$div_size;
		$pass = false;
		$count = 0;
		do {
			$divisions = $diff/$treshold;
			if ($divisions>10) $treshold = (int)($treshold*2); else $pass = true;
			$count++; //cycles counter, in order to avoid infinite loops
		} while (!$pass || $count<1000);

		$max_val = $max;
		$min_val = $min;

		if ($diff % $treshold != 0) {
			if ($max != 0) $max_val = (int)(ceil($max / $treshold))*(int)$treshold;
			if ($min != 0) $min_val = (int)(floor($min / $treshold))*(int)$treshold;
		}
		
		$divisions = (int)(($max_val - $min_val)/$treshold);
		$this->chart->setFixedScale($min_val, $max_val, $divisions);
	}

	function _setChartBackground() {
		$this->_setChartFont("tahoma", 8);
		
		$this->chart->drawRoundedRectangle(
			0,
			0,
			$this->settings->width,
			$this->settings->height,
			$this->settings->roundRadius,
			$this->settings->bgColor[_RED],
			$this->settings->bgColor[_GREEN],
			$this->settings->bgColor[_BLUE]
		);

		$this->chart->drawFilledRoundedRectangle(
			$this->settings->roundThreshold,
			$this->settings->roundThreshold,
			$this->settings->width - $this->settings->roundThreshold*2,
			$this->settings->height - $this->settings->roundThreshold*2,
			$this->settings->roundRadius,
			$this->settings->bgColor[_RED],
			$this->settings->bgColor[_GREEN],
			$this->settings->bgColor[_BLUE]
		);

	}

	function _setGraphArea($drawScale = true, $scaleType = SCALE_NORMAL) {
		$this->_setChartFont("tahoma", 8);

		if (!$drawScale) {
			$this->chart->drawFilledRectangle(
				$this->settings->horizontalDistance,
				$this->settings->verticalDistance,
				$this->settings->width - $this->settings->horizontalDistance - $this->settings->legendWidth,
				$this->settings->height - $this->settings->verticalDistance,
				$this->settings->graphColor[_RED],
				$this->settings->graphColor[_GREEN],
				$this->settings->graphColor[_BLUE]
			);
		}

		$this->chart->setGraphArea(
			$this->settings->horizontalDistance,
			$this->settings->verticalDistance,
			$this->settings->width - $this->settings->horizontalDistance - $this->settings->legendWidth,
			$this->settings->height - $this->settings->verticalDistance
		);

		if ($drawScale) {

			$this->chart->drawGraphArea(
				$this->settings->graphColor[_RED],
				$this->settings->graphColor[_GREEN],
				$this->settings->graphColor[_BLUE],
				true
			);

			$this->chart->drawScale(
				$this->dataSet->GetData(),
				$this->dataSet->GetDataDescription(),
				$scaleType,
				$this->settings->scaleColor[_RED],
				$this->settings->scaleColor[_GREEN],
				$this->settings->scaleColor[_BLUE],
				true,
				0,
				2,
				true
			);

			$this->chart->drawGrid(
				$this->settings->lineWidth,
				true,
				$this->settings->lineColor[_RED],
				$this->settings->lineColor[_GREEN],
				$this->settings->lineColor[_BLUE],
				50
			);
		}
	}

	function _setChartTreshold() {
		$this->_setChartFont("tahoma", 6);
		$this->chart->drawTreshold(
			0,
			$this->settings->tresholdColor[_RED],
			$this->settings->tresholdColor[_GREEN],
			$this->settings->tresholdColor[_BLUE],
			true,
			true
		);
	}

	function _setChartLegend() {
		$this->_setChartFont("tahoma", 8);
		$this->chart->drawLegend(
			$this->settings->width - $this->settings->legendWidth - (int)($this->settings->horizontalDistance/2),
			$this->settings->verticalDistance,
			$this->dataSet->GetDataDescription(),
			$this->settings->legendColor[_RED],
			$this->settings->legendColor[_GREEN],
			$this->settings->legendColor[_BLUE]
		);
	}

	function _setChartTitle() {
		$this->_setChartFont("tahoma", 10);
		$this->chart->drawTitle(
			50,
			22,
			$this->_getTestName(),
			$this->settings->titleColor[_RED],
			$this->settings->titleColor[_GREEN],
			$this->settings->titleColor[_BLUE],
			585
		);
	}

	//--- internal functions -----------------------------------------------------

	function _getTestName() {
		$query = "SELECT title FROM ".$GLOBALS['prefix_lms']."_test WHERE idTest=".$this->idTest;
		list($name) = sql_fetch_row( sql_query( $query ) );
		return $name;
	}

	function _getTestCategories() {
		$categories = array();
		$query = "SELECT DISTINCT c.idCategory, c.name FROM ".$GLOBALS['prefix_lms']."_quest_category as c "
			." JOIN ".$GLOBALS['prefix_lms']."_testquest as q ON (q.idCategory = c.idCategory AND q.idTest=".$this->idTest.")";
		$res = sql_query($query);
		while (list($idCategory, $name) = sql_fetch_row($res)) $categories[$idCategory] = $name;
		
		return $categories;
	}
	
	
	function _getUserStats() {
		$query = "SELECT idTrack FROM ".$GLOBALS['prefix_lms']."_testtrack WHERE idUser=".$this->idUser." AND idTest=".$this->idTest;
		$res = sql_query($query);
		list($idTrack) = sql_fetch_row($res);

		$user_values = array();
		$query = "SELECT tq.idCategory, SUM(ta.score_assigned) "
			." FROM ".$GLOBALS['prefix_lms']."_testtrack_answer as ta JOIN ".$GLOBALS['prefix_lms']."_testquest as tq "
			." ON (ta.idQuest = tq.idQuest) "
			." WHERE ta.idTrack=".$idTrack." GROUP BY tq.idCategory ";
		$res = sql_query($query);
		while (list($idCategory, $score) = sql_fetch_row($res)) $user_values[$idCategory] = $score;
		
		return $user_values;
	}
	
	
	function _getAverageStats() {
		$tracks = array();
		$query = "SELECT idTrack FROM ".$GLOBALS['prefix_lms']."_testtrack WHERE idTest=".$this->idTest;
		$res = sql_query($query);
		while (list($idTrack) = sql_fetch_row($res)) $tracks[] = $idTrack;

		//TO DO: check count($tracks) ...
		$average_values = array();
		$query = "SELECT tq.idCategory, COUNT(DISTINCT ta.idTrack), SUM(ta.score_assigned) "
			." FROM ".$GLOBALS['prefix_lms']."_testtrack_answer as ta JOIN ".$GLOBALS['prefix_lms']."_testquest as tq "
			." ON (ta.idQuest = tq.idQuest) "
			." WHERE ta.idTrack IN (".implode(",", $tracks).") GROUP BY tq.idCategory ";
		$res = sql_query($query);
		while (list($idCategory, $num_tracks, $score_total) = sql_fetch_row($res)) {
			$average_values[$idCategory] = ((int)$num_tracks>0 ? $score_total/$num_tracks : 0);
		}

		return $average_values;
	}


	//--- CHARTS -----------------------------------------------------------------

	function setRadarChart() {
		if (!$this->valid) return false;

		$categories = $this->_getTestCategories();
		$user_values = $this->_getUserStats();
		$average_values = $this->_getAverageStats();
//die('<pre>'.print_r($user_values, true).print_r($average_values, true).'</pre>');
		$this->_openDataSet();
		$cat_names = array();
		$avg_values = array();
		foreach ($user_values as $id_cat=>$value) {
			$cat_names[] = $categories[$id_cat];
			$avg_values[] = $average_values[$id_cat]; //ensure proper order to average values serie
		}
		$this->dataSet->AddPoint($cat_names, "categories");
		$this->dataSet->AddPoint(array_values($user_values), "user_serie");
		$this->dataSet->AddPoint($avg_values, "avg_serie");
		$this->dataSet->AddSerie("user_serie");
		$this->dataSet->AddSerie("avg_serie");
		$this->dataSet->SetAbsciseLabelSerie("categories");

		$this->dataSet->SetSerieName($this->lang->def('_USER_LEGEND_RADARCHART'),"user_serie");
		$this->dataSet->SetSerieName($this->lang->def('_AVERAGE_LEGEND_RADARCHART'),"avg_serie");

		// Initialise the graph
		$this->_openChart();
		$this->_setChartBackground();
		$this->_setGraphArea(false);

		//$Test->drawFilledRoundedRectangle(30,30,370,370,5,255,255,255);
		//$Test->drawRoundedRectangle(30,30,370,370,5,220,220,220);

		// Draw the radar graph
		$this->chart->drawRadarAxis(
			$this->dataSet->GetData(),
			$this->dataSet->GetDataDescription(),
			false,
			20,
			120,
			120,
			120,
			230,
			230,
			230
		);
		$this->chart->drawFilledRadar(
			$this->dataSet->GetData(),
			$this->dataSet->GetDataDescription(),
			50,
			20
		);

		// Finish the graph
		$this->_setChartLegend();
		$this->_setChartTitle();

		//set table
		$cont_h = array('', $this->lang->def('_USER_TESTSCORE'), $this->lang->def('_AVERAGE_TESTSCORE'));
		$type_h = array('', 'align_center', 'align_center');
		$this->table->addHead($cont_h, $type_h);
		foreach ($user_values as $idCategory=>$value) {
			$line = array();
			$line[] = $categories[$idCategory];
			$line[] = $value;
			$line[] = (isset($average_values[$idCategory]) ? $average_values[$idCategory] : 0);
			$this->table->addBody($line);
		}
	}


	//approccio negoziale, con le colonne in teoria orizzontali, ma in pratica saranno verticali - sia singolo utente che media
	function setColumnChart() {
		if (!$this->valid) return false;

		$categories = $this->_getTestCategories();
		$user_values = $this->_getUserStats();
		$average_values = $this->_getAverageStats();
		$max_value = 0;
		$min_value = 0;

		// Dataset definition
		$this->_openDataSet();
		foreach ($user_values as $idCategory=>$value) {
			$this->dataSet->AddPoint(array($value), "serie_".$idCategory);
			if ($value>$max_value) $max_value = $value;
			if ($value<$min_value) $min_value = $value;
		}
		$this->dataSet->AddAllSeries();
		$this->dataSet->SetAbsciseLabelSerie();
		foreach ($user_values as $idCategory=>$value) $this->dataSet->SetSerieName($categories[$idCategory], "serie_".$idCategory);
		
		// Initialise the graph
		$this->_openChart();

		//set chart scale
		$this->_setChartScale($min_value, $max_value);

		$this->_setChartBackground();
		$this->_setGraphArea();

		// Draw the 0 line
		$this->_setChartTreshold();

		// Draw the bar graph
		$this->chart->drawBarGraph(
			$this->dataSet->GetData(),
			$this->dataSet->GetDataDescription(),
			true
		);

		// Finish the graph
		$this->_setChartLegend();
		$this->_setChartTitle();

		//set table
		$cont_h = array('', $this->lang->def('_USER_TESTSCORE'), $this->lang->def('_AVERAGE_TESTSCORE'));
		$type_h = array('', 'align_center', 'align_center');
		$this->table->addHead($cont_h, $type_h);
		foreach ($user_values as $idCategory=>$value) {
			$line = array();
			$line[] = $categories[$idCategory];
			$line[] = $value;
			$line[] = (isset($average_values[$idCategory]) ? $average_values[$idCategory] : 0);
			$this->table->addBody($line);
		}
	}


	function setBarChart() {
		if (!$this->valid) return false;

		$categories = $this->_getTestCategories();
		$user_values = $this->_getUserStats();

		//[{"name":"Direttivo","ids":[14,15]},{"name":"Persuasivo","ids":[16,17]},{"name":"Partecipativo","ids":[18,19]},{"name":"Delegante","ids":[20,21]}]
		if (property_exists($this->testInfo, 'couples')) $couples = $this->testInfo->couples; else return false;

		$cat_names = array();
		$values_1 = array();
		$values_2 = array();
		$type_h = array('');
		$cont_h = array('');
		foreach ($couples as $obj) {
			$cat_names[] = $obj->name;
			$values_1[] = $user_values[$obj->ids[1]] + $user_values[$obj->ids[0]];
			$values_2[] = $user_values[$obj->ids[1]] - $user_values[$obj->ids[0]];
			$type_h[] = "align_center";
			$cont_h[] = $obj->name;
		}

		$max_value = 0;
		$min_value = 0;
		foreach ($values_1 as $value) {
			if ($value>$max_value) $max_value = $value;
			if ($value<$min_value) $min_value = $value;
		}
		foreach ($values_2 as $value) {
			if ($value>$max_value) $max_value = $value;
			if ($value<$min_value) $min_value = $value;
		}
		//echo('<pre>'.print_r($user_values, true).'</pre>');

		$this->_openDataSet();
		$this->dataSet->AddPoint($values_1, "serie_1");
		$this->dataSet->AddPoint($values_2, "serie_2");
		$this->dataSet->AddAllSeries();
		$this->dataSet->SetSerieName($this->lang->def('_FREQUENCY'), "serie_1");
		$this->dataSet->SetSerieName($this->lang->def('_EFFICACY'), "serie_2");
		$this->dataSet->AddPoint($cat_names, "labels");
		$this->dataSet->SetAbsciseLabelSerie("labels");

		$this->_openChart();
		$this->_setChartScale($min_value, $max_value);
		$this->_setChartBackground();
		$this->_setGraphArea();
		$this->_setChartTreshold();

		$this->chart->drawBarGraph(
			$this->dataSet->GetData(),
			$this->dataSet->GetDataDescription(),
			true
		);
		
		$this->_setChartLegend();
		$this->_setChartTitle();

		//set table
		$this->table->addHead($cont_h, $type_h);
		//frequency values
		$frequency = array($this->lang->def('_FREQUENCY'));
		foreach ($values_1 as $value) $frequency[] = $value;
		$this->table->addBody($frequency);
		//efficacy values
		$efficacy = array($this->lang->def('_EFFICACY'));
		foreach ($values_2 as $value) $efficacy[] = $value;
		$this->table->addBody($efficacy);
	}




	function setStackedChart() {
		if (!$this->valid) return false;

		$categories = $this->_getTestCategories();
		$user_values = $this->_getUserStats();
		$max_value = 0;
		$min_value = 0;
		$average_values = $this->_getAverageStats();

		$this->_openDataSet();
		foreach ($user_values as $idCategory=>$value) {
			$this->dataSet->AddPoint(array($value), "serie_".$idCategory);
			if ($value>0) $max_value += $value;
			if ($value<0) $min_value -= $value;
		}
		$this->dataSet->AddAllSeries();
		$this->dataSet->SetAbsciseLabelSerie();
		foreach ($user_values as $idCategory=>$value) $this->dataSet->SetSerieName($categories[$idCategory], "serie_".$idCategory);

		$this->_openChart();
		$this->_setChartScale($min_value, $max_value);		
		$this->_setChartBackground();
		$this->_setGraphArea(true, SCALE_ADDALL);

		$this->_setChartTreshold();

		$this->chart->drawStackedBarGraph(
			$this->dataSet->GetData(),
			$this->dataSet->GetDataDescription(),
			true
		);

		$this->_setChartLegend();
		$this->_setChartTitle();

		//set table
		$cont_h = array('', $this->lang->def('_USER_TESTSCORE'), $this->lang->def('_AVERAGE_TESTSCORE'));
		$type_h = array('', 'align_center', 'align_center');
		$this->table->addHead($cont_h, $type_h);
		foreach ($user_values as $idCategory=>$value) {
			$line = array();
			$line[] = $categories[$idCategory];
			$line[] = $value;
			$line[] = (isset($average_values[$idCategory]) ? $average_values[$idCategory] : 0);
			$this->table->addBody($line);
		}
	}

	
	//--- draw table -------------------------------------------------------------

	function renderTable() {
		return $this->table->getTable();
	}


	//--- general rendering function ---------------------------------------------

	function render($type, $print = false) {
		if (!$this->valid) return false;
		
		switch ($type) {
			case _STACKED_CHART : { $this->setStackedChart(); } break;
			case _BAR_CHART     : { $this->setBarChart(); } break;
			case _RADAR_CHART   : { $this->setRadarChart(); } break;
			case _COLUMN_CHART  : { $this->setColumnChart(); } break;
			default: return false;
		}

		$filename = $this->imagePath.'_chart_'.$type.'_'.$this->idTest.'_'.$this->idUser.'.png';
		$this->chart->Render($filename);

		if ($print) {
			cout('<div class="align_center">', 'content');
			cout('<img src="'.$filename.'" alt="'.$this->lang->def('_UNABLE_TO_DISPLAY_CHART').'" />', 'content');
			cout('<br /><br /><div>'.$this->renderTable().'</div>', 'content');
			cout('</div>', 'content');
		} else {
			if ($res)
				return $filename;
			else
				return false;
		}
	}

}

?>