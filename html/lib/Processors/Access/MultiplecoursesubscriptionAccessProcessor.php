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

class MultiplecoursesubscriptionAccessProcessor extends AccessProcessor
{

    public const NAME = 'multiplecoursesubscription';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getMulitpleAccessList($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : self {
        $filteredSelection = $this->accessModel->checkSelection($selection);

        $this->setSessionData(static::NAME, [self::USER_KEY => $filteredSelection]);

        $this->setParams($this->accessModel->setMultipleAccessList($filteredSelection, ['viewParams' => true]));

        return $this;
        
    }
}