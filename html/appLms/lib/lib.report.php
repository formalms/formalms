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

function load_categories() {
  $res = sql_query("SELECT * FROM %lms_report WHERE enabled=1");
  $GLOBALS['report_categories'] = array();
  while ($row = sql_fetch_assoc($res)) {
    $GLOBALS['report_categories'][ $row['id_report'] ] = $row['report_name'];
  }
}


function report_save($report_id, $filter_name, &$filter_data, $is_public = false) {
	$data = addslashes( serialize($filter_data) ); //put serialized data in DB
	$query = "INSERT INTO %lms_report_filter ".
		"(id_report, author, creation_date, filter_data, filter_name, is_public) VALUES ".
		"($report_id, ".Docebo::user()->getIdst().", NOW(), '$data', '$filter_name', ".($is_public ? "1" : "0").")";
		
	if (!sql_query($query)) {
		return false;
	} else {
		$row = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		return $row[0];
	}
}

function report_update($report_id, $filter_name, &$filter_data, $is_public = false) {
	//if $is_public is false, than do not change it in the update query
	$public_value = ($is_public !== false
		? (int)$is_public > 0 ? '1' : '0'
		: false);
	$data = addslashes( serialize($filter_data) ); //put serialized data in DB
	$query = "UPDATE %lms_report_filter SET ".
		"creation_date=NOW(), filter_data='$data', filter_name='$filter_name'".
		($public_value !== false ? ", is_public=".$public_value : "")." ".
		"WHERE id_filter=$report_id";

	return sql_query($query);
}

function report_save_schedulation($id_rep, $name, $period, $time, &$recipients) {
	//TO DO : try to use transation for this
	$query = "INSERT INTO %lms_report_schedule ".
		"(id_report_filter, id_creator, name, period, time, creation_date) VALUES ".
		"($id_rep, ".Docebo::user()->getIdst().",'".trim($name)."', '$period', '$time', NOW())";
	
	if (!sql_query($query)) {
		return false;
	} else {
		$row = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		$id_sched = $row[0];
	}
	
	$temp = array();
	foreach ($recipients as $value) {
		$temp[] = '('.$id_sched.', '.$value.')';
	}
	
	//TO DO : handle void recipients case
	$query = "INSERT INTO %lms_report_schedule_recipient ".
		"(id_report_schedule, id_user) VALUES ".implode(',', $temp);
		
	if (!sql_query($query))
		return false;
	else
		return $id_sched;
}


function getReportNameById($id) {
	$qry = "SELECT filter_name, author FROM %lms_report_filter WHERE id_filter=$id";
	$row = sql_fetch_row( sql_query($qry) );
	
	if($row[1])
		return $row[0];
	else
	{
		$lang =& DoceboLanguage::createInstance('report', 'framework');
		return $lang->def($row[0]);
	}
}

function getScheduleNameById($id) {
	$qry = "SELECT name FROM %lms_report_schedule WHERE id_report_schedule=$id";
	$row = sql_fetch_row( sql_query($qry) );	
	return $row[0];
}

function report_delete_filter($id_filter) {
	$qry = "DELETE FROM %lms_report_filter WHERE id_filter=$id_filter";
	$output = sql_query($qry);
	if ($output) {
		//delete schedulations connected to this filter
		$qry = "SELECT * FROM %lms_report_schedule WHERE id_filter=$id_filter";
		$res = sql_query($qry);
		while ($row = sql_fetch_assoc($res)) {
			$output = report_delete_scheduletion($row['id_report_schedule']);
		}
	}
	return $output;
}

function report_delete_schedulation($id_sched) {
	//delete row from report_schedule table and recipients row
	$output = false;
	$qry = "DELETE FROM %lms_report_schedule WHERE id_report_schedule=$id_sched";
	if ($output = sql_query($qry)) {
		$qry2 = "DELETE FROM %lms_report_schedule_recipients WHERE id_report_schedule=$id_sched";
		$output = sql_query($qry2);		
	}
	return $output;
}


function report_update_schedulation($id_sched, $name, $period, $time, &$recipients) {
	$output = true;
	$qry = "UPDATE %lms_report_schedule ".
		"SET name='$name', period='$period' ".
		"WHERE id_report_schedule=$id_sched";
	$output = sql_query($qry);

	if ($output) {
		$qry2 = "DELETE FROM %lms_report_schedule_recipient WHERE id_report_schedule=$id_sched";
		$output = sql_query($qry2);
		if ($output) {
			//delete old recipients and replace with new ones
			$temp = array();
			foreach ($recipients as $value) {
				$temp[] = '('.$id_sched.', '.$value.')';
			}		
			$qry3 = "INSERT INTO %lms_report_schedule_recipient ".
				"(id_report_schedule, id_user) VALUES ".implode(',', $temp);
			$output &= sql_query($qry3);
			
		} else return false;
	} else return false;

	return $output;
}


