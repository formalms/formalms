<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class LomanagerhomerepoLmsController extends LomanagerLmsController
{
    public $name = 'lo-manager-homerepo';

    protected function setTab()
    {
        checkPerm('home', false, 'storage');
        $this->model->setTdb(LomanagerLms::HOMEREPODIRDB, $_SESSION['idCourse']);
    }

    public function setCurrentTab()
    {
        $this->model->setCurrentTab(LomanagerLms::STORAGE_HOMEREPODIRDB);
        echo json_encode(true);
        exit;
    }

    public function getTab()
    {
        if (checkPerm('home', true, 'storage')) {
            return [
                'active' => $this->model->getCurrentTab() === LomanagerLms::STORAGE_HOMEREPODIRDB,
                'type' => LomanagerLms::HOMEREPODIRDB,
                'controller' => 'lomanagerhomerepo',
                'type' => $this->model::HOMEREPODIRDB,
                'edit' => true,
                'title' => Lang::t('_HOMEREPOROOTNAME', 'storage'),
                'data' => $this->getFolders($_SESSION['idCourse'], false),
                'currentState' => serialize([$this->getCurrentState(0)]),
            ];
        } else {
            return null;
        }
    }
}
