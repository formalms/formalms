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

if (!Docebo::user()->isAnonymous()) {

// XXX: additem
    function additem($object_item)
    {
        //checkPerm('view', false, 'storage');
        $lang =& DoceboLanguage::createInstance('item');

        require_once(_base_ . '/lib/lib.form.php');

        $GLOBALS['page']->add(getTitleArea($lang->def('_SECTIONNAME_ITEM'), 'item')
            .'<div class="std_block">'
            .getBackUi( Util::str_replace_once('&', '&amp;', $object_item->back_url).'&amp;create_result=0', $lang->def('_BACK') )

            .Form::openForm('itemform', 'index.php?modname=item&amp;op=insitem', 'std_form', 'post', 'multipart/form-data')
            .Form::openElementSpace()

            .Form::getHidden('back_url', 'back_url', htmlentities(urlencode($object_item->back_url)))
            .Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 100, $lang->def('_TITLE'))
            .Form::getFilefield($lang->def('_FILE'), 'file', 'attach')

            .Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $lang->def('_DESCRIPTION'))
            .Form::closeElementSpace()
            .Form::openButtonSpace()
            .Form::getButton('additem', 'additem', $lang->def('_INSERT'))
            .Form::closeButtonSpace()
            .Form::closeForm()
            .'</div>', 'content');

        //@TODO to enable dropzone uncomment this
        //$GLOBALS['page']->add(\appCore\Template\TwigManager::getInstance()->render('upload-file.html.twig', ['back_url' => $object_item->back_url], _lms_ . '/views/lo'), 'content');
    }

    function insitem()
    {
        //@TODO to enable dropzone uncomment this
        /*
        require_once(_base_ . '/lib/lib.upload.php');
        $response = [];
        $response['status'] = true;
        $back_url = Get::pReq('back_url', DOTY_MIXED, '');
        //checkPerm('view', false, 'storage');

        $idCourse = $_SESSION['idCourse'];

        try {
            $filesInfo = json_decode($_REQUEST['info'], true);
        } catch (JsonException $e) {
            $response['status'] = false;
            $response['errors'][] = Lang::t('_INPUT_IS_NOT_VALID', 'item');
            echo json_encode($response);
            die();
        }
        if (isset($idCourse) && defined("LMS")) {
            $quota = $GLOBALS['course_descriptor']->getQuotaLimit();
            $used = $GLOBALS['course_descriptor']->getUsedSpace();

            $totalSize = 0;
            foreach ($filesInfo as $index => $fileItem) {
                $file = $_FILES['file' . $index];

                $totalSize += Get::dir_size($file['tmp_name']);
            }
            if (Util::exceed_quota('', $quota, $used, $totalSize)) {

                $response['errors']['quota'] = Lang::t('_QUOTA_EXCEDED', 'item');
                echo json_encode($response);
                die();
            }
        }


        $idLessons = [];
        foreach ($filesInfo as $index => $fileItem) {

            $fileIndex = 'file' . $index;
            $error = false;
            if (empty(trim($fileItem['title']))) {
                $response['status'] = false;
                $response['errors']['files'][$fileIndex] = Lang::t('_ITEM_DOES_NOT_HAVE_TITLE', 'item');
                $error = true;
            }

            $file = $_FILES[$fileIndex];

            if (empty($file['name'])) {
                $response['status'] = false;
                $response['errors']['files'][$fileIndex] = Lang::t('_FILE_IS_UNSPECIFIED', 'item');
                $error = true;
            }

            if (!$error) {

                $path = '/' . _folder_lms_ . '/' . Get::sett('pathlesson');
                $savefile = ($idCourse ?? '0') . '_' . random_int(0, 100) . '_' . time() . '_' . $fileItem['title'];
                $savefile = str_replace("'", "\'", $savefile);//Patch file con apostrofo

                if (!file_exists(_files_ . $path . $savefile)) {
                    sl_open_fileoperations();
                    if (!sl_upload($file['tmp_name'], $path . $savefile)) {
                        sl_close_fileoperations();
                        $response['status'] = false;
                        $response['errors']['files'][$fileIndex] = Lang::t('_FILE_ERROR_UPLOAD', 'item');
                    }
                    sl_close_fileoperations();
                } else {
                    $response['status'] = false;
                    $response['errors']['files'][$fileIndex] = Lang::t('_FILE_ERROR_UPLOAD_FILE_EXISTS', 'item');
                }

                $insert_query = "INSERT INTO %lms_materials_lesson  SET author = '" . getLogUserId() . "', title = '" . $fileItem['title'] . "', description = '" . $fileItem['description'] . "', path = '$savefile'";

                if (!sql_query($insert_query)) {
                    sl_unlink($path . $savefile);
                    $response['errors']['files'][$fileIndex] = Lang::t('_FILE_ERROR_UPLOAD_MATERIAL_LESSON_INSER_FAIL', 'item');

                }
                if (isset($_SESSION['idCourse']) && defined("LMS")) $GLOBALS['course_descriptor']->addFileToUsedSpace(_files_ . $path . $savefile);
                list($idLesson) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
                $idLessons[] = $idLesson;
            }
        }

        $response['back_url'] = str_replace('&amp;', '&', $back_url . '&id_los=' . implode(',', $idLessons) . '&create_result=3');

        echo json_encode($response);
        die();*/
        Forma::removeErrors();
        require_once(_base_.'/lib/lib.upload.php');

        $back_url = urldecode($_POST['back_url']);

        //scanning title
        if(trim($_POST['title']) == "") $_POST['title'] = Lang::t('_NOTITLE');

        //save file
        if($_FILES['attach']['name'] == '') {

            Forma::addError(Lang::t('_FILEUNSPECIFIED'));

            Util::jump_to( $back_url.'&create_result=0' );
        } else {
            if(isset($_SESSION['idCourse']) && defined("LMS")) {
                $quota = $GLOBALS['course_descriptor']->getQuotaLimit();
                $used = $GLOBALS['course_descriptor']->getUsedSpace();

                if(Util::exceed_quota($_FILES['attach']['tmp_name'], $quota, $used)) {

                    Forma::addError(Lang::t('_QUOTA_EXCEDED'));
                    Util::jump_to( $back_url.'&create_result=0' );
                }
            }
            $path = '/appLms/'.Get::sett('pathlesson');
            $savefile = ( isset($_SESSION['idCourse']) ? $_SESSION['idCourse'] : '0' ).'_'.mt_rand(0,100).'_'.time().'_'.$_FILES['attach']['name'];
            $savefile = str_replace("'", "\'", $savefile);//Patch file con apostrofo
            if(!file_exists( _files_.$path.$savefile )) {
                sl_open_fileoperations();
                if(!sl_upload($_FILES['attach']['tmp_name'], $path.$savefile)) {
                    sl_close_fileoperations();
                    Forma::addError(Lang::t('_ERROR_UPLOAD'));
                    Util::jump_to( $back_url.'&create_result=0' );
                }
                sl_close_fileoperations();
            } else {
                Forma::addError(Lang::t('_ERROR_UPLOAD_FILE_EXISTS'));
                Util::jump_to( $back_url.'&create_result=0' );
            }
        }

        $insert_query = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_materials_lesson 
	SET author = '".getLogUserId()."',
		title = '".$_POST['title']."',
		description = '".$_POST['description']."',
		path = '$savefile'";

        if(!sql_query($insert_query)) {
            sl_unlink($GLOBALS['prefix_lms'].$savefile );
            Forma::addError(Lang::t('_ERROR_UPLOAD_MATERIAL_LESSON_INSER_FAIL'));
            Util::jump_to( $back_url.'&create_result=0' );
        }
        if(isset($_SESSION['idCourse']) && defined("LMS")) $GLOBALS['course_descriptor']->addFileToUsedSpace(_files_.$path.$savefile);
        list($idLesson) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
        Util::jump_to( $back_url.'&id_lo='.$idLesson.'&create_result=1' );
    }

