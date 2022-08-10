<?php
namespace FormaLms\lib\Selectors\Multiuserselector\DataSelectors;

require_once _adm_ . '/models/UsermanagementAdm.php';
require_once _base_ . '/widget/lib.widget.php';
class OrgDataSelector extends DataSelector{ 


    public function __construct() {
     
        $this->builder = new \UsermanagementAdm();
        $this->widgetBuilder = new \Widget();
        $this->name = 'OrgDataSelector';

        parent::__construct();
    }

    public function getData($params = []) : string  {

        $command = array_key_exists('command', $params) ? (string) $params['command'] : '';
        
        switch ($command) {
            case 'expand':
                $node_id = array_key_exists('node_id', $params) ? (string) $params['node_id'] : '';
                $idOrg = $this->_getIdOrgByNodeId($node_id);
                $initial = array_key_exists('initial', $params) ? ((int) $params['initial'] > 0 ? true : false) : false;

                $_conversion_table = $this->builder->getOrgchartIdstConversionTable();

                if ($initial) {
                    //get selected node from session and set the expanded tree
                    $idOrg = $this->_getSelectedNode();
                    $nodes = $this->builder->getOrgChartInitialNodes($idOrg, true);
                    //create actions for every node
                    $this->_assignActions($nodes, $_conversion_table);
                    //set output
                    if (is_array($nodes)) {
                        $output = [
                            'success' => true,
                            'nodes' => $nodes,
                            'initial' => $initial,
                        ];
                    } else {
                        $output = ['success' => false];
                    }
                } else {
                    //extract node data
                    $nodes = $this->builder->getOrgChartNodes($idOrg, false, false, true);
                    //create actions for every node
                    for ($i = 0; $i < count($nodes); ++$i) {
                        $index = $nodes[$i]['id'];
                        $nodes[$i]['id'] = $_conversion_table[0][$index] . '_' . $_conversion_table[1][$index];
                        $nodes[$i]['options'] = $this->_getNodeActions($nodes[$i]);
                    }
                    //set output
                    $output = [
                        'success' => true,
                        'nodes' => $nodes,
                        'initial' => $initial,
                    ];
                }
                
             break;

            case 'set_selected_node':
                $node_id = array_key_exists('node_id', $params) ? (string) $params['node_id'] : '';
                $idOrg = $this->_getIdOrgByNodeId($node_id);
                $this->_setSelectedNode($idOrg);
             break;
        }

        return $this->json->encode($output);

    }

    public function getColumns(){
        return [];
    }

    protected function _getSelectedNode()
    {
        return 0;
    }

    protected function _setSelectedNode($idOrg)
    {
    }

    protected function _assignActions(&$nodes, &$conversion_table)
    {
        if (!is_array($nodes)) {
            return;
        }
        for ($i = 0; $i < count($nodes); ++$i) {
            $index = $nodes[$i]['node']['id'];
            $nodes[$i]['node']['id'] = $conversion_table[0][$index] . '_' . $conversion_table[1][$index];
            $nodes[$i]['node']['options'] = $this->_getNodeActions($nodes[$i]['node']);
            if (isset($nodes[$i]['children']) && count($nodes[$i]['children']) > 0) {
                $this->_assignActions($nodes[$i]['children'], $conversion_table);
            }
        }
    }

    protected function _getNodeActions($node)
    {
        $output = [];

        return $output;
    }

    protected function _getIdOrgByNodeId($node_id)
    {
        if (!$node_id) {
            return 0;
        }
        $arr = explode('_', $node_id);
        $acl_man = \Docebo::user()->getACLManager();
        $groupid = $acl_man->getGroupId((int) $arr[0]);

        return (int) str_replace('/oc_', '', $groupid);
    }

    public function getChart($id = 'main') {
        $_languages = [
            '_ROOT' => \FormaLms\lib\Get::sett('title_organigram_chart', \Lang::t('_ORG_CHART', 'organization_chart')),
            '_YES' => \Lang::t('_CONFIRM', 'organization_chart'),
            '_NO' => \Lang::t('_UNDO', 'organization_chart'),
            '_LOADING' => \Lang::t('_LOADING', 'standard'),
            '_AREYOUSURE' => \Lang::t('_AREYOUSURE', 'organization_chart'),
            '_NAME' => \Lang::t('_NAME', 'standard'),
            '_RADIO_NO' => \Lang::t('_NO', 'standard'),
            '_RADIO_YES' => \Lang::t('_YES', 'standard'),
            '_RADIO_INHERIT' => \Lang::t('_INHERIT', 'standard'),
        ];
        
        $orgchart_rel_action = '<a class="" id="orgchart_unselect_all_' . $id . '" href="javascript:;" '
            . ' title="' . \Lang::t('_UNSELECT_ALL', 'organization_chart') . '">'
            . '<span>' . \Lang::t('_UNSELECT_ALL', 'organization_chart') . '</span>'
            . '</a>';

        return  $this->widgetBuilder->widget('tree', [
            'id' => 'orgchart_selector_tree_' . $id,
            'ajaxUrl' => 'ajax.adm_server.php?r=adm/userselector/getData', //widget/userselector/getorgcharttreedata 
            'treeClass' => 'SelectorTree',
            'treeFile' => \FormaLms\lib\Get::rel_path('base') . '/widget/tree/selectortree.js',
                'options' => ['simple' => $this->show_orgchart_simple_selector],
            'languages' => $_languages,
            'rootNodeId' => isset($root_node_id) ? $root_node_id : 0,
            'initialSelectedNode' => (int) $selected_node,
            'initialSelectorData' => $initial_selection,
            'canSelectRoot' => isset($can_select_root) ? (bool) $can_select_root : true,
            'show' => 'tree',
            'dragDrop' => false,
            'rel_action' => $orgchart_rel_action,
        ], false);

      
    }


    
    protected function _selectAll($params = []){}

    protected function _getDynamicFilter($input){}

    protected function mapData($records, $filter = ''){}

}