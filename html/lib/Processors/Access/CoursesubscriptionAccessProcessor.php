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

class CoursesubscriptionAccessProcessor extends AccessProcessor
{

    public const NAME = 'coursesubscription';

    public const TYPE = 'course';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getSubscribed($resourceId, static::TYPE);
    }

    public function setAccessList($resourceId, array $selection) : self {

        $this->setParams($this->accessModel->add($selection, static::TYPE, (int) $resourceId, ['viewParams' => true]));

        return $this;
        
    }
}