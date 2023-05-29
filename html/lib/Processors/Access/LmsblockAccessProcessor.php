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

class LmsblockAccessProcessor extends AccessProcessor
{

    public const NAME = 'lmsblock';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getAccessList($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : self {


        if ($this->accessModel->setAccessList($resourceId, $selection)) {
            $url  = 'index.php?modname=middlearea&amp;op=view_area&amp;of_platform=lms&amp;result=ok';
        } else {
            $url  = 'index.php?modname=middlearea&amp;op=view_area&amp;of_platform=lms&amp;result=err';
        }

        $this->setRedirect($url);
        return $this;
        
    }
}