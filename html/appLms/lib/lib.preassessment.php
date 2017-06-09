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

/**
 * @package DoceoLms
 * @subpackage course management
 * @category library
 * @author Fabio Pirovano
 * @version $Id:$
 * @since 3.5
 *
 * ( editor = Eclipse 3.2.0[phpeclipse,subclipse,WTP], tabwidth = 4 )
 */

define("RULE_ID", 			0);
define("RULE_ID_ASSESS", 	1);
define("RULE_TYPE", 		2);
define("RULE_SETTING", 		3);
define("RULE_EFFECT", 		4);
define("RULE_CASUALTIES", 	5);

define("USER_ASSES_ID", 	0);
define("USER_ASSES_ID_USER", 1);
define("USER_ASSES_TYPE", 	2);

define("USER_ASSES_TYPE_ADMIN", 'admin');
define("USER_ASSES_TYPE_USER", 	'user');

define("RULE_DEFAULT", 	0);
define("RULE_LESSER", 	1);
define("RULE_BETWEEN", 	2);
define("RULE_GREATER", 	3);

/**
 * manage the totality of preassessment
 */
class AssessmentList {

	var $user_field = array(
		USER_ASSES_ID 		=> 'id_assessment',
		USER_ASSES_ID_USER 	=> 'id_user',
		USER_ASSES_TYPE 	=> 'type_of'
	);

	function tableUserAssessment() 	{ return $GLOBALS['prefix_lms'].'_assessment_user'; }

	function courseType() 			{ return 'assessment'; }

	function fetch_row($re) 		{ return sql_fetch_row($re); }

	function fetch_array($re) 		{ return sql_fetch_array($re); }

	function num_rows($re) 			{ return sql_num_rows($re); }

	function _query($query) 		{ $re = sql_query($query); return $re; }

	function AssessmentList() {

		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		$this->man_course = new Man_Course();

		ksort($this->user_field);
		reset($this->user_field);
	}

	/**
	 * return the list of assessment created (note: an assessment is a course)
	 * @return array	array( id_course => int, name => string, code => string )
	 */
	function getAllAssessment() {

		return $this->man_course->getAllCourses(false, $this->courseType());
	}

	function getAssessment($id_assessment) {

		return $this->man_course->getCourseInfo($id_assessment);
	}

	function saveAssessment($id_assessment, $assessment_data) {

		if($id_assessment == 0) {

			// create the course
			$course_info = array(
				'code' 			=> $assessment_data['code'],
				'name' 			=> $assessment_data['name'],
				'description' 	=> $assessment_data['description'],
				'lang_code'		=> getLanguage(),
				'course_type' 	=> $this->courseType(),
				'show_rules' 	=> 2,
				'status' 		=> 2,
				'direct_play' => 1
			);
			$id_course = $this->man_course->addCourse($course_info);
			if($id_course == false) return false;
			$level_idst =& DoceboCourse::createCourseLevel($id_course);
			if($level_idst == false) return false;

			$id_main = $this->man_course->addMainToCourse($id_course, Lang::t('_PREASSESSMENT_MENU', 'preassessment', 'framework'));
			if($id_main == false) return false;

			$re = true;
			$perm = array();
			$perm['7'] = array('view');
			$perm['6'] = array('view');
			$perm['3'] = array('view');
			$re &= $this->man_course->addModuleToCourse($id_course, $level_idst, $id_main, false, 'organization', 'organization', $perm );

			$perm = array();
			$perm['7'] = array('view', 'home', 'lesson', 'public');
			$perm['6'] = array('view', 'home', 'lesson', 'public');
			$re &= $this->man_course->addModuleToCourse($id_course, $level_idst, $id_main, false, 'storage', 'display', $perm);

			$perm = array();
			$perm['7'] = array('view', 'mod');
			$perm['6'] = array('view', 'mod');
			$re &= $this->man_course->addModuleToCourse($id_course, $level_idst, $id_main, false, 'coursereport', 'coursereport', $perm );

			//after creating the assessment course, create directly the test LO
			if ($re) {

				$query = "INSERT INTO %lms_test ( author, title, description ) VALUES "
					."( '".Docebo::user()->getIdSt()."', '".$assessment_data['name']."', '' )";
				if (!sql_query($query)) {
					//...
					return false;
				}

				$id_test = sql_insert_id();
				if ($id_test) {
                    require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
					$odb= new OrgDirDb($id_course);
					$odb->addItem(0, $assessment_data['name'], 'test', $id_test, '0', '0', getLogUserId(), '1.0', '_DIFFICULT_MEDIUM', '', '', '', '', date('Y-m-d H:i:s'));
				} else {
					//...
					return false;
				}
			}

			return $re;
		} else {

			// modify the course
			$course_info = array(
				'code' 			=> $assessment_data['code'],
				'name' 			=> $assessment_data['name'],
				'description' 	=> $assessment_data['description']
			);
			return $this->man_course->saveCourse($id_assessment, $course_info);
		}
	}

