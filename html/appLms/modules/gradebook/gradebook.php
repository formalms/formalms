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

if(Docebo::user()->isAnonymous()) die("You can't access");

function showgrade() {
	checkPerm('view');

	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang 	=& DoceboLanguage::createInstance('gradebook', 'lms');
	$out 	=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$test_man 		= new GroupTestManagement();
	$report_man 	= new CourseReportManager();

	// XXX: update if needed
	$org_tests 		=& $report_man->getTest();
	$tests_info		=& $test_man->getTestInfo($org_tests);

	$i_test = array();
	$i_test_report_id = array();

	// XXX: Info for updates
	$query_tot_report = "
	SELECT COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."'";
	list($tot_report) = sql_fetch_row(sql_query($query_tot_report));

	$query_tests = "
	SELECT id_report, id_source
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."' AND source_of = 'test'";
	$re_tests = sql_query($query_tests);
	while(list($id_r, $id_t) = sql_fetch_row($re_tests)) {

		$i_test[$id_t] = $id_t;
		$i_test_report_id[$id_r] = $id_r;
	}

	// XXX: Update if needed
	if($tot_report == 0) {

		$report_man->initializeCourseReport($org_tests);
	} else {
		if(is_array($i_test)) $test_to_add = array_diff($org_tests, $i_test);
		else $test_to_add = $org_tests;
		if(is_array($i_test)) $test_to_del = array_diff($i_test, $org_tests);
		else $test_to_del = $org_tests;
		if(!empty($test_to_add) || !empty($test_to_del)) {


			$report_man->addTestToReport($test_to_add, 1);
			$report_man->delTestToReport($test_to_del);

			$included_test = $org_tests;
		}
	}
	$report_man->updateTestReport($org_tests);

	$reports 	= array();
	$id_test 	= array();
	$id_report 	= array();

	// XXX: retrive all report info
	$query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."' AND show_to_user = 'true'
	ORDER BY sequence ";
	$re_report = sql_query($query_report);

	while($info_report = sql_fetch_assoc($re_report)) {

		$reports[$info_report['id_report']]	= $info_report;

		switch($info_report['source_of']) {
			case "test" : {
				$id_test[] = $info_report['id_source'];
			};break;
			case "activity" :
			case "final_vote" : {
				$id_report[] = $info_report['id_report'];
			};break;
		}
	}

	// XXX: retrive report and test score
	$report_score 	=& $report_man->getReportsScores($id_report, getLogUserId());
	$tests_score 	=& $test_man->getTestsScores($id_test, array(getLogUserId()));

	// XXX: create table
	$table = new Table(0, $lang->def('_GRADEBOOK_CAPTION'), $lang->def('_GRADEBOOK_SUMMARY'));
    
    //** CR : LR. TABLE RESPONSE **
    $table->setTableId("table_pagella");
        
        $cont_h = array(
        $lang->def('_TITLE'),
        $lang->def('_SCORE_LAST_TEST'),
        $lang->def('_REQUIRED_SCORE'),
        $lang->def('_DATE_LAST_TEST'),
        $lang->def('_COMMENTS'),
        $lang->def('_SHOW_RESULTS')
    );
    
     $info_pagella .='<style>
                            @media
                            only screen and (max-width: 870px),
                            (min-device-width: 870px) and (max-device-width: 1024px)  {            
     
                                        #table_pagella td:nth-of-type(1):before { content: "'.$cont_h[0].'"; }
                                        #table_pagella td:nth-of-type(2):before { content: "'.$cont_h[1].'"; }
                                        #table_pagella td:nth-of-type(3):before { content: "'.$cont_h[2].'"; }
                                        #table_pagella td:nth-of-type(4):before { content: "'.$cont_h[3].'"; }
                                        #table_pagella td:nth-of-type(5):before { content: "'.$cont_h[4].'"; }
                                        #table_pagella td:nth-of-type(6):before { content: "'.$cont_h[5].'"; }
                                        }        
                                        </style>
                                    ';   

      $out->add($info_pagella, 'content');  

    //*************************************
    
	$type_h = array('', 'align_center', 'align_center', '', '');


	$table->setColsStyle($type_h);
	$table->addHead($cont_h);

	$id_user = getLogUserId();

	// XXX: construct table data
	if(!empty($reports))
	while(list($id_report, $report_info) = each($reports)) {

		$id_source = $report_info['id_source'];
		$title = strip_tags($report_info['title']);
		$score = '';
		$required = $report_info['required_score'];
		$maxscore = $report_info['max_score'];
		$date = '';
		$comment = '';

		switch($report_info['source_of']) {
			case "scorm_item" : {
					$query_report = "
						SELECT *
						FROM ".$GLOBALS['prefix_lms']."_scorm_tracking
						WHERE idscorm_item = '".$report_info['id_source']."' and idUser='".$id_user."'";

						$query2 = sql_query($query_report);
						$query = sql_fetch_assoc($query2);
						$score = $query['score_raw'];
						$date = Format::date($query['last_completed'], 'datetime');
						$comment = "";
			};break;
			case "test" : {

				$title = $tests_info[$id_source]['title'];
				if(isset($tests_score[$id_source][$id_user])) {

					switch($tests_score[$id_source][$id_user]['score_status']) {
						case "not_checked" 	: {

							$score = '<span class="cr_not_check">'.$lang->def('_NOT_CHECKED').'</span>';
						};break;
						case "passed" 		: {

							//$score = '<span class="cr_passed">'.$lang->def('_PASSED').'</span>';
							$score = '<img src="'.getPathImage('fw').'emoticons/thumbs_up.gif" alt="'.$lang->def('_PASSED').'" />&nbsp;'.$tests_score[$id_source][$id_user]['score'];
							$date = Format::date($tests_score[$id_source][$id_user]['date_attempt']);
							$comment = $tests_score[$id_source][$id_user]['comment'];
						};break;
						case "not_passed" 	: {

							//$score = '<span class="cr_not_passed">'.$lang->def('_NOT_PASSED').'</span>';
							$score = '<img src="'.getPathImage('fw').'emoticons/thumbs_down.gif" alt="'.$lang->def('_NOT_PASSED').'" />&nbsp;'.$tests_score[$id_source][$id_user]['score'];
							$date = Format::date($tests_score[$id_source][$id_user]['date_attempt']);
							$comment = $tests_score[$id_source][$id_user]['comment'];
						};break;
						case "valid" 		: {

							$score = $tests_score[$id_source][$id_user]['score'];
							if($score == $report_info['max_score']) $score = '<span class="cr_max_score">'.$score.'</span>';
							elseif($score < $report_info['required_score']) $score = '<span class="cr_not_passed">'.$score.'</span>';
							$date = Format::date($tests_score[$id_source][$id_user]['date_attempt']);
							$comment = $tests_score[$id_source][$id_user]['comment'];
						};break;
					}
				}
				$link_result = '<a href="index.php?modname=organization&op=test_track&id_user='.$id_user.'&id_org='.$id_source.'&back=gradebook">'.'<img src="'.getPathImage().'standard/report.png" /></a>';
			};break;
			case "activity" : {

				if(isset($report_score[$id_report][$id_user]) && $report_score[$id_report][$id_user]['score_status'] == 'valid') {

					$score = $report_score[$id_report][$id_user]['score'];
					if($score == $report_info['max_score']) $score = '<span class="cr_max_score">'.$score.'</span>';
					elseif($score < $report_info['required_score']) $score = '<span class="cr_not_passed">'.$score.'</span>';

					$date = Format::date($report_score[$id_report][$id_user]['date_attempt']);
					$comment = $report_score[$id_report][$id_user]['comment'];
				}
			};break;
			case "final_vote" : {

				$title = strip_tags($lang->def('_FINAL_SCORE'));
				if(isset($report_score[$id_report][$id_user]) && $report_score[$id_report][$id_user]['score_status'] == 'valid') {

					$score = $report_score[$id_report][$id_user]['score'];
					if($score == $report_info['max_score']) $score = '<span class="cr_max_score">'.$score.'</span>';
					elseif($score < $report_info['required_score']) $score = '<span class="cr_not_passed">'.$score.'</span>';

					$date = Format::date($report_score[$id_report][$id_user]['date_attempt']);
					$comment = $report_score[$id_report][$id_user]['comment'];                
				}
			};break;
		}
        
        
        if($date == "") $date = "-";
        if($comment == "") $comment = "-";
        
		$table->addBody(array(
			$title,
			( $score == '' ? $lang->def('_NOT_ASSIGNED') : $score.' '.$lang->def('_MAX_DIVISOR').' '.$maxscore ),
			($report_info['source_of'] === 'scorm_item' ? "-" : $required),
			$date,
			$comment, 
			$link_result."&nbsp"));
	}
	$out->add(
		getTitleArea($lang->def('_GRADEBOOK_AREATITLE'), 'gradebook')
		.'<div class="std_block">'
		//.'<p><a href="index.php?modname=gradebook&op=coursereport">'.$lang->def('_GRADEBOOK_COURSEREPORT').'<a/></p>'
		.$table->getTable()
		.'</div>');
}

