<?php
namespace appCore\Events\Core\FileSystem;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UnlinkEvent
 * @package appCore\Events\Core\FileSystem
 */
class UnlinkEvent extends Event
{
    const EVENT_NAME = 'core.event.filesystem.unlink';

    private $path;
    private $result;

    /**
     * UnlinkEvent constructor.
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