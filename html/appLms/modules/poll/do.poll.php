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

if(Docebo::user()->isAnonymous()) die('You can\'t access');

function retriveTrack($id_reference, $id_poll, $id_user) {
	
	if(isset($_POST['id_track']) || isset($_GET['id_track'])) {
		return importVar('id_track', true, 0);
	}
	
	if($id_reference !== FALSE) {
                require_once(_lms_.'/class.module/track.poll.php');
                $itemtrack = new Track_Poll(null);
		
                list( $exist, $idTrack ) = $itemtrack->getIdTrack( $id_reference, $id_user, $id_poll, TRUE );
                return $idTrack;
	} 
	return false;
}

function intro( $object_poll, $id_param ) {
	//-kb-play-// if(!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) die("You can't access");
	
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_lms'].'/class.module/track.poll.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.param.php' );
	require_once($GLOBALS['where_lms'].'/lib/lib.poll.php' );
	
	$lang 			=& DoceboLanguage::createInstance('poll');
	$id_poll 		= $object_poll->getId();
	$id_reference 	= getLoParam($id_param, 'idReference');
	$url_coded 		= urlencode(Util::serialize($object_poll->back_url));
	$id_track 		= retriveTrack($id_reference, $id_poll, getLogUserId());
	
	$poll_man 	= new PollManagement($id_poll);
	$play_man 	= new PlayPollManagement($id_poll, getLogUserId(), $id_track, $poll_man);
	$poll_info 	= $poll_man->getPollAllInfo();
	
	$page_title = array(
		Util::str_replace_once('&', '&amp;', $object_poll->back_url) => $lang->def('_TITLE'),
		$poll_info['title']
	);
	$GLOBALS['page']->add(
		getTitleArea($page_title, 'poll')
		.'<div class="std_block">'
		.getBackUi( Util::str_replace_once('&', '&amp;', $object_poll->back_url), $lang->def('_BACK'))

		.'<b>'.$lang->def('_TITLE').' : '.$poll_info['title'].'</b><br /><br />'
		.'<span class="text_bold">'.$lang->def('_DESCRIPTION').' : </span>'.$poll_info['description'].'<br /><br />', 'content');

	
	$GLOBALS['page']->add(
		Form::openForm('poll_intro', 'index.php?modname=poll&amp;op=play')
		.Form::getHidden('id_poll', 'id_poll', $id_poll)
		.Form::getHidden('id_param', 'id_param', $id_param)
		.Form::getHidden('id_track', 'id_track', $id_track)
		.Form::getHidden('back_url', 'back_url', $url_coded)
		.Form::getHidden('next_step', 'next_step', 'play')
		.'<div class="align_right">'
	, 'content');
	// Actions
	$score_status = $play_man->getStatus();
	$quest_number = $poll_man->getTotalQuestionNumber();
	
	if($quest_number == 0) {
		$GLOBALS['page']->add($lang->def('_NO_QUESTION_IN_POLL'), 'content');
	} elseif($id_track !== false && $score_status == 'valid') {
		$GLOBALS['page']->add($lang->def('_POLL_ALREDY_VOTED'), 'content');
	} else {
		
		$GLOBALS['page']->add(Form::getButton('begin', 'begin', $lang->def('_POLL_BEGIN')), 'content');
	}
	$GLOBALS['page']->add(
		'</div>'
		.Form::closeForm()
		.'</div>', 'content');
}

function playPollDispatch( $object_poll, $id_param ) {
	//-kb-play-// if(!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) die("You can't access");
	
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_lms'].'/class.module/track.poll.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.param.php' );
	require_once($GLOBALS['where_lms'].'/lib/lib.poll.php' );
	
	$id_poll 		= $object_poll->getId();
	$id_reference 	= getLoParam($id_param, 'idReference');
	$url_coded 		= urlencode(Util::serialize($object_poll->back_url));
	$id_track 		= retriveTrack($id_reference, $id_poll, getLogUserId());
	
	if(isset($_POST['show_result'])) {
		
		// continue a poll completed, show the result
		showResult($object_poll, $id_param);
	}  else {
		
		// play poll
		play($object_poll, $id_param);
	}
}

