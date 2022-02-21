<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package DoceboLms
 * @subpackage Course menu managment
 * @category 
 * @author Fabio Pirovano
 * @version $Id:$
 * @since 3.5
 * 
 * ( editor = Eclipse 3.2.0[phpeclipse,subclipse,WTP], tabwidth = 4 ) 
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_PreAssessment extends LmsAdminModule {
	
	
	function loadBody() {
		
		require_once(dirname(__FILE__).'/../modules/'.$this->module_name.'/'.$this->module_name.'.php');
		preAssessmentDispatch($GLOBALS['op']);
	}
	
	// Function for permission managment
	function getAllToken($op) {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'mod' => ['code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png'],
			'subscribe' => ['code' => 'subscribe',
								'name' => '_SUBSCRIBE',
								'image' => 'subscribe/add_subscribe.gif']
        ];
	}
}

?>