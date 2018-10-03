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

/**
 * @package admin-core
 * @subpackage event
 * @version  $Id: lib.eventmanager.php 955 2007-02-03 15:19:40Z fabio $
 * @author   Emanuele Sandri <esandri@docebo.com>
 */

require_once(_base_.'/lib/lib.event.php' );

class DoceboEventManager {

	/**
	 * Register a new event class
	 * @param string $class_name the name of the class
	 * @return int idClass of the registered class, FALSE otherwise
	 * @static
	 * @access public
	**/
	function registerEventClass( $class_name ) {
		$class_id = DoceboEventClass::getClassId( $class_name );
		if( $class_id !== FALSE ) {
			return $class_id;
		} else {
			$query = "INSERT INTO ".$GLOBALS['prefix_fw']."_event_class"
					." (name) VALUES ('".$class_name."')";
			$result = sql_query( $query );
			if( $result )
				return sql_insert_id();
			else
				return FALSE;
		}
	}

	/**
	 * Create a new event based on this class
	 * @param string $class_name the class of the event
	 * @param string $module the module of the event
	 * @param string $section the section of the event
	 * @param int $priority the priority of the event
	 * @param string $description the description of the event
	 * @return DoceboEvent $event the event object
	 * @static
	 * @access public
	 * @internal if you pass an int $class_id in the first parameter you can
	 *				create a new event from the class_id specified
	**/
	function &newEvent($class_name, $module, $section, $priority, $description ) {
		$class = new DoceboEventClass( $class_name );
		$istance = $class->createEvent($module, $section, $priority, $description );
		return $istance;
	}

	/**
	 * Register a new consumer for a class of events
	 * @param mixed $class_name the name of the class to be associated to this consumer
	 *						if this parameter is an array the consumer will be
	 *						related to all event class identified by the array items
	 * @param string $consumer_class the PHP class of the consumer
	 * @param string $consumer_file the PHP file to be included before the $consumer_class
	 * 					instantiation
	 * @return boolean TRUE on success, FALSE otherwise
	 * @static
	 * @access public
	 * @internal if you pass an int $class_id or an array of int in the first parameter
	 *				you can relate the consumer to these class ids.
	**/
	function registerEventConsumer($class_name, $consumer_class, $consumer_file ) {
		$idConsumer = DoceboEventManager::_registerConsumer($consumer_class, $consumer_file);
		if( $idConsumer === FALSE )
			return FALSE;
		if( is_array( $class_name ) ) {
			foreach( $class_name as $cn ) {
				$class_id = DoceboEventClass::getClassId( $cn );
				if( $class_id !== FALSE )
					DoceboEventManager::_makeConsumerClassRelation($idConsumer, $class_id);
				else
					return FALSE;
			}
		} else {
			$class_id = DoceboEventClass::getClassId( $class_name );
			if( $class_id !== FALSE )
				DoceboEventManager::_makeConsumerClassRelation($idConsumer, $class_id);
			else
				return FALSE;
		}
		return TRUE;
	}

	/**
	 * Make association from consumer and class
	 * @param int $consumer_id the id of the consumer
	 * @param int $class_id the id of the event class
	 * @static
	 * @access private
	**/
	function _makeConsumerClassRelation( $consumer_id, $class_id ) {
		$query = "INSERT INTO ".$GLOBALS['prefix_fw']."_event_consumer_class"
				." (idConsumer, idClass) VALUES "
				." ('".(int)$consumer_id."','".(int)$class_id."')";
		sql_query( $query );
	}

	/**
	 * Register a new consumer
	 * @param string $consumer_class
	 * @param string $consumer_file
	 * @return int the id of the registered consumer, FALSE if error
	 * @static
	 * @access private
	**/
	function _registerConsumer($consumer_class, $consumer_file) {
		$consumer_id = DoceboEventConsumer::getConsumerId( $consumer_class );
		if( $consumer_id !== FALSE ) {
			return $consumer_id;
		} else {
			$query = "INSERT INTO ".$GLOBALS['prefix_fw']."_event_consumer"
					." (consumer_class, consumer_file) VALUES "
					." ('".$consumer_class."','".$consumer_file."')";
			$result = sql_query( $query );
			if( $result )
				return sql_insert_id();
			else
				return FALSE;
		}
	}

