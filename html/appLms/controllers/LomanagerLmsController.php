<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class LomanagerLmsController extends LmsController
{
    public $name = 'lo-manager';

    /** @var Services_JSON */
    protected $json;

    /**
     * @var LomanagerLms $model
     */
    protected $model;

    public function init()
    {
        if (isset($_SESSION['idCourse'])) {
            $this->idCourse = (int)$_SESSION['idCourse'];
        }
        checkPerm('view', false, 'storage');
        $this->json = new Services_JSON();
        $this->model = new LomanagerLms();
        $this->setTab();
    }

    protected function setTab()
    {
    }

    protected function getFolders($idCourse, $idFolder = false)
    {
        $loData = array_values($this->model->getFolders($idCourse, $idFolder));
        switch($loData[0]['typeId']) {
            case LomanagerLms::ORGDIRDB: 
                return LomanagerorganizationLmsController::formatLoData($loData);
                break;
            case LomanagerLms::REPODIRDB:
                return LomanagerrepoLmsController::formatLoData($loData);
                break;
            case LomanagerLms::HOMEREPODIRDB:
                return LomanagerhomerepoLmsController::formatLoData($loData);
                break;
        }
    }

    protected function getCurrentState($idFolder = false)
    {
        return $this->model->getCurrentState($idFolder);
    }

    public function show()
    {
        $lo_types = $this->model->getLoTypes();

        $tabs_controllers = [
            new LomanagerhomerepoLmsController(),
            new LomanagerorganizationLmsController(),
            //new LomanagerrepoLmsController(), // TODO commenta prima di committare
        ];

        $tabs = [];

        foreach ($tabs_controllers as $t) {
            if ($tab = $t->getTab()) {
                $tabs[] = $tab;
            }
        }

        $this->render('show', [
            'tabs' => $tabs,
            'lo_types' => $lo_types,
        ]);
    }

    public function setCurrentTab()
    {
        echo $this->json->encode(false);
        exit;
    }

    public function get()
    {
        $id = Get::req('id', DOTY_INT, false);
        $responseData = [];
        $responseData['data'] = $this->getFolders($this->idCourse, $id);
        $responseData['currentState'] = serialize([$this->getCurrentState(0)]);
        echo $this->json->encode($responseData);
        exit;
    }

    public function getFolderTree()
    {
        $responseData = [];
        $responseData['data'] = $this->model->getFolderTree();
        echo $this->json->encode($responseData);
        exit;
    }

    public function delete()
    {
        $idsString = Get::req('ids', DOTY_MIXED, false);
        $ids = explode(',', $idsString);

        $responseData = [];
        if ($id = Get::req('id', DOTY_INT, false)) {
            $ids = [$id];
        }
        foreach ($ids as $id) {
            if ($id) {
                $res = $this->model->deleteFolder($id);
                $responseData[] = ['success' => $res, 'id' => $id];
            }
        }
        echo $this->json->encode($res);
        exit;
    }

    public function rename()
    {
        $id = Get::req('id', DOTY_INT, false);
        $newName = Get::req('newName', DOTY_STRING, false);
        echo $this->json->encode($this->model->renameFolder($id, $newName));
        exit;
    }

    public function move()
    {
        $idsString = Get::req('ids', DOTY_MIXED, false);
        $ids = explode(',', $idsString);
        $newParent = Get::req('newParent', DOTY_INT, false);

        $responseData = [];

        if ($ids && $newParent !== false) {
            foreach ($ids as $id) {
                $res = $this->model->moveFolder($id, $newParent);
                $responseData[] = ['success' => $res, 'id' => $id];
            }
        }
        die($this->json->encode($responseData));
    }

    public function reorder()
    {
        $newParent = Get::req('newParent', DOTY_INT, false);
        $newOrderString = Get::req('newOrder', DOTY_STRING, false);
        $newOrder = explode(",", $newOrderString);
        $newOrder = array_filter($newOrder);

        $responseData = [];

        if ($id = Get::req('id', DOTY_INT, false)) {
            $res = $this->model->reorder($id, $newParent, $newOrder ? $newOrder : null);
            $responseData = ['success' => $res, 'id' => $id];
        }
        die($this->json->encode($responseData));
    }

    public function edit()
    {
        $id = Get::req('id', DOTY_INT, false);

        require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
        $saveObj = new Session_Save();
        $saveName = $saveObj->getName('organization' . $_SESSION['idCourse'], true);
        $saveObj->save($saveName, $this->model->getTreeView()->getState());

        $folder = $this->model->getTdb()->getFolderById((string)$id);
        $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
        $lo->edit($folder->otherValues[REPOFIELDIDRESOURCE], 'index.php?r=lms/lomanager/completeAction');
    }

    public function createFolder()
    {
        $selectedNode = Get::req('selectedNode', DOTY_INT, false);
        $folderName = Get::req('folderName', DOTY_STRING, false);

        if (!$folderName) {
            header('HTTP/1.1 400');
            echo $this->json->encode(['error' => Lang::t('_NAME_REQUIRED', 'storage')]);
        } else {
            $this->model->addFolderById($selectedNode, $folderName, $this->idCourse);
            echo $this->json->encode(true);
        }
        exit;
    }

    public function copy()
    {
        $fromType = Get::req('type', DOTY_STRING, false);
        $newtype = Get::req('newtype', DOTY_STRING, false);

        if ($ids = Get::req('ids', DOTY_MIXED, false)) {
            $ids_arr = explode(',', $ids);
            $this->model->setTdb($fromType);
            foreach ($ids_arr as $id) {
                if ($id > 0 && $this->model->copy($id, $fromType)) {
                    $this->model->setTdb($newtype);
                    $this->model->paste(0);
                    $this->model->setTdb($fromType);
                }
            }
        }
        die($this->json->encode(true));
    }

    public function completeAction()
    {
        $this->model->completeAction();
        Util::jump_to('index.php?r=lms/lomanager/show');
    }
}
