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

class HomecatalogueLmsController extends LmsController
{
	public $name = 'catalog';

	private $path_course = '';

	protected $_default_action = 'show';

	var $model;
	var $json;
	var $acl_man;

	public function isTabActive($tab_name)
	{
		return true;
	}

	public function init()
	{
        if(!HomepageAdm::staticIsCatalogToShow()) {
            Util::jump_to('');
        }

		YuiLib::load('base,tabview');
		Lang::init('course');
		$this->path_course = $GLOBALS['where_files_relative'].'/appLms/'.Get::sett('pathcourse').'/';
		$this->model = new HomecatalogueLms();
		$this->model_catalog = new CatalogLms();

		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();

		$this->acl_man =& Docebo::user()->getAclManager();
	}

	public function show()
	{
		$this->allCourse();
	}

	public function allCourse()
	{
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_lms_.'/lib/lib.middlearea.php');

		$active_tab = 'all';
		$action = Get::req('action', DOTY_STRING, '');
		$page = Get::req('page', DOTY_INT, 1);
		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$nav_bar = new NavBar('page', Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

		$nav_bar->setLink('index.php?r=homecatalogue/allCourse'.($id_cat > 1 ? '&amp;id_cat='.$id_cat : ''));

		//$html = $this->model->getCourseList($active_tab, $page);   
		$user_catalogue = array();//$this->model->getUserCatalogue(Docebo::user()->getIdSt());
		$user_coursepath = array();//$this->model->getUserCoursepath(Docebo::user()->getIdSt());

        /*
		echo '<div style="margin:1em;">';

		$this->render('tab_start', array(	'user_catalogue' => $user_catalogue,
											'active_tab' => $active_tab,
											'user_coursepath' => $user_coursepath,
											'std_link' => 'index.php?r=homecatalogue/allCourse'.($page > 1 ? '&amp;page='.$page : ''),
											'model' => $this->model_catalog));
		$this->render('courselist', array(	'html' => $html,
											'nav_bar' => $nav_bar));
		$this->render('tab_end', array(	'std_link' => 'index.php?r=homecatalogue/allCourse'.($page > 1 ? '&amp;page='.$page : ''),
										'model' => $this->model_catalog));
		echo '</div>';
        */
        
        echo '<div class="middlearea_container">';

        $this->render('catalog_header');
        $this->render('catalog_tree', array('model' => $this->model, 'id_cat=' => $id_cat));
                 
        echo '</div>';        
        
	}

    
    // AJAX 
    public function allCourseForma()
    {
  
       $id_cat = Get::req('id_cat', DOTY_INT, 0);     
       $typeCourse = Get::req('type_course', DOTY_STRING, '');
              
       $result = $this->model->getCourseList($typeCourse,1);
           
       $this->render('courselist', array( "result" => $result));

    }     
    
    
    
	public function newCourse()
	{
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_lms_.'/lib/lib.middlearea.php');

		$active_tab = 'new';
		$action = Get::req('action', DOTY_STRING, '');
		$page = Get::req('page', DOTY_INT, 1);
		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$nav_bar = new NavBar('page', Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

		$nav_bar->setLink('index.php?r=homecatalogue/allCourse'.($id_cat > 1 ? '&amp;id_cat='.$id_cat : ''));

		$html = $this->model->getCourseList($active_tab, $page);
		$user_catalogue = array();//$this->model->getUserCatalogue(Docebo::user()->getIdSt());
		$user_coursepath = array();//$this->model->getUserCoursepath(Docebo::user()->getIdSt());

		echo '<div style="margin:1em;">';

		$this->render('tab_start', array(	'user_catalogue' => $user_catalogue,
											'active_tab' => $active_tab,
											'user_coursepath' => $user_coursepath,
											'std_link' => 'index.php?r=homecatalogue/allCourse'.($page > 1 ? '&amp;page='.$page : ''),
											'model' => $this->model_catalog));
		$this->render('courselist', array(	'html' => $html,
											'nav_bar' => $nav_bar));
		$this->render('tab_end', array(	'std_link' => 'index.php?r=homecatalogue/allCourse'.($page > 1 ? '&amp;page='.$page : ''),
										'model' => $this->model_catalog));
		echo '</div>';
	}

