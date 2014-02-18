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

class CoursechartsLms extends Model {

	protected $db;

	public function  __construct() {
		$this->db = DbConn::getInstance();
	}

	public function getCourseInfo($idCourse) {
		$output = false;
		$query = "SELECT code, name FROM %lms_course WHERE idCourse=".(int)$idCourse;
		$res = $this->db->query($query);
		if ($res) {
			list($code, $name) = $this->db->fetch_row($res);
			$output = array('name'=>$name, 'code'=>$code);
		}
		return $output;
	}

	public function getCourseUsers($idCourse) {
		$output = false;
		$query = "SELECT cu.idUser, u.userid FROM %lms_courseuser as cu JOIN %adm_user as u ON (u.idst=cu.idUser) WHERE cu.idCourse=".(int)$idCourse;
		$res = $this->db->query($query);
		if ($res) {
			$output = array();
			$acl_man = Docebo::user()->getAclManager();
			while (list($idUser, $userName) = $this->db->fetch_row($res)) {
				$output[$idUser] = $acl_man->relativeId($userName);
			}
		}
		return $output;
	}

	public function getScormList($idCourse, $titles = true) {
		$output = false;
		$query = "SELECT idOrg, idResource, title FROM %lms_organization WHERE idCourse=".(int)$idCourse." AND objectType='scormorg'";
		$res = $this->db->query($query);
		if ($res) {
			$output = array();
			while ($row = $this->db->fetch_obj($res)) {
				if ($titles)
					$output[$row->idResource] = $row->title;
				else
					$output[] = $row->idResource;
			}
		}
		return $output;
	}

	public function getItems($idScorm, $titles = false, $max_length = false) {
		$output = array();
		if (is_numeric($idScorm) && $idScorm>0) {
			$query = "SELECT idscorm_item".($titles ? ", title" : "")." FROM %lms_scorm_items WHERE idscorm_organization=".(int)$idScorm
					." ORDER BY idscorm_item ";
			$res = $this->db->query($query);
			if ($res && $this->db->num_rows($res)>0) {
				while ($row = $this->db->fetch_obj($res)) {
					if ($titles) {
						$title = $row->title;
						if (is_numeric($max_length) && $max_length>0) {
							if (strlen($title)>$max_length) $title = substr($title, 0, $max_length).'...';
						}
						$output[$row->idscorm_item] = $title;
					} else
						$output[] = $row->idscorm_item;
				}
			}
		}
		return $output;
	}

	//get data for minutes chart

	function decodeSessionTime($stime) {
		$output = $stime;
			$re1 = preg_match ('/^P((\d*)Y)?((\d*)M)?((\d*)D)?(T((\d*)H)?((\d*)M)?((\d*)(\.(\d{1,2}))?S)?)?$/', $stime, $t1_s );
			if(!isset($t1_s[13]) || $t1_s[13] == '') $t1_s[13] = '00';
			if(!isset($t1_s[11]) || $t1_s[11] == '') $t1_s[11] = '00';
			if(!isset($t1_s[9]) || $t1_s[9] == '') $t1_s[9] = '00';
			if(!isset($t1_s[7]) || $t1_s[7] == '') $t1_s[7] = '0000';
			$output = ($t1_s[6]=='0000' || $t1_s[6] == '' ? '' : $t1_s[6].':')
				.sprintf("%'02s:%'02s.%'02s",  $t1_s[9], $t1_s[11], $t1_s[13]);
		
		return $output;
	}
	
