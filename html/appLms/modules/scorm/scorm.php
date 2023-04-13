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

/*
 * @module scorm.php
 * Impor module for scorm content packages
 * @version $Id: scorm.php 1002 2007-03-24 11:55:51Z fabio $
 * @copyright 2004
 * @author Emanuele Sandri
 **/

const STRPOSTCONTENT = '_content';

function additem($object_item)
{
    //checkPerm( 'view', FALSE, 'storage' );

    $lang = &FormaLanguage::createInstance('scorm', 'lms');
    require_once \FormaLms\lib\Forma::inc(_lib_ . '/lib.form.php');
    $form = new Form();

    //area title
    $GLOBALS['page']->add(getTitleArea(
            $lang->getLangText('_SCORMIMGSECTION'),
            'scorm',
            $lang->getLangText('_SCORMSECTIONNAME'))
    );

    $GLOBALS['page']->add(
        '<div class="std_block">'
        . getBackUi(Util::str_replace_once('&', '&amp;', $object_item->back_url) . '&amp;create_result=0',
            $lang->getLangText('_BACK_TOLIST'))
    );

    $GLOBALS['page']->add(Form::getFormHeader($lang->def('_SCORM_ADD_FORM')));

    $GLOBALS['page']->add(
        $form->openForm('scormform',
            'index.php?modname=scorm&amp;op=insitem',
            false,
            false,
            'multipart/form-data')
    );
    $GLOBALS['page']->add($form->openElementSpace());

    $GLOBALS['page']->add($form->getHidden('back_url', 'back_url', htmlentities(urlencode($object_item->back_url))));
    $GLOBALS['page']->add($form->getFilefield($lang->getLangText('_CONTENTPACKAGE'), 'attach', 'attach'));

    $GLOBALS['page']->add($form->getCheckbox($lang->getLangText('_SCORMIMPORTRESOURCES'),
        'lesson_resources',
        'lesson_resources',
        'import'));
    $GLOBALS['page']->add($form->closeElementSpace());
    $GLOBALS['page']->add($form->openButtonSpace());
    $GLOBALS['page']->add($form->getButton('scorm_add_submit',
        'scorm_add_submit',
        $lang->getLangText('_SCORMLOAD')));
    $GLOBALS['page']->add($form->closeButtonSpace());
    $GLOBALS['page']->add($form->closeForm() . '</div>');
}

