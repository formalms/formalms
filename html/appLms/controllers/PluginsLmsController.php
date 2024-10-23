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

class PluginsLmsController extends LmsController
{
    public $name = 'elearning';

    public $ustatus = [];
    public $cstatus = [];

    public $levels = [];

    public $path_course = '';

    protected $_default_action = 'show';

    public $info = [];

    public function isTabActive($tab_name)
    {
        /*switch($tab_name) {
            case "new" : {
                if(!isset($this->info['elearning'][0])) return false;
            };break;
            case "inprogress" : {
                if(!isset($this->info['elearning'][1])) return false;
            };break;
            case "completed" : {
                if(!isset($this->info['elearning'][2])) return false;
            };break;
        }*/
        return true;
    }

    public function init()
    {
    }

    public function showTask()
    {
        require_once _lms_ . '/lib/lib.middlearea.php';
        $ma = new Man_MiddleArea();
        $block_list = [];
        //if($ma->currentCanAccessObj('user_details_short')) $block_list['user_details_short'] = true;
        if ($ma->currentCanAccessObj('user_details_full')) {
            $block_list['user_details_full'] = true;
        }
        if ($ma->currentCanAccessObj('credits')) {
            $block_list['credits'] = true;
        }
        if ($ma->currentCanAccessObj('news')) {
            $block_list['news'] = true;
        }


        if (!empty($block_list)) {
            $this->render('_tabs_block', ['block_list' => $block_list]);
        } else {
            $this->render('_tabs', []);
        }


        require_once _lms_ . '/lib/lib.middlearea.php';
        $ma = new Man_MiddleArea();
        $this->render('pluginslist', [
            'pluginslist' => $pluginslist,
        ]);
    }

    public function active()
    {
        require_once _adm_ . '/models/PluginAdm.php';
        $plugin_adm = new PluginAdm();

        $active_plugins = $plugin_adm->getMainView('all');
    }
}
