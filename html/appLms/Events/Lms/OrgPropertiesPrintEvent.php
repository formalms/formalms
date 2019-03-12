<?php

namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

class OrgPropertiesPrintEvent extends Event {
    
    const EVENT_NAME = 'lms.org.properties.print';

    /**
     * @var
     */

    protected $element;

    protected $displayable;

    protected $accessible;

    protected $id;

    protected $action;

    /**
     * @return mixed
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param mixed $element
     */
    public function setElement($element)
    {
        $this->element = $element;
    }

    /**
     * @return mixed
     */
    public function getDisplayable()
    {
        return $this->displayable;
    }

    /**
     * @param mixed $printing_box
     */
    public function setDisplayable($printing_box)
    {
        $this->displayable = $printing_box;
    }

    /**
     * @return mixed
     */
    public function getAccessible()
    {
        return $this->accessible;
    }

    /**
     * @param mixed $printing_title
     */
    public function setAccessible($printing_title)
    {
        $this->accessible = $printing_title;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action []= $action;
    }
}