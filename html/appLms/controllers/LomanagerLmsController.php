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

class LomanagerLmsController extends LmsController
{
    public $name = 'lo-manager';

    /** @var Services_JSON */
    protected $json;

    /**
     * @var LomanagerLms
     */
    protected $model;

    public function init()
    {
        if (isset($_SESSION['idCourse'])) {
            $this->idCourse = (int) $_SESSION['idCourse'];
        }
        checkPerm('view', false, 'storage');
        $this->json = new Services_JSON();
        $this->model = new LomanagerLms();
        $this->setTab();
    }

    protected function setTab()
    {
    }

    /**
     * @param $idCourse
     * @param false $idFolder
     *
     * @return array|void
     */
    protected function getFolders($idCourse, $idFolder = false)
    {
        $loData = array_values($this->model->getFolders($idCourse, $idFolder));
        $results = [];
        switch ($loData[0]['typeId']) {
            case LomanagerLms::ORGDIRDB:
                $results = LomanagerorganizationLmsController::formatLoData($loData);
                break;
            case LomanagerLms::REPODIRDB:
                $results = LomanagerrepoLmsController::formatLoData($loData);
                break;
            case LomanagerLms::HOMEREPODIRDB:
                $results = LomanagerhomerepoLmsController::formatLoData($loData);
                break;
            default:
        }
        if (!empty($loData)) {
            $eventData = Events::trigger(sprintf('lms.course_lo_%s.folder_listing', $loData[0]['typeId']), ['teacher' => true, 'idCourse' => $idCourse, 'idFolder' => $idFolder, 'learningObjects' => $results]);
            $results = $eventData['learningObjects'];
        }

        return $results;
    }

    protected function getCurrentState($idFolder = false)
    {
        return $this->model->getCurrentState($idFolder);
    }

    public function show()
    {
        if (Forma::errorsExists()) {
            UIFeedback::error(Forma::getFormattedErrors(true));
        }

        $lo_types = $this->model->getLoTypes();

        $tabsControllers = [];

        if (checkPerm('home', true, 'storage')) {
            $tabsControllers[] = new LomanagerhomerepoLmsController();
        }

        if (checkPerm('lesson', true, 'storage')) {
            $tabsControllers[] = new LomanagerorganizationLmsController();
        }

        if (checkPerm('public', true, 'storage')) {
            $tabsControllers[] = new LomanagerrepoLmsController();
        }

        $tabs = [];

        /** @var LomanagerLmsController $controller */
        foreach ($tabsControllers as $controller) {
            $tabs[] = $controller->getTab();
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
        $id = Forma\lib\Get::req('id', DOTY_INT, false);
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
        $idsString = Forma\lib\Get::req('ids', DOTY_MIXED, false);
        $ids = explode(',', $idsString);

        $responseData = [];
        if ($id = Forma\lib\Get::req('id', DOTY_INT, false)) {
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
        $id = Forma\lib\Get::req('id', DOTY_INT, false);
        $newName = Forma\lib\Get::req('newName', DOTY_STRING, false);
        echo $this->json->encode($this->model->renameFolder($id, $newName));
        exit;
    }

    public function move()
    {
        $idsString = Forma\lib\Get::req('ids', DOTY_MIXED, false);
        $ids = explode(',', $idsString);
        $newParent = Forma\lib\Get::req('newParent', DOTY_INT, false);

        $responseData = [];

        if ($ids && $newParent !== false) {
            foreach ($ids as $id) {
                $res = $this->model->moveFolder($id, $newParent);
                $responseData[] = ['success' => $res, 'id' => $id];
            }
        }
        exit($this->json->encode($responseData));
    }

    public function reorder()
    {
        $newParent = Forma\lib\Get::req('newParent', DOTY_INT, false);
        $newOrderString = Forma\lib\Get::req('newOrder', DOTY_STRING, false);
        $newOrder = explode(',', $newOrderString);
        $newOrder = array_filter($newOrder);

        $responseData = [];

        if ($id = Forma\lib\Get::req('id', DOTY_INT, false)) {
            $res = $this->model->reorder($id, $newParent, $newOrder ? $newOrder : null);
            $responseData = ['success' => $res, 'id' => $id];
        }
        exit($this->json->encode($responseData));
    }

    public function edit()
    {
        $id = Forma\lib\Get::req('id', DOTY_INT, false);

        require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
        $saveObj = new Session_Save();
        $saveName = $saveObj->getName('organization' . $_SESSION['idCourse'], true);
        $saveObj->save($saveName, $this->model->getTreeView()->getState());

        $folder = $this->model->getTdb()->getFolderById((string) $id);
        $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
        $lo->edit($folder->otherValues[REPOFIELDIDRESOURCE], 'index.php?r=lms/lomanager/completeAction');
    }

    public function createFolder()
    {
        $selectedNode = Forma\lib\Get::req('selectedNode', DOTY_INT, false);
        $folderName = Forma\lib\Get::req('folderName', DOTY_STRING, false);

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
        $fromType = Forma\lib\Get::req('type', DOTY_STRING, false);
        $newtype = Forma\lib\Get::req('newtype', DOTY_STRING, false);

        if ($ids = Forma\lib\Get::req('ids', DOTY_MIXED, false)) {
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
        exit($this->json->encode(true));
    }

    public function completeAction()
    {
        $this->model->completeAction();
        Util::jump_to('index.php?r=lms/lomanager/show');
    }

    public static function getLearningObjectIcon($learningObject)
    {
        switch ($learningObject['type']) {
            case 'item':
                $resource = DbConn::getInstance()->query('SELECT  title, path FROM %lms_materials_lesson WHERE idLesson = ' . (int) $learningObject['resource']);

                $result = DbConn::getInstance()->fetch_assoc($resource);
                $fileTypeArray = explode('.', $result['path']);

                return strtolower(end($fileTypeArray));
            default:
                return $learningObject['image_type'];
        }
    }
}