function play($object_poll, $id_param) {
	//-kb-play-// if(!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) die("You can't access");
	
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_lms'].'/class.module/track.poll.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.param.php' );
	require_once($GLOBALS['where_lms'].'/lib/lib.poll.php' );
	
	$lang 			=& DoceboLanguage::createInstance('poll');
	$id_poll 		= $object_poll->getId();
	$id_reference 	= getLoParam($id_param, 'idReference');
	$url_coded 		= urlencode(Util::serialize($object_poll->back_url));
	$id_track 		= retriveTrack($id_reference, $id_poll, getLogUserId());
	
	$poll_man 	= new PollManagement($id_poll);
	$play_man 	= new PlayPollManagement($id_poll, getLogUserId(), $id_track, $poll_man);
	$poll_info 		= $poll_man->getPollAllInfo();
	$track_info 	= $play_man->getTrackAllInfo();
	
	//number of poll pages-------------------------------------------
	$tot_page = $poll_man->getTotalPageNumber();
	
	// find the page to display 
	$previous_page = importVar('previous_page', false, false);
	if($previous_page === false) {
		
		$page_to_display = 1;
	} else {
		$page_to_display = $previous_page;
		if(isset($_POST['next_page'])) ++$page_to_display;
		if(isset($_POST['prev_page']) && $page_to_display > 1) --$page_to_display;
	}
	if(isset($_POST['page_to_save']) && ($id_reference !== false)) {
		$play_man->storePage($_POST['page_to_save'], true);
	}
	
	// save page track info
	$quest_sequence_number = $poll_man->getInitQuestSequenceNumberForPage($page_to_display);
	$query_question			= $play_man->getQuestionsForPage($page_to_display);
	
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_TITLE').' : '.$poll_info['title'], 'poll')
		.'<div class="std_block">'
		
		.Form::openForm('poll_play', 'index.php?modname=poll&amp;op=play', 'std_form', 'post', 'multipart/form-data')
		// Standard info
		.Form::getHidden('next_step', 'next_step', 'play')
		.Form::getHidden('id_poll', 'id_poll', $id_poll)
		.Form::getHidden('id_param', 'id_param', $id_param)
		.Form::getHidden('back_url', 'back_url', $url_coded)
		.Form::getHidden('id_track', 'id_track', $id_track), 'content');
	
	
	if($tot_page > 1) {
		$GLOBALS['page']->add(
			'<div class="align_center">'.$lang->def('_PAGES').' : '.$page_to_display.' / '.$tot_page.'</div><br />'
		, 'content');
	}
	
	// Page info
	$GLOBALS['page']->add(
		Form::getHidden('page_to_save', 'page_to_save', $page_to_display)
		.Form::getHidden('previous_page', 'previous_page', $page_to_display), 'content');
	
	// Get question from database
	$re_question = sql_query($query_question);
	
	// Page display
	$GLOBALS['page']->add('<div class="test_answer_space">', 'content');
	
	while(list($idQuest, $type_quest, $type_file, $type_class) = sql_fetch_row($re_question)) {
		
		require_once($GLOBALS['where_lms'].'/modules/question_poll/'.$type_file);
		$quest_obj = eval("return new $type_class( $idQuest );");
		
		$GLOBALS['page']->add($quest_obj->play( 	$quest_sequence_number, 
								false, 
								$id_track,
								false ), 'content');
		
		if(($type_quest != 'break_page') && ($type_quest != 'title')) {
			++$quest_sequence_number;
		}
	}
	$GLOBALS['page']->add('</div>'
		.'<div class="test_button_space">', 'content');
	
	if($page_to_display != 1) {
		//back to the next page
		$GLOBALS['page']->add(Form::getButton('prev_page', 'prev_page', $lang->def('_POLL_PREV_PAGE')), 'content');
	}
	if($page_to_display != $tot_page) {
		//button to the next page
		$GLOBALS['page']->add(Form::getButton('next_page', 'next_page', $lang->def('_NEXT')), 'content');
	} else {
		//button to the result page
		$GLOBALS['page']->add(Form::getButton('show_result', 'show_result', $lang->def('_POLL_END_PAGE')), 'content');
	}
	$GLOBALS['page']->add('</div>'
		.Form::closeForm()
		.'</div>', 'content');
}

