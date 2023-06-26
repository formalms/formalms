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

class LearninggroupAccessProcessor extends AccessProcessor
{

    public const NAME = 'learninggroup';

    public function getAccessList($resourceId) : array {

        $acl_man = new \FormaACLManager();
        return $acl_man->getGroupUMembers($resourceId);

    }

    public function setAccessList($resourceId, array $selection) : self {

        $message = $this->accessModel->setAccessList($resourceId, $selection);

        $this->setRedirect('index.php?modname=groups&op=groups&result='.($message ? 'ok' : 'err'));
        $this->setFolder('lms');
        return $this;
        
    }
}