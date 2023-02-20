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

namespace appLms\Events\Lms;

use Symfony\Contracts\EventDispatcher\Event;

class OrgPropertiesPrintEvent extends Event
{
    public const EVENT_NAME = 'lms.org.properties.print';

    /**
     * @var
     */
    protected $element;

    protected $displayable;

    protected $accessible;

    protected $id;

    protected $action;

    protected $orgTreeView;

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
    public function getOrgTreeView()
    {
        return $this->orgTreeView;
    }

    /**
     * @param mixed $orgTreeView
     */
    public function setOrgTreeView($orgTreeView)
    {
        $this->orgTreeView = $orgTreeView;
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
        $this->action[] = $action;
    }
}
