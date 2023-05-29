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

class AdminmanagerAccessProcessor extends AccessProcessor
{

    public const NAME = 'adminmanager';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getAccessList($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : self {

        if ($this->accessModel->setAccessList($resourceId, $selection)) {
            $url = 'index.php?r=adm/adminmanager/show&res=ok_ins';
        } else {
            $url  = 'index.php?r=adm/adminmanager/show&res=err_ins';
        }

        $this->setRedirect($url);
        return $this;
        
    }
}



