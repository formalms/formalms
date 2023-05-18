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

class CommunicationLmsController extends LmsController
{
    public $name = 'communication';

    protected $_default_action = 'show';
    protected $json = null;

    protected $info = false;
    public $data;

    public function isTabActive($tab_name)
    {
        return true;
    }

    public function track_communication()
    {
        $id_comm = FormaLms\lib\Get::req('id_comm', DOTY_INT, 0);
        $model = new CommunicationAlms();
        $comm = $model->findByPk($id_comm, \FormaLms\lib\FormaUser::getCurrentUser()->getArrSt());

        if ($comm !== false && $comm['type_of'] == 'none') {
            $model->markAsRead($id_comm, \FormaLms\lib\FormaUser::getCurrentUser()->getId());
        }
        if (defined('IS_AJAX')) {
            echo json_encode($comm);
        } else {
            return $comm;
        }
    }

    public function init()
    {
        YuiLib::load('base,tabview');
        Lang::init('course');
        $this->json = new Services_JSON();

        $upd = new UpdatesLms();
        $this->info = $upd->communicationCounterUpdates();
    }

    public function showTask()
    {
        if (!$this->info['history'] && !$this->info['unread']) {
            $this->render('emptycommunication', []);

            return;
        }
        if ($this->info['history'] && !$this->info['unread']) {
            Util::jump_to('index.php?r=lms/communication/showhistory');
        }
        $this->render('_tabs', [
            'active_tab' => 'unread',
            'ajax_action' => 'gettabledata',
            'show_unread_tab' => $this->info['unread'],
            'show_history_tab' => $this->info['history'],
        ]);
    }

    public function showhistoryTask()
    {
        $this->render('_tabs', [
            'active_tab' => 'history',
            'ajax_action' => 'gethistorydata',
            'show_unread_tab' => $this->info['unread'],
            'show_history_tab' => $this->info['history'],
        ]);
    }

