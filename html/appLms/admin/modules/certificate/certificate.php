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

define('IS_META', 0);

if(Docebo::user()->isAnonymous()) die("You can't access");

function certificate() {
    checkPerm('view');

    require_once(_base_.'/lib/lib.form.php');
    require_once(_base_.'/lib/lib.table.php');

    $mod_perm	= checkPerm('mod', true);
    // create a language istance for module admin_certificate
    $lang 		=& DoceboLanguage::createInstance('certificate', 'lms');
    $out 		=& $GLOBALS['page'];
    $out->setWorkingZone('content');

    $tb	= new Table(Get::sett('visuItem'), $lang->def('_CERTIFICATE_CAPTION'), $lang->def('_CERTIFICATE_SUMMARY'));
    $tb->initNavBar('ini', 'link');
    $tb->setLink("index.php?modname=certificate&amp;op=certificate");
    $ini=$tb->getSelectedElement();

    $form = new Form();

    if (isset($_POST['filter_reset'])) unset($_POST['filter_text']);

    //search query of certificates
    $query_certificate = "
	SELECT id_certificate, code, name, description
	FROM ".$GLOBALS['prefix_lms']."_certificate"
        ." WHERE meta = 0";
    if (isset($_POST['filter_text'])) {
        $query_certificate .= " AND (name LIKE '%".$_POST['filter_text']."%'".
            " OR code LIKE '%".$_POST['filter_text']."%')";
    }
    $query_certificate .= " ORDER BY id_certificate
	LIMIT $ini,".Get::sett('visuItem');

    $query_certificate_tot = "
	SELECT COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_certificate";

    $re_certificate = sql_query($query_certificate);
    list($tot_certificate) = sql_fetch_row(sql_query($query_certificate_tot));

    $type_h = array('', '', '');
    $cont_h	= array(
        $lang->def('_CODE'),
        $lang->def('_NAME'),
        $lang->def('_DESCRIPTION')
    );
    if($mod_perm) {
        $cont_h[] = $lang->def( '_TEMPLATE' );
        $type_h[] = 'image';
    }

    $cont_h[] = Get::sprite('subs_view', Lang::t('_PREVIEW', 'certificate'));
    $type_h[] = 'image';

    if($mod_perm) {

        $cont_h[] =Get::sprite('subs_print', Lang::t('_CERTIFICATE_VIEW_CAPTION', 'certificate'));
        $type_h[] =	'image';

        $cont_h[] = Get::sprite('subs_mod', Lang::t('_MOD', 'certificate'));
        $type_h[] = 'image';

        $cont_h[] = Get::sprite('subs_del', Lang::t('_DEL', 'certificate'));
        $type_h[] = 'image';
    }

    $tb->setColsStyle($type_h);
    $tb->addHead($cont_h);
    while(list($id_certificate, $code, $name, $descr) = sql_fetch_row($re_certificate))
    {
        $title = strip_tags($name);
        $cont = array(
            $code,
            $name,
            Util::cut($descr)
        );
        if($mod_perm) {

            $cont[] = '<a href="index.php?modname=certificate&amp;op=elemcertificate&amp;id_certificate='.$id_certificate.'" title="'.Lang::t('_TEMPLATE', 'certificate').'">'
                .Lang::t('_TEMPLATE', 'certificate').'</a>';
        }

        $cont[] = Get::sprite_link('subs_view', 'index.php?modname=certificate&amp;op=preview&amp;id_certificate='.$id_certificate, Lang::t('_PREVIEW', 'certificate'));

        if($mod_perm) {

            $cont[] = Get::sprite_link('subs_print',
                'index.php?modname=certificate&amp;op=report_certificate&amp;id_certificate='.$id_certificate,
                Lang::t('_CERTIFICATE_VIEW_CAPTION', 'certificate'));
            $cont[] = Get::sprite_link('subs_mod',
                'index.php?modname=certificate&amp;op=modcertificate&amp;id_certificate='.$id_certificate,
                Lang::t('_MOD', 'certificate'));
            $cont[] = Get::sprite_link('subs_del',
                'index.php?modname=certificate&amp;op=delcertificate&amp;id_certificate='.$id_certificate,
                Lang::t('_DEL', 'certificate'));
        }
        $tb->addBody($cont);
    }

    require_once(_base_.'/lib/lib.dialog.php');
    setupHrefDialogBox('a[href*=delcertificate]');

    if($mod_perm) {
        $tb->addActionAdd('<a class="ico-wt-sprite subs_add" href="index.php?modname=certificate&amp;op=addcertificate" title="'.$lang->def('_ADD').'">'
            .'<span>'.$lang->def('_ADD').'</span></a>');
    }

    $out->add(getTitleArea($lang->def('_TITLE_CERTIFICATE'), 'certificate')
        .'<div class="std_block">'
        .$form->openForm('certificate_filter', 'index.php?modname=certificate&amp;op=certificate')
        .'<div class="quick_search_form" style="float: none;">
			<div>
				<div class="simple_search_box">'
        .Form::getInputTextfield("search_t", "filter_text", "filter_text", Get::req('filter_text', DOTY_MIXED, ''), '', 255, '' )
        .Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b")
        .Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b")
        .'</div>
			</div>
		</div>'
        .$form->closeForm());
    if(isset($_GET['result'])) {
        switch($_GET['result']) {
            case "ok" 		: $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));break;
            case "err" 		: $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
            case "err_del" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
        }
    }

    $out->add($tb->getTable().$tb->getNavBar($ini, $tot_certificate).'</div>');
}


