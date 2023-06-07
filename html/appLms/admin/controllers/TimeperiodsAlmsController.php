<?php

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

defined('IN_FORMA') or exit('Direct access is forbidden');

class TimeperiodsAlmsController extends AlmsController
{
    protected $model;
    protected $json;
    protected $permissions;

    /*
     * initialize the class
     */
    public function init()
    {
        parent::init();
        require_once _base_ . '/lib/lib.json.php';
        $this->json = new Services_JSON();
        $this->model = new TimeperiodsAlms();
        $this->permissions = [
            'view' => checkPerm('view', true, 'timeperiods', 'lms'),
            'add' => checkPerm('mod', true, 'timeperiods', 'lms'),
            'mod' => checkPerm('mod', true, 'timeperiods', 'lms'),
            'del' => checkPerm('mod', true, 'timeperiods', 'lms'),
        ];
    }

    protected function _getMessage($code)
    {
        $message = '';
        switch ($code) {
            case 'no permission': $message = ''; break;
        }

        return $message;
    }

    //--- operative methods ------------------------------------------------------

    /*
     * show the periods table
     */
    public function showTask()
    {
        //we will need some date input fields in popups; since we can't declare them
        //in an ajax response (for the moment), than prepare here the necessary script
        Form::loadDatefieldScript();

        //prepare view to render
        $params = [
            'permissions' => $this->permissions,
        ];
        $this->render('show', $params);
    }

    /*
     * requests table records
     */
    public function gettimeperiodslistTask()
    {
        //read input data (table parameters and filter)
        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem'));
        $rowsPerPage = FormaLms\lib\Get::req('rowsPerPage', DOTY_INT, $results);
        $sort = FormaLms\lib\Get::req('sort', DOTY_STRING, '');
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');

        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');
        $searchFilter = [
            'text' => $filter_text,
            //... TO DO: make dates filtrable too
        ];

        //calculate total records to display
        $total = $this->model->getTimePeriodsTotal($searchFilter);
        if ($startIndex >= $total) {
            if ($total < $results) {
                $startIndex = 0;
            } else {
                $startIndex = $total - $results;
            }
        }

        //get records from DB and format data
        $list = $this->model->getTimePeriodsList($startIndex, $results, $sort, $dir, $searchFilter);
        $output_records = [];
        if (is_array($list) && count($list) > 0) {
            foreach ($list as $record) {
                $output_records[] = [
                    'id' => (int) $record->id_period,
                    'title' => Layout::highlight($record->title, $filter_text),
                    'label' => Layout::highlight($record->label, $filter_text),
                    'start_date' => Format::date($record->start_date, 'date'),
                    'end_date' => Format::date($record->end_date, 'date'),
                    'mod' => 'ajax.adm_server.php?r=alms/timeperiods/mod&id=' . (int) $record->id_period,
                    'del' => 'ajax.adm_server.php?r=alms/timeperiods/del&id=' . (int) $record->id_period,
                ];
            }
        }

        //prepare the output for the datatable
        $output = [
            'totalRecords' => $total,
            'startIndex' => $startIndex,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $rowsPerPage,
            'results' => count($output_records),
            'records' => $output_records,
        ];

        echo $this->json->encode($output);
    }

    /*
     * create the mask to add a new time period
     */
    public function addTask()
    {
        if (!$this->permissions['add']) {
            $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
            echo $this->json->encode($output);
        }

        $this->render('add', [
            'json' => $this->json,
        ]);
    }

    /*
     * create the mask to edit a new time period
     */
    public function modTask()
    {
        if (!$this->permissions['mod']) {
            $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
            echo $this->json->encode($output);
        }

        $id = FormaLms\lib\Get::req('id', DOTY_INT, 0);

        //check if specified period is valid
        if ($id <= 0) {
            //if invalid, output an error message
            $output = [
                'success' => false,
                'message' => Lang::t('_INVALID_PERIOD_SPECIFIED'),
            ];
            echo $this->json->encode($output);
        } else {
            //extract DB data by id
            $record = $this->model->getTimePeriod($id);

            //render the dialog content
            $this->render('mod', [
                'id' => $id,
                'title' => $record->title,
                'start_date' => $record->start_date,
                'end_date' => $record->end_date,
                'json' => $this->json,
            ]);
        }
    }

    public function addactionTask()
    {
        if (!$this->permissions['add']) {
            $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
            echo $this->json->encode($output);
        }

        //prepare output variable
        $output = ['success' => false];

        $title = FormaLms\lib\Get::req('title', DOTY_STRING, '');
        $start_date = FormaLms\lib\Get::req('start_date', DOTY_STRING, '');
        $end_date = FormaLms\lib\Get::req('end_date', DOTY_STRING, '');

        $start_date = Format::dateDb($start_date, 'date');
        $end_date = Format::dateDb($end_date, 'date');

        //prepare parameters object
        $params = new stdClass();
        $params->title = $title;
        $params->start_date = $start_date;
        $params->end_date = $end_date;

        //update data in DB
        $output['success'] = $this->model->createTimePeriod($params);
        echo $this->json->encode($output);
    }

    public function modactionTask()
    {
        if (!$this->permissions['mod']) {
            $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
            echo $this->json->encode($output);
        }

        //prepare output variable
        $output = ['success' => false];

        //read inputs and validate data
        $id = FormaLms\lib\Get::req('id', DOTY_INT, 0);
        if ($id <= 0) {
            echo $this->json->encode($output);

            return;
        }

        $title = FormaLms\lib\Get::req('title', DOTY_STRING, '');
        $start_date = FormaLms\lib\Get::req('start_date', DOTY_STRING, '');
        $end_date = FormaLms\lib\Get::req('end_date', DOTY_STRING, '');

        $start_date = Format::dateDb($start_date, 'date');
        $end_date = Format::dateDb($end_date, 'date');

        //prepare parameters object
        $params = new stdClass();
        $params->id = $id;
        $params->title = $title;
        $params->start_date = $start_date;
        $params->end_date = $end_date;

        //update data in DB
        $output['success'] = $this->model->updateTimePeriod($params);
        echo $this->json->encode($output);
    }

    public function delTask()
    {
        if (!$this->permissions['del']) {
            $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
            echo $this->json->encode($output);
        }

        //prepare output variable
        $output = ['success' => false];

        $id = FormaLms\lib\Get::req('id', DOTY_INT, 0);
        if ($id <= 0) {
            echo $this->json->encode($output);

            return;
        }

        //delete data in DB
        $output['success'] = $this->model->deleteTimePeriod($id);
        echo $this->json->encode($output);
    }
}
