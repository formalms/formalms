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

require_once($GLOBALS['where_lms'] . '/class.module/track.object.php');
require_once(Docebo::inc(_folder_lms_ . '/class.module/learning.test.php'));

class Track_Test extends Track_Object
{

    protected $idTest;

    protected $number_of_attempt;

    /**
     * object constructor
     * Table : learning_commontrack
     * idReference | idUser | idTrack | objectType | date_attempt  | status |
     **/
    function Track_Test($idTrack, $idResource = false, $idParams = false, $back_url = NULL)
    {
        $this->objectType = 'test';
        parent::Track_Object($idTrack);

        $this->db = DbConn::getInstance();
        if ($idTrack !== NULL) {
            $res = $this->db->query("SELECT idTest, number_of_attempt FROM %lms_testtrack WHERE idTrack = '" . (int)$idTrack . "'");
            if ($res && $this->db->num_rows($res) > 0) {
                list($this->idTest, $this->number_of_attempt) = $this->db->fetch_row($res);
            }
        }

        $this->idResource = $idResource;
        $this->idParams = $idParams;
        if ($back_url === NULL) $this->back_url = array();
        else $this->back_url = $back_url;
    }

    /**
     * function createTrack( $idUser, $idTest, $idReference, $attempt_number = 0  )
     *
     * create a new row in the _testtrack table for tracking purpose
     *
     * @param int $idUser the id of the user that display the object
     * @param int $idTest the id of the test that is displayed
     * @param int $idReference the idReference from the table of the lesson
     *
     * @return int    idTrack if the row is created correctly otherwise false
     **/
    function createNewTrack($idUser, $idTest, $idReference, $attempt_number = 0)
    {


        $query = "
		INSERT INTO " . $GLOBALS['prefix_lms'] . "_testtrack 
		SET idUser = '" . (int)$idUser . "', 
			idTest = '" . (int)$idTest . "', 
			idReference = '" . (int)$idReference . "', 
			date_attempt = '" . date("Y-m-d H:i:s") . "', 
			date_end_attempt = '" . date("Y-m-d H:i:s") . "', 
			last_page_seen = '0', 
			number_of_save = '0',
			number_of_attempt = '" . $attempt_number . "'";
        if (!sql_query($query)) {

            return false;
        }

        list($idTrack) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
        if (!$idTrack) return false;
        else return $idTrack;
    }

    function getTrack($id_test, $id_user)
    {

        $query = "
		SELECT idTrack
		FROM " . $GLOBALS['prefix_lms'] . "_testtrack 
		WHERE idUser = '" . $id_user . "' AND idTest = '" . $id_test . "'";
        $re = sql_query($query);

        if (!sql_num_rows($re)) return false;
        list($id_track) = sql_fetch_row($re);
        return $id_track;
    }

    public static function getIdTracksFromTest($id_test)
    {
        $query_track = "SELECT idTrack"
            . " FROM " . $GLOBALS['prefix_lms'] . "_testtrack"
            . " WHERE idTest = '" . $id_test . "'";

        $re = sql_query($query_track);

        $idTracks = array();
        if (!sql_num_rows($re)) {
            return $idTracks;
        }

        while (list($id_track) = sql_fetch_row($re)) {
            $idTracks[] = $id_track;
        }
        return $idTracks;
    }

    function getIdTrack($idReference, $idUser, $idResource, $createOnFail = FALSE)
    {

        $rsTrack = $this->getTrack($idResource, $idUser);
        if ($rsTrack !== FALSE) {
            return array(TRUE, $rsTrack);
        } else if ($createOnFail) {
            $rsTrack = $this->createNewTrack($idUser, $idResource, $idReference);
            return array(FALSE, $rsTrack);
        }
        return FALSE;
    }

    /**
     * function isTrack( $idUser, $idTest, $idReference )
     *
     * control if exists at least one row in _testtrack table for tracking purpose
     *
     * @param int $idUser the id of the user that display the object
     * @param int $idTest the id of the test that is displayed
     * @param int $idReference the idReference from the table of the lesson
     *
     * @return int    true if the row exists otherwise false
     **/
    function isTrack($idUser, $idTest, $idReference)
    {


        $query = "
		SELECT COUNT(*) 
		FROM " . $GLOBALS['prefix_lms'] . "_testtrack 
		WHERE idUser = '" . (int)$idUser . "' AND 
			idTest = '" . (int)$idTest . "' AND 
			idReference = '" . (int)$idReference . "'";
        list($re_track) = sql_fetch_row(sql_query($query));

        return $re_track;
    }

