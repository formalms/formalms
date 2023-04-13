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

class HomecatalogueLms extends CatalogLms
{
    public $edition_man;
    public $course_man;
    public $classroom_man;

    public $cstatus;
    public $acl_man;

    /* category handling */
    public $children;
    public $the_tree;
    public $tree_deep;

    public function __construct()
    {
        require_once _lms_ . '/lib/lib.course.php';
        require_once _lms_ . '/lib/lib.edition.php';
        require_once _lms_ . '/lib/lib.date.php';

        $this->course_man = new Man_Course();
        $this->edition_man = new EditionManager();
        $this->classroom_man = new DateManager();

        $this->cstatus = [CST_PREPARATION => '_CST_PREPARATION',
                                CST_AVAILABLE => '_CST_AVAILABLE',
                                CST_EFFECTIVE => '_CST_CONFIRMED',
                                CST_CONCLUDED => '_CST_CONCLUDED',
                                CST_CANCELLED => '_CST_CANCELLED', ];

        $this->acl_man = &Forma::user()->getAclManager();
        parent::__construct();
    }

    public function getTotalCourseNumber($type = '')
    {
        require_once _lms_ . '/lib/lib.catalogue.php';
        $cat_man = new Catalogue_Manager();

        $user_catalogue = $cat_man->getUserAllCatalogueId(Forma::user()->getIdSt());
        $filter = '';

        switch ($type) {
            case 'elearning':
                $filter = " AND course_type = '" . $type . "'";
            break;
            case 'classroom':
                $filter = " AND course_type = '" . $type . "'";
            break;
            case 'edition':
                $filter = ' AND course_edition = 1';
            break;
            case 'new':
                $filter = " AND create_date >= '" . date('Y-m-d', mktime(0, 0, 0, date('m'), ((int) date('d') - 7), date('Y'))) . "'";
            break;
            case 'catalogue':
                $id_catalogue = FormaLms\lib\Get::req('id_cata', DOTY_INT, '0');

                $catalogue_course = &$cat_man->getCatalogueCourse($id_catalogue);
                $filter = ' AND idCourse IN (' . implode(',', $catalogue_course) . ')';
            break;
            default:
            break;
        }

        $filter .= ' AND show_rules = 0';

        $id_cat = FormaLms\lib\Get::req('id_cat', DOTY_INT, 0);

        $query = 'SELECT COUNT(*)'
                    . ' FROM %lms_course'
                    . ' WHERE status NOT IN (' . CST_PREPARATION . ', ' . CST_CONCLUDED . ', ' . CST_CANCELLED . ')'
                    . " AND course_type <> 'assessment'"
                    . ' AND ('
                    . " date_end = '0000-00-00'"
                    . " OR date_end > '" . date('Y-m-d') . "'"
                    . ' )'
                    . $filter
                    . ($id_cat > 0 ? ' AND idCategory = ' . (int) $id_cat : '')
                    . ' ORDER BY name';

        list($res) = sql_fetch_row(sql_query($query));

        return $res;
    }
}
