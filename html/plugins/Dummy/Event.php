<?php

\appCore\Events\DispatcherManager::addListener('core.dummy.event', function($event) {
    $event->setFoo("Dummy name");
});

\appCore\Events\DispatcherManager::addListener(appCore\Events\Core\User\RegisterUserEvent::EVENT_NAME, function($event) {
    error_log($event->getId());
});