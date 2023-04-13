<?php

namespace FormaLms\lib\Selectors\Multiuserselector;

use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\UserDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\RoleDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\GroupDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\OrgDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\DataSelector;
use FormaLms\lib\Services\Courses\CourseSubscriptionService;

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

class MultiUserSelector
{
    protected $dataSelectors = array();
    protected $accessModel = null;
    protected $db;
    protected $session;


    public const SESSION_KEY = 'selectedUsers';

    public const NAMESPACE = 'FormaLms\lib\Selectors\Multiuserselector\DataSelectors\\';

    public const ALL_USER_ACCESS = 1; //è l'idst che corrisponde al nodo master di org di forma che non è selezionabile in alcun modo dallo user selector e solo tramite il checkbox apposito

    public const ACCESS_MODELS = [
        'communication' => ['includes' => _lms_ . '/admin/models/CommunicationAlms.php',
                            'className' => 'CommunicationAlms', 'returnType' => 'redirect'],
        'adminmanager' => ['includes' => _adm_.'/models/AdminmanagerAdm.php',
                            'className' => 'AdminmanagerAdm','returnType' => 'redirect'],
        'lmsmenu' => ['includes' => _lms_ . '/admin/models/LmsMenuAlms.php',
                            'className' => 'LmsMenuAlms', 'returnType' => 'redirect'],
        'coursesubscription' => ['includes' => 'FormaLms\lib\Services\Courses\\' ,
                            'className' => 'CourseSubscriptionService',
                            'returnType' => 'render',
                            'returnView' => 'level',
                            'subFolderView' => 'subscription',
                            'additionalPaths' => [_lms_.'/admin/views'],
                            'use_namespace' =>  true],
        'multiplecoursesubscription' => ['includes' => 'FormaLms\lib\Services\Courses\\' ,
                            'className' => 'CourseSubscriptionService',
                            'returnType' => 'render',
                            'returnView' => 'multiple_subscription_2',
                            'subFolderView' => 'subscription',
                            'additionalPaths' => [_lms_.'/admin/views'],
                            'use_namespace' =>  true],
        'lmstab' => ['includes' => _lms_ . '/lib/lib.middlearea.php',
                    'className' => 'Man_MiddleArea', 'returnType' => 'redirect'],
        'lmsblock' => ['includes' => _lms_ . '/lib/lib.middlearea.php',
                    'className' => 'Man_MiddleArea', 'returnType' => 'redirect'],
        'dashboardsetting' => ['includes' => _adm_.'/models/DashboardsettingsAdm.php',
                    'className' => 'DashboardsettingsAdm','returnType' => 'redirect'],
    ];



