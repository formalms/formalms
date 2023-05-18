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
 * @version  $Id: lib.coursepath.php 849 2006-12-15 17:35:49Z fabio $
 *
 * @category Course managment
 *
 * @author	 Fabio Pirovano <fabio [at] docebo [dot] com>
 */
class Selector_CoursePath
{
    public $show_filter = true;

    public $filter = [];

    public $current_page = '';

    public $current_selection = [];

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->show_filter = true;
    }

    public function enableFilter()
    {
        $this->show_filter = true;
    }

    public function disableFilter()
    {
        $this->show_filter = false;
    }

    /**
     * return the current status in a pratic format.
     *
     * @return string a string with the data used for reloading the current status
     */
    public function getStatus()
    {
        $status = [
            'page' => $this->current_page,
            'filter' => serialize($this->filter),
            'show_filter' => $this->show_filter,
            'current_selection' => serialize($this->current_selection), ];

        return serialize($status);
    }

    /**
     * reset the current status to te given one.
     *
     * @param string $status_serialized a valid status saved using getStatus
     */
    public function loadStatus(&$status_serialized)
    {
        if ($status_serialized == '') {
            return;
        }
        $status = unserialize($status_serialized);

        $this->current_page = $status['page'];
        $this->filter = unserialize($status['filter']);
        $this->show_filter = $status['show_filter'];
        $this->current_selection = unserialize($status['current_selection']);
    }

    public function parseForAction($array_action)
    {
    }

    public function parseForState($array_state)
    {
        // older selection
        if (isset($array_state['coursepath_selected'])) {
            $this->current_selection = Util::unserialize(urldecode($array_state['coursepath_selected']));
        }
        // add last selection
        if (isset($array_state['new_coursepath_selected'])) {
            foreach ($_POST['new_coursepath_selected'] as $id_cpath => $v) {
                $this->current_selection[$id_cpath] = $id_cpath;
            }
        }
    }

    public function stateSelection()
    {
        return Form::getHidden('coursepath_selected', 'coursepath_selected', urlencode(Util::serialize($this->current_selection)));
    }

    public function getSelection()
    {
        return $this->current_selection;
    }

    public function resetSelection($new_selection)
    {
        $this->current_selection = $new_selection;
    }

    public function loadCoursepathSelector($noprint = false)
    {
        require_once _base_ . '/lib/lib.table.php';
        require_once _base_ . '/lib/lib.form.php';

        $lang = &FormaLanguage::createInstance('coursepath', 'lms');
        $output = '';

        // Filter
        $this->filter['coursepath_name'] = (isset($_POST['coursepath_filter_name']) ? $_POST['coursepath_filter_name'] : '');
        if ($this->show_filter === true) {
            /*
                $form = new Form();
                $output .= $form->getOpenFieldset($lang->def('_COURSEPATH_FILTER'))
                    .Form::getTextfield($lang->def('_NAME'), 'coursepath_filter_name', 'coursepath_filter_name', '255',
                        ( isset($_POST['coursepath_filter_name']) ? $_POST['coursepath_filter_name'] : '' ))
                    .$form->openButtonSpace()
                    .$form->getButton('coursepath_filter', 'coursepath_filter', $lang->def('_SEARCH'))
                    .$form->closeButtonSpace()
                    .$form->getCloseFieldset();*/

            $output .= '<div class="quick_search_form">'
                . '<div>'
                . Form::getInputTextfield('search_t', 'coursepath_filter_name', 'coursepath_filter_name', FormaLms\lib\Get::req('coursepath_filter_name', DOTY_MIXED, ''), '', 255, '')
                . Form::getButton('coursepath_filter', 'coursepath_filter', Lang::t('_SEARCH', 'standard'), 'search_b')
                . '</div>'
                . '</div>';
        }
        // End Filter

        $tb = new Table(FormaLms\lib\Get::sett('visuItem'), $lang->def('_COURSE_PATH_CAPTION'), $lang->def('_COURSE_PATH_SUMMARY'));

        $tb->initNavBar('ini_cpath', 'button');
        $ini = $tb->getSelectedElement();

        $select = '
		SELECT id_path, path_name, path_descr ';
        $query_coursepath = '
		FROM %lms_coursepath
		WHERE 1 ';
        if (Forma::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            $all_courses = false;

            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $admin_courses = $adminManager->getAdminCourse(Forma::user()->getIdST());
            if (isset($admin_courses['course'][0])) {
                $all_courses = true;
            }
            if (isset($admin_courses['course'][-1])) {
                $query = 'SELECT id_path'
                            . ' FROM %lms_coursepath_user'
                            . " WHERE idUser = '" . $id_user . "'";

                $result = sql_query($query);
                $admin_courses['coursepath'] = [];

                while (list($id_path) = sql_fetch_row($result)) {
                    $admin_courses['coursepath'][$id_path] = $id_path;
                }

                if (!empty($admin_courses['coursepath']) && FormaLms\lib\Get::sett('on_catalogue_empty', 'off') == 'on') {
                    $all_courses = true;
                }
            }

            if (!$all_courses) {
                if (empty($admin_courses['coursepath'])) {
                    $query_coursepath .= ' AND 0 ';
                } else {
                    $query_coursepath .= ' AND id_path IN (' . implode(',', $admin_courses['coursepath']) . ') ';
                }
            }
        }
        if ($this->filter['coursepath_name'] != '') {
            $query_coursepath .= " AND path_name LIKE '%" . $this->filter['coursepath_name'] . "%'";
        }
        list($tot_coursepath) = sql_fetch_row(sql_query('SELECT COUNT(*) ' . $query_coursepath));

        $query_coursepath .= '
		ORDER BY path_name
		LIMIT ' . $ini . ',' . (int) FormaLms\lib\Get::sett('visuItem');

        $re_coursepath = sql_query($select . $query_coursepath);

        $type_h = ['image', '', '', ''];
        $cont_h = [
            '<span class="access-only">' . $lang->def('_SELECT') . '</span>',
            $lang->def('_NAME'),
            $lang->def('_DESCRIPTION'),
        ];
        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);
        while (list($id_path, $name, $descr) = sql_fetch_row($re_coursepath)) {
            $tb_content = [
                Form::getInputCheckbox('new_coursepath_selected_' . $id_path, 'new_coursepath_selected[' . $id_path . ']', $id_path,
                    isset($this->current_selection[$id_path]), ''),
                '<label for="new_coursepath_selected_' . $id_path . '">' . $name . '</label>',
                '<label for="new_coursepath_selected_' . $id_path . '">' . $descr . '</label>',
            ];
            $tb->addBody($tb_content);
            if (isset($this->current_selection[$id_path])) {
                unset($this->current_selection[$id_path]);
            }
        }

        $output .= $tb->getTable()
            . $tb->getNavBar($ini, $tot_coursepath)
            . $this->stateSelection();

        if ($noprint) {
            return $output;
        } else {
            cout($output, 'content');
        }
    }
}

