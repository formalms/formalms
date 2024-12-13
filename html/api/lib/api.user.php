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
use FormaLms\lib\Encryption\SSLEncryption;

require_once _base_ . '/api/lib/lib.api.php';

class User_API extends API
{
    public function __construct()
    {
        parent::__construct();
        $this->needAuthentication = true;
        $this->endpoingWithoutAuthentication = ['api/user/downloadCertificate'];
    }

    protected function _getBranch($like, $parent = false, $lang_code = false)
    {
        if (!$like) {
            return false;
        }
        $query = 'SELECT oct.idOrg FROM %adm_org_chart as oc JOIN %adm_org_chart_tree as oct '
            . " ON (oct.idOrg = oc.id_dir) WHERE oc.translation LIKE '" . addslashes($like) . "'";
        if ($lang_code !== false) { //TO DO: check if lang_code is valid
            $query .= " AND oc.lang_code = '" . $lang_code . "'";
        }
        if ($parent !== false) {
            $query .= ' AND oct.idParent = ' . (int) $parent;
        }
        $res = $this->db->query($query);
        if ($this->db->num_rows($res) > 0) {
            [$output] = $this->db->fetch_row($res);

            return $output;
        } else {
            return false;
        }
    }

    //  return idOrg using org chart code
    protected function _getBranchByCode($like, $parent = false, $lang_code = false)
    {
        if (!$like) {
            return false;
        }

        $query = 'SELECT oct.idOrg FROM %adm_org_chart as oc JOIN %adm_org_chart_tree as oct '
            . " ON (oct.idOrg = oc.id_dir) WHERE oct.code LIKE '" . addslashes($like) . "'";
        if ($lang_code !== false) { //TO DO: check if lang_code is valid
            $query .= " AND oc.lang_code = '" . $lang_code . "'";
        }
        if ($parent !== false) {
            $query .= ' AND oct.idParent = ' . (int) $parent;
        }

        $res = $this->db->query($query);
        if ($this->db->num_rows($res) > 0) {
            [$output] = $this->db->fetch_row($res);

            return $output;
        } else {
            return false;
        }
    }

    public function checkUserIdst($idst)
    {
        $output = false;
        if (is_numeric($idst)) {
            $res = $this->db->query('SELECT * FROM %adm_user WHERE idst=' . $idst);
            $output = ($this->db->num_rows($res) > 0);
        }

        return $output;
    }

