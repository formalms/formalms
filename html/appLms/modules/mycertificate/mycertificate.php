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

//Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN

function mycertificate(&$url) {
	checkPerm('view');

	require_once(_lms_.'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang =& DoceboLanguage::createInstance('profile', 'framework');
	$lang =& DoceboLanguage::createInstance('course', 'lms');
	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

  $admin_level = Docebo::user()->getUserLevelId();
  $show_preview = true;//($admin_level == ADMIN_GROUP_ADMIN || $admin_level == ADMIN_GROUP_GODADMIN);

  $title = $lang->def('_MY_CERTIFICATE', 'certificate');
	$html = getTitleArea($title, 'mycertificate')
		.'<div class="std_block">';

  //--- draw time periods dropdown ---------------------------------------------

	$period_start = '';
	$period_end = '';

	//extract checking period
  $p_model = new TimeperiodsAlms();
	$year = date("Y");
	$p_list = array("0" => $lang->def('_ALL'));
	$p_selected = Get::req('selected_period', DOTY_INT, 0);
	$p_data = $p_model->getTimePeriods('', true);
	if (count($p_data) > 0) {
		foreach ($p_data as $obj) {
			$p_list[$obj->id_period] = /*$obj->title.' ('
				.*/Format::date($obj->start_date, 'date').' - '
				.Format::date($obj->end_date, 'date')/*.')'*/;
			if ($p_selected == $obj->id_period) {
				$period_start = $obj->start_date;
				$period_end = $obj->end_date;
			}
		}
	}

	if (!array_key_exists($p_selected, $p_list)) {
		$p_selected = 0;
		$period_start = '';
		$period_end = '';
	}

	//date dropdown
	$onchange = ' onchange="javascript:this.form.submit();"';
	$html_filter_cert = ''
		.Form::openForm('selected_period_form_cert', $url->getUrl())
		.Form::openElementSpace()
		.Form::getDropdown(Lang::t('_TIME_PERIODS', 'menu'), 'selected_period_cert', 'selected_period', $p_list, $p_selected, '', '', $onchange)
		.Form::closeElementSpace()
		.Form::getHidden('current_tab_cert', 'current_tab', Get::req('current_tab', DOTY_STRING, 'cert')) //"cert" or "meta"
		.Form::getHidden('is_filtering_cert', 'is_filtering_cert', 1)
		.Form::closeForm();

	$html_filter_meta = ''
		.Form::openForm('selected_period_form_meta', $url->getUrl())
		.Form::openElementSpace()
		.Form::getDropdown(Lang::t('_TIME_PERIODS', 'menu'), 'selected_period_meta', 'selected_period', $p_list, $p_selected, '', '', $onchange)
		.Form::closeElementSpace()
		.Form::getHidden('current_tab_meta', 'current_tab', Get::req('current_tab', DOTY_STRING, 'meta')) //"cert" or "meta"
		.Form::getHidden('is_filtering_meta', 'is_filtering_meta', 1)
		.Form::closeForm();

	//----------------------------------------------------------------------------

	$cert = new Certificate();

	/*
	 * Print certificates tables, subdivided by year and course type
	 */

	$html_cert = '';
	$tb_cert = new Table(0);

	$cont_h = array (
		$lang->def('_YEAR', 'standard'),
		$lang->def('_COURSE_CODE', 'course'),
		$lang->def('_COURSE', 'course'),
		//$lang->def('_DATE_BEGIN', 'course'),
		$lang->def('_CERTIFICATE_NAME', 'course'),
		$lang->def('_DATE_END', 'course')
		/* hide course scores - remove comment to show
		$lang->def('_SCORE_INIT', 'profile', 'framework'),
		$lang->def('_SCORE_FINAL', 'profile', 'framework')
		*/
	);
	//if ($show_preview) $cont_h[] = '<img src="'.getPathImage('lms').'certificate/preview.gif" alt="'.$lang->def('_PREVIEW').'" />';
	//$cont_h[] = '<img src="'.getPathImage('lms').'certificate/certificate.gif" alt="'.$lang->def('_ALT_TAKE_A_COPY').'" />';
	if ($show_preview) $cont_h[] = '<span class="ico-sprite subs_view"><span>'.$lang->def('_PREVIEW').'"</span></span>';
	$cont_h[] = '<span class="ico-sprite subs_pdf"><span>'.$lang->def('_ALT_TAKE_A_COPY').'</span></span>';

	$type_h = array(
		'img-cell',
		'',
		'',
		'align-center',
		'align-center',
		'img-cell',
		'img-cell'
		/* hide course scores - remove comment to show
		'img-cell',
		'img-cell'
		*/
	);
	if ($show_preview) $type_h[] = 'nowarp';
	$type_h[] = 'nowarp';

	$tb_cert->setColsStyle($type_h);
	$tb_cert->addHead($cont_h);

	$available_cert = $cert->certificateForCourses(false, false);
	$released = $cert->certificateReleased(Docebo::user()->getIdST());

	$query_courses = ""
	." SELECT c.idCourse, c.code, c.name, u.status AS user_status, c.course_type, c.permCloseLO "
	." FROM %lms_course AS c JOIN %lms_courseuser AS u ON (c.idCourse = u.idCourse) "
	." WHERE u.idUser = '".Docebo::user()->getIdST()."' "
	//." AND c.course_type <> 'assessment' "
	.($period_start != '' ? " AND u.date_complete >= '".$period_start."' " : "")
	.($period_end != '' ? " AND u.date_complete <= '".$period_end."' " : "")
	." ORDER BY u.date_complete DESC, u.status DESC ";
	$course_list = sql_query($query_courses);

	$arr_courses =  array();
	$arr_courses_ids = array();
	while ($obj = sql_fetch_object($course_list)) {
		$arr_courses[$obj->course_type][] = array(
			$obj->idCourse,
			$obj->code,
			$obj->name,
			$obj->user_status,
			$obj->permCloseLO,
		);
		$arr_courses_ids[] = $obj->idCourse;
	}
	$arr_course_types = getCourseTypes();
	$table_displayed = false;

	//extract certificates details and availability by courses ids
	$arr_courses_ids = array_unique($arr_courses_ids);
	$arr_certificates_availability = array();
	$arr_certificates_details = array();
	if (count($arr_courses_ids) > 0) {
		$query = "SELECT id_certificate, id_course, available_for_status "
			." FROM ".$GLOBALS['prefix_lms']."_certificate_course"." WHERE id_course IN (".implode(",", $arr_courses_ids).")";
			//." WHERE id_certificate = '".$id_cert."'"
			//." AND id_course IN (".implode(",", $arr_courses_ids).")";
		$res = sql_query($query);
		while (list($id_certificate, $id_course, $available_for_status) = sql_fetch_row($res)) {
			$arr_certificates_availability[$id_course][$id_certificate] = $available_for_status;
		}

		$cont = array();

		$query =	"SELECT idCourse, date_inscr, date_first_access, date_complete, status"
			." FROM %lms_courseuser WHERE idUser = '".Docebo::user()->getIdST()."'"
			." AND idCourse IN (".implode(",", $arr_courses_ids).") "
			.($period_start != '' ? " AND date_complete >= '".$period_start."' " : "")
			.($period_end != '' ? " AND date_complete <= '".$period_end."' " : "");

		$res = sql_query($query);
		while (list($id_course, $date_inscr, $date_begin, $date_end, $status) = sql_fetch_row($res)) {
			$arr_certificate_details[$id_course] = array($date_inscr, $date_begin, $date_end, $status);
		}
	}


	//order arr_courses by key
	ksort($arr_courses);

	$years = array();
	foreach ($arr_courses as $course_type=>$course_data) {

		if (in_array($course_type, array_keys($arr_course_types))) {
			//$html .= '';

			$tb = new Table(0);
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);

			//filter and organize data to display
			$display_data = array();
			foreach ($course_data as $k => $value) {
				list($id_course, $code, $name, $user_status, $perm_close_lo) = $value;

				if(isset($available_cert[$id_course]))
				{
					$can_rel_exceptional = false;

					while(list($id_cert, $certificate) = each($available_cert[$id_course]))
     
					if($cert->certificateAvailableForUser($id_cert, $id_course, getLogUserId()) ) {
						if($certificate[CERT_AV_POINT_REQUIRED] > 0)
						{
							$course_score_final = false;

							if($perm_close_lo == 0)
							{
								require_once($GLOBALS['where_lms'].'/lib/lib.orgchart.php');
								$org_man = new OrganizationManagement(false);

								$score_final = $org_man->getFinalObjectScore(array(getLogUserId()), array($id_course));

								if(isset($score_final[$id_course][getLogUserId()])  && $score_final[$id_course][getLogUserId()]['max_score'])
								{
									$course_score_final = $score_final[$id_course][getLogUserId()]['score'];
									$course_score_final_max = $score_final[$id_course][getLogUserId()]['max_score'];
								}
							}
							else
							{
								require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
								$rep_man = new CourseReportManager();

								$score_course = $rep_man->getUserFinalScore(array(getLogUserId()), array($id_course));

								if(!empty($score_course))
								{
									$course_score_final 	= ( isset($score_course[getLogUserId()][$id_course]) ? $score_course[getLogUserId()][$id_course]['score'] : false );
									$course_score_final_max = ( isset($score_course[getLogUserId()][$id_course]) ? $score_course[getLogUserId()][$id_course]['max_score'] : false );
								}
							}

							if($course_score_final >= $certificate[CERT_AV_POINT_REQUIRED])
								$can_rel_exceptional = true;
						}
					}
					reset($available_cert[$id_course]);

					//count years for rowspans
					while(list($id_cert, $certificate) = each($available_cert[$id_course])) {
                                                //(aggiunto if prima dell'or)
                                                if($cert->certificateAvailableForUser($id_cert, $id_course, getLogUserId()) ) {
                                                        //$value[4] = $id_cert;

                                                        list($available_for_status) = $arr_certificates_availability[$id_course][$id_cert];
                                                        list($date_inscr, $date_begin, $date_end, $status) = $arr_certificate_details[$id_course];
                                                        if(($available_for_status == 3 && $status == 2) || ($available_for_status == 2 && $status == 1) || $available_for_status == 1) {
                                                                //$year = substr($date_end, 0, 4);
                                                                switch ($available_for_status) {
                                                                        case 3: $cur_year = substr($date_end, 0, 4); break;
                                                                        case 2: $cur_year = substr($date_begin, 0, 4); break;
                                                                        case 1: $cur_year = substr($date_inscr, 0, 4); break;
                                                                        default: $cur_year = '-';
                                                                }

																// (mi ricreo l'array value perchè manca di date_* BUG FIX)
                                                                $value = array($id_course, $code, $name, $date_begin, $date_end, $user_status, $perm_close_lo);
                                                                $value[6] = $id_cert;

																$query = "SELECT name "
																	." FROM ".$GLOBALS['prefix_lms']."_certificate"." WHERE id_certificate = ".$id_cert;

																$res = sql_query($query);
																list($cname) = sql_fetch_row($res);
																$value[7] = $cname;

                                                                if($can_rel_exceptional && $certificate[CERT_AV_POINT_REQUIRED] > 0)
                                                                {
                                                                        if (isset($years[$course_type][$cur_year]))
                                                                                $years[$course_type][$cur_year]++;
                                                                        else
                                                                                $years[$course_type][$cur_year] = 1;

                                                                        $display_data[$cur_year][] = $value;
                                                                }
                                                                elseif(!$can_rel_exceptional && $certificate[CERT_AV_POINT_REQUIRED] == 0)
                                                                {
                                                                        if (isset($years[$course_type][$cur_year]))
                                                                                $years[$course_type][$cur_year]++;
                                                                        else
                                                                                $years[$course_type][$cur_year] = 1;

                                                                        $display_data[$cur_year][] = $value;
                                                                }
                                                        }
                                                }
					
                                        }
                                }
			}

			if (count($display_data) > 0)
				krsort($display_data);

			$av_cert = 0;
			$prev_year = false;
			$rowspan_counter = 0;

			require_once($GLOBALS['where_lms'].'/lib/lib.orgchart.php');
			$org_man = new OrganizationManagement(false);

			foreach ($display_data as $year=>$rows) {
				$first = true;
				foreach ($rows as $row) {

					// list($id_course, $code, $name, $user_status, $id_cert) = $row;
					list($id_course, $code, $name, $date_begin, $date_end, $user_status, $id_cert, $cname) = $row;
					
					$cont = array();

					if ($first) {
						$cont[] = array(
							'rowspan' => isset($years[$course_type][$year]) ? $years[$course_type][$year] : 1,
							'value' => $year,
							'style' => $type_h[0].' mycertificate_rowspan'.($rowspan_counter%2 > 0 ? '_odd' : '')
						);
						$rowspan_counter++;
						$first = false;
					}

					// 2 - the code of the course
					$cont[] = array(
						'value' => $code,
						'style' => $type_h[1]
					);

					// 3 - the name of the course
					$cont[] = array(
						'value' => $name,
						'style' => $type_h[2]
					);


					// sostituito date_begin con certificate name
					// 4 - starting date
//					$cont[] = array(
//						'value' => Format::date($date_begin, 'datetime'),
//						'style' => $type_h[3]
//					);

					$cont[] = array(
						'value' => $cname,
						'style' => $type_h[3]
					);


					// 5 - complete date
					$cont[] = array(
						'value' => Format::date($date_end, 'datetime'),
						'style' => $type_h[4]
					);

					//-- scores --
					$score_start = $org_man->getStartObjectScore(array(Docebo::user()->getIdST()), array($id_course));
					$score_final = $org_man->getFinalObjectScore(array(Docebo::user()->getIdST()), array($id_course));

					$_value1 =	(isset($score_start[$id_course][Docebo::user()->getIdST()])  && $score_start[$id_course][Docebo::user()->getIdST()]['max_score']
								? $score_start[$id_course][Docebo::user()->getIdST()]['score'].' / '.$score_start[$id_course][Docebo::user()->getIdST()]['max_score']
								: '' );
					$_value2 =	(isset($score_final[$id_course][Docebo::user()->getIdST()])  && $score_final[$id_course][Docebo::user()->getIdST()]['max_score']
								? $score_final[$id_course][Docebo::user()->getIdST()]['score'].' / '.$score_final[$id_course][Docebo::user()->getIdST()]['max_score']
								: '' );

					/* hide course scores - remove comment to show
					// 6 - init score
					$cont[] = array(
						'value' => $_value1,
						'style' => $type_h[5]
					);

					// 7 - end score
					$cont[] = array(
						'value' => $_value2,
						'style' => $type_h[6]
					);
					*/

					if (isset($released[$id_course][$id_cert])) {
						$av_cert++;

						if ($show_preview) {
							$cont[] = array(
								'value' => '',
								'style' => $type_h[7]
							);
						}

						$_value =	'<a class="ico-wt-sprite subs_pdf" href="'.$url->getUrl('op=release_cert&id_certificate='.$id_cert.'&id_course='.$id_course).'" '
							.' title="'.$lang->def('_TAKE_A_COPY').'"><span>'
							//.'<img src="'.getPathImage('lms').'certificate/certificate.gif" alt="'.$lang->def('_ALT_TAKE_A_COPY').' : '.strip_tags($certificate[CERT_NAME]).'" />'
							.$lang->def('_TAKE_A_COPY').'</span></a>';
						$cont[] = array(
							'value' => $_value,
							'style' => $type_h[$show_preview ? 8 : 7]
						);

					} else {

						$av_cert++;

						if ($show_preview) {
							$_value =	'<a class="ico-wt-sprite subs_view" href="'.$url->getUrl('op=preview_cert&id_certificate='.$id_cert.'&id_course='.$id_course).'" '
								.' title="'.$lang->def('_PREVIEW').'"><span>'
								//.'<img src="'.getPathImage('lms').'certificate/preview.gif" alt="'.$lang->def('_PREVIEW').' : '.strip_tags($certificate[CERT_NAME]).'" />'
								.$lang->def('_PREVIEW').'</span></a>';
							$cont[] = array(
								'value' => $_value,
								'style' => $type_h[7]
							);
						}

						$_value =	'<a class="ico-wt-sprite subs_pdf" href="'.$url->getUrl('op=release_cert&id_certificate='.$id_cert.'&id_course='.$id_course).'" '
							.' title="'.$lang->def('_NEW_CERTIFICATE').'"><span>'
							//.'<img src="'.getPathImage('lms').'certificate/certificate.gif" alt="'.$lang->def('_ALT_NEW_CERTIFICATE').' : '.strip_tags($certificate[CERT_NAME]).'" />'
							.$lang->def('_NEW_CERTIFICATE').'</span></a>';
						$cont[] = array(
							'value' => $_value,
							'style' => $type_h[$show_preview ? 8 : 7]
						);

					}

					$tb->addBody($cont);

				}
			}


			if($av_cert > 0) {
				$table_displayed = true;
				$html_cert .= '<h2 class="mycertificate_title">'.$arr_course_types[$course_type].'</h2>';
				$html_cert .= $tb->getTable();
			}

		}

	} //end course_type foreach

	if (!$table_displayed) {
		$is_filtering = Get::req('is_filtering_cert', DOTY_INT, 0);
		$html_cert .= '<p>'.($is_filtering ? $html_filter_cert : '').$lang->def('_NO_CONTENT').'</p>';
	} else {
		$html_cert = $html_filter_cert.$html_cert;
	}

//-------------------------------------------------------------------------------------------


	/*
	 * Print meta-certificates table
	 */

	$html_meta = '';
	$tb_meta_cert = new Table(0);

	$cont_h = array	();
	$cont_h[] = $lang->def('_CODE');
	$cont_h[] = $lang->def('_NAME');
	$cont_h[] = $lang->def('_COURSE_LIST');
  //if ($show_preview) $cont_h[] = '<img src="'.getPathImage('lms').'certificate/preview.gif" alt="'.$lang->def('_PREVIEW').'" />';
	//$cont_h[] = '<img src="'.getPathImage('lms').'certificate/certificate.gif" alt="'.$lang->def('_ALT_TAKE_A_COPY').'" />';
	if ($show_preview) $cont_h[] = '<span class="ico-sprite subs_view"><span>'.$lang->def('_PREVIEW').'"</span></span>';
	$cont_h[] = '<span class="ico-sprite subs_pdf"><span>'.$lang->def('_ALT_TAKE_A_COPY').'</span></span>';


	$type_h = array();
	$type_h[] = '';
	$type_h[] = '';
	$type_h[] = '';
	if ($show_preview) $type_h[] = 'img-cell';//'nowrap';
	$type_h[] = 'img-cell';//'nowrap';

	$tb_meta_cert->setColsStyle($type_h);
	$tb_meta_cert->addHead($cont_h);

	$query =	"SELECT c.idMetaCertificate, m.title, m.description, m.idCertificate"
				." FROM %lms_certificate_meta_course as c"
				." JOIN %lms_certificate_meta as m ON c.idMetaCertificate = m.idMetaCertificate"
				." WHERE c.idUser = '".Docebo::user()->getIdST()."'"
				." GROUP BY c.idMetaCertificate"
				." ORDER BY m.title, m.description";

	$result = sql_query($query);
	$av_meta_cert = sql_num_rows($result);
	$cert_meta_html = '';

	while(list($id_meta, $name, $description, $id_certificate) = sql_fetch_row($result)) {
		$cont = array();

		$query =	"SELECT code, name"
					." FROM %lms_certificate"
					." WHERE id_certificate = "
					." ("
					." SELECT idCertificate"
					." FROM %lms_certificate_meta"
					." WHERE idMetaCertificate = '".$id_meta."'"
					." )";

		list($code, $name) = sql_fetch_row(sql_query($query));

		$cont[] = $code;
		$cont[] = $name;

		$query_released =	"SELECT on_date"
							." FROM %lms_certificate_meta_assign"
							." WHERE idUser = '".Docebo::user()->getIdST()."'"
							." AND idMetaCertificate = '".$id_meta."'";

		$result_released = sql_query($query_released);

		$query =	"SELECT user_release"
					." FROM %lms_certificate"
					." WHERE id_certificate = '".$id_certificate."'";

		list($user_release) = sql_fetch_row(sql_query($query));

		if (sql_num_rows($result_released)) {
			$course_list = '';

			$first = true;

			$query_course =	"SELECT code, name"
							." FROM %lms_course"
							." WHERE idCourse IN "
							."("
							."SELECT idCourse"
							." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
							." WHERE idUser = '".Docebo::user()->getIdST()."'"
							." AND idMetaCertificate = '".$id_meta."'"
							.")";

			$result_course = sql_query($query_course);

			while (list($code, $name) = sql_fetch_row($result_course)) {
				if($first)
					$first = false;
				else
					$course_list .= '<br/>';

				$course_list .= '('.$code.') - '.$name;
			}

			$cont[] = $course_list;
			if ($show_preview) $cont[] = '';

			list($date) = sql_fetch_row($result_released);

			$cont[] =	'<a class="ico-wt-sprite subs_pdf" href="'.$url->getUrl('op=release_cert&id_certificate='.$id_certificate.'&idmeta='.$id_meta).'" '
				.' title="'.$lang->def('_TAKE_A_COPY').'"><span>'
				.$lang->def('_TAKE_A_COPY').'</span></a>';

			$tb_meta_cert->addBody($cont);

		} elseif($user_release == 0) {

			$av_meta_cert--;

		} else {

			$query =	"SELECT idCourse"
						." FROM %lms_certificate_meta_course"
						." WHERE idUser = '".Docebo::user()->getIdST()."'"
						." AND idMetaCertificate = '".$id_meta."'";
			$result_int = sql_query($query);

			$control = true;
			while (list($id_course) = sql_fetch_row($result_int)) {
				$query =	"SELECT COUNT(*)"
							." FROM %lms_courseuser"
							." WHERE idCourse = '".$id_course."'"
							." AND idUser = '".Docebo::user()->getIdST()."'"
							." AND status = '"._CUS_END."'";
				list($number) = sql_fetch_row(sql_query($query));
				if(!$number) $control = false;


			}

			if ($control) {

				$course_list = '';
				$first = true;
				$query_course =	"SELECT code, name"
								." FROM %lms_course"
								." WHERE idCourse IN "
								."("
								."SELECT idCourse"
								." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
								." WHERE idUser = '".Docebo::user()->getIdST()."'"
								." AND idMetaCertificate = '".$id_meta."'"
								.")";
				$result_course = sql_query($query_course);

				while (list($code, $name) = sql_fetch_row($result_course)) {
					if($first)
						$first = false;
					else
						$course_list .= '<br/>';

					$course_list .= '('.$code.') - '.$name;
				}

				$cont[] = $course_list;

				if ($show_preview) {
					$cont[] =	'<a class="ico-wt-sprite subs_view" href="'.$url->getUrl('op=preview_cert&id_certificate='.$id_certificate.'&idmeta='.$id_meta).'" '
						.' title="'.$lang->def('_PREVIEW').'"><span>'
						.$lang->def('_PREVIEW').'</span></a>';
				}

				$cont[] =	'<a class="ico-wt-sprite subs_pdf" href="'.$url->getUrl('op=release_cert&id_certificate='.$id_certificate.'&idmeta='.$id_meta).'" '
					.' title="'.$lang->def('_NEW_CERTIFICATE').'"><span>'
					.$lang->def('_NEW_CERTIFICATE').'</span></a>';

				$tb_meta_cert->addBody($cont);

			} else {
				$av_meta_cert--;
			}
		}
	}

	if ($av_meta_cert) {
		$html_meta .= $tb_meta_cert->getTable().'<br/><br/>';
	} else {
		//$is_filtering = Get::req('is_filtering_meta', DOTY_INT, 0);
		//$html_meta .= '<p>'.($is_filtering>0 ? $html_filter_meta : '').$lang->def('_NO_CONTENT').'</p>';
		$html_meta .= '<p>'.$lang->def('_NO_CONTENT').'</p>';
	}

 //-----------------------------------------------------------------------------

	$selected_tab = Get::req('current_tab', DOTY_STRING, 'cert');
	$html .= '<div id="mycertificate_tabs" class="yui-navset">
			<ul class="yui-nav">
					<li'.($selected_tab == 'cert' ? ' class="selected"' : '').'><a href="#cert"><em>'.Lang::t('_CERTIFICATE', 'menu').'</em></a></li>
					<li'.($selected_tab == 'meta' ? ' class="selected"' : '').'><a href="#meta"><em>'.Lang::t('_TITLE_META_CERTIFICATE', 'certificate').'</em></a></li>
			</ul>
			<div class="yui-content">
					<div>'.$html_cert.'</div>
					<div>'.$html_meta.'</div>
			</div>
		</div>';

	$html .= '</div>'; //close std_block div

	cout($html, 'content');

	YuiLib::load('tabs');
	cout('<script type="text/javascript">var myTabs = new YAHOO.widget.TabView("mycertificate_tabs");</script>', 'scripts');

}

