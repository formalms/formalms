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

class CoursesubscriptionAccessProcessor extends AccessProcessor
{

    public const NAME = 'coursesubscription';

    public int $instanceId;

    public int $courseId;

    public string $type = 'course';

    public function getAccessList($resourceId) : array {

        return $this->parseSegments($resourceId, true);

    }

    public function setAccessList($resourceId, array $selection) : self {
        $this->parseSegments($resourceId);
      
        $this->setParams($this->accessModel->add($selection, $this->type, (int) $this->instanceId, ['viewParams' => true, 'courseId' => $this->courseId]));

        return $this;
        
    }

    public function parseSegments($resourceId, $return = false) {

        $segments = explode("_", $resourceId);

        $this->instanceId = $this->courseId = $segments[0];

        if(count($segments) > 1) {

            $parts = explode('@', $segments[1]);
            $this->type = $parts[0];
            $this->instanceId = $parts[1];
        }

        if($return) {

            return $this->accessModel->getSubscribed($this->instanceId, $this->type);
        }

    }
}