define('COURSEPATH_ID', 0);
define('COURSEPATH_CODE', 1);
define('COURSEPATH_NAME', 2);
define('COURSEPATH_DESCR', 3);
define('COURSEPATH_METHOD', 4);

define('CP_ENROLLED', 5);
define('CP_WAITING', 6);

define('METHOD_MANUAL', 0);
define('METHOD_WAIT', 1);
define('METHOD_AUTO', 2);

define('CP_COURSE_ID_PATH', 0);
define('CP_COURSE_ID_ITEM', 1);
define('CP_COURSE_IN_SLOT', 2);
define('CP_COURSE_PREREQ', 3);
define('CP_COURSE_IS_SLOT', 4);
define('CP_COURSE_SEQ', 5);

define('CP_SLOT_ID_SLOT', 0);
define('CP_SLOT_ID_PATH', 1);
define('CP_SLOT_MIN', 2);
define('CP_SLOT_MAX', 3);
define('CP_SLOT_SEQ', 4);

class CoursePath_Manager
{
    public $acl;

    public $aclManager;

    public $_path_field = [
        COURSEPATH_ID => 'id_path',
        COURSEPATH_CODE => 'path_code',
        COURSEPATH_NAME => 'path_name',
        COURSEPATH_DESCR => 'path_descr',
        COURSEPATH_METHOD => 'subscribe_method',
    ];

    public $_cp_course_field = [
        CP_COURSE_ID_PATH => 'id_path',
        CP_COURSE_ID_ITEM => 'id_item',
        CP_COURSE_IN_SLOT => 'in_slot',
        CP_COURSE_PREREQ => 'prerequisites',
        CP_COURSE_SEQ => 'sequence',
    ];

    public $_cp_slot_field = [
        CP_SLOT_ID_SLOT => 'id_slot',
        CP_SLOT_ID_PATH => 'id_path',
        CP_SLOT_MIN => 'min_selection',
        CP_SLOT_MAX => 'max_selection',
        CP_SLOT_SEQ => 'sequence',
    ];

