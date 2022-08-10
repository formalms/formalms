<?php
namespace FormaLms\lib\Selectors\Multiuserselector\DataSelectors;

require_once _adm_ . '/models/UsermanagementAdm.php';
require_once _adm_ . '/lib/lib.field.php';
class UserDataSelector extends DataSelector{ 

    protected $filedList;

    public function __construct() {
     
        $this->builder = new \UsermanagementAdm();
        $this->fieldList = new \FieldList();
        $this->name = 'UserDataSelector';
        
        parent::__construct();
    }

    public function getData($params = []){

        $op = array_key_exists('op', $params) ? (string) $params['op'] : false;
        switch ($op) {
            case 'selectall':
                return $this->_selectAll($params);

             break;
        }

        $columns = array_key_exists('columns', $params) ? (int) $params['columns'] : [];
        $idOrg = array_key_exists('id_org', $params) ? (int) $params['id_org'] : 0;
        $descendants = false; //(FormaLms\lib\Get::req('descendants', DOTY_INT, 0) > 0 ? true : false);
        $startIndex = array_key_exists('start', $params) ? (int) $params['start'] : 0;
        $results = array_key_exists('length', $params) ? (int) $params['length'] : \FormaLms\lib\Get::sett('visuItem', 25);
        $rowsPerPage = array_key_exists('rowsPerPage', $params) ? (int) $params['rowsPerPage'] : $results;
        if(array_key_exists('order', $params)) {

            $order = $params['order'][0];
            $sort = array_key_exists('column', $order) ? (string) $columns[$params['column']]['data'] != ''? (string) $columns[$params['column']]['data'] : '0' : '0';
            $dir = array_key_exists('dir', $order) ? (string) $order['dir'] : 'asc'; 
        } else {
            $sort = '0';
            $dir = 'asc';
        }
        $searchValue = array_key_exists('search', $params) ? (string) $params['search']['value'] : false;
        $learning_filter = array_key_exists('learning_filter', $params) ? (string) $params['learning_filter'] : 'none'; 

        $var_fields = array_key_exists('_dyn_field', $params) ? (array) $params['_dyn_field'] : [];
        if (stristr($sort, '_dyn_field_') !== false) {
            $index = str_replace('_dyn_field_', '', $sort);
            $sort = $var_fields[(int) $index];
        }

        $searchFilter = [
            'text' => $searchValue,
            'suspended' => (array_key_exists('suspended', $params) && (int) $params['suspended'] > 0) ? true : false,
        ];

        $dyn_filter = $this->_getDynamicFilter(array_key_exists('dyn_filter', $params) ? (string) $params['dyn_filter'] : '');
        if ($dyn_filter !== false) {
            $searchFilter['dyn_filter'] = $dyn_filter;
        }

        $total = (int) $this->builder->getTotalUsers(0, $descendants, $searchFilter, true, $learning_filter);
        if ($startIndex >= $total) {
            if ($total < $results) {
                $startIndex = 0;
            } else {
                $startIndex = $total - $results;
            }
        }

        $pagination = [
            'startIndex' => $startIndex,
            'results' => $results,
            'sort' => $sort,
            'dir' => $dir,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'rowsPerPage' => $rowsPerPage,
        ];

        $list = $this->builder->getUsersList($idOrg, $descendants, $pagination, $searchFilter, true, $learning_filter);

        $records = $this->mapData($list, $searchValue);

        $pagination['data'] = $records;
     
        if(array_key_exists('json_format', $params)) {
            $pagination = $this->json->encode($pagination);
        }
        
        return $pagination;

    }

      /*
     * return an instance of dynamic filter for users
     */
    protected function _getDynamicFilter($input)
    {
        $output = false;
     
        if (is_string($input) && $input != '') {
            $dyn_data = $this->json->decode(urldecode(stripslashes($input))); //decode the filter json string
            //die($input."\n\n".urldecode(stripslashes($input))."\n\n".'<pre>'.print_r($dyn_data, true).'</pre>');
            if (isset($dyn_data['exclusive']) && isset($dyn_data['filters'])) { //required fields
                if (count($dyn_data['filters']) > 0) { //there must be any filter selected
                    $output = $dyn_data;
                }
            }
        }

        return $output;
    }

