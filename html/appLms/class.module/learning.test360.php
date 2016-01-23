<?php defined("IN_FORMA") or die('Direct access is forbidden.');

require_once(dirname(__FILE__) . '/learning.test.php');
require_once($GLOBALS['where_lms'] . '/modules/question/class.question.php');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

class Learning_Test360 extends Learning_Test
{

    /**
     * function learning_Test()
     * class constructor
     **/
    function Learning_Test360($id = NULL)
    {
        parent::Learning_Test($id);
        $this->obj_type = 'test360';
    }

    function getObjectType()
    {
        return $this->obj_type;
    }

    /**
     * @param array $excludedTypes
     * @return Question[]
     */
    function getQuests($excludedTypes = array('break_page'))
    {
        $objList = array();
        $query = "SELECT idQuest FROM %lms_testquest WHERE idTest = '" . (int)$this->id . "'";
        $query .= " AND type_quest NOT IN (";
        foreach ($excludedTypes as $excludedType) {
            $query .= "'".$excludedType."'";
            if (next($excludedTypes)==true) $query .= ",";
        }
        $query .= ")";
        $res = $this->db->query($query);
        while (list($idQuest) = $this->db->fetch_row($res)) {
            $objList[$idQuest] = new Question($idQuest);
        }
        return $objList;
    }

}

?>
