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

class KbAlmsController extends AlmsController
{
    protected $model = null;
    protected $json = null;
    protected $permissions = null;
    public $show_actions = true;
    public $data;

    public function init()
    {
        parent::init();
        require_once _base_ . '/lib/lib.json.php';

        //Util::get_css();

        $this->model = new KbAlms();
        $this->json = new Services_JSON();

        $this->permissions = [
            'view' => checkPerm('view', true, 'kb'),
            'add' => checkPerm('mod', true, 'kb'),
            'mod' => checkPerm('mod', true, 'kb'),
            'del' => checkPerm('mod', true, 'kb'),
        ];
    }

    protected function _getErrorMessage($code)
    {
        $output = '';
        switch ($code) {
            case 'no permission': $output .= "You don't have permission for this."; break;
        }

        return $output;
    }

    public function show()
    {
        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');

        if (isset($_GET['error'])) {
            UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'kb'));
        }
        if (isset($_GET['success'])) {
            UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'kb'));
        }

        require_once _lms_ . '/lib/lib.kbres.php';
        $kbres = new KbRes();
        $res_type_arr = $kbres->getResourceTypeArr(true);

        $res_type_dd_arr = [
            0 => Lang::t('_ALL', 'kb'),
        ];
        $res_type_dd_arr += $res_type_arr;

        $categorized_filter_arr = [
            'all' => Lang::t('_CATEGORIZED_AND_UNCATEGORIZED', 'kb'),
            'categorized' => Lang::t('_CATEGORIZED_ONLY', 'kb'),
            'uncategorized' => Lang::t('_UNCATEGORIZED_ONLY', 'kb'),
            //'permissions' => $this->permissions
        ];

        $res = FormaLms\lib\Get::req('res', DOTY_STRING, '');
        $result_message = '';
        switch ($res) {
            case 'ok': $result_message = UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'standard')); break;
            case 'err': $result_message = UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'standard')); break;
        }

        $this->render('show', [
                'selected_node' => $this->_getSelectedNode(),
                'addfolder_markup' => $this->_getAddFolderDialogContent($this->_getSelectedNode()),
                'filter_text' => $filter_text,
                'res_type_dd_arr' => $res_type_dd_arr,
                'categorized_filter_arr' => $categorized_filter_arr,
                'result_message' => $result_message,
                'permissions' => $this->permissions,
            ]
        );
    }

    public function add()
    {
        require_once _lms_ . '/lib/lib.kbres.php';

        $type = FormaLms\lib\Get::req('type', DOTY_STRING, '');
        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');

        $kbres = new KbRes();
        $all_resources = $kbres->getRawResources();
        $this->render('add', [
            'all_resources' => $all_resources,
            'filter_text' => $filter_text,
            'type' => $type,
            ]
        );
    }

    public function categorize()
    {
        require_once _lms_ . '/lib/lib.kbres.php';

        $res_id = 0;

        if (isset($_POST['org_categorize_save'])) {
            require_once _lms_ . '/lib/lib.kbres.php';

            $name = FormaLms\lib\Get::req('r_name', DOTY_STRING, '');
            $original_name = ''; // won't update this field
            $desc = FormaLms\lib\Get::req('r_desc', DOTY_STRING, '');
            $r_item_id = FormaLms\lib\Get::req('r_item_id', DOTY_INT, 0);
            $type = FormaLms\lib\Get::req('r_type', DOTY_STRING, '');
            $env = FormaLms\lib\Get::req('r_env', DOTY_STRING, '');
            $env_parent_id = FormaLms\lib\Get::req('r_env_parent_id', DOTY_INT, 0);
            $param = ''; //FormaLms\lib\Get::req('', DOTY_STRING, "");
            $alt_desc = '';
            $lang_id = FormaLms\lib\Get::req('r_lang', DOTY_INT, '');
            $lang_arr = \FormaLms\lib\Forma::langManager()->getAllLangCode();
            $lang = $lang_arr[$lang_id];
            $force_visible = FormaLms\lib\Get::req('force_visible', DOTY_INT, 0);
            $is_mobile = FormaLms\lib\Get::req('is_mobile', DOTY_INT, 0);
            $folders = FormaLms\lib\Get::req('h_selected_folders', DOTY_STRING, '');
            $json_tags = Util::strip_slashes(FormaLms\lib\Get::req('tag_list', DOTY_STRING, '[]'));

            $kbres = new KbRes();
            $res_id = $kbres->saveResource($res_id, $name, $original_name, $desc, $r_item_id,
                $type, $env, $env_parent_id, $param, $alt_desc, $lang, $force_visible,
                $is_mobile, $folders, $json_tags
            );

            Util::jump_to('index.php?r=alms/kb/show');
        } elseif (isset($_POST['org_categorize_cancel'])) {
            Util::jump_to('index.php?r=alms/kb/show');
        } else {
            $r_type = FormaLms\lib\Get::req('type', DOTY_STRING, '');
            $r_env = FormaLms\lib\Get::req('env', DOTY_STRING, '');
            $r_item_id = FormaLms\lib\Get::req('id', DOTY_INT, 0);
            $original_name = FormaLms\lib\Get::req('title', DOTY_STRING, '');

            $this->render('categorize', [
                'r_type' => $r_type,
                'r_env' => $r_env,
                'r_item_id' => $r_item_id,
                'original_name' => $original_name,
                ]
            );
        }
    }

    public function edit()
    {
        require_once _lms_ . '/lib/lib.kbres.php';
        $kbres = new KbRes();
        $res_id = FormaLms\lib\Get::req('id', DOTY_INT, 0);

        if (isset($_POST['subcategorize_switch'])) {
            $cat_sub_items = FormaLms\lib\Get::pReq('subcategorize_switch', DOTY_INT);

            $kbres->saveResourceSubCategorizePref($res_id, $cat_sub_items);

            Util::jump_to('index.php?r=alms/kb/show&amp;success=1');
            //Util::jump_to('index.php?r=alms/kb/edit&amp;id='.$res_id);
            exit();
        }
        if (isset($_POST['org_categorize_save'])) {
            $name = FormaLms\lib\Get::req('r_name', DOTY_STRING, '');
            $original_name = ''; // won't update this field
            $desc = FormaLms\lib\Get::req('r_desc', DOTY_STRING, '');
            $r_item_id = FormaLms\lib\Get::req('r_item_id', DOTY_INT, 0);
            $type = FormaLms\lib\Get::req('r_type', DOTY_STRING, '');
            $env = FormaLms\lib\Get::req('r_env', DOTY_STRING, '');
            $env_parent_id = FormaLms\lib\Get::req('r_env_parent_id', DOTY_INT, 0);
            $param = ''; //FormaLms\lib\Get::req('', DOTY_STRING, "");
            $alt_desc = '';
            $lang_id = FormaLms\lib\Get::req('r_lang', DOTY_INT, '');
            $lang_arr = \FormaLms\lib\Forma::langManager()->getAllLangCode();
            $lang = $lang_arr[$lang_id];
            $force_visible = FormaLms\lib\Get::req('force_visible', DOTY_INT, 0);
            $is_mobile = FormaLms\lib\Get::req('is_mobile', DOTY_INT, 0);
            $folders = FormaLms\lib\Get::req('h_selected_folders', DOTY_STRING, '');
            $json_tags = Util::strip_slashes(FormaLms\lib\Get::req('tag_list', DOTY_STRING, '[]'));

            $res_id = $kbres->saveResource($res_id, $name, $original_name, $desc, $r_item_id,
                $type, $env, $env_parent_id, $param, $alt_desc, $lang, $force_visible,
                $is_mobile, $folders, $json_tags
            );

            Util::jump_to('index.php?r=alms/kb/show&res=' . ($res_id ? 'ok' : 'err'));
        } elseif (isset($_POST['org_categorize_cancel'])) {
            Util::jump_to('index.php?r=alms/kb/show');
        } else {
            $this->render('edit', [
                    'res_id' => $res_id,
                ]
            );
        }
    }

    public function getlist()
    {
        $folder_id = FormaLms\lib\Get::req('folder_id', DOTY_INT, 0);
        $start_index = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_MIXED, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'title');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'asc');
        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');
        $type_filter = FormaLms\lib\Get::req('type_filter', DOTY_STRING, 'all');
        $show_what = FormaLms\lib\Get::req('categorized_filter', DOTY_STRING, 'all');

        $where = (!empty($filter_text) ? "kr.r_name LIKE '%" . $filter_text . "%'" : '');
        $where .= (!empty($type_filter) ? (!empty($where) ? ' AND ' : '') . "kr.r_type='" . $type_filter . "'" : '');

        $total_comm = $this->model->count($where);
        $res_arr = $this->model->getResources($folder_id, $start_index, $results, $sort, $dir, $where, false, false, true, $show_what);
        $array_comm = $res_arr['data'];

        $tags = $this->model->getAllTagsForResources($res_arr['id_arr']);

        $list = [];
        $parent_id = [];
        foreach ($array_comm as $key => $value) {
            $id = $value['res_id'];
            $r_env = $value['r_env'];

            if (!empty($value['r_env_parent_id'])) {
                $parent_id[$r_env][$key] = $value['r_env_parent_id'];
            } else {
                $array_comm[$key]['r_env_parent'] = '';
            }

            /*$array_comm[$key]['id'] = $value['id_comm'];
            if($filter_text) {
                $array_comm[$key]['title'] = highlightText($value['title'], $filter_text);
                $array_comm[$key]['description'] = highlightText($value['description'], $filter_text);
            }
            $array_comm[$key]['publish_date'] = Format::date($value['publish_date'], 'date');
            if($value['access_entity']) {
                $array_comm[$key]['user'] = '<a class="ico-sprite subs_user" title="'.Lang::t('_ASSIGN_USERS', 'communication').'"
                    href="index.php?r=alms/communication/mod_user&id_comm='.$value['id_comm'].'&load=1"><span>'
                    .Lang::t('_ASSIGN_USERS', 'communication').'</span></a>';
            } else {
                $array_comm[$key]['user'] = '<a class="ico-sprite fd_notice" title="'.Lang::t('_NO_USER_SELECTED', 'communication').'"
                    href="index.php?r=alms/communication/mod_user&id_comm='.$value['id_comm'].'&load=1"><span>'
                    .Lang::t('_ASSIGN_USERS', 'communication').'</span></a>';
            }
            $array_comm[$key]['edit'] = '<a class="ico-sprite subs_mod" href="index.php?r=alms/communication/edit&id_comm='.$value['id_comm'].'"><span>'
                .Lang::t('_MOD', 'communication').'</span></a>';
            $array_comm[$key]['del'] = 'ajax.adm_server.php?r=alms/communication/del&id_comm='.$value['id_comm'];*/
            $array_comm[$key]['tags'] = (isset($tags[$id]) ? implode(', ', $tags[$id]) : '');

            $img_type = $array_comm[$key]['r_type'];
            switch ($img_type) {
                case 'scorm':
                    $img_type = 'scormorg';
                    break;
                case 'file':
                    $img_type = 'item';
                    break;
                default:
                    break;
            }
            $image = '<img src="' . getPathImage() . 'lobject/' . $img_type . '.png' . '" '
                        . 'width="16px" alt="' . $img_type . '" '
                        . 'title="' . $img_type . '" />';
            $array_comm[$key]['r_type'] = $image;
        }

        $this->model->getParentInfo($parent_id, $array_comm, ['course_lo', 'communication', 'games']);

        $result = [
            'totalRecords' => $total_comm,
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($array_comm),
            'records' => $array_comm,
        ];

        $this->data = $this->json->encode($result);
        echo $this->data;
    }

    public function getUncategorized()
    {
        require_once _lms_ . '/lib/lib.kbres.php';
        $kbres = new KbRes();

        $start_index = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_MIXED, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'title');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'asc');

        $type = FormaLms\lib\Get::req('type', DOTY_STRING, '');
        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');

        $where = (!empty($filter_text) ? "items.title LIKE '%" . $filter_text . "%'" : '');
        $limit = $start_index . ', ' . $results;

        $data_arr = $kbres->getUnCategorizedResourcesByType($type, $where, $limit);

        $tot = $data_arr['tot'];

        $result = [
            'totalRecords' => $tot,
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($data_arr),
            'records' => $data_arr['data'],
        ];

        $data = $this->json->encode($result);
        echo $data;
    }

    public function fvSwitch()
    {
        if (!$this->permissions['mod']) {
            return;
        }

        $this->model->update(
            ['force_visible' => (FormaLms\lib\Get::req('is_active', DOTY_INT, 0) ? 0 : 1)],
                ['res_id==' => FormaLms\lib\Get::req('id', DOTY_INT, 0)]
        );
    }

    protected function _getNodeActions($node)
    {
        if (!$this->show_actions) {
            return [];
        }

        if (is_numeric($node)) {
            $nodedata = $this->model->getFolderById($node);
            $node = [
                'id' => $nodedata->node_id,
                'label' => $this->model->getFolderTranslation($nodedata->node_id, Lang::get()),
                'is_leaf' => (($nodedata->iRight - $nodedata->iLeft) == 1),
                'count_content' => (int) (($nodedata->iRight - $nodedata->iLeft - 1) / 2),
            ];
        }
        if (!is_array($node)) {
            return false;
        } //unrecognized type for node data

        $actions = [];
        $id_action = $node['id'];
        $is_root = ($id_action == 0);

        /*
        if (!$this->model->isFolderEnabled($id_action)) return false;


        //assign users to folder action
        if (!$is_root) {
            $actions[] = array(
                'id' => 'moduser_'.$id_action,
                'command' => 'moduser',
                'icon' => 'standard/moduser.png',
                'href' => 'index.php?r=adm/usermanagement/assignuser&id='.$id_action,
                'alt' => Lang::t('_ASSIGN_USERS', 'organization_chart')
            );
        } else {
            $actions[] = array(
                'id' => 'moduser_'.$id_action,
                'command' => false,
                'icon' => 'blank.png'
            );
        }

        //assign custom fields action
        $actions[] = array(
            'id' => 'assignfields_'.$id_action,
            'command' => 'assignfields',
            'icon' => 'standard/database.png',
            'alt' => Lang::t('_ASSIGN_USERS', 'organization_chart')
        );
        */

        //rename action
        if ($this->permissions['mod']) {
            $actions[] = [
                'id' => 'mod_' . $id_action,
                'command' => 'modify',
                'icon' => 'standard/edit.png',
                'alt' => Lang::t('_MOD', 'standard'),
            ];
        }

        //delete action
        if ($this->permissions['del']) {
            if ($node['is_leaf'] && !$is_root) {
                $actions[] = [
                    'id' => 'del_' . $id_action,
                    'command' => 'delete',
                    'icon' => 'standard/delete.png',
                    'alt' => Lang::t('_DEL', 'standard'),
                ];
            } else {
                $actions[] = [
                    'id' => 'del_' . $id_action,
                    'command' => false,
                    'icon' => 'blank.png',
                ];
            }
        }

        return $actions;
    }

    public function setShowActions($show_actions)
    {
        $this->show_actions = (bool) $show_actions;
    }

    protected function _getSelectedNode()
    {
        if (!$this->session->has('kb_selector_selected_node')) {
            $this->session->set('kb_selector_selected_node', 0);
            $this->session->save();
        }

        return $this->session->get('kb_selector_selected_node', 0);
    }

    protected function _setSelectedNode($node_id)
    {
        $this->session->set('kb_selector_selected_node', $node_id);
        $this->session->save();
    }

    protected function _assignActions(&$nodes)
    {
        if (!is_array($nodes)) {
            return;
        }
        for ($i = 0; $i < count($nodes); ++$i) {
            $nodes[$i]['node']['options'] = $this->_getNodeActions($nodes[$i]['node']);
            if (isset($nodes[$i]['children']) && count($nodes[$i]['children']) > 0) {
                $this->_assignActions($nodes[$i]['children']);
            }
        }

        return [];
    }

    public function gettreedata()
    {
        $command = FormaLms\lib\Get::req('command', DOTY_ALPHANUM, '');

        $show_actions = FormaLms\lib\Get::req('show_actions', DOTY_INT, 1);
        //$this->show_actions =$show_actions;

        switch ($command) {
            case 'set_selected_node':
                $from_widget = FormaLms\lib\Get::gReq('from_widget', DOTY_INT, 0);
                if (!$from_widget) {
                    $node_id = FormaLms\lib\Get::req('node_id', DOTY_INT, -1);
                    $this->_setSelectedNode($node_id);
                }
             break;

            case 'expand':
                $node_id = FormaLms\lib\Get::req('node_id', DOTY_INT, -1);
                $initial = (FormaLms\lib\Get::req('initial', DOTY_INT, 0) > 0 ? true : false);

                if ($initial) {
                    //get selected node from session and set the expanded tree
                    $node_id = $this->_getSelectedNode();
                    $nodes = $this->model->getKbInitialNodes($node_id, true);
                    //create actions for every node
                    if ($show_actions) {
                        $this->_assignActions($nodes);
                    }
                    //set output
                    if (is_array($nodes)) {
                        $output = [
                            'success' => true,
                            'nodes' => $nodes,
                            'initial' => $initial,
                        ];
                    } else {
                        $output = ['success' => false];
                    }
                } else {
                    //extract node data
                    $nodes = $this->model->getKbNodes($node_id, false, false, true);
                    //create actions for every node
                    if ($show_actions) {
                        for ($i = 0; $i < count($nodes); ++$i) {
                            $nodes[$i]['options'] = $this->_getNodeActions($nodes[$i]);
                        }
                    }
                    //set output
                    $output = [
                        'success' => true,
                        'nodes' => $nodes,
                        'initial' => $initial,
                    ];
                }
                echo $this->json->encode($output);
             break;

            case 'getmodform':
                if (!$this->permissions['mod']) {
                    $output = ['success' => false, 'message' => $this->_getErrorMessage('no permission')];
                    echo $this->json->encode($output);

                    return;
                }

                $output = [];
                $id = FormaLms\lib\Get::req('node_id', DOTY_INT, -1);
                if ($id < 0) {
                    $output = [
                        'success' => false,
                        'message' => Lang::t('_INVALID_INPUT'),
                    ];
                } else {
                    if ($id == 0) {
                        $root_name = FormaLms\lib\Get::sett('title_organigram_chart', Lang::t('_ORG_CHART', 'organization_chart'));
                        $body = Form::openForm('modfolder_form', 'ajax.adm_server.php?r=alms/kb/modrootfolder')
                            . '<p id="addfolder_error_message"></p>'
                            . Form::getTextfield(Lang::t('_ROOT_RENAME', 'organization_chart'), 'modfolder_root', 'modfolder_root', 50, $root_name)
                            . Form::closeForm();
                    } else {
                        $languages = \FormaLms\lib\Forma::langManager()->getAllLanguages(true); //getAllLangCode();
                        $std_lang = Lang::get();

                        $form_content = Form::getHidden('modfolder_id', 'node_id', $id);

                        $translations = $this->model->getFolderTranslations($id, true);
                        foreach ($languages as $language) {
                            $lang_code = $language['code'];
                            $lang_name = $language['description'];
                            $translation = (isset($translations[$lang_code]) ? $translations[$lang_code] : '');
                            $form_content .= Form::getTextfield($lang_name, 'modfolder_' . $lang_code, 'modfolder[' . $lang_code . ']', 50, $translation);
                        }
                        $body = Form::openForm('modfolder_form', 'ajax.adm_server.php?r=alms/kb/modfolder')
                            . '<p id="addfolder_error_message"></p>'
                            . $form_content
                            . Form::closeForm();
                    }

                    $output = [
                        'success' => true,
                        'body' => $body,
                    ];
                }

                echo $this->json->encode($output);
             break;

            case 'delete': $this->delfolder(); break;
            case 'options':
                $id = FormaLms\lib\Get::req('node_id', DOTY_INT, -1);
                $output = [];
                if ($id <= 0) {
                    $output['success'] = false;
                } else {
                    $output['success'] = true;
                    $output['options'] = $this->_getNodeActions($id);
                }
                echo $this->json->encode($output);
        } //end switch command
    }

    protected function _getAddFolderDialogContent($id_parent)
    {
        $languages = \FormaLms\lib\Forma::langManager()->getAllLanguages(true); //getAllLangCode();
        $std_lang = Lang::get();

        $form_content = Form::getHidden('addfolder_id_parent', 'id_parent', $id_parent);
        //$form_content .= Form::getBreakRow();

        foreach ($languages as $language) {
            $lang_code = $language['code'];
            $lang_name = $language['description'];
            $form_content .= Form::getTextfield($lang_name, 'newfolder_' . $lang_code, 'newfolder_' . $lang_code, 50);
        }
        $body = Form::openForm('addfolder_form', 'ajax.adm_server.php?r=alms/kb/createfolder')
            . '<p id="addfolder_error_message"></p>'
            . $form_content
            . Form::closeForm();

        $footer = ''
            . Form::openButtonSpace()
            . Form::getButton('addfolder_save', 'addfolder_save', Lang::t('_SAVE', 'standard'), false, '', false)
            . Form::getButton('addfolder_undo', 'addfolder_undo', Lang::t('_UNDO', 'standard'), false, '', false)
            . Form::closeButtonSpace();

        $output['header'] = Lang::t('_ORGCHART_ADDNODE', 'organization_chart');
        $output['body'] = $body;
        $output['buttons'] = [
            ['text' => Lang::t('_CONFIRM', 'standard'), 'handler' => 'addfolder_save', 'isDefault' => true],
            ['text' => Lang::t('_UNDO', 'standard'), 'handler' => 'addfolder_undo'],
        ];
        $output['script'] = '
				YAHOO.util.Event.addListener("addfolder_form", "submit", function(e) { YAHOO.util.stopEvent(e); });
				var addfolder_undo = function(e) { Dialog_add_folder_dialog.hide(); };
				var addfolder_save = function(e) {
					var id_parent = YAHOO.util.Dom.get("addfolder_id_parent").value;
					var list = YAHOO.util.Selector.query("input[id^=newfolder_]"), postdata = ["id_parent="+id_parent];
					for (var i=0; i<list.length; i++) {
						postdata.push("langs["+list[i].name.split("_")[1]+"]="+list[i].value);
					}
					YAHOO.util.Connect.asyncRequest("POST", YAHOO.util.Dom.get("addfolder_form").action, {
						success: function(o) {
							var res = YAHOO.lang.JSON.parse(o.responseText);
							if (res.success) {
								for (var i=0; i<list.length; i++) list[i].value = "";
								if (res.node) {
									parent = TreeView_kbtree._getNodeById(res.id_parent);
									TreeView_kbtree.appendNode(parent, res.node, false);
								}
								Dialog_add_folder_dialog.hide();
							} else {
								YAHOO.util.Dom.get("addfolder_error_message").innerHTML = (res.message ? res.message : "error");
							}
						},
						failure: function() {
						}
					}, postdata.join("&"));
				};
			';

        return $output;
    }

    public function createfolder()
    {
        if (!$this->permissions['add']) {
            $output = ['success' => false, 'message' => $this->_getErrorMessage('no permission')];
            echo $this->json->encode($output);

            return;
        }

        $output = [];
        $langs = FormaLms\lib\Get::req('langs', DOTY_MIXED, false);
        if ($langs == false) {
            $output['success'] = false;
            $output['message'] = Lang::t('_INVALID_INPUT');
        } else {
            $id_parent = FormaLms\lib\Get::req('id_parent', DOTY_INT, -1);
            if ($id_parent < 0) {
                $id_parent = 0;
            }
            $id = $this->model->addFolder($id_parent, $langs);
            if ($id > 0) {
                $output['success'] = true;
                $nodedata = [
                    'id' => $id,
                    'label' => $this->model->getFolderTranslation($id, Lang::get()),
                    'is_leaf' => true,
                    'count_content' => 0,
                ];
                $nodedata['options'] = $this->_getNodeActions($nodedata);
                $output['node'] = $nodedata;
                $output['id_parent'] = $id_parent;
            } else {
                $output['success'] = false;
                $output['message'] = Lang::t('_CONNECTION_ERROR');
            }
        }

        echo $this->json->encode($output);
    }

    /**
     * Update a folder name.
     */
    public function modfolder()
    {
        if (!$this->permissions['mod']) {
            $output = ['success' => false, 'message' => $this->_getErrorMessage('no permission')];
            echo $this->json->encode($output);

            return;
        }

        $output = [];
        $id = FormaLms\lib\Get::req('node_id', DOTY_INT, -1);
        $langs = FormaLms\lib\Get::req('modfolder', DOTY_MIXED, false);
        $res = $this->model->renameFolder($id, $langs);
        if ($res) {
            $output['success'] = true;
            $output['new_name'] = $langs [Lang::get()];
        } else {
            $output['success'] = false;
            $output['message'] = Lang::t('_CONNECTION_ERROR');
        }
        echo $this->json->encode($output);
    }

    /**
     * Update the tree root folder name.
     */
    public function modrootfolder()
    {
        if (!$this->permissions['mod']) {
            $output = ['success' => false, 'message' => $this->_getErrorMessage('no permission')];
            echo $this->json->encode($output);

            return;
        }

        $output = [];
        $root_name = FormaLms\lib\Get::req('modfolder_root', DOTY_STRING, '');
        $res = $this->model->renameRootFolder($root_name);
        if ($res) {
            $output['success'] = true;
            $output['new_name'] = $root_name;
        } else {
            $output['success'] = false;
            $output['message'] = Lang::t('_CONNECTION_ERROR');
        }
        echo $this->json->encode($output);
    }

    /**
     * Delete a tree folder.
     */
    public function delfolder()
    {
        if (!$this->permissions['del']) {
            $output = ['success' => false, 'message' => $this->_getErrorMessage('no permission')];
            echo $this->json->encode($output);

            return;
        }

        $output = ['success' => false];
        $id = FormaLms\lib\Get::req('node_id', DOTY_INT, -1);
        if ($id > 0) {
            $output['success'] = $this->model->deleteFolder($id, true);
        }
        echo $this->json->encode($output);
    }

    public function test()
    {
        $this->render('test');
    }

    public function addfolder_dialog()
    {
        if (!$this->permissions['add']) {
            $output = ['success' => false, 'message' => $this->_getErrorMessage('no permission')];
            echo $this->json->encode($output);

            return;
        }

        $id_parent = FormaLms\lib\Get::req('id', DOTY_INT, 0);
        if ($id_parent < 0) {
            $id_parent = 0;
        }

        $this->render('add_folder', [
            'id_parent' => $id_parent,
            'title' => Lang::t('_ORGCHART_ADDNODE', 'organization_chart'),
            'json' => $this->json,
        ]);
    }
}