    public function createUser($params, $userdata)
    {
        if (defined('_API_DEBUG') && _API_DEBUG) {
            file_put_contents('create_user.txt', "\n\n----------------\n\n" . print_r($params, true) . ' || ' . print_r($userdata, true), FILE_APPEND);
        }

        $set_idst = (isset($params['idst']) ? $params['idst'] : false);

        $userdata = (!isset($userdata['userid']) && isset($params)) ? $params : $userdata;

        if (!isset($userdata['userid'])) {
            return false;
        }

        if (!isset($userdata['sendmail']) || $userdata['sendmail'] == '') {
            $sendMailToUser = false;
        } else {
            $sendMailToUser = true;
        }

        if (!isset($userdata['password']) || $userdata['password'] == '') {
            $userdata['password'] = mt_rand();
            $sendMailToUser = true;
        }

        $id_user = $this->aclManager->registerUser(
            $userdata['userid'],
            (isset($userdata['firstname']) ? $userdata['firstname'] : ''),
            (isset($userdata['lastname']) ? $userdata['lastname'] : ''),
            (isset($userdata['password']) ? $userdata['password'] : ''),
            (isset($userdata['email']) ? $userdata['email'] : ''),
            '',
            (isset($userdata['signature']) ? $userdata['signature'] : ''),
            false, // alredy_encripted
            $set_idst,
            (isset($userdata['pwd_expire_at']) ? $userdata['pwd_expire_at'] : ''),
            (isset($userdata['force_change']) ? $userdata['force_change'] : 0)
        );

        //TODO: EVT_OBJECT (§)
        //$event = new \appLms\Events\Api\ApiUserRegistrationEvent();
        //$event->setId($id_user);
        //TODO: EVT_LAUNCH (&)
        //\appCore\Events\DispatcherManager::dispatch(\appLms\Events\Api\ApiUserRegistrationEvent::EVENT_NAME, $event);

        // suspend
        if (isset($userdata['valid']) && $userdata['valid'] == '0') {
            $res = $this->aclManager->suspendUser($id_user);
        }

        // registration code:
        if ($id_user && !empty($userdata['reg_code']) && !empty($userdata['reg_code_type'])) {
            require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.usermanager.php');
            $user_manager = new UserManager();
            $uma = new UsermanagementAdm();
            $reg_code_res = $user_manager->_render->processRegistrationCode(
                $this->aclManager, $uma, $id_user, $userdata['reg_code'], $userdata['reg_code_type']
            );
            if ($reg_code_res['success'] == false) {
                $this->aclManager->deleteUser($id_user);
                $output = ['success' => false, 'message' => 'Registration Code Error: ' . $reg_code_res['msg']];
                $id_user = false;

                return $output;
            }
        }

        if (!$id_user) {
            if (defined('_API_DEBUG') && _API_DEBUG) {
                file_put_contents('create_user.txt', '?!: ' . var_export($id_user, true), FILE_APPEND);
            }

            return false;
        }

        if ($id_user) {
            if (!isset($userdata['role'])) {
                $level = ADMIN_GROUP_USER;
            } else {
                switch ($userdata['role']) {
                    case 'godadmin':
                        $level = ADMIN_GROUP_GODADMIN;
                        break;
                    case 'admin':
                        $level = ADMIN_GROUP_ADMIN;
                        break;
                    default:
                        $level = ADMIN_GROUP_USER;
                        break;
                }
            }

            //subscribe to std groups
            $group = $this->aclManager->getGroupST($level); //'/framework/level/user');
            $this->aclManager->addToGroup($group, $id_user);
            $group = $this->aclManager->getGroupST('/oc_0');
            $this->aclManager->addToGroup($group, $id_user);
            $group = $this->aclManager->getGroupST('/ocd_0');
            $this->aclManager->addToGroup($group, $id_user);

            if (isset($userdata['language'])) {
                require_once _base_ . '/lib/lib.preference.php';
                $user_pref = new UserPreferences($id_user);
                $user_pref->setLanguage($userdata['language']);
            }

            //check if some additional fields have been set
            $okcustom = true;
            if (isset($userdata['_customfields'])) {
                require_once _adm_ . '/lib/lib.field.php';
                $fields = &$userdata['_customfields'];
                if (count($fields) > 0) {
                    $fl = new FieldList();
                    $okcustom = $fl->storeDirectFieldsForUser($id_user, $fields);
                }
            }

            $entities = [];
            if (isset($userdata['orgchart'])) {
                $branches = explode(';', $userdata['orgchart']);
                if (is_array($branches)) {
                    foreach ($branches as $branch) {
                        $idOrg = $this->_getBranch($branch);

                        if ($idOrg !== false) {
                            $oc = $this->aclManager->getGroupST('/oc_' . $idOrg);
                            $ocd = $this->aclManager->getGroupST('/ocd_' . $idOrg);
                            $this->aclManager->addToGroup($oc, $id_user);
                            $this->aclManager->addToGroup($ocd, $id_user);
                            $entities[$oc] = $oc;
                            $entities[$ocd] = $ocd;

                        }
                    }
                }
            }

            $enrollrules = new EnrollrulesAlms();
            if (isset($userdata['orgchart_code'])) {
                $branches = explode(';', $userdata['orgchart_code']);
                if (is_array($branches)) {
                    foreach ($branches as $branch) {
                        $idOrg = $this->_getBranchByCode($branch);

                        if ($idOrg !== false) {
                            $oc = $this->aclManager->getGroupST('/oc_' . $idOrg);
                            $ocd = $this->aclManager->getGroupST('/ocd_' . $idOrg);
                            $this->aclManager->addToGroup($oc, $id_user);
                            $this->aclManager->addToGroup($ocd, $id_user);
                            $entities[$oc] = $oc;
                            $entities[$ocd] = $ocd;
                            $enrollrules->applyRulesMultiLang('_LOG_USERS_TO_ORGCHART', [$id_user], $idOrg);
                        }
                    }
                }
            }


            $enrollrules->newRules('_NEW_USER',
                [$id_user],
                $userdata['language'],
                0, // idOrg
                (!empty($entities) ? $entities : false)
            );

            // save external user data:
            if ($params['ext_not_found'] && !empty($params['ext_user']) && !empty($params['ext_user_type'])) {
                $pref_path = 'ext.user.' . $params['ext_user_type'];
                $pref_val = 'ext_user_' . $params['ext_user_type'] . '_' . (int) $params['ext_user'];

                $pref = new UserPreferencesDb();
                $pref->assignUserValue($id_user, $pref_path, $pref_val);
                if (defined('_API_DEBUG') && _API_DEBUG) {
                    file_put_contents('create_user.txt', print_r($id_user, true) . ' || ' . print_r($pref_path, true) . ' || ' . print_r($pref_val, true), FILE_APPEND);
                }
            } else {
                if (defined('_API_DEBUG') && _API_DEBUG) {
                    file_put_contents('create_user.txt', "??: \n\n" . print_r($params, true), FILE_APPEND);
                }
            }
        }

        if ($sendMailToUser) {
            $reg_code = null;
            $uma = new UsermanagementAdm();
            $nodes = $uma->getUserFolders($id_user);
            if ($nodes) {
                $idst_oc = array_keys($nodes)[0];

                $query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idst_oc LIMIT 1");
                if ($query) {
                    $reg_code = sql_fetch_object($query)->idOrg;
                }
            }

            // Send Message
            require_once _base_ . '/lib/lib.eventmanager.php';

            $array_subst = [
                '[url]' => FormaLms\lib\Get::site_url(),
                '[dynamic_link]' => getCurrentDomain($reg_code) ?: FormaLms\lib\Get::site_url(),
                '[userid]' => $userdata['userid'],
                '[password]' => $userdata['password'],
            ];

            $e_msg = new EventMessageComposer();
            $e_msg->setSubjectLangText('email', '_REGISTERED_USER_SBJ', false);
            $e_msg->setBodyLangText('email', '_REGISTERED_USER_TEXT', $array_subst);

            $recipients = [$id_user];

            if (!empty($recipients)) {
                createNewAlert('UserNewApi', 'directory', 'edit', '1', 'New user created API', $recipients, $e_msg);
            }
        }

        return $id_user;
    }

