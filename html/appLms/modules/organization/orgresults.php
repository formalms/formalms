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

function decodeSessionTime($stime) {
	$output = $stime;
	if (strpos($stime, 'P')!==false) {
		$re1 = preg_match ('/^P((\d*)Y)?((\d*)M)?((\d*)D)?(T((\d*)H)?((\d*)M)?((\d*)(\.(\d{1,2}))?S)?)?$/', $stime, $t1_s );
		if(!isset($t1_s[15]) || $t1_s[15] == '') $t1_s[15] = '00';
		if(!isset($t1_s[13]) || $t1_s[13] == '') $t1_s[13] = '00';
		if(!isset($t1_s[11]) || $t1_s[11] == '') $t1_s[11] = '00';
		if(!isset($t1_s[9]) || $t1_s[9] == '') $t1_s[9] = '0000';
		$output = ($t1_s[9]=='0000' || $t1_s[9] == '' ? '' : $t1_s[9].':')
			.sprintf("%'02s:%'02s.%'02s",  $t1_s[11], $t1_s[13], $t1_s[15]);
	}
	return $output;
}

function getCompilationTable($id_user, $id_test)
{
    require_once(_base_.'/lib/lib.table.php');
    require_once($GLOBALS['where_lms'].'/lib/lib.param.php' );
    require_once($GLOBALS['where_lms'].'/class.module/track.test.php');
    require_once($GLOBALS['where_lms'].'/lib/lib.test.php' );

    	$test_man       = new TestManagement($id_test);


    if(isset($_GET['back']) && $_GET['back'])
        $back = getBackUi( 'index.php?modname=course&amp;op=mycourses&amp;sop=unregistercourse' , Lang::t('_BACK', 'standard', 'framework') );
    else
        $back = getBackUi( 'index.php?modname=organization' , Lang::t('_BACK', 'standard', 'framework') );

    // Parch per link in reportcard
    if(isset($_GET['back']) && $_GET['back'] && $_GET['back'] == "gradebook" )
        $back = getBackUi( 'index.php?modname=gradebook&op=showgrade' , Lang::t('_BACK', 'standard', 'framework') );

    $query =    "SELECT *"
                ." FROM %lms_testtrack"
                ." WHERE idTest = ".(int)$id_test
                ." AND idUser = ".(int)$id_user;

    $result = sql_query($query);

    cout(   getTitleArea('')
            .'<div class="std_block">'
            .$back, 'content');

    if(sql_num_rows($result) > 0)
    {
      $track_info = sql_fetch_assoc($result);
      $play_man       = new PlayTestManagement($id_test, Docebo::user()->getIdst(), $track_info['idTrack'], $test_man);
      $test_info      = $test_man->getTestAllInfo();
      $score_status   = $play_man->getScoreStatus();

        if ($score_status == 'passed') $incomplete = FALSE;
        elseif ($score_status == 'valid') {


            if ($track_info['score'] >= $test_info['point_required'])
                $incomplete = FALSE;
            else
                $incomplete = TRUE;
        } else {
            $incomplete = TRUE;
        }
        $show_solution = false;
        if( $test_info['show_solution'] == 1 )
            $show_solution = true;
        elseif($test_info['show_solution'] == 2 && !$incomplete )
            $show_solution = true;
        cout(   '<b>'.Lang::t('_DATE', 'organization').':</b> '.Format::date($track_info['date_end_attempt'], 'datetime').'<br/>'
                .'<b>'.Lang::t('_SCORE', 'organization').':</b> '.($track_info['score'] == '' ? '0' : $track_info['score']).'<br/>', 'content');

        $query =    "SELECT date_attempt, score"
                    ." FROM %lms_testtrack_times"
                    ." WHERE idTrack = ".(int)$track_info['idTrack'];

        $result = sql_query($query);

        if(sql_num_rows($result) > 1)
        {
            cout('<div id="hystoric">', 'content');

            $tb = new Table(0, Lang::t('_HYSTORIC_TABLE', 'organization'), Lang::t('_HYSTORIC_TABLE', 'organization'));

            $tb_h = array(Lang::t('_DATE', 'organization'), Lang::t('_SCORE', 'organization'));
            $tb_s = array('align-center', 'align-center');

            $tb->setColsStyle($tb_s);
            $tb->addHead($tb_h);

            while($row = sql_fetch_assoc($result))
                $tb->addBody(array(Format::date($row['date_attempt'], 'datetime'), $row['score']));

            cout(   $tb->getTable()
                    .'</div>', 'content');
        }
        $query_passed = "SELECT status FROM %lms_commontrack WHERE idTrack = ".(int)$track_info['idTrack']." AND objectType = 'test' AND idUser = ".(int)$id_user;
        $result_passed = sql_query($query_passed);
        $test_passed = false;
        if(sql_num_rows($result_passed)>0)
            {
                list($test_status) = sql_fetch_row($result_passed);
                if ($test_status == "passed")
                    $test_passed = true;
            }
        if($test_info['show_doanswer'] == 1 || ($test_info['show_doanswer'] == 2 && $test_passed ))
        {
            $re_visu_quest = sql_query("SELECT idQuest
            FROM %lms_testtrack_quest
            WHERE idTrack = '".(int)$track_info['idTrack']."' ");

            $quest_see = array();
            while(list($id_q) = sql_fetch_row($re_visu_quest))
                $quest_see[] = $id_q;

            $query_question = "
            SELECT q.idQuest, q.type_quest, t.type_file, t.type_class
            FROM %lms_testquest AS q JOIN %lms_quest_type AS t
            WHERE q.idTest = '".$id_test."' AND q.type_quest = t.type_quest AND q.idQuest IN (".implode($quest_see, ',').")
                 AND q.type_quest <> 'break_page' AND q.type_quest <> 'title'
            ORDER BY q.sequence";

            $reQuest = sql_query($query_question);

            cout('<div class="test_answer_space">', 'content');

            $quest_sequence_number = 1;

            while(list($idQuest, $type_quest, $type_file, $type_class) = sql_fetch_row($reQuest))
            {
				require_once(Docebo::inc(_folder_lms_.'/modules/question/'.$type_file));
                $quest_obj = eval("return new $type_class( $idQuest );");

                $review = $quest_obj->displayUserResult(    $track_info['idTrack'],
                                                            ($type_quest != 'title' ? $quest_sequence_number++ : $quest_sequence_number),
                                                            $show_solution );

                cout(   '<div class="test_quest_review_container">'
                        .$review['quest'], 'content');

                if($review['score'] !== false)
                {
                    cout(   '<div class="test_answer_comment">'
                            .'<div class="test_score_note">'.Lang::t('_SCORE', 'test').' : ', 'content');

                    if($quest_obj->getScoreSetType() == 'manual' && !$review['manual_assigned'] )
                        cout(Lang::t('_NOT_ASSIGNED', 'test'), 'content');
                    else
                    {
                        if($review['score'] > 0)
                            cout('<span class="test_score_positive">'.$review['score'].'</span>', 'content');
                        else
                            cout('<span class="test_score_negative">'.$review['score'].'</span>', 'content');
                    }

                    cout(   '</div>'
                            .( $review['comment'] != '' ? $review['comment'] : '' )
                            .'</div>', 'content');
                }

                cout('</div>', 'content');
            }

            cout('</div>', 'content');
        }
    }
    else
        cout(Lang::t('_NO_TEST_STATS', 'organization'), 'content');

    cout(   $back
            .'</div>', 'content');
}

function getTrackingTable($id_user, $id_org) {

	require_once(_base_.'/lib/lib.table.php');
	$tb = new Table(Get::sett('visu_course'));
	
	$lang = DoceboLanguage::CreateInstance('organization', 'lms');
	
	$h_type = array('', '', 'image', 'image', '', 'nowrap', 'image', 'image nowrap');
	$h_content = array(
		$lang->def('_NAME'),
		$lang->def('_STATUS'),
		$lang->def('_SCORE'),
		$lang->def('_MAX_SCORE'),
		$lang->def('_DATE_LAST_ACCESS'),
		$lang->def('_TIME'),
		$lang->def('_ATTEMPTS'),
		''
	);

	$tb->setColsStyle($h_type);
	$tb->addHead($h_content);

	$query = "SELECT idscorm_item, status ".
		" FROM ".$GLOBALS['prefix_lms']."_scorm_items_track  ".
		" WHERE idscorm_organization=$id_org ".
		" AND idUser=$id_user ";
	$lessons_status = array();
	$res = sql_query($query);
	while (list($id, $s) = sql_fetch_row($res)) {
		$lessons_status[$id] = $s;
	}

	$qry = "SELECT t3.title, t1.lesson_status, t1.score_raw, t1.score_max, t1.session_time, ".
		" MAX(t2.date_action) as last_access, COUNT(*) as attempts, t1.idscorm_item as item, t1.idscorm_tracking as id_track ".
		" FROM ".$GLOBALS['prefix_lms']."_scorm_tracking as t1, ".
		" ".$GLOBALS['prefix_lms']."_scorm_tracking_history as t2, ".
		" ".$GLOBALS['prefix_lms']."_scorm_items as t3 ".
		" WHERE t1.idscorm_item=t3.idscorm_item AND ".
		" t2.idscorm_tracking=t1.idscorm_tracking AND t3.idscorm_organization=$id_org ".
		" AND t1.idUser=$id_user ".
		" GROUP BY t2.idscorm_tracking".
		" ORDER BY t1.idscorm_item ";

	$res = sql_query($qry);
	while ($row = sql_fetch_assoc($res)) {
		
		$line = array();
		
		
		$interactions = '<a href="index.php?modname=organization&op=scorm_interactions&amp;id_user='.$id_user.'&amp;id_org='.$id_org.'&amp;id_track='.$row['id_track'].'">'.$lang->def('_SHOW_INTERACTIONS').'</a>';
		$scorm_history = '<a href="index.php?modname=organization&op=scorm_history&amp;id_user='.$id_user.'&amp;id_org='.$id_org.'&amp;id_obj='.$row['item'].'">'.$lang->def('_HISTORY').'</a>';
		
		$line[] = $row['title'];
		//$line[] = $lessons_status[$row['item']];
		if ($lessons_status[$row['item']] === 'completed') {
			$line[] = Lang::t('_COMPLETED', 'standard');
		} else if ($lessons_status[$row['item']] == 'incomplete') {
			$line[] = Lang::t('_INCOMPLETE', 'standard');
		} else {
			$line[] = $lessons_status[$row['item']];
		}
		
		$line[] = $row['score_raw'];
		$line[] = $row['score_max'];
		$line[] = Format::date($row['last_access']);
		$line[] = decodeSessionTime($row['session_time']);
		$line[] = $row['attempts'];
		//$line[] = ($row['score_raw']!='' ? $interactions : '');
		$line[] = ( $row['attempts'] > 1 ? $scorm_history : '' ) 
			.($row['score_raw']!='' ? '<br />'.$interactions : '');
	
	
		$tb->addBody($line);
	
	}

	//title
	cout( getTitleArea( '' ), 'content' );
	cout( '<div class="std_block">', 'content' );

	//back button, back to treeview
	if(isset($_GET['back']) && $_GET['back'])
		$back = getBackUi( 'index.php?modname=course&amp;op=mycourses&amp;sop=unregistercourse' , $lang->def('_BACK', 'standard', 'framework') );
	else
		$back = getBackUi( 'index.php?modname=organization' , $lang->def('_BACK') );
	cout( $back, 'content' );
	cout( $tb->getTable(), 'content' );
	cout( $back, 'content' );
	cout( '</div>', 'content' );

} //end function


function getHistoryTable($id_user, $id_obj) {
	
	require_once(_base_.'/lib/lib.table.php');
	$tb = new Table(Get::sett('visu_course'));
	
	$id_org = Get::req('id_org', DOTY_INT, 0);
	
	$lang = DoceboLanguage::CreateInstance('organization', 'lms');
	
	$h_type = array('', '', '', '', '');
	$h_content = array(
		$lang->def('_ATTEMPT'),
		$lang->def('_STATUS'),
		$lang->def('_SCORE'),
		$lang->def('_DATE'),
		$lang->def('_TIME')
	);
	
	$tb->setColsStyle($h_type);
	$tb->addHead($h_content);
	
	$qry = "SELECT t1.* FROM ".
		$GLOBALS['prefix_lms']."_scorm_tracking_history as t1 JOIN ".
		$GLOBALS['prefix_lms']."_scorm_tracking as t2 ON (t1.idscorm_tracking=t2.idscorm_tracking) ".
		" WHERE t2.idscorm_item=$id_obj AND t2.idUser=$id_user ".
		" ORDER BY t1.date_action ASC ";
	$res = sql_query($qry); $i=1;
	while ($row = sql_fetch_assoc($res)) {
		
		$line = array();
		
		$line[] = $lang->def('_ATTEMPT').' '.$i;
		$line[] = $row['lesson_status'];
		$line[] = $row['score_raw'];
		$line[] = Format::date($row['date_action']);
		$line[] = decodeSessionTime($row['session_time']);
				
		$tb->addBody($line);
		$i++;
	}
	
	//title
	cout( getTitleArea( '' ), 'content' );
	cout( '<div class="std_block">', 'content' );

	//back button, back to treeview
	$back = getBackUi( 'index.php?modname=organization&amp;op=scorm_track&amp;id_user='.$id_user.'&amp;id_org='.$id_org , $lang->def('_BACK') );
	
	//back button, back to treeview
	cout( $back, 'content' );
	cout( $tb->getTable(), 'content' );
	cout( $back, 'content' );
	cout( '</div>', 'content' );
}



function getInteractionsTable($id_user, $idtrack) {
	
	require_once(_base_.'/lib/lib.domxml.php');
	require_once(_base_.'/lib/lib.table.php');
	$tb = new Table(Get::sett('visu_course'));
	
	$lang = DoceboLanguage::CreateInstance('organization', 'lms');
	
	$id_org = Get::req('id_org', DOTY_INT, 0);
	
	$h_type = array('', '', '');
	$h_content = array(
		$lang->def('_DESCRIPTION'),
		$lang->def('_TYPE'),
		$lang->def('_RESULT')
	);

	$tb->setColsStyle($h_type);
	$tb->addHead($h_content);

	$qry = "SELECT xmldata FROM ".$GLOBALS['prefix_lms']."_scorm_tracking WHERE idscorm_tracking=$idtrack AND idUser=$id_user";
	$res = sql_query($qry);
	$row = sql_fetch_array($res);
	
	
	$doc = new DoceboDOMDocument();
	$doc->loadXML($row['xmldata']);

	$context = new DoceboDOMXPath( $doc );
	$root = $doc->documentElement;
	
	$temp = $context->query('//interactions');
	
	$lines = array();
	for ($i=0; $i<$temp->length; $i++) {
		$arr = array();
		$node =& $temp->item($i);
		
		//interaction index
		//$arr['index'] = $node->getAttribute('index');

		//get description
		$elem = $context->query('description/text()', $node);
		$elemNode =& $elem->item(0);
		if($elemNode && isset($elemNode->textContent)) {
			$arr['description'] = $elemNode->textContent;
			
			//get type
			$elem = $context->query('type/text()', $node);
			$elemNode =& $elem->item(0);
			$arr['type'] = $elemNode->textContent;
		
			//get result
			$elem = $context->query('result/text()', $node);
			$elemNode =& $elem->item(0);
			$arr['result'] = $elemNode->textContent;
			
			//get id
			$elem = $context->query('id/text()', $node);
			$elemNode =& $elem->item(0);
			$id = $elemNode->textContent;
			
			if($arr['result'] == '1') $arr['result'] = 'true';
			else $arr['result'] = 'false';
			
			$lines[$id] = array( $arr['description'], $arr['type'], $arr['result'] );
		}
	
	}
	
	foreach ($lines as $key=>$line) {
		$tb->addBody($line);
	}
	
	//title
	cout( getTitleArea( $lang->def('_SCORM_INTERACTIONS_TABLE') ), 'content' );
	cout( '<div class="std_block">', 'content' );

	//back button, back to treeview
	$back = getBackUi( 
		'index.php?modname=organization&amp;op=scorm_track&amp;id_user='.$id_user.'&amp;id_org='.$id_org, 
		$lang->def('_BACK_TO_TRACK') );//'index.php?modname=organization&amp;op=history&amp;id_user='.$id_user.'&amp;id_org='.$org , $lang->def('_BACK_TO_TRACK') );
	
	//back button, back to treeview
	cout( $back, 'content' );
	cout( $tb->getTable(), 'content' );
	cout( $back, 'content' );
	cout( '</div>', 'content' );
}

?>