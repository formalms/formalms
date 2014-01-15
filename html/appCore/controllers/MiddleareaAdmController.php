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

class MiddleareaAdmController extends AdmController {

	protected $db;
	protected $table;

    public function  __construct() {
            $this->db = DbConn::getInstance();
            $this->table = 'learning_middlearea';
    }
        
	public function order() {
		$list = $_GET['list'];
	
		$elements = explode(",", $list);
		$order = 1;
		foreach($elements as $element) {
			if(!sql_query("UPDATE ".$this->table." SET sequence = '$order' WHERE obj_index = '$element'")) {
                        	echo "false";
			}
			$order = $order + 1;
		}
		echo "true";

	}

}

?>