    public function updateUser($id_user, $userdata) {

        $acl_man = new FormaACLManager();
        $output = array();

        $user_data = $this->aclManager->getUser($id_user, false);

        if (!$user_data) {
            return -1;

        }

        if (isset($userdata['valid']) && $userdata['valid'] == '1'){
            $res = $this->aclManager->recoverUser($id_user);
        } elseif (isset($userdata['valid']) && $userdata['valid'] == '0'){
            $res = $this->aclManager->suspendUser($id_user);
        }

        $res = $this->aclManager->updateUser(
            $id_user,
            (isset($userdata['userid']) ? $userdata['userid'] :  false),
            (isset($userdata['firstname']) ? $userdata['firstname'] :  false),
            (isset($userdata['lastname']) ? $userdata['lastname'] :  false),
            (isset($userdata['password']) ? $userdata['password'] :  false),
            (isset($userdata['email']) ? $userdata['email'] :  false),
            false,
            (isset($userdata['signature']) ? $userdata['signature'] :  false),
            (isset($userdata['lastenter']) ? $userdata['lastenter'] :  false),
            false
        );

        if (!empty($userdata['orgchart'])) {
            $branches = explode(";", $userdata['orgchart']);
            if (is_array($branches)) {
                $this->aclManager->removeUserFromNonBaseGroups($id_user);
                foreach ($branches as $branch) {
                    $idOrg = $this->_getBranch($branch);

                    if ($idOrg !== false) {
                        $oc = $this->aclManager->getGroupST('/oc_'.$idOrg);
                        $ocd = $this->aclManager->getGroupST('/ocd_'.$idOrg);
                        $this->aclManager->addToGroup($oc, $id_user);
                        $this->aclManager->addToGroup($ocd, $id_user);
                        $entities[$oc] = $oc;
                        $entities[$ocd] = $ocd;
                    }
                }
            }
        }

        if (!empty($userdata['orgchart_code'])) {
            $branches = explode(";", $userdata['orgchart_code']);
            if (is_array($branches)) {
                $this->aclManager->removeUserFromNonBaseGroups($id_user);
                foreach ($branches as $branch) {
                    $idOrg = $this->_getBranchByCode($branch);

                    if ($idOrg !== false) {
                        $oc = $this->aclManager->getGroupST('/oc_'.$idOrg);
                        $ocd = $this->aclManager->getGroupST('/ocd_'.$idOrg);
                        $this->aclManager->addToGroup($oc, $id_user);
                        $this->aclManager->addToGroup($ocd, $id_user);
                        $entities[$oc] = $oc;
                        $entities[$ocd] = $ocd;
                    }
                }
            }
        }

        //additional fields
        $okcustom = true;
        if (isset($userdata['_customfields']) && $res) {
            require_once _adm_ . '/lib/lib.field.php';
            $fields =& $userdata['_customfields'];
            if(count($fields) > 0) {
                $fl = new FieldList();
                $okcustom = $fl->storeDirectFieldsForUser($id_user, $fields);
            }
        }
        return $id_user;
    }

    public function getCustomFields($lang_code = false, $indexes = false)
    {
        require_once _adm_ . '/lib/lib.field.php';
        $output = [];
        $fl = new FieldList();
        $fields = $fl->getFlatAllFields(false, false, $lang_code);
        foreach ($fields as $key => $val) {
            if ($indexes) {
                $output[$key] = $val;
            } else {
                $output[] = ['id' => $key, 'name' => $val];
            }
        }

        return $output;
    }

    /**
     * Return all the info about the user.
     *
     * @param <int> $id_user the idst of the user
     */
    private function getUserDetails($id_user)
    {
        require_once _adm_ . '/lib/lib.field.php';

        $user_data = $this->aclManager->getUser($id_user, false);
        $output = [];
        if (!$user_data) {
            $output['success'] = false;
            $output['message'] = 'Invalid user ID: ' . $id_user . '.';
            $output['details'] = false;
        } else {
            $user_details = [
                'idst' => $user_data[ACL_INFO_IDST],
                'userid' => $this->aclManager->relativeId($user_data[ACL_INFO_USERID]),
                'firstname' => $user_data[ACL_INFO_FIRSTNAME],
                'lastname' => $user_data[ACL_INFO_LASTNAME],
                'email' => $user_data[ACL_INFO_EMAIL],
                'signature' => $user_data[ACL_INFO_SIGNATURE],
                'valid' => $user_data[ACL_INFO_VALID],
                'pwd_expire_at' => $user_data[ACL_INFO_PWD_EXPIRE_AT],
                'register_date' => $user_data[ACL_INFO_REGISTER_DATE],
                'last_enter' => $user_data[ACL_INFO_LASTENTER],
            ];

            $field_man = new FieldList();
            $field_data = $field_man->getFieldsAndValueFromUser($id_user, false, true);

            $fields = [];
            foreach ($field_data as $field_id => $value) {
                $fields[] = ['id' => $field_id, 'name' => $value[0], 'value' => $value[1]];
            }

            $user_details['custom_fields'] = $fields;

            $output['success'] = true;
            $output['message'] = '';
            $output['details'] = $user_details;
        }

        return $output;
    }

    /**
     * @param type $params
     *                     - userid
     *                     - password
     *                     - password encoded: if true, it will consider the password as MD5 encoded string; else as plain text
     *
     * @return array
     */
    private function getUserDetailsFromCredentials($params)
    {
        $output = [];

        if (empty($params['userid']) || empty($params['password'])) {
            $output['success'] = false;
            $output['message'] = 'Invalid parameters.';
            $output['details'] = $params;
        } else {
            $userIdst = '';
            if (!empty($params['password_encoded'])) {
                $password = $params['password_encoded'];

                $qtxt = "SELECT idst FROM %adm_user WHERE
					userid = '" . $this->aclManager->absoluteId($params['userid']) . "' AND 
					pass='" . $password . "'";

                $q = $this->db->query($qtxt);

                if ($q && $this->db->num_rows($q) > 0) {
                    $row = $this->db->fetch_obj($q);

                    $userIdst = $row->idst;
                }
            } else {
                $password = $params['password'];

                $query = 'SELECT * FROM %adm_user WHERE '
                    . " userid = '" . $this->aclManager->absoluteId($params['userid']) . "'";
                $res = $this->db->query($query);

                if ($this->db->num_rows($res) > 0) {
                    $row = $this->db->fetch_obj($res);
                    if ($this->aclManager->password_verify_update($password, $row->pass, $row->idst)) {
                        $userIdst = $row->idst;
                    }
                }
            }

            if (!empty($userIdst)) {
                $output = $this->getUserDetails($userIdst);
            } else {
                $output['success'] = false;
                $output['message'] = 'Invalid credentials specified for user: ' . $params['userid'] . '.';
                $output['details'] = false;
            }
        }

        return $output;
    }