	function deleteAssessment($id_assessment) {

		//$rules = new AssessmentRule();

		if(!$this->deleteAssessmentUser($id_assessment)) return false;
		//if(!$rules->deleteRules($id_assessment)) return false;
		if(!$this->man_course->deleteCourse($id_assessment)) return false;
		return true;
	}

	function getAssessmentAdministrator($id_assessment) {

		$users = array();
		$query = "
		SELECT ".implode($this->user_field, ',')."
		FROM ".$this->tableUserAssessment()."
		WHERE ".$this->user_field[USER_ASSES_TYPE]." = '".USER_ASSES_TYPE_ADMIN."'";
		if($id_assessment !== false) $query .= " AND ".$this->user_field[USER_ASSES_ID]." = '".$id_assessment."'";
		$re = $this->_query($query);
		while($row = $this->fetch_row($re)) {

			$users[] = $row[USER_ASSES_ID_USER];
		}
		return $users;
	}

	function getAssessmentUser($id_assessment) {

		$users = array();
		$query = "
		SELECT ".implode($this->user_field, ',')."
		FROM ".$this->tableUserAssessment()."
		WHERE ".$this->user_field[USER_ASSES_TYPE]." = '".USER_ASSES_TYPE_USER."'";
		if($id_assessment !== false) $query .= " AND ".$this->user_field[USER_ASSES_ID]." = '".$id_assessment."'";
		$re = $this->_query($query);
		while($row = $this->fetch_row($re)) {

			$users[] = $row[USER_ASSES_ID_USER];
		}
		return $users;
	}

	/**
	 * add some user to an assessment
	 * @param int 	$id_assessment 	the id of the assessment
	 * @param int 	$user_type 		is the identifier of the type of the user, use the constant USER_ASSES_TYPE_ADMIN, USER_ASSES_TYPE_USER
	 * @param array $user_list 		the list of user that must be assigned to the assesment.
	 */
	function addAssessmentUser($id_assessment, $user_type, $user_list) {

		$op_result = true;
		if(empty($user_list)) return true;

		$query = "
		SELECT ".implode($this->user_field, ',')."
		FROM ".$this->tableUserAssessment()."
		WHERE ".$this->user_field[USER_ASSES_ID]." = '".$id_assessment."'
			AND ".$this->user_field[USER_ASSES_ID_USER]." IN ( ".implode($user_list, ',')." ) ";
		$re_query = $this->_query($query);

		$user_assigned = array();
		while($row = sql_fetch_row($re_query)) {

			$user_assigned[$row[USER_ASSES_ID_USER]] = $row[USER_ASSES_TYPE];
		}
		reset($user_list);
		while(list(, $id_user) = each($user_list)) {

			if(isset($user_assigned[$id_user])) {

				if($user_assigned[$id_user] != $user_type) {

					// modify the user level
					$upd_query = "
					UPDATE ".$this->tableUserAssessment()."
					SET ".$this->user_field[USER_ASSES_TYPE]." = '".$user_type."'
					WHERE ".$this->user_field[USER_ASSES_ID]." = '".$this->user_field[USER_ASSES_TYPE]."'
						AND ".$this->user_field[USER_ASSES_ID_USER]." = '".$id_user."'";
					$op_result &= $this->_query($upd_query);
				}
				// all right
			} else {
				// add the user
				$upd_query = "
				INSERT INTO ".$this->tableUserAssessment()."
				( 	".$this->user_field[USER_ASSES_ID].",
					".$this->user_field[USER_ASSES_ID_USER].",
					".$this->user_field[USER_ASSES_TYPE]." ) VALUES
				( 	'".$id_assessment."',
					'".$id_user."',
					'".$user_type."' )";
				$op_result &= $this->_query($upd_query);
			}
		}
		return $op_result;
	}

