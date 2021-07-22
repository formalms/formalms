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

/**
 * @module scorm
 * Custom routines for scorm 1.3
 * @version $Id$
 * @copyright 2008 
 * @author Emanuele Sandri
 **/

define('SCORM_RTE_STUDENTNAME','cmi.learner_name');
define('SCORM_RTE_LEARNERNAME','cmi.learner_name');
define('SCORM_RTE_STUDENTID','cmi.learner_id');
define('SCORM_RTE_LEARNERID','cmi.learner_id');


define('SCORM_RTE_CREDIT','cmi.credit');
define('SCORM_RTE_LESSONMODE','cmi.mode');
define('SCORM_RTE_ENTRY','cmi.entry');
define('SCORM_RTE_EXIT','cmi.exit');
define('SCORM_RTE_TOTALTIME','cmi.total_time');
define('SCORM_RTE_SESSIONTIME','cmi.session_time');

define('SCORM_RTE_MASTERYSCORE','cmi.completion_threshold');
define('SCORM_RTE_COMPLETIONTHRESHOLD','cmi.completion_threshold');
define('SCORM_RTE_PROGRESS','cmi.progress_measure');
define('SCORM_RTE_LESSONSTATUS','cmi.completion_status');
define('SCORM_RTE_COMPLETIONSTATUS','cmi.completion_status');
define('SCORM_RTE_SUCCESSSTATUS','cmi.success_status');

define('SCORM_RTE_MAXTIMEALLOWED','cmi.max_time_allowed');
define('SCORM_RTE_LAUNCH_DATA','cmi.launch_data');
define('SCORM_RTE_TIMELIMITACTION','cmi.time_limit_action');

$GLOBALS['xpathwritedb'] = array( 	'lesson_location' => '//cmi/location',
					'lesson_status' => '//cmi/completion_status',
					'entry' => '//cmi/entry',
					'score_raw' => '//cmi/score/raw',
					'score_min' => '//cmi/score/min',
					'score_max' => '//cmi/score/max',
					'exit' => '//cmi/exit',
					'session_time' => '//cmi/session_time'
					);

function scormInitializeParams($trackobj, $scormtype, $idscorm_item) {
	
	/* masteryscore in 1.3 is completionthreshold */
	list(	$adlcp_completionthreshold,
			$adlcp_maxtimeallowed,
			$adlcp_datafromlms,
			$adlcp_timelimitaction ) = 
					sql_fetch_row(sql_query( "SELECT  adlcp_completionthreshold,"
														."adlcp_maxtimeallowed,"
														."adlcp_datafromlms,"
														."adlcp_timelimitaction"
												."  FROM learning_scorm_items"
												." WHERE idscorm_item=".$idscorm_item ));
	
	// tracking initializations
	if( $scormtype == 'sco' ) 
		$trackobj->setParam(SCORM_RTE_COMPLETIONSTATUS, 'unknown', false, true);
	else
		$trackobj->setParam(SCORM_RTE_COMPLETIONSTATUS, 'completed', false, true);
	
	$trackobj->setParam(SCORM_RTE_LEARNERNAME, sl_sal_getUserName(), false, true);
	$trackobj->setParam(SCORM_RTE_CREDIT, 'credit', false, true);
	$trackobj->setParam(SCORM_RTE_LESSONMODE, 'normal', false, true);
	$trackobj->setParam(SCORM_RTE_ENTRY, 'ab-initio', false, true);
	$trackobj->setParam(SCORM_RTE_TOTALTIME, 'PT0H0M0S', false, true);
	$trackobj->setParam(SCORM_RTE_COMPLETIONTHRESHOLD, $adlcp_completionthreshold, false, true);
	if($adlcp_maxtimeallowed) $trackobj->setParam(SCORM_RTE_MAXTIMEALLOWED, $adlcp_maxtimeallowed, false, true);
	$trackobj->setParam(SCORM_RTE_LAUNCH_DATA, $adlcp_datafromlms, false, true);
	$trackobj->setParam(SCORM_RTE_TIMELIMITACTION, $adlcp_timelimitaction, false, true);
	
}

function computeCompletionStatus( $trackobj, $adlcp_completionthreshold ) {
	
	// this status is only used in the lms, not in the tracking passed to the lms
	
	$completion_status = 'incomplete';
	if( $trackobj->getParam(SCORM_RTE_PROGRESS, false) >= $adlcp_completionthreshold ) {
		$trackobj->setParam(SCORM_RTE_COMPLETIONSTATUS, 'completed', false, true);
		$trackobj->setParam(SCORM_RTE_CREDIT, 'no-credit', false, true);
		$completion_status = 'completed';
	} else {
		$trackobj->setParam(SCORM_RTE_LESSONSTATUS, 'incomplete', false, true);
		$completion_status = 'incomplete';
	}
	$success_status = $trackobj->getParam(SCORM_RTE_SUCCESSSTATUS, false);
	if($success_status == 'failed') $lesson_status = 'failed';
	return $completion_status;
}

?>