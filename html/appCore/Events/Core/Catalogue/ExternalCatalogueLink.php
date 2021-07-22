<?php

namespace appCore\Events\Core\Catalogue;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ExternalCatalogueLink
 * @package appLms\Events\Core
 */
class ExternalCatalogueLink extends Event
{
    const EVENT_NAME = 'core.externalcataloguelink.event';

    protected $link;

    /**
     * @param $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return $link
     */
    public function getLink()
    {
        return $this->link;
    }
}
