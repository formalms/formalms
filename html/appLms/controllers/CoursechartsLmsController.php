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

require_once(_base_.'/lib/lib.json.php');

define("CHART_TIME", 'time');
define("CHART_PASSED", 'passed');
define("CHART_SCORE", 'score');
define("CHART_ACTIVITY", 'activity');
define("CHART_CHAPTER", 'chapter');

class CoursechartsLmsController extends LmsController {

	protected $model;
	protected $json;

	public function init() {
		$this->model = new CoursechartsLms();
		$this->json = new Services_JSON();
	}

	protected function _format($num) {
		return number_format($num, 2);
	}

	protected function _encodeTimeData($idScorm, $data) {
		$output = '';
		$items_names = $this->model->getItems($idScorm, true);
		if (is_array($data)) {
			$arr = array();
			foreach ($items_names as $key=>$value) {
				$row = array();
				$row[] = 'id: '.$this->json->encode($key);
				$row[] = 'name: '.$this->json->encode($value);
				$row[] = 'average: '.$this->json->encode(isset($data['average'][$key]) ? $this->_format($data['average'][$key]) : 0);
				$row[] = 'user: '.$this->json->encode(isset($data['user'][$key]) ? $this->_format($data['user'][$key]) : 0);
				$arr[] = '{'.implode(',', $row).'}';
			}
			$output .= '['.implode(',', $arr).']';
		}
		return $output;
	}

	protected function _encodePassedData($idScorm, $data) {
		$output = '';
		$items_names = $this->model->getItems($idScorm, true);
		if (is_array($data)) {
			$arr = array();
			foreach ($items_names as $key=>$value) {
				$row = array();
				$row[] = 'id: '.$this->json->encode($key);
				$row[] = 'name: '.$this->json->encode($value);
				$row[] = 'passed: '.$this->json->encode(isset($data[$key]) ? $this->_format($data[$key]) : 0);
				$arr[] = '{'.implode(',', $row).'}';
			}
			$output .= '['.implode(',', $arr).']';
		}
		return $output;
	}


	protected function _encodeScoreData($idScorm, $data) {
		$output = '';
		$items_names = $this->model->getItems($idScorm, true);
		if (is_array($data)) {
			$arr = array();
			foreach ($items_names as $key=>$value) {
				$row = array();
				$row[] = 'id: '.$this->json->encode($key);
				$row[] = 'name: '.$this->json->encode($value);
				$row[] = 'average: '.$this->json->encode(isset($data[$key]['average']) ? $this->_format($data[$key]['average']) : 0);
				$row[] = 'user: '.$this->json->encode(isset($data[$key]['user']) ? $this->_format($data[$key]['user']) : 0);
				$arr[] = '{'.implode(',', $row).'}';
			}
			$output .= '['.implode(',', $arr).']';
		}
		return $output;
	}


	protected function _encodeActivityData($data) { //echo '<pre>'.print_r($data, true).'</pre>';
		$output = '';
		if (is_array($data)) {
			$arr = array();
			foreach ($data as $date=>$value) {
				$row = array();
				$row[] = 'date: '.$this->json->encode($date);//(Format::date($date, 'date'));
				$row[] = 'average: '.$this->json->encode(isset($data[$date]['average']) ? $this->_format($data[$date]['average']) : 0);
				$row[] = 'user: '.$this->json->encode(isset($data[$date]['user']) ? $this->_format($data[$date]['user']) : 0);
				$arr[] = '{'.implode(',', $row).'}';
			}
			$output .= '['.implode(',', $arr).']';
		}
		return $output;
	}



	protected function _encodeChapterData($idScorm, $data) {
		$output = '';
		$items_names = $this->model->getItems($idScorm, true);
		if (is_array($data)) {
			$arr = array();
			foreach ($items_names as $key=>$value) {
				$row = array();
				$row[] = 'id: '.$this->json->encode($key);
				$row[] = 'name: '.$this->json->encode($value);
				$row[] = 'passed: '.$this->json->encode(isset($data[$key]) ? ($data[$key] ? 1 : 0) : 0);
				$arr[] = '{'.implode(',', $row).'}';
			}
			$output .= '['.implode(',', $arr).']';
		}
		return $output;
	}


	//----------------------------------------------------------------------------

