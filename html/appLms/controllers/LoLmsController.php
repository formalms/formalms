<?php defined("IN_FORMA") or die('Direct access is forbidden.');


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
        checkPerm('view', false, 'organization');

        $this->model = new LoLms();
        $this->idCourse = $_SESSION['idCourse'];

        $this->model->setTdb($this->idCourse);

        $this->json = new Services_JSON();
    }

    private function getFolders($idCourse, $idFolder = false)
    {
        $loData = array_values($this->model->getFolders($idCourse, $idFolder));
        $results = $this->formatLoData($loData);

        if (!empty($loData)) {
            $eventData = Events::trigger(sprintf('lms.course_lo_%s.folder_listing', $loData[0]['typeId']), ['teacher' => false, 'idCourse' => $idCourse, 'idFolder' => $idFolder, 'learningObjects' => $results]);
            $results = $eventData['learningObjects'];
        }

        return $results;
    }

    private function getCurrentState($idFolder = false)
    {
        return $this->model->getCurrentState($idFolder);
    }

    public function show()
    {
        if (Forma::errorsExists()) {
            UIFeedback::error(Forma::getFormattedErrors(true));
        }

        $this->render('show', [
            'data' => [
                'edit' => false,
                'title' => Lang::t('_ORGROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse, false),
                'type' => 'organization',
            ],
        ]);
    }

    private function formatLoData($loData)
    {
        $results = [];
        foreach ($loData as $lo) {
            $id = $lo['id'];
            $lo['image_type'] = LomanagerLmsController::getLearningObjectIcon($lo);
            $lo["actions"] = [];
            $lo["visible_actions"] = [];
            if (!$lo["is_folder"]) {
                $lo["actions"][] = [
                    "name" => "play",
                    "active" => true,
                    "type" => "link",
                    "content" => "index.php?modname=organization&op=custom_playitem&id_item=$id",
                    "showIcon" => false,
                    "icon" => "icon-play",
                    "label" => "Play",
                ];
                if ($lo['track_detail']) {
                    $lo["visible_actions"][] = [
                        "name" => "tracking",
                        "active" => true,
                        "type" => "link",
                        "content" => 'index.php?modname=organization&amp;op=track_details&amp;type=' . $lo['track_detail']["type"] . '&amp;id_user=' . $lo['track_detail']["is_user"] . '&amp;id_org=' . $lo['track_detail']["id_org"] . '"',
                        "showIcon" => false,
                        "icon" => "icon-chart",
                        "label" => "Tracking",
                    ];
                }
            }
            $results[] = $lo;
        }
        return $results;
    }

    public function get()
    {
        $id = Get::req('id', DOTY_INT, 0);
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
    }/* 

    public function delete()
    {
        $id = Get::req('id', DOTY_INT, false);
        $ids = Get::req('ids', DOTY_MIXED, null);

        $ids = $ids ? explode(',', $ids) : [$id];

        $res = [];
        foreach ($ids as $id) {
            if ($id > 0) {
                $res[] = $this->model->deleteFolder($id);
            }
        }

        die($this->json->encode($res));
    }

    public function rename()
    {
        $id = Get::req('id', DOTY_INT, false);
        $newName = Get::req('newName', DOTY_STRING, false);
        $type = Get::req('type', DOTY_STRING, LoLms::ORGDIRDB);
        $this->model->setCurrentTab($type);

        echo $this->json->encode($this->model->renameFolder($id, $newName));
        exit;
    }

    public function move()
    {
        $id = Get::req('id', DOTY_INT, false);
        $newParentId = Get::req('newParentId', DOTY_INT, false);

        echo $this->json->encode($this->model->moveFolder($id, $newParentId));
        exit;
    }

    public function reorder()
    {
        $ids = Get::req('id', DOTY_MIXED, '');
        $newParent = Get::req('newParent', DOTY_INT, false);
        $newOrderString = Get::req('newOrder', DOTY_STRING, false);
        $newOrder = explode(",", $newOrderString);
        $newOrder = array_filter($newOrder);

        $responseData = ['success' => false];

        if ($ids && $newParent !== false) {
            $ids_arr = explode(',', $ids);
            foreach ($ids_arr as $id) {
                if ($this->model->reorder($id, $newParent, $newOrder)) {
                    $responseData = ['success' => true];
                }
            }
        }
        die($this->json->encode($responseData));
    }

    public function edit()
    {
        $id = Get::req('id', DOTY_INT, false);
        $type = Get::req('type', DOTY_STRING, LoLms::ORGDIRDB);

        $tdb = $this->model->setTdb($type, $_SESSION['idCourse']);
        $tree_view = new Org_TreeView($tdb, 'organization');

        require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
        $saveObj = new Session_Save();
        $saveName = $saveObj->getName('organization' . $_SESSION['idCourse'], true);
        $saveObj->save($saveName, $tree_view->getState());

        $folder = $tdb->getFolderById((string)$id);
        $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
        $lo->edit($folder->otherValues[REPOFIELDIDRESOURCE], 'index.php?r=lms/lo/organization&id_course=1');
    }

    public function createFolder()
    {
        $selectedNode = Get::req('selectedNode', DOTY_INT, false);
        $folderName = Get::req('folderName', DOTY_STRING, false);
        // $currentState = Get::req('currentState', DOTY_STRING, false);
        $type = Get::req('type', DOTY_STRING, LoLms::ORGDIRDB);
        $this->model->setCurrentTab($type);

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
        $fromType = Get::req('type', DOTY_STRING, LoLms::ORGDIRDB);
        $newtype = Get::req('newtype', DOTY_STRING, false);

        if ($ids = Get::req('ids', DOTY_MIXED, false)) {
            $ids_arr = explode(',', $ids);
            foreach ($ids_arr as $id) {
                if ($id > 0 && $this->model->copy($id, $fromType)) {
                    $this->model->setTdb($newtype);
                    $this->model->paste(0);
                    $this->model->setTdb($fromType);
                }
            }
        }
        die($this->json->encode(true));
    } */
}
