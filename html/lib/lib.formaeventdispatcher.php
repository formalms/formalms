<?php

use Symfony\Component\EventDispatcher\EventDispatcher as SymphonyEventDispatcher;

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

/**
 * Forma event disparcher.
 */
final class FormaEventDispatcher extends SymphonyEventDispatcher
{
    /**
     * Singleton instance getter.
     *
     * @return self
     */
    public static function getInstance()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new static();
        }
        return $instance;
    }
}
