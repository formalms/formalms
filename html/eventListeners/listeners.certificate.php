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

Events::listen('lms.course_user.updated', function ($event) {
    if ($event['new_data']['status'] == _CUS_END) {
        require_once Forma::inc(_lms_ . '/lib/lib.aggregated_certificate.php');
        $ca = new AggregatedCertificate();
        $ca->releaseNewAggrCertCourses($event);
    }
}, Events::PRIORITY_CORE);

Events::listen('lms.coursepath_user.completed', function ($event) {
    require_once Forma::inc(_lms_ . '/lib/lib.aggregated_certificate.php');
    $ca = new AggregatedCertificate();
    $ca->releaseNewAggrCertPaths($event);
}, Events::PRIORITY_CORE);
