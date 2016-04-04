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
// TODO: obsolete
function mycertificate(&$url) {
	checkPerm('view');

	require_once(_base_.'/lib/lib.table.php');

	$lang =& DoceboLanguage::createInstance('profile', 'framework');
	$lang =& DoceboLanguage::createInstance('course', 'lms');
	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

  $title = $lang->def('_MY_CERTIFICATE', 'certificate');
	$html = getTitleArea($title, 'mycertificate')
		.'<div class="std_block">';

	$cert = new Certificate();
        $show_preview = true;

	/*
	 * Print certificates tables, subdivided by year and course type
	 */

	$html_cert = '';

	$cont_h = array (
		$lang->def('_YEAR', 'standard'),
		$lang->def('_COURSE_CODE', 'course'),
		$lang->def('_COURSE', 'course'),
		$lang->def('_CERTIFICATE_NAME', 'course'),
		$lang->def('_DATE_END', 'course')
	);
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
	);
	if ($show_preview) $type_h[] = 'nowarp';
	$type_h[] = 'nowarp';

			$tb = new Table(0);
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);

	$assignment = $cert->getAssignment(array('id_user' => Docebo::user()->getIdSt()));
					
        foreach($assignment AS $row){
            switch ($row['available_for_status']) {
                case 3: $year = substr($row['date_end'], 0, 4); break;
                case 2: $year = substr($row['date_begin'], 0, 4); break;
                case 1: $year = substr($row['date_inscr'], 0, 4); break;
                default: $year = '-';
                                        }
					
					$cont = array();
            $cont[] = $year;
            $cont[] = $row['code'];
            $cont[] = $row['course_name'];
            $cont[] = $row['cert_name'];
            $cont[] = $row['date_complete'];

						if ($show_preview) {
                isset($row['on_date'])
                    ? $cont[] = ''
                    : $cont[] = '<a class="ico-wt-sprite subs_view" href="'.$url->getUrl('op=preview_cert&id_certificate='.$row['id_certificate'].'&id_course='.$row['id_course']).'" '
                                                            .' title="'.$lang->def('_PREVIEW').'"><span>'.$lang->def('_PREVIEW').'</span></a>';
            }
            isset($row['on_date'])
                ? $cont[] = '<a class="ico-wt-sprite subs_pdf" href="'.$url->getUrl('op=release_cert&id_certificate='.$row['id_certificate'].'&id_course='.$row['id_course']).'" '
                                                    .' title="'.$lang->def('_DOWNLOAD').'"><span>'.$lang->def('_DOWNLOAD').'</span></a>'
                : $cont[] = '<a class="ico-wt-sprite subs_pdf" href="'.$url->getUrl('op=release_cert&id_certificate='.$row['id_certificate'].'&id_course='.$row['id_course']).'" '
                                                    .' title="'.$lang->def('_GENERATE').'"><span>'.$lang->def('_GENERATE').'</span></a>';

					$tb->addBody($cont);
			}

				$html_cert .= '<h2 class="mycertificate_title">'.$arr_course_types[$course_type].'</h2>';
				$html_cert .= $tb->getTable();

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

			$cont[] =	'<a class="ico-wt-sprite subs_pdf" href="'.$url->getUrl('op=release_cert&id_certificate='.$id_certificate.'&id_meta='.$id_meta).'" '
				.' title="'.$lang->def('_DOWNLOAD').'"><span>'
				.$lang->def('_DOWNLOAD').'</span></a>';

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
					$cont[] =	'<a class="ico-wt-sprite subs_view" href="'.$url->getUrl('op=preview_cert&id_certificate='.$id_certificate.'&id_meta='.$id_meta).'" '
						.' title="'.$lang->def('_PREVIEW').'"><span>'
						.$lang->def('_PREVIEW').'</span></a>';
				}

				$cont[] =	'<a class="ico-wt-sprite subs_pdf" href="'.$url->getUrl('op=release_cert&id_certificate='.$id_certificate.'&id_meta='.$id_meta).'" '
					.' title="'.$lang->def('_GENERATE').'"><span>'
					.$lang->def('_GENERATE').'</span></a>';

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
	checkPerm('obsolete');

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