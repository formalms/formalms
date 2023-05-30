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

class RoleAccessProcessor extends AccessProcessor
{

    public const NAME = 'role';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getAccessList($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : self {

        $message = $this->accessModel->setAccessList($resourceId, $selection);
        $this->setRedirect('index.php?r=adm/functionalroles/man_users&id=' . $resourceId .'&res=' . ($message ? 'ok_users' : 'err_users'));

        return $this;
        
    }
}


