<?php

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

defined('IN_FORMA') or die('Direct access is forbidden.');

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Event class.
 */
final class FormaEvent extends GenericEvent
{
    public function __get($name)
    {
        return $this->getArgument($name);
    }

    public function __set($name, $value)
    {
        return $this->setArgument($name, $value);
    }
    
    public function __isset($name)
    {
        return $this->hasArgument($name);
    }
}
