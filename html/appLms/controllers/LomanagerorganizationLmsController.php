<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class LomanagerorganizationLmsController extends LomanagerLmsController
{
    public $name = 'lo-manager-organization';

    protected function setTab()
    {
        checkPerm('lesson', false, 'storage');
        $this->model->setTdb(LomanagerLms::ORGDIRDB, $_SESSION['idCourse']);
    }

    public function setCurrentTab()
    {
        $this->model->setCurrentTab(LomanagerLms::STORAGE_ORGDIRDB);
        echo json_encode(true);
        exit;
    }

    public function getTab()
    {        
        if (checkPerm('lesson', true, 'storage')) {
            return [
                'active' => $this->model->getCurrentTab() === LomanagerLms::STORAGE_ORGDIRDB,
                'controller' => 'lomanagerorganization',
                'edit' => true,
                'title' => Lang::t('_ORGROOTNAME', 'storage'),
                'data' => $this->getFolders($_SESSION['idCourse'], false),
                'currentState' => serialize([$this->getCurrentState(0)]),
            ];
        } else {
            return null;
        }
    }
}
