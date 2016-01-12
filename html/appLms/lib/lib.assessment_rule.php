<?php

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

Class AssessmentRuleManager {

	var $test_id =0;
	protected $db;

	
	function  __construct($test_id) {
		$this->test_id =(int)$test_id;
		$this->db =DbConn::getInstance();
	}


	public function loadJs() {
		Util::get_js(Get::rel_path('lms').'/modules/test/assessment_rule.js', true, true);
	}


	public function getRules() {
		$res =array();

		$fields ="*";
		$qtxt ="SELECT ".$fields." FROM %lms_assessment_rule
			WHERE test_id='".$this->test_id."'
			ORDER BY category_id, from_score ASC";

		$q =$this->db->query($qtxt);

		while($row=$this->db->fetch_assoc($q)) {
			$cat_id =$row['category_id'];
			$res[$cat_id][]=$row;
		}

		return $res;
	}


	public function getAddEditForm($form_url, $data) {
		$this->loadJs();
		$res ='';

		$e =false;
		if ($data['rule_id'] > 0) {
			$e =true;
		}

		$res.=Form::openForm('main_form', $form_url);
		$res.=Form::openElementSpace()
			.Form::getTextfield(Lang::t('_FROM', 'test'), 'from_score', 'from_score', 11, ($e ? $data['from_score'] : ''))
			.Form::getTextfield(Lang::t('_TO', 'test'), 'to_score', 'to_score', 11, ($e ? $data['to_score'] : '') )

			.'<br />'

			.'<p class="section_title">'.Lang::t('_COMPETENCES', 'test').'</p>'
			.'<div id="competence_box"></div>'
			.'<div class="form_line_l">
					<p><label for="input_add_competence" class="floating">'.Lang::t('_NAME', 'test').'</label></p>
					<div class="form_autocomplete_container">
					<input type="text" maxlength="255" value="" name="input_add_competence" id="input_add_competence" class="textfield">
					<a href="" id="link_add_competence"><img alt="'.Lang::t('_ADD', 'test').
					'" src="'.getPathImage().'standard/add.png" class="valing-middle"></a>
					<div id="box_autocomplete_competence"></div>
					</div>
				</div>'

			.'<br />'

			.'<p class="section_title">'.Lang::t('_COURSES', 'test').'</p>'
			.'<div id="course_box"></div>'
			.'<div class="form_line_l">
					<p><label for="input_add_course" class="floating">'.Lang::t('_NEW_COURSE', 'test').'</label></p>
					<div class="form_autocomplete_container">
					<input type="text" maxlength="255" value="" name="input_add_course" id="input_add_course" class="textfield">
					<a href="" id="link_add_course"><img alt="'.Lang::t('_ADD', 'test').
					'" src="'.getPathImage().'standard/add.png" class="valing-middle"></a>
					<div id="box_autocomplete_course"></div>
					</div>
				</div>'

			.'<br /><br />'

			//.'<p class="section_title">'.Lang::t('_FEEDBACK_TEXT', 'test').'</p>'
			.Form::getTextarea(Lang::t('_FEEDBACK_TEXT', 'test'), 'feedback_txt', 'feedback_txt', ($e ? $data['feedback_txt'] : ''))

			.Form::getHidden('rule_id', 'rule_id', (int)$data['rule_id'])
			.Form::getHidden('competences_list', 'competences_list', '')
			.Form::getHidden('courses_list', 'courses_list', '')
			.Form::getHidden('test_id', 'test_id', (int)$data['test_id'])
			.Form::getHidden('category_id', 'category_id', (int)$data['category_id'])
			.Form::getHidden('save', 'save', '1')
			.Form::closeElementSpace()

			.Form::openButtonSpace()
			.Form::getButton('btn_save', 'btn_save', Lang::t('_SAVE', 'test') )
			.Form::getButton('btn_cancel', 'btn_cancel', Lang::t('_UNDO', 'test') )
			.Form::closeButtonSpace();

		$res.=Form::closeForm();


		$res.='<script type="text/javascript">
			var course_ac_url="'.Get::rel_path('adm')
			.'/ajax.adm_server.php?mn=course&plf=lms&op=course_autocomplete&results=20'.'";
			var competence_ac_url="'.Get::rel_path('adm')
			.'/ajax.adm_server.php?r=adm/competences/competences_autocomplete'.'";
			var lang={"remove_item": "'.Lang::t('_DEL', 'test').'"};
			var course_arr='.($e && !empty($data['courses_list']) ? $data['courses_list'] : '{}').';
			var competence_arr='.($e && !empty($data['competences_list']) ? $data['competences_list'] : '{}').';
			</script>';

		return $res;
	}


	public function save() {

		// TODO: check that user can access to the test object

		$rule_id =Get::pReq('rule_id', DOTY_INT, 0);
		$test_id =Get::pReq('test_id', DOTY_INT, 0);
		$category_id =Get::pReq('category_id', DOTY_INT, 0);
		$from_score =Get::pReq('from_score', DOTY_INT, 0);
		$to_score =Get::pReq('to_score', DOTY_INT, 0);
		$competences_list =Get::pReq('competences_list', DOTY_STRING, '');
		$courses_list =Get::pReq('courses_list', DOTY_STRING, '');
		$feedback_txt =Get::pReq('feedback_txt', DOTY_MIXED, '');

		if ($rule_id <= 0) {
			$qtxt ="INSERT INTO %lms_assessment_rule
				(test_id, category_id, from_score, to_score,
				competences_list, courses_list, feedback_txt)
				VALUES ('".$test_id."', '".$category_id."',
					'".$from_score."', '".$to_score."', '".$competences_list."',
					'".$courses_list."', '".$feedback_txt."')";
		}
		else {
			$qtxt ="UPDATE %lms_assessment_rule SET
				test_id='".$test_id."', category_id='".$category_id."',
				from_score='".$from_score."', to_score='".$to_score."',
				competences_list='".$competences_list."',
				courses_list='".$courses_list."', feedback_txt='".$feedback_txt."'
				WHERE rule_id='".$rule_id."' LIMIT 1";
		}

		
		$q =$this->db->query($qtxt);

		return $q;
	}


	public function delete($rule_id) {

		$qtxt ="DELETE FROM %lms_assessment_rule
				WHERE rule_id='".(int)$rule_id."' LIMIT 1";

		$q =$this->db->query($qtxt);

		return $q;
	}
	

	public function getRuleInfo($rule_id) {
		$res =array();

		$qtxt ="SELECT * FROM %lms_assessment_rule
			WHERE rule_id='".(int)$rule_id."' LIMIT 0,1";

		$q =$this->db->query($qtxt);

		if ($q) {
			$res =$this->db->fetch_assoc($q);
		}
		else {
			$res =false;
		}

		return $res;
	}


	public function setRulesFromScore($score_arr) {
		require_once(_base_.'/lib/lib.json.php');
		require_once(_lms_.'/lib/lib.subscribe.php');

		$res =true;

		$where_score_arr =array();
		foreach($score_arr as $val) {
			$where_score_arr[]="(category_id = '".(int)$val['category_id']."' ".
				"AND from_score <= '".(int)$val['score']."' AND to_score >= '".(int)$val['score']."')";
		}
		if (empty($where_score_arr)) {
			return '';
		}

		$fields ="*";
		$qtxt ="SELECT ".$fields." FROM %lms_assessment_rule
			WHERE test_id='".$this->test_id."'
			AND (".implode(' OR ', $where_score_arr).")
			ORDER BY from_score ASC";

		$q =$this->db->query($qtxt);

		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$csm = new CourseSubscribe_Management();
		$cmpman = new CompetencesAdm();

		$feedback_txt = array();
		while($row=$this->db->fetch_assoc($q)) {
			$course_arr = $json->decode($row['courses_list']);
			$competence_arr = $json->decode($row['competences_list']);

			$feedback_txt[] = $row['feedback_txt'];

			//courses subscriptions - only students are affected
			if (!empty($course_arr) && $_SESSION['levelCourse'] <= 3) {
				$arr_courses =array_keys($course_arr);
				$csm->multipleUserSubscribe(getLogUserId(), $arr_courses, 3);
			}

			//competences assignment - only students are affected
			if (!empty($competence_arr) && $_SESSION['levelCourse'] <= 3) {
				foreach($competence_arr as $c_id=>$data) {
					if ($data['type'] == 'score') {
						$score =(isset($data['score']) ? $data['score'] : 0);
						if ($score > 0) {
							if (!$cmpman->userHasCompetence($c_id, Docebo::user()->getIdst())) {
								$cmpman->assignCompetenceUsers($c_id, array(Docebo::user()->getIdst()=>$score));
							}
							else {
								$cmpman->addScoreToUsers($c_id, array(Docebo::user()->getIdst()), $score);
							}
						}
					}
					else {
						if (!$cmpman->userHasCompetence($c_id, Docebo::user()->getIdst())) {
							$cmpman->assignCompetenceUsers($c_id, array(Docebo::user()->getIdst()=>1));
						}
					}
				}
			}

		}

		$output = "";
		if (!empty($feedback_txt)) $output = implode('<br/><br />', $feedback_txt);
		return $output;
	}


}