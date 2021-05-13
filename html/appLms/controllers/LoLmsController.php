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
        $this->idCourse = $_SESSION['idCourse'];

        $type = Get::req('type', DOTY_STRING, LoLms::ORGDIRDB);

        try {
            $this->model->setTdb($type, $this->idCourse);
        } catch (\Exception $exception) {
            $this->model->setTdb(LoLms::ORGDIRDB, $this->idCourse);
        }

        $this->json = new Services_JSON();
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
        $loData = array_values($this->model->getFolders($idCourse, $idFolder));
        return $this->formatLoData($loData);
    }

    private function getCurrentState($idFolder = false, $type = false)
    {
        if ($type) {
            try {
                $this->model->setTdb($type);
            } catch (\Exception $exception) {
                $this->model->setTdb(LoLms::ORGDIRDB);
            }
        }
        return $this->model->getCurrentState($idFolder);
    }

    public function setCurrentTab()
    {
        $type = Get::req('type', DOTY_STRING, LoLms::ORGDIRDB);

        echo $this->json->encode($this->model->setCurrentTab($type));
        exit;
    }

    public function show()
    {
        $this->render('show', [
            'data' => [
                'edit' => false,
                'type' => LoLms::ORGDIRDB,
                'title' => Lang::t('_ORGROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse, false, LoLms::ORGDIRDB),
            ],
        ]);
    }

    public function organization()
    {
        $lo_types = [
            [
                'title' => Lang::t('_DIRECTORY', 'organization_chart'),
                'type' => 'folder',
            ]
        ];
        $query = "SELECT objectType, className, fileName FROM %lms_lo_types";
        $rs = sql_query($query);
        while (list($type) = sql_fetch_row($rs)) {
            $lo_types[] = [
                'title' => Lang::t('_LONAME_' . $type, 'storage'),
                'type' => $type,
            ];
        }

        $activeTab = $status = [];
        if (isset($_SESSION['storage'])) {
            $status = unserialize($_SESSION['storage']);
            $activeTab = $status['tabview_storage_status'];
        } else {
            $activeTab = LoLms::STORAGE_TABS[LoLms::HOMEREPODIRDB];
        }

        $tabs = [
            [
                'active' => LoLms::STORAGE_TABS[LoLms::HOMEREPODIRDB] == $activeTab,
                'edit' => true,
                'type' => LoLms::HOMEREPODIRDB,
                'title' => Lang::t('_HOMEREPOROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse, false, LoLms::HOMEREPODIRDB),
                'currentState' => serialize([$this->getCurrentState(0, LoLms::HOMEREPODIRDB)]),
            ],
            [
                'active' => LoLms::STORAGE_TABS[LoLms::ORGDIRDB] == $activeTab,
                'type' => LoLms::ORGDIRDB,
                'edit' => true,
                'title' => Lang::t('_ORGROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse, false, LoLms::ORGDIRDB),
                'currentState' => serialize([$this->getCurrentState(0, LoLms::ORGDIRDB)]),
            ],
            [
                'active' => LoLms::STORAGE_TABS[LoLms::REPODIRDB] == $activeTab,
                'type' => LoLms::REPODIRDB,
                'edit' => true,
                'title' => Lang::t('_PUBREPOROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse, false, LoLms::REPODIRDB),
                'currentState' => serialize([$this->getCurrentState(0, LoLms::REPODIRDB)]),
            ],
        ];
        $this->render('organization', [
            'tabs' => $tabs,
            'lo_types' => $lo_types,
        ]);
    }

    function formatLoData($loData)
    {
        $results = [];
        foreach ($loData as $lo) {
            $type = $lo['typeId'];
            $id = $lo['id'];
            $lo["actions"] = [];
            if (!$lo["is_folder"]) {
                if ($lo["play"] && !$lo['canEdit']) {
                    $lo["actions"][] = [
                        "name" => "play",
                        "active" => true,
                        "type" => "link",
                        "content" => "index.php?modname=organization&op=custom_playitem&id_item=$id",
                        "showIcon" => false,
                        "icon" => "icon-play",
                        "label" => "Play",
                    ];
                } else if ($lo['canEdit']) {
                    $lo["actions"][] = [
                        "name" => "play",
                        "active" => true,
                        "type" => "link",
                        "content" => "index.php?modname=organization&op=custom_playitem&edit=1&id_item=$id",
                        "showIcon" => false,
                        "icon" => "icon-play",
                        "label" => "Play",
                    ];
                }
            }
            if ($lo['canEdit']) {
                if (!$lo["is_folder"]) {
                    $lo["actions"][] = [
                        "name" => "edit",
                        "active" => true,
                        "type" => "link",
                        "content" => "index.php?r=lms/lo/edit&id=$id&type=$type",
                        "showIcon" => true,
                        "icon" => "icon-edit",
                        "label" => "Edit",
                    ];
                }

                $lo["actions"][] = [
                    "name" => "properties",
                    "active" => true,
                    "type" => "submit",
                    "content" => "${type}[org_opproperties][$id]",
                    "showIcon" => true,
                    "icon" => "icon-properties",
                    "label" => "Properties",
                ];

                $lo["actions"][] = [
                    "name" => "access",
                    "active" => true,
                    "type" => "submit",
                    "content" => "${type}[org_opaccess][$id]",
                    "showIcon" => true,
                    "icon" => "icon-access",
                    "label" => "Access",
                ];

                if ($lo['canBeCategorized']) {
                    $lo["actions"][] = [
                        "name" => "categorize",
                        "active" => true,
                        "type" => "submit",
                        "content" => "${type}[org_opcategorize][$id]",
                        "showIcon" => true,
                        "icon" => "icon-categorize",
                        "label" => "Categorize",
                    ];
                }

                if (!$lo["is_folder"]) {
                    $lo["actions"][] = [
                        "name" => "copy",
                        "active" => true,
                        "type" => "ajax",
                        "content" => "index.php?r=lms/lo/copy&id=$id&type=$type&newType=",
                        "showIcon" => true,
                        "icon" => "icon-copy",
                        "label" => "Copy",
                    ];
                }

                $lo["actions"][] = [
                    "name" => "delete",
                    "active" => true,
                    "type" => "link",
                    "content" => "index.php?r=lms/lo/delete&id=$id&type=$type",
                    "showIcon" => true,
                    "icon" => "icon-delete",
                    "label" => "Delete",
                ];
            }
            $results[] = $lo;
        }
        return $results;
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
        $id = Get::req('id', DOTY_INT, false);
        $fromType = Get::req('type', DOTY_STRING, LoLms::ORGDIRDB);
        $newtype = Get::req('newtype', DOTY_STRING, false);
        if ($this->model->copy($id, $fromType)) {
            $this->model->setTdb($newtype);
            $this->model->paste(0);
        }
        echo $this->json->encode(true);
        exit();
    }
}
