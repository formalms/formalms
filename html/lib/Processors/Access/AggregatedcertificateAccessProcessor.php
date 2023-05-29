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

class AggregatedcertificateAccessProcessor extends AccessProcessor
{

    public const NAME = 'aggregatedcertificate';

    public function getAccessList($resourceId) : array {

        $selection = $this->accessModel->getAccessList($resourceId);
        $this->requestParams['selection'] = $selection;
        $this->setSessionData(static::NAME, $this->requestParams);
        
        return $selection;
    }

    public function setAccessList($resourceId, array $selection) : self {
   
        $args = $this->getSessionData(static::NAME);
        $args['selection'] = $selection;
        $this->setParams($this->accessModel->getAssociationView($args));

        return $this;
        
    }
}



