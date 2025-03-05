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

defined('IN_FORMA') or exit('Direct access is forbidden.');

use FormaLms\lib\Interfaces\Accessible;
use FormaLms\lib\Session\SessionManager;

class GroupmanagementAdm extends Model implements Accessible
{
    protected $db;
    protected $acl_man;

    public function __construct()
    {
        $this->db = \FormaLms\db\DbConn::getInstance();
        $this->acl_man = \FormaLms\lib\Forma::getAclManager();;
        parent::__construct();
    }

    public function getPerm()
    {
        return [
            'view' => 'standard/view.png',
            'add' => 'standard/add.png',
            'mod' => 'standard/edit.png',
            'del' => 'standard/delete.png',
            'associate_user' => 'standard/moduser.png',
        ];
    }

    public function getGroupsList($pagination = [], $filter = false, $learning_filter = 'none', $columnsFilter = [])
    {
        if (!is_array($pagination)) {
            $pagination = [];
        }

        $admin_tree = [];
        $res = [];

        $startIndex = (isset($pagination['startIndex']) ? $pagination['startIndex'] : 0);
        $results = (isset($pagination['results']) ? $pagination['results'] : FormaLms\lib\Get::sett('visuItem', 25));

        $sort = 'g.groupid';
        if (isset($pagination['sort'])) {
            switch ($pagination['sort']) {
                case 'description':
                    $sort = 'g.description';
                    break;
                //case 'usercount': $sort = 'usercount'; break;
                default:
                    $sort = 'g.groupid';
            }
        }

        $dir = 'ASC';
        if (isset($pagination['dir'])) {
            switch ($pagination['dir']) {
                case 'yui-dt-asc':
                    $dir = 'ASC';
                    break;
                case 'yui-dt-desc':
                    $dir = 'DESC';
                    break;
                case 'asc':
                    $dir = 'ASC';
                    break;
                case 'desc':
                    $dir = 'DESC';
                    break;
                default:
                    $dir = 'ASC';
            }
        }

        $query = 'SELECT g.idst, g.groupid, g.description, COUNT(*) as usercount '
            . ' FROM %adm_group as g LEFT JOIN (%adm_group_members AS gm ) ON (gm.idst = g.idst) '
            . " WHERE g.hidden = 'false' " . ($learning_filter === 'none' ? "AND g.type <> 'course' " : '');

        $ulevel = \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId();
        if ($ulevel != ADMIN_GROUP_GODADMIN) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $admin_tree = $adminManager->getAdminTree(\FormaLms\lib\FormaUser::getCurrentUser()->getIdST());
         
        }

        if ($filter) {
            $query .= " AND (g.groupid LIKE '%" . $filter . "%' OR g.description LIKE '%" . $filter . "%') ";
        }

