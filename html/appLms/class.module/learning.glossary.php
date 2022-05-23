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

class Learning_Glossary extends Learning_Object
{
    public $id;

    public $idAuthor;

    public $title;

    public $back_url;

    /**
     * function learning_Object()
     * class constructor.
     **/
    public function __construct($id = null)
    {
        parent::__construct($id);
        if ($id !== null) {
            $res = $this->db->query("SELECT author, title FROM %lms_glossary WHERE idGlossary = '" . $id . "'");
            if ($res && $this->db->num_rows($res) > 0) {
                list($this->idAuthor, $this->title) = $this->db->fetch_row($res);
                $this->isPhysicalObject = true;
            }
        }
    }

    public function getObjectType()
    {
        return 'glossary';
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
     * function create( $back_url ).
     *
     * @param string $back_url contains the back url
     *
     * @return nothing
     *                 attach the id of the created object at the end of back_url with the name, in attach the result in create_result
     *
     * static
     **/
    public function create($back_url)
    {
        $this->back_url = $back_url;

        Forma::removeErrors();

        require_once _lms_ . '/modules/glossary/glossary.php';
        addglossary($this);
    }

    /**
     * function edit.
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contains the back url
     *
     * @return nothing
     *                 attach in back_url id_lo that is passed $id and attach edit_result with the result of operation in boolean format
     **/
    public function edit($id, $back_url)
    {
        $this->id = $id;
        $this->back_url = $back_url;

        Forma::removeErrors();

        require_once _lms_ . '/modules/glossary/glossary.php';
        modglossarygui($this);
    }

    /**
     * function del.
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contains the back url (not used yet)
     *
     * @return false if fail, else return the id lo
     **/
    public function del($id, $back_url = null)
    {
        checkPerm('view', false, 'storage');
        Forma::removeErrors();

        if (!sql_query('DELETE FROM ' . $GLOBALS['prefix_lms'] . "_glossaryterm WHERE idGlossary='" . $id . "'")) {
            Forma::addError(Lang::t('_OPERATION_FAILURE', 'standard'));

            return false;
        } elseif (!sql_query('DELETE FROM ' . $GLOBALS['prefix_lms'] . "_glossary WHERE idGlossary = '" . (int) $id . "'")) {
            Forma::addError(Lang::t('_OPERATION_FAILURE', 'standard'));

            return false;
        }

        return $id;
    }

    /**
     * function copy( $id, $back_url ).
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contain the back url (not used yet)
     *
     * @return int $id if success FALSE if fail
     **/
    public function copy($id, $back_url = null)
    {
        //find source info
        list($title, $descr, $author) = sql_fetch_row(sql_query('
		SELECT title, description, author 
		FROM ' . $GLOBALS['prefix_lms'] . "_glossary 
		WHERE idGlossary = '" . (int) $id . "'"));

        //insert new item
        $insertQuery = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_glossary 
		SET title = '" . sql_escape_string($title) . "',
			description = '" . sql_escape_string($descr) . "',
			author = '" . $author . "'";
        if (!sql_query($insertQuery)) {
            return false;
        }
        list($idGlossary) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
        //retriving term
        $reTerm = sql_query('
			SELECT term, description 
			FROM ' . $GLOBALS['prefix_lms'] . "_glossaryterm 
			WHERE idGlossary = '" . $id . "'");
        while (list($term, $term_descr) = sql_fetch_row($reTerm)) {
            $query_ins = '
				INSERT INTO ' . $GLOBALS['prefix_lms'] . "_glossaryterm 
				SET idGlossary = '" . $idGlossary . "',
					term = '" . sql_escape_string($term) . "',
					description = '" . sql_escape_string($term_descr) . "'";
            if (!sql_query($query_ins)) {
                $this->del($idGlossary);

                return false;
            }
        }

        return $idGlossary;
    }

    /**
     * function play( $id, $id_param, $back_url ).
     *
     * @param int    $id       contains the resource id
     * @param int    $id_param contains the id needed for params retriving
     * @param string $back_url contain the back url
     *
     * @return nothing return
     **/
    public function play($id, $id_param, $back_url)
    {
        require_once _lms_ . '/modules/glossary/do.glossary.php';

        $this->id = $id;
        $this->back_url = $back_url;

        play($this, $id_param);
    }

    /**
     * function search( $key ).
     *
     * @param string $key contains the keyword to search
     *
     * @return array with results found
     **/
    public function search($key)
    {
        $output = false;
        $query = "SELECT * FROM %lms_glossary WHERE title LIKE '%" . $key . "%' OR description LIKE '%" . $key . "%' ORDER BY title";
        $res = $this->db->query($query);
        $results = [];
        if ($res) {
            $output = [];
            while ($row = $this->db->fetch_obj($res)) {
                $output[] = [
                    'id' => $row->idGlossary,
                    'title' => $row->title,
                    'description' => $row->description,
                ];
            }
        }

        return $output;
    }
}