function get_schedule_recipients($id_sched, $more_info = false) {
	$output = false;

	$query = "(SELECT t2.idst, t2.userid AS name, t2.firstname AS value1, t2.lastname AS value2, 'user' AS type "
		." FROM %lms_report_schedule_recipient as t1 "
		." JOIN %adm_user AS t2 "
		." ON (t2.idst = t1.id_user) "
		." WHERE t1.id_report_schedule = ".(int)$id_sched.") "

		." UNION "

		." (SELECT t2.idst, t2.groupid AS name, '' AS value1, '' AS value2, 'group' AS type "
		." FROM %lms_report_schedule_recipient as t1 "
		." JOIN %adm_group AS t2 "
		." ON (t2.idst = t1.id_user) "
		." WHERE t1.id_report_schedule = ".(int)$id_sched." "
		." AND t2.hidden = 'false') "

		." UNION "

		." (SELECT t2.idst, t4.translation AS name, t2.groupid AS value1, '' AS value2, 'folder' AS type "
		." FROM %lms_report_schedule_recipient as t1 "
		." JOIN %adm_group AS t2 "
		." JOIN %adm_org_chart_tree AS t3 "
		." JOIN %adm_org_chart AS t4 "
		." ON (t2.idst = t1.id_user AND t3.idst_oc = t2.idst AND t4.id_dir = t3.idOrg AND t4.lang_code = '".Lang::get()."') "
		." WHERE t1.id_report_schedule = ".(int)$id_sched." "
		." AND t2.groupid LIKE '/oc\_%' "
		." AND t2.hidden = 'true') "

		." UNION "

		." (SELECT t2.idst, t4.translation AS name, t2.groupid AS value1, '+descendants' AS value2, 'folder' AS type "
		." FROM %lms_report_schedule_recipient as t1 "
		." JOIN %adm_group AS t2 "
		." JOIN %adm_org_chart_tree AS t3 "
		." JOIN %adm_org_chart AS t4 "
		." ON (t2.idst = t1.id_user AND t3.idst_ocd = t2.idst AND t4.id_dir = t3.idOrg AND t4.lang_code = '".Lang::get()."') "
		." WHERE t1.id_report_schedule = ".(int)$id_sched." "
		." AND t2.groupid LIKE '/ocd\_%' "
		." AND t2.hidden = 'true') "

		." UNION "

		." (SELECT t2.idst, t4.name AS name, t2.groupid AS value1, t4.description AS value2, 'fncrole' AS type "
		." FROM %lms_report_schedule_recipient as t1 "
		." JOIN %adm_group AS t2 "
		." JOIN %adm_fncrole AS t3 "
		." JOIN %adm_fncrole_lang AS t4 "
		." ON (t2.idst = t1.id_user AND t3.id_fncrole = t2.idst AND t4.id_fncrole = t3.id_fncrole AND t4.lang_code = '".Lang::get()."') "
		." WHERE t1.id_report_schedule = ".(int)$id_sched." "
		." AND t2.groupid LIKE '/fncroles/%' "
		." AND t2.hidden = 'true') "

		." ORDER BY type, name";

	$res = sql_query($query);

	if ($res) {

		$output = array(
			'users' => array(),
			'groups' => array(),
			'folders' => array(),
			'fncroles' => array()
		);

		while ($obj = sql_fetch_object($res)) {

			switch ($obj->type) {

				case 'user': {
					if ($more_info) {
						$output['users'][$obj->idst] = $obj;
					} else {
						$output['users'][] = $obj->idst;
					}
				} break;

				case 'group': {
					if ($more_info) {
						$output['groups'][$obj->idst] = $obj;
					} else {
						$output['groups'][] = $obj->idst;
					}
				} break;
				
				case 'folder': {
					if ($more_info) {
						$output['folders'][$obj->idst] = $obj;
					} else {
						$output['folders'][] = $obj->idst;
					}
				} break;
				
				case 'fncrole': {
					if ($more_info) {
						$output['fncroles'][$obj->idst] = $obj;
					} else {
						$output['fncroles'][] = $obj->idst;
					}
				} break;

			}

		}
	}

	return $output;
}


//------------------------------------------------------------------------------

/*
 * This returns an array $objectType => {translation}
 */