function list_element_certificate() {
    checkPerm('view');

    require_once(_base_.'/lib/lib.form.php');
    require_once(_base_.'/lib/lib.table.php');

    $mod_perm		= checkPerm('mod', true);
    $id_certificate = importVar('id_certificate', true);

    // create a language istance for module admin_certificate
    $lang 		=& DoceboLanguage::createInstance('certificate', 'lms');
    $out 		=& $GLOBALS['page'];
    $out->setWorkingZone('content');
    $form		= new Form();

    $page_title = array(
        'index.php?modname=certificate&amp;op=certificate' => $lang->def('_TITLE_CERTIFICATE'),
        $lang->def('_STRUCTURE_CERTIFICATE')
    );

    $out->add(getTitleArea($page_title, 'certificate')
        .'<div class="std_block">'
        .getBackUi( 'index.php?modname=certificate&amp;op=certificate', $lang->def('_BACK') )
    );

    if(isset($_GET['result'])) {
        switch($_GET['result']) {
            case "ok" 		: $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));break;
            case "err" 		: $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
            case "err_del" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
        }
    }

    $query_structure = "
	SELECT cert_structure, orientation, bgimage
	FROM ".$GLOBALS['prefix_lms']."_certificate 
	WHERE id_certificate = '".(int)$id_certificate."'";

    list($structure, $orientation, $bgimage) = sql_fetch_row(sql_query($query_structure));

    $out->add('<div class="std_block">'	);

    // $out->add( getInfoUi($lang->def('_CERTIFICATE_WARNING')) );

    $out->add($form->openForm('structure_certificate', 'index.php?modname=certificate&amp;op=savecertificate', false, false, 'multipart/form-data'));
    $out->add($form->openElementSpace()

        .$form->getTextarea ($lang->def('_STRUCTURE_CERTIFICATE'), 'structure', 'structure', $structure)
        .'<p><b>'.$lang->def('_ORIENTATION').'</b></p>'
        .$form->getRadio($lang->def('_PORTRAIT'), 'portrait', 'orientation', 'P', ($orientation == 'P'))
        .$form->getRadio($lang->def('_LANDSCAPE'), 'landscape', 'orientation', 'L', ($orientation == 'L'))

        .$form->getExtendedFilefield(	$lang->def('_BACK_IMAGE'),
            'bgimage',
            'bgimage',
            $bgimage)

        .$form->closeElementSpace()
        .$form->openButtonSpace()
        .$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
        .$form->getButton('save_structure', 'save_structure', ($lang->def('_SAVE') ) )
        .$form->getButton('undo', 'undo', $lang->def('_UNDO'))
        .$form->closeButtonSpace()
        .$form->closeForm());


    $tb = new Table(0, $lang->def('_TAG_LIST_CAPTION'), $lang->def('_TAG_LIST_SUMMARY'));

    $tb->setColsStyle(array('', ''));
    $tb->addHead(array($lang->def('_TAG_CODE'), $lang->def('_TAG_DESCRIPTION')));

    //search query of certificates tag
    $query_format_tag = "
	SELECT file_name, class_name 
	FROM ".$GLOBALS['prefix_lms']."_certificate_tags ";
    $re_certificate_tags = sql_query($query_format_tag);
    while(list($file_name, $class_name) = sql_fetch_row($re_certificate_tags)) {

        if(file_exists($GLOBALS['where_lms'].'/lib/certificate/'.$file_name)) {

            require_once($GLOBALS['where_lms'].'/lib/certificate/'.$file_name);
            $instance = new $class_name(0, 0);
            $this_subs = $instance->getSubstitutionTags();
            foreach($this_subs as $tag => $description) {

                $tb->addBody(array($tag, $description));
            } // end foreach
        } // end if
    }
    $out->add($tb->getTable());

    $out->add('</div>');
}

function manageCertificateFile($new_file_id, $old_file, $path, $delete_old, $is_image = false) {
    require_once(_base_.'/lib/lib.upload.php');
    $arr_new_file = ( isset($_FILES[$new_file_id]) && $_FILES[$new_file_id]['tmp_name'] != '' ? $_FILES[$new_file_id] : false );
    $return = array(	'filename' => $old_file,
        'new_size' => 0,
        'old_size' => 0,
        'error' => false,
        'quota_exceeded' => false);
    sl_open_fileoperations();
    if(($delete_old || $arr_new_file !== false) && $old_file != '')
        sl_unlink($path.$old_file);// the flag for file delete is checked or a new file was uploaded ---------------------

    if(!empty($arr_new_file))
    {
        // if present load the new file --------------------------------------------------------
        $filename = $new_file_id.'_'.mt_rand(0, 100).'_'.time().'_'.$arr_new_file['name'];

        if(!sl_upload($arr_new_file['tmp_name'], $path.$filename))
            return false;
        else
            return $filename;
    }
    sl_close_fileoperations();
    return '';
}

function editcertificate($load = false) {
    checkPerm('mod');

    require_once(_base_.'/lib/lib.form.php');

    $lang 		=& DoceboLanguage::createInstance('certificate', 'lms');
    $form		= new Form();
    $out 		=& $GLOBALS['page'];
    $out->setWorkingZone('content');

    $id_certificate = importVar('id_certificate', true, 0);
    $all_languages 	= Docebo::langManager()->getAllLanguages();
    $languages = array();
    foreach($all_languages as $k => $v) { $languages[$v[0]] = $v[1]; }

    if($load) {

        $query_certificate = "
		SELECT code, name, base_language, description, user_release
		FROM ".$GLOBALS['prefix_lms']."_certificate
		WHERE id_certificate = '".$id_certificate."'";
        list($code, $name, $base_language, $descr, $user_release) = sql_fetch_row(sql_query($query_certificate));
    } else {

        $code = '';
        $name 	= '';
        $descr 	= '';
        $user_release = 1;
        $base_language = getLanguage();
    }

    $page_title = array(
        'index.php?modname=certificate&amp;op=certificate' => $lang->def('_TITLE_CERTIFICATE'),
        ( $load ? $lang->def('_MOD') : $lang->def('_NEW_CERTIFICATE') )
    );
    $out->add(getTitleArea($page_title, 'certificate')
        .'<div class="std_block">'
        .getBackUi( 'index.php?modname=certificate&amp;op=certificate', $lang->def('_BACK') )

        .$form->openForm('adviceform', 'index.php?modname=certificate&amp;op=savecertificate')
    );
    if($load) {

        $out->add($form->getHidden('id_certificate', 'id_certificate', $id_certificate)
            .$form->getHidden('load', 'load', 1)	);
    }
    $out->add(
        $form->openElementSpace()
        .$form->getTextfield($lang->def('_CODE'), 'code', 'code', 255, $code)
        .$form->getTextfield($lang->def('_NAME'), 'name', 'name', 255, $name)

        .Form::getDropdown( $lang->def('_BASE_LANGUAGE'),
            'base_language',
            'base_language',
            $languages,
            $base_language)

        .$form->getCheckbox($lang->def('_USER_RELEASE'), 'user_release', 'user_release', '1', $user_release)

        .$form->getTextarea($lang->def('_DESCRIPTION'), 'descr', 'descr', $descr)
        .$form->closeElementSpace()
        .$form->openButtonSpace()
        .$form->getButton('certificate', 'certificate', ( $load ? $lang->def('_SAVE') : $lang->def('_INSERT') ) )
        .$form->getButton('undo', 'undo', $lang->def('_UNDO'))
        .$form->closeButtonSpace()
        .$form->closeForm()
        .'</div>'
    );
}

