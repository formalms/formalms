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

namespace appLms\Events;

/**
 * Created by PhpStorm.
 * User: cocciagialla
 * Date: 19/10/15
 * Time: 19.33.
 */
class DumpAndDieLmsListener
{
    public function onFooAction(\Symfony\Component\EventDispatcher\Event $event)
    {
        var_dump($event);
        exit;
    }

    public function printOnlyADot(\Symfony\Component\EventDispatcher\Event $event)
    {
        echo '...';
    }
}
