<?php defined("IN_FORMA") or die("Direct access is forbidden");

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

/**
 * The Location controller class
 *
 * This Controller is used to retrieve and manipulate all kind of
 * information about the classrooms and their locations (add-edit-delete).
 * @since 4.0
 */
class LocationAlmsController extends AlmsController {

	protected $json = false;
	protected $model = false;
	protected $perm = array();

	public function init() {
		parent::init();

		$this->json = new Services_JSON();
		$this->model = new LocationAlms();
		$this->perm = array(
			'view' => checkPerm('view', true, 'location', 'lms'),
			'mod' => checkPerm('mod', true, 'location', 'lms')
		);
	}

	public function showTask() {
		$this->render('show', array());
	}

	public function addmask() {

		$location = new stdClass();
		$location->location = '';


		$this->render('location_form', array('location' => $location));

		$params = array(
			'success' => true,
			'header' => Lang::t('_ADD', 'standard'),
			'body' => ob_get_clean()
		);
		@ob_start();
		echo $this->json->encode($params);
	}

	public function getlocation() {

		$sortable = array('location');

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");

		$filter = Get::req('filter_text', DOTY_STRING, "");

		if (!in_array($sort, $sortable))
			$sort = 'location';
		switch ($dir) {
			case "desc" : {
					$dir = 'desc';
				};
				break;
			default : {
					$dir = 'asc';
				};
				break;
		}

		$location_list = $this->model->getLocationList($startIndex, $results, $sort, $dir, $filter);
		$total = $this->model->getLocationTotal($filter);

		//check if the user is a sub admin and has limited visibility on class locations
		$admin_locations = array();
		$ulevel = Docebo::user()->user_level;
		if ($ulevel != ADMIN_GROUP_GODADMIN) {
			$amodel = FALSE;
			switch ($ulevel) {
				case ADMIN_GROUP_ADMIN: $amodel = new AdminmanagerAdm();
					break;
			}
			if ($amodel !== FALSE) {
				$admin_locations = $amodel->loadClasslocationsSelection(Docebo::user()->idst);
			}
		}

		while (list($i, $location) = each($location_list)) {
			if ($ulevel != ADMIN_GROUP_GODADMIN && !in_array($location->id_location, $admin_locations)) {
				$location->can_manage_classrooms = 0;
				$location->location_mod = FALSE;
				$location->location_del = FALSE;
			} else {
				$location->can_manage_classrooms = 1;
				$location->location_mod = ($this->perm['mod'] ? 'ajax.adm_server.php?r=alms/location/mod&amp;location='.$location->id_location : FALSE);
				$location->location_del = ($this->perm['mod'] ? 'ajax.adm_server.php?r=alms/location/del&amp;location='.$location->id_location : FALSE);
			}
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => 25,
			'results' => count($location_list),
			'records' => array_values($location_list)
		);
		echo $this->json->encode($output);
	}

	protected function _canAdminLocation($id_user, $id_location) {
		//check if the user is a sub admin and has limited visibility on class locations
		$ulevel = Docebo::user()->user_level;
		if ($ulevel == ADMIN_GROUP_GODADMIN) {
			return TRUE;
		}

		$amodel = FALSE;
		switch ($ulevel) {
			case ADMIN_GROUP_ADMIN: $amodel = new AdminmanagerAdm();
				break;
		}
		if ($amodel !== FALSE) {
			$list = $amodel->loadClasslocationsSelection(Docebo::user()->idst);
			return in_array($id_location, $list);
		}

		return FALSE;
	}

	public function mod() {
		$location = Get::req('location', DOTY_STRING, '');

		if ($location <= 0 || !$this->_canAdminLocation(Docebo::user()->idst, $location)) {
			$params = array('success' => false);
			echo $this->json->encode($params);
			return;
		}

		$location_b = $this->model->getLocation($location);

		$this->render('edit_form', array('location' => $location_b));
		$params = array(
			'success' => true,
			'header' => Lang::t('_MOD', 'standard'),
			'body' => ob_get_clean()
		);
		@ob_start();
		echo $this->json->encode($params);
	}

	public function updatelocation() {

		$location_id = Get::req('location_id', DOTY_STRING, '');
		$location_new = Get::req('location_new', DOTY_STRING, '');

		if ($location_new != '') {

			$answ = $this->model->updateLocation($location_id, $location_new);

			$result = array(
				'success' => $answ,
				'message' => ( $answ ? '' : Lang::t('_OPERATION_FAILED', 'standard') )
			);
			echo $this->json->encode($result);
		} else {
			Lang::t('_OPERATION_FAILED', 'standard');
		}
	}

