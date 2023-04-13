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
        $tb_label = (FormaLms\lib\Get::sett('use_course_label', false) == 'off' ? false : true);
        if (!$tb_label) {
            $this->session->set('id_common_label', 0);
        } else {
            $id_common_label = FormaLms\lib\Get::req('id_common_label', DOTY_INT, -1);
            if ($id_common_label >= 0) {
                $this->session->set('id_common_label', $id_common_label);
            } elseif ($id_common_label <= -1) {
                $this->session->set('id_common_label', -1);
            }
            $block_list['labels'] = true;
        }
        $this->session->save();

        if ($tb_label) {
            require_once _lms_ . '/admin/models/LabelAlms.php';
            $label_model = new LabelAlms();
            $user_label = $label_model->getLabelForUser(\FormaLms\lib\FormaUser::getCurrentUser()->getId());
            $this->render('_tabs_block', ['block_list' => $block_list, 'use_label' => $tb_label, 'label' => $user_label, 'current_label' => $id_common_label]);
        } else {
            if (!empty($block_list)) {
                $this->render('_tabs_block', ['block_list' => $block_list, 'use_label' => $tb_label]);
            } else {
                $this->render('_tabs', []);
            }
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
