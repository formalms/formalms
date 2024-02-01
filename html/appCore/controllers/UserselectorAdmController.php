<?php

use FormaLms\lib\Selectors\Multiuserselector\MultiUserSelector;

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


class UserselectorAdmController extends AdmController
{
    protected $multiUserSelector;

    protected $tabFilters =  array();

    protected $requestObj;

    protected $requestArray = [];

    protected $selection = 'user';


    public $tabs = ['user' => false,
                        'group' => false,
                        'org' => false,
                        'role' => false];

    public function init()
    {
       
        $this->_mvc_name = 'multiuserselector';
        $this->requestObj = $this->request->getMethod() == 'POST' ? $this->request->request : $this->request->query;
        $this->requestArray = array_merge($this->request->request->all(), $this->request->query->all());
        $this->multiUserSelector = new MultiUserSelector($this->requestArray);

        if (array_key_exists('instance', $this->requestArray)) {
            $instanceType = $this->requestArray['instance'];

            $this->multiUserSelector->setAccessProcessor($instanceType);

            if(\Util::config('multiuserselector.use_filter.' . $instanceType)) {
                $this->tabs = array_diff_key($this->tabs, array_flip(['org', 'role']));
            }
        }

        $tabs = array_key_exists('tab_filters', $this->requestArray) ? $this->requestArray['tab_filters'] : array_keys($this->tabs);


        if(count($tabs)) {
            foreach ($tabs as $tabFilter) {
                if (!in_array($tabFilter, array_keys($this->tabs))) {
                    //non è un filtro accettato lo skippo
                    continue;
                }
                $this->tabs[$tabFilter] = true;
                $dataSelectorName = ucfirst($tabFilter) . 'DataSelector';
    
    
                $this->multiUserSelector->setDataSelectors($dataSelectorName, $tabFilter);
            }

            $this->selection = $tabs[0];
        }
        return $this;
    }



    public function show()
    {
        $selectedData = [];
        $accessSelection = [];
        $learningFilter = 'none';
        $idOrg = 0;
        $disableAjax = array_key_exists('disable_ajax', $this->requestArray) ? true : false;
        $instanceValue = $this->requestArray['instance']; 
        $instanceId = array_key_exists('id', $this->requestArray) ?  $this->requestArray['id'] : 0;
        $showSelectAll = (bool) array_key_exists('showSelectAll', $this->requestArray) ? $this->requestArray['showSelectAll'] : false;
        $showUserAlert = array_key_exists('showUserAlert', $this->requestArray) ? $this->requestArray['showUserAlert'] : false;
        $clearSelection = array_key_exists('clearSelection', $this->requestArray) ? $this->requestArray['clearSelection'] : false;
        $selectAllValue = 1;

        if ($instanceValue) {
            if ($clearSelection) {
                $this->multiUserSelector->getAccessProcessor()->setSessionData($instanceValue, []);
            }
            $accessSelection = $this->multiUserSelector->getAccessProcessor()->getSessionData($instanceValue, true);
            if ($instanceId) {
                $accessSelection = $this->multiUserSelector->getAccessList($instanceId, true);
            }
        }


        if (array_key_exists('selected_tab', $this->requestArray) && in_array($this->requestArray['selected_tab'], array_keys($this->tabs))) {
            $this->selection = $this->requestArray['selected_tab'];
        }

        foreach ($this->tabs as $tabKey => $tab) {

            if(!$tab) {
                continue;
            }
            $multiUserSelectorTab = $this->multiUserSelector->retrieveDataSelector($tabKey);

            $columns[$tabKey] = $multiUserSelectorTab->getColumns();
            if (count($multiUserSelectorTab::ADDITIONAL_COLS)) {
                $hiddenColumns = $multiUserSelectorTab->getHiddenColumns();
                $columns[$tabKey] = array_merge($columns[$tabKey], $hiddenColumns);
            }

            if ($disableAjax) {
                $requestParams['op'] = 'selectall';
                $selectedData[$tabKey] = $multiUserSelectorTab->getData($requestParams);
            }
        }

        if ($showSelectAll) {
            if (in_array($this->multiUserSelector->getSelectedAllValue(), $accessSelection['org'])) {
                $selectAllValue = $this->multiUserSelector->getSelectedAllValue();
            }
        }

        if(\Util::config('multiuserselector.use_filter.' . $instanceValue)) {
            $learningFilter = \Util::config('multiuserselector.use_filter.' . $instanceValue);
            $idOrg =  $instanceId;
        }

        //it needs to select radiobox all or selected customers
        $selectAllValue = (count($accessSelection['org']) == 1 && (int) reset($accessSelection['org']) == 1) ? 1 : 0;
        
        $renderParams = ['tabs' => $this->tabs,
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
                            'learningFilter' => $learningFilter,
                            'idOrg' => $idOrg,
                            'debug' => array_key_exists('debug', $this->requestArray) ? $this->requestArray['debug'] : false
        ];
      
        //fallback if i had to substitute some parmas with saved value
        $renderParams = array_replace($renderParams, $this->multiUserSelector->getInstanceParams((int) $instanceId));

        $this->render('show', $renderParams);
    }