	public function insertlocation() {

		$location = Get::req('location', DOTY_STRING, '');
		if ($location == '') {
			$result = array('success' => false, 'message' => Lang::t('_NO_TITLE', 'standard'));
			echo $this->json->encode($result);
			return;
		}
		$re = $this->model->insertLocation($location);

		$result = array(
			'success' => $re,
			'message' => ( $re ? '' : Lang::t('_OPERATION_FAILED', 'standard') )
		);
		echo $this->json->encode($result);
	}

	public function delTask() {
		$location = Get::req('location', DOTY_STRING, '');

		if ($location <= 0 || !$this->_canAdminLocation(Docebo::user()->idst, $location)) {
			$params = array('success' => false);
			echo $this->json->encode($params);
			return;
		}

		$re = false;
		if ($location != '') {

			$re = $this->model->delLocation($location);
		}
		$result = array(
			'success' => $re,
			'message' => ( $re ? '' : Lang::t('_OPERATION_FAILED', 'standard') )
		);
		echo $this->json->encode($result);
	}

	public function listTask() {
		YuiLib::load('table');

		$location_id = Get::req('location_id', DOTY_STRING, Lang::get());

		$classroom_list = $this->model->getClassroomList();
		array_unshift($module_list, Lang::t('_ALL'));

		$language_list_diff = $language_list = $this->model->getLangCodeList();
		array_unshift($language_list_diff, Lang::t('_NONE'));

		$this->render('list', array(
			'lang_code' => $lang_code,
			'module_list' => $module_list,
			'language_list' => $language_list,
			'language_list_diff' => $language_list_diff
		));
	}

	public function show_classroom() {
		$id_location = Get::req('id_location', DOTY_INT, 0);
		$this->render('show_classroom',
				array('id_location' => $id_location)
		);
	}

	public function getclassroom() {

		$sortable = array('classroom');

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");

		$filter = Get::Req('filter_Text', DOTY_STRING, "");
		$id_location = Get::req('location_id', DOTY_INT, 0);

		$sort = 'lc.name';
		if (!in_array($sort, $sortable)) {
			switch ($dir) {
				case "desc" : {
					$dir = 'desc';
				};break;
				default : {
					$dir = 'asc';
				};break;
			}
		}
		$classroom_list = $this->model->getClassroomList($id_location, $startIndex, $results, $sort, $dir, $filter);

		$total = $this->model->getClassroomTotal($id_location, $filter);

		while (list($i, $classroom) = each($classroom_list)) {
			$classroom->classroom = $classroom->name;
			$classroom->classroom_mod = 'ajax.adm_server.php?r=alms/location/mod&amp;idClassroom=' . $classroom->name;
			$classroom->classroom_del = 'ajax.adm_server.php?r=alms/location/delClassroom&amp;idClassroom=' . $classroom->id_classroom;
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => 25,
			'results' => count($classroom_list),
			'records' => array_values($classroom_list)
		);
		echo $this->json->encode($output);
	}

	/**
	 * deletes an existing classroom
	 */
	public function delClassroomTask() {
		$classroom_id = Get::req('idClassroom', DOTY_INT, '');

		$re = false;
		if ($classroom_id != '') {

			$re = $this->model->delClassroom($classroom_id);
		}
		$result = array(
			'success' => $re,
			'message' => ( $re ? '' : Lang::t('_OPERATION_FAILED', 'standard') )
		);
		echo $this->json->encode($result);
	}

	/**
	 * Calls classroom_editmask.php to create a new classroom
	 */
	public function addclassroom() {

		$id_location = Get::req('id_location', DOTY_INT, 0);

		$this->render('classroom_editmask', array(
			'id_location' => $id_location,
			'_is_editing' => false
		));
	}