function savecertificate() {
    checkPerm('mod');

    $id_certificate = importVar('id_certificate', true, 0);
    $load 			= importVar('load', true, 0);

    $all_languages 	= Docebo::langManager()->getAllLangCode();
    $lang 			=& DoceboLanguage::createInstance('certificate', 'lms');

    if($_POST['name'] == '') $_POST['name'] = $lang->def('_NOTITLE');

    if(isset($_POST['save_structure'])){

        $path 	= '/appLms/certificate/';
        $path 	= $path.( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

        $bgimage = manageCertificateFile('bgimage',
            $_POST["old_bgimage"],
            $path,
            isset($_POST['file_to_del']['bgimage']) );
        if(!$bgimage) $bgimage = '';

        $query_insert = "
		UPDATE ".$GLOBALS['prefix_lms']."_certificate
		SET	cert_structure = '".$_POST['structure']."',
			orientation = '".$_POST['orientation']."'
			". ( $bgimage != '' && !isset($_POST['file_to_del']['bgimage']) ? " , bgimage = '".$bgimage."'" : '' )."  
			". ( $bgimage == '' && isset($_POST['file_to_del']['bgimage']) ? " , bgimage = ''" : '' )."
		WHERE id_certificate = '".$id_certificate."'";

        if(!sql_query($query_insert)) Util::jump_to('index.php?modname=certificate&op=certificate&result=err');
        Util::jump_to('index.php?modname=certificate&op=certificate&result=ok');
    }
    if($load == 1) {

        $query_insert = "
		UPDATE ".$GLOBALS['prefix_lms']."_certificate
		SET	code = '".$_POST['code']."', 
			name = '".$_POST['name']."',
			base_language = '".$_POST['base_language']."',
			description = '".$_POST['descr']."',
			user_release = '".(isset($_POST['user_release']) ? 1 : 0)."'
		WHERE id_certificate = '".$id_certificate."'";

        if(!sql_query($query_insert)) Util::jump_to('index.php?modname=certificate&op=certificate&result=err');
        Util::jump_to('index.php?modname=certificate&op=certificate&result=ok');
    }
    else
    {
        $query_insert = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_certificate
		( code, name, base_language, description, user_release ) VALUES
		( 	'".$_POST['code']."' ,
			'".$_POST['name']."' ,
		 	'".$_POST['base_language']."' ,
			'".$_POST['descr']."',
			'".(isset($_POST['user_release']) ? 1 : 0)."'
		)";
        if(!sql_query($query_insert)) Util::jump_to('index.php?modname=certificate&op=certificate&result=err');

        list($id_certificate) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
        Util::jump_to('index.php?modname=certificate&op=elemcertificate&id_certificate='.$id_certificate);
    }
}

function delcertificate() {
    checkPerm('mod');

    require_once(_base_.'/lib/lib.form.php');

    $id_certificate 	= Get::req('id_certificate', DOTY_INT, 0);
    $lang 		=& DoceboLanguage::createInstance('certificate', 'lms');

    if(Get::req('confirm', DOTY_INT, 0) == 1) {

        $query_certificate = "
		DELETE FROM ".$GLOBALS['prefix_lms']."_certificate
		WHERE id_certificate = '".$id_certificate."'";
        if(!sql_query($query_certificate)) Util::jump_to('index.php?modname=certificate&op=certificate&result=err_del');
        else Util::jump_to('index.php?modname=certificate&op=certificate&result=ok');
    } else {

        list($name, $descr) = sql_fetch_row(sql_query("
		SELECT name, description
		FROM ".$GLOBALS['prefix_lms']."_certificate
		WHERE id_certificate = '".$id_certificate."'"));

        $form = new Form();
        $page_title = array(
            'index.php?modname=certificate&amp;op=certificate' => $lang->def('_TITLE_CERTIFICATE'),
            $lang->def('_DEL')
        );
        $GLOBALS['page']->add(
            getTitleArea($page_title, 'certificate')
            .'<div class="std_block">'
            .$form->openForm('del_certificate', 'index.php?modname=certificate&amp;op=delcertificate')
            .$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
            .getDeleteUi(	$lang->def('_AREYOUSURE'),
                '<span>'.$lang->def('_NAME').' : </span>'.$name.'<br />'
                .'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$descr,
                false,
                'confirm',
                'undo'	)
            .$form->closeForm()
            .'</div>', 'content');
    }
}

function report_certificate() {
    require_once(_base_.'/lib/lib.form.php');
    require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));
    require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
    require_once(_base_.'/lib/lib.table.php');

    checkPerm('view');

    $out =& $GLOBALS['page'];
    $out->setWorkingZone('content');

    $form = new Form();
    $certificate = new Certificate();

    $lang =& DoceboLanguage::createInstance('certificate', 'lms');

    if (isset($_GET['id_certificate'])) {

        $id_certificate = importVar('id_certificate', true, 0);
        $man_course = new Man_Course();

        $id_course = array();
        $id_course = $certificate->getCourseForCertificate($id_certificate);

        $course_info = array();

        $out->add(
            getTitleArea(array(
                'index.php?modname=certificate&amp;op=certificate' => $lang->def('_TITLE_CERTIFICATE'),
                $lang->def('_COURSES')
            ))
            .'<div class="std_block">'
            .getBackUi('index.php?modname=certificate&amp;op=certificate', $lang->def('_BACK'))
        );

        $tb	= new Table(Get::sett('visuItem'), $lang->def('_CHOOSE_COURSE'), $lang->def('_COURSE_LIST'));

        $type_h = array('', '', 'min-cell');
        $cont_h	= array(
            $lang->def('_CODE'),
            $lang->def('_NAME'),
            $lang->def('_CERTIFICATE_REPORT')
        );

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);
        foreach ($id_course as $course_id) {
            $course_info = $man_course->getCourseInfo($course_id);
            $cont = array(
                $course_info['code'],
                '<a href="index.php?modname=certificate&amp;op=view_report_certificate&amp;id_certificate='.$id_certificate.'&amp;id_course='.$course_id.'">'
                .$course_info['name'].'</a>',
                $certificate->getNumberOfCertificateForCourse($id_certificate, $course_info['idCourse'])
            );
            $tb->addBody($cont);
        }

        $out->add(	$tb->getTable()
            .'<br/>'
            .getBackUi('index.php?modname=certificate&amp;op=certificate', $lang->def('_BACK'))
            .'</div>');
    } else {

        $out->add(
            getTitleArea($lang->def('_CERTIFICATE_REPORT'), 'certificate')
            .'<div class="std_block">'
        );

        if (isset($_POST['toggle_filter']))
        {
            unset($_POST['name_filter']);
            unset($_POST['code_filter']);
        }

        $out->add(
            $form->openForm('certificate_filter', 'index.php?modname=certificate&amp;op=report_certificate')
            .$form->openElementSpace()
            .$form->getTextfield($lang->def('_NAME'), 'name_filter', 'name_filter', '255', (isset($_POST['name_filter']) && $_POST['name_filter']!== '' ? $_POST['name_filter'] : ''))
            .$form->getTextfield($lang->def('_CODE'), 'code_filter', 'code_filter', '255', (isset($_POST['code_filter']) && $_POST['code_filter']!== '' ? $_POST['code_filter'] : ''))
            .$form->closeElementSpace()
            .$form->openButtonSpace()
            .$form->getButton('filter', 'filter', $lang->def('_FILTER'))
            .$form->getButton('toggle_filter', 'toggle_filter', $lang->def('_TOGGLE_FILTER'))
            .$form->closeButtonSpace()
            .$form->closeForm());

        if (isset($_POST['filter']))
        {
            if ($_POST['name_filter'] !== '' && $_POST['code_filter'] !== '')
                $certificate_info = $certificate->getCertificateList($_POST['name_filter'], $_POST['code_filter']);
            elseif ($_POST['name_filter'] !== '')
                $certificate_info = $certificate->getCertificateList($_POST['name_filter']);
            elseif ($_POST['code_filter'] !== '')
                $certificate_info = $certificate->getCertificateList(false, $_POST['code_filter']);
            else
                $certificate_info = $certificate->getCertificateList();
        }
        else
            $certificate_info = $certificate->getCertificateList();

        $tb	= new Table(Get::sett('visuItem'), $lang->def('_CHOOSE_CERTIFICATE'), $lang->def('_CERTIFICATE_LIST'));

        $type_h = array('', '');
        $cont_h	= array(
            $lang->def('_CODE'),
            $lang->def('_NAME'),
            $lang->def('_DESCRIPTION')
        );
        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);
        foreach($certificate_info as $info_certificate) {

            $cont = array(
                $info_certificate[CERT_CODE],
                '<a href="index.php?modname=certificate&amp;op=report_certificate&amp;id_certificate='.$info_certificate[CERT_ID].'">'
                .$info_certificate[CERT_NAME].'</a>',
                $info_certificate[CERT_DESCR]
            );
            $tb->addBody($cont);
        }
        $out->add($tb->getTable().'</div>');
    }
}

