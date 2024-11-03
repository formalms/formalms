<?php

use FormaLms\lib\Forma;

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

function lmsLoginOperation()
{
    require_once Forma::include(_lms_ . '/lib/', 'lib.subscribe.php');
    require_once _lms_ . '/lib/lib.preassessment.php';

    $pa_man = new AssessmentList();
    $user_course_as_assessment = $pa_man->getUserAssessmentSubsription(\FormaLms\lib\FormaUser::getCurrentUser()->getArrSt());

    if (is_array($user_course_as_assessment)) {
        $subs_man = new CourseSubscribe_Management();
        $subs_man->multipleUserSubscribe(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(),
                                            $user_course_as_assessment['course_list'],
                                            $user_course_as_assessment['level_number']);
    }
}

function lmsLogoutOperation()
{
}
