<?php
namespace appLms\Events\Lms;

use Symfony\Contracts\EventDispatcher\Event;

class MyCertificateTabLoading extends Event
{
    const EVENT_NAME = 'lms.mycertificatetab.loading';
    protected $tabs;

    public function __construct()
    {
        $this->tabs = [];
    }

    /**
     * @return array
     */
    public function addTab($tab_id, $tab_content)
    {
        $this->tabs[$tab_id] = $tab_content;
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        return $this->tabs;
    }

}