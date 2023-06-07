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

/**
 * Class UserListEvent.
 */
class UserListEvent extends Event
{
    public const EVENT_NAME = 'lms.userlist.event';

    /** @var null */
    protected $lang;

    /** @var null */
    protected $out;

    protected $defaultExportEndpoint;

    /** @var null */
    protected $exportLink;

    /**
     * @var null;
     */
    protected $exportEndpoint;

    /** @var null */
    protected $idEvent;

    /**
     * UserProfileShowEvent constructor.
     */
    public function __construct($out, $lang)
    {
        $this->out = $out;

        $this->lang = $lang;

        $this->idEvent = null;
    }

    /**
     * @param null $idEvent
     */
    public function setIdEvent($idEvent)
    {
        $this->idEvent = $idEvent;
    }

    /**
     * @param mixed $defaultExportEndpoint
     */
    public function setDefaultExportEndpoint($defaultExportEndpoint)
    {
        $this->defaultExportEndpoint = $defaultExportEndpoint;

        $this->exportLink = '<a href="' . $this->defaultExportEndpoint . '" target="_blank">' . $this->lang->def('_EXPORT_XLS') . '</a>';
    }

    /**
     * @param null $exportEndpoint
     */
    public function setExportEndpoint($exportEndpoint)
    {
        $this->exportEndpoint = $exportEndpoint;
    }

    /**
     * @return null
     */
    public function getIdEvent()
    {
        return $this->idEvent;
    }

    /**
     * @param mixed $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return mixed
     */
    public function getExportLink()
    {
        return $this->exportLink;
    }

    /**
     * @param mixed $exportLink
     */
    public function setExportLink($exportLink)
    {
        $this->exportLink = $exportLink;
    }

    /**
     * @return mixed
     */
    /**
     * @return mixed
     */
    public function getDefaultExportEndpoint()
    {
        return $this->defaultExportEndpoint;
    }

    /**
     * @return mixed
     */
    public function getExportEndpoint()
    {
        return $this->exportEndpoint;
    }

    /**
     * @return null
     */
    public function getOut()
    {
        return $this->out;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'lang' => $this->lang,
            'out' => $this->out,
            'defaultExportEndpoint' => $this->defaultExportEndpoint,
            'exportLink' => $this->exportLink,
            'exportEndpoint' => $this->exportEndpoint,
            'idEvent' => $this->idEvent,
        ];
    }
}
