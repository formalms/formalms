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

class LmsmenuAccessProcessor extends AccessProcessor
{

    public const NAME = 'lmsmenu';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getAccessList($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : self {

        if ($this->accessModel->setAccessList($resourceId, $selection)) {
            $url  = 'index.php?modname=middlearea&amp;op=view_area&amp;result=ok&amp;of_platform=lms';
        } else {
            $url  = 'index.php?modname=middlearea&amp;op=view_area&amp;result=err&amp;of_platform=lms';
        }

        $this->setRedirect($url);
        return $this;
        
    }
}