	/**
	 * assign a group of user to a preassessment, you can specify if the users are administrator or not
	 * @param int 	$id_assessment 	the id of the assessment
	 * @param int 	$user_type 		is the identifier of the type of the user, use the constant USER_ASSES_TYPE_ADMIN, USER_ASSES_TYPE_USER
	 * @param array $user_list 		the list of user that must be assigned to the assesment.If a user is not in this list, but is actually
	 * 								assigned to the ass. the function will remove the user
	 */
	function updateAssessmentUser($id_assessment, $user_type, $user_list) {
		require_once(_lms_.'/lib/lib.course.php');
		require_once(_lms_.'/admin/models/SubscriptionAlms.php');
		$docebo_course = new DoceboCourse($id_assessment);
		$acl_man = Docebo::user()->getAclManager();
		$subsciption_model = new SubscriptionAlms($id_assessment, 0, 0);

		$level_idst = $docebo_course->getCourseLevel($id_assessment);
		if (count($level_idst) == 0 || $level_idst[1] == '')
			$level_idst = $docebo_course->createCourseLevel($id_assessment);

		$level = 3;
		if($user_type !== 'user')
			$level = 6;
		$op_result = true;
		$query = "
		SELECT ".implode($this->user_field, ',')."
		FROM ".$this->tableUserAssessment()."
		WHERE ".$this->user_field[USER_ASSES_ID]." = '".$id_assessment."'
			AND ".$this->user_field[USER_ASSES_TYPE]." = '".$user_type."'";
		$re_query = $this->_query($query);
		while($row = $this->fetch_row($re_query)) {

			if(isset($user_list[$row[USER_ASSES_ID_USER]]))
			{
				unset($user_list[$row[USER_ASSES_ID_USER]]);
			}
			else
			{
				$del_query = "
				DELETE FROM ".$this->tableUserAssessment()."
				WHERE ".$this->user_field[USER_ASSES_ID]." = '$id_assessment'
					 AND ".$this->user_field[USER_ASSES_ID_USER]." = '".$row[USER_ASSES_ID_USER]."'";
				$op_result &= $this->_query($del_query);

				$acl_man->removeFromGroup($level_idst[$level], $row[USER_ASSES_ID_USER]);
				$subsciption_model->delUser($row[USER_ASSES_ID_USER]);
			}
		} // end while
		reset($user_list);
		while(list(, $id_user) = each($user_list)) {

			$upd_query = "
			INSERT INTO ".$this->tableUserAssessment()."
			( 	".$this->user_field[USER_ASSES_ID].",
				".$this->user_field[USER_ASSES_ID_USER].",
				".$this->user_field[USER_ASSES_TYPE]." ) VALUES
			( 	'".$id_assessment."',
				'".$id_user."',
				'".$user_type."' )";
			$op_result &= $this->_query($upd_query);

			$acl_man->addToGroup($level_idst[$level], $id_user);
			$subsciption_model->subscribeUser($id_user, $level, false);
		}
		return $op_result;
	}

	/**
	 * remove users assigned at the assesment
	 * @param int 	$id_assessment 	the id of the assessment
	 * @param array	$user_list		the list of user to remove
	 */
	function deleteAssessmentUser($id_assessment, $user_list = false) {

		$query = "
		DELETE FROM ".$this->tableUserAssessment()."
		WHERE ".$this->user_field[USER_ASSES_ID]." = '$id_assessment'";
		if($user_list !== false) $query .= " AND ".$this->user_field[USER_ASSES_ID_USER]." IN ( ".implode(',', $user_list)." )";

		return $this->_query($query);
	}

	function getUserAssessmentSubsription($all_user_idst) {

		$assess = array('course_list' => array(), 'level_number' => array());
		if(!is_array($all_user_idst) || count($all_user_idst) == 0) return $assess;

		$query = "
		SELECT ".implode($this->user_field, ',')."
		FROM ".$this->tableUserAssessment()."
		WHERE ".$this->user_field[USER_ASSES_ID_USER]." IN ( ".implode(',', $all_user_idst)." ) ";
		$re = $this->_query($query);
		while($row = $this->fetch_row($re)) {

			$assess['course_list'][$row[USER_ASSES_ID]] = $row[USER_ASSES_ID];
			$new_lvl = ( $row[USER_ASSES_TYPE] == USER_ASSES_TYPE_ADMIN ? 6 : 3 );
			if(!isset($assess['level_number'][$row[USER_ASSES_ID]]) || $assess['level_number'][$row[USER_ASSES_ID]] < $new_lvl) {
				$assess['level_number'][$row[USER_ASSES_ID]] = $new_lvl;
			}
		}
		return $assess;
	}

}

class AssessmentRule {

