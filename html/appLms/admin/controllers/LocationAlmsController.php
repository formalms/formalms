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

defined('IN_FORMA') or exit('Direct access is forbidden');

/**
 * The Location controller class.
 *
 * This Controller is used to retrieve and manipulate all kind of
 * information about the classrooms and their locations (add-edit-delete).
 *
 * @since 4.0
 */
class LocationAlmsController extends AlmsController
{
    protected $json = false;
    protected $model = false;
    protected $perm = [];

    public function init()
    {
        parent::init();

        $this->json = new Services_JSON();
        $this->model = new LocationAlms();
        $this->perm = [
            'view' => checkPerm('view', true, 'location', 'lms'),
            'mod' => checkPerm('mod', true, 'location', 'lms'),
        ];
    }

    public function showTask()
    {
        $this->render('show', []);
    }

    public function addmask()
    {
        $location = new stdClass();
        $location->location = '';

        $this->render('location_form', ['location' => $location]);

        $params = [
            'success' => true,
            'header' => Lang::t('_ADD', 'standard'),
            'body' => ob_get_clean(),
        ];
        @ob_start();
        echo $this->json->encode($params);
    }

    public function getlocation()
    {
        $sortable = ['location'];

        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_STRING, '');
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');

        $filter = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');

        if (!in_array($sort, $sortable)) {
            $sort = 'location';
        }
        switch ($dir) {
            case 'desc':
                    $dir = 'desc';

                break;
            default:
                    $dir = 'asc';

                break;
        }

        $location_list = $this->model->getLocationList($startIndex, $results, $sort, $dir, $filter);
        $total = $this->model->getLocationTotal($filter);

        //check if the user is a sub admin and has limited visibility on class locations
        $admin_locations = [];
        $ulevel = Docebo::user()->user_level;
        if ($ulevel != ADMIN_GROUP_GODADMIN) {
            $amodel = false;
            switch ($ulevel) {
                case ADMIN_GROUP_ADMIN: $amodel = new AdminmanagerAdm();
                    break;
            }
            if ($amodel !== false) {
                $admin_locations = $amodel->loadClasslocationsSelection(Docebo::user()->idst);
            }
        }

        foreach ($location_list as $i => $location) {
            if ($ulevel != ADMIN_GROUP_GODADMIN && !in_array($location->id_location, $admin_locations)) {
                $location->can_manage_classrooms = 0;
                $location->location_mod = false;
                $location->location_del = false;
            } else {
                $location->can_manage_classrooms = 1;
                $location->location_mod = ($this->perm['mod'] ? 'ajax.adm_server.php?r=alms/location/mod&amp;location=' . $location->id_location : false);
                $location->location_del = ($this->perm['mod'] ? 'ajax.adm_server.php?r=alms/location/del&amp;location=' . $location->id_location : false);
            }
        }

