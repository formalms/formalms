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


class DomainconfigAdmController extends AdmController
{

    protected $model;

    protected $requestObj;

    protected $title = ' _DOMAIN_CONFIG';
    public array $queryString;
    public UsermanagementAdm $userModel;
    public MailconfigAdm $mailModel;

    public function init()
    {
        parent::init();
        
        $this->model = new DomainconfigAdm();
        $this->mailModel = new MailconfigAdm();
        $this->userModel = new UsermanagementAdm();
        $this->requestObj = $this->request->request->all();

        $this->queryString = $this->request->query->all();

    }


    public function show() {
        if (Forma::errorsExists()) {
            UIFeedback::error(Forma::getFormattedErrors(true));
        }

        $params = $this->getFieldsParams();
        $params['title'] = $this->title;

       
        $params['domains'] = $this->model->get($this->queryString['domainConfigId'], $params['mailConfigs'], $params['orgs']);
        $params['insertUrl'] = 'index.php?r=adm/domainconfig/insert';
        if($this->queryString['domainConfigId']) {
            $params['item'] = $this->model->read($this->queryString['domainConfigId']);
            $params['insertUrl'] .= '&parentId='.$this->queryString['domainConfigId'];
        }

        
    
        $this->render('show', $params);
    }


    public function insert() {
        if (Forma::errorsExists()) {
            UIFeedback::error(Forma::getFormattedErrors(true));
        }
        $params = $this->getFieldsParams();
        $params['title'] = Lang::t('_INSERT', 'standard');

        if($this->queryString['parentId']) {
            $params['parentId'] = $this->queryString['parentId'];
        }
        
        $this->render('view', $params);
    }

    public function edit() {
        if (Forma::errorsExists()) {
            UIFeedback::error(Forma::getFormattedErrors(true));
        }
        $params = $this->getFieldsParams();
        $params['title'] = Lang::t('_MOD', 'standard');

        if($this->queryString['id']) {
            $params['item'] = $this->model->read($this->queryString['id']);
            $params['id'] = $this->queryString['id'];
        }
        
        $this->render('view', $params);
    }

  

    public function save() {
        
        if(!$this->model->save($this->requestObj)) {
            $view = 'view';
        } else {
            $view = 'show';
            if($this->requestObj['parentId']) {
                $view .= '&domainConfigId=' . $this->requestObj['parentId'];
            }

            
            if($this->requestObj['id'] && $this->model->read($this->requestObj['id'])['parentId']) {
                ;
                $view .= '&domainConfigId=' . $this->model->read($this->requestObj['id'])['parentId'];
            }

        }
        return Util::jump_to('index.php?r=adm/domainconfig/'.$view);
        
    }

    public function getFieldsParams() {

        $templates = getTemplateList();
        $params['templates'] = array_combine($templates, $templates);
        $params['mailConfigs'] = $this->mailModel->getList();
        $tree_names = $this->userModel->getAllFolders(false);
        
        $nodes = [];
        foreach ($tree_names as &$node) {
            $node_name = $node->translation ?: $node->code;
            $nodes[$node->idOrg] = addslashes($node_name);
        }
        asort($nodes);

        $params['orgs'] = $nodes;

        return $params;
    }

    public function delete() {

        $backlink = 'index.php?r=adm/domainconfig/show';
        $item = $this->model->read($this->queryString['id']);
        $result = $this->model->delete($this->queryString['id']);
        if($item['parentId'] && $this->model->checkChildren($item['parentId'])) {
            $backlink .= '&domainConfigId=' . $item['parentId'];
        }
        
        return Util::jump_to($backlink);
        
    }

    

}