function view_report_certificate() {
    checkPerm('view');

    require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));
    require_once(_lms_.'/lib/lib.course.php');
    require_once($GLOBALS['where_lms'].'/lib/lib.track_user.php');

    $out =& $GLOBALS['page'];
    $out->setWorkingZone('content');

    $lang =& DoceboLanguage::createInstance('certificate', 'lms');

    $deletion = importVar('deletion', true, 0);

    if ($deletion)
        switch ($deletion)
        {
            case 1:	$out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));	break;
            case 2:	$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));	break;
            case 3:	$out->add(getErrorUi($lang->def('_OPERATION_FAILURE'))); break;
        }

    $certificate = new Certificate();

    $id_certificate = importVar('id_certificate', true, 0);
    $id_course = importVar('id_course', true, 0);

    $selection = importVar('selection', false, array()); //all possible selected values
    $selected = importVar('selected', false, array()); //effectively selected values with checkbox
    $sel = importVar('old_selection', false, ''); //selected values from previous table pages
    if ($sel != '') $total_selection = explode(',', $sel); else $total_selection = array();

    //update total selection
    foreach ($selection as $key=>$val)
    {
        if (in_array($val, $selected))
        {
            if (!in_array($val, $total_selection))
                $total_selection[] = $val;
        }
        else
        {
            $index = array_search($val, $total_selection);
            if ($index !== false)
                array_splice($total_selection, $index, 1);
        }
    }

    $search_filter = importVar('filter', false, '');
    $only_released = importVar('only_released', true, 0);

    require_once(_adm_.'/lib/user_selector/lib.dynamicuserfilter.php');
    $dyn_filter = new DynamicUserFilter("user_dyn_filter");
    $dyn_filter->init();
    $dyn_conds = $dyn_filter->getConditions(false);
    $idsts = !empty($dyn_conds)?$dyn_filter->getUsers(false):null;
    $dyn_data = $dyn_filter->get(true, true);
    $dyn_data['js_custom'] = '