    /**
     * function getTrackInfo( $idUser, $idTest, $idReference )
     *
     * return some information abiout a track
     *
     * @param int $idUser the id of the user that display the object
     * @param int $idTest the id of the test that is displayed
     * @param int $idReference the idReference from the table of the lesson
     *
     * @return array    return false if track doesn't exists, otherwise return an array with some info in this way:
     *                    array (
     *                        idTrack,
     *                        date_attempt,
     *                        date_end_attempt,
     *                        last_page_seen,
     *                        last_page_saved,
     *                        number_of_save
     *                    )
     *
     **/
    function getTrackInfo($idUser, $idTest, $idReference)
    {
        $query = "
			SELECT idTrack, date_attempt, date_end_attempt, last_page_seen, last_page_saved, number_of_save, number_of_attempt, attempts_for_suspension, suspended_until
			FROM %lms_testtrack
			WHERE idUser = '" . (int)$idUser . "' AND
				idTest = '" . (int)$idTest . "' AND
				idReference = '" . (int)$idReference . "'";
        $re_track = sql_query($query);

        if (!sql_num_rows($re_track)) return array();
        else return sql_fetch_array($re_track);
    }


    function getTrackInfoById($idTrack)
    {
        $query = "
			SELECT idTrack, date_attempt, date_end_attempt, last_page_seen, last_page_saved, score, number_of_save, number_of_attempt, attempts_for_suspension, suspended_until
			FROM %lms_testtrack
			WHERE idTrack = '" . (int)$idTrack . "'";
        $re_track = sql_query($query);

        if (!sql_num_rows($re_track)) return array();
        else return sql_fetch_array($re_track);
    }


    /**
     * function updateTrack( $idTrack, $new_info )
     *
     * create a new row in the _testtrack table for tracking purpose
     *
     * @param int $idTrack the track of the object
     * @param array $new_info an array with the new information
     *
     * @return bool    true if success false otherwise
     **/
    function updateTrack($idTrack, $new_info)
    {


        $first = true;
        if (!is_array($new_info)) return true;
        $query = "
		UPDATE " . $GLOBALS['prefix_lms'] . "_testtrack 
		SET ";
        foreach ($new_info as $field_name => $field_value) {

            $query .= ($first ? '' : ', ');
            if ($field_value == NULL) $query .= $field_name . " = NULL ";
            else  $query .= $field_name . " = '" . $field_value . "'";
            if ($first) $first = false;
        }
        $query .= " WHERE idTrack = '" . (int)$idTrack . "'";

        if (isset($_POST['show_review'])) return true;

        if (!sql_query($query)) return false;
        else return true;
    }

    /**
     * print in standard output
     **/
    function loadReport($idUser = false, $mvc = false)
    {
        require_once(Forma::inc(_lms_.'/modules/test/do.test.php'));
        if ($idUser) {
            $output = user_report($idUser, $this->idResource, $this->idParams, false, $mvc);
            if ($mvc) return $output;
        }
    }

    /**
     * @return bool    true if this object use extra colum in user report
     */
    function otherUserField()
    {
        return true;
    }

    /**
     * @return array    an array with the header of extra colum
     */
    function getHeaderUserField()
    {

        return array(
            array('content' => _TEST_POINTDO, 'type' => 'align_right')
        );
    }

