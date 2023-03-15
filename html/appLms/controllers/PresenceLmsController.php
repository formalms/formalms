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

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once _base_ . '/lib/lib.json.php';

class PresenceLmsController extends LmsController
{
    protected $model;
    protected $json;
    protected $permissions;

    protected $id_date;

    public function init()
    {
        $this->id_date = FormaLms\lib\Get::req('id_date', DOTY_INT, 0);
        $this->model = new PresenceLms($this->session->get('idCourse'), $this->id_date);
        $this->json = new Services_JSON();
        $this->permissions = [
            'view' => true,
        ];
    }

    protected function _getMessage($code)
    {
        switch ($code) {
            default:
                $message = '';
            break;
        }

        return $message;
    }

    public function presenceTask()
    {
        require_once _base_ . '/lib/lib.form.php';

        YuiLib::load();

        $user_date = $this->model->getUserDateForCourse(getLogUserId(), $this->session->get('idCourse'));
        $date_info = $this->model->getDateInfoForPublicPresence($user_date);

        if ($this->id_date == 0) {
            $this->id_date = (isset($date_info[0]['id_date']) ? $date_info[0]['id_date'] : 0);
        }

        $this->model->setIdDate($this->id_date);

        if (isset($_POST['save'])) {
            $this->model->savePresence();
        }

        foreach ($date_info as $info_date) {
            $date_for_dropdown[$info_date['id_date']] = $info_date['code'] . ' - ' . $info_date['name'] . ' (' . Format::date($info_date['date_begin'], 'date') . ')';
        }

        if ($this->id_date == 0) {
            $this->render('presence_empty', []);
        } else {
            $params = [];
            $params['model'] = $this->model;
            $params['dropdown'] = $date_for_dropdown;
            $params['tb'] = $this->model->getPresenceTable();
            $params['test_type'] = $this->model->getTestType();
            $params['date_for_dropdown'] = $date_for_dropdown;
            $this->render('presence', $params);
        }
    }
}
