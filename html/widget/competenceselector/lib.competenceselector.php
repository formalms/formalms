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

class CompetenceselectorWidget extends Widget
{
    public $id = '';
    public $selected_category = 0;
    public $filter_text = '';
    public $show_descendants = false;
    public $selection = [];

    protected $json = null;

    public function __construct()
    {
        parent::__construct();
        $this->_widget = 'competenceselector';
    }

    public function init()
    {
        require_once _base_ . '/lib/lib.json.php';
        $this->json = new Services_JSON();

        Util::get_js(Forma\lib\Get::rel_path('base') . '/lib/lib.elem_selector.js', true, true);
        Util::get_js(Forma\lib\Get::rel_path('base') . '/lib/js_utils.js', true, true);

        //$js_path = Forma\lib\Get::rel_path('base').'/widget/competenceselector/';
        //Util::get_js($js_path.'competenceselector.js', true, true);
    }

    public function run()
    {
        if (!$this->id) {
            //..
            return false;
        }

        //render view
        $this->render('competenceselector', [
            'id' => $this->id,
            'selected_node' => (int) $this->selected_category,
            'filter_text' => (string) $this->filter_text,
            'show_descendants' => $this->show_descendants,
            'selection' => $this->selection,
        ]);
    }
}
