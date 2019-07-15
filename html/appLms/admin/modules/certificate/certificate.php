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

    $mod_perm    = checkPerm('mod', true);
    // create a language istance for module admin_certificate
    $lang         =& DoceboLanguage::createInstance('certificate', 'lms');
    $out         =& $GLOBALS['page'];
    $out->setWorkingZone('content');

    $tb    = new Table(Get::sett('visuItem'), $lang->def('_CERTIFICATE_CAPTION'), $lang->def('_CERTIFICATE_SUMMARY'));
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
    $cont_h    = array(
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
        $type_h[] =    'image';

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

    require_once(Forma::inc(_base_ . '/lib/lib.dialog.php'));
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
            case "ok"         : $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));break;
            case "err"         : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
            case "err_del" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
        }
    }

    $out->add($tb->getTable().$tb->getNavBar($ini, $tot_certificate).'</div>');
}


function list_element_certificate() {
    checkPerm('view');

    require_once(_base_.'/lib/lib.form.php');
    require_once(_base_.'/lib/lib.table.php');

    $mod_perm        = checkPerm('mod', true);
    $id_certificate = importVar('id_certificate', true);

    // create a language istance for module admin_certificate
    $lang         =& DoceboLanguage::createInstance('certificate', 'lms');
    $out         =& $GLOBALS['page'];
    $out->setWorkingZone('content');
    $form        = new Form();

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
            case "ok"         : $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));break;
            case "err"         : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
            case "err_del" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
        }
    }

    $query_structure = "
    SELECT cert_structure, orientation, bgimage
    FROM ".$GLOBALS['prefix_lms']."_certificate 
    WHERE id_certificate = '".(int)$id_certificate."'";

    list($structure, $orientation, $bgimage) = sql_fetch_row(sql_query($query_structure));

    $out->add('<div class="std_block">'    );

    // $out->add( getInfoUi($lang->def('_CERTIFICATE_WARNING')) );

    $out->add($form->openForm('structure_certificate', 'index.php?modname=certificate&amp;op=savecertificate', false, false, 'multipart/form-data'));
    $out->add($form->openElementSpace()

        .$form->getTextarea ($lang->def('_STRUCTURE_CERTIFICATE'), 'structure', 'structure', $structure)
        .'<p><b>'.$lang->def('_ORIENTATION').'</b></p>'
        .$form->getRadio($lang->def('_PORTRAIT'), 'portrait', 'orientation', 'P', ($orientation == 'P'))
        .$form->getRadio($lang->def('_LANDSCAPE'), 'landscape', 'orientation', 'L', ($orientation == 'L'))

        .$form->getExtendedFilefield(    $lang->def('_BACK_IMAGE'),
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
    $return = array(    'filename' => $old_file,
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

    $lang         =& DoceboLanguage::createInstance('certificate', 'lms');
    $form        = new Form();
    $out         =& $GLOBALS['page'];
    $out->setWorkingZone('content');

    $id_certificate = importVar('id_certificate', true, 0);
    $all_languages     = Docebo::langManager()->getAllLanguages();
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
        $name     = '';
        $descr     = '';
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
            .$form->getHidden('load', 'load', 1)    );
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
    $load             = importVar('load', true, 0);

    $all_languages     = Docebo::langManager()->getAllLangCode();
    $lang             =& DoceboLanguage::createInstance('certificate', 'lms');

    if($_POST['name'] == '') $_POST['name'] = $lang->def('_NOTITLE');

    if(isset($_POST['save_structure'])){

        $path     = '/appLms/certificate/';
        $path     = $path.( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

        $bgimage = manageCertificateFile('bgimage',
            $_POST["old_bgimage"],
            $path,
            isset($_POST['file_to_del']['bgimage']) );
        if(!$bgimage) $bgimage = '';

        $query_insert = "
        UPDATE ".$GLOBALS['prefix_lms']."_certificate
        SET    cert_structure = '".$_POST['structure']."',
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
        SET    code = '".$_POST['code']."', 
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
        (     '".$_POST['code']."' ,
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

    $id_certificate     = Get::req('id_certificate', DOTY_INT, 0);
    $lang         =& DoceboLanguage::createInstance('certificate', 'lms');

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
            .getDeleteUi(    $lang->def('_AREYOUSURE'),
                '<span>'.$lang->def('_NAME').' : </span>'.$name.'<br />'
                .'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$descr,
                false,
                'confirm',
                'undo'    )
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

        $tb    = new Table(Get::sett('visuItem'), $lang->def('_CHOOSE_COURSE'), $lang->def('_COURSE_LIST'));

        $type_h = array('', '', 'min-cell');
        $cont_h    = array(
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
                '<a href="index.php?r=alms/course/list_certificate&amp;id_certificate='.$id_certificate.'&amp;id_course='.$course_id.'&amp;from=manage">'
                .$course_info['name'].'</a>',
                $certificate->getNumberOfCertificateForCourse($id_certificate, $course_info['idCourse'])
            );
            $tb->addBody($cont);
        }

        $out->add(    $tb->getTable()
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

        $tb    = new Table(Get::sett('visuItem'), $lang->def('_CHOOSE_CERTIFICATE'), $lang->def('_CERTIFICATE_LIST'));

        $type_h = array('', '');
        $cont_h    = array(
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
                Util::jump_to('index.php?r=alms/course/list_certificate&id_certificate='.$id_certificate.'&id_course='.$id_course.'&from='.$from.'&deletion=1');               
            else
                // to improve
                die('ERROR ON CERTIFICATE DELETION');
        }
        else
            // to improve
            die('ERROR ON CERTIFICATE DELETION');
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
    $id_course         = importVar('course_id', true, 0);
    $id_user         = importVar('user_id', true, 0);

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
    $id_course         = importVar('course_id', true, 0);
    $id_user         = importVar('user_id', true, 0);

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




 function gen_zip_cert(){
    require_once(_base_.'/addons/pclzip/pclzip.lib.php');
    require_once(_base_.'/'._folder_lms_.'/lib/lib.certificate.php');
    require_once(_base_.'/lib/lib.download.php');        
    
    
    $str_rows = importVar('str_rows');
    
    $files = array();
    $zipName = date("YmdHis").'_certs.zip';
   
   
   //die("step-0: ".$str_rows);
   
   
    $zip = new PclZip('/tmp/'.$zipName);
    
    $list_cert = explode(",",$str_rows);
    
    foreach ($list_cert as $key => $vett_users) {
                                          
        
        list($id_user,$id_certificate,$id_course) = explode("-",$vett_users);
      
       
      
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


function certificateDispatch($op)
{
    if(isset($_POST['undo'])) $op = 'certificate';
    if(isset($_POST['undo_report'])) $op = 'report_certificate';
    if(isset($_POST['certificate_course_selection_back'])) $op = 'report_certificate';
    switch($op) {
        case "download_all":
            gen_zip_cert();
            break;
                            
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