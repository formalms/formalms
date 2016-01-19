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

class EnrollrulesAlms extends Model {

	protected $sortable = array('title', 'lang_code', 'creation_date');

	protected $db = false;
	protected $json = false;

	public function  __construct() {
		parent::__construct();
		$this->json = new Services_JSON();
		$this->db = DbConn::getInstance();
	}

	public function getPerm() {
		return array('view' => 'standard/view.png');
	}

	/**
	 * Return the list of rule types with the translations
	 * @return array (keys -> type code, value -> code translation)
	 */
	public function ruleTypes() {
		$types = array(
			'base' 		=> Lang::t('_BASE', 'enrollrules'),
			'group'		=> Lang::t('_GROUPS', 'enrollrules'),
			'orgchart' 	=> Lang::t('_ORGCHART', 'enrollrules'),
			'fncrole' 	=> Lang::t('_FUNCTIONAL_ROLE', 'enrollrules')
		);
		return $types;
	}

	/**
	 * Return a list of rules
	 * @param int $start_index start from this record
	 * @param int $results return only this number of result
	 * @param string $sort sort order
	 * @param string $dir sort direction
	 * @return array array of rule object
	 */
	public function getRules($start_index, $results, $sort, $dir) {

		if(!isset($this->sortable[$sort])) $sort = 'title';
		
		$query = "SELECT id_rule, title, lang_code, rule_type, creation_date, rule_active"
			." FROM %adm_rules"
			." WHERE rule_type <> 'base' OR (rule_type = 'base' AND lang_code = 'all' ) "
			." ORDER BY ".$sort." ".$dir
			." LIMIT ".$start_index.", ".$results;
		$result = $this->db->query($query);
		
		$res = array();
		while($rule = $this->db->fetch_obj($result)) {
			if($rule->lang_code == 'all') $rule->lang_code = Lang::t('_ALL', 'enrollrules');
			if($rule->id_rule == 0) {
				$rule->title = Lang::t('_NEW_USER', 'enrollrules');
				$rule->creation_date = '';
				$rule->lang_code = '';
			}
			$res[] = $rule;
		}
		return $res;
	}

	public function getTotalRulesCount() {

		$query = "SELECT COUNT(*)"
			." FROM %adm_rules"
			." WHERE rule_type <> 'base' OR (rule_type = 'base' AND lang_code = 'all' )";
		$result = $this->db->query($query);
		list($tot_rule) = $this->db->fetch_row($result);
		return $tot_rule;
	}

	public function getRule($id_rule) {

		$query = "SELECT id_rule, title, lang_code, rule_type, creation_date, rule_active, course_list"
			." FROM %adm_rules"
			." WHERE id_rule = ".(int)$id_rule;
		$result = $this->db->query($query);

		return $this->db->fetch_obj($result);
	}

	public function changeActivationState($id_rule) {
		
		$rule = $this->getRule($id_rule);
		$query = " UPDATE %adm_rules"
			." SET rule_active = ".( $rule->rule_active ? 0 : 1 )." "
			." WHERE id_rule = ".(int)$id_rule;
		return $this->db->query($query);
	}

	public function createRule($data) {

		$languages = Docebo::langManager()->getAllLangCode();
		array_unshift($languages, 'all');
		
		$query = "INSERT INTO %adm_rules ( title, lang_code, rule_type, creation_date, rule_active )"
			." VALUES "
			." ( '".$data['title']."', '".$languages[$data['lang_code']]."', '".$data['rule_type']."', '".date('Y-m-d H:i:s')."', 1 ) ";
		return $this->db->query($query);
	}

	public function updateRule($data) {

		$languages = Docebo::langManager()->getAllLangCode();
		array_unshift($languages, 'all');
		
		$query = "UPDATE %adm_rules SET "
			." title = '".$data['title']."', "
			." lang_code = '".$languages[$data['lang_code']]."' "
			." WHERE id_rule = ".(int)$data['id_rule'];
		return $this->db->query($query);
	}

