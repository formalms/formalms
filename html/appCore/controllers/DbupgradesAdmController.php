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

class DbupgradesAdmController extends AdmController
{
    protected $model;
    protected $json;

    public $link = 'adm/dbupgrades';

    public function init()
    {
        parent::init();
        require_once _base_ . '/lib/lib.json.php';
        $this->model = new DbupgradesAdm();
        $this->json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
    }

    public function showTask()
    {
        $this->render('show', [
                    'filter_text' => '',
            ]);
    }

    public function getdbupgradestabledataTask()
    {
        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 25));
        $rowsPerPage = FormaLms\lib\Get::req('rowsPerPage', DOTY_INT, $results);
        $sort = FormaLms\lib\Get::req('sort', DOTY_STRING, '');
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');
        $filter = FormaLms\lib\Get::req('filter', DOTY_STRING, '');

        $total = $this->model->getDbUpgradesTotal($filter);
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
        ];

        $list = $this->model->getDbUpgradesList($pagination, $filter);

        //format models' data
        $records = [];
        $acl_man = \FormaLms\lib\Forma::getAclManager();
        if (is_array($list)) {
            foreach ($list as $record) {
                $records[] = [
                    'script_id' => Layout::highlight($record->script_id, $filter),
                    'script_name' => Layout::highlight($record->script_name, $filter),
                    'script_description' => Layout::highlight($record->script_description, $filter),
                    'script_version' => Layout::highlight($record->script_version, $filter),
                    'core_version' => Layout::highlight($record->core_version, $filter),
                    'creation_date' => Format::date($record->creation_date, 'datetime'),
                    'execution_date' => Format::date($record->execution_date, 'datetime'),
                ];
            }
        }

        $output = [
            'startIndex' => $startIndex,
            'recordsReturned' => count($records),
            'sort' => $sort,
            'dir' => $dir,
            'totalRecords' => $total,
            'pageSize' => $rowsPerPage,
            'records' => $records,
        ];

        echo $this->json->encode($output);
    }
}