    public function __construct()
    {
        $this->db =\FormaLms\db\DbConn::getInstance();
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function setDataSelectors(string $dataSelector, string $key): self
    {
        $className = self::NAMESPACE . $dataSelector;
        try {
            $this->dataSelectors[$key] = new $className();
        } catch(\Exception $e) {
            //
        }


        return $this;
    }

    public function getDataSelectors(): array
    {
        return $this->dataSelectors;
    }

    public function injectAccessModel(string $type): self
    {
        if (self::ACCESS_MODELS[$type]['use_namespace']) {
            $className = self::ACCESS_MODELS[$type]['includes'].self::ACCESS_MODELS[$type]['className'];
        } else {
            require_once(self::ACCESS_MODELS[$type]['includes']);
            $className = self::ACCESS_MODELS[$type]['className'];
        }

        $this->accessModel = new $className();

        return $this;
    }


   public function associate($instanceType, $instanceId, $selection)
   {
       $return['type'] = self::ACCESS_MODELS[$instanceType]['returnType'];
       $moreParams = [];
       switch($instanceType) {
           case 'communication':

               $oldSelection = $this->accessModel->accessList($instanceId);

               if ($this->accessModel->updateAccessList((int)  $instanceId, $oldSelection, $selection)) {
                   $return['redirect']  = 'index.php?r=alms/communication/show&success=1';
               } else {
                   $return['redirect']  = 'index.php?r=alms/communication/show&error=1';
               }

               break;

           case 'adminmanager':

               if ($this->accessModel->saveUsersAssociation((int) $instanceId, $selection)) {
                   $return['redirect'] = 'index.php?r=adm/adminmanager/show&res=ok_ins';
               } else {
                   $return['redirect']  = 'index.php?r=adm/adminmanager/show&res=err_ins';
               }

               break;

           case 'lmsmenu':

               $oldSelection = $this->accessModel->getRoleMemebers((int) $instanceId);

               if ($this->accessModel->saveMembersAssociation($instanceId, $selection, $oldSelection)) {
                   $return['redirect']  = 'index.php?modname=middlearea&amp;op=view_area&amp;result=ok&amp;of_platform=lms';
               } else {
                   $return['redirect'] = 'index.php?modname=middlearea&amp;op=view_area&amp;result=err&amp;of_platform=lms';
               }

               break;

           case 'coursesubscription':

               $moreParams['viewParams'] = true;

               $return['params'] = $this->accessModel->add($selection, 'course', (int) $instanceId, $moreParams);
               $return['subFolderView'] = self::ACCESS_MODELS[$instanceType]['subFolderView'] ?? '';
               $return['additionalPaths'] = self::ACCESS_MODELS[$instanceType]['additionalPaths'] ?? [];
               $return['view'] = self::ACCESS_MODELS[$instanceType]['returnView'];


               break;

           case 'multiplecoursesubscription':

               $moreParams['viewParams'] = true;
               $filteredSelection = $this->accessModel->checkSelection($selection, $moreParams);


               $this->setSessionData($instanceType, $filteredSelection);
               $return['params'] =  $this->accessModel->multipleAdd($filteredSelection, $moreParams);
               ;
               $return['subFolderView'] = self::ACCESS_MODELS[$instanceType]['subFolderView'] ?? '';
               $return['additionalPaths'] = self::ACCESS_MODELS[$instanceType]['additionalPaths'] ?? [];
               $return['view'] = self::ACCESS_MODELS[$instanceType]['returnView'];


               break;

           case 'dashboardsetting':

               $result = $this->accessModel->setObjIdstList((int) $instanceId, $selection);

               $return['redirect'] = 'index.php?r=adm/dashboardsettings/show&result=' . ($result ? 'ok' : 'err');

               break;

           case 'lmsblock':
           case 'lmstab':

               $result = $this->accessModel->setObjIdstList($instanceId, $selection);

               $return['redirect'] = 'index.php?modname=middlearea&amp;op=view_area&amp;of_platform=lms&amp;result=' . ($result ? 'ok' : 'err');

               break;
       }

       return $return;
   }


   public function getAccessList($instanceType, $instanceId, $parsing = false)
   {
       switch($instanceType) {
           case 'communication':

               $selection = $this->accessModel->accessList($instanceId);

               break;

           case 'adminmanager':

               $selection = $this->accessModel->loadUserSelectorSelection($instanceId);

               break;

           case 'lmsmenu':

               $selection = $this->accessModel->getRoleMembers($instanceId);

               break;

           case 'multiplecoursesubscription':

               //handled by session

               break;


           case 'coursesubscription':

               $selection = $this->accessModel->getSubscribed($instanceId, 'course');

               break;

           case 'dashboardsetting':

               $selection = $this->accessModel->getObjIdstList($instanceId);

               break;

           case 'lmsblock':
           case 'lmstab':

               $selection = $this->accessModel->getObjIdstList($instanceId);

               break;
       }


       if ($parsing) {
           $selection = $this->parseSelection($selection);
       }

       return $selection;
   }


   public function getAccessModel(): object
   {
       return $this->accessModel;
   }

   public function getSelectedAllValue(): int
   {
       return self::ALL_USER_ACCESS;
   }

    public function retrieveDataSelector($key): ?DataSelector
    {
        return $this->dataSelectors[$key];
    }


    public function parseSelection($selectedIds)
    {
        $selection = [];
        $selectString = implode(",", $selectedIds);
        $query = 'SELECT
                    GROUP_CONCAT( DISTINCT(coretables.idst) ) AS ids,
                    nametables.table_name AS selector
                        FROM
                        (
                            SELECT
                                idst,
                                "user" AS table_name 
                            FROM
                                core_user 
                            WHERE
                                idst IN ( ' . $selectString . ' ) UNION ALL
                            SELECT
                                idst,
                                "role" AS table_name 
                            FROM
                                core_role 
                            WHERE
                                idst IN ( ' . $selectString . ' ) UNION ALL
                            SELECT
                                idst,
                                "org" AS table_name 
                            FROM
                                core_group 
                            WHERE
                                idst IN ( ' . $selectString . ' ) 
                                AND groupid LIKE \'%/oc%\' UNION ALL
                            SELECT
                                idst,
                                "group" AS table_name 
                            FROM
                                core_group 
                            WHERE
                                idst IN ( ' . $selectString . ') 
                                AND groupid NOT LIKE \'%/oc%\' 
                                ) coretables
                    RIGHT JOIN (
                            SELECT
                                "user" AS table_name 
                            FROM
                                core_user UNION 
                            SELECT
                                "role" AS table_name 
                            FROM
                                core_role UNION 
                            SELECT
                                "org" AS table_name 
                            FROM
                                core_group 
                            WHERE
                                groupid LIKE \'%/oc%\' UNION 
                            SELECT
                                "group" AS table_name 
                            FROM
                                core_group 
                            WHERE
                                groupid NOT LIKE \'%/oc%\' 
                                ) nametables ON nametables.table_name = coretables.table_name 
                            GROUP BY
                                nametables.table_name';

        $results = $this->db->query($query);

        if ($results) {
            foreach ($results as $result) {
                $selection[$result['selector']] = $result['ids'] ? explode(',', $result['ids']) : [];
            }
        }

        return $selection;
    }


    public function getSessionData(string $instance): array
    {
        return $this->session->get($instance . '_' . self::SESSION_KEY) ? $this->parseSelection($this->session->get($instance . '_' . self::SESSION_KEY)) : [];
    }


    public function setSessionData(string $instance, array $selection): bool
    {
        $this->session->set($instance . '_' . self::SESSION_KEY, $selection);
        $this->session->save();

        return true;
    }
}
