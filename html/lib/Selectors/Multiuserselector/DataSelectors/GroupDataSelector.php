<?php
namespace FormaLms\lib\Selectors\Multiuserselector\DataSelectors;

require_once _adm_ . '/models/GroupmanagementAdm.php';
class GroupDataSelector extends DataSelector{ 

    const ADDITIONAL_COLS = [];

    public function __construct() {
     
        $this->builder = new \GroupmanagementAdm();
        $this->name = 'GroupDataSelector';

        parent::__construct();
    }

    public function getData($params = []) {
      
        $columnsFilter = [];
        $op = array_key_exists('op', $params) ? (string) $params['op'] : false;
        $columns = array_key_exists('columns', $params) ? $params['columns'] : [];
        foreach($columns as $column) {
            if($column['search']['value']!='') {
                $columnsFilter[$column['name']] = $column['search']['value'];
            }
        }
        switch ($op) {
            case 'selectall':
                return $this->_selectAll($params, $columnsFilter);

             break;
        }

        
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
        $total = $this->builder->getTotalGroups($searchValue, $learning_filter, $columnsFilter);

        $pagination = [
            'startIndex' => $startIndex,
            'results' => $results,
            'sort' => $sort,
            'dir' => $dir,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'rowsPerPage' => $rowsPerPage,
        ];

        $list = $this->builder->getGroupsList($pagination, $searchValue, $learning_filter, $columnsFilter);

        $records = $this->mapData($list, $searchValue);

        $pagination['data'] = $records;
     
        if(array_key_exists('json_format', $params)) {
            $pagination = $this->json->encode($pagination);
        }
        
        return $pagination;
    }

    public function getColumns(){

        return [
            [
                'data' => 'groupid',
                'title' => \Lang::t('_TITLE', 'standard'),
                'sortable' => true,
                'searchable' => true,
                'search_field' => 'text'
            ],
            [
                'data' => 'description',
                'title' => \Lang::t('_DESCRIPTION', 'standard'),
                'sortable' => true,
                'searchable' => true,
                'search_field' => 'text'
            ],
            [
                'data' => 'usercount',
                'title' => \Lang::t('_USERS', 'standard'),
                'sortable' => true,
                'searchable' => false
            ],
        ];
    }

    public function getHiddenColumns(){

        $hiddenColumns = [];
        foreach(self::ADDITIONAL_COLS as $additonalCol) {
            
            $hiddenColumns[] = [
                'data' => $additonalCol,
                'title' => \Lang::t('_'.strtoupper($additonalCol), 'standard'),
                'sortable' => false,
                'searchable' => false,
                'search_field' => 'text',
                'visible' => false
            ];
        }
        
        return $hiddenColumns;
    }

    protected function _selectAll($params = [], $columnsFilter = []){

        $filter_text = array_key_exists('filter_text', $params) ? (string) $params['filter_text'] : '';
        $output = $this->builder->getAllGroups($filter_text, true, $columnsFilter);
        $output = $this->builder->getAllGroupDetails($output);
       
        return $output;
    }

    protected function _getDynamicFilter($input){}

    protected function mapData($records, $filter = ''){

        $list = [];
        $acl_man = \Docebo::user()->getAclManager();
        if (is_array($records)) {
            foreach ($records as $record) {
                $_groupid = $acl_man->relativeId($record->groupid);
                $_description = strip_tags($record->description);
                if (strlen($_description) > 100) {
                    $_description = substr($_description, 0, 97) . '...';
                }
                $list[] = [
                    'id' => (int) $record->idst,
                    'groupid' => highlightText($_groupid, $filter),
                    'description' => highlightText($_description, $filter),
                    'usercount' => $record->usercount,
                ];
            }
        }

        return $list;
    }

}