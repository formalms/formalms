<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace appCore\Events\Core\FileSystem;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class UnlinkEvent.
 */
class UnlinkEvent extends Event
{
    public const EVENT_NAME = 'core.event.filesystem.unlink';

    private $path;
    private $result;

    /**
     * UnlinkEvent constructor.
     *
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'path' => $this->path,
            'result' => $this->result,
        ];
    }
}
