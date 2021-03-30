<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class LoLmsController extends LmsController
{

    public $name = 'lo';

    /** @var Services_JSON */
    protected $json;

    /**
     * @var LoLms $model
     */
    protected $model;

    protected $user_status;

    protected $idCourse;

    function init()
    {
        $this->model = new LoLms();
        $this->json = new Services_JSON();

        $this->idCourse = $_SESSION['idCourse'];
    }

    private function getFolders($idCourse, $idFolder = false, $type = false)
    {
        if ($type) {
            try {
                $this->model->setTdb($type);
            } catch (\Exception $exception) {
                $this->model->setTdb(LoLms::ORGDIRDB);
            }
        }
        return array_values($this->model->getFolders($idCourse, $idFolder));
    }

    private function getCurrentState($idCourse, $idFolder = false)
    {
        return $this->model->getCurrentState($idCourse, $idFolder);
    }

    public function show()
    {
        $tabs = [
            [
                'active' => true,
                'type' => LoLms::HOMEREPODIRDB,
                'title' => Lang::t('_HOMEREPOROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse,false,LoLms::HOMEREPODIRDB),
            ],
            [
                'type' => LoLms::ORGDIRDB,
                'title' => Lang::t('_ORGROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse,false,LoLms::ORGDIRDB),
            ],
            [
                'type' => LoLms::REPODIRDB,
                'title' => Lang::t('_PUBREPOROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse,false,LoLms::REPODIRDB),
            ],
        ];
        $this->render('show', ['tabs' => $tabs]);
    }

    public function organization()
    {
        $this->render('organization', array([
            'teacher' => true,
            'data' => [
                'alias' => LoLms::ORGDIRDB,
                'type' => LoLms::ORGDIRDB,
                'title' => Lang::t('_ORGROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse),
            ],
        ]));
    }

    public function get()
    {
        $id = Get::req('id', DOTY_INT, false);
        $responseData = [];
        $responseData['data'] = $this->getFolders($this->idCourse, $id);
        $responseData['currentState'] = serialize([$this->getCurrentState($this->idCourse, 0)]);
        echo $this->json->encode($responseData);
    }

    public function delete()
    {
        $id = Get::req('id', DOTY_INT, false);
        $type = Get::req('type', DOTY_INT, false);

        echo $this->json->encode($this->model->deleteFolder($this->idCourse, $id, $type));
    }

    public function rename()
    {
        $id = Get::req('id', DOTY_INT, false);
        $newName = Get::req('newName', DOTY_STRING, false);

        echo $this->json->encode($this->model->renameFolder($this->idCourse, $id, $newName));
    }

    public function move()
    {
        $id = Get::req('id', DOTY_INT, false);
        $newParentId = Get::req('newParentId', DOTY_INT, false);

        echo $this->json->encode($this->model->moveFolder($this->idCourse, $id, $newParentId));
    }

    public function reorder()
    {
        $id = Get::req('id', DOTY_INT, false);
        $newParent = Get::req('newParent', DOTY_INT, false);
        $newOrderString = Get::req('newOrder', DOTY_STRING, false);
        $newOrder = explode(",", $newOrderString);

        $responseData = ['success' => false];

        if ($id && $newParent !== false) {

            if ($this->model->reorder($this->idCourse, $id, $newParent, $newOrder ? $newOrder : null)) {
                $responseData = ['success' => true];
            }
        }
        echo $this->json->encode($responseData);
    }

    public function edit()
    {
        $tdb = new OrgDirDb($this->idCourse, array());

        $tree_view = new Org_TreeView($tdb, 'organization');

        require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
        $saveObj = new Session_Save();
        $saveName = $saveObj->getName('organization' . $_SESSION['idCourse'], true);
        $saveObj->save($saveName, $tree_view->getState());

        $id = Get::req('id', DOTY_INT, false);

        $folder = $tdb->getFolderById((string)$id);
        $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
        $lo->edit($folder->otherValues[REPOFIELDIDRESOURCE], 'index.php?r=lms/lo/organization&id_course=1');
    }
}