<script type="text/javascript">YAHOO.util.Event.onDOMReady(function(e) {
    ';
    foreach ($dyn_conds as $dyn_cond) {
        $dyn_data['js_custom'] .= '
        var __field = YAHOO.dynFilter.getFieldById( "' . $dyn_cond['id_field'] . '" );
        
 __filter = YAHOO.dynFilter.oFilterList.add( __field, \'' . $dyn_cond['value'] . '\' );
 
YAHOO.dynFilter.addTableRow(__filter);

';
    }
    $dyn_data['js_custom'] .= '});</script>';

    //which command?
    if (importVar('search_button', false, false) !== false)
    {
    }
    if (importVar('reset_button', false, false) !== false)
    {
        $search_filter = '';
        $only_released = 0;
    }
    if (importVar('print_button', false, false) !== false)
    {
    }

    $numtablerows = Get::sett('visuItem', 25);

    $tb = new Table($numtablerows, $lang->def('_CERTIFICATE_VIEW_CAPTION'), $lang->def('_CERTIFICATE_VIEW_CAPTION'));
    $tb->initNavBar('ini', 'button');
    $ini = $tb->getSelectedElement();

    $ini_param = Get::req('ini', DOTY_MIXED, array());//floor($ini / Get::sett('visuItem', 25));
    if (empty($ini_param)) {
        $ini_param = 1;
    } else {
        list($ini_param) = each($ini_param);
    }


    //apply sub admin filters, if needed
    $userlevelid = Docebo::user()->getUserLevelId();
    $alluser = true;
    if( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous() ) {
        //filter users
        $alluser = false;
        require_once(_base_.'/lib/lib.preference.php');
        $adminManager = new AdminPreference();
        $acl_man = Docebo::user()->getAclManager();
        $admin_users = $adminManager->getAdminUsers(Docebo::user()->getIdST());
    }


    $tc  = $GLOBALS['prefix_lms']."_certificate as c";
    $tca = $GLOBALS['prefix_lms']."_certificate_assign as ca";
    $tcc = $GLOBALS['prefix_lms']."_certificate_course as cc";
    $tcu = $GLOBALS['prefix_lms']."_courseuser as cu";
    $tu = $GLOBALS['prefix_fw']."_user as u";

    $where = "";
    if ($search_filter != '') $where .= " AND (u.userid LIKE '%".$search_filter."%' OR u.lastname LIKE '%".$search_filter."%' OR u.firstname LIKE '%".$search_filter."%') ";

    //list($aval_status) = sql_fetch_row(sql_query("SELECT available_for_status FROM ".$tcc." "
    //	." WHERE id_course='".(int)$id_course."'"));// id_certificate='".(int)$id_certificate."' AND

    list($aval_status, $minutes_required) = sql_fetch_row(sql_query("SELECT available_for_status, minutes_required FROM ".$tcc." "
        ." WHERE id_course='".(int)$id_course."'".($id_certificate != 0 ? " AND id_certificate = ".$id_certificate : "") ));

    if ($minutes_required>0) $where .= " AND ( ca.on_date IS NOT NULL OR ((SELECT SUM((UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)))"
        ." FROM ".$GLOBALS['prefix_lms']."_tracksession WHERE idCourse = cu.idCourse AND idUser = cu.idUser )/60) >= ".$minutes_required.") ";


    if ($only_released > 0) $where = " AND ca.on_date ".($only_released==1 ? "IS NOT" : "IS")." NULL ";//$where .= " AND ".$aval_status." ".($only_released==1 ? "<" : ">=")." cu.status ";
    if( !$alluser ) $where .= " AND cu.idUser IN (".implode(',', $admin_users).")";

    switch($aval_status)
    {
        case AVS_ASSIGN_FOR_ALL_STATUS 		: { $aval_status = " 1 "; };break;
        case AVS_ASSIGN_FOR_STATUS_INCOURSE : { $aval_status = " cu.status = "._CUS_BEGIN." "; };break;
        case AVS_ASSIGN_FOR_STATUS_COMPLETED : { $aval_status = " cu.status = "._CUS_END." "; };break;
    }

    $dynUserFiltersCondition = is_array($idsts)? " AND u.idst IN (" . implode(',', $idsts) . ")" : "";

    list($totalrows) = sql_fetch_row(sql_query("SELECT COUNT(*) "
        ." FROM ( ".$tu." JOIN ".$tcu." ON (u.idst = cu.idUser) )  "
        ." JOIN ".$tcc." ON cc.id_course = cu.idCourse "
        ." JOIN ".$tc." ON c.id_certificate = cc.id_certificate"
        ." LEFT JOIN ".$tca." ON ( ca.id_course = cu.idCourse AND ca.id_user=cu.idUser AND ca.id_certificate = cc.id_certificate ) "
        ." LEFT JOIN (SELECT iduser, idcourse, SUM( (UNIX_TIMESTAMP( lastTime ) - UNIX_TIMESTAMP( enterTime ) ) ) elapsed from learning_tracksession group by iduser, idcourse) t_elapsed on t_elapsed.idcourse=cu.idCourse and cu.idUser = t_elapsed.idUser "
        ." WHERE 1 "
        ." AND ".$aval_status." "
        ." AND coalesce(elapsed,0) >= coalesce(cc.minutes_required,0)*60 "
        . ($id_certificate != 0 ? " AND cc.id_certificate = ".$id_certificate : "")
        ." AND cu.idCourse='".(int)$id_course."' ".$where
        . $dynUserFiltersCondition));

    $query = "SELECT u.userid, u.firstname, u.lastname, cu.date_complete, ca.on_date, cu.idUser as id_user, cu.status , cu.idCourse, cc.id_certificate, c.name as name_certificate"
        ." FROM ( ".$tu." JOIN ".$tcu." ON (u.idst = cu.idUser) ) "
        ." JOIN ".$tcc." ON cc.id_course = cu.idCourse "
        ." JOIN ".$tc." ON c.id_certificate = cc.id_certificate"
        ." LEFT JOIN ".$tca." ON ( ca.id_course = cu.idCourse AND ca.id_user=cu.idUser AND ca.id_certificate = cc.id_certificate ) "
        ." LEFT JOIN (SELECT iduser, idcourse, SUM( (UNIX_TIMESTAMP( lastTime ) - UNIX_TIMESTAMP( enterTime ) ) ) elapsed from learning_tracksession group by iduser, idcourse) t_elapsed on t_elapsed.idcourse=cu.idCourse and cu.idUser = t_elapsed.idUser "
        ." WHERE 1 "
        ." AND ".$aval_status." "
        . ($id_certificate != 0 ? " AND cc.id_certificate = ".$id_certificate : "")
        ." AND coalesce(elapsed,0) >= coalesce(cc.minutes_required,0)*60 "
        ." AND cu.idCourse='".(int)$id_course."' ".$where
        . $dynUserFiltersCondition
        ." ORDER BY u.userid, c.name LIMIT ".$ini.", ".$numtablerows;
//cout('<pre>'.print_r($_POST, true).'</pre>');
//cout($query);
    $res = sql_query($query);

    $from = Get::req('from', DOTY_MIXED, '');
    if($from === 'course')
    {
        $back_ui = getBackUi('index.php?r=alms/course/certificate&amp;id_course='.(int)$id_course, $lang->def('_BACK'));

        $out->add(getTitleArea(array(
            'index.php?r=alms/course/certificate&amp;id_course='.(int)$id_course => $lang->def('_CERTIFICATE_ASSIGN_STATUS', 'course'),
            $lang->def('_COURSES')), 'certificate'));
    }
    elseif ($from === 'courselist')
    {
        $back_ui = getBackUi('index.php?r=alms/course/show', $lang->def('_BACK'));

        $out->add(getTitleArea(array(
            'index.php?r=alms/course/show' => $lang->def('_CERTIFICATE_ASSIGN_STATUS', 'course'),
            $lang->def('_COURSES')), 'certificate'));
    }
    else
    {
        $back_ui = getBackUi('index.php?modname=certificate&amp;op=report_certificate&amp;id_certificate='.(int)$id_certificate, $lang->def('_BACK'));

        $out->add(getTitleArea(array(
            'index.php?modname=certificate&amp;op=certificate' => $lang->def('_TITLE_CERTIFICATE'),
            'index.php?modname=certificate&amp;op=report_certificate&amp;id_certificate='.(int)$id_certificate => $lang->def('_COURSES'),
            $lang->def('_CERTIFICATE_VIEW_CAPTION')), 'certificate'));
    }
    $out->add('<div class="std_block">'.$back_ui);

    $numrows = sql_num_rows($res);
    $downloadables = array();


    $clang =& DoceboLanguage::CreateInstance('course', 'lms');

    $type_h = array('image', '', '', '', '', '', '', 'image', 'image');
    $cont_h = array('',
        $lang->def('_USERNAME'),
        $lang->def('_LASTNAME'),
        $lang->def('_FIRSTNAME'),
        $clang->def('_STATUS'),
        $clang->def('_CERTIFICATE_REPORT', 'certificate'),
        $lang->def('_DATE'),
        $lang->def('_RELASE_DATE'),
        Get::sprite('subs_pdf', Lang::t('_TITLE_VIEW_CERT', 'certificate')),
        Get::sprite('subs_del', Lang::t('_DEL', 'certificate'))
    );

    $tb->setColsStyle($type_h);
    $tb->addHead($cont_h);

    $acl_man =& $GLOBALS['current_user']->getAclManager();
    $arr_status = array(
        _CUS_CONFIRMED 		=> $clang->def('_USER_STATUS_CONFIRMED'),//, 'subscribe', 'lms'),
        _CUS_SUBSCRIBED 	=> $clang->def('_USER_STATUS_SUBS'),//, 'subscribe', 'lms'),//_USER_STATUS_SUBS(?)
        _CUS_BEGIN 			=> $clang->def('_USER_STATUS_BEGIN'),//, 'subscribe', 'lms'),
        _CUS_END 			=> $clang->def('_USER_STATUS_END'),//, 'subscribe', 'lms'),
        _CUS_SUSPEND 		=> $clang->def('_USER_STATUS_SUSPEND')//, 'subscribe', 'lms')
    );

    //foreach($report_info as $info_report)
    while ($info = sql_fetch_assoc($res)) {

        $cont = array();

        $can_assign = (bool)($info['on_date']=='');
        $input_id = $info['id_user'];

        $sel_cell_content = '';
        $label_open = '';
        $label_close = '';
        if(true || $can_assign)
        {
            $input = '<input type="hidden" id="selection_'.$input_id.'" name="selection['.$input_id.']" value="'.$input_id.'"/> ';
            if(in_array($input_id, $total_selection)) $checked = ' checked="checked"'; else $checked = '';
            $sel_cell_content .= $input.'<input type="checkbox" id_certificate="'.$info['id_certificate'].'" id="selected_'.$input_id.'" name="selected['.$input_id.']" value="'.$input_id.'"'.$checked.'/>';

            $label_open = '<label for="selected_'.$input_id.'">';
            $label_close = '</label>';
        }

        $userid = $acl_man->relativeId($info['userid']);

        $cont[] = $sel_cell_content;
        $cont[] = $label_open.($search_filter!='' ? highlightText($userid, $search_filter) : $userid).$label_close;
        $cont[] = $label_open.($search_filter!='' ? highlightText($info['lastname'], $search_filter) : $info['lastname']).$label_close;
        $cont[] = $label_open.($search_filter!='' ? highlightText($info['firstname'], $search_filter) : $info['firstname']).$label_close;
        $cont[] = $arr_status[$info['status']];
        $cont[] = $info['name_certificate'];
        $cont[] = $info['date_complete'];
        $cont[] = $info['on_date'];

        $url = 'index.php?modname=certificate&amp;certificate_id='.$info['id_certificate'].'&amp;course_id='.$id_course.'&amp;user_id='.$info['id_user'];

        $dl_url = $url."&amp;op=send_certificate";
        if($can_assign) $downloadables[] = 'dl_single_'.$input_id.'_'.$info['id_certificate'];
        $cont[] = ($can_assign
            ? '<a id="dl_single_'.$input_id.'_'.$info['id_certificate'].'" class="ico-wt-sprite subs_pdf" href="javascript:;" title="'.$lang->def('_GENERATE').'"><span>'.$lang->def('_GENERATE').'</span></a>'
            //? '<a id="dl_single_'.$input_id.'_'.$info['id_certificate'].'" class="ico-wt-sprite subs_pdf" href="javascript:gen_cert('.$info['id_certificate'].');" title="'.$lang->def('_GENERATE').'"><span>'.$lang->def('_GENERATE').'</span></a>'
            : ($info['on_date'] != ''?'<a id="dl_single_'.$input_id.'_'.$info['id_certificate'].'" class="ico-wt-sprite subs_pdf" href="'.$dl_url.'" title="'.$lang->def('_DOWNLOAD').'"><span>'.$lang->def('_DOWNLOAD').'</span></a>':'')
        );
        $cont[] = ($info['on_date'] == '' ? '' : Get::sprite_link('subs_del', $url.'&amp;op=del_report_certificate&amp;from='.$from, Lang::t('_DEL', 'certificate')) );

        $tb->addBody($cont);
    }

    require_once(_base_.'/lib/lib.dialog.php');
    setupHrefDialogBox('a[href*=del_report_certificate]');

    //search filter ...
    $release_options = array(
        $lang->def('_ALL') => 0,
        $lang->def('_RELEASED') => 1,
        $lang->def('_TO_RELEASE') => 2
    );

    //search and store all non-available certificates
    $all_selection = array();
    $query = "SELECT cu.idUser as id_user "
        ." FROM ( ".$tu." JOIN ".$tcu." ON (u.idst = cu.idUser) )  "
        ." JOIN ".$tcc." ON cc.id_course = cu.idCourse "
        ." JOIN ".$tc." ON c.id_certificate = cc.id_certificate"
        ." LEFT JOIN ".$tca." ON ( ca.id_course = cu.idCourse AND ca.id_user=cu.idUser  AND ca.id_certificate = cc.id_certificate  ) "
        ." WHERE 1 "
        ." AND ".$aval_status." "
        . ($id_certificate != 0 ? " AND cc.id_certificate = ".$id_certificate : "")
        ." AND cu.idCourse='".(int)$id_course."' "
        .($search_filter != '' ? " AND (u.userid LIKE '%".$search_filter."%' OR u.lastname LIKE '%".$search_filter."%' OR u.firstname LIKE '%".$search_filter."%') " : "")
        ." AND ca.on_date IS NULL ";
    $res = sql_query($query);
    while (list($id_user) = sql_fetch_row($res)) {
        $all_selection[] = $id_user;
    }
    //---

    $form = new Form();
    $submit_url = "index.php?modname=certificate&amp;op=view_report_certificate&amp;id_certificate=".(int)$id_certificate."&amp;id_course=".(int)$id_course.'&amp;from='.$from;

    $out->add($form->openForm("certificates_emission", $submit_url)
        .$form->getHidden('old_selection', 'old_selection', implode(',', $total_selection))
        .$form->getHidden('all_selection', 'all_selection', implode(',', $all_selection))

        .$form->getHidden('active_text_filter', 'active_text_filter', $search_filter)
        .$form->getHidden('active_only_released', 'active_only_released', $only_released)
        .$form->getHidden('active_ini', 'active_ini', $ini_param)
        .$form->getHidden('from', 'from', $from)

        .'<div class="quick_search_form" style="float: none;">'

        .'<div class="simple_search_box">'
        .Form::getInputTextfield("search_t", "filter", "filter", $search_filter, '', 255, '' )
        .Form::getButton("search_button", "search_button", Lang::t('_SEARCH', 'standard'), "search_b")
        .Form::getButton("reset_button", "reset_button", Lang::t('_RESET', 'standard'), "reset_b")
        .'</div>'
        .'<a id="advanced_search" class="advanced_search" href="javascript:;">'
        .Lang::t("_ADVANCED_SEARCH", 'standard')
        .'</a>'
        .'<div id="certificate_release_as" class="advanced_search_options" style="display: '.( $only_released != 0 || is_array($idsts) ? 'block' : 'none' ).'">'
        .$dyn_data['html']
        .$dyn_data['js']
        .$dyn_data['js_custom']
        .Form::openButtonSpace()
        .Form::getButton('apply_dyn_filter', 'apply_dyn_filter', Lang::t('_SEARCH', 'admin_directory'))
        .' '
        .Form::getButton('reset_dyn_filter', 'reset_dyn_filter', Lang::t('_RESET', 'admin_directory'))
        .Form::closeButtonSpace()
        .Form::getRadioHoriz(Lang::t('_FILTER', 'certificate'), 'only_released', 'only_released', $release_options, $only_released)
        .'</div>'

        .'</div>'

        .'<script type="text/javascript">'
        .'YAHOO.util.Event.addListener("advanced_search", "click", function(e){
            var el = YAHOO.util.Dom.get("certificate_release_as");
            if (el.style.display != \'block\') el.style.display = \'block\';
            else el.style.display = \'none\';
        });'
        .'</script>'
        , 'content');

    //$delgen_url = 'index.php?modname=certificate&amp;op=del_gen_certificate'
    //	.'&amp;id_certificate='.(int)$id_certificate.'&amp;id_course='.(int)$id_course;
    //$delgen_txt = Lang::t('_DELETE_ALL_GENERATED', 'certificate');

    $print_button_1 = '<div>'
        .'<a id="print_selected_button_1" href="javascript:;">'
        .Get::img('course/certificate.png', Lang::t('_GENERATE_ALL_SELECTED', 'certificate'))
        .Lang::t('_GENERATE_ALL_SELECTED', 'certificate')
        .'</a>'
        .'&nbsp;&nbsp;&nbsp;<a id="download_selected_button_1" href="javascript:;">'
        .Get::img('course/certificate.png', Lang::t('_DOWNLOAD_ALL_SELECTED', 'certificate'))
        .Lang::t('_DOWNLOAD_ALL_SELECTED', 'certificate')
        .'</a>'
        .'</div>';

    $print_button_2 = '<div><a id="print_selected_button_2" href="javascript:;">'
        .Get::img('course/certificate.png', Lang::t('_GENERATE_ALL_SELECTED', 'certificate'))
        .Lang::t('_GENERATE_ALL_SELECTED', 'certificate')
        .'</a>'
        .'</div>';

    $print_button_1 .= Lang::t('_SELECT', 'directory').': ';
    $print_button_2 .= Lang::t('_SELECT', 'directory').': ';

    /*
        $print_button_1 .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" id="select_page_1">'.Lang::t('_SELECT_PAGE', 'standard').'</a>';
        $print_button_2 .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" id="select_page_2">'.Lang::t('_SELECT_PAGE', 'standard').'</a>';
        $print_button_1 .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" id="unselect_page_1">'.Lang::t('_UNSELECT_PAGE', 'standard').'</a>';
        $print_button_2 .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" id="unselect_page_2">'.Lang::t('_UNSELECT_PAGE', 'standard').'</a>';
    */
    $print_button_1 .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" id="select_all_1">'.Lang::t('_ALL_PAGES', 'directory').'</a>';
    $print_button_2 .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" id="select_all_2">'.Lang::t('_ALL_PAGES', 'directory').'</a>';
    $print_button_1 .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" id="unselect_all_1">'.Lang::t('_NONE', 'directory').'</a>';
    $print_button_2 .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" id="unselect_all_2">'.Lang::t('_NONE', 'directory').'</a>';

    $navbar = $tb->getNavBar($ini, $totalrows);

    //if ($from == 'courselist') {
    //	$out->add($navbar.$tb->getTable().$navbar);
    //} else {
    $out->add($print_button_1.'<br />'.$navbar.$tb->getTable().$navbar.'<br />'.$print_button_2);
    //}

    $out->add($form->closeForm());

    $out->add($back_ui.'</div>');

    //addCss('style_menu', 'lms');
    Util::get_js(Get::rel_path('base').'/widget/dialog/dialog.js', true, true);
    Util::get_js(Get::rel_path('lms').'/admin/modules/certificate/certificate.js?rnd='.time(), true, true);
    //addJs($GLOBALS['where_lms_relative'].'/admin/modules/certificate/', 'certificate.js');
    //0='.(int)$id_certificate.'
    $script = 'var ajax_url="ajax.adm_server.php?plf=lms&mn=certificate"; var _STOP="'.$lang->def('_STOP').'"; '
        .'var glob_id_certificate = 0, glob_id_course = '.(int)$id_course.';'
        .'var single_list = ['.(count($downloadables) ? '"'.implode('","', $downloadables).'"' : '').']; '
        .'var reload_url = "'.str_replace('&amp;', '&', (isset(/*$form_url*/$submit_url) ? /*$form_url*/$submit_url : '')).'", '
        .'_ERROR_PARSE = "'.$lang->def('_OPERATION_FAILURE').'", _SUCCESS = "'.$lang->def('_OPERATION_SUCCESSFUL').'", '
        .'_AREYOUSURE="'.Lang::t('_AREYOUSURE', 'standard').'";';

//	$script .= ' function gen_cert(cert_id) {'
//		.'glob_id_certificate = cert_id;'
//		. '}';

    $out->add('<script type="text/javascript">'.$script.'</script>', 'page_head');
}


function del_report_certificate()
{
    checkPerm('view');

    require_once(_base_.'/lib/lib.form.php');
    require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

    $certificate = new Certificate();
    $form = new Form();

    $lang =& DoceboLanguage::createInstance('certificate', 'lms');

    $id_certificate = importVar('certificate_id', true, 0);
    $id_course = importVar('course_id', true, 0);
    $id_user = importVar('user_id', true, 0);
    $from = Get::req('from', DOTY_MIXED, '');

    $certificate_info = array();
    $certificate_info = $certificate->getCertificateInfo($id_certificate);

    $c_infos = $certificate->getInfoForCourseCertificate($id_course, $id_certificate, $id_user);
    $certificate_info = current($c_infos);

    if(Get::req('confirm_del_report_certificate', DOTY_INT, 0) == 1 || (isset($_GET['confirm']) && $_GET['confirm'] == 1))
    {
        require_once(_base_.'/lib/lib.upload.php');
        Get::sett('pathcourse');
        $path = '/appLms/certificate/';
        $deletion_result = true;
        if($certificate_info[CERT_NAME] != '')
            $deletion_result = sl_unlink($path.$certificate_info[ASSIGN_CERT_FILE]);

        if($deletion_result)
        {
            $deletion_result = $certificate->delCertificateForUserInCourse($id_certificate, $id_user, $id_course);
            if ($from == 'courselist') { $id_certificate = 0; }
            if($deletion_result)
                Util::jump_to('index.php?modname=certificate&amp;op=view_report_certificate&id_certificate='.$id_certificate.'&id_course='.$id_course.'&from='.$from.'&deletion=1');
            else
                Util::jump_to('index.php?modname=certificate&amp;op=view_report_certificate&id_certificate='.$id_certificate.'&id_course='.$id_course.'&from='.$from.'&deletion=2');
        }
        else
            Util::jump_to('index.php?modname=certificate&amp;op=view_report_certificate&id_certificate='.$id_certificate.'&id_course='.$id_course.'&from='.$from.'&deletion=3');
    }
    elseif(isset($_POST['undo_del_report_certificate']))
        Util::jump_to('index.php?modname=certificate&amp;op=view_report_certificate&id_certificate='.$id_certificate.'&id_course='.$id_course.'&from='.$from);
    else
    {
        $GLOBALS['page']->add(
            getTitleArea($lang->def('_VIEW_REPORT_DELETION'), 'certificate')
            .'<div class="std_block">'
            .$form->openForm('del_certificate', 'index.php?modname=certificate&amp;op=del_report_certificate&amp;certificate_id='.$id_certificate.'&amp;course_id='.$id_course.'&amp;from='.$from.'&amp;user_id='.$id_user)
            .$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
            .getDeleteUi(	$lang->def('_AREYOUSURE'),
                '<span>'.$lang->def('_NAME').' : </span>'.$certificate_info[$id_certificate][CERT_NAME].'<br />'
                .'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$certificate_info[$id_certificate][CERT_DESCR],
                false,
                'confirm_del_report_certificate',
                'undo_del_report_certificate'	)
            .$form->closeForm()
            .'</div>', 'content');
    }
}


function send_zip_certificates() {
    require_once(_base_.'/addons/pclzip/pclzip.lib.php');
    require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));
    require_once(_base_.'/lib/lib.download.php');

    $files = array();
    $zipName = date("YmdHis").'_certs.zip';
    $list = explode(',', Get::req('list', DOTY_STRING));
    $list_cert = explode(',', Get::req('list_cert', DOTY_STRING));
    $id_course = Get::req('id_course', DOTY_INT, -1);

    $zip = new PclZip('/tmp/'.$zipName);

    foreach ($list_cert as $key => $id_certificate) {
        $id_user = $list[$key];

        $certificate = new Certificate();

        $report_info = $certificate->getInfoForCourseCertificate($id_course, $id_certificate, $id_user);
        $info_report = current($report_info);

        $file = $info_report[ASSIGN_CERT_FILE];
        if ($file == null) continue;

        $sendname = $info_report[ASSIGN_CERT_SENDNAME];
        copy(_files_.'/appLms/certificate/'.$file, _files_.'/tmp/'.$sendname);
        $files[] = '/tmp/'.$sendname;
    }

    $zip->create($files, '', '/tmp/');

    foreach ($files as $fileToRemove) {
        unlink(_files_.$fileToRemove);
    }

    sendFile('/tmp/', $zipName);
}

