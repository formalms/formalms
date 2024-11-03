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

class OrganizationAccessProcessor extends AccessProcessor
{

    public const NAME = 'organization';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getAccessList($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : self {

        $this->accessModel->setAccessList($resourceId, $selection);
        $this->setRedirect('index.php?r=lms/lomanager/show&modname=storage');
        $this->setFolder('lms');

        return $this;
        
    }
}