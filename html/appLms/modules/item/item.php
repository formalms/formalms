<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

if (!Docebo::user()->isAnonymous()) {
    // XXX: additem
    function additem($object_item)
    {
        //checkPerm('view', false, 'storage');
        $lang = &DoceboLanguage::createInstance('item');

        require_once _base_ . '/lib/lib.form.php';

        /*$GLOBALS['page']->add(getTitleArea($lang->def('_SECTIONNAME_ITEM'), 'item')
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
    */

        $GLOBALS['page']->add(FormaLms\appCore\Template\TwigManager::getInstance()->render('upload-file.html.twig', ['back_url' => $object_item->back_url, 'op' => 'insitem', 'id_comm' => $object_item->id], _lms_ . '/views/lo'), 'content');
    }

    function insitem()
    {
        require_once _base_ . '/lib/lib.upload.php';
        $response = [];
        $response['status'] = true;
        $back_url = FormaLms\lib\Get::pReq('back_url', DOTY_MIXED, '');
        //checkPerm('view', false, 'storage');

        $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');

        try {
            $filesInfo = json_decode($_REQUEST['info'], true);
        } catch (JsonException $e) {
            $response['status'] = false;
            $response['errors'][] = Lang::t('_INPUT_IS_NOT_VALID', 'item');
            echo json_encode($response);
            exit();
        }
        if (isset($idCourse) && defined('LMS')) {
            $quota = $GLOBALS['course_descriptor']->getQuotaLimit();
            $used = $GLOBALS['course_descriptor']->getUsedSpace();

            $totalSize = 0;
            foreach ($filesInfo as $index => $fileItem) {
                $file = $_FILES['file' . $index];

                $totalSize += FormaLms\lib\Get::dir_size($file['tmp_name']);
            }
            if (Util::exceed_quota('', $quota, $used, $totalSize)) {
                $response['errors']['quota'] = Lang::t('_QUOTA_EXCEDED', 'item');
                echo json_encode($response);
                exit();
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
                $path = '/' . _folder_lms_ . '/' . FormaLms\lib\Get::sett('pathlesson');
                $savefile = ($idCourse ?? '0') . '_' . random_int(0, 100) . '_' . time() . '_' . $file['name'];
                $savefile = str_replace("'", "\'", $savefile); //Patch file con apostrofo

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
                    $response['errors']['files'][$fileIndex] = Lang::t('_FILE_ERROR_UPLOAD', 'item');
                }

                $insert_query = "INSERT INTO %lms_materials_lesson  SET author = '" . getLogUserId() . "', title = '" . $fileItem['title'] . "', description = '" . $fileItem['description'] . "', path = '$savefile'";

                if (!sql_query($insert_query)) {
                    sl_unlink($path . $savefile);
                    $response['errors']['files'][$fileIndex] = Lang::t('_FILE_OPERATION_FAILURE', 'item');
                }

                if (isset($idCourse) && defined('LMS')) {
                    $GLOBALS['course_descriptor']->addFileToUsedSpace(_files_ . $path . $savefile);
                }
                list($idLesson) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
                $idLessons[] = $idLesson;
            }
        }

        $response['back_url'] = str_replace('&amp;', '&', $back_url . '&id_los=' . implode(',', $idLessons) . '&create_result=3');

        echo json_encode($response);
        exit();
    }

    //= XXX: edit=====================================================================

    function moditem($object_item)
    {
        //checkPerm('view', false, 'storage');

        require_once _base_ . '/lib/lib.form.php';
        $lang = &DoceboLanguage::createInstance('item');

        $back_coded = htmlentities(urlencode($object_item->back_url));

        list($filename) = sql_fetch_row(sql_query('SELECT path'
        . ' FROM %lms_materials_lesson'
        . ' WHERE idLesson = ' . (int) $object_item->id . ''));

        $file['name'] = $filename;
        $file['size'] = filesize(_base_ . '/files/appLms/' . FormaLms\lib\Get::sett('pathlesson') . $filename);
        $files[] = $file;

        /*
            $GLOBALS['page']->add(getTitleArea($lang->def('_SECTIONNAME_ITEM'), 'item')
                . '<div class="std_block">'
                . getBackUi(Util::str_replace_once('&', '&amp;', $object_item->back_url) . '&amp;mod_result=0', $lang->def('_BACK'))

                . Form::openForm('itemform', 'index.php?modname=item&amp;op=upitem', 'std_form', 'post', 'multipart/form-data')
                . Form::openElementSpace()

                . Form::getHidden('idItem', 'idItem', $object_item->getId())
                . Form::getHidden('back_url', 'back_url', htmlentities(urlencode($object_item->back_url)))
                . Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 100, $title)

                . Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $description)
                . Form::closeElementSpace()
                . Form::openButtonSpace()
                . Form::getButton('additem', 'additem', $lang->def('_SAVE'))
                . Form::closeButtonSpace()
                . Form::closeForm()
                . '</div>', 'content');
                */

        $GLOBALS['page']->add(FormaLms\appCore\Template\TwigManager::getInstance()->render('upload-file.html.twig', ['back_url' => $object_item->back_url, 'op' => 'upitem', 'id_comm' => $object_item->id], _lms_ . '/views/lo'), 'content');
    }

    function upitem()
    {
        //checkPerm('view', false, 'storage');

        require_once _base_ . '/lib/lib.upload.php';
        $response = [];
        $response['status'] = true;
        $back_url = FormaLms\lib\Get::pReq('back_url', DOTY_MIXED, '');
        $idLesson = FormaLms\lib\Get::pReq('id_comm', DOTY_INT, null);
        $title = FormaLms\lib\Get::pReq('title', DOTY_STRING, Lang::t('_NOTITLE', 'item', 'lms'));

        $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');

        try {
            $filesInfo = json_decode($_REQUEST['info'], true);
        } catch (JsonException $e) {
            $response['status'] = false;
            $response['errors'][] = Lang::t('_INPUT_IS_NOT_VALID', 'item');
            echo json_encode($response);
            exit();
        }

        //save file
        if (count($filesInfo)) {
            $path = '/appLms/' . FormaLms\lib\Get::sett('pathlesson');

            // retrive and delte ld file --------------------------------------------------

            list($old_file) = sql_fetch_row(sql_query("
            SELECT path 
            FROM %lms_materials_lesson 
            WHERE idLesson = '" . (int) $idLesson . "'"));

            $size = FormaLms\lib\Get::file_size(_files_ . $path . $old_file);
            if (!sl_unlink($path . $old_file)) {
                sl_close_fileoperations();
                Forma::addError(Lang::t('_OPERATION_FAILURE', 'item', 'lms'));
                Util::jump_to($back_url . '&id_los=' . (int) $idLesson . '&mod_result=0');
            }

            if (isset($idCourse) && defined('LMS')) {
                $GLOBALS['course_descriptor']->subFileToUsedSpace(false, $size);

                // control course quota ---------------------------------------------------

                $quota = $GLOBALS['course_descriptor']->getQuotaLimit();
                $used = $GLOBALS['course_descriptor']->getUsedSpace();

                $totalSize = 0;
                foreach ($filesInfo as $index => $fileItem) {
                    $file = $_FILES['file' . $index];

                    $totalSize += FormaLms\lib\Get::dir_size($file['tmp_name']);
                }
                if (Util::exceed_quota('', $quota, $used, $totalSize)) {
                    $response['errors']['quota'] = Lang::t('_QUOTA_EXCEDED', 'item');
                    echo json_encode($response);
                    exit();
                }
            }

            // save new file ------------------------------------------------------------
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
                    $path = '/' . _folder_lms_ . '/' . FormaLms\lib\Get::sett('pathlesson');
                    $savefile = ($idCourse ?? '0') . '_' . random_int(0, 100) . '_' . time() . '_' . $file['name'];
                    $savefile = str_replace("'", "\'", $savefile); //Patch file con apostrofo

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
                        $response['errors']['files'][$fileIndex] = Lang::t('_FILE_ERROR_UPLOAD', 'item');
                    }

                    $update_query = "UPDATE %lms_materials_lesson  SET author = '" . getLogUserId() . "', title = '" . $fileItem['title'] . "', description = '" . $fileItem['description'] . "', path = '$savefile'
                    WHERE idLesson = '" . (int) $idLesson . "'";

                    if (!sql_query($update_query)) {
                        sl_unlink($path . $savefile);
                        $response['errors']['files'][$fileIndex] = Lang::t('_FILE_OPERATION_FAILURE', 'item');
                    }
                    if (isset($idCourse) && defined('LMS')) {
                        $GLOBALS['course_descriptor']->addFileToUsedSpace(_files_ . $path . $savefile);
                    }
                }
            }
        }

        $response['back_url'] = str_replace('&amp;', '&', $back_url . '&id_los=' . (int) $idLesson . '&mod_result=1');
        echo json_encode($response);
        exit();
    }

    //= XXX: switch===================================================================
    switch ($GLOBALS['op']) {
        case 'insitem':
                insitem();

            break;

        case 'upitem':
                upitem();

            break;
    }
}
