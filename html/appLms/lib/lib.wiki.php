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

/**
 * @category Wiki
 *
 * @author   Giovanni Derks <virtualdarkness[AT]gmail-com>
 *
 * @version  $Id: $
 */
// ----------------------------------------------------------------------------

require_once _adm_ . '/lib/lib.wiki.php';

class LmsWikiAdmin extends CoreWikiAdmin
{
    public $course_id = 0;

    public function __construct($source_platform = 'lms')
    {
        $this->lang = &FormaLanguage::createInstance('wiki', 'framework');
        $this->source_platform = $source_platform;
        $this->wikiManager = new LmsWikiManager();
    }

    public function setCourseId($course_id)
    {
        $this->course_id = (int) $course_id;
    }

    public function getCourseId()
    {
        return $this->course_id;
    }

    public function getCourseWikiList()
    {
        $course_id = $this->getCourseId();

        return $this->wikiManager->getCourseWikiList($course_id);
    }

    public function getCourseWikiTable($can_mod = false, $wiki_list = false)
    {
        $um = &UrlManager::getInstance();

        // $course_id =$this->getCourseId();

        $res = '';

        if (($wiki_list === false) || (!is_array($wiki_list))) {
            $wiki_list = $this->getCourseWikiList();
        }
        $where = 'wiki_id IN (' . implode(',', $wiki_list['list']) . ')';
        $source_platform = $this->getSourcePlatform();
        $data_info = $this->wikiManager->getWikiList(false, false, $where, $source_platform);
        $data_arr = $data_info['data_arr'];
        $db_tot = $data_info['data_tot'];

        $tot = count($data_arr);
        for ($i = 0; $i < $tot; ++$i) {
            $id = $data_arr[$i]['wiki_id'];

            $rowcnt = [];
            $rowcnt[] = $data_arr[$i]['title'];

            $url = $um->getUrl('op=show&wiki_id=' . $id);
            $res .= '<div class="list_block">';
            $res .= '<h2 class="heading"><a href="' . $url . '">' . $data_arr[$i]['title'] . '</a></h2>' . "\n";
            $res .= '<p class="content">' . $data_arr[$i]['description'] . '</p>' . "\n";
            if ($can_mod) {
                $res .= '<div class="actions">'
                    . '<ul class="link_list_inline">' . "\n";

                $url = $um->getUrl('op=editwiki&wiki_id=' . $id);
                $res .= '<li><a class="ico-wt-sprite subs_mod" href="' . $url . '">';
                $res .= '<span>' . $this->lang->def('_MOD') . '</span></a></li>';
                $url = $um->getUrl('op=setperm&wiki_id=' . $id);

                if ($data_arr[$i]['public'] == 1 && FormaLms\lib\Get::cur_plat() != 'lms') {
                    $res .= '<li><a class="ico-wt-sprite subs_users" href="' . $url . '">';
                    $res .= '<span>' . $this->lang->def('_ALT_SETPERM') . '</span></a></li>';
                }
                if ($wiki_list['data'][$id]['is_owner'] == 1) {
                    $url = $um->getUrl('op=delwiki&wiki_id=' . $id);
                    $res .= '<li><a class="ico-wt-sprite subs_del" href="' . $url . '" title="' . $this->lang->def('_DEL')
                        . ' : ' . strip_tags($data_arr[$i]['title']) . '">';
                    $res .= '<span>' . $this->lang->def('_DEL') . '</span></a></li>';
                }
                $res .= '</ul></div>';
            }
            $res .= "</div>\n"; // wiki_box
        }

        if ($can_mod) {
            $res .= '<div class="table-container-below">';
            $res .= '<ul class="link_list_inline">' . "\n";
            $url = $um->getUrl('op=addwiki');
            $res .= '<li><a class="ico-wt-sprite subs_add" href="' . $url . '">';
            $res .= '<span>' . $this->lang->def('_ADD_WIKI') . '</span></a></li>';
            $url = $um->getUrl('op=selectwiki');
            $res .= '<li><a class="ico-wt-sprite subs_import" href="' . $url . '">';
            $res .= '<span>' . $this->lang->def('_SELECT_WIKI') . '</span></a></li>';
            $res .= "</ul>\n";
            $res .= "</div>\n"; // table-container-below

            require_once _base_ . '/lib/lib.dialog.php';
            setupHrefDialogBox('a[href*=delwiki]');
        }

        return $res;
    }

