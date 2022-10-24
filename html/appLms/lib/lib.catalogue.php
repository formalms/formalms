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
 * @version  $Id: lib.catalogue.php 635 2006-09-15 07:28:40Z fabio $
 *
 * @author	 Fabio Pirovano <fabio [at] docebo-com>
 */
class Selector_Catalogue
{
    public $show_filter = true;

    public $filter = [];

    public $current_page = '';

    public $current_selection = [];

    /**
     * Class constructor.
     */
    public function Selector_Catalogue()
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
        if (isset($array_state['catalogue_selected'])) {
            $this->current_selection = Util::unserialize(urldecode($array_state['catalogue_selected']));
        }
        // add last selection
        if (isset($array_state['new_catalogue_selected'])) {
            foreach ($_POST['new_catalogue_selected'] as $id_c) {
                $this->current_selection[$id_c] = $id_c;
            }
        }
    }

    public function stateSelection()
    {
        return Form::getHidden('catalogue_selected', 'catalogue_selected', urlencode(Util::serialize($this->current_selection)));
    }

    public function getSelection()
    {
        return $this->current_selection;
    }

    public function resetSelection($new_selection)
    {
        $this->current_selection = $new_selection;
    }

    public function loadCatalogueSelector($noprint = false)
    {
        require_once _base_ . '/lib/lib.table.php';
        require_once _base_ . '/lib/lib.form.php';

        // Filter
        $this->filter['catalogue_name'] = (isset($_POST['cat_filter_name']) ? $_POST['cat_filter_name'] : '');
        if ($this->show_filter === true) {
            $form = new Form();
            /*$GLOBALS['page']->add(
                $form->getOpenFieldset($lang->def('_SEARCH'))
                .Form::getTextfield($lang->def('_NAME'), 'cat_filter_name', 'cat_filter_name', '255',
                    ( isset($_POST['cat_filter_name']) ? $_POST['cat_filter_name'] : '' ))
                .$form->openButtonSpace()
                .$form->getButton('catalogue_filter', 'catalogue_filter', $lang->def('_SEARCH'))
                .$form->closeButtonSpace()
                .$form->getCloseFieldset()
            , 'content');
            */
            cout('<div class="quick_search_form">'
                . '<div>'
                . Form::getInputTextfield('search_t', 'cat_filter_name', 'cat_filter_name', FormaLms\lib\Get::req('cat_filter_name', DOTY_MIXED, ''), '', 255, '')
                . Form::getButton('catalogue_filter', 'catalogue_filter', Lang::t('_SEARCH', 'standard'), 'search_b')
                . '</div>'
                . '</div>', 'content');
        }
        // End Filter

        $tb = new Table(FormaLms\lib\Get::sett('visuItem'), Lang::t('_CATALOGUE', 'catalogue', 'lms'), Lang::t('_CATALOGUE_SUMMARY', 'catalogue', 'lms'));

        $tb->initNavBar('ini_cat', 'button');
        $ini = $tb->getSelectedElement();

        $select = '
		SELECT c.idCatalogue, c.name, c.description';
        $query_catalogue = '
		FROM %lms_catalogue AS c
		WHERE 1';
        // Retriving data
        if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            $all_courses = false;

            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
            if (isset($admin_courses['course'][0])) {
                $all_courses = true;
            }
            if (isset($admin_courses['course'][-1])) {
                require_once _lms_ . '/lib/lib.catalogue.php';
                $cat_man = new Catalogue_Manager();

                $admin_courses['catalogue'] = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());

                if (count($admin_courses['catalogue']) == 0 && FormaLms\lib\Get::sett('on_catalogue_empty', 'off') == 'on') {
                    $all_courses = true;
                }
            }

            if (!$all_courses) {
                if (empty($admin_courses['catalogue'])) {
                    $query_catalogue .= ' AND 0 ';
                } else {
                    $query_catalogue .= ' AND c.idCatalogue IN (0,' . implode(',', $admin_courses['catalogue']) . ') ';
                }
            }
        }
        if ($this->filter['catalogue_name'] != '') {
            $query_catalogue .= " AND c.name LIKE '%" . $this->filter['catalogue_name'] . "%'";
        }
        list($tot_catalogue) = sql_fetch_row(sql_query('SELECT COUNT(*) ' . $query_catalogue));
        $query_catalogue .= ' ORDER BY c.name
							LIMIT ' . $ini . ',' . (int) FormaLms\lib\Get::sett('visuItem');

        $re_catalogue = sql_query($select . $query_catalogue);

        $type_h = ['image', '', '', ''];
        $cont_h = [
            '<span class="access-only">' . Lang::t('_CATALOGUE_SELECTION', 'catalogue', 'lms') . '</span>',
            Lang::t('_NAME', 'catalogue', 'lms'),
            Lang::t('_DESCRIPTION', 'catalogue', 'lms'),
        ];
        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);
        while (list($id_catalogue, $name, $descr) = sql_fetch_row($re_catalogue)) {
            $tb_content = [
                Form::getInputCheckbox('new_catalogue_selected_' . $id_catalogue, 'new_catalogue_selected[' . $id_catalogue . ']', $id_catalogue,
                    isset($this->current_selection[$id_catalogue]), ''),
                '<label for="new_catalogue_selected_' . $id_catalogue . '">' . $name . '</label>',
                '<label for="new_catalogue_selected_' . $id_catalogue . '">' . $descr . '</label>',
            ];
            $tb->addBody($tb_content);
            if (isset($this->current_selection[$id_catalogue])) {
                unset($this->current_selection[$id_catalogue]);
            }
        }

        $output = $tb->getTable()
                    . $tb->getNavBar($ini, $tot_catalogue)
                    . $this->stateSelection();

        if ($noprint) {
            return $output;
        } else {
            cout($output, 'content');
        }
    }
}