    /**
     * @return array    an array with the extra colum
     */
    function getUserField()
    {


        $field = array();
        $re_score = sql_query("
		SELECT idUser, is_end, type_of_result, result 
		FROM " . $GLOBALS['prefix_lms'] . "_testtrack 
		WHERE idTest = '" . $this->idResource . "'");
        while (list($id_user, $is_end, $point_type, $point_do) = sql_fetch_row($re_score)) {

            if ($is_end) $field[$id_user] = array($point_do . ($point_type ? '%' : ''));
        }
        return $field;
    }

    /**
     * @return idTrack if exists or false
     **/
    function deleteTrack($idTrack)
    {

        $query = "DELETE FROM " . $GLOBALS['prefix_lms'] . "_commontrack "
            . " WHERE idTrack='" . (int)$idTrack . "'"
            . "   AND objectType='test'";
        if (!sql_query($query)) return false;

        $query = "
		DELETE FROM " . $GLOBALS['prefix_lms'] . "_testtrack 
		WHERE idTrack='" . (int)$idTrack . "'";

        if (sql_query($query)) return true;
        else return false;
    }

    function deleteTrackInfo($id_lo, $id_user)
    {

        $query = "SELECT idUser, idReference, idTrack FROM " . $this->_table .
            " WHERE idUser=" . (int)$id_user . " AND idReference=" . (int)$id_lo .
            " AND objectType='test'";
        $res = sql_query($query);
        if ($res && sql_num_rows($res) > 0) {

            list($id_user, $id_lo, $id_track) = sql_fetch_row($res);
            $query_question = "SELECT q.idQuest, q.type_quest, t.type_file, t.type_class "
                . " FROM %lms_testquest AS q JOIN %lms_quest_type AS t "
                . " WHERE q.idTest = '" . $id_lo . "' AND q.type_quest = t.type_quest "
                . " ORDER BY q.sequence";
            $re_quest = sql_query($query_question);
            while (list($idQuest, $type_quest, $type_file, $type_class) = sql_fetch_row($re_quest)) {
                $quest_obj = new $type_class($idQuest);
                if (!$quest_obj->deleteAnswer($id_track)) return false;
            }

            $query_page = "DELETE FROM %lms_testtrack_page WHERE idTrack = '" . $id_track . "'";
            $query_quest = "DELETE FROM %lms_testtrack_quest WHERE idTrack = '" . $id_track . "'";
            if (!sql_query($query_page)) return false;
            if (!sql_query($query_quest)) return false;

            $re_update = $this->deleteTrack($id_track);
            if ($re_update) {
                $re_common = parent::deleteTrackInfo($id_lo, $id_user);
                if ($re_common) return true;
            }
        }

        return false;
    }

    /**
     * @return Track_TestAnswer[]
     */
    public function getAnswers()
    {
        $res = sql_query("SELECT idQuest, idAnswer, score_assigned, more_info, number_time FROM %lms_testtrack_answer WHERE idTrack = '" . (int)$this->idTrack . "'");
        $list = array();
        while (list($questId, $answerId, $score, $moreInfo, $numberTime) = sql_fetch_row($res)) {
            $list[$questId][$numberTime] = new Track_TestAnswer($this->idTrack, $questId, $answerId, $score, $moreInfo);
        }
        return $list;
    }

    /**
     * @return mixed
     */
    public function getIdTest()
    {
        return $this->idTest;
    }

    /**
     * @param mixed $idTest
     * @return Track_Test
     */
    public function setIdTest($idTest)
    {
        $this->idTest = $idTest;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumberOfAttempt()
    {
        return (int)$this->number_of_attempt;
    }

    /**
     * @param mixed $number_of_attempt
     * @return Track_Test
     */
    public function setNumberOfAttempt($number_of_attempt)
    {
        $this->number_of_attempt = $number_of_attempt;
        return $this;
    }

    public function getTestObj()
    {
        return new Learning_Test($this->getIdTest());
    }

    static function getValidTestTrackFromTestAndUsers($idTest, $idStrudents)
    {

        $query_track = "SELECT idTrack"
            . " FROM " . $GLOBALS['prefix_lms'] . "_testtrack"
            . " WHERE idTest = '" . $idTest . "'"
            . " AND score_status = 'valid'"
            . " AND idUser in (" . implode(",", $idStrudents) . ")";

        $result_track = sql_query($query_track);

        $idTracks = array();
        while (list($id_track) = sql_fetch_row($result_track)) {
            $idTracks[] = $id_track;
        }

        return $idTracks;
    }

    static function getValidTotalPlaysTestTrackFromTestAndUsers($idTest, $idStrudents)
    {

        $query_total_play = "SELECT COUNT(*)"
            . " FROM " . $GLOBALS['prefix_lms'] . "_testtrack"
            . " WHERE idTest = '" . $idTest . "'"
            . " AND score_status = 'valid'"
            . " AND idUser in (" . implode(",", $idStrudents) . ")";

        list($total_play) = sql_fetch_row(sql_query($query_total_play));

        return $total_play;
    }

    static function getTestTrackAnswersFromTrack($idTrack){
        $query_track_answer = "SELECT idQuest, idAnswer, more_info"
            . " FROM " . $GLOBALS['prefix_lms'] . "_testtrack_answer"
            . " WHERE idTrack = '" . $idTrack . "'";

        $result_track_answer = sql_query($query_track_answer);

        $trackAnswers = array();
        while (list($idQuest, $id_answer, $more_info) = sql_fetch_row($result_track_answer)) {

            $trackAnswers[] = array("idQuest" => $idQuest,"idAnswer" => $id_answer,"more_info" => $more_info);
        }

        return $trackAnswers;
    }
}

?>