    public function saveWiki()
    {
        $um = &UrlManager::getInstance();

        $source_platform = $this->getSourcePlatform();
        $course_id = $this->getCourseId();
        $wiki_id = $this->wikiManager->saveWiki($_POST, $source_platform, $course_id);

        $url = $um->getUrl();
        Util::jump_to($url);
    }

    public function selectLmsWiki()
    {
        include_once _lms_ . '/lib/lib.course.php';
        include_once _base_ . '/lib/lib.form.php';
        include_once _lib_ . '/lib.table.php';

        $um = &UrlManager::getInstance();
        $back_url = $um->getUrl();
        $course_id = $this->getCourseId();

        if (isset($_POST['undo'])) {
            Util::jump_to($back_url);
        } elseif (isset($_POST['save'])) {
            $this->wikiManager->saveLmsSelectedWiki($course_id, $_POST);

            Util::jump_to($back_url);
        } else {
            $res = '';
            $form = new Form();

            $table_caption = $this->lang->def('_TABLE_SELECT_WIKI_CAP');
            $table_summary = $this->lang->def('_TABLE_SELECT_WIKI_SUM');

            $tab = new Table(0, $table_caption, $table_summary);

            $head = [$this->lang->def('_TITLE'), ''];

            $head_type = ['', 'image'];

            $tab->setColsStyle($head_type);
            $tab->addHead($head);

            $course_list = Man_CourseUser::getUserCourseList(getLogUserId());
            $selectable_wiki = $this->wikiManager->getCourseWikiList($course_list);

            $wiki_list = $this->wikiManager->getCourseWikiList($course_id);

            $source_platform = $this->getSourcePlatform();
            $data_info = $this->wikiManager->getWikiList(false, false, ' wiki_id IN (' . implode(',', $selectable_wiki['list']) . ') ', $source_platform);
            $data_arr = $data_info['data_arr'];
            $db_tot = $data_info['data_tot'];

            $tot = count($data_arr);
            for ($i = 0; $i < $tot; ++$i) {
                $id = $data_arr[$i]['wiki_id'];

                $rowcnt = [];
                $rowcnt[] = $data_arr[$i]['title'];
                $check_cell = '';

                $checked = (in_array($id, $wiki_list['list']) ? true : false);
                $disabled = '';

                if ($checked) {
                    $field_id = 'db_sel_wiki_' . $id;
                    $field_name = 'db_sel_wiki[' . $id . ']';
                    $check_cell .= $form->getHidden($field_id, $field_name, $id);

                    $is_owner = ($wiki_list['data'][$id]['is_owner'] == 1 ? true : false);

                    if ($is_owner) {
                        $disabled = ' disabled="disabled"';

                        $field_id = 'owned_wiki_' . $id;
                        $field_name = 'owned_wiki[' . $id . ']';
                        $check_cell .= $form->getHidden($field_id, $field_name, $id);
                    }
                }

                $chk_id = 'sel_wiki_' . $id;
                $chk_name = 'sel_wiki[' . $id . ']';
                $check_cell .= $form->getCheckbox('', $chk_id, $chk_name, $id, $checked, $disabled);
                $rowcnt[] = $check_cell;

                $tab->addBody($rowcnt);
            }

            $url = $um->getUrl('op=selectwiki');
            $res .= $form->openForm('main_form', $url);
            $res .= $tab->getTable();
            $res .= $form->openButtonSpace();
            $res .= $form->getButton('save', 'save', $this->lang->def('_SAVE'));
            $res .= $form->getButton('undo', 'undo', $this->lang->def('_UNDO'));
            $res .= $form->closeButtonSpace();
            $res .= $form->closeForm();

            return $res;
        }
    }

    public function requireLmsWikiOwner($wiki_id)
    {
        $wiki_list = $this->getCourseWikiList();
        $is_owner = ($wiki_list['data'][$wiki_id]['is_owner'] == 1 ? true : false);

        if (!$is_owner) {
            exit("You can't access!");
        }
    }
}

class LmsWikiManager extends CoreWikiManager
{
    public function _getWikiCourseTable()
    {
        return $GLOBALS['prefix_lms'] . '_wiki_course';
    }