	public function elearningCourse()
	{
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_lms_.'/lib/lib.middlearea.php');

		$active_tab = 'elearning';
		$action = Get::req('action', DOTY_STRING, '');
		$page = Get::req('page', DOTY_INT, 1);
		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$nav_bar = new NavBar('page', Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

		$nav_bar->setLink('index.php?r=homecatalogue/allCourse'.($id_cat > 1 ? '&amp;id_cat='.$id_cat : ''));

		$html = $this->model->getCourseList($active_tab, $page);
		$user_catalogue = array();//$this->model->getUserCatalogue(Docebo::user()->getIdSt());
		$user_coursepath = array();//$this->model->getUserCoursepath(Docebo::user()->getIdSt());

		echo '<div style="margin:1em;">';

		$this->render('tab_start', array(	'user_catalogue' => $user_catalogue,
											'active_tab' => $active_tab,
											'user_coursepath' => $user_coursepath,
											'std_link' => 'index.php?r=homecatalogue/allCourse'.($page > 1 ? '&amp;page='.$page : ''),
											'model' => $this->model_catalog));
		$this->render('courselist', array(	'html' => $html,
											'nav_bar' => $nav_bar));
		$this->render('tab_end', array(	'std_link' => 'index.php?r=homecatalogue/allCourse'.($page > 1 ? '&amp;page='.$page : ''),
										'model' => $this->model_catalog));
		echo '</div>';
	}

	public function classroomCourse()
	{
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_lms_.'/lib/lib.middlearea.php');

		$active_tab = 'classroom';
		$action = Get::req('action', DOTY_STRING, '');
		$page = Get::req('page', DOTY_INT, 1);
		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$nav_bar = new NavBar('page', Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

		$nav_bar->setLink('index.php?r=homecatalogue/allCourse'.($id_cat > 1 ? '&amp;id_cat='.$id_cat : ''));

		$html = $this->model->getCourseList($active_tab, $page);
		$user_catalogue = array();//$this->model->getUserCatalogue(Docebo::user()->getIdSt());
		$user_coursepath = array();//$this->model->getUserCoursepath(Docebo::user()->getIdSt());

		echo '<div style="margin:1em;">';

		$this->render('tab_start', array(	'user_catalogue' => $user_catalogue,
											'active_tab' => $active_tab,
											'user_coursepath' => $user_coursepath,
											'std_link' => 'index.php?r=homecatalogue/allCourse'.($page > 1 ? '&amp;page='.$page : ''),
											'model' => $this->model_catalog));
		$this->render('courselist', array(	'html' => $html,
											'nav_bar' => $nav_bar));
		$this->render('tab_end', array(	'std_link' => 'index.php?r=homecatalogue/allCourse'.($page > 1 ? '&amp;page='.$page : ''),
										'model' => $this->model_catalog));
		echo '</div>';
	}

	public function courseSelection()
	{
		$id_course = Get::req('id_course', DOTY_INT, 0);

		$res = $this->model->courseSelectionInfo($id_course);

		echo $this->json->encode($res);
	}

	function downloadDemoMaterialTask()
	{
		require_once(_base_.'/lib/lib.download.php');

		$id =Get::gReq('course_id', DOTY_INT);
		$db =DbConn::getInstance();

		$qtxt ="SELECT course_demo FROM %lms_course WHERE idCourse=".$id;

		$q =$db->query($qtxt);
		list($fname) =$db->fetch_row($q);

		if (!empty($fname)) {
			sendFile('/appLms/course/', $fname);
		}
		else {
			echo "nothing found";
		}
		die();
	}
} 
?>