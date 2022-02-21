<?php defined("IN_FORMA") or die('Direct access is forbidden.');

Events::listen("lms.course_user.updated", function($event)
{
    if($event['new_data']['status'] == _CUS_END) {
        require_once(Forma::inc(_lms_.'/lib/lib.aggregated_certificate.php'));
        $ca = new AggregatedCertificate();
        $ca->releaseNewAggrCertCourses($event); 
    }
},Events::PRIORITY_CORE);


Events::listen("lms.coursepath_user.completed", function($event)
{
    require_once(Forma::inc(_lms_.'/lib/lib.aggregated_certificate.php'));
    $ca = new AggregatedCertificate();
    $ca->releaseNewAggrCertPaths($event); 
    
},Events::PRIORITY_CORE);