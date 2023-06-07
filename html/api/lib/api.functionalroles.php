<?php

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

require_once _base_ . '/api/lib/lib.api.php';

class FunctionalRoles_API extends API
{
    public function call($name, $params)
    {
        $output = false;

        switch ($name) {
            case 'createrole':
                $res = $this->createRole($_POST);
                if (is_array($res)) {
                    $output = $res;
                } elseif ($res > 0) {
                    $output = ['success' => true, 'id_role' => $res];
                } else {
                    $output = ['success' => false, 'message' => 'Error: unable to create new role.'];
                }
             break;
            case 'creategroup':
                $res = $this->creategroup($_POST);
                if (is_array($res)) {
                    $output = $res;
                } elseif ($res > 0) {
                    $output = ['success' => true, 'id_group' => $res];
                } else {
                    $output = ['success' => false, 'message' => 'Error: unable to create new role group.'];
                }
             break;
            case 'deleterole':
                $res = $this->deleteRole($_POST);
                if (is_array($res)) {
                    $output = $res;
                } elseif ($res > 0) {
                    $output = ['success' => true, 'deleted' => $res];
                } else {
                    $output = ['success' => false, 'message' => 'Error: unable to delete role.'];
                }
             break;
            case 'deletegroup':
                $res = $this->deleteGroup($_POST);
                if (is_array($res)) {
                    $output = $res;
                } elseif ($res > 0) {
                    $output = ['success' => true, 'deleted' => $res];
                } else {
                    $output = ['success' => false, 'message' => 'Error: unable to delete role group.'];
                }
             break;
            case 'getroles':
                $res = $this->getFunctionalRolesList($_GET);
                if (is_array($res)) {
                    $output = ['success' => true, 'roles' => $res];
                } else {
                    $output = ['success' => false, 'message' => 'Error: unable to get list roles.'];
                }
             break;
            case 'getrolegroups':
                $res = $this->getRoleGroups($_GET);
                if (is_array($res)) {
                    $output = ['success' => true, 'groups' => $res];
                } else {
                    $output = ['success' => false, 'message' => 'Error: unable to get list groups.'];
                }
             break;
            case 'adduser':
                $res = $this->addUser($_POST);
                if (is_array($res)) {
                    $output = $res;
                } elseif ($res > 0) {
                    $output = ['success' => true, 'result' => $res];
                } else {
                    $output = ['success' => false, 'message' => 'Error: unable to add user to fncrole.'];
                }
             break;

            default: $output = parent::call($name, $_POST);
        }

        return $output;
    }

    public function createRole($params)
    {
        try {
            $id_group = (isset($params['id_group'])) ? $params['id_group'] : 0;
            $langs[FormaLms\lib\Get::sett('default_language')]['name'] = (isset($params['name'])) ? $params['name'] : '';
            $langs[FormaLms\lib\Get::sett('default_language')]['description'] = (isset($params['description'])) ? $params['description'] : '';

            $functionalRole = new FunctionalrolesAdm();
            $result = $functionalRole->createFunctionalRole($id_group, $langs);
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    public function createGroup($params)
    {
        try {
            $langs[FormaLms\lib\Get::sett('default_language')]['name'] = (isset($params['name'])) ? $params['name'] : '';
            $langs[FormaLms\lib\Get::sett('default_language')]['description'] = (isset($params['description'])) ? $params['description'] : '';

            $functionalRole = new FunctionalrolesAdm();
            $result = $functionalRole->createGroup($langs);
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    public function getRoleGroups($params)
    {
        try {
            $functionalRole = new FunctionalrolesAdm();
            $result = $functionalRole->getGroupsList($params);
            /*
                        $list = $functionalRole->getGroupsList($params);
                        //prepare the data for sending
                        $output_results = array();
                        if (is_array($list) && count($list)>0) {
                                foreach ($list as $idst=>$record) {
                                        //format description field
                                        $description = strip_tags($record->description);
                                        if (strlen($description) > 200) {
                                                $description = substr($description, 0, 197).'...';
                                        }
                                        //prepare output record
                                        $output_results[] = array(
                                                'id' => $record->id_group,
                                                'name' => $record->name,
                                                'description' => $description
                                        );
                                }
                        }
            */
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    public function getFunctionalRolesList($params)
    {
        try {
            $functionalRole = new FunctionalrolesAdm();
            $result = $functionalRole->getFunctionalRolesList($params);
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    public function deleteGroup($params)
    {
        try {
            $functionalRole = new FunctionalrolesAdm();
            $id_group = $params['id_group'];
            $result = $functionalRole->deleteGroup($id_group);
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    public function deleteRole($params)
    {
        try {
            $functionalRole = new FunctionalrolesAdm();
            $id_fncrole = $params['id_role'];
            $result = $functionalRole->deleteFunctionalRole($id_fncrole);
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    public function addUser($params)
    {
        try {
            $functionalrolesadm = new FunctionalrolesAdm();
            $result = $functionalrolesadm->assignMembers($params['id_fncrole'], [0 => $params['id_user']]);
            $enrollrules = new EnrollrulesAlms();
            $enrollrules->applyRulesMultiLang('_LOG_USERS_TO_FNCROLE', [0 => $params['id_user']], false, $params['id_fncrole']);
            $result = $params['id_user'];
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }
}