        $output = [
            'totalRecords' => $total,
            'startIndex' => $startIndex,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => 25,
            'results' => count($location_list),
            'records' => array_values($location_list),
        ];
        echo $this->json->encode($output);
    }

    protected function _canAdminLocation($id_user, $id_location)
    {
        //check if the user is a sub admin and has limited visibility on class locations
        $ulevel = Docebo::user()->user_level;
        if ($ulevel == ADMIN_GROUP_GODADMIN) {
            return true;
        }

        $amodel = false;
        switch ($ulevel) {
            case ADMIN_GROUP_ADMIN: $amodel = new AdminmanagerAdm();
                break;
        }
        if ($amodel !== false) {
            $list = $amodel->loadClasslocationsSelection(Docebo::user()->idst);

            return in_array($id_location, $list);
        }

        return false;
    }

    public function mod()
    {
        $location = FormaLms\lib\Get::req('location', DOTY_STRING, '');

        if ($location <= 0 || !$this->_canAdminLocation(Docebo::user()->idst, $location)) {
            $params = ['success' => false];
            echo $this->json->encode($params);

            return;
        }

        $location_b = $this->model->getLocation($location);

        $this->render('edit_form', ['location' => $location_b]);
        $params = [
            'success' => true,
            'header' => Lang::t('_MOD', 'standard'),
            'body' => ob_get_clean(),
        ];
        @ob_start();
        echo $this->json->encode($params);
    }

    public function updatelocation()
    {
        $location_id = FormaLms\lib\Get::req('location_id', DOTY_STRING, '');
        $location_new = FormaLms\lib\Get::req('location_new', DOTY_STRING, '');

        if ($location_new != '') {
            $answ = $this->model->updateLocation($location_id, $location_new);

            $result = [
                'success' => $answ,
                'message' => ($answ ? '' : Lang::t('_OPERATION_FAILED', 'standard')),
            ];
            echo $this->json->encode($result);
        } else {
            Lang::t('_OPERATION_FAILED', 'standard');
        }
    }

    public function insertlocation()
    {
        $location = FormaLms\lib\Get::req('location', DOTY_STRING, '');
        if ($location == '') {
            $result = ['success' => false, 'message' => Lang::t('_NO_TITLE', 'standard')];
            echo $this->json->encode($result);

            return;
        }
        $re = $this->model->insertLocation($location);

        $result = [
            'success' => $re,
            'message' => ($re ? '' : Lang::t('_OPERATION_FAILED', 'standard')),
        ];
        echo $this->json->encode($result);
    }

    public function delTask()
    {
        $location = FormaLms\lib\Get::req('location', DOTY_STRING, '');

        if ($location <= 0 || !$this->_canAdminLocation(Docebo::user()->idst, $location)) {
            $params = ['success' => false];
            echo $this->json->encode($params);

            return;
        }

        $re = false;
        if ($location != '') {
            $re = $this->model->delLocation($location);
        }
        $result = [
            'success' => $re,
            'message' => ($re ? '' : Lang::t('_OPERATION_FAILED', 'standard')),
        ];
        echo $this->json->encode($result);
    }

    public function listTask()
    {
        YuiLib::load('table');

        $location_id = FormaLms\lib\Get::req('location_id', DOTY_STRING, Lang::get());

        $classroom_list = $this->model->getClassroomList();
        array_unshift($module_list, Lang::t('_ALL'));

        $language_list_diff = $language_list = $this->model->getLangCodeList();
        array_unshift($language_list_diff, Lang::t('_NONE'));

        $this->render('list', [
            'lang_code' => $lang_code,
            'module_list' => $module_list,
            'language_list' => $language_list,
            'language_list_diff' => $language_list_diff,
        ]);
    }

    public function show_classroom()
    {
        $id_location = FormaLms\lib\Get::req('id_location', DOTY_INT, 0);
        $this->render('show_classroom',
                ['id_location' => $id_location]
        );
    }

    public function getclassroom()
    {
        $sortable = ['classroom'];

        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_STRING, '');
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');

        $filter = FormaLms\lib\Get::Req('filter_Text', DOTY_STRING, '');
        $id_location = FormaLms\lib\Get::req('location_id', DOTY_INT, 0);

        $sort = 'lc.name';
        if (!in_array($sort, $sortable)) {
            switch ($dir) {
                case 'desc':
                    $dir = 'desc';
                 break;
                default:
                    $dir = 'asc';
                 break;
            }
        }
        $classroom_list = $this->model->getClassroomList($id_location, $startIndex, $results, $sort, $dir, $filter);

        $total = $this->model->getClassroomTotal($id_location, $filter);
        foreach ($classroom_list as $i => $classroom) {
            $classroom->classroom = $classroom->name;
            $classroom->classroom_mod = 'ajax.adm_server.php?r=alms/location/mod&amp;idClassroom=' . $classroom->name;
            $classroom->classroom_del = 'ajax.adm_server.php?r=alms/location/delClassroom&amp;idClassroom=' . $classroom->id_classroom;
        }

        $output = [
            'totalRecords' => $total,
            'startIndex' => $startIndex,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => 25,
            'results' => count($classroom_list),
            'records' => array_values($classroom_list),
        ];
        echo $this->json->encode($output);
    }

    /**
     * deletes an existing classroom.
     */
    public function delClassroomTask()
    {
        $classroom_id = FormaLms\lib\Get::req('idClassroom', DOTY_INT, '');

        $re = false;
        if ($classroom_id != '') {
            $re = $this->model->delClassroom($classroom_id);
        }
        $result = [
            'success' => $re,
            'message' => ($re ? '' : Lang::t('_OPERATION_FAILED', 'standard')),
        ];
        echo $this->json->encode($result);
    }

    /**
     * Calls classroom_editmask.php to create a new classroom.
     */
    public function addclassroom()
    {
        $id_location = FormaLms\lib\Get::req('id_location', DOTY_INT, 0);

        $this->render('classroom_editmask', [
            'id_location' => $id_location,
            '_is_editing' => false,
        ]);
    }

    /**
     * Called by classroom_editmask.php to create a new classroom.
     */
    public function insertclassroom()
    {
        $id_location = FormaLms\lib\Get::req('id_location', DOTY_INT, 0);
        $id_classroom = FormaLms\lib\Get::req('id_classroom', DOTY_INT, 0);
        $name = FormaLms\lib\Get::Req('name', DOTY_STRING, '');
        $description = FormaLms\lib\Get::Req('description', DOTY_MIXED, '');
        $room = FormaLms\lib\Get::Req('room', DOTY_STRING, '');
        $street = FormaLms\lib\Get::Req('street', DOTY_STRING, '');
        $city = FormaLms\lib\Get::Req('city', DOTY_STRING, '');
        $state = FormaLms\lib\Get::Req('state', DOTY_STRING, '');
        $zip_code = FormaLms\lib\Get::Req('zip_code', DOTY_STRING, '');
        $phone = FormaLms\lib\Get::Req('phone', DOTY_STRING, '');
        $fax = FormaLms\lib\Get::Req('fax', DOTY_STRING, '');
        $capacity = FormaLms\lib\Get::Req('capacity', DOTY_STRING, '');
        $disposition = FormaLms\lib\Get::Req('disposition', DOTY_MIXED, '');
        $instrument = FormaLms\lib\Get::Req('instrument', DOTY_MIXED, '');
        $available_instrument = FormaLms\lib\Get::Req('available_instrument', DOTY_MIXED, '');
        $note = FormaLms\lib\Get::Req('note', DOTY_MIXED, '');
        $responsable = FormaLms\lib\Get::Req('responsable', DOTY_STRING, '');

        $re = $this->model->InsertClassroomMod($name, $description, $id_location, $room, $street, $city, $state, $zip_code, $phone, $fax, $capacity, $disposition, $instrument, $available_instrument, $note, $responsable);

        $result = [
            'success' => $re,
            'message' => ($re ? '' : Lang::t('_OPERATION_FAILED', 'standard')),
        ];
        echo $this->json->encode($result);
    }

    /**
     * Calls classroom_editmask.php to edit an existing classroom.
     */
    public function modclassroom()
    {
        $id_location = FormaLms\lib\Get::req('id_location', DOTY_INT, 0);
        $id_classroom = FormaLms\lib\Get::req('id_classroom', DOTY_INT, 0);

        if ($id_classroom != '') {
            $re = $this->model->getClassroomDetails($id_classroom);
        }

        $info = $re;

        $this->render('classroom_editmask', [
            'id_location' => $id_location,
            'id_classroom' => $id_classroom,
            'info' => $info,
            '_is_editing' => true,
        ]);
    }

    /**
     * Called by classroom_editmask.php to save modifies to a classroom.
     */
    public function saveclassroom()
    {
        $id_location = FormaLms\lib\Get::req('id_location', DOTY_INT, 0);
        $idc = FormaLms\lib\Get::req('id_classroom', DOTY_INT, 0);
        $name = FormaLms\lib\Get::Req('name', DOTY_STRING, '');
        $description = FormaLms\lib\Get::Req('description', DOTY_MIXED, '');
        $room = FormaLms\lib\Get::Req('room', DOTY_STRING, '');
        $street = FormaLms\lib\Get::Req('street', DOTY_STRING, '');
        $city = FormaLms\lib\Get::Req('city', DOTY_STRING, '');
        $state = FormaLms\lib\Get::Req('state', DOTY_STRING, '');
        $zip_code = FormaLms\lib\Get::Req('zip_code', DOTY_STRING, '');
        $phone = FormaLms\lib\Get::Req('phone', DOTY_STRING, '');
        $fax = FormaLms\lib\Get::Req('fax', DOTY_STRING, '');
        $capacity = FormaLms\lib\Get::Req('capacity', DOTY_STRING, '');
        $disposition = FormaLms\lib\Get::Req('disposition', DOTY_MIXED, '');
        $instrument = FormaLms\lib\Get::Req('instrument', DOTY_MIXED, '');
        $available_instrument = FormaLms\lib\Get::Req('available_instrument', DOTY_MIXED, '');
        $note = FormaLms\lib\Get::Req('note', DOTY_MIXED, '');
        $responsable = FormaLms\lib\Get::Req('responsable', DOTY_STRING, '');

        $re = $this->model->UpdateClassroomMod($name, $description, $id_location, $room, $street, $city, $state, $zip_code, $phone, $fax, $capacity, $disposition, $instrument, $available_instrument, $note, $responsable, $idc);

        $result = [
            'success' => $re,
            'message' => ($re ? '' : Lang::t('_OPERATION_FAILED', 'standard')),
        ];
        echo $this->json->encode($result);
    }

    /**
     *  Calls classroom_editmask.php to edit an existing classroom.
     */
    public function show_calendar()
    {
        $id_classroom = FormaLms\lib\Get::req('id_classroom', DOTY_INT, 0);

        $date_list = $this->model->getClassroomDates($id_classroom);

        $this->render('show_calendar', [
            'id_classroom' => $id_classroom,
            'date_list' => $date_list,
            'info' => $this->model->getClassroomDetails($id_classroom),
        ]);
    }

    public function getclassroomdates()
    {
        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_STRING, '');
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');

        $date = FormaLms\lib\Get::req('date_range', DOTY_STRING, '');
        $filter = FormaLms\lib\Get::Req('filter_Text', DOTY_STRING, '');

        $id_classroom = FormaLms\lib\Get::req('id_classroom', DOTY_INT, 0);

        if ($date == 'false') {
            $date = date('n-Y');
        }

        $date_list = $this->model->getClassroomDates2date($id_classroom, $date, $startIndex, $results, $sort, $dir, $filter);
        $date_list_total = $this->model->getClassroomDateTotalDate($id_classroom, $date);

        $output = [
            'totalRecords' => $date_list_total,
            'startIndex' => $startIndex,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => 25,
            'results' => count($date_list),
            'records' => (!empty($date_list) ? array_values($date_list) : []),
        ];
        echo $this->json->encode($output);
    }
}