function showResult( $object_poll, $id_param ) {
	//-kb-play-// if(!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) die("You can't access");
	
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_lms'].'/class.module/track.poll.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.param.php' );
	require_once($GLOBALS['where_lms'].'/lib/lib.poll.php' );
	
	$lang 			=& DoceboLanguage::createInstance('poll');
	$id_poll 		= $object_poll->getId();
	$id_reference 	= getLoParam($id_param, 'idReference');
	$url_coded 		= urlencode(Util::serialize($object_poll->back_url));
	$id_track 		= retriveTrack($id_reference, $id_poll, getLogUserId());
	
	Track_Poll::createTrack(	$id_reference, 
								$id_track, 
								getLogUserId(), 
								date('Y-m-d H:i:s'), 
								'completed', 
								'poll' );
	
	$poll_man 		= new PollManagement($id_poll);
	$play_man 		= new PlayPollManagement($id_poll, getLogUserId(), $id_track, $poll_man);
	$poll_info 		= $poll_man->getPollAllInfo();
	$track_info 	= $play_man->getTrackAllInfo();
	
	$previous_page = importVar('previous_page', false, false);
	
	if($id_reference !== false && $id_track != false) {
		
		if(isset($_POST['page_to_save'])) $play_man->storePage($_POST['page_to_save'], true);
		
		$now = date('Y-m-d H:i:s');
		$poll_track = new Track_Poll($id_track);
		$poll_track->setDate($now);
		$poll_track->status = 'completed';
		$poll_track->update();
		
		$poll_track->updateTrack($id_track, array('status' => 'valid'));
	}
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_TITLE').' : '.$poll_info['title'], 'poll')
		.'<div class="std_block">'
		.$lang->def('_POLL_COMPLETED')
		.'<br /><br />'
		.Form::openForm('poll_show', Util::str_replace_once('&', '&amp;', $object_poll->back_url))
		.'<div class="align_right">'
		.Form::getButton('end_poll', 'end_poll', $lang->def('_POLL_END_BACKTOLESSON'))
		.'</div>'
		.Form::closeForm(), 'content');
	
	$GLOBALS['page']->add('</div>', 'content');
}