    public function getDataTask()
    {
      
        $dataType = $this->requestArray['dataType'] ?? 'org';
        $params = array_merge($this->requestArray, ['json_format' => true]);

        switch($dataType) {
            case 'user':
            case 'group':
            case 'role':
                $response = $this->multiUserSelector->retrieveDataselector($dataType)->getData($params);

                break;
            default:
              

                $response = $this->multiUserSelector->retrieveDataselector('org')->getData($this->requestArray);
                break;
        }

        echo $response;
    }


    public function associate()
    {
      
        $instanceId = $this->requestArray['id'];

        $selection =  explode(',', $this->requestArray['selected']);
        $exclusion =  explode(',', $this->requestArray['excluded']);
        $allSelections =  explode(',', $this->requestArray['allselection']);
        $allIdst =  array_key_exists('all_idst', $this->requestArray) ? (int) $this->requestArray['all_idst'] : 0;

        //if allidst is checked empty all selections

       
        if ($allIdst) {
            $allSelections = $selection = $exclusion = [];
            $selection[] = $allIdst;
        } else {
            //check if 1 - all idst was in selection and extract it
            if(array_key_exists('all_idst', $this->requestArray) && in_array(1, $selection)) {
                $selection = array_diff($selection, [1]);
            }
        }
        if (count($allSelections)) {
            foreach ($allSelections as $allSelection) {
                if (in_array($allSelection, array_keys($this->tabs))) {
                    $boundSelection = $this->multiUserSelector->retrieveDataselector($allSelection)->getAllSelection($exclusion);

                    $cleanArray = array_diff(array_unique($selection), $exclusion); //remove exclude delements

                    $selection = array_unique(array_merge($cleanArray, $boundSelection));
                }
            }
        }

        $result = $this->multiUserSelector->associate($instanceId, $selection);
        $this->multiUserSelector->postProcess(compact('allIdst', 'instanceId', 'selection'));

        $responseMethod = 'response' . ucfirst($result['type']);
        if(method_exists($this, $responseMethod)) {
            $this->$responseMethod($result);
        } else {
            throw new Exception('$responseMethod not implemented');
        }
        
    }


    public function getOrgChartData()
    {
        $accessSelection = [];
       
        $instanceValue = $this->requestArray['instance'];
        $instanceId = $this->requestArray['id'];

        if ($instanceValue && $instanceId) {
            $accessSelection = $this->multiUserSelector->getAccessList($instanceValue, $instanceId);
        }

        $params['selected_nodes'] = $accessSelection;
        echo $this->multiUserSelector->retrieveDataselector('org')->getData($this->requestArray);
    }


    public function responseRedirect(array $params)
    {
        return Util::jump_to($params['redirect'],'', $params['folder']);
    }

    public function responseRender(array $params)
    {
        $this->_mvc_name = $params['subFolderView'];

        return $this->render($params['view'], $params['params'], false, $params['additionalPaths']);
    }
}