    /**
     * Return the complete user list.
     */
    private function getUsersList()
    {
        $output = [];
        $query = 'SELECT idst, userid, firstname, lastname FROM %adm_user WHERE idst<>' . $this->aclManager->getAnonymousId() . ' ORDER BY userid';
        $res = $this->db->query($query);
        if ($res) {
            $output['success'] = true;
            $output['users_list'] = [];
            while ($row = $this->db->fetch_assoc($res)) {
                $output['users_list'][] = [
                    'userid' => $this->aclManager->relativeId($row['userid']),
                    'idst' => $row['idst'],
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname'],
                ];
            }
        } else {
            $output['success'] = false;
        }

        return $output;
    }

    /**
     * Delete a user.
     *
     * @param <type> $id_user delete the user
     */
    private function deleteUser($id_user)
    {
        $output = [];
        if ($this->aclManager->deleteUser($id_user)) {
            $output = ['success' => true, 'message' => 'User #' . $id_user . ' has been deleted.'];
        } else {
            $output = ['success' => false, 'message' => 'Error: unable to delete user #' . $id_user . '.'];
        }

        return $output;
    }

    public function getMyCourses($id_user, $params = false)
    {
        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.course.php');
        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.date.php');
        $output = [];

        $output['success'] = true;

        $search = ['cu.iduser = :id_user'];
        $search_params = [':id_user' => $id_user];

        if (!empty($params['filter'])) {
            switch ($params['filter']) {
                case 'completed':
                    $search[] = 'cu.status = :status';
                    $search_params[':status'] = _CUS_END;
                    break;
                case 'notcompleted':
                    $search[] = 'cu.status >= :status_from';
                    $search_params[':status_from'] = _CUS_SUBSCRIBED;
                    $search[] = 'cu.status < :status_to';
                    $search_params[':status_to'] = _CUS_END;
                    break;
                case 'notstarted':
                    $search[] = 'cu.status = :status';
                    $search_params[':status'] = _CUS_SUBSCRIBED;
                    break;
            }
        }

        $model = new CourseLms();
        $course_list = $model->findAll($search, $search_params);

        //check courses accessibility

        foreach ($course_list as $key => $value) {
            $course_list[$key]['can_enter'] = Man_Course::canEnterCourse($course_list[$key]);
        }

        //$output['log'] = $course_list;

        foreach ($course_list as $key => $course_info) {
            $dates = [];

            switch ($course_info['course_type']) {
                case 'classroom':
                    $classroomManager = new DateManager();
                    $courseDates = $classroomManager->getCourseDate($course_info['idCourse']);

                    foreach ($courseDates as $courseDate) {
                        $userStatus = $classroomManager->getCourseEditionUserStatus($id_user, $course_info['idCourse'], $courseDate['id_date']);
                        if (!empty($userStatus)) {
                            $dates[] = $userStatus;
                        }
                    }
                    break;
                default:
                    break;
            }

            $output['courses'][]['course_info'] = [
                'course_id' => $course_info['idCourse'],
                'course_type' => $course_info['course_type'],
                'course_name' => str_replace('&', '&amp;', $course_info['name']),
                'course_description' => str_replace('&', '&amp;', $course_info['description']),
                'course_link' => FormaLms\lib\Get::site_url() . _folder_lms_ . '/index.php?modname=course&amp;op=aula&amp;idCourse=' . $course_info['idCourse'],
                'user_status' => $course_info['user_status'],
                'dates' => $dates,
            ];
        }

        return $output;
    }

    public function KbSearch($id_user, $params)
    {
        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.course.php');
        $output = [];

        $output['success'] = true;

        $filter_text = (!empty($params['search']) ? $params['search'] : '');
        $course_filter = (!empty($params['course_filter']) ? (int) $params['course_filter'] : -1);
        $start_index = (!empty($params['start_index']) ? (int) $params['start_index'] : false);
        $results = (!empty($params['results']) ? (int) $params['results'] : false);

        //TODO: call getSearchFilter()

        $kb_model = new KbAlms();
        $sf = $kb_model->getSearchFilter($id_user, $filter_text, $course_filter);

        $res_arr = $kb_model->getResources(0, $start_index, $results, false,
            false, $sf['where'], $sf['search'], false, true, $sf['show_what']);

        foreach ($res_arr['data'] as $key => $content_info) {
            $output[]['content_info'] = $content_info;
        }

        return $output;
    }

    public function importExternalUsers($userdata, $from_email = false)
    {
        $output = ['success' => true];

        $i = 0;
        foreach ($userdata as $user_info) {
            $pref_path = 'ext.user.' . $user_info['ext_user_type'];
            $pref_val = 'ext_user_' . $user_info['ext_user_type'] . '_' . (int) $user_info['ext_user'];

            $users = $this->aclManager->getUsersBySetting($pref_path, $pref_val);

            // if the user is not yet in sync..
            if (count($users) <= 0) {
                if (!$from_email) {
                    // we search for the user from the userid:
                    $user = $this->aclManager->getUser(false, $user_info['userid']);
                } else { // we search for the user from e-mail:
                    $user = $this->aclManager->getUserByEmail($user_info['email']);
                }
                // if found, we link the account to the external one:
                if ($user) {
                    $pref = new UserPreferencesDb();
                    $pref->assignUserValue($user[ACL_INFO_IDST], $pref_path, $pref_val);
                    $output['sync_' . $i] = $user_info['userid'];
                    ++$i;
                }
            }
        }

        return $output;
    }

    public function importExternalUsersFromEmail($userdata)
    {
        return $this->importExternalUsers($userdata, true);
    }

