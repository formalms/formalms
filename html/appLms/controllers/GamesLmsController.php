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

class GamesLmsController extends LmsController
{
    public $name = 'games';
    protected $_default_action = 'show';
    protected $json = null;

    protected $info = false;

    public function isTabActive($tab_name)
    {
        return true;
    }

    public function init()
    {
        YuiLib::load('base,tabview');
        Lang::init('course');
        $this->json = new Services_JSON();

        $upd = new UpdatesLms();
        $this->info = $upd->gamesCounterUpdates();
    }

    public function showTask()
    {
        if (!$this->info['history'] && !$this->info['unread']) {
            $this->render('emptygames', []);

            return;
        }
        if ($this->info['history'] && !$this->info['unread']) {
            Util::jump_to('index.php?r=lms/games/showhistory');
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

        $model = new GamesAlms();
        $games = $model->findAllUnread(0, 0, 'start_date', 'DESC', Docebo::user()->getId(), [
                    'viewer' => Docebo::user()->getArrSt(),
        ]);
        foreach ($games as $id => $games) {
            $game['start_date'] = Format::date($game['start_date'], 'date');
            $game['end_date'] = Format::date($game['end_date'], 'date');

            if (($game['type_of'] == 'scorm') and (($game['status'] == '') or (($game['status'] != 'completed') and ($game['status'] != 'passed')))) {
                $games[$id]['play'] = '<a class="ico-wt-sprite subs_play js-scorm_lightbox" href="index.php?r=games/play&amp;id_game=' . $game['id_game'] . '" title="' . $game['title'] . '"><span>' . Lang::t('_PLAY', 'games') . '</span></a>';
            } else {
                $games[$id]['play'] = '';
            }

            $games[$id]['standings'] = '<a href="index.php?r=lms/games/standings&amp;id_game=' . $game['id_game'] . '" title="' . Lang::t('_STANDINGS', 'games') . ': ' . $game['title'] . '"><span>'
                    . Lang::t('_STANDINGS', 'games')
                    . '</span></a>';
        }
        $result = [
            'totalRecords' => count($games),
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($games),
            'records' => $games,
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

        $model = new GamesAlms();
        $games = $model->findAllReaded(0, 0, 'start_date', 'DESC', Docebo::user()->getId(), [
                    'viewer' => Docebo::user()->getArrSt(),
        ]);
        $result = [
            'totalRecords' => count($games),
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($games),
            'records' => $games,
        ];

        $this->data = $this->json->encode($result);
        echo $this->data;
    }

    /**
     * List all the unseen games.
     */
    public function newTask()
    {
        $model = new GamesAlms();
        $games = $model->findAllUnread(0, 0, 'start_date', 'DESC', Docebo::user()->getId(), [
                    'viewer' => Docebo::user()->getArrSt(),
        ]);
        $this->render('games', [
            'games' => $games,
        ]);
    }

    public function historyTask()
    {
        $model = new GamesAlms();
        $games = $model->findAllReaded(0, 0, 'start_date', 'DESC', Docebo::user()->getId(), [
                    'viewer' => Docebo::user()->getArrSt(),
        ]);
        $this->render('games', [
            'games' => $games,
        ]);
    }

    public function playTask()
    {
        $id_game = FormaLms\lib\Get::req('id_game', DOTY_INT, 0);
        $model = new GamesAlms();
        $game = $model->findByPk($id_game, Docebo::user()->getArrSt());

        if ($game != false) {
            switch ($game['type_of']) {
                case 'scorm':
                        $lo = createLO('scormorg', $game['id_resource'], 'games');
                        if ($game['id_resource'] != 0 && $lo) {
                            $lo->env_play($id_game, 'index.php?r=games/show');
                        }

                    break;
            }
        } //endif
        Util::jump_to('index.php?r=games/show');
    }

    public function standingsTask()
    {
        $id_game = FormaLms\lib\Get::req('id_game', DOTY_INT, 0);
        $model = new GamesAlms();
        $game = $model->findByPk($id_game, Docebo::user()->getArrSt());

        YuiLib::load('base,charts');

        $this->render('standings', [
            'game' => $game,
            'track' => $model->getUserStandings($game['id_game'], getLogUserId()),
            'standings' => $model->getStandings($game['id_game'], 0, 30),
            'chart_data' => $this->json->encode($model->getStandingsChartData($game['id_game'])),
        ]);
    }
}