	public function updateRuleCourseSelection($id_rule, $data_selection) {

		$jselection = addslashes( $this->json->encode($data_selection) );
		$query = "UPDATE %adm_rules SET "
			." course_list = '".$jselection."' "
			." WHERE id_rule = ".(int)$id_rule;
		$re = $this->db->query($query);
		// we also need to remove the removed course from the rules
		$entityr = $this->getEntityRule($id_rule);
		while(list($id_e, $ent) = each($entityr)) {
			
			$surplus = array_diff($ent->course_list, $data_selection);
			if(!empty($surplus)) {
				$to_save = array_diff($ent->course_list, $surplus);
				$this->saveEntityRule($id_rule, $ent->id_entity, $to_save);
			}
		}
		return $re;
	}

	public function deleteRule($id_rule) {

		$query = "DELETE FROM %adm_rules WHERE id_rule = ".(int)$id_rule;
		return $this->db->query($query);
	}

	public function getEntityRule($id_rule, $id_entities = false) {
		
		$query = "SELECT id_rule, id_entity, course_list "
			."FROM %adm_rules_entity "
			."WHERE id_rule = ".(int)$id_rule."";
		if($id_entities != false) {

			$query .= " AND id_entity IN ( '".implode("','", $id_entities)."' ) ";
		}
		$result = $this->db->query($query);
		
		$res = array();
		while($entity = $this->db->fetch_obj($result)) {

			$entity->course_list = $this->json->decode($entity->course_list);
			$res[$entity->id_entity] = $entity;
		}
		return $res;
	}

	public function getBaseEntityRule($id_rule, $id_entities = false, $only_existing = false) {
		
		$entities = Docebo::langManager()->getAllLangCode();
		array_unshift($entities, 'all');

		$entities_name = array();
		foreach($entities as $i => $en) {
			$ename = new stdClass();
			$ename->id_entity	= $en;
			$ename->title		= $en;
			$ename->course_list = array();

			$entities_name[$ename->id_entity] = $ename;
		}
		if($only_existing) $entities_name = array();

		$query = "SELECT id_rule, id_entity, course_list "
			."FROM %adm_rules_entity "
			."WHERE id_rule = ".(int)$id_rule."";
		if($id_entities != false) {

			$query .= " AND id_entity IN ( '".implode("','", $id_entities)."' ) ";
		}
		$result = $this->db->query($query);
		
		while($entity = $this->db->fetch_obj($result)) {

			//$entity->course_list = $this->json->decode($entity->course_list);
			$entities_name[$entity->id_entity]->course_list = $this->json->decode($entity->course_list);
		}
		return $entities_name;
	}

	public function convertEntity($id_entities, $rule_type) {

		$entities_name = array();
		if(empty($id_entities) && $rule_type != 'base') return $entities_name;
		
		switch($rule_type) {
			case "base" : {
				
				foreach($id_entities as $i => $ename) {
					if(isset($ename->id_entity)) $entities_name[$ename->id_entity] = $ename->title;
				}
			};break;
			case "orgchart" : {
				$us_man = new UsermanagementAdm();
				$folders = $us_man->getInfoFolders($id_entities);
				$entities_name = $folders['idst'];
			};break;
			case "group" : {
				$aclman = Docebo::aclm();
				$names = $aclman->getGroups($id_entities);
				if($names)
				foreach($names as $group)  {
					$entities_name[$group[ACL_INFO_IDST]] = $aclman->relativeId( $group[ACL_INFO_GROUPID] );
				}
			};break;
			case "fncrole" : {
				$fmodel = new FunctionalrolesAdm();
				$entities_name = $fmodel->getFunctionalRolesNames($id_entities);
			};break;
		}
		return $entities_name;
	}
	
	public function getCourseRuleElaborated($id_rule) {

		$query = "SELECT id_rule, id_entity, course_list "
			."FROM %adm_rules_entity "
			."WHERE id_rule = ".(int)$id_rule."";
		$result = $this->db->query($query);
		$res = array(
			'entities' => array(),
			'entitity_rule' => array()
		);
		while($entity = $this->db->fetch_obj($result)) {

			$entity->course_list = $this->json->decode($entity->course_list);
			$res['entities'][] = $entity->id_entity;
			$res['entitity_rule'][] = $entity;
		}
		return $res;
	}
	