	/**
	 * return the array of consumers realted to a given event class
	 * @param int $class_id the id of the event class
	 * @return array an array of the consumer related to $class_id
	 *				any element of the array is
	 *					consumer_id => array( consumer_class, consumer_file )
	 * @static
	 * @access public
	**/
	function listConsumerFromClassId( $class_id ) {
		$query = "SELECT DISTINCT ev.idConsumer, ev.consumer_class, ev.consumer_file "
				."  FROM ".$GLOBALS['prefix_fw']."_event_consumer AS ev"
				."  JOIN ".$GLOBALS['prefix_fw']."_event_consumer_class AS ecc"
				." WHERE ecc.idClass = '".$class_id."'"
				."   AND ev.idConsumer = ecc.idConsumer";
		$rs = sql_query($query);
		$result = array();
		if( $rs === FALSE ) {
			echo $query;
			echo "  Errore: ".sql_error();
			return FALSE;
		}
		if( sql_num_rows($rs) > 0 ) {
			while( list( $id, $class, $file ) = sql_fetch_row( $rs ) )
				$result[$id] = array( $class, $file );
		}
		return $result;
	}

	/**
	 * This method execute the dispatch of the event on all the consumer related
	 *	to the class of the event
	 * @param DoceboEvent $event the event to be dispatched
	 * @static
	 * @access public
	**/
	function dispatch( &$event ) {

		$arr_consumer = DoceboEventManager::listConsumerFromClassId( $event->getClassId() );

		foreach( $arr_consumer as $consumer_id => $consumer_param ) {
			require_once(Forma::inc($GLOBALS['where_framework'].$consumer_param[1]));

			$consumer = eval( "return new ".$consumer_param[0]."(". $consumer_id .");" );
			$consumer->actionEvent( $event );
		}
	}
}

class EventMessageComposer {

	var $module;

	var $platform;

	var $lang;

	var $arr_subject;

	var $arr_body;

	var $subject_composed;

	var $body_composed;

	/**
	 * @param string 	$module 		the module name
	 * @param string 	$platform 		the platform name
	 * @param string 	$arr_subject 	the array with the subject info
	 * @param string 	$arr_body 		the array with the object info
	 *  array(
	 *		array( 	['lang_text'] => '_ALERT_TEXT',
	 *				['lang_substtution'] =>  array( text_find => text_replace , ... )
	 *		), ...
	 *	)
	 */
	public function  __construct($module = false, $platform = false, $arr_subject = false, $arr_body = false) {

		$this->module 		= 'email';
		$this->platform 	= false;
		$this->arr_subject 	= $arr_subject;
		$this->arr_body 	= $arr_body;
		$this->subject_composed = false;
		$this->body_composed = false;
	}

	function setSubject($array_info, $media = false) {

		$this->subject_composed = false;
		if($media === false) $this->arr_subject = $array_info;
		else $this->arr_subject[$media] = $array_info;
	}

	function setBody($array_info, $media = false) {

		$this->body_composed = false;
		if($media === false) $this->arr_body = $array_info;
		else $this->arr_body[$media] = $array_info;
	}

	function setSubjectLangText($media, $lang_text, $arr_substitution, $simple_text = false) {

		$this->subject_composed = false;
		$this->arr_subject[$media][] = array(
			'lang_text' => $lang_text,
			'lang_substtution' => $arr_substitution,
			'simple_text' => $simple_text);
	}

	function setBodyLangText($media, $lang_text, $arr_substitution, $simple_text = false) {

		$this->body_composed = false;
		$this->arr_body[$media][] = array(
			'lang_text' => $lang_text,
			'lang_substtution' => $arr_substitution,
			'simple_text' => $simple_text);
	}