    /**
     * Count the users of this Forma installation.
     *
     * @param array $params parameters:
     *                      - status: filter by "active", "suspended", "all"
     *
     * @return array
     */
    public function countUsers($params)
    {
        $output = ['success' => true];

        $status = (!empty($params['status']) ? $params['status'] : 'all');

        $qtxt = "SELECT COUNT(*) AS tot FROM %adm_user WHERE
			userid != '/Anonymous' ";

        switch ($status) {
            case 'active':
                $qtxt .= 'AND valid=1';
                break;
            case 'suspended':
                $qtxt .= 'AND valid=0';
                break;
        }

        $q = $this->db->query($qtxt);
        if ($q) {
            [$tot] = $this->db->fetch_row($q);
            $output['users_count'] = $tot;
        } else {
            $output['success'] = false;
        }

        return $output;
    }

    public function checkRegistrationCode($params)
    {
        $output = ['success' => true];

        $registration_code_type = $params['reg_code_type'];
        $code = $params['reg_code'];

        if (empty($registration_code_type) || empty($code)) {
            $output['success'] = false;
        } else {
            require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.usermanager.php');
            $user_manager = new UserManager();

            $res = $user_manager->checkRegistrationCode($code, $registration_code_type);
            if (!$res) {
                $output['success'] = false;
            }
        }

        return $output;
    }

    /**
     * Check if a user exists by its username (userid)
     * or its email if also_check_as_email is true.
     *
     * @param type $params
     *                     - userid
     *                     - also_check_as_email
     *
     * @return bool
     */
    public function checkUsername($params)
    {
        $userid = $params['userid'];
        $query = 'SELECT idst, userid, firstname, lastname, pass, email, avatar, signature,'
            . ' level, lastenter, valid, pwd_expire_at, register_date, lastenter, force_change,
					 facebook_id, twitter_id, linkedin_id, google_id, privacy_policy '
            . ' FROM ' . $this->aclManager->_getTableUser();
        $query .= " WHERE userid = '" . $this->aclManager->absoluteId($userid) . "'";

        if ($params['also_check_as_email']) {
            $query .= " OR email='" . $params['also_check_as_email'] . "'";
        }

        $q = $this->aclManager->_executeQuery($query);
        if (sql_num_rows($q) > 0) {
            $res = sql_fetch_row($q);
        } else {
            $res = false;
        }

        if (!$res) {
            $output = [
                'success' => false,
                'message' => 'User not found',
            ];
        } else {
            $output['idst'] = (int) $res[ACL_INFO_IDST];
            $output['success'] = true;
            $output['message'] = ($res[ACL_INFO_VALID] == 0) ? '_DISABLED' : '';
        }

        return $output;
    }

    /**
     * Get the ID related to a profile name.
     *
     * @param string $profile_name
     *
     * @return int
     */
    private function getProfilebyName($profile_name)
    {
        $out = 0;
        $q = "SELECT idst from core_group where groupid = '/framework/adminrules/" . $profile_name . "'";
        $res = $this->db->query($q);
        if ($res) {
            [$out] = $this->db->fetch_row($res);
        }

        return $out;
    }

    private function hasAdminProfile($userid)
    {
        $out = false;
        $m = new AdminmanagerAdm();
        $out = $m->getProfileAssociatedToAdmin($userid);

        return $out;
    }

    private function isAdmin($idst)
    {
        $out = 0;
        $q = 'SELECT count(*) as t from core_group_members where idst = 4 and idstMember =' . $idst;
        $res = $this->db->query($q);
        if ($res) {
            [$out] = $this->db->fetch_row($res);
        }

        return $out;
    }

    /**
     * Assign an Admin Profile for a given username or email.
     *
     * @param $params
     * - profile name
     * - username  or user email
     *
     * @return bool
     */
    public function assignProfile($params)
    {
        $idst = $this->checkUsername($params);
        if (!array_key_exists('idst', $idst)) {
            $output = ['success' => false, 'message' => 'Id user not found'];
        } else {
            if (!$this->isAdmin($idst['idst'])) {
                $output = ['success' => false, 'message' => 'User is not admin'];
            } else {
                $profile_id = $this->getProfilebyName($params['profile_name']);
                if (!$profile_id) {
                    $output = ['success' => false, 'message' => 'Input profile does not exist'];
                } else {
                    $m = new AdminmanagerAdm();
                    $r = $m->saveSingleAdminAssociation($profile_id, $idst['idst']);
                    $output = ($r) ? ['success' => true, 'message' => 'Profile assigned'] : ['success' => false, 'message' => 'Profile not assigned'];
                }
            }
        }

        return $output;
    }