    public $filter_in_path = false;

    public $filter_or_in_path = false;

    public function __construct()
    {
        ksort($this->_path_field);
        reset($this->_path_field);

        ksort($this->_cp_course_field);
        reset($this->_cp_course_field);

        ksort($this->_cp_slot_field);
        reset($this->_cp_slot_field);

        $this->acl = new FormaACL();
        $this->aclManager = $this->acl->getACLManager();
    }

    public function _query($query)
    {
        $rs = sql_query($query);

        return $rs;
    }

    public function _getPathTable()
    {
        return $GLOBALS['prefix_lms'] . '_coursepath';
    }

    public function _getPathCourseTable()
    {
        return $GLOBALS['prefix_lms'] . '_coursepath_courses';
    }

    public function _getPathSlotTable()
    {
        return $GLOBALS['prefix_lms'] . '_coursepath_slot';
    }

    public function _getPathUserTable()
    {
        return $GLOBALS['prefix_lms'] . '_coursepath_user';
    }

    public function filterInPath($cat_path)
    {
        if (is_array($cat_path)) {
            $this->filter_in_path = $cat_path;
        }
    }

    public function filterOrInPath($cat_path)
    {
        if (is_array($cat_path)) {
            $this->filter_or_in_path = $cat_path;
        }
    }

    /**
     * return information about the course path.
     *
     * @param int $ini           start result from
     * @param int $result_number maximum number of result
     *
     * @result array an array with all the info about a coursepath
     */
    public function getCoursepathList($ini, $result_number)
    {
        $select = 'SELECT p.' . implode(', p.', $this->_path_field) . ', COUNT(u.idUser), SUM(u.waiting) ';
        $from = 'FROM ' . $this->_getPathTable() . ' AS p
			LEFT JOIN ' . $this->_getPathUserTable() . ' AS u ON ( p.id_path = u.id_path ) ';
        $where = ' 1 ';
        $group_by = 'GROUP BY p.' . $this->_path_field[COURSEPATH_ID] . ' ';
        $order_by = 'ORDER BY p.' . $this->_path_field[COURSEPATH_NAME] . '';
        if ($result_number != 0) {
            $limit = '';
        } else {
            $limit = "LIMIT $ini, $result_number";
        }

        if ($this->filter_in_path !== false) {
            $where .= ' AND ( p.' . $this->_path_field[COURSEPATH_ID] . ' IN (' . implode(',', $this->filter_in_path) . ') )';
        }
        if ($this->filter_or_in_path !== false) {
            $where = '( ' . $where . ' ) OR p.' . $this->_path_field[COURSEPATH_ID] . ' IN (' . implode(',', $this->filter_or_in_path) . ') ';
        }

        $coursepath = [];
        $repath = $this->_query($select . $from . ' WHERE ' . $where . $group_by . $order_by . $limit);
        while ($row = sql_fetch_row($repath)) {
            $coursepath[$row[COURSEPATH_ID]] = $row;
        }

        return $coursepath;
    }

    /**
     * @param int $arr_id the id_path of a coursepath
     *
     * @return array an array with id => array( [id_path] [path_code] [path_name] [path_descr] [subscribe_method] )
     */
    public function getCoursepathAllInfo($arr_id)
    {
        $coursepath = [];
        if (count($arr_id) != 0) {
            $select = '
            SELECT ' . implode(',', $this->_path_field) . '
            FROM ' . $this->_getPathTable() . '
            WHERE ' . $this->_path_field[COURSEPATH_ID] . ' IN ( ' . implode(',', $arr_id) . ' )';
            $re_select = $this->_query($select);
            if ($re_select) {
                while ($row = sql_fetch_row($re_select)) {
                    $coursepath[$row[COURSEPATH_ID]] = $row;
                }
            }
        }
        return $coursepath;
    }

    /**
     * @param	array	an array with the value of the id to search
     *
     * @return array an array with id => name of the course path
     */
    public function &getNames(&$coursepath)
    {
        $re_coursepath = [];
        if (empty($coursepath)) {
            return $re_coursepath;
        }

        $select = '
		SELECT id_path, path_name
		FROM ' . $this->_getPathTable() . '
		WHERE id_path IN (' . implode(',', $coursepath) . ')';
        $re_select = sql_query($select);
        while (list($id, $name) = sql_fetch_row($re_select)) {
            $re_coursepath[$id] = $name;
        }

        return $re_coursepath;
    }

