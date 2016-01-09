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

class Test360LmsController extends LmsController
{

    public $name = 'calendar';

    protected $_default_action = 'show';

    public function isTabActive($tab_name)
    {
        return true;
    }

    public function init()
    {

    }

    private function _formatCsvValue($value, $delimiter) {
        $value = html_entity_decode($value, ENT_QUOTES, 'utf-8');
        $formatted_value = str_replace($delimiter, '\\'.$delimiter, $value);
        return $delimiter.$formatted_value.$delimiter;
    }

    public function exportCSVTask()
    {
        require_once(_base_.'/lib/lib.download.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');
        require_once($GLOBALS['where_lms'] . '/class.module/track.test.php');
        require_once($GLOBALS['where_lms'] . '/class.module/track.testAnswer.php');
        require_once($GLOBALS['where_lms'] . '/class.module/learning.test360.php');

        $idTest = $_GET['idTest'];

        $testObj = new Learning_Test360($idTest);

        $query_test_users = "
        SELECT value, params
        FROM " . $GLOBALS['prefix_lms'] . "_organization LEFT JOIN " . $GLOBALS['prefix_lms'] . "_organization_access ON idOrg = idOrgAccess
        WHERE objectType = '" . $testObj->getObjectType() . "' AND idResource = '" . $testObj->getId() . "'";
        $re_test_users = sql_query($query_test_users);


        $separator = ',';
        $delimiter = '"';
        $line_end = "\r\n";

        $output = "";

        $head = array();
        $head[] = $this->_formatCsvValue("", $delimiter);
        $head[] = $this->_formatCsvValue("", $delimiter);

        $subhead = array();
        $subhead[] = $this->_formatCsvValue("Domanda", $delimiter);
        $subhead[] = $this->_formatCsvValue("Categoria", $delimiter);

        $questionsArray = array();


        while (list($idUser, $testRelation) = sql_fetch_row($re_test_users)) {
            $userInfo = Docebo::user()->getAclManager()->getUser($idUser, false);
            $firstname = $userInfo[ACL_INFO_FIRSTNAME];
            $lastname = $userInfo[ACL_INFO_LASTNAME];
            $head[] = $this->_formatCsvValue($firstname.' '.$lastname, $delimiter);
            $subhead[] = $this->_formatCsvValue($testRelation, $delimiter);

            $idTrack = Track_Test::getTrack($testObj->getId(), $idUser);
            $trackTest = new Track_Test($idTrack);

            $answers = $trackTest->getAnswers();
            foreach ($testObj->getQuests() as $question) {
                if (!array_key_exists($question->getId(), $questionsArray)){
                    $questionsArray[$question->getId()] = array();
                    $questionsArray[$question->getId()][] = $this->_formatCsvValue($question->getTitle(), $delimiter);
                    $questionsArray[$question->getId()][] = $this->_formatCsvValue($question->getCategoryName(), $delimiter);
                }
                if (array_key_exists($question->getId(), $answers)) {
                    $questionsArray[$question->getId()][] = $this->_formatCsvValue($answers[$question->getId()]->getMoreInfo(), $delimiter);
                } else {
                    $questionsArray[$question->getId()][] = $this->_formatCsvValue("", $delimiter);
                }
            }
        }
        $output .= implode($separator, $head).$line_end;
        $output .= implode($separator, $subhead).$line_end;
        foreach ($questionsArray as $questionArray) {
            $output .= implode($separator, $questionArray).$line_end;
        }
        sendStrAsFile($output, 'export_test360_'.date("Ymd").'.csv');

    }

