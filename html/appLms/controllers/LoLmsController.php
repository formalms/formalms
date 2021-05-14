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
                'type' => 'organization',
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
                    $lo["actions"][] = [
                        "name" => "tracking",
                        "active" => true,
                        "type" => "link",
                        "content" => 'index.php?modname=organization&amp;op=track_details&amp;type=' . $lo['track_detail']["type"] . '&amp;id_user='.$lo['track_detail']["is_user"].'&amp;id_org='.$lo['track_detail']["id_org"].'"',
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
        $id = Get::req('id', DOTY_INT, false);
        $responseData = [];
        $responseData['data'] = $this->getFolders($this->idCourse, $id);
        $responseData['currentState'] = serialize([$this->getCurrentState(0)]);
        echo $this->json->encode($responseData);
        exit;
    }
}