    /**
     * Assign or revoke user to an admin profile.
     *
     * @param $params
     *          username: or  user email
     *          gorup_name: string the name of a group
     *          orgchart_name: string the name of an org_chart
     *          orgchart_code: the code of an org_chart
     */
    public function admin_assignUsers($params, $op)
    {
        $output = ['success' => false, 'message' => $op . ' user failed'];
        $selected_group = 0;
        // select group ID
        if (array_key_exists('group_name', $params)) {
            $q = "select idst from core_group where core_group.groupid = '/" . $params['group_name'] . "'";
            $rs = $this->db->query($q);
            if ($rs) {
                [$selected_group] = $this->db->fetch_row($rs);
            }
        }

        // select users in org chart by code
        if (array_key_exists('orgchart_code', $params)) {
            $q = "select idst_ocd from core_org_chart_tree where code = '" . $params['orgchart_code'] . "'";
            $rs = $this->db->query($q);
            if ($rs) {
                [$selected_group] = $this->db->fetch_row($rs);
            }
        }

        // select users in org chart by name
        if (array_key_exists('orgchart_name', $params)) {
            $q = "select id_dir from core_org_chart where translation = '" . $params['orgchart_name'] . "' LIMIT 1";
            $rs = $this->db->query($q);
            if ($rs) {
                [$id_org] = $this->db->fetch_row($rs);
                $q = 'select idst_ocd from core_org_chart_tree where idOrg = ' . intval($id_org);
                $rs = $this->db->query($q);
                if ($rs) {
                    [$selected_group] = $this->db->fetch_row($rs);
                }
            }
        }

        // select single user to admin
        if (array_key_exists('single_user', $params)) {
            $q = "select idst from core_user where userid = '/" . $params['single_user'] . "'";
            $rs = $this->db->query($q);
            if ($rs) {
                [$selected_group] = $this->db->fetch_row($rs);
            }
        }

        // associates users to admin
        if (array_key_exists('userid', $params) && $selected_group > 0) {
            $idst = $this->checkUsername($params);
            if ($this->isAdmin($idst['idst'])) {   // check admin
                $id_admin = intval($idst['idst']);
                if ($this->hasAdminProfile($id_admin)) {
                    if ($op == 'assign') {
                        $q = 'INSERT INTO core_admin_tree (idst, idstAdmin) VALUES (' . $selected_group . ',' . $id_admin . ')';
                    } else {
                        $q = 'DELETE FROM core_admin_tree WHERE idst=' . $selected_group . ' and idstAdmin=' . $id_admin;
                    }
                    $r = $this->db->query($q);
                    if ($r) {
                        $output = ['success' => true, 'message' => $op . ' user success'];
                    }
                } else {
                    $output = ['success' => fails, 'message' => $op . ' user has not an admin profile'];
                }
            } else {
                $output = ['success' => fails, 'message' => $op . ' user is not an admin'];
            }
        }

        return $output;
    }

    private function getCatalogueID($catalogue_name)
    {
        $cat_str = implode("','", $catalogue_name);
        $q = "select idCatalogue from learning_catalogue where name in ('" . $cat_str . "')";
        $r = $this->db->query($q);
        if ($r) {
            while ($row = $this->db->fetch_array($r)) {
                $selected_cat[] = $row[0];
            }

            return $selected_cat;
        }

        return '';
    }

    private function getCoursePathID($coursepath_name)
    {
        $cat_str = implode("','", $coursepath_name);
        $q = "select id_path from learning_coursepath where path_name in ('" . $cat_str . "')";
        $r = $this->db->query($q);
        if ($r) {
            while ($row = $this->db->fetch_array($r)) {
                $selected_course_path[] = $row[0];
            }

            return $selected_course_path;
        }

        return '';
    }

    /**
     *  Assign catalogues or coursepath to an admin.
     *
     * @param $params
     *          userid: string
     *          also_check_as_email: user's email
     *          coursepath_names: course paths name array
     *          catalogue_names: catalogues name array
     */
    public function admin_assignCourses($params)
    {
        $idst = $this->checkUsername($params);
        $admin = intval($idst['idst']);
        if ($this->isAdmin($admin) && $this->hasAdminProfile($admin)) {
            $coursepath = isset($params['coursepath_names']) ? $this->getCoursePathID($params['coursepath_names']) : '';
            $catalogue = isset($params['catalogue_names']) ? $this->getCatalogueID($params['catalogue_names']) : '';
            $m = new AdminmanagerAdm();
            $r = $m->saveCoursesAssociation($admin, '', $coursepath, $catalogue);
            if ($r) {
                $output = ['success' => true, 'message' => $op . ' courses associated to admin'];
            } else {
                $output = ['success' => fails, 'message' => $op . ' courses not associated to admin'];
            }
        } else {
            $output = ['success' => fails, 'message' => $op . ' user is not an admin or has not ad admin profile'];
        }

        return $output;
    }

    private function getIdOrgByCode($code)
    {
        $idParent = 0;
        $query = "select idOrg from core_org_chart_tree where code like '" . $code . "' ";

        $rs = $this->db->query($query);
        if ($rs) {
            [$idParent] = $this->db->fetch_row($rs);
        }

        return $idParent;
    }

    //GRIFO LRZ: new org user
    public function newOrg($orgData)
    {
        // get array language
        $name = [];
        $q = 'select lang_code from %adm_lang_language';
        $r = $this->db->query($q);
        if ($r) {
            while ($row = $this->db->fetch_array($r)) {
                $name[$row[0]] = $orgData['name_org'];
            }
        }

        // calcola idParent by code
        $output = [];

        $idParent = $orgData['parent_org'];

        if (empty($orgData['parent_org'])) {
            $idParent = 0;
        }

        include_once _adm_ . '/models/UsermanagementAdm.php';
        $adm = new UsermanagementAdm();
        $idOrg = $adm->addFolder($idParent, $name, $orgData['code']);
        if ($idOrg) {
            $output = ['success' => true, 'message' => $idOrg, 'name' => $name];
        } else {
            $output = ['success' => false, 'message' => "Organizzazione:$name; idParent:$idParent; code:" . $orgData['code']];
        }

        return $output;
    }

    public function moveOrg($orgData)
    {
        $name = ['italian' => $orgData['name']];

        $output = [];
        $output['success'] = true;

        if (empty($orgData['code'])) {
            $output['success'] = false;
            $output['message'] = 'Missing Code org Source ' . $orgData['code'];

            return $output;
        }

        if (empty($orgData['parent_org_new'])) {
            $output['success'] = false;
            $output['message'] = 'Missing Code org Destination ' . $orgData['parent_org_new'];

            return $output;
        }

        $idSrcFolder = $this->getIdOrgByCode($orgData['code']);
        $idParentNew = $this->getIdOrgByCode($orgData['parent_org_new']);

        include_once _adm_ . '/models/UsermanagementAdm.php';
        $adm = new UsermanagementAdm();
        $idOrg = $adm->moveFolder($idSrcFolder, $idParentNew);
        if ($idOrg) {
            $output = ['success' => true, 'message' => $idOrg];
        } else {
            $output = ['success' => false, 'message' => "Organizzazione:$name; idParent:$idParentNew; code:" . $orgData['code']];
        }

        $output['id_org'] = $idSrcFolder;

        return $output;
    }

