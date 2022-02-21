<?php defined("IN_FORMA") or die('Direct access is forbidden.');



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


	public function menuOrder() {

		$list = $_GET['list'];

		$elements = explode(",", $list);
		$order = 1;
		foreach($elements as $element) {

			CoreMenu::updateSequence($element,$order);

			$order++;
		}
		echo "true";
	}
}

?>