	function getSubject($media, $language) {

		if($this->subject_composed !== false && isset($this->subject_composed[$media][$language]))
			return $this->subject_composed[$media][$language];

		if(isset($this->arr_subject[$media])) {

			$this->subject_composed[$media][$language] = $this->_composeElement($this->arr_subject[$media], $language);
		} else return '';

		return $this->subject_composed[$media][$language];
	}

	function getBody($media, $language) {

		if($this->body_composed !== false && isset($this->body_composed[$media][$language]))
			return $this->body_composed[$media][$language];

		if(isset($this->arr_body[$media])) {

			$this->body_composed[$media][$language] = $this->_composeElement($this->arr_body[$media], $language, $media);
		} else return '';

		return $this->body_composed[$media][$language];
	}

	function _composeElement($arr_element, $language, $media = 'email') {

		$compose = '';
		Lang::init('email', false, $language);
		Lang::init('sms', false, $language);
		while(list(, $arr_text) = each($arr_element) ) {
			if(isset($arr_text['simple_text']) && $arr_text['simple_text'] === true) {
				$compose .= $arr_text['lang_text'];
			} else {
				$compose .= Lang::t($arr_text['lang_text'], $media, $arr_text['lang_substtution'], $language);
			}
		}
		return $compose;
	}
	
	function prepare_serialize() {
		unset($this->lang);
	}

	function after_unserialize() {}
}

/**
 * This function encapsulate a set of common instruction for event notification generation
 * @param string 				$class 			The class name ho event (eg. UserMod)
 * @param string 				$module			The module generator (eg. directory)
 * @param string 				$section 		The section in module that generate event (eg. edit)
 * @param int	 				$priority		The priority level of event
 * @param string 				$description 	The description of the event
 * @param array	 				$recipients 	An array of userid that should be notified
 * @param EventMessageComposer 	$msg_composer 	a class for message composition
 * @param bool					$force_email_send		if true the message is sent to all the user in $recipients ignoring their settings for email
 **/
function createNewAlert(	$class,$module,$section,$priority,$description,
							$recipients,$msg_composer,$force_email_send = false) {

	$event =& DoceboEventManager::newEvent($class, $module, $section, $priority, $description);

	$event->deleteOldProperty();

	if (is_array($recipients["to"]) && is_array($recipients["cc"]) && is_array($recipients["bcc"]) ){
		$event->setProperty('recipientid',implode(',',$recipients["to"]));
		$event->setProperty('recipientcc',implode(',',$recipients["cc"]));
		$event->setProperty('recipientbcc',implode(',',$recipients["bcc"]));
	} else {
		$event->setProperty('recipientid',implode(',',$recipients));
	}
	$event->setProperty('subject', addslashes($msg_composer->getSubject('email', getLanguage() ) ));
	$event->setProperty('body', addslashes($msg_composer->getBody('email', getLanguage() )) );
	$msg_composer->prepare_serialize(); // __sleep is preferred but i preferr this method
	$event->setProperty('MessageComposer', addslashes(rawurlencode(serialize($msg_composer))) );
	$event->setProperty('force_email_send', ( $force_email_send === false ? 'false' : 'true' ) );
	DoceboEventManager::dispatch($event);
}

/**
 * This function encapsulate a set of common instruction for event notification generation
 * @param string $class 	The class name ho event (eg. UserMod)
 * @param string $module	The module generator (eg. directory)
 * @param string $section 	The section in module that generate event (eg. edit)
 * @param int	 $priority	The priority level of event
 * @param string $description The description of the event
 * @param array	 $recipients An array of userid that should be notified
 * @param string $subject 	The subject of notification
 * @param string $body		The body of the notification
 **/
/*function createNewAlert(	$class,$module,$section,$priority,$description,
							$recipients,$subject,$body ) {
	$event =& DoceboEventManager::newEvent($class, $module, $section, $priority, $description);
	$event->setProperty('recipientid',implode(',',$recipients));
	$event->setProperty('subject',$subject);
	$event->setProperty('body',$body);
	DoceboEventManager::dispatch($event);
}
*/
?>
