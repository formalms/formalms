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

require_once __DIR__ . '/learning.object.php';

class Learning_Item extends Learning_Object
{
    public function __construct($id_resource = null, $environment = false)
    {
        parent::__construct($id_resource, $environment);
        $this->obj_type = 'item';
        if ($id_resource != false) {
            $this->load();
        }
    }

    public function load()
    {
        $res = $this->db->query('SELECT author, title FROM %lms_materials_lesson WHERE idLesson = ' . (int) $this->id . ' ');
        if ($res && $this->db->num_rows($res) > 0) {
            list($this->idAuthor, $this->title) = $this->db->fetch_row($res);
        }
    }

    public function getParamInfo()
    {
        $params = parent::getParamInfo();

        return $params;
    }

    public function renderCustomSettings($arrParams, $form, $lang)
    {
        $out = parent::renderCustomSettings($arrParams, $form, $lang);

        return $out;
    }

    /**
     * attach the id of the created object at the end of back_url with the name, in attach the result in create_result.
     *
     * @param string $back_url contains the back url
     */
    public function create($back_url)
    {
        $this->back_url = $back_url;

        Forma::removeErrors();

        require_once _lms_ . '/modules/item/item.php';
        additem($this);
    }

    /**
     * attach in back_url id_lo that is passed $id and attach edit_result with the result of operation in boolean format.
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contains the back url
     */
    public function edit($id, $back_url)
    {
        $this->id = $id;
        $this->back_url = $back_url;

        Forma::removeErrors();

        require_once _lms_ . '/modules/item/item.php';
        moditem($this);
    }

    /**
     * @param int    $id       contains the resource id
     * @param string $back_url contains the back url (not used yet)
     */
    public function del($id, $back_url = null)
    {
        //checkPerm('view', false, 'storage');

        Forma::removeErrors();

        require_once _base_ . '/lib/lib.upload.php';

        $path_to_file = '/appLms/' . Forma\lib\Get::sett('pathlesson');

        list($old_file) = sql_fetch_row(sql_query('
		SELECT path 
		FROM ' . $GLOBALS['prefix_lms'] . "_materials_lesson 
		WHERE idLesson = '" . $id . "'"));

        $size = Forma\lib\Get::file_size(_files_ . $path_to_file . $old_file);
        if ($old_file != '') {
            sl_open_fileoperations();
            if (!sl_unlink($path_to_file . $old_file)) {
                sl_close_fileoperations();
                Forma::addError(Lang::t('_OPERATION_FAILURE', 'item'));

                return false;
            }
            sl_close_fileoperations();
            $idCourse = \Forma\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
            if (isset($idCourse) && defined('LMS')) {
                $GLOBALS['course_descriptor']->subFileToUsedSpace(false, $size);
            }
        }
        $delete_query = '
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_materials_lesson 
		WHERE idLesson = '" . $id . "'";

        if (!sql_query($delete_query)) {
            Forma::addError(Lang::t('_OPERATION_FAILURE', 'item'));

            return false;
        }

        return $id;
    }

    /**
     * @param int    $id       contains the resource id
     * @param string $back_url contain the back url (not used yet)
     */
    public function copy($id, $back_url = null)
    {
        require_once _base_ . '/lib/lib.upload.php';

        //find source info
        list($title, $descr, $file) = sql_fetch_row(sql_query('
		SELECT title, description, path 
		FROM ' . $GLOBALS['prefix_lms'] . "_materials_lesson 
		WHERE idLesson = '" . (int) $id . "'"));

        //create the copy filename
        $path_to_file = '/appLms/' . Forma\lib\Get::sett('pathlesson');
        $savefile = $this->session->get('idCourse') . '_' . mt_rand(0, 100) . '_' . time() . '_'
            . implode('_', array_slice(explode('_', $file), 3));

        //copy fisic file
        sl_open_fileoperations();
        if (!sl_copy($path_to_file . $file, $path_to_file . $savefile)) {
            sl_close_fileoperations();

            return false;
        }

        //insert new item
        $insertQuery = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_materials_lesson 
		SET author = '" . getLogUserId() . "',
			title = '" . sql_escape_string($title) . "',
			description = '" . sql_escape_string($descr) . "',
			path = '" . sql_escape_string($savefile) . "'";

        if (!sql_query($insertQuery)) {
            sl_unlink($path_to_file . $savefile);
            sl_close_fileoperations();

            return false;
        }
        sl_close_fileoperations();

        list($idLesson) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));

        return $idLesson;
    }

    /**
     * @param int    $id       contains the resource id
     * @param int    $id_param contains the id needed for params retriving
     * @param string $back_url contain the back url
     */
    public function play($id, $id_param, $back_url)
    {
        require_once _lms_ . '/modules/item/do.item.php';

        $this->id = $id;
        $this->back_url = $back_url;
        play($id, $id_param, $back_url);
    }

    public function env_play($id_reference, $back_url, $options = [])
    {
        require_once _lms_ . '/modules/item/do.item.php';
        //$this->id;
        //$this->obj_type;

        //$this->environment;
        $this->id_reference = $id_reference;
        $this->back_url = $back_url;
        env_play($this, $options);
    }
}