	/**
	 * Called by classroom_editmask.php to create a new classroom
	 */
	public function insertclassroom() {

		$id_location = Get::req('id_location', DOTY_INT, 0);
		$id_classroom = Get::req('id_classroom', DOTY_INT, 0);
		$name = Get::Req('name', DOTY_STRING, "");
		$description = Get::Req('description', DOTY_MIXED, "");
		$room = Get::Req('room', DOTY_STRING, "");
		$street = Get::Req('street', DOTY_STRING, "");
		$city = Get::Req('city', DOTY_STRING, "");
		$state = Get::Req('state', DOTY_STRING, "");
		$zip_code = Get::Req('zip_code', DOTY_STRING, "");
		$phone = Get::Req('phone', DOTY_STRING, "");
		$fax = Get::Req('fax', DOTY_STRING, "");
		$capacity = Get::Req('capacity', DOTY_STRING, "");
		$disposition = Get::Req('disposition', DOTY_MIXED, "");
		$instrument = Get::Req('instrument', DOTY_MIXED, "");
		$available_instrument = Get::Req('available_instrument', DOTY_MIXED, "");
		$note = Get::Req('note', DOTY_MIXED, "");
		$responsable = Get::Req('responsable', DOTY_STRING, "");

		$re = $this->model->InsertClassroomMod($name, $description, $id_location, $room, $street, $city, $state, $zip_code, $phone, $fax, $capacity, $disposition, $instrument, $available_instrument, $note, $responsable);

		$result = array(
			'success' => $re,
			'message' => ( $re ? '' : Lang::t('_OPERATION_FAILED', 'standard') )
		);
		echo $this->json->encode($result);
	}

	/**
	 * Calls classroom_editmask.php to edit an existing classroom
	 */
	public function modclassroom() {

		$id_location = Get::req('id_location', DOTY_INT, 0);
		$id_classroom = Get::req('id_classroom', DOTY_INT, 0);

		if ($id_classroom != '') {
			$re = $this->model->getClassroomDetails($id_classroom);
		}

		$info = $re;

		$this->render('classroom_editmask', array(
			'id_location' => $id_location,
			'id_classroom' => $id_classroom,
			'info' => $info,
			'_is_editing' => true
		));
	}

	/**
	 * Called by classroom_editmask.php to save modifies to a classroom
	 */
	public function saveclassroom() {

		$id_location = Get::req('id_location', DOTY_INT, 0);
		$idc = Get::req('id_classroom', DOTY_INT, 0);
		$name = Get::Req('name', DOTY_STRING, "");
		$description = Get::Req('description', DOTY_MIXED, "");
		$room = Get::Req('room', DOTY_STRING, "");
		$street = Get::Req('street', DOTY_STRING, "");
		$city = Get::Req('city', DOTY_STRING, "");
		$state = Get::Req('state', DOTY_STRING, "");
		$zip_code = Get::Req('zip_code', DOTY_STRING, "");
		$phone = Get::Req('phone', DOTY_STRING, "");
		$fax = Get::Req('fax', DOTY_STRING, "");
		$capacity = Get::Req('capacity', DOTY_STRING, "");
		$disposition = Get::Req('disposition', DOTY_MIXED, "");
		$instrument = Get::Req('instrument', DOTY_MIXED, "");
		$available_instrument = Get::Req('available_instrument', DOTY_MIXED, "");
		$note = Get::Req('note', DOTY_MIXED, "");
		$responsable = Get::Req('responsable', DOTY_STRING, "");

		$re = $this->model->UpdateClassroomMod($name, $description, $id_location, $room, $street, $city, $state, $zip_code, $phone, $fax, $capacity, $disposition, $instrument, $available_instrument, $note, $responsable, $idc);

		$result = array(
			'success' => $re,
			'message' => ( $re ? '' : Lang::t('_OPERATION_FAILED', 'standard') )
		);
		echo $this->json->encode($result);
	}

	/**
	 *  Calls classroom_editmask.php to edit an existing classroom
	 */
	public function show_calendar() {

		$id_classroom = Get::req('id_classroom', DOTY_INT, 0);

		$date_list = $this->model->getClassroomDates($id_classroom);

		$this->render('show_calendar', array(
			'id_classroom' => $id_classroom,
			'date_list' => $date_list,
			'info' => $this->model->getClassroomDetails($id_classroom)
		));
	}

	public function getclassroomdates() {

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");

		$date = Get::req('date_range', DOTY_STRING, "");
		$filter = Get::Req('filter_Text', DOTY_STRING, "");

		$id_classroom = Get::req('id_classroom', DOTY_INT, 0);

		if ($date == "false")
			$date = date("n-Y");

		$date_list = $this->model->getClassroomDates2date($id_classroom, $date, $startIndex, $results, $sort, $dir, $filter);
		$date_list_total = $this->model->getClassroomDateTotalDate($id_classroom, $date);

		$output = array(
			'totalRecords' => $date_list_total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => 25,
			'results' => count($date_list),
			'records' => (!empty($date_list) ? array_values($date_list) : array() )
		);
		echo $this->json->encode($output);
	}

}
