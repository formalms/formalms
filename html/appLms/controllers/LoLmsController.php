<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class LoLmsController extends LmsController
{
    public $name = 'lo';

    /** @var Services_JSON */
    protected $json;

    /**
     * @var LoLms
     */
    protected $model;

    protected $user_status;

    protected $idCourse;

    public function init()
    {
        checkPerm('view', false, 'organization');

        $this->model = new LoLms();
        $this->idCourse = $this->session->get('idCourse');

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
            $lo['actions'] = [];
            $lo['visible_actions'] = [];
            if (!$lo['is_folder']) {
                $lo['actions'][] = [
                    'name' => 'play',
                    'active' => true,
                    'type' => 'link',
                    'content' => "index.php?modname=organization&op=custom_playitem&id_item=$id",
                    'showIcon' => false,
                    'icon' => 'icon-play',
                    'label' => 'Play',
                ];
                if (array_key_exists('track_detail', $lo) && $lo['track_detail']) {
                    $lo['visible_actions'][] = [
                        'name' => 'tracking',
                        'active' => true,
                        'type' => 'link',
                        'content' => 'index.php?modname=organization&amp;op=track_details&amp;type=' . $lo['track_detail']['type'] . '&amp;id_user=' . $lo['track_detail']['is_user'] . '&amp;id_org=' . $lo['track_detail']['id_org'] . '"',
                        'showIcon' => false,
                        'icon' => 'icon-chart',
                        'label' => 'Tracking',
                    ];
                }
            }
            $results[] = $lo;
        }

        return $results;
    }

    public function get()
    {
        $id = FormaLms\lib\Get::req('id', DOTY_INT, 0);
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
}
