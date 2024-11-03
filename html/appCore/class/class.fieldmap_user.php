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

require_once _adm_ . '/class/class.fieldmap.php';

class FieldMapUser extends FieldMap
{
    public $lang = null;

    /**
     * class constructor.
     */
    public function __construct()
    {
        $this->lang = &FormaLanguage::createInstance('admin_directory', 'framework');

        parent::__construct();
    }

    public function getPrefix()
    {
        return 'user_';
    }

    public function getPredefinedFieldLabel($field_id)
    {
        $res['name'] = $this->lang->def('_FIRSTNAME');
        $res['lastname'] = $this->lang->def('_LASTNAME');
        $res['userid'] = $this->lang->def('_USERNAME');

        return $res[$field_id];
    }

    public function getRawPredefinedFields()
    {
        return ['name', 'lastname', 'userid'];
    }

    public function getCustomFields($with_prefix = true)
    {
        require_once _adm_ . '/lib/lib.field.php';

        $res = [];
        $fl = new FieldList();

        $acl_manager = \FormaLms\lib\Forma::getAclManager();
        $user_groups = [$acl_manager->getGroupRegisteredId()];

        $pfx = ($with_prefix ? $this->getPrefix() . 'custom_' : '');
        $field_list = $fl->getFieldsFromIdst($user_groups);

        foreach ($field_list as $field_id => $val) {
            $res[$pfx . $field_id] = $val[FIELD_INFO_TRANSLATION];
        }

        return $res;
    }

    /**
     * @param array $predefined_data
     * @param array $custom_data
     * @param int   $id              user id; if 0 a new user will be created
     * @param bool  $dropdown_id     if true will take dropdown values as id;
     *                               else will search the id starting from the value
     */
    public function saveFields($predefined_data, $custom_data, $id = 0, $dropdown_id = true)
    {
        require_once $GLOBALS['where_crm'] . '/modules/company/lib.company.php';

        // TODO: at this moment the function works only for user creation;
        // does not update the user if it already exists

        $acl = \FormaLms\lib\Forma::getAcl();
        $acl_manager = \FormaLms\lib\Forma::getAclManager();

        $data = [];

        $userid = $predefined_data['userid'];
        $firstname = $predefined_data['firstname'];
        $lastname = $predefined_data['lastname'];
        $pass = $predefined_data['pass'];
        $email = $predefined_data['email'];

        if (!empty($userid)) {
            $idst = $acl_manager->registerUser($userid, $firstname, $lastname,
                                                            $pass, $email, '', '');
        } else {
            $idst = false;
        }

        if ($idst !== false) {
            //  -- Add user to registered users group if not importing into root ---

            $idst_oc = $acl_manager->getGroup(false, '/oc_0');
            $idst_oc = $idst_oc[ACL_INFO_IDST];

            $idst_ocd = $acl_manager->getGroup(false, '/ocd_0');
            $idst_ocd = $idst_ocd[ACL_INFO_IDST];

            $acl_manager->addToGroup($idst_oc, $idst);
            $acl_manager->addToGroup($idst_ocd, $idst);

            //  -------------------------------------------------------------------|

            // add to group level
            $userlevel = $acl_manager->getGroupST(ADMIN_GROUP_USER);
            $acl_manager->addToGroup($userlevel, $idst);

            // -- Custom fields ----------------------------------------------------

            require_once _adm_ . '/lib/lib.field.php';

            $res = [];
            $fl = new FieldList();

            $custom_fields = array_keys($this->getCustomFields(false));
            $field_info_arr = $fl->getFieldsFromIdst($custom_fields);

            foreach ($custom_fields as $field_id) {
                // store direct
                if (isset($custom_data[$field_id])) {
                    $field_obj = &$fl->getFieldInstance($field_id);
                    //					$field_obj->setFieldEntryTable($company_entry_table);
                    $field_obj->storeDirect($idst, $custom_data[$field_id], $dropdown_id, false, true);
                }
            }
        }

        return $idst;
    }
}
