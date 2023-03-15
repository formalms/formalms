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
 * Class UploadEvent.
 */
class UploadEvent extends Event
{
    public const EVENT_NAME = 'core.event.filesystem.upload';

    private $srcFile;
    private $dstFile;
    private $result;

    /**
     * UploadEvent constructor.
     *
     * @param $srcFile
     * @param $dstFile
     */
    public function __construct($srcFile, $dstFile)
    {
        $this->srcFile = $srcFile;
        $this->dstFile = $dstFile;
    }

    /**
     * @return mixed
     */
    public function getSrcFile()
    {
        return $this->srcFile;
    }

    /**
     * @return mixed
     */
    public function getDstFile()
    {
        return $this->dstFile;
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
            'srcFile' => $this->srcFile,
            'dstFile' => $this->dstFile,
            'result' => $this->result,
        ];
    }
}
