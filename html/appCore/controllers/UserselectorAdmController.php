<?php

use FormaLms\lib\Selectors\Multiuserselector\MultiUserSelector;

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


class UserselectorAdmController extends AdmController
{
    protected $multiUserSelector;

    protected $tabFilters =  array();

    protected $requestObj;

    protected $selection = 'user';


    protected $tabs = ['user' => false,
                        'group' => false,
                        'org' => false,
                        'role' => false];

    public function init(){

        $this->multiUserSelector = new MultiUserSelector();
        $this->_mvc_name = 'multiuserselector';
        $this->requestObj = $this->request->getMethod() == 'POST' ? $this->request->request : $this->request->query;
        
        $tabs = ($this->requestObj->has('tab_filters')) ? $this->requestObj->get('tab_filters') : array_keys($this->tabs);

        foreach($tabs as $tabFilter) {

            if(!in_array($tabFilter, array_keys($this->tabs))) {
                //non è un filtro accettato lo skippo
                continue;
            }
            $this->tabs[$tabFilter] = true;
            $dataSelectorName = ucfirst($tabFilter) . 'DataSelector';
    
            
            $this->multiUserSelector->setDataSelectors($dataSelectorName, $tabFilter);
        }

        if($this->requestObj->has('instance') && $this->requestObj->has('id')) {
            $instanceType = $this->requestObj->get('instance');
            $instanceId = (int) $this->requestObj->get('id');
    
           
            $this->multiUserSelector->injectAccessModel($instanceType);
    
            $this->multiUserSelector->getAccessModel();
        }
        
        return $this;
    }

  

    public function show() {

        $selectedData = [];
        $accessSelection = [];
        $disableAjax = $this->requestObj->has('disable_ajax') ? true : false;
        $instanceValue = $this->requestObj->get('instance');
        $instanceId = $this->requestObj->get('id');
        $showSelectAll = $this->requestObj->get('showSelectAll') ?? false;
        $showUserAlert = $this->requestObj->get('showUserAlert') ?? false;
        $selectAllValue = 0;

        if($instanceValue && $instanceId) {
            $accessSelection = $this->multiUserSelector->getAccessList($instanceValue, $instanceId, true);
          
        }
        if($this->requestObj->has('selected_tab') && in_array($this->requestObj->get('selected_tab'), array_keys($this->tabs))) {
            $this->selection = $this->requestObj->get('selected_tab');
        }

        foreach($this->tabs as $tabKey => $tab) {
            $multiUserSelectorTab = $this->multiUserSelector->retrieveDataSelector($tabKey);
           
            $columns[$tabKey] = $multiUserSelectorTab->getColumns();
            if(count($multiUserSelectorTab::ADDITIONAL_COLS)) {
                $hiddenColumns = $multiUserSelectorTab->getHiddenColumns();
                $columns[$tabKey] = array_merge($columns[$tabKey], $hiddenColumns);
            }

            if($disableAjax) {
                $requestParams['op'] = 'selectall';
                $selectedData[$tabKey] = $multiUserSelectorTab->getData($requestParams);
            }
        }

        if($showSelectAll) {
            if(in_array($this->multiUserSelector->getSelectedAllValue(), $accessSelection['org'])) {
                $selectAllValue = $this->multiUserSelector->getSelectedAllValue();
            }
        }

        $this->render('show',['tabs' => $this->tabs,
                            'selection'=> $this->selection,
                            'columns' => $columns,
                            'ajax' => $disableAjax,
                            'selectedData' => $selectedData,
                            'instanceValue' => $instanceValue,
                            'instanceId' => $instanceId,
                            'accessSelection' => $accessSelection,
                            'showSelectAll' => $showSelectAll,
                            'showUserAlert' => $showUserAlert,
                            'selectAllValue' => $selectAllValue,
                            'debug' => $this->requestObj->has('debug') ? $this->requestObj->get('debug') : false
                        ]);
    }


    public function getDataTask()
    {
        
        $dataType = $this->requestObj->get('dataType');
        $params = array_merge($this->requestObj->all(), ['json_format' => true]);

        switch($dataType) {
            case 'user':
            case 'group':
            case 'role':
                $response = $this->multiUserSelector->retrieveDataselector($dataType)->getData($params);
               
               break;
            default:
                $params = $this->request->query->all();
             
                $response = $this->multiUserSelector->retrieveDataselector('org')->getData($params);
                break;
        }

        echo $response;

    }


    public function associate()
    {
        $instanceType = $this->requestObj->get('instance');
        $instanceId = (int) $this->requestObj->get('id');

        $selection =  explode(',', $this->requestObj->get('selected'));
        $exclusion =  explode(',', $this->requestObj->get('excluded'));
        $allSelections =  explode(',', $this->requestObj->get('allselection'));
        $allIdst =  (int) $this->requestObj->get('all_idst');
        
        //if allidst is checked empty all selections
    
        if($allIdst) {
            $allSelections = $selection = $exclusion = [];
            $selection[] = $allIdst;
        }
        if(count($allSelections)) {
            foreach($allSelections as $allSelection) {
                if(in_array($allSelection, array_keys($this->tabs))) {
                    $boundSelection = $this->multiUserSelector->retrieveDataselector($allSelection)->getAllSelection($exclusion);
            
                    $cleanArray = array_diff(array_unique($selection), $exclusion); //remove exclude delements
                
                    $selection = array_unique(array_merge($cleanArray, $boundSelection));
                }
                
            
            }
            
        }

        $result = $this->multiUserSelector->associate($instanceType, $instanceId, $selection);
        
        switch($result['type']) {
            case "redirect":
                return Util::jump_to($result['redirect']);
       
                break;
            
            case "render":
                $this->_mvc_name = $result['subFolderView'];
             
                return $this->render($result['view'], $result['params'], false, $result['additionalPaths']);
                break;
        }
        

    }


    public function getOrgChartData() {

        $accessSelection = [];
        $params = $this->requestObj->all();
        $instanceValue = $this->requestObj->get('instance');
        $instanceId = $this->requestObj->get('id');

        if($instanceValue && $instanceId) {
            $accessSelection = $this->multiUserSelector->getAccessList($instanceValue, $instanceId);
        }
    
        $params['selected_nodes'] = $accessSelection;
        echo $this->multiUserSelector->retrieveDataselector('org')->getData($params);
    }


}
