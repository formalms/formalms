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

function lmsLoginOperation() {
	require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');

	$pa_man = new AssessmentList();
	$user_course_as_assessment = $pa_man->getUserAssessmentSubsription(Docebo::user()->getArrSt());

	if(is_array($user_course_as_assessment)) {

		$subs_man = new CourseSubscribe_Management();
		$subs_man->multipleUserSubscribe(	getLogUserId(),
											$user_course_as_assessment['course_list'],
											$user_course_as_assessment['level_number']);
	}	
}


function lmsLogoutOperation() {
	
}


?>