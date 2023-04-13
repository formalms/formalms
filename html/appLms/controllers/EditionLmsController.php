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

class EditionLmsController extends LmsController
{
    public $name = 'classroom';

    protected $json;
    protected $acl_man;

    protected $data;

    public function __construct($mvc_name)
    {
        parent::__construct($mvc_name);

        require_once _base_ . '/lib/lib.json.php';

        $this->json = new Services_JSON();
        $this->acl_man = &\FormaLms\lib\Forma::getAclManager();
    }

    protected function show()
    {
        //Course info
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);

        $model = new EditionLms($id_course);
        $this->render('show', ['model' => $model]);
    }

    protected function geteditionlist()
    {
        //Course info
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);

        //Datatable info
        $start_index = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_MIXED, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'userid');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'asc');

        $model = new EditionLms($id_course);

        $total_edition = $model->getEditionNumber();
        $array_edition = $model->loadEdition($start_index, $results, $sort, $dir);

        $result = ['totalRecords' => $total_edition,
                            'startIndex' => $start_index,
                            'sort' => $sort,
                            'dir' => $dir,
                            'rowsPerPage' => $results,
                            'results' => count($array_edition),
                            'records' => $array_edition, ];

        $this->data = $this->json->encode($result);

        echo $this->data;
    }

    public function add()
    {
        require_once _lms_ . '/lib/lib.course.php';

        //Course info
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);

        $course_info = Man_Course::getCourseInfo($id_course);

        $model = new EditionLms($id_course);

        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=edition/show&id_course=' . $model->getIdCourse());
        } elseif (isset($_POST['ins'])) {
            if ($model->addEdition()) {
                Util::jump_to('index.php?r=edition/show&id_course=' . $model->getIdCourse() . '&result=ok');
            }
            Util::jump_to('index.php?r=edition/show&id_course=' . $model->getIdCourse() . '&result=err_ins');
        } else {
            $this->render('add', ['model' => $model, 'course_info' => $course_info]);
        }
    }

    public function edit()
    {
        //Course info
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);
        $id_edition = FormaLms\lib\Get::req('id_edition', DOTY_INT, 0);

        $model = new EditionLms($id_course, $id_edition);

        $edition_info = $model->getEditionInfo($id_edition);

        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=edition/show&id_course=' . $model->getIdCourse());
        } elseif (isset($_POST['mod'])) {
            if ($model->modEdition()) {
                Util::jump_to('index.php?r=edition/show&id_course=' . $model->getIdCourse() . '&result=ok');
            }
            Util::jump_to('index.php?r=edition/show&id_course=' . $model->getIdCourse() . '&result=err_mod');
        } else {
            $this->render('edit', ['model' => $model, 'edition_info' => $edition_info]);
        }
    }

    public function del()
    {
        //Course info
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);
        $id_edition = FormaLms\lib\Get::req('id_edition', DOTY_INT, 0);

        $model = new EditionLms($id_course, $id_edition);

        $res = ['success' => $model->delEdition()];

        $this->data = $this->json->encode($res);

        echo $this->data;
    }
}