function insitem()
{
    //checkPerm( 'view', FALSE, 'storage' );

    require_once \FormaLms\lib\Forma::inc(_lib_ . '/lib.upload.php');
    require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/RendererDb.php');
    require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/CPManager.php');

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

    $idCourse = $session->get('idCourse');
    $back_url = urldecode($_POST['back_url']);

    // there is a file?
    if ($_FILES['attach']['name'] == '') {
        \FormaLms\lib\Forma::addError(Lang::t('_FILEUNSPECIFIED'));
        Util::jump_to('' . $back_url . '&create_result=0');
    }
    $path = str_replace('\\', '/', '/' . _folder_lms_ . '/' . FormaLms\lib\Get::sett('pathscorm'));
    $savefile = \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt() . '_' . rand(0, 100) . '_' . time() . '_' . $_FILES['attach']['name'];
    if (!file_exists(_files_ . $path . $savefile)) {
        sl_open_fileoperations();
        if (!sl_upload($_FILES['attach']['tmp_name'], $path . $savefile)) {
            //if( !move_uploaded_file($_FILES['attach']['tmp_name'], _files_.$path.$savefile ) ) {
            sl_close_fileoperations();
            \FormaLms\lib\Forma::addError(Lang::get('_ERROR_UPLOAD'));
            Util::jump_to('' . $back_url . '&create_result=0');
        }
    } else {
        sl_close_fileoperations();
        \FormaLms\lib\Forma::addError(Lang::get('_ERROR_UPLOAD'));
        Util::jump_to('' . $back_url . '&create_result=0');
    }

    // compute filepath
    $filepath = $path . $savefile . STRPOSTCONTENT;
    // extract zip file
    $zip = new PclZip(_files_ . $path . $savefile);

    // check disk quota --------------------------------------------------
    if ($idCourse && defined('LMS')) {
        $zip_content = $zip->listContent();
        $zip_extracted_size = 0;
        foreach ($zip_content as $file_info) {
            if (strpos($file_info['filename'], '../') !== false) {
                \FormaLms\lib\Forma::addError(Lang::get('_ERROR_UPLOAD'));

                Util::jump_to('' . $back_url . '&create_result=0');
            }
            $zip_extracted_size += $file_info['size'];
        }

        $quota = $GLOBALS['course_descriptor']->getQuotaLimit();
        $used = $GLOBALS['course_descriptor']->getUsedSpace();

        if (Util::exceed_quota(false, (int)$quota, (int)$used, (int)$zip_extracted_size)) {
            sl_unlink($path . $savefile);
            \FormaLms\lib\Forma::addError(Lang::t('_QUOTA_EXCEDED'));
            Util::jump_to('' . $back_url . '&create_result=0');
        }
        $GLOBALS['course_descriptor']->addFileToUsedSpace(false, $zip_extracted_size);
    }
    // extract zip ------------------------------------------------------

    $zip->extract(PCLZIP_OPT_PATH, _files_ . $filepath);

    // If zip folders has \\ this code replace with slash
    //$files = glob(_files_ . $filepath . '/*');
    $finder = new Symfony\Component\Finder\Finder();
    $finder->files()->in(_files_ . $filepath);

    $notAllowedExtentions = ['php', 'exe'];
    /** @var SplFileInfo $file */
    foreach ($finder->sortByName() as $file) {
        $fileParts = pathinfo($file->getPathname());
        if (in_array($fileParts['extension'], $notAllowedExtentions, true)) {
            unlink($file->getPathname());
            continue;
        }
        $newFile = str_replace('\\', '/', $file);

        if (!is_dir(dirname($newFile))) {
            if (!mkdir($concurrentDirectory = dirname($newFile), 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        rename($file, $newFile);
    }

    if ($zip->errorCode() != PCLZIP_ERR_NO_ERROR && $zip->errorCode() != 1) {
        sl_unlink($path . $savefile);
        \FormaLms\lib\Forma::addError(Lang::get('_ERROR_UPLOAD'));
        sl_close_fileoperations();

        Util::jump_to('' . $back_url . '&create_result=0');
    }

    /* remove zip file */
    sl_unlink($path . $savefile);
    sl_close_fileoperations();

    $cpm = new CPManager();
    // try to open content package
    if (!$cpm->Open(_files_ . $filepath)) {
        \FormaLms\lib\Forma::addError('Error: ' . $cpm->errText . ' [' . $cpm->errCode . ']');
        Util::jump_to('' . $back_url . '&create_result=0');
    }
    // and parse the manifest
    if (!$cpm->ParseManifest()) {
        \FormaLms\lib\Forma::addError('Error: ' . $cpm->errText . ' [' . $cpm->errCode . ']');
        Util::jump_to('' . $back_url . '&create_result=0');
    }

    // create entry in content package table
    $query = 'INSERT INTO %lms_scorm_package'
        . ' (idpackage,idProg,path,defaultOrg,idUser,scormVersion) VALUES'
        . " ('" . addslashes($cpm->identifier)
        . "','0','" . str_replace("'", "\'", $savefile) . STRPOSTCONTENT
        . "','" . addslashes($cpm->defaultOrg)
        . "','" . (int) \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt()
        . "','" . $cpm->scorm_version
        . "')";
    if (!($result = sql_query($query))) {
        \FormaLms\lib\Forma::addError(Lang::get('_OPERATION_FAILURE'));
        Util::jump_to('' . $back_url . '&create_result=0');
    }

    $idscorm_package = sql_insert_id();

    // create the n entries in resources table
    for ($i = 0; $i < $cpm->GetResourceNumber(); ++$i) {
        $info = $cpm->GetResourceInfo($cpm->GetResourceIdentifier($i));
        $query = 'INSERT INTO %lms_scorm_resources (idsco,idscorm_package,scormtype,href)'
            . " VALUES ('" . addslashes($info['identifier']) . "','"
            . (int) $idscorm_package . "','"
            . $info['scormtype'] . "','"
            . addslashes($info['href']) . "')";

        $result = sql_query($query);

        if (!$result) {
            \FormaLms\lib\Forma::addError(Lang::get('_OPERATION_FAILURE'));
            Util::jump_to('' . $back_url . '&create_result=0');
        } elseif (sql_affected_rows() == 0) {
            \FormaLms\lib\Forma::addError(Lang::get('_OPERATION_FAILURE'));
            Util::jump_to('' . $back_url . '&create_result=0');
        }
    }

    $rdb = new RendererDb($GLOBALS['dbConn'], $GLOBALS['prefix_lms'], $idscorm_package);
    $orgElems = $cpm->orgElems;
    // save all organizations
    for ($iOrg = 0; $iOrg < $orgElems->getLength(); ++$iOrg) {
        $org = $orgElems->item($iOrg);
        $cpm->RenderOrganization($org->getAttribute('identifier'), $rdb);
    }

    if ($_POST['lesson_resources'] == 'import' || $cpm->defaultOrg == '-resource-') {
        // save flat organization with resources
        $cpm->RenderOrganization('-resource-', $rdb);
    }

    $so = new Scorm_Organization($cpm->defaultOrg, $idscorm_package, $GLOBALS['dbConn']);
    if ($so->err_code > 0) {
        \FormaLms\lib\Forma::addError('Error: ' . $so->getErrorText() . ' [' . $so->getErrorCode() . ']');
        Util::jump_to('' . $back_url . '&create_result=0');
    } else {
        //Util::jump_to( ''.$back_url.'&id_lo='.$so->idscorm_organization.'&create_result=1' );
        Util::jump_to('' . $back_url . '&id_lo=' . $idscorm_package . '&create_result=2');
    }
}

function moditem($object_item)
{
    checkPerm('view', false, 'storage');

    $lang = &FormaLanguage::createInstance('scorm', 'lms');

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $idCourse = $session->get('idCourse');
    //area title
    $query = 'SELECT idOrg ' .
        ' FROM %lms_organization ' .
        ' WHERE idResource = ' . (int) $object_item->id . " AND objectType = 'scormorg' ";
    list($id_reference) = sql_fetch_row(sql_query($query));

    require_once \FormaLms\lib\Forma::inc(_lib_ . '/lib.table.php');
    $tb = new Table();
    $h_type = ['', ''];
    $h_content = [
        $lang->def('_NAME'),
        $lang->def('_LINK'),
    ];

    $tb->setColsStyle($h_type);
    $tb->addHead($h_content);

    $qry = 'SELECT item_identifier, idscorm_resource, title ' .
        ' FROM %lms_scorm_items ' .
        ' WHERE idscorm_organization = ' . (int) $object_item->id . '' .
        ' ORDER BY idscorm_item ';

    $res = sql_query($qry);
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $idCourse = $session->get('idCourse');

    while ($row = sql_fetch_row($res)) {
        $line = [];
        $line[] = $row[2];
        $line[] = ($row[1] != 0
            ? FormaLms\lib\Get::abs_path('lms') . '/index.php?id_course=' . $idCourse . '&amp;act=playsco&amp;courseid=' . $idCourse . '&amp;id_item=' . $id_reference . '&amp;chapter=' . $row[0] . ''
            : '');
        $tb->addBody($line);
    }

    $GLOBALS['page']->add(getTitleArea($lang->getLangText('_SCORMIMGSECTION'), 'scorm')
        . '<div class="std_block">'
        . getBackUi($object_item->back_url . '&amp;edit_result=0', $lang->getLangText('_BACK_TOLIST'))
        . $tb->getTable()
        . '</div>', 'content');
}

function play($aidResource, $aidReference, $aback_url, $aautoplay, $aplayertemplate, $environment = 'course_lo')
{
    $GLOBALS['idReference'] = $aidReference;
    $GLOBALS['idResource'] = $aidResource;
    $GLOBALS['back_url'] = $aback_url;
    $GLOBALS['autoplay'] = $aautoplay;
    $GLOBALS['playertemplate'] = $aplayertemplate;
    $GLOBALS['environment'] = $environment;
    require \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm_frameset.php');
}

function _scorm_deleteitem($idscorm_package, $idscorm_organization, $erasetrackcontent = false)
{
    /* remove items: based on organizations */
    //$rs = sql_query("SELECT idscorm_organization FROM ".$prefix."_scorm_organizations WHERE idscorm_package=".$idscorm_package);
    //while(list($idscorm_organization) = sql_fetch_row($rs)) {
    if ($erasetrackcontent) { // selected tracking remove
        $rsItems = sql_query('SELECT idscorm_item FROM %lms_scorm_items WHERE idscorm_organization=' . $idscorm_organization);
        while (list($idscorm_item) = sql_fetch_row($rsItems)) {
            sql_query('DELETE FROM %lms_scorm_tracking WHERE idscorm_resource=' . $idscorm_item);
        }
    }
    sql_query('DELETE FROM %lms_scorm_items WHERE idscorm_organization=' . $idscorm_organization);

    //}

    /* remove organizations */
    sql_query('DELETE FROM %lms_scorm_organizations WHERE idscorm_organization=' . $idscorm_organization);

    // detect if there are other organization in package
    $rs = sql_query('SELECT idscorm_organization FROM %lms_scorm_organizations WHERE idscorm_package=' . $idscorm_package);

    if (sql_num_rows($rs) == 0) {
        $rs = sql_query('SELECT path FROM ' . $GLOBALS['prefix_lms'] . "_scorm_package WHERE idscorm_package='" . (int) $idscorm_package . "'")
        or exit(sql_error());

        list($path) = sql_fetch_row($rs);
        $scopath = str_replace('\\', '/', _files_ . '/appLms/' . FormaLms\lib\Get::sett('pathscorm'));
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $idCourse = $session->get('idCourse');
        /* remove all zip directory */
        if (file_exists($scopath . $path)) {
            /* if is the only occurrence of path in db delete files */
            $rs = sql_query('SELECT idscorm_package FROM %lms_scorm_package'
                . " WHERE path = '" . $path . "'");
            if (sql_num_rows($rs) == 1) {
                $size = FormaLms\lib\Get::dir_size($scopath . $path);

                require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm_utils.php'); // for del tree
                delDirTree($scopath . $path);

                if (isset($idCourse) && defined('LMS')) {
                    $GLOBALS['course_descriptor']->subFileToUsedSpace(false, $size);
                }
            }
        }

        /* remove resources */
        sql_query('DELETE FROM %lms_scorm_resources WHERE idscorm_package=' . $idscorm_package);

        /* remove packages */
        sql_query('DELETE FROM %lms_scorm_package WHERE idscorm_package=' . $idscorm_package);
    }
}

function _scorm_copyitem($idscorm_package, $idscorm_organization)
{
    require_once \FormaLms\lib\Forma::inc(_lib_ . '/lib.upload.php');
    require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/RendererDb.php');
    require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/CPManager.php');

    if (($rs = sql_query('SELECT path FROM %lms_scorm_package '
            . "WHERE idscorm_package='"
            . (int) $idscorm_package . "'")) === false) {
        \FormaLms\lib\Forma::addError(Lang::t('_OPERATION_FAILURE', 'standard') . ': ' . sql_error());

        return false;
    }

    list($path) = sql_fetch_row($rs);
    $scopath = str_replace('\\', '/', _files_ . '/appLms/' . FormaLms\lib\Get::sett('pathscorm'));

    /* copy package record */
    $rs_package = sql_query("SELECT idpackage,idProg,\"" . $path . "\",defaultOrg,idUser,scormVersion "
        . ' FROM %lms_scorm_package '
        . " WHERE idscorm_package='" . (int) $idscorm_package . "'");

    $arr_package = sql_fetch_row($rs_package);
    for ($i = 0; $i < count($arr_package); ++$i) {
        $arr_package[$i] = addslashes($arr_package[$i]);
    }
    sql_query('INSERT INTO %lms_scorm_package '
        . ' (idpackage,idProg,path,defaultOrg,idUser,scormVersion) VALUES '
        . "('" . implode("','", $arr_package) . "')");

    /*	sql_query("INSERT INTO ".$GLOBALS['prefix_lms']."_scorm_package "
                    ." (idpackage,idProg,path,defaultOrg,idUser) "
                    ." SELECT idpackage,idProg,'".$path."',defaultOrg,idUser "
                    ."   FROM ".$GLOBALS['prefix_lms']."_scorm_package "
                    ."  WHERE idscorm_package='".(int)$idscorm_package."'");*/

    $new_idscorm_package = sql_insert_id();

    /* copy resources */
    $rs_resources = sql_query(" SELECT idsco,'" . $new_idscorm_package . "',scormtype,href "
        . '  FROM %lms_scorm_resources '
        . " WHERE idscorm_package='" . (int) $idscorm_package . "'");

    while ($arr_resource = sql_fetch_row($rs_resources)) {
        for ($i = 0; $i < count($arr_resource); ++$i) {
            $arr_resource[$i] = addslashes($arr_resource[$i]);
        }
        sql_query('INSERT INTO %lms_scorm_resources '
            . ' (idsco,idscorm_package,scormtype,href) VALUES '
            . "('" . implode("','", $arr_resource) . "')");
    }
    /*sql_query("INSERT INTO ".$GLOBALS['prefix_lms']."_scorm_resources "
                ." (idsco,idscorm_package,scormtype,href) "
                ." SELECT idsco,'".$new_idscorm_package."',scormtype,href "
                ."   FROM ".$GLOBALS['prefix_lms']."_scorm_resources "
                ."  WHERE idscorm_package='".(int)$idscorm_package."'");*/

    $cpm = new CPManager();
    // try to open content package
    if (!$cpm->Open($scopath . $path)) {
        \FormaLms\lib\Forma::addError('Error: ' . $cpm->errText . ' [' . $cpm->errCode . ']');

        return false;
    }

    // and parse the manifest
    if (!$cpm->ParseManifest()) {
        \FormaLms\lib\Forma::addError('Error: ' . $cpm->errText . ' [' . $cpm->errCode . ']');

        return false;
    }

    $rdb = new RendererDb($GLOBALS['dbConn'], $GLOBALS['prefix_lms'], $new_idscorm_package);
    /*$orgElems = $cpm->orgElems;
    // save all organizations
    foreach( $orgElems as $org )
        $cpm->RenderOrganization( $org->get_attribute('identifier'), $rdb );*/

    list($org_identifier) = sql_fetch_row(sql_query(
        'SELECT org_identifier FROM %lms_scorm_organizations '
        . " WHERE idscorm_organization='" . (int) $idscorm_organization . "'"));

    $cpm->RenderOrganization($org_identifier, $rdb);

    // save flat organization with resources
    //$cpm->RenderOrganization( '-resource-', $rdb );

    $so = new Scorm_Organization(addslashes($org_identifier), $new_idscorm_package, $GLOBALS['dbConn']);
    if ($so->err_code > 0) {
        \FormaLms\lib\Forma::addError('Error: ' . $so->getErrorText() . ' [' . $so->getErrorCode() . ']');

        return false;
    } else {
        return $so->idscorm_organization;
    }
}

if (isset($GLOBALS['op'])) {
    switch ($GLOBALS['op']) {
        case 'additem' :
                additem();

            break;
        case 'insitem' :
                insitem();

            break;
        case 'deleteitem':
                deleteitem();

            break;
        case 'dodelete':
                dodelete();

            break;
        case 'category' :
                category();

            break;
        case 'categorysave':
                categorysave();

            break;
        case 'play':
                play();

            break;
        case 'tree':
                require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm_page_tree.php');

            break;
        case 'head':
                require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm_page_head.php');

            break;
        case 'body':
                require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm_page_body.php');

            break;
        case 'scoload':
                require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/soaplms.php');

            break;
        default:
            break;
    }
}
