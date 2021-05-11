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
        checkPerm('view', false, 'organization');

        $this->model = new LoLms();
        $this->idCourse = $_SESSION['idCourse'];

        $this->model->setTdb($this->idCourse);

        $this->json = new Services_JSON();
    }

    private function getFolders($idCourse, $idFolder = false)
    {
        $loData = array_values($this->model->getFolders($idCourse, $idFolder));
        return $this->formatLoData($loData);
    }

    private function getCurrentState($idFolder = false)
    {
        return $this->model->getCurrentState($idFolder);
    }

    public function show()
    {
        $this->render('show', [
            'data' => [
                'edit' => false,
                'title' => Lang::t('_ORGROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse, false),
            ],
        ]);
    }

    private function formatLoData($loData)
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
}
