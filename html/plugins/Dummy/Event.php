<?php

/*
 * Here you can hook events
 */

\appCore\Events\DispatcherManager::addListener('core.dummy.event', function($event) {
    $event->setFoo("Dummy name");
});

\appCore\Events\DispatcherManager::addListener(appCore\Events\Core\User\RegisterUserEvent::EVENT_NAME, function($event) {
    $query = "INSERT INTO `learning_dummy_userslog` (`username`, `timestamp`) VALUES ('".$event->getId()."', ".time().")";
    sql_query($query);
});

Events::listen('core.user.creating', function($event) {
    $userdata = $event['userdata'];
    $userdata[ACL_INFO_FIRSTNAME] = 'Firstname changed by Dummy plugin';
    $userdata[ACL_INFO_LASTNAME] = 'Lastname changed by Dummy plugin';
    $event['userdata'] = $userdata;
});

Events::listen('core.user.registered', function($event, $eventName) {
    file_put_contents(_files_ . '/tmp/test_events.log', "Function called listening to '{$eventName}' event. Registered user {$event['idst']}.\n", FILE_APPEND);
});

Events::listen('core.user.created', function($event, $eventName) {
    file_put_contents(_files_ . '/tmp/test_events.log', "Function called listening to '{$eventName}' event. Created user {$event['idst']} with username '{$event['userdata'][ACL_INFO_USERID]}'.\n", FILE_APPEND);
});


Events::listen('lms.course_user.updated', function($event, $eventName) {
    

    $params = $event->getArguments();
    if ($params['new_data']['status'] != _CUS_END) return;
    
    $man_courseuser = new Man_CourseUser(DbConn::getInstance()); 
    require_once(Forma::inc(_lms_.'/lib/lib.aggregated_certificate.php'));
    $lib_aggreg = new AggregatedCertificate();
    $associated_aggr_cert_courses = $lib_aggreg->getIdAssocFromUserCourse($params['id_user'], $params['id_course']);
    
    foreach($associated_aggr_cert_courses as $idcert=>$associations) {
        foreach ($associations as $id_association =>$courses) {
            if ($man_courseuser->hasCompletedCourses($params['id_user'], $courses) ) {
                if (!$lib_aggreg->isCertIssued($params['id_user'],$idcert,$id_association))
                    $lib_aggreg->releaseNewCertificate($params['id_user'],$idcert,$id_association); 
            }
        }
        
    }
    

});