    protected function mapData($records, $filter = ''){

        $date_fields = $this->fieldList->getFieldsByType('date');

        $acl_man = \Docebo::user()->getAclManager();
        $idst_org = $acl_man->getGroupST('/oc_' . (int) $idOrg);
        $output_results = [];
        if (is_array($records) && count($records) > 0) {
            foreach ($records as $idst => $record) {
                $query = 'SELECT params'
                        . " FROM %lms_organization_access WHERE idOrgAccess = '" . $idOrg . "' AND kind='user' AND value='" . $record['idst'] . "'";
                $relation = sql_fetch_row(sql_query($query));

                $record_row = [
                    'id' =>  $record['idst'],
                    'userid' => \Layout::highlight($acl_man->relativeId($record['userid']), $filter_text),
                    'firstname' => \Layout::highlight($record['firstname'], $filter),
                    'lastname' => \Layout::highlight($record['lastname'], $filter),
                    'relation' => isset($relation[0]) ? $relation[0] : '',
                    'email' => \Layout::highlight($record['email'], $filter),
                    'register_date' => \Format::date($record['register_date'], 'datetime'),
                    'lastenter' => \Format::date($record['lastenter'], 'datetime'),
                    'unassoc' => $idOrg > 0 ? ($record['is_descendant'] ? 0 : 1) : -1,
                    'valid' => $record['valid'],
                    'mod' => 'ajax.adm_server.php?r=adm/usermanagement/moduser&id=' . (int) $idst,
                    'del' => 'ajax.adm_server.php?r=adm/usermanagement/deluser&id=' . (int) $idst,
                ];

                foreach ($var_fields as $i => $value) {
                    if (is_numeric($value)) {
                        $name = '_custom_' . $value;
                    } else {
                        $name = $value;
                    }

                    //check if we must perform some post-format on retrieved field values
                    $content = (isset($record[$name]) ? $record[$name] : '');
                    if ($name == 'register_date') {
                        $content = \Format::date($content, 'datetime');
                    }
                    if ($name == 'lastenter') {
                        $content = \Format::date($content, 'datetime');
                    }
                    if ($name == 'level' && $content != '') {
                        $content = Lang::t('_DIRECTORY_' . $content, 'admin_directory');
                    }
                    if (!empty($date_fields) && in_array($value, $date_fields)) {
                        $content = \Format::date(substr($content, 0, 10), 'date');
                    }

                    $record_row['_dyn_field_' . $i] = $content;
                }

                $output_results[] = $record_row;
            }
        }


        return $output_results;
    }

     /*
     * list of all selected users by their idst
     */
    protected function _selectAll($params = [])
    {
        $idOrg = 0; //FormaLms\lib\Get::req('id_org', DOTY_INT, 0);
        $descendants = false; //(FormaLms\lib\Get::req('descendants', DOTY_INT, 0) > 0 ? true : false);
        $filter_text = array_key_exists('filter_text', $params) ? (string) $params['filter_text'] : '';
        $learning_filter = array_key_exists('learning_filter', $params) ? (string) $params['learning_filter'] : 'none'; 
        $searchFilter = [
            'text' => $filter_text,
            'suspended' => (array_key_exists('suspended', $params) && (int) $params['suspended'] > 0) ? true : false,
        ];
        $dyn_filter = $this->_getDynamicFilter(array_key_exists('dyn_filter', $params) ? (string) $params['dyn_filter'] : '');
        if ($dyn_filter !== false) {
            $searchFilter['dyn_filter'] = $dyn_filter;
        }
        $users = $this->builder->getAllUsers($idOrg, $descendants, $searchFilter, true, $learning_filter);
        $output = $this->builder->getUsersDetails($users);
        return $output;
    }

    public function getColumns(){

        return [
            [
                'data' => 'userid',
                'title' => \Lang::t('_USERNAME', 'standard'),
                'sortable' => true,
                'searchable' => true
            ],
            [
                'data' => 'lastname',
                'title' => \Lang::t('_LASTNAME', 'standard'),
                'sortable' => true,
                'searchable' => true
            ],
            [
                'data' => 'firstname',
                'title' => \Lang::t('_NAME', 'standard'),
                'sortable' => true,
                'searchable' => true
            ],
            [
                'data' => 'email',
                'title' => \Lang::t('_EMAIL', 'standard'),
                'sortable' => true,
                'searchable' => true
            ],
            [
                'data' => 'lastenter',
                'title' => \Lang::t('_DATE_LAST_ACCESS', 'standard'),
                'sortable' => true,
                'searchable' => false
            ],
            [
                'data' => 'register_date',
                'title' => \Lang::t('_REGISTER_DATE', 'standard'),
                'sortable' => true,
                'searchable' => false
            ]
        ];
    }

}