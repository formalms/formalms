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

define('_TREE_COLUMNS_TYPE_CHECK', 'checkbox');
define('_TREE_COLUMNS_TYPE_RADIO', 'radiobutton');

require_once _base_ . '/lib/folder_tree/class.client_tree.php';

/**
 * Ajax Tree base class.
 */
class CourseCategoryTree extends ClientTree
{
    public $ajax_url = '';

    public $preload_tree = false;

    public $root_name = false;

    public $sel_columns = '';

    public $initial_selection = [];

    public $use_form_input = true;

    public $draggable_nodes = false;

    public $use_context_menu = false;

    public function __construct($id, $url = false, $preload = false, $cols_type = '')
    {
        //$this->id = $id;
        parent::__construct($id);

        $this->ajax_url = ($url
            ? $url
            : $GLOBALS['where_lms_relative'] . '/ajax.adm_server.php?plf=lms&file=categorytree&sf=category');

        $this->preload_tree = $preload;
        $this->sel_columns = $cols_type;
    }

    public function init()
    {
        YuiLib::load([
            'json' => 'json-min.js',
            'container' => 'container_core-min.js', //menu
            'menu' => 'menu-min.js', //menu
            'button' => 'button-min.js', //dialog
            'treeview' => 'treeview-min.js', ],
        [
            'assets/skins/sam' => 'skin.css',
        ]);
        cout(Util::get_css('base-folder-tree.css'), 'page_head');
        cout(Util::get_js('appLms/lib/category/lib.categorytree.js'), 'page_head');
        Util::get_js(FormaLms\lib\Get::rel_path('base') . '/lib/lib.elem_selector.js', true, true);
    }

    /**
     * This function set an initial selection of values, which will be pre-selected in the treeview.
     *
     * @param $data = an array of selected elements
     *
     * @return void
     */
    public function setInitialSelection($data = [])
    {
        if (is_array($data)) {
            $this->initial_selection = $data;
        } elseif (is_string($data)) {
            $this->initial_selection = explode(',', $data);
        } elseif (is_int($data)) {
            $this->initial_selection = [$data];
        }
    }

    //format ids or whatever is being used as strings
    private function formatInitialSelection()
    {
        $output = [];
        foreach ($this->initial_selection as $val) {
            $output[] = '"' . $val . '"';
        }

        return implode(',', $output);
    }

    /**
     * This function create the initialization data needed to load the tree and the markup.
     *
     * @return array 'js' -> the js used to setup the tree, 'html' => thd html markup needed for the tree
     */
    public function get($domready = true, $tags = true, $global = false)
    {
        $lang = &DoceboLanguage::createInstance('organization_chart', 'framework');
        $lang = &DoceboLanguage::createInstance('treeview', 'framework');

        if (!$this->root_name) {
            $this->root_name = $lang->def('_CATEGORY');
        }

        $out = [];
        $out['js'] = ($tags ? '<script type="text/javascript">' . "\n" : '') .

        //global var, if wanted
        (is_string($global) ? 'var ' . $global . ';' : '') .

        //init the language
            'var _lang_tree = {' .
            '_SAVE: "' . $lang->def('_SAVE') . '",' .
            '_CONFIRM: "' . $lang->def('_CONFIRM') . '",' .
            '_UNDO: "' . $lang->def('_UNDO') . '",' .
            '_AREYOUSURE: "' . $lang->def('_AREYOUSURE') . '",' .
            '_NAME: "' . $lang->def('_NAME') . '",' .
            '_ADD: "' . $lang->def('_ADD') . '",' .
            '_MOD: "' . $lang->def('_MOD') . '",' .
            '_DEL: "' . $lang->def('_DEL') . '",' .

            '_YES: "' . $lang->def('_YES') . '",' .
            '_NO: "' . $lang->def('_NO') . '",' .
            '_INHERIT: "' . $lang->def('_ORG_CHART_INHERIT', 'organization_chart') . '",' .

            '_ROOT: "' . $this->root_name . '",' .
            '_NEW_FOLDER: "' . $lang->def('_NEW_FOLDER') . '",' .
            '_DEL: "' . $lang->def('_DEL') . '",' .

            '_AJAX_FAILURE: "' . $lang->def('_DEL') . '"' .
            ' };' .
            "\n\n" .
        // init the tree
        ($domready ? ' YAHOO.util.Event.onDOMReady(function(e) {' : '') .
            'var BaseTree = new CourseCategoryTree("' . $this->id . '", {' . "\n" .
            '     tree_id: "' . $this->id . '",' . "\n" .
            '     request_url: "' . $this->ajax_url . '",' . "\n" .
        //'     tree_data: ['.$tree_data.'],'."\n".
            '     selector_columns_type: "' . $this->sel_columns . '",' . "\n" .
            '     use_form_input: ' . ($this->use_form_input ? 'true' : 'false') . ',' . "\n" .
            '     draggable_nodes: ' . ($this->draggable_nodes ? 'true' : 'false') . ',' . "\n" .
            '     use_context_menu: ' . ($this->use_context_menu ? 'true' : 'false') . ',' . "\n" .
            '     langs: _lang_tree,' . "\n" .
            '     initial_selection: [' . $this->formatInitialSelection() . ']' . "\n" .
            '   }' . "\n" .
            '); ' . (is_string($global) ? $global . '=BaseTree; ' : '') . ($domready ? '} );' : '') .
        ($tags ? '</script>' : '');

        $out['html'] = '<div class="tree_container">' .
            /*'<div id="'.$this->id.'_root" class="ygtvroot">'.
                '<span>'.( $this->root_name ? $this->root_name : $lang->def("_ROOT") ).'</span>'.
            '</div>'.*/
            '<div class="folder_status_message"><div id="' . $this->id . '_status"></div></div>' .
            '<div class="folder_tree" id="' . $this->id . '"></div>' .
        ($this->use_form_input ? '<input type="hidden" id="' . $this->id . '_input" name="' . $this->id . '_input" />' : '') .
            '</div>';

        return $out;
    }

    public function getCategories($param = false)
    {
        $temp = ($param ? $param : FormaLms\lib\Get::req($this->id . '_input', DOTY_MIXED, false));
        $nodes = explode(',', $temp);

        $output = [];
        $branches = [];
        foreach ($nodes as $node) {
            if (stristr($node, 'd')) {
                $branches[] = '/ocd_' . str_replace('d', '', $node);
            } else {
                $branches[] = '/oc_' . $node;
            }
        }

        $acl = new DoceboACLManager();
        $groups = $acl->getGroupsIdByPaths($branches);
        $output = $acl->getAllUsersFromIdst($groups);

        return $output;
    }
}
