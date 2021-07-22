<?php

namespace appLms\Events;
/**
 * Created by PhpStorm.
 * User: cocciagialla
 * Date: 19/10/15
 * Time: 19.33
 */
class DumpAndDieLmsListener
{
    public function onFooAction(\Symfony\Component\EventDispatcher\Event $event)
    {
        var_dump($event);
        die;
    }

    public function printOnlyADot(\Symfony\Component\EventDispatcher\Event $event)
    {
        echo "...";
    }
}