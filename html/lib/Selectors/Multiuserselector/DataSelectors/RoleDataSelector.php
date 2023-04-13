<?php
namespace FormaLms\lib\Selectors\Multiuserselector\DataSelectors;

require_once _adm_ . '/models/FunctionalrolesAdm.php';
class RoleDataSelector extends DataSelector{ 

    const ADDITIONAL_COLS = [];

    public function __construct() {
     
        $this->builder = new \FunctionalrolesAdm();
        $this->name = 'RolerDataSelector';

        parent::__construct();
    }

    public function getData($params = []) {
        $columnsFilter = [];
        $columns = array_key_exists('columns', $params) ? $params['columns'] : [];
        
        $activeSearch = array_key_exists('active_search', $params) ? (int) $params['active_search'] : false;
        
        foreach($columns as $column) {
            if($column['search']['value']!='') {
                $columnsFilter[$column['name']] = $column['search']['value'];
            }
        }
        $op = array_key_exists('op', $params) ? (string) $params['op'] : false;
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
            $sort = array_key_exists('column', $order) ? (string) $columns[$order['column']]['data'] != ''? (string) $columns[$order['column']]['data'] : '0' : '0';
            $dir = array_key_exists('dir', $order) ? (string) $order['dir'] : 'asc'; 
        } else {
            $sort = '0';
            $dir = 'asc';
        }

        
        $searchValue = array_key_exists('search', $params) ? (string) $params['search']['value'] : false;
        $total = $this->builder->getFunctionalRolesTotal($searchValue,  $columnsFilter);
        if($activeSearch) {
            $results = $total;
            $rowsPerPage = $total;
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

        $list = $this->builder->getFunctionalRolesList($pagination, ['text' => $searchValue],  $columnsFilter);
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
                'data' => 'name',
                'title' => \Lang::t('_NAME', 'standard'),
                'sortable' => true,
                'searchable' => true,
                'search_field' => 'text'
            ],
            [
                'data' => 'group',
                'title' => \Lang::t('_GROUPS', 'standard'),
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
                'data' => 'users',
                'title' => \Lang::t('_USERS', 'standard'),
                'sortable' => true,
                'searchable' => false,
                'search_field' => 'text'
            ],
        ];
    }

    
    protected function _selectAll($params = [], $columnsFilter = []){
        $filter = array_key_exists('filter', $params) ? (string) $params['filter'] : '';
        $output = $this->builder->getAllFunctionalRoles($filter, true,$columnsFilter);
        $output = $this->builder->getRoleInfoById($output);
     
        return $output;
    }


    public function getAllSelection($exclusions = []) {
        $allSelection = $this->builder->getAllFunctionalRoles($filter, true);
        return array_diff($allSelection, $exclusions);
    }

    protected function _getDynamicFilter($input){}

    protected function mapData($records, $filter = ''){

        //format models' data
        $list = [];
        $acl_man = \Forma::user()->getAclManager();
        if (is_array($list)) {
            foreach ($records as $record) {
                $_description = strip_tags($record->description);
                if (strlen($_description) > 100) {
                    $_description = substr($_description, 0, 97) . '...';
                }
                $list[] = [
                    'id' => (int) $record->id_fncrole,
                    'name' => highlightText($record->name, $filter),
                    'group' => highlightText($record->group_name, $filter),
                    'description' => highlightText($_description, $filter),
                    'users' => $record->users,
                ];
            }
        }

    
        return $list;
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

}