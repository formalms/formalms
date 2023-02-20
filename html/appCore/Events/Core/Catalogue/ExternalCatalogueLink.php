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

namespace appCore\Events\Core\Catalogue;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ExternalCatalogueLink.
 */
class ExternalCatalogueLink extends Event
{
    public const EVENT_NAME = 'core.externalcataloguelink.event';

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