//= XXX: edit=====================================================================

    function moditem($object_item)
    {
        //checkPerm('view', false, 'storage');

        require_once(_base_ . '/lib/lib.form.php');
        $lang =& DoceboLanguage::createInstance('item');

        $back_coded = htmlentities(urlencode($object_item->back_url));

        list($title, $description) = sql_fetch_row(sql_query("
	SELECT title, description 
	FROM " . $GLOBALS['prefix_lms'] . "_materials_lesson 
	WHERE idLesson = '" . $object_item->getId() . "'"));

        $GLOBALS['page']->add(getTitleArea($lang->def('_SECTIONNAME_ITEM'), 'item')
            . '<div class="std_block">'
            . getBackUi(Util::str_replace_once('&', '&amp;', $object_item->back_url) . '&amp;mod_result=0', $lang->def('_BACK'))


            . Form::openForm('itemform', 'index.php?modname=item&amp;op=upitem', 'std_form', 'post', 'multipart/form-data')
            . Form::openElementSpace()

            . Form::getHidden('idItem', 'idItem', $object_item->getId())
            . Form::getHidden('back_url', 'back_url', htmlentities(urlencode($object_item->back_url)))
            . Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 100, $title)
            . Form::getFilefield($lang->def('_FILE_MOD'), 'file', 'attach')

            . Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $description)
            . Form::closeElementSpace()
            . Form::openButtonSpace()
            . Form::getButton('additem', 'additem', $lang->def('_SAVE'))
            . Form::closeButtonSpace()
            . Form::closeForm()
            . '</div>', 'content');
    }

    function upitem()
    {
        //checkPerm('view', false, 'storage');

        require_once(_base_ . '/lib/lib.upload.php');

        $back_url = urldecode($_POST['back_url']);

        //scanning title
        if (trim($_POST['title']) == "") $_POST['title'] = Lang::t('_NOTITLE', 'item', 'lms');

        //save file
        if ($_FILES['attach']['name'] != '') {

            $path = '/appLms/' . Get::sett('pathlesson');

            // retrive and delte ld file --------------------------------------------------

            list($old_file) = sql_fetch_row(sql_query("
		SELECT path 
		FROM " . $GLOBALS['prefix_lms'] . "_materials_lesson 
		WHERE idLesson = '" . (int)$_POST['idItem'] . "'"));

            $size = Get::file_size(_files_ . $path . $old_file);
            if (!sl_unlink($path . $old_file)) {

                sl_close_fileoperations();
                Forma::addError(Lang::t('_OPERATION_FAILURE', 'item', 'lms'));
                Util::jump_to($back_url . '&id_lo=' . (int)$_POST['idItem'] . '&mod_result=0');
            }
            $GLOBALS['course_descriptor']->subFileToUsedSpace(false, $size);

            // control course quota ---------------------------------------------------

            $quota = $GLOBALS['course_descriptor']->getQuotaLimit();
            $used = $GLOBALS['course_descriptor']->getUsedSpace();

            if (Util::exceed_quota($_FILES['attach']['tmp_name'], $quota, $used)) {

                Forma::addError(Lang::t('_QUOTA_EXCEDED'));
                Util::jump_to($back_url . '&create_result=0');
            }

            // save new file ------------------------------------------------------------

            sl_open_fileoperations();
            $savefile = $_SESSION['idCourse'] . '_' . mt_rand(0, 100) . '_' . time() . '_' . $_FILES['attach']['name'];
            if (!file_exists(_files_ . $path . $savefile)) {
                if (!sl_upload($_FILES['attach']['tmp_name'], $path . $savefile)) {

                    sl_close_fileoperations();
                    Forma::addError(Lang::t('_ERROR_UPLOAD', 'item', 'lms'));
                    Util::jump_to($back_url . '&id_lo=' . (int)$_POST['idItem'] . '&mod_result=0');
                }
                sl_close_fileoperations();
            } else {

                Forma::addError(Lang::t('_ERROR_UPLOAD', 'item', 'lms'));
                Util::jump_to($back_url . '&id_lo=' . (int)$_POST['idItem'] . '&mod_result=0');
            }
            $new_file = ", path = '" . $savefile . "'";
        }

        $insert_query = "
	UPDATE " . $GLOBALS['prefix_lms'] . "_materials_lesson 
	SET title = '" . $_POST['title'] . "',
		description = '" . $_POST['description'] . "'
		$new_file
	WHERE idLesson = '" . (int)$_POST['idItem'] . "'";

        if (!sql_query($insert_query)) {
            sl_unlink($path . $savefile);
            Forma::addError(Lang::t('_OPERATION_FAILURE', 'item', 'lms'));
            Util::jump_to($back_url . '&id_lo=' . (int)$_POST['idItem'] . '&mod_result=0');
        }
        if (isset($_SESSION['idCourse']) && defined("LMS")) {
            $GLOBALS['course_descriptor']->addFileToUsedSpace(_files_ . $path . $savefile);
            require_once($GLOBALS['where_lms'] . '/class.module/track.object.php');
            Track_Object::updateObjectTitle($_POST['idItem'], 'item', $_POST['title']);
        }

        Util::jump_to($back_url . '&id_lo=' . (int)$_POST['idItem'] . '&mod_result=1');
    }

//= XXX: switch===================================================================
    switch ($GLOBALS['op']) {

        case "insitem" :
            {
                insitem();
            };
            break;

        case "upitem" :
            {
                upitem();
            };
            break;
    }

}

?>