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

class RuleAccessProcessor extends AccessProcessor
{

    public const NAME = 'rule';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getAccessList($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : self {

        if ($this->accessModel->setAccessList($resourceId, $selection)) {
            $url  = 'index.php?r=alms/enrollrules/modelem&amp;id_rule=' . $resourceId . '&amp;result=true';
        } else {
            $url  = 'index.php?r=alms/enrollrules/modelem&amp;id_rule=' . $resourceId . '&amp;result=false';
        }

        $this->setRedirect($url);
        return $this;
        
    }
}