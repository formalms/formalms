<?php
namespace appCore\Events\Core\FileSystem;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UploadEvent
 * @package appCore\Events\Core\FileSystem
 */
class UploadEvent extends Event
{
    const EVENT_NAME = 'core.event.filesystem.upload';

    private $srcFile;
    private $dstFile;
    private $result;

    /**
     * UploadEvent constructor.
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