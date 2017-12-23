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

class CourseLmsController extends LmsController
{
	public $name = 'course';

	protected $model;

	public function isTabActive($tab_name) {
		return true;
	}

	public function init() {
		YuiLib::load('base,tabview');

		require_once(_lms_.'/lib/lib.course.php');

		//$this->model = new CoursepathLms();
	}

	public function show()
	{
	    // url accesso al corso http://forma/appLms/index.php?r=course/show&course_id=1

        $course_id	= Get::req('course_id', DOTY_INT, "");
        if(!Docebo::user()->isAnonymous() && $course_id) {
            $db = DbConn::getInstance();

            $query_course = "SELECT name, img_course FROM %lms_course WHERE idCourse = ".$course_id." ";
            $course_data = $db->query($query_course);
            $path_course = $GLOBALS['where_files_relative'] . '/appLms/' . Get::sett('pathcourse') . '/';
            while($course = $db->fetch_obj($course_data)) {
                $course_name = $course->name;
                $course_img = (empty($course->img_course) || is_null($course->img_course)) ? Get::tmpl_path().'images/course/course_nologo.png' : $path_course.$course->img_course;
            }

            // get select menu
            $id_list = array();
            $menu_module = array();
            $query = "SELECT idMain AS id, name FROM %lms_menucourse_main WHERE idCourse = ".$course_id." ORDER BY sequence";
            $re_main = $db->query($query);
            while($main = $db->fetch_obj($re_main)) {

                $menu_module[] = array(
                    // 'submenu'=> array(),
                    'id_menu' => $main->id,
                    'name' => Lang::t($main->name, 'menu_course', false, false, $main->name ),
                    'link' => 'id_main_sel='.$main->id
                );
                $id_list[] = '"menu_lat_'.$main->id.'"';
            }
            $main_menu_id = Get::req('main_menu_id', DOTY_INT, "") ? Get::req('main_menu_id', DOTY_INT, "") : $menu_module[0]['id_menu'];
            // horizontal menu

            $menu_horizontal = array();
            $query_menu = "
	SELECT mo.idModule AS id, mo.module_name, mo.default_op, mo.default_name, mo.token_associated AS token, mo.mvc_path, under.idMain AS id_main, under.my_name
	FROM %lms_module AS mo JOIN %lms_menucourse_under AS under ON (mo.idModule = under.idModule)
	WHERE under.idCourse = ".$course_id."
	AND under.idMain = ".$main_menu_id."
	ORDER BY under.idMain, under.sequence";
            $re_menu_voice = $db->query($query_menu);

            while($obj = $db->fetch_obj($re_menu_voice)) {
                // checkmodule module
                if(checkPerm($obj->token, true, $obj->module_name)) {
                    $GLOBALS['module_assigned_name'][$obj->module_name] = ( $obj->my_name != '' ? $obj->my_name : Lang::t($obj->default_name, 'menu_course') );

                    $menu_horizontal[] = array(
                        'id_submenu' => $obj->id,
                        'name' => $GLOBALS['module_assigned_name'][$obj->module_name],
                        'link' => ( $obj->mvc_path != ''
                            ? 'index.php?r='.$obj->mvc_path.'&amp;id_module_sel='.$obj->id.'&amp;id_main_sel='.$obj->id_main
                            : 'index.php?modname='.$obj->module_name.'&amp;op='.$obj->default_op.'&amp;id_module_sel='.$obj->id.'&amp;id_main_sel='.$obj->id_main
                        )
                    );
                } // end if checkPerm

            } // end while

            $this->render('show', array(
                'menu_module' => $menu_module,
                'menu_horizontal' => $menu_horizontal,
                'course_name' => $course_name,
                'course_img' => $course_img));
        }
	}

	public function all()
	{
		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		
		$conditions = '';
		if (!empty($filter_text)) {
			$conditions = "AND cp.path_name LIKE '%".addslashes($filter_text)."%'";
		}
		
		$user_coursepath = $this->model->getAllCoursepath(Docebo::user()->getIdSt(), $conditions);
		$coursepath_courses = $this->model->getCoursepathCourseDetails(array_keys($user_coursepath));

		if(count($user_coursepath) > 0)
			$this->render('coursepath', array(	'type' => 'all',
												'user_coursepath' => $user_coursepath,
												'coursepath_courses' => $coursepath_courses));
		else
			echo Lang::t('_NO_COURSEPATH_IN_SECTION', 'coursepath');
	}
}