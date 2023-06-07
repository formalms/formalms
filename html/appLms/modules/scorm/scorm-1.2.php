<?php

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

defined('IN_FORMA') or exit('Direct access is forbidden.');

/*
 * @module scorm
 * Custom routines for scorm 1.2
 * @version $Id$
 * @copyright 2008
 * @author Emanuele Sandri
 **/

const SCORM_RTE_STUDENTNAME = 'cmi.core.student_name';
const SCORM_RTE_LEARNERNAME = 'cmi.core.student_name';
const SCORM_RTE_STUDENTID = 'cmi.core.student_id';
const SCORM_RTE_LEARNERID = 'cmi.core.student_id';

const SCORM_RTE_CREDIT = 'cmi.core.credit';
const SCORM_RTE_LESSONMODE = 'cmi.core.lesson_mode';
const SCORM_RTE_ENTRY = 'cmi.core.entry';
const SCORM_RTE_EXIT = 'cmi.core.exit';
const SCORM_RTE_TOTALTIME = 'cmi.core.total_time';
const SCORM_RTE_SESSIONTIME = 'cmi.core.session_time';

const SCORM_RTE_MASTERYSCORE = 'cmi.student_data.mastery_score';
const SCORM_RTE_COMPLETIONTHRESHOLD = 'cmi.student_data.mastery_score';
const SCORM_RTE_PROGRESS = 'cmi.core.score.raw';
const SCORM_RTE_LESSONSTATUS = 'cmi.core.lesson_status';
const SCORM_RTE_COMPLETIONSTATUS = 'cmi.core.lesson_status';

const SCORM_RTE_MAXTIMEALLOWED = 'cmi.student_data.max_time_allowed';
const SCORM_RTE_LAUNCH_DATA = 'cmi.launch_data';
const SCORM_RTE_TIMELIMITACTION = 'cmi.student_data.time_limit_action';

$GLOBALS['xpathwritedb'] = ['lesson_location' => '//cmi/core/lesson_location',
                    'lesson_status' => '//cmi/core/lesson_status',
                    'entry' => '//cmi/core/entry',
                    'score_raw' => '//cmi/core/score/raw',
                    'score_min' => '//cmi/core/score/min',
                    'score_max' => '//cmi/core/score/max',
                    'exit' => '//cmi/core/exit',
                    'session_time' => '//cmi/core/session_time',
];

function scormInitializeParams($trackobj, $scormtype, $idscorm_item)
{
    list($adlcp_masteryscore,
            $adlcp_maxtimeallowed,
            $adlcp_datafromlms,
            $adlcp_timelimitaction) =
                    sql_fetch_row(sql_query('SELECT  adlcp_masteryscore,'
                                                        . 'adlcp_maxtimeallowed,'
                                                        . 'adlcp_datafromlms,'
                                                        . 'adlcp_timelimitaction'
                                                . '  FROM learning_scorm_items'
                                                . ' WHERE idscorm_item=' . $idscorm_item));

    // tracking initializations
    if ($scormtype == 'sco') {
        $trackobj->setParam(SCORM_RTE_LESSONSTATUS, 'not attempted', false, true);
    } else {
        $trackobj->setParam(SCORM_RTE_LESSONSTATUS, 'completed', false, true);
    }

    $trackobj->setParam(SCORM_RTE_STUDENTNAME, sl_sal_getUserName(), false, true);
    $trackobj->setParam(SCORM_RTE_CREDIT, 'credit', false, true);
    $trackobj->setParam(SCORM_RTE_LESSONMODE, 'normal', false, true);
    $trackobj->setParam(SCORM_RTE_ENTRY, 'ab-initio', false, true);
    $trackobj->setParam(SCORM_RTE_TOTALTIME, '0000:00:00.00', false, true);
    $trackobj->setParam(SCORM_RTE_MASTERYSCORE, $adlcp_masteryscore, false, true);
    $trackobj->setParam(SCORM_RTE_MAXTIMEALLOWED, $adlcp_maxtimeallowed, false, true);
    $trackobj->setParam(SCORM_RTE_LAUNCH_DATA, $adlcp_datafromlms, false, true);
    $trackobj->setParam(SCORM_RTE_TIMELIMITACTION, $adlcp_timelimitaction, false, true);
}

function computeCompletionStatus($trackobj, $adlcp_masteryscore)
{
    if ($trackobj->getParam(SCORM_RTE_PROGRESS, false) >= $adlcp_masteryscore) {
        $trackobj->setParam(SCORM_RTE_LESSONSTATUS, 'passed', false, true);
        $trackobj->setParam(SCORM_RTE_CREDIT, 'no-credit', false, true);
        $lesson_status = 'passed';
    } elseif ($trackobj->getParam(SCORM_RTE_LESSONSTATUS, false) != 'passed') {
        $trackobj->setParam(SCORM_RTE_LESSONSTATUS, 'failed', false, true);
        $lesson_status = 'failed';
    } else {
        $trackobj->setParam(SCORM_RTE_CREDIT, 'no-credit', false, true);
        $lesson_status = 'passed';
    }

    return $lesson_status;
}