    /**
     * @param int $id the id_path of a coursepath
     *
     * @return array an array with [id_path] [path_code] [path_name] [path_descr] [subscribe_method]
     */
    public function getCoursepathInfo($id)
    {
        $select = '
		SELECT ' . implode(',', $this->_path_field) . '
		FROM ' . $this->_getPathTable() . '
		WHERE ' . $this->_path_field[COURSEPATH_ID] . " = '" . $id . "'";
        $re_select = sql_query($select);
        if (!$re_select) {
            return false;
        }

        return sql_fetch_assoc($re_select);
    }

    /**
     * return all the courses directly assigned to a list coursepath.
     *
     * @param int|array $coursepaths the id of the coursepath
     *
     * @return array the id of the courses (id => id, ...)
     **/
    public function getAllCourses($coursepaths)
    {
        $courses = [];
        if (is_numeric($coursepaths)) {
            $coursepaths = [(int) $coursepaths];
        }
        if (empty($coursepaths)) {
            return [];
        }
        $query = '
		SELECT DISTINCT id_item
		FROM ' . $this->_getPathCourseTable() . '
		WHERE id_path  IN (' . implode(',', $coursepaths) . ') ';
        $re_courses = $this->_query($query);
        while (list($id) = sql_fetch_row($re_courses)) {
            $courses[$id] = $id;
        }

        return $courses;
    }

    /**
     * return all the courses directly assigned to a list coursepath.
     *
     * @param int|array $coursepaths the id of the coursepath
     *
     * @return array the id of the courses (id => id, ...)
     **/
    public function getAllCoursesInfo($coursepaths)
    {
        $courses = [];
        if (is_numeric($coursepaths)) {
            $coursepaths = [(int) $coursepaths];
        }
        if (empty($coursepaths)) {
            return [];
        }
        $query = '
		SELECT *
		FROM ' . $this->_getPathCourseTable() . '
		WHERE id_path  IN (' . implode(',', $coursepaths) . ') ';
        $re_courses = $this->_query($query);
        while ($row = sql_fetch_assoc($re_courses)) {
            $id = $row['id_item'];
            $courses[$id] = $row;
        }

        return $courses;
    }

    /**
     * return all the courses directly assigned to a list coursepath, subdivided by path, also return the sum of all courses.
     *
     * @param array $coursepaths the id of the coursepath
     *
     * @return array
     **/
    public function getPathStructure($coursepaths)
    {
        $path_struct['all_items'] = [];
        if (empty($coursepaths)) {
            return $path_struct;
        }

        $query = '
		SELECT id_path, in_slot, id_item
		FROM ' . $this->_getPathCourseTable() . '
		WHERE id_path IN (' . implode(',', $coursepaths) . ')
		ORDER BY id_path, in_slot, sequence';
        $re_courses = $this->_query($query);
        while (list($id_path, $in_slot, $id_item) = sql_fetch_row($re_courses)) {
            if (!isset($courses[$id_path])) {
                $courses[$id_path] = [];
            }
            if (!isset($courses[$id_path][$in_slot])) {
                $courses[$id_path][$in_slot] = [];
            }
            $path_struct[$id_path][$in_slot][$id_item] = $id_item;

            $path_struct['all_items'][$id_item] = $id_item;
            $path_struct['all_paths'][$id_path] = $id_path;
        }

        return $path_struct;
    }

    public function getPathCourses($id_path)
    {
        $courses = [];

        $query = '
		SELECT id_item, prerequisites, sequence
		FROM ' . $this->_getPathCourseTable() . '
		WHERE id_path  = ' . (int) $id_path . '
		ORDER BY sequence ';
        $re_courses = $this->_query($query);
        while (list($id) = sql_fetch_row($re_courses)) {
            $courses[$id] = $id;
        }

        return $courses;
    }

    public function moveUp($id_path, $id_slot, $id_course)
    {
        $query = '
		SELECT sequence
		FROM ' . $this->_getPathCourseTable() . '
		WHERE id_path  = ' . (int) $id_path . "
			AND in_slot = '" . $id_slot . "'
			AND id_item = " . (int) $id_course . ' ';
        list($seq) = sql_fetch_row(sql_query($query));

        $query = '
		UPDATE ' . $this->_getPathCourseTable() . '
		SET sequence = ' . $seq . '
		WHERE id_path  = ' . (int) $id_path . "
			AND in_slot = '" . $id_slot . "'
			AND sequence = " . ($seq - 1) . ' ';
        sql_query($query);

        $query = '
		UPDATE ' . $this->_getPathCourseTable() . '
		SET sequence = ' . ($seq - 1) . '
		WHERE id_path  = ' . (int) $id_path . "
			AND in_slot = '" . $id_slot . "'
			AND id_item = " . (int) $id_course . ' ';
        sql_query($query);
    }

