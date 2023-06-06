<?php

namespace FormaLms\lib\Processors\Access;

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

class NewsletterAccessProcessor extends AccessProcessor
{

    public const NAME = 'newsletter';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getAccessList($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : self {

        $this->accessModel->setAccessList($resourceId, $selection);
        $back_url = 'index.php?modname=newsletter&amp;op=summary&amp;tot=' . $this->accessModel->getTotalSent() . '&amp;id_send=' . $resourceId;
       
        $this->setRedirect(str_replace('&amp;', '&', $back_url));
        return $this;
        
    }
}