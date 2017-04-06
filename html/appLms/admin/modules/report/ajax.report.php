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

if(Docebo::user()->isAnonymous()) die('You can\'t access');

function _encode(&$data) { return serialize($data); } //{ return urlencode(Util::serialize($data)); }
function _decode(&$data) { return unserialize($data); } //{ return Util::unserialize(urldecode($data)); }


$rep_cat = Get::req('rep_cat', DOTY_ALPHANUM, false);

switch ($rep_cat) {

	case 'competences': {
		//include('ajax.report_competences.php');
	} break;
	
	default: {
	
$op = Get::req('op', DOTY_ALPHANUM, '');
switch($op) {

	case 'save_filter_window': {
		require_once(_base_.'/lib/lib.form.php');
		$lang =& DoceboLanguage::createInstance('report', 'framework');
		
		$output = array();
		$output['title'] = $lang->def('_SAVE_REPORT_TITLE');
		
		$output['content'] = //'nome filtro:<input type="text" name="filter_name" value="" />';
			Form::getTextfield( 
				'Nome del filtro: ', //$label_name, 
				'filter_name', //$id, 
				'filter_name', //$name, 
				'200', '').Form::getHidden('filter_op','op','save_filter');
		
		$output['button_ok'] = $lang->def('_SAVE');
		$output['button_undo'] = $lang->def('_UNDO');
		
		$json = new Services_JSON();
		aout($json->encode($output));
	} break;


	case 'show_recipients_window': {
		require_once($GLOBALS['where_lms'].'/lib/lib.report.php');
		$lang =& DoceboLanguage::createInstance('report', 'framework');
		$output = array(
			'success' => true,
			'header' => Lang::t('_RECIPIENTS', 'standard'),
			'body' => ''
		);

		$id_sched = Get::req('idsched', DOTY_INT, false);

		if ($id_sched>0) {
			
			$tables = array();
			$records = get_schedule_recipients($id_sched, true);

			foreach ($records AS $type => $list) {

				switch ($type) {

					case 'users':
						if (!empty($list)) {
							$tb = new Table();
							$tb->addHead(array(Lang::t('_USERNAME', 'standard'), Lang::t('_FULLNAME', 'standard')), array('',''));
							foreach ($list as $key => $value) {
								$tb->addBody(array(
									Docebo::aclm()->relativeId($value->name),
									trim($value->value1.' '.$value->value2)
								));
							}
							$tables[] = $tb->getTable();
							unset($tb);
						}
					break;

					case 'groups':
						if (!empty($list)) {
							$tb = new Table();
							$tb->addHead(array(Lang::t('_GROUPUSER_groupid', 'organization_chart')), array(''));
							foreach ($list as $key => $value) {
								$tb->addBody(array(
									Docebo::aclm()->relativeId($value->name)
								));
							}
							$tables[] = $tb->getTable();
							unset($tb);
						}
					break;

					case 'folders':
						if (!empty($list)) {
							$tb = new Table();
							$tb->addHead(array(Lang::t('_ORGFOLDERNAME', 'storage')), array(''));
							foreach ($list as $key => $value) {
								$is_descendants = strpos($obj->value1, 'ocd') !== false;

								$tb->addBody(array(
									$value->name.($is_descendants ? " (+ ".Lang::t('_INHERIT', 'standard').")" : "")
								));
							}
							$tables[] = $tb->getTable();
							unset($tb);
						}
					break;

					case 'fncroles':
						if (!empty($list)) {
							$tb = new Table();
							$tb->addHead(array(Lang::t('_FNCROLE', 'fncroles')), array(''));
							foreach ($list as $key => $value) {
								$tb->addBody(array(
									$value->name
								));
							}
							$tables[] = $tb->getTable();
							unset($tb);
						}
					break;

				}

			}

			
		}

		if (!empty($tables)) {
			$output['body'] = implode('<br />', $tables);
		} else {
			$output['body'] = Lang::t('_NO_VALUE', 'user_managment');
		}

		//$output['button_close'] = $lang->def('_CLOSE');
		$json = new Services_JSON();
		aout($json->encode($output));
	} break;


	case 'save_filter': {
		$output=array();
		$filter_data = Get::req('filter_data', DOTY_ALPHANUM, ''); //warning: check urlencode-serialize etc.
		$data = urldecode($filter_data); //put serialized data in DB
		
		$name = Get::req('filter_name', DOTY_ALPHANUM, '');
		$query = "INSERT INTO %lms_report_filter ".
			"(id_report, author, creation_date, filter_data, filter_name) VALUES ".
			"(".$_SESSION['report']['id_report'].", ".Docebo::user()->getIdst().", NOW(), ".
			" '".addslashes(serialize($_SESSION['report']))."', '$name')";
		
		if (!$output['success']=sql_query($query)) {
			$output['error']=sql_error();
		} else {
			//if query is ok, I got the inserted ID and I put in session, telling the system I'm using it
			$row = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			$_SESSION['report_saved'] = $row[0];
		}
		$json = new Services_JSON();
		aout($json->encode($output));
	} break;


	case 'delete_filter': {
		$output=array();
		$filter_id = Get::req('filter_id', DOTY_ALPHANUM, '');
		if (sql_query("DELETE FROM %lms_report_filter WHERE id_filter=$filter_id")) {
			$output['success']=true;
		} else {
			$output['success']=false;
		}
		$json = new Services_JSON();
		aout($json->encode($output));
	} break;

	case 'sched_enable' : {
		$output=array();
		$success=false;
		$message='';
		$id_sched=Get::req('id', DOTY_INT, false);
		$value=Get::req('val', DOTY_INT, -1);
		if ($value>=0 && $id_sched!==false) {
			$query="UPDATE %lms_report_schedule SET enabled=$value ".
				"WHERE id_report_schedule=$id_sched";
			$success=sql_query($query);
		}
		$output['success']=$success;
		$json = new Services_JSON();
		aout($json->encode($output));
	} break;

	case 'public_rep': {
		$output=array();
		$success=false;
		$message='';
		$id_rep=Get::req('id', DOTY_INT, false);
		$value=Get::req('val', DOTY_INT, -1);
		if ($value>=0 && $id_rep!==false) {
			$query="UPDATE %lms_report_filter SET is_public=$value ".
				"WHERE id_filter=$id_rep";
			$success=sql_query($query);
		}
                $output['success']=$success;
        $json = new Services_JSON();
        aout($json->encode($output));
	} break;

}

} break;

}

?>