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

require_once _base_ . '/lib/table_view/class.tableview.php';

class CourseTableView extends TableView
{
    public function __construct($id)
    {
        parent::__construct($id);

        $this->serverUrl = FormaLms\lib\Get::rel_path('lms') . '/ajax.adm_server.php?plf=lms&file=coursetableview&sf=table_view';
        $this->addFormatter('man_subscr', 'courseFormatters.man_subscr');

        $this->addFormatter('classroom', 'courseFormatters.classroom');
        $this->addFormatter('certificate', 'courseFormatters.certificate');
        $this->addFormatter('competence', 'courseFormatters.competence');
        $this->addFormatter('menu', 'courseFormatters.menu');
        $this->addFormatter('mod', 'courseFormatters.mod');

        $array_columns = [];

        if (checkPerm('mod', true, 'course', 'lms')) {
            $array_columns[] = ['key' => 'code', 'label' => Lang::t('_CODE', 'course', 'lms'), 'sortable' => true, 'className' => 'min-cell', 'editor' => 'new YAHOO.widget.TextboxCellEditor({asyncSubmitter: saveData})'];
            $array_columns[] = ['key' => 'name', 'label' => Lang::t('_COURSE_NAME', 'course', 'lms'), 'sortable' => true, 'editor' => 'new YAHOO.widget.TextboxCellEditor({asyncSubmitter: saveData})'];
        } else {
            $array_columns[] = ['key' => 'code', 'label' => Lang::t('_CODE', 'course', 'lms'), 'sortable' => true, 'className' => 'min-cell'];
            $array_columns[] = ['key' => 'name', 'label' => Lang::t('_COURSE_NAME', 'course', 'lms'), 'sortable' => true];
        }

        $array_columns[] = ['key' => 'waiting', 'label' => Lang::t('_WAITING_USERS', 'course', 'lms'), 'className' => 'img-cell'];

        if (checkPerm('subscribe', true, 'course', 'lms')) {
            $array_columns[] = ['key' => 'subscriptions', 'label' => FormaLms\lib\Get::img('course/subscribe.png', Lang::t('_SUBSCRIBE', 'course', 'lms')), 'sortable' => true, 'className' => 'img-cell', 'formatter' => $this->getCellFormatter('man_subscr')];
        }
        if (checkPerm('mod', true, 'course', 'lms')) {
            $array_columns[] = ['key' => 'classroom', 'label' => FormaLms\lib\Get::img('course/classroom-cal.png', Lang::t('_CLASSROOM', 'course', 'lms')), 'className' => 'img-cell'];
            $array_columns[] = ['key' => 'certificate', 'label' => FormaLms\lib\Get::img('course/certificate.png', Lang::t('_CERTIFICATE', 'certificate', 'lms')), 'className' => 'img-cell', 'formatter' => $this->getCellFormatter('certificate')];
            $array_columns[] = ['key' => 'competence', 'label' => FormaLms\lib\Get::img('course/competences.png', Lang::t('_COMPETENCES', 'competences', 'lms')), 'className' => 'img-cell', 'formatter' => $this->getCellFormatter('competence')];
            $array_columns[] = ['key' => 'menu', 'label' => FormaLms\lib\Get::img('course/menu.png', Lang::t('_ASSIGN_MENU', 'course', 'lms')), 'className' => 'img-cell', 'formatter' => $this->getCellFormatter('menu')];
        }

        if (checkPerm('add', true, 'course', 'lms')) {
            $array_columns[] = ['key' => 'dup', 'label' => FormaLms\lib\Get::img('standard/dup.png', Lang::t('_MAKE_A_COPY', 'course', 'lms')), 'className' => 'img-cell'];
        }

        if (checkPerm('mod', true, 'course', 'lms')) {
            $array_columns[] = ['key' => 'mod', 'label' => FormaLms\lib\Get::img('standard/edit.png', Lang::t('_MOD', 'course', 'lms')), 'className' => 'img-cell', 'formatter' => $this->getCellFormatter('mod')];
        }

        if (checkPerm('del', true, 'course', 'lms')) {
            $array_columns[] = ['key' => 'del', 'label' => FormaLms\lib\Get::img('standard/delete.png', Lang::t('_DEL', 'course', 'lms')), 'className' => 'img-cell', 'formatter' => $this->getCellFormatter('delete')];
        }

        $this->columns = $array_columns;

        $this->fields = [
            'idCourse', 'code', 'name', 'status', 'waiting',
            'subscriptions', 'classroom', 'certificate', 'competence', 'menu', 'dup', 'mod', 'del',
        ];

        $this->addOption('langs', [
            '_START' => Lang::t('_START', 'course', 'lms'),
            '_PREV' => Lang::t('_PREV', 'course', 'lms'),
            '_NEXT' => Lang::t('_NEXT', 'course', 'lms'),
            '_END' => Lang::t('_END', 'course', 'lms'),
            '_OF' => Lang::t('_OF', 'course', 'lms'),
            'MSG_EMPTY' => Lang::t('_EMPTY', 'course', 'lms'),
            'MSG_ERROR' => Lang::t('_SERVER_CONNECTION_ERROR', 'course', 'lms'),
            'MSG_LOADING' => Lang::t('_LOADING', 'course', 'lms'),
            '_YES' => Lang::t('_CONFIRM', 'course', 'lms'),
            '_NO' => Lang::t('_UNDO', 'course', 'lms'),
            '_AREYOUSURE' => Lang::t('_AREYOUSURE', 'course', 'lms'),
            '_DEL' => Lang::t('_DEL', 'course', 'lms'),
            '_SERVER_CONNECTION_ERROR' => Lang::t('_SERVER_CONNECTION_ERROR', 'course', 'lms'),
        ]);

        $courseCategory = $this->session->get('course_category');

        if (!isset($courseCategory['filter_status'])) {
            $courseCategory['filter_status'] = [
                'c_category' => 0,
                'c_filter' => '',
                'c_flatview' => true,
                'c_waiting' => false,
            ];
        } else {
            $filter = $courseCategory['filter_status'];
            if (!isset($filter['c_category'])) {
                $courseCategory['filter_status']['c_category'] = 0;
            }
            if (!isset($filter['c_filter'])) {
                $courseCategory['filter_status']['c_filter'] = '';
            }
            if (!isset($filter['c_flatview'])) {
                $courseCategory['filter_status']['c_flatview'] = true;
            }
            if (!isset($filter['c_waiting'])) {
                $courseCategory['filter_status']['c_waiting'] = '';
            }
        }
        $this->session->set('course_category', $courseCategory);
        $this->session->save();

        $filter = $courseCategory['filter_status'];

        $this->addOption('baseUrl', 'index.php');
        $this->addOption('imageUrl', FormaLms\lib\Get::tmpl_path('base') . 'images/');

        $this->addOption('initialFilter', [
                'c_category' => ['operator' => '', 'value' => $filter['c_category']],
                'c_filter' => ['operator' => '', 'value' => $filter['c_filter']],
                'c_flatview' => ['operator' => '', 'value' => $filter['c_flatview']],
                'c_waiting' => ['operator' => '', 'value' => $filter['c_waiting']],
            ]
        );

        $this->addOption('deleteDialog', ['id' => 'idCourse', 'name' => 'name']);
    }
}
