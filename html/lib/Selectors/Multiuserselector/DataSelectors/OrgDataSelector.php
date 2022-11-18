<?php
namespace FormaLms\lib\Selectors\Multiuserselector\DataSelectors;


use FormaLms\lib\FolderTree\Extension\OrgDataNode;

require_once _adm_ . '/models/UsermanagementAdm.php';
require_once _base_ . '/widget/lib.widget.php';
class OrgDataSelector extends DataSelector{ 

    const ADDITIONAL_COLS = [];

    private $widgetBuilder;

    public function __construct() {
     
        $this->builder = new \UsermanagementAdm();
        $this->widgetBuilder = new \Widget();
        $this->name = 'OrgDataSelector';

        parent::__construct();
    }

    public function getData($params = []) {

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

             default:
                $node_id = array_key_exists('node_id', $params) ? (string) $params['node_id'] : '';
                $idOrg = $this->_getIdOrgByNodeId($node_id);
                $initial = array_key_exists('initial', $params) ? ((int) $params['initial'] > 0 ? true : false) : false;
                $output = [];
                $isSubadmin = false;
                $nodes = [];
              
                $userlevelid = $this->builder->getUserLevel();
                if ($userlevelid != ADMIN_GROUP_GODADMIN) {
                    $orgTree = $this->builder->_getAdminOrgTree();
                    $isSubadmin = true;
                }
                
                $_conversion_table = $this->builder->getOrgchartIdstConversionTable();
               
                $results = $this->builder->buildOrgChartNodes($idOrg, false, false, true);

                foreach($results as $result) {
                    $index = $result['idOrg'];
                    $id = $_conversion_table[0][$index] . '_' . $_conversion_table[1][$index];
                    $isNodeVisible = true;
                    $codeLabel = $result['code'];
                    if ($isSubadmin) {
                        $isForbidden = !in_array($result['idOrg'], $orgTree);
                        $countSubnodes = $this->builder->_checkSubnodesVisibility($result['idOrg'], $result['iLeft'], $result['iRight'], $orgTree);
                        $hasVisibleSubnodes = ($countSubnodes > 0);
                        if ($isForbidden && !$hasVisibleSubnodes) {
                            //forbidden with no visible subnodes:don't show it
                            $isNodeVisible = false;
                        } else {
                            if ($isForbidden) {
                                //forbidden, but with visible valid subnodes: show it
                                $label = $codeLabel . $result['translation'];
                                $hasChildren = true;
                    
                            } else {
                                //not forbidden, check as normal
                                $label = $codeLabel . $result['translation'];
                                $hasChildren = $hasVisibleSubnodes;
                            }
                        }
                    } else {
                        $label = $codeLabel . $result['translation'];
                        $hasChildren = !(($result['iRight'] - $result['iLeft']) == 1);
                        
                    }
                    //set node for output
                    if ($isNodeVisible) {
                        $nodes[] = new OrgDataNode($id, $label, $hasChildren);
                    }
                
                }
                //nella variabile c'è un array a 2 indici dove nel primo sono listati i grouppi con oc_ e nel secondo quelli con ocd_
                //l'array viene inziailizzato col nodo zero senza discendenti, il match avviene per chiave dei 2 array basata su idorg 

                if (is_array($nodes)) {
                    $output = [
                        'data' => $nodes
                    ];
                } 

                break;
        }

        dd($output);

        return $this->json->encode($output);

    }

    public function getColumns(){
        return [];
    }

    public function getHiddenColumns(){
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


    protected function _getRootNodeId()
    {
        $acl_man = \Docebo::user()->getACLManager();
        $arr_idst = $acl_man->getArrGroupST(['/oc_0', '/ocd_0']);

        return $arr_idst['/oc_0'] . '_' . $arr_idst['/ocd_0'];

    }

    public function getAllSelection($exclusions = []) {
        return [];
    }

    public function getChart($selection = [], $id = 'main') {
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
            'rootNodeId' => $this->_getRootNodeId() ?? 0,
            'initialSelectedNode' => 0,
            'initialSelectorData' => $selection,
            'canSelectRoot' => true,
            'show' => 'tree',
            'dragDrop' => false,
            'rel_action' => $orgchart_rel_action,
        ], false);

      
    }


    
    protected function _selectAll($params = [], $columnsFilter = []){}

    protected function _getDynamicFilter($input){}

    protected function mapData($records, $filter = ''){}

}