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
        $this->requestObj = $this->request->getMethod() == "POST" ? $this->request->request : $this->request->query;
        
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

       
        return $this;
    }

  

    public function list() {

        if($this->requestObj->has('selected_tab') && in_array($this->requestObj->get('selected_tab'), array_keys($this->tabs))) {
            $this->selection = $this->requestObj->get('selected_tab');
        }

       // $this->multiUserSelector->setDataSelectors('DataSelector', 'user');
        $requestParams = ($this->requestObj->has('params')) ? $this->requestObj->get('params') : [];
        $requestParams['op'] = 'selectall';
        $selectedData = []; //$this->multiUserSelector->retrieveDataSelector($this->selection)->getData($requestParams);
        //dd($selectedData);
        $this->render('show',['tabs' => $this->tabs,
                            'selection'=> $this->selection,
                            'data' => $selectedData]);
    }


    public function getDataTask()
    {
        
        $dataType = $this->requestObj->get('dataType');
        $params = array_merge($this->requestObj->all(), ['json_format' => true]);

        switch($dataType) {
            case "user":
                $response = $this->multiUserSelector->retrieveDataselector($dataType)->getData($params);
                break;
        }

        echo $response;

    }


}
