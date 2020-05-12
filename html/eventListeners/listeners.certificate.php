<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* =========================================================================\
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

Events::listen("lms.course_user.updated", function ($event) {
    if ($event['new_data']['status'] == _CUS_END) {
        file_put_contents(_files_ . "/test-core-listener.txt", "Generate certificate for user {$event['id_user']} in course {$event['id_course']}");
    }
}, Events::PRIORITY_CORE);
