<?php

namespace FormaLms\lib\Processors\Access;

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

class NewslettercourseAccessProcessor extends NewsletterAccessProcessor
{

    public const NAME = 'newslettercourse';

    public function setAccessList($resourceId, array $selection) : self {

        $this->accessModel->setAccessList($resourceId, $selection, $this->session->get('idCourse'));
        $back_url = 'index.php?modname=newsletter&amp;op=summary&amp;tot=' . $this->accessModel->getTotalSent() . '&amp;id_send=' . $resourceId;
       
        $this->setRedirect(str_replace('&amp;', '&', $back_url));
        return $this;
        
    }

}