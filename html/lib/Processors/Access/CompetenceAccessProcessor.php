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

class CompetenceAccessProcessor extends AccessProcessor
{

    public const NAME = 'competence';

    public function getAccessList($resourceId) : array {

        return $this->accessModel->getAccessList($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : self {

        $info = $this->accessModel->getCompetenceInfo($resourceId);
       
        $this->accessModel->setInfoType($info->type);
        $resultFlag = $this->accessModel->setAccessList($resourceId, $selection);
    
        //if no users to add: check removed users (if any) then go back
        if (!$resultFlag) {
       
            $this->setReturnType('redirect');
            
            $message = $this->accessModel->getResponseForAccessor();
            $this->setRedirect('index.php?r=adm/competences/show_users&id=' . (int) $resourceId . '&res=' . $message);

        } else {

            if ($info->type == 'score') {
        
                $viewParams = $this->accessModel->getAssociationView($resourceId, $this->accessModel->getNewUsers());
                $this->setParams(array_merge($viewParams, [
                                                        'type' => $info->type,
                                                        'form_url' => 'index.php?r=adm/competences/assign_users_action',
                                                        'del_selection' => implode(',', $this->accessModel->getOldUsers()),
                                                        ]));
            } else {
                $this->setReturnType('redirect');
              
                $message = $this->accessModel->getResponseForAccessor();
                $this->setRedirect('index.php?r=adm/competences/show_users&id=' . (int) $resourceId . '&res=' . $message);
        
            }
        }

        return $this;
    }
}