    public function gettabledata()
    {
        $start_index = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_MIXED, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'title');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'asc');

        $model = new CommunicationAlms();
        $communications = $model->findAllUnread(0, 0, 'publish_date', 'DESC', \FormaLms\lib\FormaUser::getCurrentUser()->getId(), [
            'viewer' => \FormaLms\lib\FormaUser::getCurrentUser()->getArrSt(),
        ]);

        foreach ($communications as $id => $comm) {
            //$communications[$id]['publish_date'] = Format::dateDistance($comm['publish_date']);
            switch ($comm['type_of']) {
                case 'none':
                        $communications[$id]['play'] = '<a class="ico-wt-sprite subs_unread" href="index.php?r=communication/play&amp;id_comm=' . $comm['id_comm'] . '"><span>'
                            . Lang::t('_MARK_AS_READ', 'communication')
                            . '</span></a>';

                    break;
                case 'file':
                        $communications[$id]['play'] = '<a class="ico-wt-sprite subs_download" href="index.php?r=communication/play&amp;id_comm=' . $comm['id_comm'] . '"><span>'
                            . Lang::t('_DOWNLOAD', 'communication')
                            . '</span></a>';

                    break;
                case 'scorm':
                        $communications[$id]['play'] = '<a class="ico-wt-sprite subs_play js-scorm_lightbox" rel="" href="index.php?r=communication/play&amp;id_comm=' . $comm['id_comm'] . '" title="' . $comm['title'] . '"><span>'
                            . Lang::t('_PLAY', 'communication')
                            . '</span></a>';

                    break;
            }

            $communications[$id]['title'] = '<b>' . $comm['title'] . '</b><br /><br />' . $comm['description'];
        }
        $result = [
            'totalRecords' => count($communications),
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($communications),
            'records' => $communications,
        ];

        $this->data = $this->json->encode($result);
        echo $this->data;
    }

    public function gethistorydata()
    {
        $start_index = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_MIXED, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'title');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'asc');

        $model = new CommunicationAlms();
        $communications = $model->findAllReaded(0, 0, 'publish_date', 'DESC', \FormaLms\lib\FormaUser::getCurrentUser()->getId(), [
            'viewer' => \FormaLms\lib\FormaUser::getCurrentUser()->getArrSt(),
        ]);

        foreach ($communications as $id => $comm) {
            //$communications[$id]['publish_date'] = Format::dateDistance($comm['publish_date']);
            switch ($comm['type_of']) {
                case 'none':
                        $communications[$id]['play'] = '<a class="ico-wt-sprite subs_unread" href="index.php?r=communication/play&amp;unread=1&amp;id_comm=' . $comm['id_comm'] . '"><span>'
                            . Lang::t('_MARK_AS_UNREAD', 'communication')
                            . '</span></a>';

                    break;
                case 'file':
                        $communications[$id]['play'] = '<a class="ico-wt-sprite subs_download" href="index.php?r=communication/play&amp;id_comm=' . $comm['id_comm'] . '"><span>'
                            . Lang::t('_DOWNLOAD', 'communication')
                            . '</span></a>';

                    break;
                case 'scorm':
                        $communications[$id]['play'] = '<a class="ico-wt-sprite subs_play js-scorm_lightbox" href="index.php?r=communication/play&amp;id_comm=' . $comm['id_comm'] . '" title="' . $comm['title'] . '"><span>'
                            . Lang::t('_PLAY', 'communication')
                            . '</span></a>';

                    break;
            }
        }
        $result = [
            'totalRecords' => count($communications),
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($communications),
            'records' => $communications,
        ];

        $this->data = $this->json->encode($result);
        echo $this->data;
    }

    /**
     * List all the unseen communications.
     */
    public function newTask()
    {
        $model = new CommunicationAlms();
        $communications = $model->findAllUnread(0, 0, 'publish_date', 'DESC', \FormaLms\lib\FormaUser::getCurrentUser()->getId(), [
            'viewer' => \FormaLms\lib\FormaUser::getCurrentUser()->getArrSt(),
        ]);
        $this->render('communication', [
            'communications' => $communications,
        ]);
    }

    public function historyTask()
    {
        $model = new CommunicationAlms();
        $communications = $model->findAllReaded(0, 0, 'publish_date', 'DESC', \FormaLms\lib\FormaUser::getCurrentUser()->getId(), [
            'viewer' => \FormaLms\lib\FormaUser::getCurrentUser()->getArrSt(),
        ]);
        $this->render('communication', [
            'communications' => $communications,
        ]);
    }

    public function playTask()
    {
        $id_comm = FormaLms\lib\Get::req('id_comm', DOTY_INT, 0);
        $unread = FormaLms\lib\Get::req('unread', DOTY_INT, 0);
        $model = new CommunicationAlms();
        $comm = $model->findByPk($id_comm, \FormaLms\lib\FormaUser::getCurrentUser()->getArrSt());

        if ($comm !== false) {
            switch ($comm['type_of']) {
                case 'none' :
                    if ($unread) {
                        $model->markAsUnread($id_comm, \FormaLms\lib\FormaUser::getCurrentUser()->getId());
                    } else {
                        $model->markAsRead($id_comm, \FormaLms\lib\FormaUser::getCurrentUser()->getId());
                    }

                    break;
                case 'file' :
                    $lo = createLO('item', $comm['id_resource'], 'communication');
                    if ($lo) {
                        $lo->env_play($id_comm, 'index.php?r=communication/show');
                    }

                    return;
                    break;
                case 'scorm' :
                    $lo = createLO('scormorg', $comm['id_resource'], 'communication');
                    if ($comm['id_resource'] != 0 && $lo) {
                        $lo->env_play($id_comm, 'index.php?r=communication/show');
                    }
                    break;
                default:
                    break;
            }
        } //endif
        UpdatesLms::resetCache();
        Util::jump_to('index.php?r=communication/show');
    }
}