	public function show() {
		checkPerm('view', true, 'coursecharts');
		echo '<div class="std_block">';
		//echo getBackUi('index.php?r=coursecharts/show', Lang::t('_BACK', 'standard'));

		$idCourse = (isset($_SESSION['idCourse']) ? $_SESSION['idCourse'] : false);
		if (!is_numeric($idCourse) || $idCourse<=0) {
			echo Lang::t('_INVALID_COURSE', 'course_charts').'</div>';
			return;
		}

		$usersList = $this->model->getCourseUsers($idCourse);
		$idUser = getLogUserId();
		if (!array_key_exists($idUser, $usersList)) {
			$keysList = array_keys($usersList);
			$idUser = $keysList[0];
			unset($keysList);
		}
		$users = array('list'=>$usersList, 'selected'=>$idUser);

		//time chart
		$params = array(
			'id' => 'time_chart',
			'title' => Lang::t('_TIME_CHART_TITLE', 'course_charts'),
			'description' => Lang::t('_TIME_CHART_DESCRIPTION', 'course_charts'),
			'url' => 'index.php?r=coursecharts/showchart&chart_type='.CHART_TIME,
			'users' => $users
		);
		$this->render('chart_link', $params);

		//score chart
		$params = array(
			'id' => 'score_chart',
			'title' => Lang::t('_SCORE', 'course_charts'),
			'description' => Lang::t('_SCORE_CHART_DESCRIPTION', 'course_charts'),
			'url' => 'index.php?r=coursecharts/showchart&chart_type='.CHART_SCORE,
			'users' => $users
		);
		$this->render('chart_link', $params);

		//activity chart
		$params = array(
			'id' => 'activity_chart',
			'title' => Lang::t('_ACTIVITY', 'course_charts'),
			'description' => Lang::t('_ACTIVITY_CHART_DESCRIPTION', 'course_charts'),
			'url' => 'index.php?r=coursecharts/showchart&chart_type='.CHART_ACTIVITY,
			'users' => $users
		);
		$this->render('chart_link', $params);

		//chapter chart
		$params = array(
			'id' => 'chapter_chart',
			'title' => Lang::t('_CHAPTERS', 'course_charts'),
			'description' => Lang::t('_CHAPTER_CHART_DESCRIPTION', 'course_charts'),
			'url' => 'index.php?r=coursecharts/showchart&chart_type='.CHART_CHAPTER,
			'users' => $users
		);
		$this->render('chart_link', $params);

		//passed chart
		$params = array(
			'id' => 'passed_chart',
			'title' => Lang::t('_COMPLETED', 'course_charts'),
			'description' => Lang::t('_COMPLETED_CHART_DESCRIPTION', 'course_charts'),
			'url' => 'index.php?r=coursecharts/showchart&chart_type='.CHART_PASSED
		);
		$this->render('chart_link', $params);
		echo '</div>';
	}


