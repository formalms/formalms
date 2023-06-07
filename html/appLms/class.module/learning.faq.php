<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class Learning_Faq extends Learning_Object
{
    public $id;

    public $idAuthor;

    public $title;

    public $back_url;

    /**
     * object constructor.
     **/
    public function __construct($id = null)
    {
        parent::__construct($id);
        if ($id !== null) {
            $res = $this->db->query("SELECT author, title FROM %lms_faq_cat WHERE idCategory = '" . $id . "'");
            if ($res && $this->db->num_rows($res) > 0) {
                list($this->idAuthor, $this->title) = $this->db->fetch_row($res);
                $this->isPhysicalObject = true;
            }
        }
    }

    public function getObjectType()
    {
        return 'faq';
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

        \FormaLms\lib\Forma::removeErrors();

        require_once _lms_ . '/modules/faq/faq.php';
        addfaqcat($this);
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

        \FormaLms\lib\Forma::removeErrors();

        require_once _lms_ . '/modules/faq/faq.php';
        modfaqgui($this);
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
        if (!sql_query('
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_faq 
		WHERE idCategory = '" . $id . "'")) {
            return false;
        }
        if (!sql_query('
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_faq_cat 
		WHERE idCategory = '" . $id . "'")) {
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
		FROM ' . $GLOBALS['prefix_lms'] . "_faq_cat 
		WHERE idCategory = '" . $id . "'"));

        //insert new item
        $query_ins = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_faq_cat
		SET title = '" . sql_escape_string($title) . "',
			description = '" . sql_escape_string($descr) . "',
			author = '" . $author . "'";
        if (!sql_query($query_ins)) {
            return false;
        }
        list($idCat) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));

        //retriving quest
        $reQuest = sql_query('
		SELECT question, title, keyword, answer, sequence 
		FROM ' . $GLOBALS['prefix_lms'] . "_faq 
		WHERE idCategory = '" . (int) $id . "'");
        while (list($question, $title, $keyword, $answer, $seq) = sql_fetch_row($reQuest)) {
            $query_ins = '
			INSERT INTO ' . $GLOBALS['prefix_lms'] . "_faq
			SET idCategory = '" . $idCat . "',
				question = '" . sql_escape_string($question) . "',
				title = '" . sql_escape_string($title) . "',
				keyword = '" . sql_escape_string($keyword) . "',
				answer = '" . sql_escape_string($answer) . "',
				sequence = '" . $seq . "'";
            if (!sql_query($query_ins)) {
                $this->del($idCat);

                return false;
            }
        }

        return $idCat;
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
        require_once _lms_ . '/modules/faq/do.faq.php';

        $this->id = $id;
        $this->back_url = $back_url;

        $this->checkObjPerm();

        $step = importVar('next_step');
        switch ($step) {
            default:
                play($this, $id_param);
             break;
        }
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
        $query = "SELECT * FROM %lms_faq WHERE title LIKE '%" . $key . "%' OR question LIKE '%" . $key . "%' ORDER BY title";
        $res = $this->db->query($query);
        $results = [];
        if ($res) {
            $output = [];
            while ($row = $this->db->fetch_obj($res)) {
                $output[] = [
                    'id' => $row->idFaq,
                    'title' => $row->title,
                    'description' => $row->question,
                ];
            }
        }

        return $output;
    }
}
