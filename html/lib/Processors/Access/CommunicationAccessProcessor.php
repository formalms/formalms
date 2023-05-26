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

class CommunicationAccessProcessor extends AccessProcessor
{

    public const NAME = 'communication';

    public function getAccessList(int $resourceId) : array {

        return $this->accessModel->getAccessList($resourceId);
    }

    public function setAccessList(int $resourceId, array $selection) : array {

        if ($this->accessModel->setAccessList($resourceId, $selection)) {
            $url  = 'index.php?r=alms/communication/show&success=1';
        } else {
            $url  = 'index.php?r=alms/communication/show&error=1';
        }

        return $this->response($url);
        
    }
}