    // associa utente ad organigramma
    public function moveUserInOrg($params)
    {
        $output = [];
        $output['message'] = '';
        $id_user = $params['id_user'];

        $branches = explode(';', $params['code_new_org']);
        if (is_array($branches)) {
            foreach ($branches as $branch) {
                $idOrg = $this->_getBranchByCode($branch);

                if ($idOrg !== false) {
                    $oc = $this->aclManager->getGroupST('/oc_' . $idOrg);
                    $ocd = $this->aclManager->getGroupST('/ocd_' . $idOrg);
                    $this->aclManager->addToGroup($oc, $id_user);
                    $this->aclManager->addToGroup($ocd, $id_user);
                    $entities[$oc] = $oc;
                    $entities[$ocd] = $ocd;

                    $output['id_user'] = $id_user;
                    $output['orgchart'] = $idOrg;
                    $output['success'] = true;
                } else {
                    $output['id_user'] = $id_user;
                    $output['orgchart'] = $id_org;
                    $output['success'] = false;
                }
            }
        }

        return $output;
    }

    public function removeUserFromOrg($params)
    {
        $output = [];
        $output['message'] = ' ' . $params['id_user'] . ' - ' . $params['code_org'];

        $id_user = $params['id_user'];
        $code_org = $params['code_org'];

        if (empty($id_user)) {
            $output['success'] = false;
            $output['message'] = 'Missing User ID' . $params['id_user'];

            return $output;
        }

        if (empty($code_org)) {
            $output['success'] = false;
            $output['message'] = 'Missing Code ORG' . $params['code_org'];

            return $output;
        }

        $id_org = $this->_getBranchByCode($code_org);

        $acl_man = \FormaLms\lib\Forma::getAclManager();

        $idst_org = $acl_man->getGroupST('oc_' . $id_org);
        $idst_orgd = $acl_man->getGroupST('ocd_' . $id_org);

        //cancel from group
        $acl_man->removeFromGroup($idst_org, $id_user);
        $acl_man->removeFromGroup($idst_orgd, $id_user);
        $output['success'] = true;

        return $output;
    }

    public function renameOrg($params)
    {
        $lang_code = 'italian';
        $id_org = $params['id_org'];
        $code_org = $params['code_org'];
        $name_org = $params['name_org'];

        $output = [];
        $output['message'] = 'rename org: ' . $params['id_org'] . ' - ' . $params['code_org'] . ' - ' . $params['name_org'] . ' - ' . !empty($name_org);

        $output['success'] = true;

        if (empty($id_org)) {
            $output['success'] = false;
            $output['message'] = 'Missing ORG ID' . $params['id_org'];

            return $output;
        }

        if (!empty($name_org)) {
            $query = "UPDATE core_org_chart SET translation = '" . $name_org . "' WHERE lang_code='" . $lang_code . "' AND id_dir=" . $id_org;
            $res = $this->db->query($query);
            $output['message_name'] = 'Nome organigramma modificato con successo';
        }

        if (!empty($code_org)) {
            $query = "UPDATE core_org_chart_tree SET code = '" . $code_org . "' WHERE idOrg=" . $id_org;
            $res = $this->db->query($query);
            $output['message_code'] = 'Codice organigramma modificato con successo';
        }

        return $output;
    }

    public function removeOrg($params)
    {
        $id_org = $params['id_org'];

        $output = [];
        $output['message'] = 'remove org: ' . $id_org;

        $output['success'] = true;

        if (empty($id_org)) {
            $output['success'] = false;
            $output['message'] = 'Missing ORG ID' . $params['id_org'];

            return $output;
        }

        // conta quanti figli ha il nodo corrente
        $query = 'select count(*) as c from core_org_chart_tree where idParent=' . $id_org;

        $rs = $this->db->query($query);
        if ($rs) {
            [$numChild] = $this->db->fetch_row($rs);
        }

        if ($numChild > 0) {
            $output['success'] = false;
            $output['message'] = 'ORG ID' . $params['id_org'] . ' ha dei nodi figli, eliminare prima i nodi figli per eliminare il nodo corrente';

            return $output;
        }

        require_once _adm_ . '/models/UsermanagementAdm.php';
        $classUser = new UsermanagementAdm();
        $res = $classUser->deleteFolder($id_org);

        if ($res) {
            $output['success'] = true;
            $output['message'] = 'Remove ORG ID' . $params['id_org'];
        } else {
            $output['success'] = false;
            $output['message'] = 'Errore in Remove ORG ID' . $params['id_org'];
        }

        return $output;
    }

    public function downloadCertificate()
    {
        $queryStringArray = explode('/', $this->request->get('q'));
        $downloadString = end($queryStringArray);
        $fileName = SSLEncryption::decryptDownloadUrl($downloadString);
        $baseUrl = $_SERVER['DOCUMENT_ROOT'] . '/files/appLms/certificate/';
        $fileUrl = $baseUrl . $fileName;

        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: Binary');
        header('Content-disposition: attachment; filename="' . $fileName . '"');
        readfile($fileUrl);
        exit(); // end process to prevent any problems.
    }

    // ---------------------------------------------------------------------------