	public function showchart() {
		checkPerm('view', true, 'coursecharts');
		$back_url = getBackUi('index.php?r=coursecharts/show', Lang::t('_BACK', 'standard'));
		echo '<div class="std_block">';
		echo $back_url;

		$chart_type = Get::req('chart_type', DOTY_ALPHANUM, '');
		$idCourse = (isset($_SESSION['idCourse']) ? $_SESSION['idCourse'] : false);//Get::req('id_course', DOTY_INT, -1);
		$scormList = $this->model->getScormList($idCourse);
		YuiLib::load('charts');

		if (!is_numeric($idCourse) || $idCourse<=0) {
			echo Lang::t('_INVALID_COURSE', 'course_charts').$back_url.'</div>';
			return;
		}

		if ((is_array($scormList) && count($scormList)>0) || $chart_type==CHART_ACTIVITY) {
			//YuiLib::load('charts');

			switch ($chart_type) {

					case CHART_TIME: {
						$usersList = $this->model->getCourseUsers($idCourse);
						if (is_array($usersList) && count($usersList)>0) {

							$idUser = Get::req('id_user', DOTY_INT, -1);
							if (!array_key_exists($idUser, $usersList)) {
								$keysList = array_keys($usersList);
								$idUser = $keysList[0];
								unset($keysList);
							}
							$params = array(
								'selected_user' => $idUser,
								'users_list' => $usersList,
								'form_url' => 'index.php?r=coursecharts/showchart&chart_type='.CHART_TIME
							);
							$this->render('show_time_chart', $params);

							foreach ($scormList as $idScorm=>$scormTitle) {
								if ($idScorm>0 && $idUser>0) {
									$chart_data = $this->model->getTimeData($idScorm, $idUser);
									
									$js_data = $this->_encodeTimeData($idScorm, $chart_data);

									$params = array(
										'id' => "time_chart_".$idScorm,
										'title' => $scormTitle,
										'js_data' => $js_data
									);
									$this->render('_time_chart', $params);
								} else {
									echo Lang::t('_INVALID_DATA', 'course_charts');
								}
							}
						} else {
							echo Lang::t('_NO_USERS_IN_THE_COURSE', 'course_charts');
						}
					} break;


					case CHART_PASSED: {
						$params = array();
						$this->render('show_passed_chart', $params);
						foreach ($scormList as $idScorm=>$scormTitle) {
							if ($idScorm>0) {
								$chart_data = $this->model->getPassedData($idScorm);
								$js_data = $this->_encodePassedData($idScorm, $chart_data);
								$params = array(
									'id' => "passed_chart_".$idScorm,
									'title' => $scormTitle,
									'js_data' => $js_data
								);
								$this->render('_passed_chart', $params);
							}
						}
					} break;


					case CHART_SCORE: {
						$usersList = $this->model->getCourseUsers($idCourse);
						if (is_array($usersList) && count($usersList)>0) {

							$idUser = Get::req('id_user', DOTY_INT, -1);
							if (!array_key_exists($idUser, $usersList)) {
								$keysList = array_keys($usersList);
								$idUser = $keysList[0];
								unset($keysList);
							}
							$params = array(
								'selected_user' => $idUser,
								'users_list' => $usersList,
								'form_url' => 'index.php?r=coursecharts/showchart&chart_type='.CHART_SCORE
							);
							$this->render('show_score_chart', $params);
							foreach ($scormList as $idScorm=>$scormTitle) {
								if ($idScorm>0 && $idUser>0) {
									$chart_data = $this->model->getScoreData($idScorm, $idUser);
									$js_data = $this->_encodeScoreData($idScorm, $chart_data);

									$params = array(
										'id' => "score_chart_".$idScorm,
										'title' => $scormTitle,
										'js_data' => $js_data
									);
									$this->render('_score_chart', $params);
								} else {
									echo Lang::t('_INVALID_DATA', 'course_charts');
								}
							}
						} else {
							echo Lang::t('_NO_USERS_IN_THE_COURSE', 'course_charts');
						}
					} break;


					case CHART_ACTIVITY: {
						$usersList = $this->model->getCourseUsers($idCourse);
						if (is_array($usersList) && count($usersList)>0) {

							$idUser = Get::req('id_user', DOTY_INT, -1);
							if (!array_key_exists($idUser, $usersList)) {
								$keysList = array_keys($usersList);
								$idUser = $keysList[0];
								unset($keysList);
							}
							$params = array(
								'selected_user' => $idUser,
								'users_list' => $usersList,
								'form_url' => 'index.php?r=coursecharts/showchart&chart_type='.CHART_ACTIVITY
							);
							$this->render('show_activity_chart', $params);

							if ($idCourse>0 && $idUser>0) {
								$chart_data = $this->model->getActivityData($idCourse, $idUser);
								$js_data = $this->_encodeActivityData($chart_data);
								$course_info = $this->model->getCourseInfo($idCourse);
								$params = array(
									'id' => "activity_chart_".$idCourse,
									'title' => $course_info['name'].' ('.$course_info['code'].')',
									'js_data' => $js_data
								);
								$this->render('_activity_chart', $params);
							} else {
								echo Lang::t('_INVALID_DATA', 'course_charts');
							}
						} else {
							echo Lang::t('_NO_USERS_IN_THE_COURSE', 'course_charts');
						}
					} break;


					case CHART_CHAPTER: {
						$usersList = $this->model->getCourseUsers($idCourse);
						if (is_array($usersList) && count($usersList)>0) {

							$idUser = Get::req('id_user', DOTY_INT, -1);
							if (!array_key_exists($idUser, $usersList)) {
								$keysList = array_keys($usersList);
								$idUser = $keysList[0];
								unset($keysList);
							}
							$params = array(
								'selected_user' => $idUser,
								'users_list' => $usersList,
								'form_url' => 'index.php?r=coursecharts/showchart&chart_type='.CHART_CHAPTER
							);
							$this->render('show_chapter_chart', $params);

							foreach ($scormList as $idScorm=>$scormTitle) {
								if ($idScorm>0 && $idUser>0) {
									$chart_data = $this->model->getChapterData($idScorm, $idUser);
									$js_data = $this->_encodeChapterData($idScorm, $chart_data);
									$params = array(
										'id' => "time_chart_".$idScorm,
										'title' => $scormTitle,
										'js_data' => $js_data
									);
									$this->render('_chapter_chart', $params);
								} else {
									echo Lang::t('_INVALID_DATA', 'course_charts');
								}
							}
						} else {
							echo Lang::t('_NO_USERS_IN_THE_COURSE', 'course_charts');
						}
					} break;


					default: echo Lang::t('_INVALID_CHART_TYPE', 'course_charts'); break;
				}

		} else {
			echo Lang::t('_NO_SCORM_IN_COURSE', 'course_charts');
		}

		echo $back_url;
		echo '</div>';
	}

}

?>