    public function moveDown($id_path, $id_slot, $id_course)
    {
        $query = '
		SELECT sequence
		FROM ' . $this->_getPathCourseTable() . '
		WHERE id_path  = ' . (int) $id_path . "
			AND in_slot = '" . $id_slot . "'
			AND id_item = " . (int) $id_course . ' ';
        list($seq) = sql_fetch_row(sql_query($query));

        $query = '
		UPDATE ' . $this->_getPathCourseTable() . '
		SET sequence = ' . $seq . '
		WHERE id_path  = ' . (int) $id_path . "
			AND in_slot = '" . $id_slot . "'
			AND sequence = " . ($seq + 1) . ' ';
        sql_query($query);

        $query = '
		UPDATE ' . $this->_getPathCourseTable() . '
		SET sequence = ' . ($seq + 1) . '
		WHERE id_path  = ' . (int) $id_path . "
			AND in_slot = '" . $id_slot . "'
			AND id_item = " . (int) $id_course . ' ';
        sql_query($query);
    }

    /**
     * fix the sequence of the course in the database.
     *
     * @param int $id_path the id of the path to fix
     */
    public function fixSequence($id_path, $id_slot)
    {
        $i = 0;
        $query = '
		SELECT id_item
		FROM ' . $this->_getPathCourseTable() . '
		WHERE id_path  = ' . (int) $id_path . "
			AND in_slot = '" . $id_slot . "'
		ORDER BY sequence ";
        $re_courses = $this->_query($query);
        while (list($id) = sql_fetch_row($re_courses)) {
            $query = '
			UPDATE ' . $this->_getPathCourseTable() . '
			SET sequence = ' . ($i++) . '
			WHERE id_path  = ' . (int) $id_path . '
				AND in_slot = ' . (int) $id_slot . '
				AND id_item = ' . (int) $id . ' ';
            sql_query($query);
        }

        return true;
    }

    public function getPathSlot($arr_path)
    {
        if (!is_array($arr_path)) {
            $is_array = false;
            $arr_path = [$arr_path];
        } else {
            $is_array = true;
        }

        $query_pathelem = '
		SELECT id_path, id_slot, min_selection, max_selection
		FROM ' . $this->_getPathSlotTable() . '
		WHERE id_path IN ( ' . implode(',', $arr_path) . ' )
		ORDER BY sequence';
        $repath_elem = sql_query($query_pathelem);

        $info = [];
        if ($is_array) {
            foreach ($arr_path as $k => $idpath) {
                $info[$idpath][0] = ['min_selection' => 0, 'max_selection' => 0];
            }
        } else {
            $info[0] = ['min_selection' => 0, 'max_selection' => 0];
        }
        while (list($id_path, $id_slot, $min_selection, $max_selection) = sql_fetch_row($repath_elem)) {
            if ($is_array) {
                $info[$id_path][$id_slot] = [
                    'min_selection' => $min_selection,
                    'max_selection' => $max_selection,
                ];
            } else {
                $info[$id_slot] = [
                    'min_selection' => $min_selection,
                    'max_selection' => $max_selection,
                ];
            }
        }

        return $info;
    }

    public function getSlotInfo($id_slot)
    {
        $query_pathelem = '
		SELECT id_slot, id_path, min_selection, max_selection, sequence
		FROM ' . $this->_getPathSlotTable() . "
		WHERE id_slot = '" . $id_slot . "'
		ORDER BY sequence";
        $repath_elem = sql_query($query_pathelem);

        $info = [];
        $info = sql_fetch_array($repath_elem);

        return $info;
    }

