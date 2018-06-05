<?php
namespace appCore\Events\Core\FileSystem;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class DownloadEvent
 * @package appCore\Events\Core\FileSystem
 */
class DownloadEvent extends Event
{
    const EVENT_NAME = 'core.event.filesystem.download';

    private $path;
    private $filename;
    private $ext;
    private $sendname;

    /**
     * DownloadEvent constructor.
     * @param $path
     * @param $filename
     * @param $ext
     * @param $sendname
     */
    public function __construct($path, $filename, $ext = NULL, $sendname = NULL)
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