    public function reportTask()
    {
        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');
        require_once($GLOBALS['where_lms'] . '/class.module/track.test.php');
        require_once($GLOBALS['where_lms'] . '/class.module/track.testAnswer.php');
        require_once($GLOBALS['where_lms'] . '/class.module/learning.test360.php');

        $idTrack = $_GET['idTrack'];
        $idTest = $_GET['idTest'];
        $studentName = $_GET['studentName'];

        $query_testreport = "
        SELECT DATE_FORMAT(date_attempt, '%d/%m/%Y %H:%i'), score
        FROM " . $GLOBALS['prefix_lms'] . "_testtrack_times
        WHERE idTrack = '" . $idTrack . "' AND idTest = '" . $idTest . "' ORDER BY date_attempt";
        $re_testreport = sql_query($query_testreport);

        $testObj = new Learning_Test360($idTest);


        $query_test_users = "
        SELECT value, params
        FROM " . $GLOBALS['prefix_lms'] . "_organization LEFT JOIN " . $GLOBALS['prefix_lms'] . "_organization_access ON idOrg = idOrgAccess
        WHERE objectType = '" . $testObj->getObjectType() . "' AND idResource = '" . $testObj->getId() . "'";
        $re_test_users = sql_query($query_test_users);

        $questAnswers = array();
        $categoryQuestAnswers = array();
        $categories = array();
        while (list($idUser, $testRelation) = sql_fetch_row($re_test_users)) {
            $idTrack = Track_Test::getTrack($testObj->getId(), $idUser);
            $userInfo = Docebo::user()->getAclManager()->getUser($idUser, false);
            $trackTest = new Track_Test($idTrack);

            foreach ($trackTest->getAnswers() as $answer) {
                if ($testRelation == 'OWNER') {
                    $questAnswers[$answer->getQuestId()]['auto']['total'] = (int)$answer->getMoreInfo();
                    $questAnswers[$answer->getQuestId()]['auto']['counter'] += 1;
                    $categoryQuestAnswers[$answer->getQuestion()->getCategoryId()]['auto']['total'] += (int)$answer->getMoreInfo();
                    $categoryQuestAnswers[$answer->getQuestion()->getCategoryId()]['auto']['counter'] += 1;
                } else {
                    $questAnswers[$answer->getQuestId()]['etero']['total'] += (int)$answer->getMoreInfo();
                    $questAnswers[$answer->getQuestId()]['etero']['counter'] += 1;
                    $categoryQuestAnswers[$answer->getQuestion()->getCategoryId()]['etero']['total'] += (int)$answer->getMoreInfo();
                    $categoryQuestAnswers[$answer->getQuestion()->getCategoryId()]['etero']['counter'] += 1;
                }
                if (!array_key_exists($answer->getQuestion()->getCategoryId(), $categories)) {
                    $categories[$answer->getQuestion()->getCategoryId()] = $answer->getQuestion()->getCategoryName($answer->getQuestion()->getCategoryId());
                }
            }
        }

        $lang =& DoceboLanguage::createInstance('coursereport', 'lms');

        $page_title = array(
            'index.php?modname=coursereport&amp;op=coursereport' => $lang->def('_TH_TEST_REPORT'),
            strip_tags($testObj->getTitle())
        );
        // Titolo
        $titleArea = getTitleArea($page_title, 'coursereport');
        // Breadcumps
        $backUI = getBackUi("javascript:history.go(-1)", Lang::t('_BACK', 'standard'));


        $tb = new Table(0, $testObj->getTitle() . ' : ' . $studentName);
        $tb->addHead(array(
            'N.',
            $lang->def('_DATE'),
            $lang->def('_SCORE'),
        ), array('min-cell', '', ''));

        $i = 1;
        while (list($date_attempt, $score) = sql_fetch_row($re_testreport)) {

            $tb->addBody(array($i++, $date_attempt, $score));
        }

        $this->render('report', array(
                'tb' => $tb,
                'titleArea' => $titleArea,
                'backUI' => $backUI,
                'questAnswers' => $questAnswers,
                'categories' => $categories,
                'categoryQuestAnswers' => $categoryQuestAnswers,
                'testObj' => $testObj
            )
        );
    }


}
