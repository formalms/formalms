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