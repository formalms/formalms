<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

require_once(_base_.'/lib/table_view/class.tableview.php');

class CourseTableView extends TableView {

	public function __construct($id) {

		parent::__construct($id);
		
		$lang 	=& DoceboLanguage::CreateInstance('course', 'lms');

                $this->serverUrl = Get::rel_path('lms').'/ajax.adm_server.php?plf=lms&file=coursetableview&sf=table_view';
                $this->addFormatter("man_subscr", 'courseFormatters.man_subscr');

		$this->addFormatter("classroom", 'courseFormatters.classroom');
		$this->addFormatter("certificate", 'courseFormatters.certificate');
		$this->addFormatter("competence", 'courseFormatters.competence');
		$this->addFormatter("menu", 'courseFormatters.menu');
		$this->addFormatter("mod", 'courseFormatters.mod');

		$array_columns = array();

		if(checkPerm('mod', true, 'course', 'lms'))
		{
			$array_columns[] = array("key"=>"code", "label"=>$lang->def("_CODE"), "sortable"=>true, "className"=>'min-cell', 'editor' => 'new YAHOO.widget.TextboxCellEditor({asyncSubmitter: saveData})');
			$array_columns[] = array("key"=>"name", "label"=>$lang->def("_COURSE_NAME"), "sortable"=>true, 'editor' => 'new YAHOO.widget.TextboxCellEditor({asyncSubmitter: saveData})');
		}
		else
		{
			$array_columns[] = array("key"=>"code", "label"=>$lang->def("_CODE"), "sortable"=>true, "className"=>'min-cell');
			$array_columns[] = array("key"=>"name", "label"=>$lang->def("_COURSE_NAME"), "sortable"=>true);
		}

		$array_columns[] = array("key"=>"waiting", "label"=>$lang->def("_WAITING_USERS"), "className"=>'img-cell');

		if(checkPerm('subscribe', true, 'course', 'lms')) {
			$array_columns[] = array("key"=>"subscriptions", "label"=>Get::img('course/subscribe.png', $lang->def('_SUBSCRIBE')), "sortable"=>true, "className"=>'img-cell', "formatter"=>$this->getCellFormatter("man_subscr"));
		}
		if(checkPerm('mod', true, 'course', 'lms'))
		{
			$array_columns[] = array("key"=>"classroom", "label"=>Get::img('course/classroom-cal.png', $lang->def('_CLASSROOM')), "className"=>'img-cell');
			$array_columns[] = array("key"=>"certificate", "label"=>Get::img('course/certificate.png', Lang::t('_CERTIFICATE', 'certificate', 'lms')), "className"=>'img-cell', "formatter"=>$this->getCellFormatter("certificate"));
			$array_columns[] = array("key"=>"competence", "label"=>Get::img('course/competences.png', Lang::t('_COMPETENCES', 'competences', 'lms')), "className"=>'img-cell', "formatter"=>$this->getCellFormatter("competence"));
			$array_columns[] = array("key"=>"menu", "label"=>Get::img('course/menu.png', $lang->def('_ASSIGN_MENU')), "className"=>'img-cell', "formatter"=>$this->getCellFormatter("menu"));
		}

		if(checkPerm('add', true, 'course', 'lms'))
			$array_columns[] = array("key"=>"dup", "label"=>Get::img('standard/dup.png', $lang->def('_MAKE_A_COPY')), "className"=>'img-cell');

		if(checkPerm('mod', true, 'course', 'lms'))
			$array_columns[] = array("key"=>"mod", "label"=>Get::img('standard/edit.png', $lang->def('_MOD')), "className"=>'img-cell', "formatter"=>$this->getCellFormatter("mod"));

		if(checkPerm('del', true, 'course', 'lms'))
			$array_columns[] = array("key"=>"del", "label"=>Get::img('standard/delete.png', $lang->def('_DEL')), "className"=>'img-cell', "formatter"=>$this->getCellFormatter('delete'));

		$this->columns = $array_columns;
		
		$this->fields = array(
			"idCourse", "code", "name", "status", "waiting",
			"subscriptions", "classroom", "certificate", "competence", "menu", "dup", "mod", "del"
		);

		$this->addOption('langs', array(
			'_START'		=> $lang->def('_START'),
			'_PREV'			=> $lang->def('_PREV'),
			'_NEXT'			=> $lang->def('_NEXT'),
			'_END'			=> $lang->def('_END'),
			'_OF'			=> $lang->def('_OF'),
			'MSG_EMPTY'		=> $lang->def('_EMPTY'),
			'MSG_ERROR'		=> $lang->def('_SERVER_CONNECTION_ERROR'),
			'MSG_LOADING'	=> $lang->def('_LOADING'),
			'_YES'			=> $lang->def('_CONFIRM'),
			'_NO'			=> $lang->def('_UNDO'),
			'_AREYOUSURE'	=> $lang->def('_AREYOUSURE'),
			'_DEL'			=> $lang->def('_DEL'),
			'_SERVER_CONNECTION_ERROR' => $lang->def('_SERVER_CONNECTION_ERROR')
		));

		if (!isset($_SESSION['course_category']['filter_status'])) {
			$_SESSION['course_category']['filter_status'] = array(
				'c_category' => 0,
				'c_filter' => '',
				'c_flatview' => true,
				'c_waiting' => false
			);
		} else {
			$filter =& $_SESSION['course_category']['filter_status'];
			if (!isset($filter['c_category'])) $_SESSION['course_category']['filter_status']['c_category'] = 0;
			if (!isset($filter['c_filter'])) $_SESSION['course_category']['filter_status']['c_filter'] = '';
			if (!isset($filter['c_flatview'])) $_SESSION['course_category']['filter_status']['c_flatview'] = true;
			if (!isset($filter['c_waiting'])) $_SESSION['course_category']['filter_status']['c_waiting'] = '';
		}
		$filter =& $_SESSION['course_category']['filter_status'];

		$this->addOption('baseUrl', 'index.php');
		$this->addOption('imageUrl', Get::tmpl_path('base').'images/');
		
		$this->addOption('initialFilter', array(
				'c_category' => array('operator'=>'', 'value'=>$filter['c_category']),
				'c_filter' => array('operator'=>'', 'value'=>$filter['c_filter']),
				'c_flatview' => array('operator'=>'', 'value'=>$filter['c_flatview']),
				'c_waiting' => array('operator'=>'', 'value'=>$filter['c_waiting']),
			)
		);

		$this->addOption('deleteDialog', array('id'=>'idCourse', 'name'=>'name'));
	}

}

?>