	public function insertEntityRule($id_rule, $id_entity, $course_list) {

		$json_list = addslashes($this->json->encode($course_list));

		$query = "INSERT INTO %adm_rules_entity ( id_rule, id_entity, course_list ) "
			." VALUES ( ".(int)$id_rule.", '".$id_entity."', '".$json_list."' )";
		return $this->db->query($query);
	}
	
	public function saveEntityRule($id_rule, $id_entity, $course_list) {

		$json_list = addslashes($this->json->encode($course_list));

		$query = "UPDATE %adm_rules_entity "
			."SET course_list = '".$json_list."' "
			."WHERE id_rule = ".(int)$id_rule." AND id_entity = '".$id_entity."' ";
		return $this->db->query($query);
	}

	public function deleteEntityRule($id_rule, $id_entity) {

		
		$query = "DELETE FROM %adm_rules_entity "
			."WHERE id_rule = ".(int)$id_rule." AND id_entity = '".$id_entity."' ";
		return $this->db->query($query);
	}

	/**
	 * Return the list of courses applicable for an entity
	 * @param array $id_entities
	 * @return array all the id_course applicable for this entity
	 */
	public function getApplicableRuleForEntity($id_entities, $language = false) {

		$rules = array();
		$course_list = array();
		if(!$language) $language = getLanguage();
		$query = "SELECT re.id_rule, re.course_list "
			."FROM %adm_rules AS r JOIN %adm_rules_entity AS re "
			." ON (r.id_rule = re.id_rule) "
			."WHERE r.rule_type <> 'base' "
			." AND ( r.lang_code = 'all' OR r.lang_code = '".$language."' )"
			." AND id_entity IN ( '".implode("','", $id_entities)."' ) ";
		$result = $this->db->query($query);
		while($entity = $this->db->fetch_obj($result)) {

			$rules[] = $entity->id_rule;
			$entity_course_list = $this->json->decode($entity->course_list);
			$course_list = array_merge($course_list, $entity_course_list);
		}
		return array(
			'rules' => $rules,
			'course_list' => $course_list
		);
	}

	public function getApplicableRuleForEntityMultiLang($id_entities) {

		$rules = array();
		$course_list = array();
		$query = "SELECT re.id_rule, re.id_entity, r.lang_code, re.course_list "
			."FROM %adm_rules AS r JOIN %adm_rules_entity AS re "
			." ON (r.id_rule = re.id_rule) "
			."WHERE r.rule_type <> 'base' "
			." AND id_entity IN ( '".implode("','", $id_entities)."' ) ";
		$result = $this->db->query($query);
		while($entity = $this->db->fetch_obj($result)) {

			$rules[] = $entity->id_rule.'_'.$entity->id_entity;
			$entity_course_list = $this->json->decode($entity->course_list);
			if(!isset($course_list[$entity->lang_code])) $course_list[$entity->lang_code] = array();
			$course_list[$entity->lang_code] = array_merge($course_list[$entity->lang_code], $entity_course_list);
		}

		return array(
			'rules' => $rules,
			'course_list' => $course_list
		);
	}

	/**
	 * Return the list of courses applicable for a new user
	 * @param array $id_entities
	 * @return array all the id_course applicable for this entity
	 */
	public function getApplicableRuleForNewUser($language = false) {

		$rules = array();
		$course_list = array();
		if(!$language) $language = getLanguage();
		$query = "SELECT re.id_rule, re.id_entity, re.course_list "
			."FROM %adm_rules AS r JOIN %adm_rules_entity AS re "
			." ON (r.id_rule = re.id_rule) "
			."WHERE r.rule_type = 'base' "
			." AND ( re.id_entity = 'all' OR re.id_entity = '".$language."' ) ";
		$result = $this->db->query($query);
		while($entity = $this->db->fetch_obj($result)) {

			$rules[] = $entity->id_rule.'_'.$entity->id_entity;
			$entity_course_list = $this->json->decode($entity->course_list);
			$course_list = array_merge($course_list, $entity_course_list);
		}
		return array(
			'rules' => $rules,
			'course_list' => $course_list
		);
	}