function send_certificate() {

    require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));
    require_once(_base_.'/lib/lib.download.php');

    $id_certificate = importVar('certificate_id', true, 0);
    $id_course 		= importVar('course_id', true, 0);
    $id_user 		= importVar('user_id', true, 0);

    $certificate = new Certificate();

    $report_info = array();
    $report_info = $certificate->getInfoForCourseCertificate($id_course, $id_certificate, $id_user);
    $info_report = current($report_info);

    $file = $info_report[ASSIGN_CERT_FILE];
    $sendname = $info_report[ASSIGN_CERT_SENDNAME];

    //recognize mime type
    $expFileName = explode('.', $file);
    $totPart = count($expFileName) - 1;

    //send file
    sendFile('/appLms/certificate/', $file, $expFileName[$totPart], $sendname);
}

function print_certificate() {

    require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));
    require_once(_base_.'/lib/lib.download.php' );

    $id_certificate = importVar('certificate_id', true, 0);
    $id_course 		= importVar('course_id', true, 0);
    $id_user 		= importVar('user_id', true, 0);

    $cert = new Certificate();

    $subs = $cert->getSubstitutionArray($id_user, $id_course);
    $cert->send_certificate($id_certificate, $id_user, $id_course, $subs, true);
}

function preview() {
    checkPerm('view');

    require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

    $id_certificate = importVar('id_certificate', true, 0);

    $cert = new Certificate();
    $cert->send_preview_certificate($id_certificate, array());
}

function certificateDispatch($op)
{
    if(isset($_POST['undo'])) $op = 'certificate';
    if(isset($_POST['undo_report'])) $op = 'report_certificate';
    if(isset($_POST['certificate_course_selection'])) $op = 'view_report_certificate';
    if(isset($_POST['certificate_course_selection_back'])) $op = 'report_certificate';
    switch($op) {
        case "certificate":
            certificate();
            break;
        case "addcertificate":
            editcertificate();
            break;
        case "modcertificate":
            editcertificate(true);
            break;
        case "savecertificate":
            savecertificate();
            break;
        case "delcertificate":
            delcertificate();
            break;
        case "elemcertificate":
            list_element_certificate();
            break;
        case "report_certificate":
            report_certificate();
            break;
        case "view_report_certificate":
            view_report_certificate();
            break;
        case "del_report_certificate":
            del_report_certificate();
            break;
        case "del_gen_certificate":
            del_gen_certificate();
            break;
        case "send_certificate":
            send_certificate();
            break;
        case "send_zip_certificates":
            send_zip_certificates();
            break;
        case "print_certificate":
            print_certificate();
            break;
        case "preview":
            preview();
            break;
    }
}