    public function createSlot($id_path, $min_selection, $max_selection)
    {
        list($sequence) = sql_fetch_row($this->_query('
		SELECT MAX(sequence)+1
		FROM ' . $this->_getPathSlotTable() . "
		WHERE id_path = '" . $id_path . "'"));

        return $this->_query('
		INSERT INTO ' . $this->_getPathSlotTable() . '
		( id_slot, id_path, min_selection, max_selection, sequence  ) VALUES
		( NULL,
		  ' . (int) $id_path . ',
		  ' . (int) $min_selection . ',
		  ' . (int) $max_selection . ',
		  ' . (int) $sequence . ') ');
    }

    public function saveSlot($id_slot, $min_selection, $max_selection)
    {
        return $this->_query('
		UPDATE ' . $this->_getPathSlotTable() . '
		SET min_selection = ' . (int) $min_selection . ',
			max_selection  = ' . (int) $max_selection . '
		WHERE id_slot = ' . (int) $id_slot . '');
    }

    public function getPathElem($id_path)
    {
        $query_pathelem = '
		SELECT id_item, in_slot, prerequisites
		FROM ' . $GLOBALS['prefix_lms'] . "_coursepath_courses
		WHERE id_path = '" . $id_path . "'
		ORDER BY in_slot, sequence";
        $repath_elem = $this->_query($query_pathelem);
        $info = [];
        while (list($id_item, $in_slot, $prerequisites) = sql_fetch_row($repath_elem)) {
            $info['course_list'][] = $id_item;
            if ($prerequisites != '') {
                $info['course_list'][] = $prerequisites;
            }
            $info[$in_slot][$id_item] = $prerequisites;
        }

        return $info;
    }

    public function getSlotElem($id_path, $id_slot)
    {
        $query_pathelem = '
		SELECT id_item
		FROM ' . $this->_getPathCourseTable() . "
		WHERE id_path = '" . $id_path . "'
			AND in_slot = '" . $id_slot . "'
		ORDER BY sequence";
        $repath_elem = $this->_query($query_pathelem);
        $info = [];
        while (list($id_item) = sql_fetch_row($repath_elem)) {
            $info[$id_item] = $id_item;
        }

        return $info;
    }

    public function addToSlot($id_path, $id_slot, $id_c)
    {
        return $this->_query('
		INSERT INTO ' . $this->_getPathCourseTable() . '
		(id_path, id_item, in_slot, prerequisites ) VALUES
		( ' . (int) $id_path . ',
		  ' . (int) $id_c . ',
		  ' . (int) $id_slot . ",
		  '') ");
    }

    public function deleteSlot($id_slot, $id_path)
    {
        if (!$this->_query('
		DELETE FROM ' . $this->_getPathCourseTable() . "
		WHERE id_path = '" . $id_path . "'
			AND in_slot = '" . $id_slot . "'")) {
            return false;
        }

        return $this->_query('
		DELETE FROM ' . $this->_getPathSlotTable() . "
		WHERE id_path = '" . $id_path . "'
			AND id_slot = '" . $id_slot . "'");
    }

    public function delFromSlot($id_path, $id_slot, $id_c)
    {
        return $this->_query('
		DELETE FROM ' . $this->_getPathCourseTable() . "
		WHERE id_item = '" . $id_c . "'
			AND id_path = '" . $id_path . "'
			AND in_slot = '" . $id_slot . "'");
    }

    /**
     * return all the users directly assigned to a  coursepath.
     *
     * @param int $id_path the id of the coursepath
     *
     * @return array the id of the users (id => id, ...)
     **/
    public function getSubscribed($id_path)
    {
        $users = [];
        $query = '
		SELECT idUser
		FROM ' . $this->_getPathUserTable() . "
		WHERE id_path = '" . $id_path . "' ";
        $re_users = $this->_query($query);
        while (list($id) = sql_fetch_row($re_users)) {
            $users[$id] = $id;
        }

        return $users;
    }

    public function subscribeUserToCoursePath($id_path, $users)
    {
        require_once _lms_ . '/lib/lib.course.php';
        $re = true;

        if (!empty($users)) {
            $courses = $this->getPathCourses($id_path);
            $completed = [];
            $query = 'SELECT idUser, COUNT(*) '
                . ' FROM %lms_courseuser '
                . ' WHERE idCourse IN (' . implode(',', array_values($courses)) . ') '
                . ' AND idUser IN (' . implode(',', $users) . ") AND status = '" . _CUS_END . "' "
                . ' GROUP BY idUser';
            $res = sql_query($query);
            while (list($id_user, $num_completed) = sql_fetch_row($res)) {
                $completed[$id_user] = $num_completed;
            }

            $insert_values = [];
            foreach ($users as $id_user) {
                $course_completed = isset($completed[$id_user]) ? (int) $completed[$id_user] : 0;
                $insert_values[] = '( ' . (int) $id_path . ', ' . (int) $id_user . ", '" . date('Y-m-d h:i:s') . "', '" . Forma::user()->getIdst() . "', '" . $course_completed . "' )";
            }
            $query = 'INSERT INTO %lms_coursepath_user (id_path, idUser, date_assign, subscribed_by, course_completed ) VALUES ' . implode(', ', $insert_values);
            if (!sql_query($query)) {
                $re = false;
            } else {
                $t = count($courses);
                foreach ($completed as $id_users => $num_completed) {
                    if ($num_completed == $t) {
                        $data = Events::trigger('lms.coursepath_user.completed', ['id_user' => $id_user, 'id_paths' => $id_path]);
                    }
                }
            }
        }

        return $re;
    }

    public function getUserSubscriptionsInfo($id_user, $exclude_waiting = false)
    {
        $paths = [];
        $query = '
		SELECT id_path, waiting
		FROM ' . $this->_getPathUserTable() . "
		WHERE idUser = '" . $id_user . "' ";
        if ($exclude_waiting) {
            $query .= ' AND waiting = 0';
        }

        $re_users = $this->_query($query);
        while (list($id_path, $wait) = sql_fetch_row($re_users)) {
            $paths[$id_path] = ['id_path' => $id_path, 'waiting' => $wait];
        }

        return $paths;
    }

    /**
     *  Check if a user is enrolled in a coursepath
     * input id user, id pathCourse.
     */
    public function isEnrolled($id_user, $id_path)
    {
        $q = 'SELECT COUNT(*)'
                    . ' FROM ' . $this->_getPathUserTable()
                    . ' WHERE id_path =' . intval($id_path)
                    . ' AND idUser =' . intval($id_user);
        $row = sql_fetch_row(sql_query($q));

        return boolval($row[0]);
    }

    /**
     *  Check if a user completed one or more course paths
     * input id user
     * input single id_path or array of ids path.
     */
    public function isCoursePathCompleted($id_user, $id_path)
    {
        require_once _lms_ . '/lib/lib.course.php';
        if (is_array($id_path)) {
            $courseIdsFromPath = [];
            foreach ($id_path as $id) {
                $courses = $this->getPathCourses(intval($id));
                $courseIdsFromPath = array_merge($courseIdsFromPath, $courses);
            }
        } else {
            $courseIdsFromPath = $this->getPathCourses(intval($id_path));
        }

        if (!empty($courseIdsFromPath)) {
            $man_courseuser = new Man_CourseUser(DbConn::getInstance());
            $result = $man_courseuser->hasCompletedCourses($id_user, $courseIdsFromPath);

            return $result;
        }

        return false;
    }

    public function checkPrerequisites($prerequisites, &$courses_info)
    {
        require_once _lms_ . '/lib/lib.course.php';

        if ($prerequisites == '') {
            return true;
        }
        $arr_prere = explode(',', trim($prerequisites));
        if (($arr_prere == false) || (count($arr_prere) < 1)) {
            return true;
        }
        foreach ($arr_prere as $id_c) {
            if (isset($courses_info['course'][$id_c]['user_status'])) {
                if ($courses_info['course'][$id_c]['user_status'] != _CUS_END) {
                    return false;
                }
            } else {
                $at_least_one = false;
                foreach ($courses_info['edition'][$id_c] as $id_e => $info_e) {
                    if ($info_e['user_status'] == _CUS_END) {
                        $at_least_one = true;
                    }
                }
                if ($at_least_one == false) {
                    return false;
                }
            }
        }

        return true;
    }

    public function assignComplete($id_course, $id_user)
    {
        // path_courses containing course
        $q = ' SELECT id_path FROM %lms_coursepath_courses WHERE id_item = ' . (int) $id_course;
        $rs = sql_query($q);
        while ($r = sql_fetch_row($rs)) {
            $path_courses[] = $r[0];
        }

        $path_courses_str = implode(',', $path_courses);
        // update for the user
        $query = 'UPDATE %lms_coursepath_user SET course_completed = course_completed + 1 '
            . ' WHERE idUser = ' . (int) $id_user . ' AND id_path IN ( '
            . $path_courses_str . ' )';

        if (sql_query($query) && sql_affected_rows() > 0) {
            // check completion paths
            $q = 'SELECT id_path from %lms_coursepath_user where idUser=' . intval($id_user) . ' AND id_path IN (' . $path_courses_str . ')';
            $rs = sql_query($q);
            while ($r = sql_fetch_row($rs)) {
                if ($this->isCoursePathCompleted($id_user, $r[0])) {
                    $completed_paths[] = $r[0];
                }
            }
            if (count($completed_paths) > 0) {
                $data = Events::trigger('lms.coursepath_user.completed', [
                    'id_user' => $id_user,
                    'id_paths' => $completed_paths,
                    ]);
            }

            return true;
        } else {
            return false;
        }
    }

    //--- subscription management ------------------------------------------------

    public function updateUserDateBeginValidityInCourse($users_list, $id_path, $new_date_begin)
    {
        if ($id_path <= 0 || strlen($new_date_begin) < 10) {
            return false;
        }
        $output = false;
        $success = 0;
        $courses = $this->getAllCourses($id_path);
        if (!empty($courses)) {
            foreach ($users_list as $id_user) {
                $query = "UPDATE %lms_courseuser SET date_begin_validity = '" . $new_date_begin . "' "
                    . ' WHERE idCourse IN (' . implode(',', $courses) . ') AND idUser=' . $id_user . '';
                $res = sql_query($query);
                if ($res) {
                    ++$success;
                }
            }
        }

        return $output;
    }

    public function updateUserDateExpireValidityInCourse($users_list, $id_path, $new_date_expire)
    {
        if ($id_path <= 0 || strlen($new_date_expire) < 10) {
            return false;
        }
        $output = false;
        $success = 0;
        $courses = $this->getAllCourses($id_path);
        if (!empty($courses)) {
            foreach ($users_list as $id_user) {
                $query = "UPDATE %lms_courseuser SET date_expire_validity = '" . $new_date_expire . "' "
                    . ' WHERE idCourse IN (' . implode(',', $courses) . ') AND idUser=' . $id_user . '';
                $res = sql_query($query);
                if ($res) {
                    ++$success;
                }
            }
        }

        return $output;
    }

    //--- end subscription management --------------------------------------------

    public function deleteCourseFromCoursePaths($id_course)
    {
        $db = DbConn::getInstance();

        //retrieve all course's coursepaths
        $arr_coursepath = [];
        $arr_sequence = [];
        $query = 'SELECT id_path, sequence FROM %lms_coursepath_courses WHERE id_item = ' . (int) $id_course;
        $cres = $db->query($query);
        while (list($id_path, $sequence) = $db->fetch_row($cres)) {
            $arr_coursepath[] = (int) $id_path;
            $arr_sequence[$id_path] = $sequence;
        }

        if (!empty($arr_coursepath)) {
            $db->start_transaction();

            //delete coursepaths course references
            $query = 'DELETE FROM %lms_coursepath_courses WHERE id_item = ' . (int) $id_course;
            $res = $db->query($query);

            if (!$res) {
                $db->rollback();

                return false;
            }

            //clear the course from prerequisites of coursepaths courses
            $query = 'SELECT * FROM %lms_coursepath_courses WHERE id_path IN (' . implode(',', $arr_coursepath) . ')';
            $cres = $db->query($query);
            while ($obj = $db->fetch_obj($cres)) {
                //adjust prerequisites
                if (trim($obj->prerequisites) != '') {
                    $arr_prerequisites = explode(',', $obj->prerequisites);
                    if (in_array($id_course . '', $arr_prerequisites)) {
                        $arr_new_prereq = [];
                        foreach ($arr_prerequisites as $_prereq) {
                            if ($_prereq != $id_course && $_prereq != '') {
                                $arr_new_prereq[] = $_prereq;
                            }
                        }
                        $query = "UPDATE %lms_coursepath_courses
							SET prerequisites = '" . implode(',', $arr_new_prereq) . "'
							WHERE id_path = " . $obj->id_path . ' AND id_item = ' . $obj->id_item;
                        $res = $db->query($query);
                        if (!$res) {
                            $db->rollback();

                            return false;
                        }
                    }
                }
                //adjust sequence numbers
                if (isset($arr_sequence[$obj->id_path])) {
                    $query = 'UPDATE %lms_coursepath_courses SET sequence = sequence - 1
						WHERE id_path = ' . $obj->id_path . ' AND sequence > ' . $arr_sequence[$obj->id_path];
                    $res = $db->query($query);
                    if (!$res) {
                        $db->rollback();

                        return false;
                    }
                }
            }

            $db->commit();
        }

        return true;
    }
}