function coursereport() {
	global $nquest;
	global $course_score,$course_score_max;
	global $test_title;

	checkPerm('view');

	require_once($GLOBALS['where_lms'].'/lib/lib.test.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang 	=& DoceboLanguage::createInstance('gradebook', 'lms');
	$out 	=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$test_man 		= new GroupTestManagement();
	$report_man 	= new CourseReportManager();

	// XXX: update if needed
	$org_tests 		=& $report_man->getTest();
	$tests_info		=& $test_man->getTestInfo($org_tests);

	$i_test = array();
	$i_test_report_id = array();

	// XXX: Info for updates
	$query_tot_report = "
	SELECT COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."'";
	list($tot_report) = sql_fetch_row(sql_query($query_tot_report));

	$query_tests = "
	SELECT id_report, id_source
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."' AND source_of = 'test'";
	$re_tests = sql_query($query_tests);
	while(list($id_r, $id_t) = sql_fetch_row($re_tests)) {

		$i_test[$id_t] = $id_t;
		$i_test_report_id[$id_r] = $id_r;
	}

	// XXX: Update if needed
	if($tot_report == 0) {

		$report_man->initializeCourseReport($org_tests);
	} else {
		if(is_array($i_test)) $test_to_add = array_diff($org_tests, $i_test);
		else $test_to_add = $org_tests;
		if(is_array($i_test)) $test_to_del = array_diff($i_test, $org_tests);
		else $test_to_del = $org_tests;
		if(!empty($test_to_add) || !empty($test_to_del)) {


			$report_man->addTestToReport($test_to_add, 1);
			$report_man->delTestToReport($test_to_del);

			$included_test = $org_tests;
		}
	}
	$report_man->updateTestReport($org_tests);

	$reports 	= array();
	$id_test 	= array();
	$id_report 	= array();
	$tests=array();

	// XXX: retrive all report info
	$query_report = "
	SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
	FROM ".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$_SESSION['idCourse']."' AND show_to_user = 'true'
	ORDER BY sequence ";
	$re_report = sql_query($query_report);

	while($info_report = sql_fetch_assoc($re_report)) {

		switch($info_report['source_of']) {
			case "test" : {
				$id_test[] = $info_report['id_source'];
			};break;
		}
	}


	$id_user = getLogUserId();
	if (count($id_test)) {
		$title=$GLOBALS['course_descriptor']->getValue('name');
		$username=Docebo::user()->getUserName();

		$GLOBALS['page']->add(
			getTitleArea($lang->def('_GRADEBOOK_AREATITLE'), 'gradebook')
			.'<div class="std_block">'

			.'<div class="print">'
			.'<a href="#" onclick="window.print(); return false;">'
			.'<img src="'.getPathImage().'standard/print.gif" alt="'.$lang->def('_PRINT').'" /> '
			.$lang->def('_PRINT').'</a>'
			.'</div>'

			.getBackUi('index.php?modname=gradebook&amp;op=showgrade', $lang->def('_BACK'))
			.'<div class="title coursereport-title">'.$username.'</div><div class="title coursereport-title">'.$title.'</div>', 'content');

		$GLOBALS['page']->add('<div class="coursereport-div"><table class="coursereport-table">'
		.'<tr><td><strong>'.$lang->def('_TEST_N').'</strong></td><td><strong>'.$lang->def('_QUESTION').'</strong></td><td align="right"><strong>'.$lang->def('_SCORE').'</strong></td></tr>', 'content');
		$nquest=0;
		$course_score=0;
		$course_score_max=0;
		$j=0;
		for ($i=0;$i<count($id_test);$i++) {
			$test_title=$tests_info[$id_test[$i]]['title'];
			$GLOBALS['page']->add('<tr><td colspan="3"><br /><strong>'.$test_title.'</strong></td></tr>', 'content');

			$query_track = "SELECT idTrack FROM ".$GLOBALS['prefix_lms']."_testtrack "
							."WHERE idTest =".$id_test[$i]." AND idUser=".$id_user;
			$re_track = sql_query($query_track);
			$track = sql_fetch_assoc($re_track);
			$score=user_test_report($id_user, $id_test[$i],$track['idTrack']);
			if ($track) {
				$tests[$j]['title']=$test_title;
				$tests[$j]['score']=$score;
				$j++;
			}
		}
		$perc_course_score = round(round($course_score / $course_score_max, 2) * 100, 2);
		$GLOBALS['page']->add('<tr><td colspan="3" align="right"><strong>'.$lang->def('_TOTAL').':&nbsp;'.$course_score.' '.$lang->def('_ON').' '.$course_score_max.' ('.$perc_course_score.'%)</strong> </td></tr>', 'content');

		$GLOBALS['page']->add('</table>', 'content');
		/*
		if ($perc_course_score<75) {
			$msg_feedback=$lang->def('_COURSE_NOT_OK').' '.$lang->def('_COURSE_CHECK_GRAPH');
		} else {
			$msg_feedback=$lang->def('_COURSE_OK');
		}

		$GLOBALS['page']->add('<p><strong>'.$msg_feedback.'</strong>', 'content');
		*/
		$GLOBALS['page']->add('</div>', 'content');

		draw_bar($tests);

		$GLOBALS['page']->add('</div>', 'content');
	}


}

function user_test_report($idUser, $idTest, $id_track) {
	global $nquest;
	global $course_score,$course_score_max;
	global $test_title;

	if(!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) die("You can't access");
	$lang 	=& DoceboLanguage::createInstance('gradebook', 'lms');
	$idTrack = $id_track;

	//test info---------------------------------------------------------
	list( $title, $mod_doanswer, $point_type, $point_required, $question_random_number,
		$show_score, $show_score_cat, $show_doanswer,
		$show_solution) = sql_fetch_row( sql_query("
	SELECT  title, mod_doanswer, point_type, point_required, question_random_number,
			show_score, show_score_cat, show_doanswer,
			show_solution
	FROM ".$GLOBALS['prefix_lms']."_test
	WHERE idTest = '".(int)$idTest."'"));

	list($score, $bonus_score, $date_attempt, $date_attempt_mod) = sql_fetch_row( sql_query("
	SELECT score, bonus_score, date_attempt, date_attempt_mod
	FROM ".$GLOBALS['prefix_lms']."_testtrack
	WHERE idTrack = '".(int)$idTrack."'"));

	$point_do 				= $bonus_score;
	$max_score 				= 0;
	$num_manual 			= 0;
	$manual_score 			= 0;
	$quest_sequence_number 	= 1;
	$report_test			= '';
	$point_do_cat 			= array();

	if ($idTrack) {
	if($question_random_number != 0) {
		$re_visu_quest = sql_query("SELECT idQuest
		FROM ".$GLOBALS['prefix_lms']."_testtrack_quest
		WHERE idTrack = '".(int)$idTrack."' ");

		while(list($id_q) = sql_fetch_row($re_visu_quest)) $quest_see[] = $id_q;

		$query_question = "
		SELECT q.idQuest, q.title_quest, q.type_quest, t.type_file, t.type_class, q.idCategory
		FROM ".$GLOBALS['prefix_lms']."_testquest AS q JOIN ".$GLOBALS['prefix_lms']."_quest_type AS t
		WHERE q.idTest = '".$idTest."' AND q.type_quest = t.type_quest AND  q.idQuest IN (".implode($quest_see, ',').")
		ORDER BY q.sequence";
	} else {
		$query_question = "
		SELECT q.idQuest, q.title_quest, q.type_quest, t.type_file, t.type_class, q.idCategory
		FROM ".$GLOBALS['prefix_lms']."_testquest AS q JOIN ".$GLOBALS['prefix_lms']."_quest_type AS t
		WHERE q.idTest = '".$idTest."' AND q.type_quest = t.type_quest
		ORDER BY q.sequence";
	}
	$reQuest = sql_query($query_question);
	while(list($id_quest, $title_quest, $type_quest, $type_file, $type_class, $id_cat) = sql_fetch_row($reQuest)) {

		require_once(Docebo::inc(_folder_lms_.'/modules/question/'.$type_file));

		$quest_point_do = 0;

		$quest_obj = eval("return new $type_class( $id_quest );");
		$quest_point_do = $quest_obj->userScore($idTrack);
		$quest_max_score = $quest_obj->getMaxScore();
		if(($type_quest != 'title') && ($type_quest != 'break_page')) {
			$review = $quest_obj->displayUserResult( 	$idTrack,
														( $type_quest != 'title' ? $quest_sequence_number++ : $quest_sequence_number ),
														$show_solution );
			$nquest++;
			$report_test.="<tr><td>".$nquest."</td><td>".strip_tags($title_quest)."</td><td align=\"right\">".$review['score']."</td></tr>";

		}

		if($quest_obj->getScoreSetType() == 'manual') {
			++$num_manual;
			$manual_score = round($manual_score + $quest_max_score, 2);
		}

		$point_do = round($point_do + $quest_point_do, 2);
		$max_score = round($max_score + $quest_max_score, 2);

		$course_score+=$point_do;
		$course_score_max+=$max_score;

		if(isset($point_do_cat[$id_cat])) {
			$point_do_cat[$id_cat] = round($point_do + $point_do_cat[$id_cat], 2);
		}
		else {
			$point_do_cat[$id_cat] = $point_do;
		}

		$perc_score = round(round($point_do / $max_score, 2) * 100, 2);

	}
	}

	if ($idTrack) {
		$GLOBALS['page']->add($report_test, 'content');
		$GLOBALS['page']->add('<tr><td colspan="3" align="right"><strong>'.$test_title.' - '.$lang->def('_TOTAL').':&nbsp;'.$point_do.' su '.$max_score.' ('.$perc_score.'%)</strong> </td></tr>', 'content');

	} else {
		$GLOBALS['page']->add('<tr><td colspan="3" align="right"><strong>'.$lang->def('_TEST_NOT_PLAYED').'</strong></td></tr>', 'content');
	}

	return $perc_score;
}

function draw_bar($data) {

	$barcolors=array("#FF0000","#0FF70A","#FF7109","#0010E1","#00D5A0","#F2FB00","#FB00E0","#AB00FB");

	$GLOBALS['page']->add('<div class="coursereport-graph">', 'content');

	$bar=getPathImage('lms')."graph/chart_blank.png";

	for ($i=0;$i<count($data);$i++) {
		$test_title=$data[$i]['title'];
		$perc_done=$data[$i]['score'];
		$perc_todo=100-$perc_done;

		$j=$i % 8;

		$bar_url=getPathImage('lms')."graph/chart".$j.".png";
		$size=639-($perc_done*639)/100;
		$position=($perc_done*639)/100-20;
		$GLOBALS['page']->add('<p class="coursereport-bartitle">'.$test_title.': '.$perc_done.'%</p><div class="coursereport-bar"><img src="'.$bar.'" alt="'.$perc_done.'%" style="background: white url('.$bar_url.') top left no-repeat;background-position: -'.$size.'px 0pt;margin:0;padding:0;" /><div class="coursereport-position" style="top:-50px;left:'.$position.'px;"></div></div>', 'content');


	}

	$GLOBALS['page']->add('</div>', 'content');
}

function gradebookDispatch($op) {

	//if(isset($_POST['showgrade'])) $op = 'coursereport';

	switch($op) {
		case "showgrade" : {
			showgrade();
		};break;

		case "coursereport": {
			coursereport();
		}; break;
	}
}

?>