class Catalogue_Manager
{
    /**
     * @var the acl instance
     */
    public $acl;

    /**
     * @var the aclManager instance
     */
    public $aclManager;

    /**
     * class constructor.
     */
    public function __construct()
    {
        $this->acl = new DoceboACL();
        $this->aclManager = $this->acl->getACLManager();
    }

    /**
     * exucute querys and do some debug function.
     *
     * @param string $query the text of the query
     *
     * @return resource_id the result of the query
     */
    public function _executeQuery($query)
    {
        $rs = sql_query($query);

        return $rs;
    }

    /**
     * @return string the name of the catalogue main table
     */
    public function _getCataTable()
    {
        return $GLOBALS['prefix_lms'] . '_catalogue';
    }

    /**
     * @return string the name of the catalogue contained element
     */
    public function _getCataEntryTable()
    {
        return $GLOBALS['prefix_lms'] . '_catalogue_entry';
    }

    /**
     * @return string the name of the catalogue association with groups
     */
    public function _getCataMemberTable()
    {
        return $GLOBALS['prefix_lms'] . '_catalogue_member';
    }

    /**
     * @param int $id_user the idst of a user
     *
     * @return array the array ids of the catalogues assigned to the assed user
     */
    public function getUserAllCatalogueId($id_user)
    {
        $catalogues = [];
        $user_groups = $this->acl->getSTGroupsST($id_user);

        if (empty($user_groups)) {
            return $catalogues;
        }
        $query = '
		SELECT DISTINCT cm.idCatalogue
		FROM ' . $this->_getCataMemberTable() . ' AS cm
		WHERE cm.idst_member IN (' . implode(',', $user_groups) . ') ';
        $re_catalogue = $this->_executeQuery($query);
        while (list($id_cata) = sql_fetch_row($re_catalogue)) {
            $catalogues[$id_cata] = $id_cata;
        }

        return $catalogues;
    }

    /**
     * @param int $id_user the idst of a user
     *
     * @return array some info about the catalogues associated to the user  array( [id] => array([idCatalogue], [name], [description]), ...)
     */
    public function &getUserAllCatalogueInfo($id_user)
    {
        $catalogues = [];
        $user_groups = $this->acl->getSTGroupsST($id_user);
        $query = '
		SELECT DISTINCT cm.idCatalogue, m.name, m.description
		FROM ' . $this->_getCataTable() . ' AS m
				JOIN ' . $this->_getCataMemberTable() . ' AS cm
		WHERE m.idCatalogue = cm.idCatalogue AND cm.idst_member IN (' . implode(',', $user_groups) . ')
		ORDER BY m.name';
        $re_catalogue = $this->_executeQuery($query);
        while ($cata = sql_fetch_assoc($re_catalogue)) {
            $catalogues[$cata['idCatalogue']] = $cata;
        }

        return $catalogues;
    }

