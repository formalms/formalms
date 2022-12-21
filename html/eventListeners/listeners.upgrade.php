<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

Events::listen('platform.upgrade', function ($event) {
    
    $upgraderPath = _base_ . '/eventListeners/Upgrade/';
    $params = $event['params'] ?? [];
    $upgradeNamespace = 'FormaLms\eventListeners\Upgrade\\';

    if(!$event['upgrade_class']) {
        throw new \Exception('Missing upgrade class');
    }

    if(!preg_match('/\b[(Pre|Post)+Version]+[0-9]{14}+$/', $event['upgrade_class'])) {
        throw new \Exception('Wrong format class');
    }

    if(file_exists($upgraderPath . $event['upgrade_class'] . '.php')) {
        
        $className = $upgradeNamespace . $event['upgrade_class'];
       
        return (new $className($params))->run();
    }


    dd($event['upgrade_class']);
});