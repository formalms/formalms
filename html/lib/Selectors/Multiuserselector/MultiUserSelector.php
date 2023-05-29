<?php

namespace FormaLms\lib\Selectors\Multiuserselector;

use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\UserDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\RoleDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\GroupDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\OrgDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\DataSelector;
use FormaLms\lib\Processors\Access\CommunicationAccessProcessor;
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
    protected $accessProcessor = null;
    protected $db;

    protected $requestParams = [];

    public const NAMESPACE = 'FormaLms\lib\Selectors\Multiuserselector\DataSelectors\\';

    public const PROCESSOR_NAMESPACE = 'FormaLms\lib\Processors\Access\\';

    public const PROCESSOR_SUFFIX = 'AccessProcessor';

    public const ALL_USER_ACCESS = 1; //è l'idst che corrisponde al nodo master di org di forma che non è selezionabile in alcun modo dallo user selector e solo tramite il checkbox apposito



    public function __construct(array $requestParams)
    {
        $this->db =\FormaLms\db\DbConn::getInstance();
        $this->requestParams = $requestParams;

    }

    public function setDataSelectors(string $dataSelector, string $key): self
    {
        $className = self::NAMESPACE . $dataSelector;
        try {
            $this->dataSelectors[$key] = new $className();
        } catch(\Exception $e) {
            dd($e);
        }

        return $this;
    }

    public function setAccessProcessor(string $type) : self {

        $className = self::PROCESSOR_NAMESPACE . ucfirst($type) . self::PROCESSOR_SUFFIX;

        try {
            $this->accessProcessor = new $className($this->requestParams);
        } catch(\Exception $e) {
            dd($e);
        }

        return $this;

    }

    public function getDataSelectors(): array
    {
        return $this->dataSelectors;
    }

   public function associate($instanceId, $selection)
   {

        $return = $this->accessProcessor->applyAssociation($instanceId, $selection);

//
    //        case 'competence':
//
    //            $acl_man = \FormaLms\lib\Forma::getAclManager();
    //            $_new_users = [];
    //            $users_selected = $acl_man->getAllUsersFromIdst($selection);
    //            $competence_users = $this->accessModel->getCompetenceUsers($instanceId, true);
    //            $users_existent = array_keys($competence_users);
    //            $info = $this->accessModel->getCompetenceInfo($instanceId);
    //            //retrieve newly selected users
    //            $_common_users = array_intersect($users_existent, $users_selected);
    //            $_new_users = array_diff($users_selected, $_common_users);
    //            $_old_users = array_diff($users_existent, $_common_users);
    //            unset($_common_users); //free some memory
    //
    //            //if no users to add: check removed users (if any) then go back
    //            if (empty($_new_users)) {
    //                $res = $this->accessModel->removeCompetenceUsers($instanceId, $_old_users, true);
    //                $return['type'] = 'redirect';
    //                $message = $res ? 'ok_assign' : 'err_assign';
    //                $return['redirect'] = 'index.php?r=adm/competences/show_users&id=' . (int) $instanceId . '&res=' . $message;
 //
    //            } else {
//
    //                if ($info->type == 'score') {
    //                    $moreParams['viewParams'] = true;
    //                    $viewParams = $this->accessModel->getAssociationView($instanceId,$_new_users);
    //                    $return['params'] =  array_merge($viewParams, [
    //                                                            'type' => $info->type,
    //                                                            'form_url' => 'index.php?r=adm/competences/assign_users_action',
    //                                                            'del_selection' => implode(',', $_old_users),
    //                                                            ]);
    //                    
    //                    $return['subFolderView'] = self::ACCESS_MODELS[$instanceType]['subFolderView'] ?? '';
    //                    $return['additionalPaths'] = self::ACCESS_MODELS[$instanceType]['additionalPaths'] ?? [];
    //                    $return['view'] = self::ACCESS_MODELS[$instanceType]['returnView'];
    //                } else {
    //                    $return['type'] = 'redirect';
    //                    $data = [];
    //                    foreach ($_new_users as $id_user) {
    //                        $data[$id_user] = 1;
    //                    }
    //                    $res1 = $this->accessModel->assignCompetenceUsers($instanceId, $data, true);
    //                    $res2 = $this->accessModel->removeCompetenceUsers($instanceId, $_old_users, true);
    //                    $message = $res1 && $res2 ? 'ok_assign' : 'err_assign';
    //                    $return['redirect'] = ' index.php?r=adm/competences/show_users&id=' . (int) $instanceId . '&res=' . $message;
    //           
    //                }
    //            }
    //
    //            break;
//
//
    //            case "role":
    //                $acl_man = \FormaLms\lib\Forma::getAclManager();
//
    //    
    //                $members_existent = $this->accessModel->getMembers($instanceId);
    //    
    //                //retrieve newly selected users
    //                $_common_members = array_intersect($members_existent, $selection);
    //                $_new_members = array_diff($selection, $_common_members); //new users to add
    //                $_old_members = array_diff($members_existent, $_common_members); //old users to delete
    //                unset($_common_members); //free some memory
    //    
    //                //insert newly selected users in database
    //                $res1 = $this->accessModel->assignMembers($instanceId, $_new_members);
    //                $res2 = $this->accessModel->deleteMembers($instanceId, $_old_members);
    //    
    //                $this->accessModel->enrole($instanceId, $_new_members);
    //            
    //                //go back to main page, with result message
    //                $return['redirect'] = 'index.php?r=adm/functionalroles/man_users&id=' . $instanceId .'&res=' . ($res1 && $res2 ? 'ok_users' : 'err_users');
//
    //                break;
//
    //            case 'group':
    //    
    //                $res = $this->accessModel->saveGroupMembers($instanceId, $selection);
    //                $this->accessModel->enrole($instanceId, $selection);
    //
    //                $return['redirect'] = 'index.php?r=adm/groupmanagement/show_users&id='.$instanceId . ($res ? '&res=ok_assignuser' : '&res=err_assignuser');
    //
    //                break;
    //   }

       return $return;
   }


   public function getAccessList($instanceType, $instanceId, $parsing = false)
   {

        $selection = $this->accessProcessor->getAccessList($instanceId);


    //        case 'competence':
//
    //            $selection = $this->accessModel->getCompetenceUsers($instanceId);
    //
    //            break;
//
    //        case 'role':
    //            
    //            $selection = $this->accessModel->getMembers($instanceId);
//
    //            break;
//
    //        case 'group':
    //        
    //            $selection = $this->accessModel->getGroupMembers($instanceId);
//
    //            break;
    //        
    //        
    //   }


       if ($parsing) {
           $selection = $this->parseSelection($selection);
       }

       return $selection;
   }


   public function getAccessProcessor(): object
   {
       return $this->accessProcessor;
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

        $results = $this->db->query($query) ?? [];

        if ($results) {
            foreach ($results as $result) {
                $selection[$result['selector']] = $result['ids'] ? explode(',', $result['ids']) : [];
            }
        }

        return $selection;
    }


}
