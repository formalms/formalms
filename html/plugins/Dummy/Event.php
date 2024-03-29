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

// \appCore\Events\DispatcherManager::addListener('core.dummy.event', function($event) {
//     $event->setFoo("Dummy name");
// });

// \appCore\Events\DispatcherManager::addListener(appCore\Events\Core\User\RegisterUserEvent::EVENT_NAME, function($event) {
//     $query = "INSERT INTO `learning_dummy_userslog` (`username`, `timestamp`) VALUES ('".$event->getId()."', ".time().")";
//     sql_query($query);
// });

Events::listen('core.user.creating', function ($event) {
    $userdata = $event['userdata'];
    $userdata[ACL_INFO_FIRSTNAME] = 'Firstname changed by Dummy plugin';
    $userdata[ACL_INFO_LASTNAME] = 'Lastname changed by Dummy plugin';
    $event['userdata'] = $userdata;
});

Events::listen('core.user.registered', function ($event, $eventName) {
    file_put_contents(_files_ . '/tmp/test_events.log', "Function called listening to '{$eventName}' event. Registered user {$event['idst']}.\n", FILE_APPEND);
});

Events::listen('core.user.created', function ($event, $eventName) {
    file_put_contents(_files_ . '/tmp/test_events.log', "Function called listening to '{$eventName}' event. Created user {$event['idst']} with username '{$event['userdata'][ACL_INFO_USERID]}'.\n", FILE_APPEND);
});
