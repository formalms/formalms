<?php
namespace FormaLms\lib\Selectors\Multiuserselector;

use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\UserDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\RoleDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\GroupDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\OrgDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\DataSelector;
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

defined('IN_FORMA') or exit('Direct access is forbidden.');

class MultiUserSelector { 

    protected $dataSelectors =  array();
    protected $accessModel =  null;

    const NAMESPACE = 'FormaLms\lib\Selectors\Multiuserselector\DataSelectors\\';

    const ACCESS_MODELS = [
        'communication' => ['includes' => _lms_ . '/admin/models/CommunicationAlms.php',
                            'className' => 'CommunicationAlms'],
        'adminmanager' => ['includes' => _adm_.'/models/AdminmanagerAdm.php',
                            'className' => 'AdminmanagerAdm'
                            ]
    ];

    public function setDataSelectors(string $dataSelector, string $key) : self{

        $className = self::NAMESPACE . $dataSelector;
        try {
            $this->dataSelectors[$key] = new $className();
        } catch(Exception $e) {
            //
        }
        

        return $this;
    }

    public function getDataSelectors() : array{

        return $this->dataSelectors;
    }

    public function injectAccessModel(string $type) : self {

      
        require_once (self::ACCESS_MODELS[$type]['includes']);
        $className = self::ACCESS_MODELS[$type]['className'];
       
        $this->accessModel = new $className();
        
        return $this;
   }


   public function associate($instanceType, $instanceId, $selection) {
        switch($instanceType) {

            case 'communication':
            
                $oldSelection = $this->accessModel->accessList($instanceId);
                
                if ($this->accessModel->updateAccessList($instanceId, $oldSelection, $selection)) {
                    $redirect = 'index.php?r=alms/communication/show&success=1';
                } else {
                    $redirect = 'index.php?r=alms/communication/show&error=1';
                }
            
                break;

            case 'adminmanager':
        
                if ($this->accessModel->saveUsersAssociation($instanceId, $selection)) {
                    $redirect = 'index.php?r=adm/adminmanager/show&res=ok_ins';
                } else {
                    $redirect = 'index.php?r=adm/adminmanager/show&res=err_ins';
                }
            
                break;
        }

        return $redirect;
   }


   public function getaccessList($instanceType, $instanceId) {
        switch($instanceType) {

            case 'communication':
            
                $selection = $this->accessModel->accessList($instanceId);
            
                break;
            
            case 'adminmanager':
        
                $selection = $this->accessModel->loadUserSelectorSelection($instanceId);
            
                break;
        }

        return $selection;
    }


   public function getAccessModel() : object {
       return $this->accessModel;
   }

    public function retrieveDataSelector($key) : ?DataSelector{

        return $this->dataSelectors[$key];
    }

   
    
}
