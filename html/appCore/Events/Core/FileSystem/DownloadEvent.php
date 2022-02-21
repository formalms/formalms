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

namespace appCore\Events\Core\FileSystem;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class DownloadEvent.
 */
class DownloadEvent extends Event
{
    public const EVENT_NAME = 'core.event.filesystem.download';

    private $path;
    private $filename;
    private $ext;
    private $sendname;

    /**
     * DownloadEvent constructor.
     *
     * @param $path
     * @param $filename
     * @param $ext
     * @param $sendname
     */
    public function __construct($path, $filename, $ext = null, $sendname = null)
    {
        $this->path = $path;
        $this->filename = $filename;
        $this->ext = $ext;
        $this->sendname = $sendname;
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
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return null
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * @return null
     */
    public function getSendname()
    {
        return $this->sendname;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'path' => $this->path,
            'filename' => $this->filename,
            'ext' => $this->ext,
            'sendname' => $this->sendname,
        ];
    }
}