	var $rules_field = array(
		RULE_ID 		=> 'id_rule',
		RULE_ID_ASSESS 	=> 'id_assessment',
		RULE_TYPE 		=> 'rule_type',
		RULE_SETTING 	=> 'rule_setting',
		RULE_EFFECT 	=> 'rule_effect',
		RULE_CASUALTIES => 'rule_casualities'
	);

	/* function tableAssessmentRules() { return $GLOBALS['prefix_lms'].'_assessment_rules'; } */

	function fetch_row($re) 		{ if($re === false) return false; return sql_fetch_row($re); }

	function fetch_array($re) 		{ if($re === false) return false; return sql_fetch_array($re); }

	function num_rows($re) 			{ if($re === false) return 0; return sql_num_rows($re); }

	function _query($query) 		{ $re = sql_query($query); return $re; }

	/* function AssessmentRule() {

		ksort($this->rules_field);
		reset($this->rules_field);
	}

	function getAllRule($arr_assessment = false) {

		if(empty($arr_assessment)) return false;
		if(($arr_assessment !== false) && !is_array($arr_assessment)) $arr_assessment = array($arr_assessment);

		$query = "
		SELECT ".implode($this->rules_field, ',')."
		FROM ".$this->tableAssessmentRules()."
		WHERE 1 ";
		if($arr_assessment !== false) {
			$query .= " AND ".$this->rules_field[RULE_ID_ASSESS]."
			IN (".implode($arr_assessment, ',').")";
		}
		$query .= " ORDER BY ".$this->rules_field[RULE_TYPE]." ";
		return $this->_query($query);
	}

	function getRule($id_rule) {

		$query = "
		SELECT ".implode($this->rules_field, ',')."
		FROM ".$this->tableAssessmentRules()."
		WHERE ".$this->rules_field[RULE_ID]." = '".$id_rule."'";
		return $this->fetch_row($this->_query($query));
	}

	function resolveRuleTypePhrase(&$lang, $rule) {

		$cont = array();
		$rule_values = $this->parseRuleSetting($rule[RULE_TYPE], $rule[RULE_SETTING]);
		switch($rule[RULE_TYPE]) {
			case RULE_DEFAULT : return $lang->def('_RULE_DEFAULT');break;
			case RULE_GREATER : return str_replace('[score]', $rule_values[0], $lang->def('_RULE_GREATER'));break;
			case RULE_LESSER  : return str_replace('[score]', $rule_values[0], $lang->def('_RULE_LESSER'));break;
			case RULE_BETWEEN : return str_replace(array('[score_1]', '[score_2]'), $rule_values, $lang->def('_RULE_BETWEEN'));break;
		}
		return '';
	}

	function parseRuleSetting($rule_type, $rule) {

		// format example : ";12" "12;23" "23"

		$rule_parsed = array();
		$rule_piece = explode(';', $rule);
		switch($rule_type) {
			case RULE_DEFAULT 	: return array();
			case RULE_GREATER 	: { return array($rule_piece[0]); };break;
			case RULE_LESSER 	: { return array($rule_piece[0]); };break;
			case RULE_BETWEEN 	: { return array($rule_piece[0], $rule_piece[1]); };break;
		}
		return array();
	}

	function compressRule($rule_type, $arr_rules) {

		return implode(';', $arr_rules);
	} */

	function parseEffects($effects_string) {

		$effects = array('course' => array(), 'coursepath' => array());
		$eff_piece = explode('|', $effects_string);
		if(isset($eff_piece[0]) && $eff_piece[0] != '') {
			$temp = explode(';', $eff_piece[0]);
			while(list(, $id) = each($temp)) $effects['course'][$id] = $id;
		}
		if(isset($eff_piece[1]) && $eff_piece[1] != '') {
			$temp = explode(';', $eff_piece[1]);
			while(list(, $id) = each($temp)) $effects['coursepath'][$id] = $id;
		}
		return $effects;
	}

	/* function setEffects($id_rule, $effects_course, $effects_coursepath) {

		$effects = '';
		if(is_array($effects_course)) $effects .= implode(';', $effects_course);
		$effects .= '|';
		if(is_array($effects_coursepath)) $effects .= implode(';', $effects_coursepath);

		$query = "
		UPDATE ".$this->tableAssessmentRules()."
		SET ".$this->rules_field[RULE_EFFECT]." = '".$effects."'
		WHERE ".$this->rules_field[RULE_ID]." = '".$id_rule."'";
		return $this->_query($query);
	} */

