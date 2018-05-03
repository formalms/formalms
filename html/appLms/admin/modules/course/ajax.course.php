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

$op=Get::req('op', DOTY_ALPHANUM, '');

// courtesy of elearnit.net
function duplicateCourse()
{
	$id_dupcourse = Get::req('id_course', DOTY_INT, 0);

	// read the old course info
	$query_sel = "SELECT *
	FROM ".$GLOBALS['prefix_lms']."_course
	WHERE idCourse = '".$id_dupcourse."' ";
	$result_sel = sql_query($query_sel);
	$list_sel = sql_fetch_array($result_sel);

	foreach($list_sel as $k=>$v)
		$list_sel[$k] = sql_escape_string($v);

	$new_course_dup = 0;

	$new_file_array = array();

	if($list_sel['imgSponsor'] !== '')
	{
		$new_name_array = explode('_', str_replace('course_sponsor_logo_', '', $list_sel['imgSponsor']));
		$filename = 'course_sponsor_logo_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_sponsor_logo_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['imgSponsor']);

		$new_file_array[0]['old'] = $list_sel['imgSponsor'];
		$new_file_array[0]['new'] = $filename;
		$list_sel['imgSponsor'] = $filename;
	}

	if($list_sel['img_course'] !== '')
	{
		$new_name_array = explode('_', str_replace('course_logo_', '', $list_sel['img_course']));
		$filename = 'course_logo_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_logo_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['img_course']);

		$new_file_array[1]['old'] = $list_sel['img_course'];
		$new_file_array[1]['new'] = $filename;
		$list_sel['img_course'] = $filename;
	}

	if($list_sel['img_material'] !== '')
	{
		$new_name_array = explode('_', str_replace('course_user_material_', '', $list_sel['img_material']));
		$filename = 'course_user_material_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_user_material_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['img_material']);

		$new_file_array[2]['old'] = $list_sel['img_material'];
		$new_file_array[2]['new'] = $filename;
		$list_sel['img_material'] = $filename;
	}

	if($list_sel['img_othermaterial'] !== '')
	{
		$new_name_array = explode('_', str_replace('course_otheruser_material_', '', $list_sel['img_othermaterial']));
		$filename = 'course_otheruser_material_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_otheruser_material_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['img_othermaterial']);

		$new_file_array[3]['old'] = $list_sel['img_othermaterial'];
		$new_file_array[3]['new'] = $filename;
		$list_sel['img_othermaterial'] = $filename;
	}

	if($list_sel['course_demo'] !== '')
	{
		$new_name_array = explode('_', str_replace('course_demo_', '', $list_sel['course_demo']));
		$filename = 'course_demo_'.mt_rand(0, 100).'_'.time().'_'.str_replace('course_demo_'.$new_name_array[0].'_'.$new_name_array[1].'_', '',$list_sel['course_demo']);

		$new_file_array[4]['old'] = $list_sel['course_demo'];
		$new_file_array[4]['new'] = $filename;
		$list_sel['course_demo'] = $filename;
	}

	// duplicate the entry of learning_course
	$query_ins = "INSERT INTO ".$GLOBALS['prefix_lms']."_course
		( idCategory, code, name, description, lang_code, status, level_show_user,
		subscribe_method, linkSponsor, imgSponsor, img_course, img_material, img_othermaterial,
		course_demo, mediumTime, permCloseLO, userStatusOp, difficult, show_progress, show_time, show_extra_info,
		show_rules, valid_time, max_num_subscribe, min_num_subscribe,
		max_sms_budget, selling, prize, course_type, policy_point, point_to_all, course_edition, classrooms, certificates,
		create_date, security_code, imported_from_connection, course_quota, used_space, course_vote, allow_overbooking, can_subscribe,
		sub_start_date, sub_end_date, advance, show_who_online, direct_play, autoregistration_code, use_logo_in_courselist )
	VALUES
		( '".$list_sel['idCategory']."', '".$list_sel['code']."', '"."Copia di ".$list_sel['name']."', '".$list_sel['description']."', '".$list_sel['lang_code']."', '".$list_sel['status']."', '".$list_sel['level_show_user']."',
		'".$list_sel['subscribe_method']."', '".$list_sel['linkSponsor']."', '".$list_sel['imgSponsor']."', '".$list_sel['img_course']."', '".$list_sel['img_material']."', '".$list_sel['img_othermaterial']."',
		'".$list_sel['course_demo']."', '".$list_sel['mediumTime']."', '".$list_sel['permCloseLO']."', '".$list_sel['userStatusOp']."', '".$list_sel['difficult']."', '".$list_sel['show_progress']."', '".$list_sel['show_time']."', '".$list_sel['show_extra_info']."',
		'".$list_sel['show_rules']."', '".$list_sel['valid_time']."', '".$list_sel['max_num_subscribe']."', '".$list_sel['min_num_subscribe']."',
		'".$list_sel['max_sms_budget']."', '".$list_sel['selling']."', '".$list_sel['prize']."', '".$list_sel['course_type']."', '".$list_sel['policy_point']."', '".$list_sel['point_to_all']."', '".$list_sel['course_edition']."', '".$list_sel['classrooms']."', '".$list_sel['certificates']."',
		'".$list_sel['create_date']."', '".$list_sel['security_code']."', '".$list_sel['imported_from_connection']."', '".$list_sel['course_quota']."', '".$list_sel['used_space']."', '".$list_sel['course_vote']."', '".$list_sel['allow_overbooking']."', '".$list_sel['can_subscribe']."',
		'".$list_sel['sub_start_date']."', '".$list_sel['sub_end_date']."', '".$list_sel['advance']."', '".$list_sel['show_who_online']."', '".$list_sel['direct_play']."', '".$list_sel['autoregistration_code']."', '".$list_sel['use_logo_in_courselist']."' )";
	$result_ins = sql_query($query_ins);

	if(!$result_ins) {
		//Util::jump_to('index.php?modname=course&op=course_list&result=err_course');
		return false;
	}

	// the id of the new course created
	$new_course_dup = sql_insert_id();

	//Create the new course file
	$path = Get::sett('pathcourse');
	$path = '/appLms/'.Get::sett('pathcourse').( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

	require_once(_base_.'/lib/lib.upload.php');

	sl_open_fileoperations();

	foreach($new_file_array as $file_info)
		sl_copy($path.$file_info['old'], $path.$file_info['new']);

	sl_close_fileoperations();

	// copy the old course menu into the new one
	$query_selmen = "SELECT *
	FROM ".$GLOBALS['prefix_lms']."_menucourse_main
	WHERE idCourse = '".$id_dupcourse."' ";
	$result_selmen = sql_query($query_selmen);
	while($list_selmen = sql_fetch_array($result_selmen))
	{
		$query_dupmen = "INSERT INTO ".$GLOBALS['prefix_lms']."_menucourse_main ".
			" (idCourse, sequence, name, image) ".
			" VALUES ".
			" ( '".$new_course_dup."', '".$list_selmen['sequence']."', '".$list_selmen['name']."', '".$list_selmen['image']."' )";
		$result_dupmen = sql_query($query_dupmen);
		$array_seq[$list_selmen['idMain']] = sql_insert_id();
	}

	$query_selmenun = "SELECT *
	FROM ".$GLOBALS['prefix_lms']."_menucourse_under
	WHERE idCourse = '".$id_dupcourse."' ";
	$result_selmenun = sql_query($query_selmenun);
	while($list_selmenun = sql_fetch_array($result_selmenun)) {
		$valore_idn = $list_selmenun['idMain'];
		$_idMain = $array_seq[$valore_idn];
		$query_dupmen = "INSERT INTO ".$GLOBALS['prefix_lms']."_menucourse_under
		(idMain, idCourse, sequence, idModule, my_name)
		VALUES
		('".$_idMain."', '".$new_course_dup."', '".$list_selmenun['sequence']."', '".$list_selmenun['idModule']."', '".$list_selmenun['my_name']."')";
		$result_dupmen = sql_query($query_dupmen);
	}
	function &getCourseLevelSt($id_course) {

		$map 		= array();
		$levels 	= CourseLevel::getLevels();
		$acl_man	=& $GLOBALS['current_user']->getAclManager();

		// find all the group created for this menu custom for permission management
		foreach($levels as $lv => $name_level) {

			$group_info = $acl_man->getGroup(FALSE, '/lms/course/'.$id_course.'/subscribed/'.$lv);
			$map[$lv] 	= $group_info[ACL_INFO_IDST];
		}
		return $map;
	}
	function funAccess($functionname, $mode, $returnValue = false, $custom_mod_name = false) {

		return true;
	}
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');

	$docebo_course = new DoceboCourse($id_dupcourse);
	$subscribe_man = new CourseSubscribe_Manager();

	$group_idst =& $docebo_course->createCourseLevel($new_course_dup);
	$group_of_from  =& $docebo_course->getCourseLevel($id_dupcourse);
	$perm_form   =& createPermForCoursebis($group_of_from, $new_course_dup, $id_dupcourse);
	$levels    =  $subscribe_man->getUserLevel();

	foreach($levels as $lv => $name_level) {

		foreach($perm_form[$lv] as $idrole => $v) {

			if($group_idst[$lv] != 0 && $idrole != 0) {
				$acl_man =& $GLOBALS['current_user']->getAclManager();
				$acl_man->addToRole( $idrole, $group_idst[$lv] );
			}
		}
	}

	// duplicate the certificate assigned
	$query_selmenun = "SELECT *
	FROM ".$GLOBALS['prefix_lms']."_certificate_course
	WHERE id_course = '".$id_dupcourse."' ";
	$result_selmenun = sql_query($query_selmenun);
	while($list_selmenun = sql_fetch_array($result_selmenun)) {
		$query_dupmen = "INSERT INTO ".$GLOBALS['prefix_lms']."_certificate_course
			(id_certificate, id_course, available_for_status)
			VALUES
			('".$list_selmenun['id_certificate']."', '".$new_course_dup."', '".$list_selmenun['available_for_status']."' )";
		$result_dupmen = sql_query($query_dupmen);
	}

	require_once($GLOBALS['where_lms'].'/modules/organization/orglib.php' );
	require_once($GLOBALS['where_lms'].'/lib/lib.param.php');
	require_once($GLOBALS['where_lms'].'/class.module/track.object.php');
	require_once($GLOBALS['where_lms'].'/class.module/learning.object.php' );

	function createLO( $objectType, $idResource = NULL ) {

		$query = "SELECT className, fileName FROM ".$GLOBALS['prefix_lms']."_lo_types WHERE objectType='".$objectType."'";
		$rs = sql_query( $query );
		list( $className, $fileName ) = sql_fetch_row( $rs );
			require_once($GLOBALS['where_lms'].'/class.module/'.$fileName );
		$lo =  new $className ( $idResource );
		return $lo;
	}

	$nullVal = NULL;
	$array_cor = array();
	$map_org = array();
	$tree_course = new OrgDirDb($id_dupcourse);
	$coll = $tree_course->getFoldersCollection( $nullVal );
	while($folder = $coll->getNext()) {

		//if($folder->otherValues[REPOFIELDIDRESOURCE] != 0 ) {
		if( !empty($folder->otherValues[REPOFIELDOBJECTTYPE]) ) {

			$lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
			$id_nuovo_lo = $lo->copy($folder->otherValues[REPOFIELDIDRESOURCE]);

			$id_vecchio = $folder->otherValues[REPOFIELDIDRESOURCE];

			$query_selmenun = "SELECT * FROM
			".$GLOBALS['prefix_lms']."_organization
			WHERE idCourse = '".$id_dupcourse."'
			AND idResource = '".$id_vecchio."' ";
			$result_selmenun = sql_query($query_selmenun);

			while($list_selmenun = sql_fetch_array($result_selmenun)) {

				$query_dupmen = "INSERT INTO ".$GLOBALS['prefix_lms']."_organization
				(idParent, path, lev, title,
				objectType, idResource, idCategory, idUser, idAuthor,
				version, difficult, description, language, resource,
				objective, dateInsert, idCourse, prerequisites, isTerminator,
				idParam, visible, milestone)
				VALUES
				('".( isset($map_org[$list_selmenun['idParent']]) ? $map_org[$list_selmenun['idParent']] : 0 ) ."', '".$list_selmenun['path']."', '".$list_selmenun['lev']."', '".$list_selmenun['title']."',
				'".$list_selmenun['objectType']."', '".$id_nuovo_lo."', '".$list_selmenun['idCategory']."', '".$list_selmenun['idUser']."', '".$list_selmenun['idAuthor']."',
				'".$list_selmenun['version']."', '".$list_selmenun['difficult']."', '".$list_selmenun['description']."', '".$list_selmenun['language']."', '".$list_selmenun['resource']."',
				'".$list_selmenun['objective']."', '".$list_selmenun['dateInsert']."', '".$new_course_dup."', '".$list_selmenun['prerequisites']."', '".$list_selmenun['isTerminator']."',
				'".$list_selmenun['idParam']."', '".$list_selmenun['visible']."', '".$list_selmenun['milestone']."')";
				$result_dupmen = sql_query($query_dupmen);
				$id_org = $list_selmenun['idOrg'];
				$id_last = sql_insert_id();
				$array_cor[$id_org] = $id_last;

				$query_lo_par  = "INSERT INTO ".$GLOBALS['prefix_lms']."_lo_param
				(param_name, param_value)
				VALUES
				('idReference', '".$id_last."') ";
				$result_lo_par = sql_query($query_lo_par);
				$id_lo_par = sql_insert_id();

				$query_up_lo = "UPDATE ".$GLOBALS['prefix_lms']."_lo_param
				SET idParam = '".$id_lo_par."'
				WHERE id = '".$id_lo_par."' ";
				$result_up_lo = sql_query($query_up_lo);

				$query_up_or = "UPDATE ".$GLOBALS['prefix_lms']."_organization
				SET	idParam = '".$id_lo_par."'
				WHERE idOrg = '".$id_last."' ";
				$result_up_or = sql_query($query_up_or);
			}
		} else {
			// copy folder
			echo $id_vecchio = $folder->id;

			$query_selmenun = "SELECT * FROM
			".$GLOBALS['prefix_lms']."_organization
			WHERE idCourse = '".$id_dupcourse."'
			AND idOrg = '".$id_vecchio."' ";
			$result_selmenun = sql_query($query_selmenun);

			$list_selmenun = sql_fetch_array($result_selmenun);

			$query_dupmen = " INSERT INTO ".$GLOBALS['prefix_lms']."_organization
			(idParent, path, lev, title,
			objectType, idResource, idCategory, idUser, idAuthor,
			version, difficult, description, language, resource,
			objective, dateInsert, idCourse, prerequisites, isTerminator,
			idParam, visible, milestone)
			VALUES
			('".( isset($map_org[$list_selmenun['idParent']]) ? $map_org[$list_selmenun['idParent']] : 0 ) ."', '".$list_selmenun['path']."', '".$list_selmenun['lev']."', '".$list_selmenun['title']."',
			'".$list_selmenun['objectType']."', '".$id_nuovo_lo."', '".$list_selmenun['idCategory']."', '".$list_selmenun['idUser']."', '".$list_selmenun['idAuthor']."',
			'".$list_selmenun['version']."', '".$list_selmenun['difficult']."', '".$list_selmenun['description']."', '".$list_selmenun['language']."', '".$list_selmenun['resource']."',
			'".$list_selmenun['objective']."', '".$list_selmenun['dateInsert']."', '".$new_course_dup."', '".$list_selmenun['prerequisites']."', '".$list_selmenun['isTerminator']."',
			'".$list_selmenun['idParam']."', '".$list_selmenun['visible']."', '".$list_selmenun['milestone']."')";
			$result_dupmen = sql_query($query_dupmen);
			$map_org[$id_vecchio] = sql_insert_id();

		}
	}
	$query_cor = "SELECT *
	FROM ".$GLOBALS['prefix_lms']."_organization
	WHERE
	idCourse = '".$new_course_dup."'
	AND prerequisites !='' ";
	$result_cor = sql_query($query_cor);
	while($list_cor = sql_fetch_array($result_cor))
	{
		$id_orgup = $list_cor['prerequisites'];
		$arr_pre = explode(",",$id_orgup);

		for($i=0;$i<sizeof($arr_pre);$i++)
			$arr_pre[$i]=str_replace(intval($arr_pre[$i]),$array_cor[intval($arr_pre[$i])],$arr_pre[$i]);

		$query_updcor = "UPDATE ".$GLOBALS['prefix_lms']."_organization
			SET prerequisites = '";

		for($i=0;$i<sizeof($arr_pre);$i++)
		{
			if($i!=0)
				$query_updcor.=",";
			$query_updcor.=$arr_pre[$i];
		}

		$query_updcor.= "' WHERE idOrg = '".$list_cor['idOrg']."' ";
		$result_upcor = sql_query($query_updcor);
	}

	$query_selmenun = "SELECT * FROM
	".$GLOBALS['prefix_lms']."_forum
	WHERE idCourse = '".$id_dupcourse."' ";
	$result_selmenun = sql_query($query_selmenun);
	while($list_selmenun = sql_fetch_array($result_selmenun)) {

		$query_dupmen = "INSERT INTO
		".$GLOBALS['prefix_lms']."_forum
		(idCourse, title, description, locked, sequence, emoticons)
		VALUES
		('".$new_course_dup."', '".$list_selmenun['title']."', '".$list_selmenun['description']."',
		'".$list_selmenun['locked']."', '".$list_selmenun['sequence']."', '".$list_selmenun['emoticons']."')";
		$result_dupmen = sql_query($query_dupmen);
	}

	$query_selmenun = "SELECT * FROM
	".$GLOBALS['prefix_lms']."_coursereport
	WHERE id_course = '".$id_dupcourse."' ";
	$sql2=$query_selmenun;
	$result_selmenun = sql_query($query_selmenun);
	while($list_selmenun = sql_fetch_array($result_selmenun)) {

	if(!isset($array_organization[$list_selmenun['id_source']]) or $array_organization[$list_selmenun['id_source']]=="")
		$array_organization[$list_selmenun['id_source']]=0;
		$query_dupmen = "INSERT INTO
		".$GLOBALS['prefix_lms']."_coursereport
		(id_course,title,max_score,required_score,weight,show_to_user,use_for_final,sequence,source_of,id_source)
		VALUES
		('".$new_course_dup."', '".$list_selmenun['title']."', '".$list_selmenun['max_score']."',
		'".$list_selmenun['required_score']."', '".$list_selmenun['weight']."', '".$list_selmenun['show_to_user']."', '".$list_selmenun['use_for_final']."', '".$list_selmenun['sequence']."', '".$list_selmenun['source_of']."', '".$array_organization[$list_selmenun['id_source']]."')";
		$sql2=$query_dupmen;
		$result_dupmen = sql_query($query_dupmen);
	}

	$query_selmenun = "SELECT *
	FROM ".$GLOBALS['prefix_lms']."_htmlfront
	WHERE id_course = '".$id_dupcourse."' ";
	$result_selmenun = sql_query($query_selmenun);
	while($list_selmenun = sql_fetch_array($result_selmenun)){

		$query_dupmen = "INSERT INTO ".$GLOBALS['prefix_lms']."_htmlfront
		(id_course, textof)
		VALUES
		('".$new_course_dup."', '".sql_escape_string($list_selmenun['textof'])."')";
		$result_dupmen = sql_query($query_dupmen);
	}
	//Util::jump_to('index.php?modname=course&amp;op=course_list&result=ok_course');
	return true;
}


switch ($op) {

	case "course_autocomplete": {
		require_once(_lms_.'/lib/lib.edition.php');
		require_once(_lms_.'/lib/lib.date.php');
		require_once(_base_.'/lib/lib.json.php');

		$ed_man = new EditionManager();
		$dt_man = new DateManager();

		$json = new Services_JSON();
		$output = array(
			'courses' => array()
		);

		$filter = Get::req('query', DOTY_STRING, "");
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));

		if ($filter != "") {
			$query_filter = "";
			$userlevelid = Docebo::user()->getUserLevelId();
			if ($userlevelid != ADMIN_GROUP_GODADMIN) {
				require_once(_base_ . '/lib/lib.preference.php');
				$adminManager = new AdminPreference();
				$acl_man =& Docebo::user()->getAclManager();
				$admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
				$query_filter .= " AND idCourse IN (" . implode(',', $admin_courses['course']) . ") ";
			}

			$query = "SELECT idCourse, code, name, course_type, course_edition FROM %lms_course "
				." WHERE 1 ".$query_filter." AND  ( code LIKE '%".$filter."%' OR name LIKE '%".$filter."%' ) ORDER BY code, name "
				.($results > 0 ? " LIMIT 0, ".(int)$results : "");
			$res = sql_query($query);
			if ($res) {
				while (list($id_course, $code, $name, $course_type, $course_edition) = sql_fetch_row($res)) {
					//construct record for course instance
					$record = array(
						'cname' => ($code != "" ? '['.$code.'] ' : '').$name,
						'id_course' => $id_course,
						'code' => $code,
						'name' => $name,
						'code_highlight' => Layout::highlight($code, $filter),
						'name_highlight' => Layout::highlight($name, $filter)
					);

					//detect if the course is of type classroom or has editions
					//TO DO: optimization, do not put queries in iterations
					if ($course_type == 'elearning' && $course_edition>0) {
						$record['has_editions'] = true;
						$_arr = array();
						$_editions = $ed_man->getEdition($id_course);
						foreach ($_editions as $_edition) {
							$_arr[] = array(
								'id' => $_edition['id_edition'],
								'code' => $_edition['code'],
								'name' => $_edition['name'],
								'date_begin' => $_edition['date_begin'],
								'date_end' => $_edition['date_end'],
								'display_name' => '['.$_edition['code'].'] '.$_edition['name'].' ('.Format::date($_edition['date_begin'], 'date').' - '.Format::date($_edition['date_end'], 'date').')'
							);
						}
						$record['editions'] = $_arr;
					}
					if ($course_type == 'classroom') {
						$record['has_classrooms'] = true;
						$_arr = array();
						$_dates = $dt_man->getCourseDate($id_course);
						foreach ($_dates as $_date) {
							$_arr[] = array(
								'id' => $_date['id_date'],
								'code' => $_date['code'],
								'name' => $_date['name'],
								'date_begin' => $_date['date_begin'],
								'date_end' => $_date['date_end'],
								'display_name' => '['.$_date['code'].'] '.$_date['name'].' ('.Format::date($_date['date_begin'], 'date').' - '.Format::date($_date['date_end'], 'date').')'
							);
						}
						$record['classrooms'] = $_arr;
					}

					$output['courses'][] = $record;
				}
			}
		}

		aout($json->encode($output));
	} break;


	case "dup_course": {
		require_once(_base_.'/lib/lib.json.php');
		$json = new Services_JSON();
		$res = duplicateCourse();
		$output = array('success' => $res);
		if (!$res) $output['message'] = Lang::t('_ERROR_WHILE_SAVING', 'standard');
		aout($json->encode($output));
	} break;

}

?>