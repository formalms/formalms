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

class kbcategorizeWidget extends Widget
{
    public $ajaxUrl = '';
    public $show = false;
    public $res_id = 0;
    public $original_name = '';
    public $r_param = '';
    public $r_item_id = 0;
    public $r_type = '';
    public $r_env = '';
    public $r_env_parent_id = null;
    public $language = false;
    public $back_url = '';
    public $form_url = '';
    public $form_extra_hidden = [];
    public $session = null;

    /**
     * Constructor.
     *
     * @param <string> $config the properties of the table
     */
    public function __construct()
    {
        parent::__construct();
        $this->_widget = 'kbcategorize';
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function run()
    {
        require_once _lms_ . '/lib/lib.kbres.php';
        $kbres = new KbRes();
        $data = false;

        if ($this->res_id > 0) {
            $data = $kbres->getResource($this->res_id, true, true);
        } elseif (!empty($this->r_item_id) && !empty($this->r_type)) {
            $data = $kbres->getResourceFromItem($this->r_item_id, $this->r_type, $this->r_env, true, true);
        }

        if ($data == false) {
            $data = [
                'res_id' => 0,
                'r_name' => '',
                'original_name' => '',
                'r_desc' => '',
                'r_item_id' => $this->r_item_id,
                'r_type' => $this->r_type,
                'r_env' => $this->r_env,
                'r_env_parent_id' => $this->r_env_parent_id,
                'r_param' => $this->r_param,
                'r_alt_desc' => '',
                'r_lang' => (!empty($this->language) ? $this->language : getLanguage()),
                'force_visible' => 0,
                'is_mobile' => 0,
                'folders' => [],
                'tags' => [],
            ];
        }

        if (!empty($this->original_name)) {
            $data['original_name'] = $this->original_name;
        }
        $c_folders = array_keys($data['folders']);
        unset($data['folders']);
        $c_tags = $data['tags'];
        unset($data['tags']);

        $json = new Services_JSON();

        $this->render('kbcategorize',
            [
                'selected_node' => $this->_getSelectedNode(),
                'back_url' => $this->back_url,
                'form_url' => $this->form_url,
                'form_extra_hidden' => $this->form_extra_hidden,
                'data' => $data,
                'c_folders' => $c_folders,
                'c_tags_json' => $json->encode(array_values($c_tags)),
                'all_tags_json' => $json->encode(array_values($kbres->getAllTags())),
            ]
        );
    }

    /**
     * Include the required libraries in order to have all the things ready and working.
     */
    public function init()
    {
    }

    protected function _getSelectedNode()
    {
        if (!isset($this->session->get('kb_categorize_sel')['selected_node'])) {
            $this->session->set('kb_categorize_sel', ['selected_node' => 0]);
            $this->session->save();
        }

        return $this->session->get('kb_categorize_sel')['selected_node'];
    }

    protected function _setSelectedNode($node_id)
    {
        $this->session->set('kb_categorize_sel', ['selected_node' => (int) $node_id]);
        $this->session->save();
    }
}
