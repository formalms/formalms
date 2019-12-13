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

Events::listen('core.user.registered', function($event, $eventName) {
    file_put_contents(_files_ . '/tmp/test_events.log', "Function called listening to '{$eventName}' event. Registered user {$event['idst']}.\n", FILE_APPEND);
});

Events::listen('core.user.created', function($event, $eventName) {
    file_put_contents(_files_ . '/tmp/test_events.log', "Function called listening to '{$eventName}' event. Created user {$event['idst']}.\n", FILE_APPEND);
});