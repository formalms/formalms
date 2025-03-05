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


Events::listen('lms.course.from.coursepath.deleting', function ($event) {
    $modelCoursePath = new CoursepathLms();
    $idCourse = $event['id_course'];
    $idPath = $event['id_path'];
    $modelCoursePath->removePrerequisites($idCourse,$idPath);
},Events::PRIORITY_CORE);

Events::listen('lms.course.deleting', function ($event) {
    $modelCoursePath = new CoursepathLms();
    $idCourse = $event['id_course'];
    $modelCoursePath->removePrerequisites($idCourse);
},Events::PRIORITY_CORE);