function _getLOtranslations() {
	$output = array();
	$query = "SELECT objectType FROM %lms_lo_types";
	$db = DbConn::getInstance();
	$res = $db->query($query);
	while (list($objectType) = $db->fetch_row($res)) {
		switch ($objectType) {
			case "scormorg": $text = Lang::t('_SCORMSECTIONNAME', 'scorm'); break;
			case "item": $text = Lang::t('_FILE', 'standard'); break;
			default: $text = Lang::t('_LONAME_'.$objectType, 'storage'); break;
		}
		$output[$objectType] = $text;
	}
	return $output;
}

function getCommunicationsTable($selected = false) {
		require_once(_base_.'/lib/lib.table.php');
		$table = new Table();

		$lang_type = array(
			'none' => Lang::t('_NONE', 'communication'),
			'file' => Lang::t('_LONAME_item', 'storage'),
			'scorm' => Lang::t('_LONAME_scormorg', 'storage')
		);

		$col_type = array('image','','','align_center','align_center','align_center');
		$col_content = array(
			Lang::t(''),
			Lang::t('_TITLE'),
			Lang::t('_DESCRIPTION'),
			Lang::t('_DATE'),
			Lang::t('_TYPE'),
			//Lang::t('_COUNT_ACCESSIBILITY')
		);

		$table->setColsStyle($col_type);
		$table->addHead($col_content);

		if (!is_array($selected)) $selected = array();
		$query = "SELECT c.id_comm, c.title, c.description, c.publish_date, c.type_of, id_resource, COUNT(ca.id_comm) as access_entity "
			." FROM %lms_communication AS c "
			." LEFT JOIN %lms_communication_access AS ca ON (c.id_comm = ca.id_comm)"
			." GROUP BY c.id_comm"
			." ORDER BY c.publish_date DESC, c.title ASC";
		$db = DbConn::getInstance();
		$res = $db->query($query);
		while ($obj = $db->fetch_obj($res)) {
			$line = array();

			$line[] = Form::getInputCheckbox(
				'comm_selection_'.$obj->id_comm,    //id
				'comm_selection[]',                 //name
				$obj->id_comm,                      //value
				in_array($obj->id_comm, $selected), //is_checked
				''                                  //other param
			);
			$line[] = $obj->title;
			$line[] = $obj->description;
			$line[] = Format::date($obj->publish_date, 'date');
			$line[] = isset($lang_type[$obj->type_of]) ? $lang_type[$obj->type_of] : '';
			//$line[] = $obj->access_entity;

			$table->addBody($line);
		}

		return $table->getTable();
}


function getGamesTable($selected = false) {
		require_once(_base_.'/lib/lib.table.php');
		$table = new Table();

		$lang_type= _getLOtranslations();

		$col_type = array('image','','', '','align_center','align_center');
		$col_content = array(
			Lang::t(''),
			Lang::t('_TITLE'),
			Lang::t('_DESCRIPTION'),
			Lang::t('_FROM'),
			Lang::t('_TO'),
			Lang::t('_TYPE'),
			//Lang::t('_COUNT_ACCESSIBILITY')
		);

		$table->setColsStyle($col_type);
		$table->addHead($col_content);

		if (!is_array($selected)) $selected = array();
		$query = "SELECT c.id_game, c.title, c.description, c.start_date, c.end_date, "
			." c.type_of, id_resource, COUNT(ca.id_game) as access_entity "
			." FROM %lms_games AS c "
			." LEFT JOIN %lms_games_access AS ca ON (c.id_game = ca.id_game)"
			." GROUP BY c.id_game"
			." ORDER BY c.title";
		$db = DbConn::getInstance();
		$res = $db->query($query);
		while ($obj = $db->fetch_obj($res)) {
			$line = array();

			$line[] = Form::getInputCheckbox(
				'comp_selection_'.$obj->id_game,    //id
				'comp_selection[]',                 //name
				$obj->id_game,                      //value
				in_array($obj->id_game, $selected), //is_checked
				''                                  //other param
			);
			$line[] = $obj->title;
			$line[] = $obj->description;
			$line[] = Format::date($obj->start_date, 'date');
			$line[] = Format::date($obj->end_date, 'date');
			$line[] = isset($lang_type[$obj->type_of]) ? $lang_type[$obj->type_of] : '';
			//$line[] = $obj->access_entity;

			$table->addBody($line);
		}

		return $table->getTable();
}

function checkReport($id_filter){
    $qry = sql_query("SELECT r.id_report FROM %lms_report_filter rf JOIN %lms_report r ON rf.id_report = r.id_report WHERE id_filter=$id_filter");

    if (sql_num_rows($qry)==0){
        Util::jump_to('index.php?modname=report&op=reportlist&err=plugin');
    }
    return true;
}

?>