	/* function saveRule($id_rule, $rule_data) {

		if($id_rule == 0) {

			$query = "INSERT INTO ".$this->tableAssessmentRules()."
			( 	".$this->rules_field[RULE_ID].",
				".$this->rules_field[RULE_ID_ASSESS].",
				".$this->rules_field[RULE_TYPE].",
				".$this->rules_field[RULE_SETTING].",
				".$this->rules_field[RULE_EFFECT].",
				".$this->rules_field[RULE_CASUALTIES]." ) VALUES
			( 	NULL,
				'".$rule_data['id_assessment']."',
				'".$rule_data['rule_type']."',
				'".$rule_data['rule_setting']."',
				'',
				'0' )";
			if(!$re = $this->_query($query)) return false;
			return sql_insert_id();
		} else {

			$query = "
			UPDATE ".$this->tableAssessmentRules()."
			SET ".$this->rules_field[RULE_ID_ASSESS]		." = '".$rule_data['id_assessment']."'
				,".$this->rules_field[RULE_TYPE]			." = '".$rule_data['rule_type']."'
				,".$this->rules_field[RULE_SETTING]			." = '".$rule_data['rule_setting']."' ";
			if(isset($rule_data['rule_effect']))
				$query .= ",".$this->rules_field[RULE_EFFECT]." = '".$rule_data['rule_effect']."' ";

			if(isset($rule_data['rule_casualties']))
				$query .= ",".$this->rules_field[RULE_CASUALTIES]." = '".$rule_data['rule_casualties']."' ";

			$query .= " WHERE ".$this->rules_field[RULE_ID]." = '".$id_rule."'";
			if(!$re = $this->_query($query)) return false;
			return $id_rule;
		}
	}

	function deleteRule($id_rule) {

		$query = "
		DELETE FROM ".$this->tableAssessmentRules()."
		WHERE ".$this->rules_field[RULE_ID]." = '".$id_rule."'";
		return $this->_query($query);
	}

	function deleteRules($id_assessment, $arr_rules = false) {

		$query = "
		DELETE FROM ".$this->tableAssessmentRules()."
		WHERE ".$this->rules_field[RULE_ID_ASSESS]." = '".$id_assessment."' ";
		if($arr_rules !== false) $query .= " AND ".$this->rules_field[RULE_ID]." IN ( ".implode(',', $arr_rules)." )";
		return $this->_query($query);
	}

	function scoreMatchRule() {

	} */

	function getRelatedEffectForAssessments($arr_assessment) {

		$effects_parsed 	= array('course' => array(), 'coursepath' => array());

		$rules = $this->getAllRule($arr_assessment);
		while($rule = $this->fetch_row($rules))	{

			$effects 	= $this->parseEffects($rule[RULE_EFFECT]);
			$effects_parsed['course'] 	= array_unique(array_merge($effects_parsed['course'], $effects['course']));
			$effects_parsed['coursepath'] = array_unique(array_merge($effects_parsed['coursepath'], $effects['coursepath']));
		}
		return $effects_parsed;
	}