	public function getTimeData($idScorm, $idUser) {
		$output = false;

		if (is_numeric($idScorm) && $idScorm>0) {

			//$query = "SELECT idOrg FROM %lms_organization WHERE idResource=".(int)$idScorm;
			//$res = $this->db->query($query);
			//if ($res && $this->db->num_rows($res)>0) {

				//list($idOrg) = $this->db->fetch_row($res);
				$query = "SELECT idscorm_item "
						." FROM %lms_scorm_items "
						." WHERE idscorm_organization=".(int)$idScorm
						." ORDER BY idscorm_item ";
				$res = $this->db->query($query);
				if ($res && $this->db->num_rows($res)>0) {

					$items = array();
					while (list($id_scorm_item) = $this->db->fetch_row($res)) $items[] = $id_scorm_item;

					//extract average time for every chapter
					if (is_numeric($idUser)) {

						$user_data = array();
						$query = "SELECT idscorm_item, total_time "
							." FROM %lms_scorm_tracking"
							." WHERE idscorm_item IN (".implode(",", $items).") AND idUser=".(int)$idUser
							." ORDER BY idscorm_item ";
						$res = $this->db->query($query);
						if ($res && $this->db->num_rows($res)>0) {
							while ($row = $this->db->fetch_obj($res)) {
								
								if($row->total_time{0} == 'P') {
									// scorm 2004
									$row->total_time = $this->decodeSessionTime($row->total_time);
								}
								// scorm 1.2
								list($hours, $minutes, $seconds) = explode(':', $row->total_time);
								if (strlen($seconds)>2) list($seconds, $hundredths) = explode('.', $seconds);
								$time = $hours*3600 + $minutes*60 + $seconds;
								$user_data[$row->idscorm_item] = $time/60;
								
							}
						}

						$average_data = array();
						$query = "SELECT idscorm_item, total_time FROM %lms_scorm_tracking WHERE idscorm_item IN (".implode(",", $items).")";
						$res = $this->db->query($query);
						if ($res && $this->db->num_rows($res)>0) {
							$totals = array();
							while ($row = $this->db->fetch_obj($res)) {
								if (!isset($totals[$row->idscorm_item])) $totals[$row->idscorm_item] = array('total_time'=>0, 'count'=>0);
								
								if($row->total_time{0} == 'P') {
									// scorm 2004
									$row->total_time = $this->decodeSessionTime($row->total_time);
								}
								// scorm 1.2
								list($hours, $minutes, $seconds) = explode(':', $row->total_time);
								if (strlen($seconds)>2) list($seconds, $hundredths) = explode('.', $seconds);
								$time = $hours*3600 + $minutes*60 + $seconds;

								$totals[$row->idscorm_item]['count']++;
								$totals[$row->idscorm_item]['total_time'] += $time;
							}

							foreach ($totals as $key=>$value) {
								$average_data[$key] = number_format( ($value['total_time']/$value['count'])/60, 2);
							}
						}

						$output = array(
							'average' => $average_data,
							'user' => $user_data
						);

					}
				}
			//}
		}

		return $output;
	}



	public function getPassedData($idScorm) {
		$output = false;
		$items = $this->getItems($idScorm, false);
		$query = "SELECT idscorm_item, lesson_status, COUNT(*) as num_count "
			." FROM %lms_scorm_tracking WHERE idscorm_item IN (".implode(",", $items).") "
			." GROUP BY idscorm_item, lesson_status"
			." ORDER BY idscorm_item ";
		$res = $this->db->query($query);
		if ($res) {
			$data = array();
			while ($row = $this->db->fetch_obj($res)) {
				if (!isset($data[$row->idscorm_item])) $data[$row->idscorm_item] = array('passed'=>0, 'not_passed'=>0);
				if ($row->lesson_status == 'passed' || $row->lesson_status == 'completed') {
					$data[$row->idscorm_item]['passed'] += $row->num_count;
				} else {
					$data[$row->idscorm_item]['not_passed'] += $row->num_count;
				}
			}

			//get number of users subscribed to the course
			$subs_man = new SubscriptionAlms($_SESSION['idCourse']);
			$tot = $subs_man->totalUser("", 3); //all subscribed students

			$output = array();
			foreach ($items as $idItem) {
				$p1 = (isset($data[$idItem]['passed']) ? $data[$idItem]['passed'] : 0);
				$p2 = (isset($data[$idItem]['not_passed']) ? $data[$idItem]['not_passed'] : 0);
				//$tot = $p1 + $p2;
				if ($tot <= 0)
					$result = 0;
				else
					$result = 100*$p1/$tot;
				$output[$idItem] = $result;
			}
		}
		return $output;
	}


