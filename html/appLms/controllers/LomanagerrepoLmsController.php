<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class LomanagerrepoLmsController extends LomanagerLmsController
{
    public $name = 'lo-manager-repo';

    protected function setTab()
    {
        checkPerm('public', false, 'storage');
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
        if (checkPerm('public', true, 'storage')) {
            return [
                'active' => $this->model->getCurrentTab() === LomanagerLms::STORAGE_HOMEREPODIRDB,
                'controller' => 'lomanagerrepo',
                'edit' => true,
                'title' => Lang::t('_PUBREPOROOTNAME', 'storage'),
                'data' => $this->getFolders($_SESSION['idCourse'], false),
                'currentState' => serialize([$this->getCurrentState(0)]),
            ];
        } else {
            return null;
        }
    }
}