	/**
	 * Resolve the rules application for a new user
	 * @param string $log_action a brief description about the action that have snap the rules
	 * @param array $arr_users a list of idst to use
	 * @param int $id_org an idOrg of the org chart, it will be resolved for ancestor
	 * @param array $entities an array of entities that must be checked in order to find more triggerable rules
	 * @return boolean
	 */
	public function newRules($log_action, $arr_user, $language = false, $id_org = false, $entities = false) {

		$re = true;
		$ent = array();

		$applicable = $this->getApplicableRuleForNewUser($language);
		return $this->applyRules($log_action, $arr_user, $language, $id_org, $entities, $applicable);
	}

	/**
	 * Resolve the rules application for a list of users
	 * @param string $log_action a brief description about the action that have snap the rules
	 * @param array $arr_users a list of idst to use
	 * @param int $id_org an idOrg of the org chart, it will be resolved for ancestor
	 * @param array $entities an array of entities that must be checked in order to find more triggerable rules
	 * @param array $more_applicable an array of course that must be used and rules alredy triggered
	 * @return boolean
	 */
	public function applyRules($log_action, $arr_user, $language = false, $id_org = false, $entities = false, $more_applicable = false) {

		$re = true;
		$ent = array();
		
		if($id_org != 0 ) {

			$acl_manager = Docebo::aclm();
			$oc_sn = $acl_manager->getGroupST('oc_'.$id_org);

			$um_adm = new UsermanagementAdm();
			$ancestor = $um_adm->getAncestorInfoFolders($id_org);
			
			$ancestor['idst_ocd'][] = $oc_sn;
			$ent = $ancestor['idst_ocd'];
		}
		if($entities != false) $ent = $ent + $entities;

		$course_list = $more_applicable['course_list'];
		$rules = $more_applicable['rules'];
		if(!empty($ent)) {
			$applicable = $this->getApplicableRuleForEntity($ent, $language);
			if(!empty($applicable['course_list'])) $course_list = array_merge($course_list, $applicable['course_list']);
			if(!empty($applicable['rules'])) $rules = array_merge($rules, $applicable['rules']);
		}
		// do the subscription
		if(empty($course_list)) return $re;

		require_once(_lms_.'/lib/lib.subscribe.php');
		$cs = new CourseSubscribe_Management();
		$course_list = array_unique($course_list);

		$this->db->start_transaction();

		// create a entry log
		$id_log = $this->addLogEntry( $log_action, $rules );

		if(!$id_log) return false;

		$re = $cs->multipleSubscribe($arr_user, $course_list, 3, $id_log);
		$this->db->commit();

		return $re;
	}

