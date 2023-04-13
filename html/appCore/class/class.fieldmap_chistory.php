<?php

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

require_once _adm_ . '/class/class.fieldmap.php';

class FieldMapChistory extends FieldMap
{
    public $lang = null;

    /**
     * class constructor.
     */
    public function __construct()
    {
        $this->lang = &FormaLanguage::createInstance('company', 'crm');

        parent::__construct();
    }

    public function _getMainTable()
    {
    }

    public function getPrefix()
    {
        return 'chistory_';
    }

    public function getPredefinedFieldLabel($field_id)
    {
        $res['description'] = $this->lang->def('_CHISTORY_DESCRIPTION');

        return $res[$field_id];
    }

    public function getRawPredefinedFields()
    {
        return ['description'];
    }

    /**
     * @param array $predefined_data
     * @param array $custom_data
     * @param int   $id              company id; if 0 a new company will be created
     * @param bool  $dropdown_id     if true will take dropdown values as id;
     *                               else will search the id starting from the value
     */
    public function saveFields($predefined_data, $custom_data = false, $id = 0, $dropdown_id = true)
    {
        require_once $GLOBALS['where_crm'] . '/modules/contacthistory/lib.contacthistory.php';

        $chdm = new ContactHistoryDataManager();
        $data = [];

        $company_id = (int) $predefined_data['company_id'];

        $data['contact_id'] = (int) $id;
        $data['title'] = $predefined_data['title'];
        $data['description'] = $predefined_data['description'];
        $data['reason'] = 0;
        $data['type'] = $predefined_data['type'];

        if (isset($predefined_data['meeting_date'])) {
            $data['meeting_date'] = $predefined_data['meeting_date'];
        } else {
            $data['meeting_date'] = date('Y-m-d H:i:s');
        }

        $chistory_id = $chdm->saveContactHistory($company_id, $data);

        return $chistory_id;
    }
}