        if(count($columnsFilter) && !$filter) {
            foreach($columnsFilter as $columnName => $columnValue) {
                $query .= ' AND (
                    g.' .$columnName . ' LIKE "%' . $columnValue . '%" 
                )';
            }

        }
        $session = SessionManager::getInstance()->getSession();

        switch ($learning_filter) {
            case 'message':
                $id_course = $session->get('message_filter');

                if ($id_course != 0) {
                    $res = $this->acl_man->getGroupsIdstFromBasePath('/lms/course/' . $id_course . '/group/');
                } else {
                    require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.course.php');
                    $course_man = new Man_Course();
                    $all_courses = $course_man->getUserCourses(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());
                    $res = [];
                    foreach ($all_courses as $id_course => $name) {
                        $arr_idst_group = $this->acl_man->getGroupsIdstFromBasePath('/lms/course/' . $id_course . '/group/');
                        $res = array_merge($res, $arr_idst_group);
                    }
                }

                break;
            case 'course':
                $id_course = $session->get('idCourse');

                $res = $this->acl_man->getGroupsIdstFromBasePath('/lms/course/' . $id_course . '/group/');

             
                
                break;
        }

        if(!empty($res) || !empty($admin_tree)) {
            $res = array_merge($res,$admin_tree);
            $query .= ' AND g.idst IN (' . implode(',', $res) . ') ';
        } 

        $query .= ' GROUP BY g.idst ';
        $query .= ' ORDER BY ' . $sort . ' ' . $dir . ' ';
        $query .= 'LIMIT ' . $startIndex . ', ' . $results;

        $res = $this->db->query($query);

        if ($res) {
            $output = [];
            $glist = [];
            while ($obj = $this->db->fetch_obj($res)) {
                $groupId = explode('/', $obj->groupid);
                $obj->membercount = 0;
                $obj->usercount = 0;
                $obj->groupid = end($groupId);
                $output[$obj->idst] = $obj;
            }

            $list_idst = array_keys($output);
            $count_members = $this->countMembers($list_idst);
            $count_users = $this->countUsers($list_idst);
            if (!empty($count_members)) {
                foreach ($count_members as $idst => $count) {
                    $output[$idst]->membercount = $count;
                }
            }
            if (!empty($count_users)) {
                foreach ($count_users as $idst => $count) {
                    $output[$idst]->usercount = $count;
                }
            }
        } else {
            return false;
        }

        return array_values($output);
    }

    public function getTotalGroups($filter = false, $learning_filter = 'none', $columnsFilter = [])
    {
        $query = 'SELECT COUNT(*) '
            . " FROM %adm_group as g WHERE g.hidden = 'false' " . ($learning_filter === 'none' ? "AND g.type <> 'course' " : '');

        switch ($learning_filter) {
            case 'message':
                $id_course = $this->session->get('message_filter');

                if ($id_course != 0) {
                    $res = $this->acl_man->getGroupsIdstFromBasePath('/lms/course/' . $id_course . '/group/');
                } else {
                    require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.course.php');
                    $course_man = new Man_Course();
                    $all_courses = $course_man->getUserCourses(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());
                    $res = [];
                    foreach ($all_courses as $id_course => $name) {
                        $arr_idst_group = $this->acl_man->getGroupsIdstFromBasePath('/lms/course/' . $id_course . '/group/');
                        $res = array_merge($res, $arr_idst_group);
                    }
                }

                $query .= ' g.idst IN (' . implode(',', $res) . ') ';
                break;
            case 'course':
                $id_course = $this->session->get('idCourse');

                $res = $this->acl_man->getGroupsIdstFromBasePath('/lms/course/' . $id_course . '/group/');

                $query .= ' AND g.idst IN (' . implode(',', $res) . ') ';
                break;
            default:
                break;
        }

        if ($filter) {
            $query .= " AND (g.groupid LIKE '%" . $filter . "%' OR g.description LIKE '%" . $filter . "%') ";
        }

        if(count($columnsFilter) && !$filter) {
            foreach($columnsFilter as $columnName => $columnValue) {
                $query .= ' AND (
                    g.' .$columnName . ' LIKE "%' . $columnValue . '%" 
                )';
            }

        }

        $res = $this->db->query($query);
        list($count) = $this->db->fetch_row($res);

        return $count;
    }

    public function getAllGroups($filter, $admin_filter = false, $columnsFilter = [])
    {
        $query = "SELECT idst FROM %adm_group as g WHERE g.hidden = 'false' AND g.type <> 'course' ";
        if ($filter) {
            $query .= " AND (g.groupid LIKE '%" . $filter . "%' OR g.description LIKE '%" . $filter . "%') ";
        }

        if(count($columnsFilter)) {
            foreach($columnsFilter as $columnName => $columnValue) {
                $query .= ' AND (
                    g.' .$columnName . ' LIKE "%' . $columnValue . '%" 
                )';
            }

        }
        $res = $this->db->query($query);
        $output = false;
        if ($res) {
            $output = [];
            while (list($id_group) = $this->db->fetch_row($res)) {
                $output[] = $id_group;
            }
        }

        return $output;
    }

    public function getAllGroupDetails($ids = [])
    {
        $query = 'SELECT g.idst, g.groupid, g.description, COUNT(*) as usercount FROM %adm_group as g WHERE g.idst in (' . implode(',', $ids) . ')
        GROUP BY g.idst';

        $res = $this->db->query($query);
        if ($res) {
            $output = [];
            $glist = [];
            while ($obj = $this->db->fetch_obj($res)) {
                $obj->membercount = 0;
                $obj->usercount = 0;
                $obj->groupid = end(explode('/', $obj->groupid));
                $output[$obj->idst] = $obj;
            }

            $list_idst = array_keys($output);
            $count_members = $this->countMembers($list_idst);
            $count_users = $this->countUsers($list_idst);
            if (!empty($count_members)) {
                foreach ($count_members as $idst => $count) {
                    $output[$idst]->membercount = $count;
                }
            }
            if (!empty($count_users)) {
                foreach ($count_users as $idst => $count) {
                    $output[$idst]->usercount = $count;
                }
            }
        } else {
            return false;
        }

        return array_values($output);
    }

    public function deleteGroup($idst)
    {
        $output = false;

        $info_group = $this->getGroupInfo($idst);

        Events::trigger('core.group.deleting', ['id_group' => $idst, 'info_group' => $info_group]);

        $query = 'DELETE FROM %adm_group WHERE idst=' . (int) $idst . ' LIMIT 1';
        $res = $this->db->query($query);

        if ($res) {
            /*
            $query = "DELETE FROM %adm_group_members WHERE idst=".(int)$idst." OR idstMember=".(int)$idst;
            $res = $this->db->query($query);
            if ($res) {
                $output = true;
            }
            */
            if ($this->deleteGroupMembers($idst)) {
                $output = true;
            }
        }

        Events::trigger('core.group.deleted', ['id_group' => $idst, 'info_group' => $info_group]);

        return $output;
    }

    public function deleteGroupMembers($idst)
    {
        $output = false;

        if ($idst > 0) {
            $query = 'DELETE FROM %adm_group_members WHERE idst=' . (int) $idst . ' OR idstMember=' . (int) $idst;
            $output = $this->db->query($query);
        }

        return $output;
    }

    public function getGroupInfo($idst, $obj = false)
    {
        $output = false;

        $query = 'SELECT * FROM %adm_group WHERE idst=' . (int) $idst;
        $res = $this->db->query($query);

        if ($res && $this->db->num_rows($res) > 0) {
            if ($obj) {
                $output = $this->db->fetch_obj($res);
            } else {
                $output = $this->db->fetch_assoc($res);
            }
        }

        return $output;
    }

    public function saveGroupInfo($idst, $info)
    {
        $output = false;

        Events::trigger('core.group.editing', ['id_group' => $idst, 'info_group' => $info]);

        if ($idst > 0 && (is_array($info) || is_object($info))) {
            $output = true;
            $conditions = [];
            $acl = \FormaLms\lib\Forma::getAclManager();

            if (is_array($info)) {
                if (isset($info['groupid'])) {
                    $conditions[] = " groupid='" . $acl->absoluteId($info['groupid']) . "' ";
                }
                if (isset($info['description'])) {
                    $conditions[] = " description='" . $info['description'] . "' ";
                }
                if (isset($info['type'])) {
                    $conditions[] = " type='" . $info['type'] . "' ";
                }
                if (isset($info['show_on_platform'])) {
                    $conditions[] = " show_on_platform='" . $info['show_on_platform'] . "' ";
                }
            }

            if (is_object($info)) {
                if (isset($info->groupid)) {
                    $conditions[] = " groupid='" . $acl->absoluteId($info->groupid) . '" ';
                }
                if (isset($info->description)) {
                    $conditions[] = " description='" . $info->description . "' ";
                }
                if (isset($info->type)) {
                    $conditions[] = " type='" . $info->type . "' ";
                }
                if (isset($info->show_on_platform)) {
                    $conditions[] = " show_on_platform='" . $info->show_on_platform . "' ";
                }
            }

            if (count($conditions) > 0) {
                $query = 'UPDATE %adm_group SET ' . implode(',', $conditions) . ' WHERE idst=' . (int) $idst;
                $output = $this->db->query($query);
            }
        }

        Events::trigger('core.group.edited', ['id_group' => $idst, 'info_group' => $info]);

        return $output;
    }

    public function createGroup($info)
    {
        $output = false;

        Events::trigger('core.group.creating', ['info_group' => $info]);

        if (is_array($info) || is_object($info)) {
            if (is_array($info)) {
                if (isset($info['groupid']) && $info['groupid'] == '') {
                    return false;
                }
            }
            if (is_object($info)) {
                if (isset($info->groupid) && $info->groupid == '') {
                    return false;
                }
            }

            $res = $this->db->query('INSERT INTO %adm_st (idst) VALUES (NULL)');
            if ($res) {
                $idst = $this->db->insert_id();
                if ($idst <= 0) {
                    return false;
                }
            } else {
                return false;
            }

            $output = true;
            $fields = ['idst'];
            $values = [$idst];
            $acl = \FormaLms\lib\Forma::getAcl()->getAclManager();

            if (is_array($info)) {
                if (isset($info['groupid'])) {
                    $fields[] = 'groupid';
                    $values[] = "'" . $acl->absoluteId($info['groupid']) . "'";
                }
                if (isset($info['description'])) {
                    $fields[] = 'description';
                    $values[] = "'" . $info['description'] . "'";
                }
                if (isset($info['type'])) {
                    $fields[] = 'type';
                    $values[] = "'" . $info['type'] . "'";
                }
                if (isset($info['show_on_platform'])) {
                    $fields[] = 'show_on_platform';
                    $values[] = "'" . $info['show_on_platform'] . "'";
                }
            }

            if (is_object($info)) {
                if (isset($info->groupid)) {
                    $fields[] = 'groupid';
                    $values[] = "'" . $acl->absoluteId($info->groupid) . "'";
                }
                if (isset($info->description)) {
                    $fields[] = 'description';
                    $values[] = "'" . $info->description . "'";
                }
                if (isset($info->type)) {
                    $fields[] = 'type';
                    $values[] = "'" . $info->type . "'";
                }
                if (isset($info->show_on_platform)) {
                    $fields[] = 'show_on_platform';
                    $values[] = "'" . $info->show_on_platform . "'";
                }
            }

            if (count($fields) > 0) {
                $query = 'INSERT INTO %adm_group (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';
                $output = $this->db->query($query);
            }

            $ulevel = \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId();
            if ($ulevel == ADMIN_GROUP_ADMIN) {
                require_once _base_ . '/lib/lib.preference.php';
                $preference = new AdminPreference();
                $user_id = (int) $this->session->get('public_area_idst');
                $preference->addAdminTree($idst, $user_id);
            }
        }

        Events::trigger('core.group.created', ['id_group' => $idst, 'info_group' => $info]);

        return $output;
    }

    public function getGroupMembers($idst)
    {
        $output = false;

        $query = 'SELECT idstMember FROM %adm_group_members WHERE idst=' . (int) $idst;
        $res = $this->db->query($query);

        if ($res) {
            $output = [];
            while (list($member) = $this->db->fetch_row($res)) {
                $output[] = $member;
            }
        }

        return $output;
    }

    public function getGroupAllUsers($idst)
    {
        return $this->acl_man->getAllUsersFromSelection($this->getGroupMembers($idst));
    }

    public function saveGroupMembers($idst, $members)
    {
        //validate parameters
        if ($idst <= 0) {
            return false;
        }
        if (is_numeric($members)) {
            $members = [$members];
        }
        if (!is_array($members)) {
            return false;
        }
        if (empty($members)) {
            return true;
        }

        //filter and validate members - group can't have hisself as member, members cannot be fncroles
        $fmodel = new FunctionalrolesAdm();
        $fncroles = $fmodel->getAllFunctionalRoles();
        if (!empty($fncroles)) {
            $members = array_diff($members, $fncroles);
        }

        //delete old members
        $res = $this->deleteGroupMembers($idst);
        if (!$res) {
            return false;
        }

        //write new members
        if (count($members) > 0) {
            $insert_list = [];
            foreach (array_unique($members) as $member) {
                if (is_numeric($member) && $member > 0 && $member != $idst) {
                    $insert_list[] = '(' . (int) $idst . ', ' . (int) $member . ')';
                }
            }
            if (count($insert_list) > 0) {
                $query = 'INSERT INTO %adm_group_members (idst, idstMember) VALUES ' . implode(',', $insert_list);
                $res = $this->db->query($query);
            }
        }

        Events::trigger('core.group_member.assigned', ['idst' => $idst, 'members' => $members]);

        return $res;
    }

    public function getGroupTypes($no_translation = false)
    {
        $output = [
            'free' => $no_translation ? 'free' : Lang::t('_DIRECTORY_GROUPTYPE_FREE', 'admin_directory'),
            'moderate' => $no_translation ? 'moderate' : Lang::t('_DIRECTORY_GROUPTYPE_MODERATE', 'admin_directory'),
            'selected' => $no_translation ? 'selected' : Lang::t('_DIRECTORY_GROUPTYPE_PRIVATE', 'admin_directory'),
            'invisible' => $no_translation ? 'invisible' : Lang::t('_DIRECTORY_GROUPTYPE_INVISIBLE', 'admin_directory'),
        ];

        return $output;
    }

    /*
        protected function _extractGroupsFromMixedIdst($arr_idst) {
            $output = array();
            $query = "SELECT idst FROM %adm_group WHERE idst IN (".$arr_idst.") AND hidden='false' AND type<>'course'";
            $res = $this->db->query($query);
            if ($res) while (list($idst) = $this->db->fetch_row($res)) $output[] = $idst;
            return $output;
        }
    */

    public function searchGroupsByGroupid($query, $limit = false, $filter = false)
    {
        if ((int) $limit <= 0) {
            $limit = FormaLms\lib\Get::sett('visuItem', 25);
        }
        $output = [];

        $_qfilter = '';
        if ($filter) {
            $ulevel = \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId();
            if ($ulevel != ADMIN_GROUP_GODADMIN) {
                require_once _base_ . '/lib/lib.preference.php';
                $adminManager = new AdminPreference();
                $admin_tree = $adminManager->getAdminTree(\FormaLms\lib\FormaUser::getCurrentUser()->getIdST());
                //$admin_groups = $this->_extractGroupsFromMixedIdst($admin_tree);
                $_qfilter .= ' AND idst IN (' . implode(',', $admin_tree) . ') ';
            }
        }

        $query = 'SELECT idst, groupid FROM %adm_group '
            . " WHERE groupid LIKE '%" . $query . "%' " . $_qfilter . ' '
            . " AND hidden='false' AND type<>'course' ORDER BY groupid "
            . ((int) $limit > 0 ? ' LIMIT 0, ' . (int) $limit : '');
        $res = $this->db->query($query);
        if ($res) {
            while ($obj = $this->db->fetch_obj($res)) {
                $output[] = $obj;
            }
        }

        return $output;
    }

    public function getGroupsDropdownList()
    {
        $output = ['0' => '(' . Lang::t('_ALL', 'standard') . ')'];

        $query = "SELECT idst, groupid FROM %adm_group WHERE hidden='false' AND type<>'course' ORDER BY groupid";
        $res = $this->db->query($query);
        if ($res) {
            while ($obj = $this->db->fetch_obj($res)) {
                $arrayObj = explode('/', $obj->groupid);
                $output[$obj->idst] = end($arrayObj);
            }
        }

        return $output;
    }

    public function importGroupMembers($users, $id_group)
    {
        $output = [
            'total' => 0,
            'inserted' => 0,
            'not_inserted' => 0,
            'duplicated' => 0,
        ];

        if (empty($users)) {
            return $output;
        }
        $users = array_unique($users);
        $count_total = 0;
        foreach ($users as $user) {
            $users_list[] = strtolower($this->acl_man->absoluteId($user));
            ++$count_total;
        }

        $output['total'] = $count_total;
        $output['not_inserted'] = $count_total;

        $users_idst = [];
        $query = "SELECT idst, LOWER(userid) as userid FROM %adm_user WHERE userid IN ('" . implode("','", $users_list) . "') ";
        $res = $this->db->query($query);

        foreach ($res as $row){
            $users_idst[$this->acl_man->relativeId($row['userid'])] = (int) $row['idst'];

        }

        if (empty($users_idst)) {
            return $output;
        }

        // select estrarre tutti dalla group_memeber
        $dup = [];
        $query = 'SELECT idstMember from %adm_group_members where idst = ' . $id_group
            . " AND idstMember in ('" . implode("','", $users_idst) . "') ";
        $res = $this->db->query($query);

        foreach ($res as $row) {
            $dup[] = $row['idstMember'];
        }

        $query = 'INSERT INTO %adm_group_members (idst,idstMember) VALUES ';

        $counter = 0;
        $dup_counter = 0;
        $insert_values = [];
        foreach ($users as $key_u) {
            if (isset($users_idst[strtolower($key_u)])) {
                $idst_u = $users_idst[strtolower($key_u)];

                //check if a duplicate exists
                if (in_array($idst_u, $dup) !== false) {
                    ++$dup_counter;
                } else {
                    ++$counter;
                    $insert_values[] = '(' . $id_group . ', ' . $idst_u . ')';
                }
            }
        }

        if (empty($insert_values)) {
            return $output;
        }



        $query .= implode(',', $insert_values);
        $res = $this->db->query($query);
        if ($res) {
            $output['total'] = $count_total;
            $output['inserted'] = $counter;
            $output['not_inserted'] = $count_total - $counter;
            $output['duplicated'] = $dup_counter;
        }

        return $output;
    }

    public function getGroupUsersList($id_group, $pagination, $filter = false)
    {
        if ($id_group <= 0) {
            return false;
        } //invalid role

        //validate pagination data
        if (!is_array($pagination)) {
            $pagination = [];
        }
        $_startIndex = (isset($pagination['startIndex']) ? (int) $pagination['startIndex'] : 0);
        $_results = (isset($pagination['results']) ? (int) $pagination['results'] : FormaLms\lib\Get::sett('visuItem', 25));
        $_sort = 'userid';
        $_dir = 'ASC';

        if (isset($pagination['dir'])) {
            switch (strtoupper($pagination['dir'])) {
                case 'ASC':
                    $_dir = 'ASC';
                    break;
                case 'DESC':
                    $_dir = 'DESC';
                    break;
            }
        }

        if (isset($pagination['sort'])) {
            switch ($pagination['sort']) {
                case 'firstname':
                    $_sort = 'firstname ' . $_dir . ', lastname';
                    break;
                case 'lastname':
                    $_sort = 'lastname ' . $_dir . ', firstname';
                    break;
            }
        }

        //validate filter data and abjust query
        $_filter = '';
        if (is_array($filter)) {
            if (isset($filter['text']) && $filter['text'] != '') {
                $_filter .= " AND (u.userid LIKE '%" . $filter['text'] . "%' "
                    . " OR u.firstname LIKE '%" . $filter['text'] . "%' "
                    . " OR u.lastname LIKE '%" . $filter['text'] . "%') ";
            }
        }

        $sub_groups = $this->acl_man->getGroupGDescendants($id_group);
        $sub_groups = array_unique($sub_groups);

        //mount query
        if (count($sub_groups) <= 1) { //no sub groups in this group: go standard simple query
            $query = 'SELECT u.idst, u.userid, u.firstname, u.lastname, gm.idst AS idst_group '
                . ' FROM %adm_user AS u JOIN %adm_group_members AS gm '
                . ' ON (u.idst = gm.idstMember) '
                . ' WHERE gm.idst = ' . (int) $id_group . ' ' . $_filter . ' '
                . ' ORDER BY ' . $_sort . ' ' . $_dir . ' '
                . ' LIMIT ' . (int) $_startIndex . ', ' . (int) $_results;
        } else {
            $base_users = $this->acl_man->getGroupUMembers($id_group);
            $query = '(SELECT u.idst, u.userid, u.firstname, u.lastname, gm.idst AS idst_group '
                . ' FROM %adm_user AS u JOIN %adm_group_members AS gm '
                . ' ON (u.idst = gm.idstMember) '
                . ' WHERE gm.idst = ' . (int) $id_group . ' ' . $_filter . ') '

                . ' UNION '

                . ' (SELECT DISTINCT u.idst, u.userid, u.firstname, u.lastname, 0 AS idst_group ' //0 as a dummy for unique results
                . ' FROM %adm_user AS u JOIN %adm_group_members AS gm '
                . ' ON (u.idst = gm.idstMember) '
                . ' WHERE gm.idst IN (' . implode(',', $sub_groups) . ') ' . $_filter . ' '
                . (!empty($base_users) ? ' AND u.idst NOT IN (' . implode(',', $base_users) . ')' : '')
                . ' ) '

                . ' ORDER BY ' . $_sort . ' ' . $_dir . ' '
                . ' LIMIT ' . (int) $_startIndex . ', ' . (int) $_results;
        }
        $res = $this->db->query($query);

        //extract records from database
        $output = [];
        if ($res && $this->db->num_rows($res) > 0) {
            while ($obj = $this->db->fetch_obj($res)) {
                $obj->is_group = $obj->idst_group != $id_group;
                $output[] = $obj;
            }
        }

        return $output;
    }

    public function getGroupUsersTotal($id_group, $filter = false)
    {
        if ($id_group <= 0) {
            return false;
        } //invalid role

        //validate filter data and abjust query
        $_filter = '';
        if (is_array($filter)) {
            if (isset($filter['text']) && $filter['text'] != '') {
                $_filter .= " AND (u.userid LIKE '%" . $filter['text'] . "%' "
                    . " OR u.firstname LIKE '%" . $filter['text'] . "%' "
                    . " OR u.lastname LIKE '%" . $filter['text'] . "%') ";
            }
        }

        $sub_groups = $this->acl_man->getGroupGDescendants($id_group);
        $sub_groups = array_unique($sub_groups);

        //mount query
        if (count($sub_groups) <= 1) { //no sub groups in this group: go standard simple query
            $query = 'SELECT COUNT(*) as ucount '
                . ' FROM %adm_user AS u JOIN %adm_group_members AS gm '
                . ' ON (u.idst = gm.idstMember) '
                . ' WHERE gm.idst = ' . (int) $id_group . ' ' . $_filter . ' ';
        } else {
            $base_users = $this->acl_man->getGroupUMembers($id_group);
            $query = '(SELECT COUNT(*) as ucount '
                . ' FROM %adm_user AS u JOIN %adm_group_members AS gm '
                . ' ON (u.idst = gm.idstMember) '
                . ' WHERE gm.idst = ' . (int) $id_group . ' ' . $_filter . ') '
                . ' UNION '
                . '(SELECT COUNT(DISTINCT u.idst) as ucount '
                . ' FROM %adm_user AS u JOIN %adm_group_members AS gm '
                . ' ON (u.idst = gm.idstMember) '
                . ' WHERE gm.idst IN (' . implode(',', $sub_groups) . ') ' . $_filter . ' '
                . (!empty($base_users) ? ' AND u.idst NOT IN (' . implode(',', $base_users) . ') ' : '')
                . ' )';
        }
        $res = $this->db->query($query);

        //extract total value database
        $output = false;
        if ($res) {
            $output = 0;
            while (list($total) = $this->db->fetch_row($res)) {
                $output += $total;
            }
        }

        return $output;
    }

    public function countUsers($groups)
    {
        if (empty($groups)) {
            return false;
        }
        if (is_numeric($groups)) {
            $groups = [(int) $groups];
        }
        if (!is_array($groups)) {
            return false;
        }

        $output = [];

        $arr_gm = [];
        $query = ' SELECT gm.idst, COUNT(gm.idstMember) '
            . ' FROM %adm_group_members AS gm '
            . ' JOIN %adm_user AS u ON (u.idst = gm.idstMember) '
            . ' WHERE gm.idst IN ( SELECT idst FROM %adm_group ) '
            . ' AND gm.idst IN (' . implode(',', $groups) . ') '
            . ' GROUP BY gm.idst ';
        $res = $this->db->query($query);
        while (list($idst, $count) = $this->db->fetch_row($res)) {
            $output[$idst] = $count;
        }

        return $output;
    }

    public function countMembers($groups)
    {
        if (empty($groups)) {
            return false;
        }
        if (is_numeric($groups)) {
            $groups = [(int) $groups];
        }
        if (!is_array($groups)) {
            return false;
        }

        $output = [];

        $arr_gm = [];
        $query = ' SELECT idst, COUNT(idstMember) '
            . ' FROM %adm_group_members '
            . ' WHERE idst IN ( SELECT idst FROM %adm_group ) '
            . ' AND idst IN (' . implode(',', $groups) . ') '
            . ' GROUP BY idst ';
        $res = $this->db->query($query);
        while (list($idst, $count) = $this->db->fetch_row($res)) {
            $output[$idst] = $count;
        }

        return $output;
    }

    public function removeUsersFromGroup($id_group, $users)
    {
        if ((int) $id_group <= 0) {
            return false;
        }
        if (is_numeric($users)) {
            $users = [(int) $users];
        }
        if (!is_array($users)) {
            return false;
        }
        if (empty($users)) {
            return true;
        }

        $output = false;
        $query = 'SELECT idst FROM %adm_user WHERE idst IN (' . implode(',', $users) . ')';
        $res = $this->db->query($query);
        if ($res && $this->db->num_rows($res) > 0) {
            $to_delete = [];
            while (list($id_user) = $this->db->fetch_row($res)) {
                $to_delete[] = (int) $id_user;
            }
            $output = true;
            if (!empty($to_delete)) {
                $query = 'DELETE FROM %adm_group_members WHERE idst = ' . (int) $id_group . ' '
                    . ' AND idstMember IN (' . implode(',', $to_delete) . ')';
                $res = $this->db->query($query);
                $output = $res ? true : false;
            }
        }

        Events::trigger('core.group_member.unassigned', ['id_group' => $id_group, 'users' => $users]);

        return $output;
    }


    public function enrole($id, $members) : bool {
        // apply rules
        $enrollrules = new EnrollrulesAlms();
        $enrollrules->applyRulesMultiLang('_LOG_USERS_TO_GROUP', $members, false, $id);

        return true;
    }

    public function getAccessList($resourceId) : array {

        return $this->getGroupMembers($resourceId);

    }

    public function setAccessList($resourceId, array $selection) : bool {

        $res = $this->saveGroupMembers($resourceId, $selection);
        $this->enrole($resourceId, $selection);
        return $res;
    }
}