    /**
     * @param int $id_user the idst of a user
     *
     * @return array the id of all the course associated to the group, not include the course in the associated coursepath
     */
    public function getAllCourseOfUser($id_user)
    {
        $courses = [];
        if ($id_user == getLogUserId()) {
            $user_groups = Docebo::user()->getArrSt();
        } else {
            $user_groups = $this->acl->getSTGroupsST($id_user);
        }
        $query = '
		SELECT DISTINCT ce.idEntry
		FROM ' . $this->_getCataEntryTable() . ' AS ce
			JOIN ' . $this->_getCataMemberTable() . " AS cm
		WHERE ce.type_of_entry = 'course' AND
			ce.idCatalogue = cm.idCatalogue AND
			cm.idst_member IN (" . implode(',', $user_groups) . ') ';
        $re_courses = $this->_executeQuery($query);
        while (list($id_course) = sql_fetch_row($re_courses)) {
            $courses[$id_course] = $id_course;
        }

        return $courses;
    }

    /**
     * @param int $id_user the idst of a user
     *
     * @return array the id of the coursepath associated to the user
     */
    public function getAllCoursepathOfUser($id_user)
    {
        $coursespath = [];
        $user_groups = $this->acl->getSTGroupsST($id_user);
        $query = '
		SELECT DISTINCT ce.idEntry
		FROM ' . $this->_getCataEntryTable() . ' AS ce JOIN ' . $this->_getCataMemberTable() . " AS cm
		WHERE ce.type_of_entry = 'coursepath' AND
			ce.idCatalogue = cm.idCatalogue AND
			cm.idst_member IN (" . implode(',', $user_groups) . ') ';
        $re_courses = $this->_executeQuery($query);
        while (list($id_path) = sql_fetch_row($re_courses)) {
            $coursespath[$id_path] = $id_path;
        }

        return $coursespath;
    }

    public function getCatalogueCourse($id_cat, $for_admin = false)
    {
        require_once _lms_ . '/lib/lib.coursepath.php';
        $path_man = new CoursePath_Manager();

        $query = 'SELECT idEntry, type_of_entry'
                    . ' FROM %lms_catalogue_entry'
                    . " WHERE idCatalogue = '" . $id_cat . "'";

        $result = sql_query($query);
        $res = [];

        while (list($id_entry, $type) = sql_fetch_row($result)) {
            if ($type == 'course') {
                $res[$id_entry] = $id_entry;
            } elseif ($for_admin) {
                $coursepath_course = &$path_man->getAllCourses([$id_entry]);
                foreach ($coursepath_course as $id_course) {
                    $res[$id_course] = $id_course;
                }
            }
        }

        return $res;
    }

    public function getCatalogueCoursepath($id_cat)
    {
        $query = 'SELECT idEntry'
                . ' FROM %lms_catalogue_entry'
                . " AND type_of_entry = 'coursepath'"
                . " WHERE idCatalogue = '" . $id_cat . "'";

        $result = sql_query($query);
        $res = [];

        while (list($id_entry) = sql_fetch_row($result)) {
            $res[$id_entry] = $id_entry;
        }

        return $res;
    }
}

class AdminCatalogue
{
    /**
     * @var the acl instance
     */
    public $acl;

    /**
     * @var the aclManager instance
     */
    public $aclManager;

    /**
     * class constructor.
     */
    public function AdminCatalogue()
    {
    }

    /**
     * exucute querys and do some debug function.
     *
     * @param string $query the text of the query
     *
     * @return resource_id the result of the query
     */
    public function _executeQuery($query)
    {
        $rs = sql_query($query);
        $GLOBALS['page']->add('<!-- ' . $query . ' : ' . sql_error() . ' -->' . "\n", 'debug');

        return $rs;
    }

    /**
     * @return string the name of the catalogue main table
     */
    public function _getCataTable()
    {
        return $GLOBALS['prefix_lms'] . '_catalogue';
    }

    /**
     * @return string the name of the catalogue contained element
     */
    public function _getCataEntryTable()
    {
        return $GLOBALS['prefix_lms'] . '_catalogue_entry';
    }

    /**
     * @param array $catalogues the id of the catalogues
     *
     * @return array the id of all the course associated to the catalogues
     */
    public function getAllCourses($catalogues)
    {
        $courses = [];
        if (empty($catalogues)) {
            return [];
        }
        $query = '
		SELECT DISTINCT ce.idEntry
		FROM ' . $this->_getCataEntryTable() . " AS ce
		WHERE ce.type_of_entry = 'course' AND
			ce.idCatalogue  IN (" . implode(',', $catalogues) . ') ';
        $re_courses = $this->_executeQuery($query);
        while (list($id) = sql_fetch_row($re_courses)) {
            $courses[$id] = $id;
        }

        return $courses;
    }

    /**
     * @param array $catalogues the id of the catalogues
     *
     * @return array the id of all the coursepath associated to the catalogues
     */
    public function getAllCoursePaths($catalogues)
    {
        $coursepaths = [];
        if (empty($catalogues)) {
            return [];
        }
        $query = '
		SELECT DISTINCT ce.idEntry
		FROM ' . $this->_getCataEntryTable() . " AS ce
		WHERE ce.type_of_entry = 'coursepath' AND
			ce.idCatalogue  IN (" . implode(',', $catalogues) . ') ';
        $re_coursepaths = $this->_executeQuery($query);
        while (list($id) = sql_fetch_row($re_coursepaths)) {
            $coursepaths[$id] = $id;
        }

        return $coursepaths;
    }
}
