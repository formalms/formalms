<?php

\appCore\Events\DispatcherManager::addListener('core.dummy.event', function($event) {
    echo "event-ciao";
});