    public function call($name, $params)
    {
        $output = false;

        if (!empty($params[0]) && !isset($params['idst'])) {
            $params['idst'] = $params[0]; //params[0] should contain user idst
        }

        if (empty($params['idst']) && !empty($_POST['idst'])) {
            $params['idst'] = (int) $_POST['idst'];
        }

        switch ($name) {
            case 'listUsers':
            case 'userslist':
                $list = $this->getUsersList();
                if ($list['success']) {
                    $output = ['success' => true, 'list' => $list['users_list']];
                } else {
                    $output = ['success' => false];
                }
                break;

            case 'userdetails':
                if (count($params) > 0 && !isset($params['ext_not_found'])) { //params[0] should contain user id
                    if (is_numeric($params['idst'])) {
                        $res = $this->getUserDetails($params['idst']);
                        if (!$res) {
                            $output = ['success' => false, 'message' => 'Error: unable to retrieve user details.'];
                        } else {
                            $output = ['success' => true, 'details' => $res['details']];
                        }
                    } else {
                        $output = ['success' => false, 'message' => 'Invalid passed parameter.'];
                    }
                } else {
                    $output = ['success' => false, 'message' => 'No parameter provided.'];
                }
                break;

            case 'customfields':
                $tmp_lang = false; //if not specified, use default language
                if (isset($params['language'])) {
                    $tmp_lang = $params['language'];
                } //check if a language has been specified
                $res = $this->getCustomFields($tmp_lang);
                if ($res != false) {
                    $output = ['success' => true, 'custom_fields' => $res];
                } else {
                    $output = ['success' => false, 'message' => 'Error: unable to retrieve custom fields.'];
                }
                break;

            case 'create':
            case 'createuser':
                $res = $this->createUser($params, $_POST);
                if (is_array($res)) {
                    $output = $res;
                } elseif ($res > 0) {
                    $output = ['success' => true, 'idst' => $res];
                } else {
                    $output = ['success' => false, 'message' => 'Error: unable to create new user.'];
                }
                break;

            case 'edit':
            case 'updateuser':
                if (count($params) > 0 && !isset($params['ext_not_found'])) { //params[0] should contain user id
                    $res = $this->updateUser($params['idst'], $_POST);

                    if ($res > 0) {
                        $output = ['success' => true];
                    } elseif ($res < 0) {
                        $output = ['success' => false, 'message' => 'Error: incorrect param idst.'];
                    }
                } else {
                    $output = ['success' => false, 'message' => 'Error: user id to update has not been specified.'];
                }
                break;

            case 'delete':
            case 'deleteuser':
                if (count($params) > 0 && !isset($params['ext_not_found'])) { //params[0] should contain user id
                    $output = $this->deleteUser($params['idst'], $_POST);
                } else {
                    $output = ['success' => false, 'message' => 'Error: user id to update has not been specified.'];
                }
                break;

            case 'userdetailsbyuserid':
                $acl_man = new FormaACLManager();
                $idst = $acl_man->getUserST($params['userid']);
                if (!$idst) {
                    $output = ['success' => false, 'message' => 'Error: invalid userid: ' . $params['userid'] . '.'];
                } else {
                    $output = $this->getUserDetails($idst);
                }
                break;

            case 'userdetailsfromcredentials':
                if (!isset($params['ext_not_found'])) {
                    $output = $this->getUserDetailsFromCredentials($_POST);
                }
                break;

            case 'updateuserbyuserid':
                if (count($params) > 0) { //params[0] should contain user id
                    $acl_man = new FormaACLManager();
                    $idst = $acl_man->getUserST($params['userid']);
                    if (!$idst) {
                        $output = ['success' => false, 'message' => 'Error: invalid userid: ' . $params['userid'] . '.'];
                    } else {
                        $res = $this->updateUser($idst, $_POST);
                        $output = ['success' => true];
                    }
                } else {
                    $output = ['success' => false, 'message' => 'Error: user id to update has not been specified.'];
                }
                break;

            case 'userCourses':
            case 'mycourses':
                if (!isset($params['ext_not_found'])) {
                    $output = $this->getMyCourses($params['idst'], $_POST);
                }
                break;

            case 'kbsearch':
                if (!isset($params['ext_not_found'])) {
                    $output = $this->KbSearch($params['idst'], $_POST);
                }
                break;

            case 'importextusers':
                $output = $this->importExternalUsers($_POST);
                break;

            case 'importextusersfromemail':
                $output = $this->importExternalUsersFromEmail($_POST);
                break;

            case 'countusers':
                $output = $this->countUsers($_POST);
                break;

            case 'checkregcode':
                $output = $this->checkRegistrationCode($_POST);
                break;

            case 'checkUsername':
            case 'checkusername':
                $output = $this->checkUsername($_POST);
                break;

            case 'assignprofile':
            case 'assignProfile':
                $output = $this->assignProfile($_POST);
                break;

            case 'admin_assignUsers':
            case 'admin_assignusers':
                $output = $this->admin_assignUsers($_POST, 'assign');
                break;

            case 'admin_revokeUsers':
            case 'admin_revokeusers':
                $output = $this->admin_assignUsers($_POST, 'revoke');
                break;

            case 'admin_assignCourses':
            case 'admin_assigncourses':
                $output = $this->admin_assignCourses($_POST);
                break;

// LRZ
            case 'newOrg':
            case 'neworg':
            case 'addorg':
                $output = $this->newOrg($_POST);

                break;

            case 'moveOrg':
            case 'moveorg':
                $output = $this->moveOrg($_POST);

                break;

            case 'moveOrgUser':
            case 'moveorguser':
                $output = $this->moveUserInOrg($_POST);
                break;

            case 'removeUserFromOrg':
            case 'removeuserfromorg':
                $output = $this->removeUserFromOrg($_POST);
                break;

            case 'renameOrg':
            case 'renameorg':
                $output = $this->renameOrg($_POST);
                break;

            case 'removeOrg':
            case 'removeorg':
                $output = $this->removeOrg($_POST);
                break;

            case 'downloadCertificate':
                $this->downloadCertificate();
                break;

            default:
                $output = parent::call($name, $_POST);
        }

        return $output;
    }
}
