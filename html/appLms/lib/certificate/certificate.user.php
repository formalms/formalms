<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

require_once(__DIR__ . '/certificate.base.php');

class CertificateSubs_User extends CertificateSubstitution
{

    function getSubstitutionTags()
    {

        $subs = [];
        $subs['[meta_assoc]'] = Lang::t('_META_ASSOC', 'certificate', 'lms');
        $subs['[display_name]'] = Lang::t('_DISPLAY_NAME', 'certificate', 'lms');
        $subs['[username]'] = Lang::t('_USERNAME', 'certificate', 'lms');
        $subs['[firstname]'] = Lang::t('_FIRSTNAME', 'certificate', 'lms');
        $subs['[lastname]'] = Lang::t('_LASTNAME', 'certificate', 'lms');

        //variable fields
        require_once($GLOBALS['where_framework'] . '/lib/lib.field.php');
        $temp = new FieldList();
        $fields = $temp->getFlatAllFields();

        foreach ($fields as $key => $value) {
            $subs['[userfield_' . $key . ']'] = Lang::t('_USERFIELD', 'certificate', 'lms') . ' "' . $value . '"';
        }

        return $subs;
    }

    function getSubstitution()
    {

        $subs = [];

        $aclman =& Docebo::user()->getAclManager();
        $user = $aclman->getUser($this->id_user, false);

        if ($this->id_meta) {
            $sql = "SELECT title, description FROM %lms_certificate_meta WHERE idMetaCertificate = " . $this->id_meta;
            $query = sql_query($sql);
            list($title_meta, $description_meta) = sql_fetch_row($query);

            if ($title_meta) {
                $subs['[meta_assoc]'] = $title_meta;
            }
        }

        $subs['[display_name]'] = ($user[ACL_INFO_LASTNAME] . $user[ACL_INFO_FIRSTNAME]
            ? $user[ACL_INFO_LASTNAME] . ' ' . $user[ACL_INFO_FIRSTNAME]
            : $aclman->relativeId($user[ACL_INFO_USERID]));

        $subs['[username]'] = $aclman->relativeId($user[ACL_INFO_USERID]);
        $subs['[firstname]'] = $user[ACL_INFO_FIRSTNAME];
        $subs['[lastname]'] = $user[ACL_INFO_LASTNAME];

        //variable fields
        require_once($GLOBALS['where_framework'] . '/lib/lib.field.php');

        $temp = new FieldList();
        $fields = $temp->getFlatAllFields();
        foreach ($fields as $key => $value)
            $subs['[userfield_' . $key . ']'] = $temp->showFieldForUser($this->id_user, $key);

        return $subs;
    }
}