    public function saveWiki($data, $source_platform, $course_id)
    {
        $wiki_id = parent::saveWiki($data, $source_platform);

        $qtxt = 'INSERT INTO ' . $this->_getWikiCourseTable() . ' ';
        $qtxt .= '(course_id, wiki_id, is_owner) ';
        $qtxt .= "VALUES ('" . (int) $course_id . "', '" . $wiki_id . "', '1')";
        $q = $this->_executeQuery($qtxt);

        return $wiki_id;
    }

    public function deleteWiki($wiki_id)
    {
        $qtxt = 'DELETE FROM ' . $this->_getWikiCourseTable() . ' ';
        $qtxt .= "WHERE wiki_id='" . (int) $wiki_id . "'";
        $q = $this->_executeQuery($qtxt);

        parent::deleteWiki($wiki_id);
    }

    public function getLmsWikiList($course_id = false)
    {
        $res = ['list' => [], 'data' => []];

        $fields = 't1.course_id, t1.is_owner, t2.*';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getWikiCourseTable() . ' as t1, ';
        $qtxt .= $this->_getWikiTable() . ' as t2 ';
        $qtxt .= 'WHERE t1.wiki_id=t2.wiki_id ';
        if (($course_id !== false) && ($course_id > 0)) {
            if (is_array($course_id)) {
                $qtxt .= 'AND t1.course_id IN ( ' . implode(',', $course_id) . ') ';
            } else {
                $qtxt .= "AND t1.course_id='" . (int) $course_id . "' ";
            }
        }
        $qtxt .= 'ORDER BY t2.title';
        $q = $this->_executeQuery($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) {
            while ($row = sql_fetch_assoc($q)) {
                $wiki_id = $row['wiki_id'];
                $res['data'][$wiki_id] = $row;
                $res['list'][$wiki_id] = $wiki_id;
            }
        }

        return $res;
    }

    public function getCourseWikiList($course_id)
    {
        $res = $this->getLmsWikiList($course_id);

        return $res;
    }

    public function saveLmsSelectedWiki($course_id, $data)
    {
        if ($course_id < 1) {
            return false;
        }

        $data['sel_wiki'] = (isset($data['sel_wiki']) ? $data['sel_wiki'] : []);
        $data['db_sel_wiki'] = (isset($data['db_sel_wiki']) ? $data['db_sel_wiki'] : []);
        $data['owned_wiki'] = (isset($data['owned_wiki']) ? $data['owned_wiki'] : []);

        $to_add = array_diff($data['sel_wiki'], $data['db_sel_wiki']);
        $to_remove = array_diff($data['db_sel_wiki'], $data['sel_wiki']);

        $to_add = array_diff($to_add, $data['owned_wiki']);
        $to_remove = array_diff($to_remove, $data['owned_wiki']);

        $qtxt = 'INSERT INTO ' . $this->_getWikiCourseTable() . ' (course_id, wiki_id, is_owner) ';
        $add_to_query = [];
        foreach ($to_add as $wiki_id) {
            $add_to_query[] = "('" . $course_id . "', '" . $wiki_id . "', '0')";
        }
        if (count($add_to_query) > 0) {
            $qtxt .= 'VALUES ' . implode(',', $add_to_query);
            $q = $this->_executeQuery($qtxt);
        }

        if (count($to_remove) > 0) {
            $qtxt = 'DELETE FROM ' . $this->_getWikiCourseTable() . ' ';
            $qtxt .= "WHERE course_id='" . $course_id . "' AND ";
            $qtxt .= 'wiki_id IN (' . implode(',', $to_remove) . ')';
            $q = $this->_executeQuery($qtxt);
        }
    }

    public function isWikiUsedByOthers($course_id, $wiki_id)
    {
        $res = true;

        $qtxt = 'SELECT * FROM ' . $this->_getWikiCourseTable() . ' WHERE ';
        $qtxt .= "is_owner='0' AND course_id != '" . (int) $course_id . "' ";
        $qtxt .= "AND wiki_id='" . (int) $wiki_id . "'";

        $q = $this->_executeQuery($qtxt);

        if (($q) && (sql_num_rows($q) == 0)) {
            $res = false;
        }

        return $res;
    }
}

class LmsWikiPublic extends CoreWikiPublic
{
}
