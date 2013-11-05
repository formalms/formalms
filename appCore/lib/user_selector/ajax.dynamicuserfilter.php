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
 * Here we will process the dynamic filter actions ...
 */

require_once($GLOBALS['where_framework'].'/lib/user_selector/lib.dynamicuserfilter.php');
require_once(_base_.'/lib/lib.json.php');
require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
require_once(_base_.'/lib/lib.aclmanager.php');

$db = DbConn::getInstance();


$op = Get::req("op", DOTY_ALPHANUM);


switch($op) {

	case "checkuser": {
    $user_to_check = Get::req('user', DOTY_INT, false);
    $lib = new DynamicUserFilter("dynfilter");
    $output = $lib->chechUser($user_to_check);
    $x = array('response' => ($output ? 'true' : 'false'), '_value'=>$output, '_test' => $_testvar);
		aout( $json->encode($x) );
  } break;

  case "filterusers": {
    $lib = new DynamicUserFilter("dynfilter");
    $temp = $lib->getUsers();
    $output = array('response' => implode(', ', $temp), '_test'=>$_testvar, 'query' => $query );
		if ($output['response'] == "") $output['response'] = "Selezione vuota.";
		aout( $json->encode($output) );
  } break;

  default: {}
  
}
/*
switch($op) {

	case "checkuser": {
		
		$json	= new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$a_obj	= new DoceboACLManager();
		$fman	= new FieldList();
		
		$output		= false;
		$_testvar	= '';
		$user_to_check = Get::req('user', DOTY_INT, false);
		$f_arr = urldecode( Get::req("dynuserfilter", DOTY_MIXED, false) );
		
		if ($user_to_check) {
			
			$user_data_std		= $a_obj->getUser($user_to_check, false);
			$user_data_extra	= $fman->getUserFieldEntryData($user_to_check, false);

			if ($f_arr) {
				$filter = $json->decode(stripslashes($f_arr));
				
				$exclusive = $filter['exclusive'];
				$conds = $filter['filters'];
				$output = $exclusive;
				
				foreach ($conds as $cond) {
					
					$id_field = $cond['id_field'];
					$params = $json->decode($cond['value']);
					if($params == null) $params = $cond['value'];
					$res = $exclusive;
					
					list($id_type, $id) = explode('_', $id_field);

					switch ($id_type) {
						// stadard core_user fields
						case _STANDARD_FIELDS_PREFIX: {
							require_once($GLOBALS['where_framework'].'/modules/field/class.field.php');
							require_once($GLOBALS['where_framework'].'/modules/field/class.date.php');
							
							switch ($id) {
								case 0: { //userid
									$user_data_std[ACL_INFO_USERID] = $a_obj->relativeId($user_data_std[ACL_INFO_USERID]);
									$res = Field::checkUserField($user_data_std[ACL_INFO_USERID], $params);
								} break;

								case 1: { //firstname
									$res = Field::checkUserField($user_data_std[ACL_INFO_FIRSTNAME], $params);
								} break;

								case 2: { //lastname
									$res = Field::checkUserField($user_data_std[ACL_INFO_LASTNAME], $params);
								} break;

								case 3: { //email
									$res = Field::checkUserField($user_data_std[ACL_INFO_EMAIL], $params);
								} break;

								case 4: { //register date
									$res = Field_Date::checkUserField($user_data_std[ACL_INFO_REGISTER_DATE], $params);
								} break;

								default: { $res = false; }
							}
						} break;
						// custom fields -----------------------------------
						case _CUSTOM_FIELDS_PREFIX: {
							//first check if the user own this extra field
							if (isset($user_data_extra[$id])) {
								$fobj = $fman->getFieldInstance($id);
								$res = $fobj->checkUserField($user_data_extra[$id], $params); //check if the field value match the condition
							} else {
								$res = false;
							}
						} break;
						default: { $res = false; }
					}

					if ($exclusive) { //AND of conditions
						if (!$res) { $output = false; break; }
					} else { //OR of conditions
						if ($res) { $output = true; break; }
					}
				}
			}
		}
		$x = array('response' => ($output ? 'true' : 'false'), '_value'=>$output, '_test' => $_testvar);
		aout( $json->encode($x) );
	} break;





	case "filterusers": {

		//retrieve all users matching given conditions

		$output		= array();
		$json		= new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$a_obj		= new DoceboACLManager();
		$fman		= new FieldList();
			
		$user_to_check = Get::req('user', DOTY_INT, false);
		$f_arr = Get::req("dynuserfilter", DOTY_MIXED, false);

		$filter		= $json->decode(stripslashes($f_arr));
		$exclusive	= $filter['exclusive'];
		$conds		= $filter['filters'];

		//compose nested query
		// base query /Anonymous
		$base_query = "SELECT idst, userid "
			." FROM ".$GLOBALS['prefix_fw']."_user ";
		$std_condition	= array();
		$in_conditions	= array();
		foreach ($conds as $cond) {

			$id_field	= $cond['id_field'];
			$params		= $json->decode($cond['value']);
			if($params == null) $params = $cond['value'];
			$res		= $exclusive;
			
			list($id_type, $id) = explode('_', $id_field);

			switch ($id_type) {

				case _STANDARD_FIELDS_PREFIX: {
					require_once($GLOBALS['where_framework'].'/modules/field/class.field.php');
					require_once($GLOBALS['where_framework'].'/modules/field/class.date.php');
					
					switch ($id) {
						case 0: { //userid
							$temp = " userid ";
							switch ($params['cond']) {
								case 0: { $temp .= " = '".$a_obj->absoluteId($params['value'])."' "; } break; //equals
								case 1: { $temp .= " LIKE '%".$params['value']."%' "; } break; //contains
								case 2: { $temp .= " <> '".$a_obj->absoluteId($params['value'])."' "; } break; //not equal
								case 3: { $temp .= " NOT LIKE '%".$params['value']."%' "; } break; //does not contain
								default: { $temp .= " NOT LIKE '%' "; } //unexistent
							}
							$std_condition[] = $temp;
						} break;

						case 1: { //firstname
							$temp = " firstname ";
							switch ($params['cond']) {
								case 0: { $temp .= " = '".$params['value']."' "; } break; //equals
								case 1: { $temp .= " LIKE '%".$params['value']."%' "; } break; //contains
								case 2: { $temp .= " <> '".$params['value']."' "; } break; //not equal
								case 3: { $temp .= " NOT LIKE '%".$params['value']."%' "; } break; //does not contain
								default: { $temp .= " NOT LIKE '%' "; } //unexistent
							}
							$std_condition[] = $temp;
						} break;

						case 2: { //lastname
							$temp = " lastname ";
							switch ($params['cond']) {
								case 0: { $temp .= " = '".$params['value']."' "; } break; //equals
								case 1: { $temp .= " LIKE '%".$params['value']."%' "; } break; //contains
								case 2: { $temp .= " <> '".$params['value']."' "; } break; //not equal
								case 3: { $temp .= " NOT LIKE '%".$params['value']."%' "; } break; //does not contain
								default: { $temp .= " NOT LIKE '%' "; } //unexistent
							}
							$std_condition[] = $temp;
						} break;

						case 3: { //email
							$temp = " email ";
							switch ($params['cond']) {
								case 0: { $temp .= " = '".$params['value']."' "; } break; //equals
								case 1: { $temp .= " LIKE '%".$params['value']."%' "; } break; //contains
								case 2: { $temp .= " <> '".$params['value']."' "; } break; //not equal
								case 3: { $temp .= " NOT LIKE '%".$params['value']."%' "; } break; //does not contain
								default: { $temp .= " NOT LIKE '%' "; } //unexistent
							}
							$std_condition[] = $temp;
						} break;

						case 4: { //register date
							$date = substr(Format::dateDb($params['value'], 'date'), 0, 10);
							$temp = " register_date ";
							switch ($params['cond']) {
								case 0: { $temp .= " < '".$date.".' 00:00:00'' "; } break; //<
								case 1: { $temp .= " <= '".$date.".' 23:59:59'' "; } break; //<=
								case 2: { $temp = " ( register_date >= '".$date." 00:00:00' AND register_date <= '".$date." 23:59:59' ) "; } break; //=
								case 3: { $temp .= " >= '".$date." 00:00:00' "; } break; //>=
								case 4: { $temp .= " > '".$date.".' 23:59:59'' "; } break; //>
								default: { $temp .= " NOT LIKE '%' "; } //unexistent
							}
							$std_condition[] = $temp;
						} break;
						default: {}
					}
				} break;

				case _CUSTOM_FIELDS_PREFIX: {
					
					$fobj = $fman->getFieldInstance($id);
					$in_conditions[] = $fobj->getFieldQuery($params); //check if the field value match the condition
					
				} break;

				default: { }

			} //end switch
			
		} //end foreach

		if ($exclusive) {
			$query = $base_query.' WHERE 1 '
				.( !empty($std_condition)
					? " AND ".implode(" AND ", $std_condition)
					: ''
				)
				.( !empty($in_conditions)
					? ' AND idst IN ( '.implode(" ) AND idst IN ( ", $in_conditions).' ) '
					: '' 
				);
		} else {
			$query = $base_query.' WHERE 0 '
				.( !empty($std_condition)
					? ' OR  ( '.implode(" ) OR idst IN ( ", $std_condition).' ) '
					: '' 
				)
				.( !empty($in_conditions)
					? ' OR idst IN ( '.implode(" ) OR idst IN ( ", $in_conditions).' ) '
					: '' 
				);
		}
		
		//produce output
		$temp = array();
		$re = $db->query($query);
		while ($rw = $db->fetch_assoc($re)) {
			if($rw['userid'] != '/Anonymous') $temp[] = $rw['idst'];
		}
		$output = array('response' => implode(', ', $temp), '_test'=>$_testvar, 'query' => $query );

		if ($output['response'] == "") $output['response'] = "Selezione vuota.";
		aout( $json->encode($output) );
	} break;

	default: {}
}

*/
?>