	/**
	 * Resolve the rules application for a list of users
	 * @param string $log_action a brief description about the action that have snap the rules
	 * @param array $arr_users a list of idst to use
	 * @param int $id_org an idOrg of the org chart, it will be resolved for ancestor
	 * @param int $id_entity a generic entity, will not be resolved
	 * @return bool
	 */
	// FORMA: Added param user_temp to use the user_temp table when the user is not confirmed (double optin)
	public function applyRulesMultiLang($log_action, $arr_users, $id_org, $id_entity = false, $user_temp = false) {

		$ent = array();
		if($id_org != false) {

			$acl_man = Docebo::aclm();
			$oc_sn = $acl_man->getGroupST('oc_'.$id_org);

			$um_adm = new UsermanagementAdm();
			$ancestor = $um_adm->getAncestorInfoFolders($id_org);

			$ancestor['idst_ocd'][] = $oc_sn;
			$ent = $ancestor['idst_ocd'];
		}
		if($id_entity != false) $ent[] = $id_entity;
		if(empty($ent)) return false;

		// FORMA: if we have the user_temp param we have to use user_temp table
		$user_table = "%adm_user";
		if ($user_temp)
			$user_table = "%adm_user_temp";
                        
		$query = "SELECT DISTINCT u.idst, us.value "
			." FROM ".$user_table." AS u "
			." LEFT JOIN %adm_setting_user AS us "
			." ON ( u.idst = us.id_user AND us.path_name = 'ui.language' ) "
			." WHERE u.idst IN ( ".implode(",", $arr_users)." )";
		// END FORMA
		
		$re_query = $this->db->query($query);

		if(!$re_query) return false;
		
		$arr_users = array();
		$langs = array();
		$default_lang = getDefaultLanguage();
		while(list($idst_user, $value) = $this->db->fetch_row($re_query)) {

			if($value == '') $value = $default_lang;
			$langs[$value] = $value;
			$arr_users[$value][] = $idst_user;
		}
		
		// find rules for evry language in the array
		require_once(_lms_.'/lib/lib.subscribe.php');
		$cs = new CourseSubscribe_Management();
		$applicable = $this->getApplicableRuleForEntityMultiLang($ent);
		$course_list = $applicable['course_list'];
		if(!isset($course_list['all'])) $course_list['all'] = array();

		$this->db->start_transaction();
		// create a entry log
		$id_log = $this->addLogEntry( $log_action, $applicable['rules'] );
		if(!$id_log) return false;
		
		foreach($langs as $i => $lang_code) {
			
			if(!empty($course_list[$lang_code])) $courses = array_unique( array_merge($course_list['all'], $course_list[$lang_code]) );
			else $courses = array_unique( $course_list['all'] );
			if(!empty($arr_users[$lang_code]) && !empty($courses)) $re = $cs->multipleSubscribe($arr_users[$lang_code], $courses, 3, $id_log);
		}
		$this->db->commit();
		
		return $re;
	}

	public function getLogs($start_index, $results, $sort, $dir) {

		$sort = $this->clean_sort($sort, array('log_time'));
		$dir = $this->clean_dir($dir);
		
		$query = "SELECT id_log, log_action, log_time, applied "
			."FROM %adm_rules_log "
			."WHERE 1 "
			."ORDER BY ".$sort." ".$dir." "
			."LIMIT ".$start_index.", ".$results;
		$result = $this->db->query($query);

		$res = array();
		while($log = $this->db->fetch_obj($result)) {
			
			$res[] = $log;
		}
		return $res;
	}

	public function getTotalLogs() {

		$query = "SELECT COUNT(*) "
			." FROM %adm_rules_log "
			." WHERE 1";
		$result = $this->db->query($query);
		list($tot_logs) = $this->db->fetch_row($result);
		return $tot_logs;
	}
	
	/**
	 * Create a new log entry
	 * @param string $log_action the key of the action that have triggered the actions
	 * @param array $rules  a list of id_rules
	 * @return int the id of the log entry created
	 */
	public function addLogEntry($log_action, $rules) {

		$actions = addslashes($this->json->encode($rules));
		$query = "INSERT INTO %adm_rules_log (id_log, log_action, log_time, applied) VALUES ("
			." NULL, "
			."'".$log_action."', "
			."'".date("Y-m-d H:i:s")."', "
			."'".$actions."' "
			.")";
		if(!$this->db->query($query)) return false;
		
		return $this->db->insert_id();
	}
	
	public function logInfo($id_log) {

		require_once(_lms_.'/lib/lib.subscribe.php');
		$cs = new CourseSubscribe_Management();

		// remove all the subscription
		return $cs->retriveLogSubscriptionInfo($id_log);
	}

	public function rollbackLog($id_log) {

		require_once(_lms_.'/lib/lib.subscribe.php');
		$cs = new CourseSubscribe_Management();

		// remove all the subscription
		$cs->removeRuleLogSubscription($id_log);
		
		// remove the log
		$query = "DELETE FROM %adm_rules_log WHERE id_log = '".$id_log."'";
		if(!$this->db->query($query)) return false;
		return true;
	}

}