function writePollReport( $id_poll, $id_param, $back_url, $mvc = false ) {
	
	require_once(_lms_.'/lib/lib.param.php' );
	require_once(_lms_.'/lib/lib.poll.php' );
	
	$poll_man 		= new PollManagement($id_poll);
	$report_man 	= new ReportPollManagement();
	
	$poll_info 		= $poll_man->getPollAllInfo();
	$valid_track 	= $report_man->getAllTrackId($id_poll, 'valid');
	$tot_tracks 	= $report_man->getHowMuchStat($id_poll, 'valid');
	
	// save page track info
	$quest_sequence_number = $poll_man->getInitQuestSequenceNumberForPage(1);
	$query_question			= $report_man->getQuestions($id_poll);

	$treeview_value = str_replace('treeview_selected_'.$_SESSION['idCourse'], '', array_search($poll_info['title'], $_POST));
	$editions_filter = Get::req('poll_editions_filter', DOTY_INT, -1);
	if (Get::req('del_filter', DOTY_STRING, '') != '') $editions_filter = -1;


	$output = "";

	$str = (!$mvc ? '<div class="std_block">' : '').'<div class="test_answer_space">';
	if ($mvc) {
		$output .= $str;
	} else {
		cout($str, 'content');
	}

	//--- filter on edition ------------------------------------------------------


	//retrieve editions
	$query = "SELECT * FROM %lms_course_editions WHERE id_course = ".(int)$_SESSION['idCourse'];
	$res = sql_query($query);

	//is there any edition ?
	if (sql_num_rows($res) > 0) {
		$arr_editions = array(-1 => Lang::t('_FILTEREDITIONSELECTONEOPTION', 'stats', 'lms'));

		//list of editions for the dropdown, in the format: "[code] name (date_begin - date_end)"
		while ($einfo = sql_fetch_object($res)) {
			$_label = '';
			if ($einfo->code != '') {
				$_label .= '['.$einfo->code.'] ';
			}
			if ($einfo->name != '') {
				$_label .= $einfo->neme;
			}
			if (($einfo->date_begin != '' || $einfo->date_begin != '0000-00-00') && ($einfo->date_end != '' || $einfo->date_end != '0000-00-00')) {
				$_label .= ' ('.Format::date($einfo->date_begin, 'date')
					.' - '.Format::date($einfo->date_end, 'date').')';
			}
			if ($_label == '') {
				//...
			}
			$arr_editions[$einfo->id_edition] = $_label;
		}

		//draw editions dropdown and filter
		$str =
			Form::openForm('tree_filter_form', 'index.php?modname=stats&amp;op=statcourse')
			.Form::getHidden('seq_0.'.$treeview_value, 'treeview_selected_'.$_SESSION['idCourse'].$treeview_value, $poll_info['title'])
			.Form::getHidden('treeview_selected_'.$_SESSION['idCourse'], 'treeview_selected_'.$_SESSION['idCourse'], $treeview_value)
			.Form::getHidden('treeview_state_'.$_SESSION['idCourse'], 'treeview_state_'.$_SESSION['idCourse'], $_POST['treeview_state_'.$_SESSION['idCourse']])
			.Form::openElementSpace()
			.Form::getDropdown( 	Lang::t('_FILTEREDITIONSELECTTITLE', 'stats', 'lms'),
										'poll_editions_filter',
										'poll_editions_filter',
										$arr_editions ,
										$editions_filter )
			.Form::openButtonSpace()
			.Form::getButton('filter', 'filter', Lang::t('_SEARCH', 'stats', 'lms'))
			.Form::getButton('del_filter', 'del_filter', Lang::t('_DEL_FILTER', 'stats', 'lms'))
			.Form::closeButtonSpace()
			.Form::closeElementSpace()
			.Form::closeForm()
		;
		if ($mvc) {
			$output .= $str;
		} else {
			cout($str, 'content');
		}
	}

	//------------------------------------------------------------------------------

	$user = array();
	$tracks = array();

	if ($editions_filter > 0) {
		$query = "SELECT idUser FROM %lms_courseuser "
			." WHERE idCourse = '".(int)$_SESSION['idCourse']."' AND edition_id = '".$editions_filter."'";
		$res = sql_query($query);
		while (list($idUser) = sql_fetch_row($res)) {
			$users[] = $idUser;
		}

		if (count($users) > 0) {
			$query_traks =	"SELECT id_track "
							." FROM %lms_polltrack "
							." WHERE id_user IN (".implode(', ', $users).") ";

			$result_traks = sql_query($query_traks);

			while(list($id_traks) = sql_fetch_row($result_traks))
				$tracks[$id_traks] = $id_traks;
		}
	}

	if (!empty($tracks))
		$valid_track = array_intersect($valid_track, $tracks);
	elseif ($editions_filter != -1) {
		$valid_track = array();
		$valid_track[] = 0;
	}

	if (empty($valid_track)) {
		$valid_track[] = 0;
	}

	$tot_tracks = count($valid_track);
	//----------------------------------------------------------------------------


	// Get question from database
	$re_question = sql_query($query_question);
	
	if (isset($_POST['export'])) {
		$export = true;
		$filename = 'stats_'.str_replace(' ', '_', $poll_info['title']).'_'.date("Y\_m\_d").'.csv';
		$filetext = '';
	} else {
		$export = false;
	}
	
	while(list($idQuest, $type_quest, $type_file, $type_class) = sql_fetch_row($re_question)) {
		
		require_once(_lms_.'/modules/question_poll/'.$type_file);
		$quest_obj = eval("return new $type_class( $idQuest );");
		
		if ($export) {
			$filetext.=$quest_obj->export_CSV( $quest_sequence_number, $tot_tracks, $valid_track );
			$filetext .= "\r\n";
		} else {
			$GLOBALS['page']->add($quest_obj->playReport( $quest_sequence_number, $tot_tracks, $valid_track ), 'content');
		}
		
		if(($type_quest != 'break_page') && ($type_quest != 'title')) {
			++$quest_sequence_number;
		}
	}
	
	if ($export) {
		require_once(_base_.'/lib/lib.download.php' );
		sendStrAsFile($filetext, $filename);		
	}
	
	$treeview_value = str_replace('treeview_selected_'.$_SESSION['idCourse'], '', array_search($poll_info['title'], $_POST));
	
	$str = 
		Form::openForm('tree_export_form', 'index.php?modname=stats&amp;op=statcourse')
		.Form::getHidden('seq_0.'.$treeview_value, 'treeview_selected_'.$_SESSION['idCourse'].$treeview_value, $poll_info['title'])
		.Form::getHidden('treeview_selected_'.$_SESSION['idCourse'], 'treeview_selected_'.$_SESSION['idCourse'], $treeview_value)
		.Form::getHidden('treeview_state_'.$_SESSION['idCourse'], 'treeview_state_'.$_SESSION['idCourse'], $_POST['treeview_state_'.$_SESSION['idCourse']])
		.Form::openButtonSpace()
		.Form::getButton('export', 'export', Lang::t('_EXPORT_CSV', 'standard'))
		.Form::closeButtonSpace()
		.Form::closeForm()
	;
	if ($mvc) {
		$output .= $str;
	} else {
		cout($str, 'content');
	}
	
	$str = '</div>'.(!$mvc ? '</div>' : '');
	if ($mvc) {
		$output .= $str;
	} else {
		cout($str, 'content');
	}
}

?>