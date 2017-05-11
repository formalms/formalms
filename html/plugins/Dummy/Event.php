<?php

\appCore\Events\DispatcherManager::addListener('core.dummy.event', function($event) {
echo "<hr>Hi I'm Dummy Event (core.dummy.event): Anyone listining?<br/>";
});