	/**
	 * return a complete list of all the course and coursepath related to a list of assessment and the course and
	 * coursepath that match the user result for a specific assessment recoverd from the assessment rules and effect.
	 * @param array 	$arr_assessment 		an array of assessment
	 * @param array $result_in_assessment 	an array with the user result
	 *
	 * @return array	array( 	'parsed' => array('course' => , 'coursepath' => ),
	 * 							'to_apply' => array('course' => , 'coursepath' => ),
	 * 							'not_done' => array('course' => , 'coursepath' => ) )
	 */
	function getCompleteEffectListForAssessmentWithUserResult($arr_assessment, $result_in_assessment) {

		$effects_parsed 	= array('course' => array(), 'coursepath' => array());
		$effects_to_apply 	= array('course' => array(), 'coursepath' => array());
		$effects_not 		= array('course' => array(), 'coursepath' => array());

		$rule_match = array();
		$rules = $this->getAllRule($arr_assessment);
		while($rule = $this->fetch_row($rules))	{

			$descr 		= $this->parseRuleSetting($rule[RULE_TYPE],$rule[RULE_SETTING]);
			$effects 	= $this->parseEffects($rule[RULE_EFFECT]);

			$effects_parsed['course'] 		= array_unique(array_merge($effects_parsed['course'], $effects['course']));
			$effects_parsed['coursepath'] 	= array_unique(array_merge($effects_parsed['coursepath'], $effects['coursepath']));

			if(isset($result_in_assessment[$rule[RULE_ID_ASSESS]])) {

				$score = $result_in_assessment[$rule[RULE_ID_ASSESS]];
				switch($rule[RULE_TYPE]) {
					case RULE_GREATER : {
						if($descr[0] <= $score) {

							$rule_match[$rule[RULE_ID_ASSESS]] = true;
							$effects_to_apply['course'] 	= array_unique(array_merge($effects_to_apply['course'], $effects['course']));
							$effects_to_apply['coursepath'] = array_unique(array_merge($effects_to_apply['coursepath'], $effects['coursepath']));
						}
					};break;
					case RULE_LESSER : {
						if($score < $descr[0]) {

							$rule_match[$rule[RULE_ID_ASSESS]] = true;
							$effects_to_apply['course'] 	= array_unique(array_merge($effects_to_apply['course'], $effects['course']));
							$effects_to_apply['coursepath'] = array_unique(array_merge($effects_to_apply['coursepath'], $effects['coursepath']));
						}
					};break;
					case RULE_BETWEEN : {
						if($descr[0] <= $score && $score < $descr[1]) {

							$rule_match[$rule[RULE_ID_ASSESS]] = true;
							$effects_to_apply['course'] 	= array_unique(array_merge($effects_to_apply['course'], $effects['course']));
							$effects_to_apply['coursepath'] = array_unique(array_merge($effects_to_apply['coursepath'], $effects['coursepath']));
						}
					};break;
					case RULE_DEFAULT : {
						$default_effects[$rule[RULE_ID_ASSESS]] = $effects;
					};break;

				} // end switch

			} else {

				$effects_not['course'] 		= array_unique(array_merge($effects_not['course'], $effects['course']));
				$effects_not['coursepath'] 	= array_unique(array_merge($effects_not['coursepath'], $effects['coursepath']));
			}

		} // end while
		// no rule matched ----------------------------------------------------------------------
		foreach($arr_assessment as $id_ass) {

			if(isset($result_in_assessment[$id_ass])) {
				// if the assessment is done
				if(isset($default_effects[$id_ass]) && !isset($rule_match[$id_ass])) {
					// but no rules find a match
					$effects_to_apply['course'] 	= array_unique(array_merge($effects_to_apply['course'], $default_effects[$id_ass]['course']));
					$effects_to_apply['coursepath'] = array_unique(array_merge($effects_to_apply['coursepath'], $default_effects[$id_ass]['coursepath']));
				}
			}
		}
		return array('parsed' => $effects_parsed, 'to_apply' => $effects_to_apply, 'not_done' => $effects_not);
	}

	function getEffectForScore($id_assessment, $score) {

		$rule_match 		= false;
		$default_effects 	= false;
		$effects_to_apply 	= array('course' => array(), 'coursepath' => array());

		$rules = $this->getAllRule($id_assessment);
		while($rule = $this->fetch_row($rules))	{

			$descr 		= $this->parseRuleSetting($rule[RULE_TYPE],$rule[RULE_SETTING]);
			$effects 	= $this->parseEffects($rule[RULE_EFFECT]);

			switch($rule[RULE_TYPE]) {
				case RULE_GREATER : {
					if($descr[0] <= $score) {

						$rule_match = true;
						$effects_to_apply['course'] 	= array_unique(array_merge($effects_to_apply['course'], $effects['course']));
						$effects_to_apply['coursepath'] = array_unique(array_merge($effects_to_apply['coursepath'], $effects['coursepath']));
					}
				};break;
				case RULE_LESSER : {
					if($score < $descr[0]) {

						$rule_match = true;
						$effects_to_apply['course'] 	= array_unique(array_merge($effects_to_apply['course'], $effects['course']));
						$effects_to_apply['coursepath'] = array_unique(array_merge($effects_to_apply['coursepath'], $effects['coursepath']));
					}
				};break;
				case RULE_BETWEEN : {
					if($descr[0] <= $score && $score < $descr[1]) {

						$rule_match = true;
						$effects_to_apply['course'] 	= array_unique(array_merge($effects_to_apply['course'], $effects['course']));
						$effects_to_apply['coursepath'] = array_unique(array_merge($effects_to_apply['coursepath'], $effects['coursepath']));
					}
				};break;
				case RULE_DEFAULT : {
					$default_effects = $effects;
				};break;
			}
		}
		// no rule matched
		if($default_effects !== false && $rule_match === false) { $effects_to_apply = $default_effects; }
		return $effects_to_apply;
	}

} // end class AssessmentRule

?>