	public function getScoreData($idScorm, $idUser) {
		$output = array();
		$items = $this->getItems($idScorm, false);

		$query = "SELECT idscorm_item, AVG(score_raw) as average FROM %lms_scorm_tracking WHERE idscorm_item IN (".implode(",", $items).") GROUP BY idscorm_item"
				." ORDER BY idscorm_item ";;
		$res = $this->db->query($query);
		if ($res) {
			while ($row = $this->db->fetch_obj($res)) {
				$output[$row->idscorm_item]['average'] = number_format($row->average, 2);
			}
		}

		$query = "SELECT idscorm_item, score_raw FROM %lms_scorm_tracking WHERE idscorm_item IN (".implode(",", $items).") AND idUser=".(int)$idUser
				." ORDER BY idscorm_item ";;
		$res = $this->db->query($query);
		if ($res) {
			while ($row = $this->db->fetch_obj($res)) {
				$output[$row->idscorm_item]['user'] = number_format($row->score_raw, 2);
			}
		}

		return $output;
	}


	public function getActivityData($idCourse, $idUser, $days = 20) {
		if (!is_numeric($days) || $days<=0) $days = 10;
		$date1 = date("Y-m-d");
		$date2 = date("Y-m-d", strtotime("-".$days." days"));
		$time_condition = " enterTime>'".$date2."' AND enterTime<='".$date1."' ";

		$data = array();
		$query = "SELECT numOp, enterTime FROM %lms_tracksession WHERE idCourse=".(int)$idCourse." AND ".$time_condition;
		$res = $this->db->query($query);
		if ($res) {
			$totals = array();
			while ($row = $this->db->fetch_obj($res)) {
				$date = substr($row->enterTime, 0, 10);
				if (!isset($totals[$date])) $totals[$date] = array('total'=>0, 'count'=>0);
				$totals[$date]['total'] += $row->numOp;
				$totals[$date]['count']++;
			}
			foreach ($totals as $date=>$value) {
				$data[$date]['average'] = $value['total']/$value['count'];
			}
			unset($totals);
		}

		$query = "SELECT numOp, enterTime FROM %lms_tracksession WHERE idCourse=".(int)$idCourse." AND idUser=".(int)$idUser;
		$res = $this->db->query($query);
		if ($res) {
			while ($row = $this->db->fetch_obj($res)) {
				$date = substr($row->enterTime, 0, 10);
				if (!isset($data[$date]['user'])) $data[$date]['user'] = 0;
				$data[$date]['user'] += $row->numOp; //we can have multiple sessions in the same day for the same user
			}
		}

		$output = array();
		for ($i=$days-1; $i>=0; $i--) {
			$day = date("Y-m-d", strtotime("-".$i." days"));
			$output[$day]['average'] = (isset($data[$day]['average']) ? $data[$day]['average'] : 0);
			$output[$day]['user'] = (isset($data[$day]['user']) ? $data[$day]['user'] : 0);
		}

		return $output;
	}


	public function getChapterData($idScorm, $idUser) {
		$output = false;
		$items = $this->getItems($idScorm, false);
		$query = "SELECT idscorm_item, lesson_status "
			."FROM %lms_scorm_tracking WHERE idscorm_item IN (".implode(",", $items).") AND idUser=".(int)$idUser
			." ORDER BY idscorm_item ";
		$res = $this->db->query($query);
		if ($res) {
			$output = array();
			foreach ($items as $item) {
				$output[$item] = false;
			}
			while ($row = $this->db->fetch_obj($res)) {
				//if (!isset($output[$row->idscorm_item])) $output[$row->idscorm_item] = array('passed'=>0, 'not_passed'=>0);
				if ($row->lesson_status == 'passed' || $row->lesson_status == 'completed') {
					$output[$row->idscorm_item] = true;
				}
			}
		}
		return $output;
	}

}

?>