function preview_cert(&$url) {
	checkPerm('view');

	$id_certificate = importVar('id_certificate', true, 0);
	$id_course = importVar('id_course', true, 0);
	$id_meta = Get::req('idmeta', DOTY_INT, 0);

	$cert = new Certificate();
	$subs = $cert->getSubstitutionArray(Docebo::user()->getIdST(), $id_course, $id_meta);
	$cert->send_facsimile_certificate($id_certificate, Docebo::user()->getIdST(), $id_course, $subs);
}

function release_cert(&$url) {
	checkPerm('view');

	$id_certificate = importVar('id_certificate', true, 0);
	$id_course = importVar('id_course', true, 0);
	$id_meta = Get::req('idmeta', DOTY_INT, 0);

	$cert = new Certificate();
	$subs = $cert->getSubstitutionArray(Docebo::user()->getIdST(), $id_course, $id_meta);
	$cert->send_certificate($id_certificate, Docebo::user()->getIdST(), $id_course, $subs);
}

// ================================================================================

function mycertificateDispatch($op) {

	require_once($GLOBALS['where_lms'].'/lib/lib.certificate.php');

	require_once(_base_.'/lib/lib.urlmanager.php');
	$url =& UrlManager::getInstance('mycertificate');
	$url->setStdQuery('modname=mycertificate&op=mycertificate');

	switch($op) {
		case "preview_cert" : {
			preview_cert($url);
		};break;
		case "release_cert" : {
			release_cert($url);
		};break;

		case "mycertificate" :
		default : {
			mycertificate($url);
		}
	}

}

?>