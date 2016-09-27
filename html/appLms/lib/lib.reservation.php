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
 * Variable to read the array returned by the viewEvents() function
 */
define("EVENT_ID", 0);
define("EVENT_ID_LABORATORY", 1);
define("EVENT_ID_CATEGORY", 2);
define("EVENT_TITLE", 3);
define("EVENT_DESCRIPTION", 4);
define("EVENT_DATE", 5);
define("EVENT_MAX_USER", 6);
define("EVENT_DEADLINE", 7);
define("EVENT_FROM_TIME", 8);
define("EVENT_TO_TIME", 9);
define("EVENT_CATEGORY_NAME", 10);
define("EVENT_USER_SUBSCRIBED", 11);
define("EVENT_AVAILABLE_PLACES", 12);
define("EVENT_ID_COURSE", 13);
define("EVENT_LABORATORY_NAME", 14);

define("CATEGORY_ID", 0);
define("CATEGORY_NAME", 1);
define("CATEGORY_ID_COURSE", 2);
define("CATEGORY_MAX_SUBSCRIPTION", 3);

define("LABORATORY_ID", 0);
define("LABORATORY_NAME", 1);
define("LABORATORY_LOCATION", 2);
define("LABORATORY_DESCRIPTION", 3);
define("LABORATORY_ID_COURSE", 4);

require_once($GLOBALS['where_lms'].'/lib/lib.classroom.php');

/**
 * @package appLms
 * @subpackage reservation 
 * @author Marco Valloni
 */

/**
 * This class abstract the database structure of the reservation.
 */
class Man_Reservation {
	
	/**
	 * Class constructor, initialize the instance
	 */
	function Man_Reservation()
	{
	}
	
	/**
	 * @return string the events table of the reservation
	 */
	function getTableEvents()
	{
		
		return $GLOBALS['prefix_lms'].'_reservation_events';
	}
	
	/**
	 * @return string the laboratories table of the reservation
	 */
	/*function getTableLaboratories()
	{
		
		return $GLOBALS['prefix_lms'].'_reservation_laboratories';
	}*/
	
	/**
	 * @return string the subscribed table of the reservation
	 */
	function getTableSubscribed()
	{
		return $GLOBALS['prefix_lms'].'_reservation_subscribed';
	}
	
	/**
	 * @return string the category table of the reservation
	 */
	function getTableCategory()
	{
		return $GLOBALS['prefix_lms'].'_reservation_category';
	}
	
	/**
	 * @return string the classroom table of the lms
	 */
	function getTableClassroom()
	{
		return $GLOBALS['prefix_lms'].'_classroom';
	}
	
	/**
	 * Function that return the name of a category
	 * 
	 * @param int $id_category Id of the category
	 * 
	 * @return string Tha name of the category
	 */
	function getCategoryName($id_category)
	{
		$lang =& DoceboLanguage::createInstance('reservation');
		
		if ($id_category == 0)
			return $lang->def('_UNDEFINITED');
		
		$query = "SELECT name" .
				" FROM ".$this->getTableCategory()."" .
				" WHERE idCategory = '".$id_category."'";
		
		$result = sql_query($query);
		
		list($name_category) = sql_fetch_row($result);
		
		return $name_category;
	}
	
	/**
	 * Return the idst of the user subscibed to an event
	 * 
	 * @param int $id_event The id of the event
	 * 
	 * @return array Array with the id of the users
	 */
	function getSubscribedUserIdst($id_event)
	{
		$query = "SELECT idstUser" .
				" FROM ".$this->getTableSubscribed()."" .
				" WHERE idEvent = '".$id_event."'";
		
		$result = sql_query($query);
		
		$re = array();
		
		while(list($subscribed) = sql_fetch_row($result))
		{
			$re[] = $subscribed;
		}
		
		if (count($re))
			return $re;
		
		return false;
	}
	
	/**
	 * Function that return the number of user subscribed to an event
	 * 
	 * @param int $id_event Id of the event
	 * 
	 * @return int Number of subscribed user
	 */
	function getSubscribedUser($id_event)
	{
		$query = "SELECT COUNT(*)" .
				" FROM ".$this->getTableSubscribed()."" .
				" WHERE idEvent = '".$id_event."'";
		
		$result = sql_query($query);
		
		list($subscribed) = sql_fetch_row($result);
		
		return $subscribed;
	}
	/**
	 * Function that return the category of the db
	 * 
	 * @return array Array with the category in that format $array[ID_CATEGORY] = CATEGORY_NAME
	 */
	function getCategory($id_course = false)
	{
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$query = "SELECT idCategory, name" .
				" FROM ".$this->getTableCategory()."";
		if ($id_course)
			$query .= " WHERE idCourse = '".$id_course."'";
		
		$result = sql_query($query);
		$num_rows = sql_num_rows($result);
		
		$re = array();
		$re[0] = $lang->def('_UNDEFINITED');
		
		if($num_rows)
		{
			while(list($id_category, $name) = sql_fetch_row($result))
				$re[$id_category] = $name;
		}
		return $re;
	}
	
	function getEventCategory($id_event)
	{
		$query = "SELECT idCategory" .
				" FROM ".$this->getTableEvents()."" .
				" WHERE idEvent = '".$id_event."'";
		
		list($id_category) = sql_fetch_row(sql_query($query));
		
		return $id_category;
	}
	
	/**
	 * Function that return the laoratorie of the db
	 * 
	 * @return array Array with the laboratories in that format $array[ID_LABORATORY] = LABORATORY_NAME
	 */
	function getLaboratories()
	{
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$query = "SELECT idClassroom, name" .
				" FROM ".$this->getTableClassroom()."";
		
		$result = sql_query($query);
		$num_rows = sql_num_rows($result);
		
		$re = array();
		$re[0] = $lang->def('_UNDEFINITED');
		
		if($num_rows)
		{
			while(list($id_classroom, $name) = sql_fetch_row($result))
				$re[$id_classroom] = $name;
		}
		return $re;
	}
	
	/**
	 * Function that return the hours for the dropdown men�
	 * 
	 * @return array Array with the hours
	 */
	function getHours()
	{
		$re = array();
		
		$re['00'] = '00';
		$re['01'] = '01';
		$re['02'] = '02';
		$re['03'] = '03';
		$re['04'] = '04';
		$re['05'] = '05';
		$re['06'] = '06';
		$re['07'] = '07';
		$re['08'] = '08';
		$re['09'] = '09';
		$re['10'] = '10';
		$re['11'] = '11';
		$re['12'] = '12';
		$re['13'] = '13';
		$re['14'] = '14';
		$re['15'] = '15';
		$re['16'] = '16';
		$re['17'] = '17';
		$re['18'] = '18';
		$re['19'] = '19';
		$re['20'] = '20';
		$re['21'] = '21';
		$re['22'] = '22';
		$re['23'] = '23';
		
		return $re;
	}
	
	/**
	 * Function that return the minutes for the dropdown men�
	 * 
	 * @return array Array with the minutes
	 */
	function getMinutes()
	{
		$re = array();
		
		$re['00'] = '00';
		$re['15'] = '15';
		$re['30'] = '30';
		$re['45'] = '45';
		
		return $re;
	}
	
	/**
	 * Function that return the info of an event
	 * 
	 * @param int $id_event
	 * 
	 * @return array Array with the info of the event in that format array[FIELD] = data
	 */
	function getEventInfo($id_event)
	{
		$query = "SELECT idEvent, idCourse, idLaboratory, idCategory, title, description, date, maxUser, deadLine, fromTime, toTime" .
				" FROM ".$this->getTableEvents()."" .
				" WHERE idEvent = '".$id_event."'";
		
		$result = sql_query($query);
		$num_rows = sql_num_rows($result);
		
		if ($num_rows)
		{
			$re = array();
			
			list($id_event, $id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time) = sql_fetch_row($result);
			
			$re[EVENT_ID] = $id_event;
			$re[EVENT_ID_LABORATORY] = $id_laboratory;
			$re[EVENT_ID_CATEGORY] = $id_category;
			$re[EVENT_TITLE] = $title;
			$re[EVENT_DESCRIPTION] = $description;
			$re[EVENT_DATE] = $date;
			$re[EVENT_MAX_USER] = $max_user;
			$re[EVENT_DEADLINE] = $deadline;
			$re[EVENT_FROM_TIME] = $from_time;
			$re[EVENT_TO_TIME] = $to_time;
			$re[EVENT_ID_COURSE] = $id_course;
			
			return $re;
		}
		else
			return false;
	}
	
	function getEventUserMail($id_event)
	{
		$mail = array();
		
		$idst_user = array();
		$idst_user = $this->getSubscribedUserIdst($id_event);
		
		$acl_man =& Docebo::user()->getAclManager();
		
		$user_info = array();
		$user_info =& $acl_man->getUsers($idst_user);
		
		if ($user_info)
		{
			foreach ($user_info as $info_user)
			{
				$mail[] = $info_user[ACL_INFO_EMAIL];
			}
		}
		return $mail;
	}
	
	function getEventDropDown($id_course, $id_category, $id_user)
	{
		$query = "SELECT idEvent, title" .
				" FROM ".$this->getTableEvents()."" .
				" WHERE idCourse = '".$id_course."'" .
				" AND idCategory = '".$id_category."'" .
				" AND deadline >= '".date('Y-m-d')."'" .
				" AND idEvent IN (" .
					" SELECT idEvent" .
					" FROM ".$this->getTableSubscribed()."" .
					" WHERE idstUser = '".$id_user."'" .
				")";
		
		$result = sql_query($query);
		
		$re = array();
		
		while (list($id_event, $title) = sql_fetch_row($result))
			$re[$id_event] = $title;
		
		return $re;
	}
	
	/*function getLaboratoryInfo($id_laboratory)
	{
		$query = "SELECT idLaboratory, name, location, description" .
				" FROM ".$this->getTableLaboratories()."" .
				" WHERE idLaboratory = '".$id_laboratory."'";
		
		$result = sql_query($query);
		$num_rows = sql_num_rows($result);
		
		if ($num_rows)
		{
			$re = array();
			
			list($id_laboratory, $name, $location, $description) = sql_fetch_row($result);
			
			$re[LABORATORY_ID] = $id_laboratory;
			$re[LABORATORY_NAME] = $name;
			$re[LABORATORY_LOCATION] = $location;
			$re[LABORATORY_DESCRIPTION] = $description;
			
			return $re;
		}
		else
			return false;
	}*/
	
	function getMaxSubscriptionForCategory($id_category)
	{
		$query = "SELECT maxSubscription" .
				" FROM ".$this->getTableCategory()."" .
				" WHERE idCategory = '".$id_category."'";
		
		list($re) = sql_fetch_row(sql_query($query));
		
		return $re;
	}
	
	function controlMaxSubscriptionForCategory($id_category, $id_user)
	{
		$query = "SELECT maxSubscription" .
				" FROM ".$this->getTableCategory()."" .
				" WHERE idCategory = '".$id_category."'";
		
		list($max_subscription) = sql_fetch_row(sql_query($query));
		
		if ($max_subscription == 0)
			return true;
		
		$query_control = "SELECT COUNT(*)" .
						" FROM ".$this->getTableEvents()."" .
						" WHERE idCategory = '".$id_category."'" .
						" AND date >= '".date('Y-m-d')."'" .
						" AND idEvent IN (" .
						" SELECT idEvent" .
						" FROM ".$this->getTableSubscribed()."" .
						" WHERE idstUser = '".$id_user."'" .
						")";
		
		list($subscription) = sql_fetch_row(sql_query($query_control));
		
		if ($subscription < $max_subscription)
			return true;
		return false;
	}
	
	/**
	 * Function that control if an user is subscribed to an event
	 * 
	 * @param int $id_user The idst of the user
	 * @param int $id_event The id of the event
	 * 
	 * @return int 1 if is subscribed else 0
	 */
	function controlUserSubscription($id_user, $id_event)
	{
		$query = "SELECT COUNT(*)" .
				" FROM ".$this->getTableSubscribed()."" .
				" WHERE idstUser = '".$id_user."'" .
				" AND idEvent = '".$id_event."'";
		
		$result = sql_query($query);
		
		list($is_subscribed) = sql_fetch_row($result);
		
		return $is_subscribed;
	}
	
	/**
	 * Function that control if an event is full
	 * 
	 * @param int $id_event The id of the event to control
	 * 
	 * @return bool True if the event is full else false
	 */
	function controlEventFull($id_event)
	{
		$query = "SELECT maxUser" .
				" FROM ".$this->getTableEvents()."" .
				" WHERE idEvent = '".$id_event."'";
		
		$result = sql_query($query);
		
		list($max_user) = sql_fetch_row($result);
		
		if ($max_user == 0)
			return false;
		
		$subscription = $this->getSubscribedUser($id_event);
		
		if ($subscription < $max_user)
			return false;
		return true;
	}
	
	/**
	 * Control if there's another events in the laboratory
	 * 
	 * @param int $id_laboratory The of the event to control
	 * @param string $from_time The starting time of the event (time format hh:mm:ss)
	 * @param string $to_time The finish time of the event (time format hh:mm:ss)
	 * @param string $date The date of the event (date format YYYY-mm-dd)
	 * 
	 * @return bool True if there'isnt another event in the same laboratory and in the same time else false
	 */
	function controlLaboratoryStatus($id_laboratory, $from_time, $to_time, $date, $id_event = false)
	{
		if ($id_laboratory == 0)
			return true;
		
		$date = $date{0}.$date{1}.$date{2}.$date{3}.$date{4}.$date{5}.$date{6}.$date{7}.$date{8}.$date{9};
		
		$query = "SELECT COUNT(*)" .
				" FROM ".$this->getTableEvents()."" .
				" WHERE idLaboratory = '".$id_laboratory."'" .
				" AND date = '".$date."'" .
				" AND fromTime <= '".$from_time."'" .
				" AND toTime > '".$from_time."'";
		if ($id_event)
			$query .= " AND idEvent <> '".$id_event."'";
		
		$result = sql_query($query);
		list ($num) = sql_fetch_row($result);
		if ($num)
			return false;
		
		$query = "SELECT COUNT(*)" .
				" FROM ".$this->getTableEvents()."" .
				" WHERE idLaboratory = '".$id_laboratory."'" .
				" AND date = '".$date."'" .
				" AND fromTime < '".$to_time."'" .
				" AND toTime >= '".$to_time."'";
		if ($id_event)
			$query .= " AND idEvent <> '".$id_event."'";
		
		$result = sql_query($query);
		list ($num) = sql_fetch_row($result);
		if ($num)
			return false;
		return true;
	}
	
	function controlSwitchPossibility($id_course, $id_category, $id_user)
	{
		$query = "SELECT COUNT(*)" .
				" FROM ".$this->getTableEvents()."" .
				" WHERE idCourse = '".$id_course."'" .
				" AND idCategory = '".$id_category."'" .
				" AND deadline >= '".date('Y-m-d')."'" .
				" AND idEvent NOT IN (" .
					" SELECT idEvent" .
					" FROM ".$this->getTableSubscribed()."" .
					" WHERE idstUser = '".$id_user."'" .
				")";
		
		$result = sql_query($query);
		list($number_of_events) = sql_fetch_row($result);
		
		if ($number_of_events >= 1)
			return true;
		return false;
	}
	
	/*function viewLaboratories($id_course = false)
	{
		$query = "SELECT idLaboratory, name, location, description, idCourse" .
				" FROM ".$this->getTableLaboratories()."";
		
		if ($id_course)
			$query .= " WHERE idCourse = '".$id_course."'" .
					" OR idCourse = '0'";
		
		$result = sql_query($query);
		$num_rows = sql_num_rows($result);
		
		$re = array();
		
		if ($num_rows)
		{
			while (list($id_laboratory, $name, $location, $description, $id_course_2) = sql_fetch_row($result))
			{
				$re[$id_laboratory][LABORATORY_ID] = $id_laboratory;
				$re[$id_laboratory][LABORATORY_NAME] = $name;
				$re[$id_laboratory][LABORATORY_LOCATION] = $location;
				$re[$id_laboratory][LABORATORY_DESCRIPTION] = $description;
				$re[$id_laboratory][LABORATORY_ID_COURSE] = $id_course_2;
			}
		}
		
		return $re;
	}*/
	
	/**
	 * Function that insert a new laboratory in the db
	 * 
	 * @param string $name The name of the laboratory
	 * @param string $location The location of the laboratory
	 * @param string $description The description of the laboratory
	 * 
	 * @return bool True if done else false
	 */
	/*function addLaboratory($name, $location, $description, $id_course = false)
	{
		$query = "INSERT INTO ".$this->getTableLaboratories()."" .
				" (name, location, description)" .
				" VALUES('".$name."', '".$location."', '".$description."')";
		
		if ($id_course)
			$query = "INSERT INTO ".$this->getTableLaboratories()."" .
					" (idCourse, name, location, description)" .
					" VALUES('".$id_course."', '".$name."', '".$location."', '".$description."')";
		
		return $result = sql_query($query);
	}*/
	
	/**
	 * Function thet delete a laboratory from the db
	 * 
	 * @param int $id_laboratory The id of the laboratory to delete
	 * 
	 * @return bool True if done else false
	 */
	/*function delLaboratory($id_laboratory)
	{
		$query = "DELETE" .
				" FROM ".$this->getTableLaboratories()."" .
				" WHERE idLaboratory = '".$id_laboratory."'" .
				" LIMIT 1";
		
		return $result = sql_query($query);
	}*/
	
	/**
	 * Function that modify a laboratory
	 * 
	 * @param int $id_laboratory The id of the laboratory
	 * @param string $name New name
	 * @param string $location The location of the laboratory
	 * @param string $description The description of the laboratory
	 * 
	 * @return bool True if done else false
	 */
	/*function modLaboratory ($id_laboratory, $name, $location, $description)
	{
		$query = "UPDATE ".$this->getTableLaboratories()."" .
				" SET name = '".$name."'," .
				" location = '".$location."'," .
				" description = '".$description."'" .
				" WHERE idLaboratory = '".$id_laboratory."'" .
				" LIMIT 1";
		
		return $result = sql_query($query);
	}*/
	
	/**
	 * Function that insert a new event in the db
	 * 
	 * @param int $id_course Id of the course
	 * @param int $id_laboratory Id of the laboratory
	 * @param int $id_category Id of the category
	 * @param string $title Title
	 * @param string $description Description
	 * @param string $date Date of the event (date format YYYY-mm-dd)
	 * @param int $max_user Max number of user
	 * @param string $deadline Last day for the subscription (date format YYYY-mm-dd)
	 * @param string $from_time Starting time (time format hh:mm:ss)
	 * @param string $to_time Finish time (time format hh:mm:ss))
	 * 
	 * @return bool True if done else false
	 */
	function addEvents($id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time)
	{
		$control = $this->controlLaboratoryStatus($id_laboratory, $from_time, $to_time, $date);
		
		if (!$control)
			return false;
		
		$query = "INSERT INTO ".$this->getTableEvents()."" .
				" (idCourse, idLaboratory, idCategory, title, description, date, maxUser, deadLine, fromTime, toTime)" .
				" VALUES ('".$id_course."', '".$id_laboratory."', '".$id_category."', '".$title."', '".$description."', '".$date."', '".$max_user."', '".$deadline."', '".$from_time."', '".$to_time."')";
		
		return $result = sql_query($query);
	}
	
	/**
	 * Function that delete an event from the db
	 * 
	 * @param int $id_event The id of the event to delete
	 * 
	 * @return bool True if done else false
	 */
	function delEvent($id_event)
	{
		$query = "DELETE" .
				" FROM ".$this->getTableEvents()."" .
				" WHERE idEvent = '".$id_event."'" .
				" LIMIT 1";
		
		$result = sql_query($query);
		
		if ($result)
		{
			$query_2 = "DELETE" .
					" FROM ".$this->getTableSubscribed()."" .
					" WHERE idEvent = '".$id_event."'";
			
			return $result_2 = sql_query($query_2);
		}
		else
			return false; 
	}
	
	/**
	 * Function that modify an event
	 * 
	 * @param int $id_event Id of the event to modify
	 * @param int $id_course Id of the course
	 * @param int $id_laboratory Id of the laboratory
	 * @param int $id_category Id of the category
	 * @param string $title Title
	 * @param string $description Description
	 * @param string $date Date of the event (date format YYYY-mm-dd)
	 * @param int $max_user Max number of user
	 * @param string $deadline Last day for the subscription (date format YYYY-mm-dd)
	 * @param string $from_time Starting time (time format hh:mm:ss)
	 * @param string $to_time Finish time (time format hh:mm:ss))
	 * 
	 * @return bool True if done else false
	 */
	function modEvent($id_event, $id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time)
	{
		$control = $this->controlLaboratoryStatus($id_laboratory, $from_time, $to_time, $date, $id_event);
		
		if (!$control)
			return false;
		
		$query = "UPDATE ".$this->getTableEvents()."" .
				" SET idCourse = '".$id_course."'," .
				" idLaboratory = '".$id_laboratory."'," .
				" idCategory = '".$id_category."'," .
				" title = '".$title."'," .
				" description = '".$description."'," .
				" date = '".$date."'," .
				" maxUser = '".$max_user."'," .
				" deadLine = '".$deadline."'," .
				" fromTime = '".$from_time."'," .
				" toTime = '".$to_time."'" .
				" WHERE idEvent = '".$id_event."'";
		
		return  $result = sql_query($query);
	}
	
	/**
	 * Function that show the event
	 * 
	 * @param int $id_course The id of the course for show the event, if false show all events
	 * 
	 * @return array Return an array with the data of the events in that format: $re['id_event']['data']
	 */
	function viewEvents($id_course = false, $order_by = false)
	{
		$lang =& DoceboLanguage::createInstance('reservation');
		
		if ($id_course)
		{
			$query = "SELECT e.idEvent, e.idCourse, e.idLaboratory, e.idCategory, e.title, e.description, e.date, e.maxUser, e.deadLine, e.fromTime, e.toTime, c.name, l.name" .
					" FROM ".$this->getTableEvents()." as e LEFT JOIN ".$this->getTableCategory()." as c  ON ( e.idCategory = c.idCategory ) " .
					" LEFT JOIN ".$this->getTableClassroom()." as l ON l.idClassroom = e.idLaboratory" .
					" WHERE e.idCourse = '".$id_course."'" .
					" AND e.deadLine >= '".date('Y-m-d')."'";
					if ($order_by)
						$query .= "ORDER BY ".$order_by;
					else
						$query .= " ORDER BY c.name, e.title, e.date, e.deadLine";
		}
		else
		{
			$query = "SELECT e.idEvent, e.idCourse, e.idLaboratory, e.idCategory, e.title, e.description, e.date, e.maxUser, e.deadLine, e.fromTime, e.toTime, c.name, l.name" .
					" FROM ".$this->getTableEvents()." as e LEFT JOIN ".$this->getTableCategory()." as c  ON ( e.idCategory = c.idCategory ) " .
					" LEFT JOIN ".$this->getTableClassroom()." as l ON l.idClassroom = e.idLaboratory" .
					" WHERE 1 " .
					" ORDER BY e.date DESC, e.deadLine DESC, e.title";
		}
		
		$result = sql_query($query);
		
		$num_rows = sql_num_rows($result);
		
		if ($num_rows)
		{
			$re = array();
			
			while (list($id_event, $id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time, $category_name, $laboratory_name) = sql_fetch_row($result))
			{
				$user_subscribed = $this->getSubscribedUser($id_event);
				$available = $max_user - $user_subscribed;
				
				$re[$id_event][EVENT_ID] = $id_event;
				$re[$id_event][EVENT_ID_LABORATORY] = $id_laboratory;
				$re[$id_event][EVENT_ID_CATEGORY] = $id_category;
				$re[$id_event][EVENT_TITLE] = $title;
				$re[$id_event][EVENT_DESCRIPTION] = $description;
				$re[$id_event][EVENT_DATE] = $date;
				$re[$id_event][EVENT_MAX_USER] = $max_user;
				$re[$id_event][EVENT_DEADLINE] = $deadline;
				$re[$id_event][EVENT_FROM_TIME] = $from_time;
				$re[$id_event][EVENT_TO_TIME] = $to_time;
				if ($id_category)
					$re[$id_event][EVENT_CATEGORY_NAME] = $category_name;
				else
					$re[$id_event][EVENT_CATEGORY_NAME] = $lang->def('_UNDEFINITED');
				$re[$id_event][EVENT_USER_SUBSCRIBED] = $user_subscribed;
				$re[$id_event][EVENT_AVAILABLE_PLACES] = $available;
				$re[$id_event][EVENT_ID_COURSE] = $id_course;
				if ($id_laboratory)
					$re[$id_event][EVENT_LABORATORY_NAME] = $laboratory_name;
				else
					$re[$id_event][EVENT_LABORATORY_NAME] = $lang->def('_UNDEFINITED');
			}
			return $re;
		}
		else
			return false;
	}
	
	/**
	 * Function that return the info for the events in the Subscribed user Tab
	 * 
	 * @param int $id_course the id of the course
	 * 
	 * @return array Return an array with the data of the events in that format: $re['id_event']['data']
	 */
	function viewEventsForSubscribedTab($id_course = false, $order_by = false)
	{
		$lang =& DoceboLanguage::createInstance('reservation');
		
		if ($id_course)
		{
			$query = "SELECT e.idEvent, e.idCourse, e.idLaboratory, e.idCategory, e.title, e.description, e.date, e.maxUser, e.deadLine, e.fromTime, e.toTime, c.name, l.name" .
					" FROM ".$this->getTableEvents()." as e LEFT JOIN ".$this->getTableCategory()." as c ON ( e.idCategory = c.idCategory ) " .
					" LEFT JOIN ".$this->getTableClassroom()." as l ON l.idClassroom = e.idLaboratory" .
					" WHERE e.idCourse = '".$id_course."'";
					if ($order_by)
						$query .= "ORDER BY ".$order_by;
					else
						$query .= " ORDER BY c.name, e.title, e.date, e.deadLine";
		}
		else
		{
			$query = "SELECT e.idEvent, e.idCourse, e.idLaboratory, e.idCategory, e.title, e.description, e.date, e.maxUser, e.deadLine, e.fromTime, e.toTime, c.name, l.name" .
					" FROM ".$this->getTableEvents()." as e LEFT JOIN ".$this->getTableCategory()." as c ON ( e.idCategory = c.idCategory ) " .
					" LEFT JOIN ".$this->getTableClassroom()." as l ON l.idClassroom = e.idLaboratory" .
					" WHERE 1 " .
					" ORDER BY c.name, e.date DESC, e.deadLine DESC";
		}
		
		$result = sql_query($query);
		$num_rows = sql_num_rows($result);
		
		if ($num_rows)
		{
			$re = array();
			
			while (list($id_event, $id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time, $category_name, $laboratory_name) = sql_fetch_row($result))
			{
				$user_subscribed = $this->getSubscribedUser($id_event);
				$available = $max_user - $user_subscribed;
				
				$re[$id_event][EVENT_ID] = $id_event;
				$re[$id_event][EVENT_ID_LABORATORY] = $id_laboratory;
				$re[$id_event][EVENT_ID_CATEGORY] = $id_category;
				$re[$id_event][EVENT_TITLE] = $title;
				$re[$id_event][EVENT_DESCRIPTION] = $description;
				$re[$id_event][EVENT_DATE] = $date;
				$re[$id_event][EVENT_MAX_USER] = $max_user;
				$re[$id_event][EVENT_DEADLINE] = $deadline;
				$re[$id_event][EVENT_FROM_TIME] = $from_time;
				$re[$id_event][EVENT_TO_TIME] = $to_time;
				if ($id_category)
					$re[$id_event][EVENT_CATEGORY_NAME] = $category_name;
				else
					$re[$id_event][EVENT_CATEGORY_NAME] = $lang->def('_UNDEFINITED');
				$re[$id_event][EVENT_USER_SUBSCRIBED] = $user_subscribed;
				$re[$id_event][EVENT_AVAILABLE_PLACES] = $available;
				$re[$id_event][EVENT_ID_COURSE] = $id_course;
				if ($id_laboratory)
					$re[$id_event][EVENT_LABORATORY_NAME] = $laboratory_name;
				else
					$re[$id_event][EVENT_LABORATORY_NAME] = $lang->def('_UNDEFINITED');
			}
			return $re;
		}
		else
			return false;
	}
	
	/**
	 * Function that return the event maked in the past by the user
	 * 
	 * @param int $id_course The id of the course for show the event, if false show all events
	 * @param int $id_user The idst of the user
	 * 
	 * @return array List of the id of the events that the user maked else return false
	 */
	function viewPastEvents($id_course = false, $id_user, $order_by = false)
	{
		$lang =& DoceboLanguage::createInstance('reservation');
		
		if ($id_course)
		{
			$query = "SELECT e.idEvent, e.idCourse, e.idLaboratory, e.idCategory, e.title, e.description, e.date, e.maxUser, e.deadLine, e.fromTime, e.toTime, c.name" .
					" FROM ( ".$this->getTableEvents()." as e LEFT JOIN  ".$this->getTableCategory()." as c ON (e.idCategory = c.idCategory) )".
					" JOIN  ".$this->getTableSubscribed()." as s" .
					" LEFT JOIN ".$this->getTableClassroom()." as l ON l.idClassroom = e.idLaboratory" .
					" WHERE e.idCourse = '".$id_course."'" .
					" AND e.date < '".date('Y-m-d')."'" .
					" AND s.idEvent = e.idEvent" .
					" AND s.idstUser = '".$id_user."' ";
					if ($order_by)
						$query .= "ORDER BY ".$order_by;
					else
						$query .= " ORDER BY c.name, e.title, e.date, e.deadLine";
		}
		else
		{
			$query = "SELECT e.idEvent, e.idCourse, e.idLaboratory, e.idCategory, e.title, e.description, e.date, e.maxUser, e.deadLine, e.fromTime, e.toTime, c.name" .
					" FROM ( ".$this->getTableEvents()." as e LEFT JOIN ".$this->getTableCategory()." as c  ON (e.idCategory = c.idCategory) ) ".
					" JOIN ".$this->getTableSubscribed()." as s" .
					" LEFT JOIN ".$this->getTableClassroom()." as l ON l.idClassroom = e.idLaboratory" .
					" WHERE e.date < '".date('Y-m-d')."'" .
					" s.idEvent = e.idEvent" .
					" AND s.idstUser = '".$id_user."' " .
					" ORDER BY c.name, e.date, e.deadLine";
		}
		
		$result = sql_query($query);
		$num_rows = sql_num_rows($result);
		
		if ($num_rows)
		{
			$re = array();
			
			while (list($id_event, $id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time, $category_name, $laboratory_name) = sql_fetch_row($result))
			{
				$user_subscribed = $this->getSubscribedUser($id_event);
				$available = $max_user - $user_subscribed;
				
				$re[$id_event][EVENT_ID] = $id_event;
				$re[$id_event][EVENT_ID_LABORATORY] = $id_laboratory;
				$re[$id_event][EVENT_ID_CATEGORY] = $id_category;
				$re[$id_event][EVENT_TITLE] = $title;
				$re[$id_event][EVENT_DESCRIPTION] = $description;
				$re[$id_event][EVENT_DATE] = $date;
				$re[$id_event][EVENT_MAX_USER] = $max_user;
				$re[$id_event][EVENT_DEADLINE] = $deadline;
				$re[$id_event][EVENT_FROM_TIME] = $from_time;
				$re[$id_event][EVENT_TO_TIME] = $to_time;
				if ($id_category)
					$re[$id_event][EVENT_CATEGORY_NAME] = $category_name;
				else
					$re[$id_event][EVENT_CATEGORY_NAME] = $lang->def('_UNDEFINITED');
				$re[$id_event][EVENT_USER_SUBSCRIBED] = $user_subscribed;
				$re[$id_event][EVENT_AVAILABLE_PLACES] = $available;
				$re[$id_event][EVENT_ID_COURSE] = $id_course;
				if ($id_laboratory)
					$re[$id_event][EVENT_LABORATORY_NAME] = $laboratory_name;
				else
					$re[$id_event][EVENT_LABORATORY_NAME] = $lang->def('_UNDEFINITED');
			}
			return $re;
		}
		else
			return false;
	}
	
	/**
	 * Function that return the event where and user is subscribed, don't show the past events
	 * 
	 * @param int $id_course The id of the course for show the event, if false show all events
	 * @param int $id_user The idst of the user
	 * 
	 * @return array List of the id of the events where the user is subscribed else return false
	 */
	function viewMyEvents($id_course = false, $id_user, $order_by = false)
	{
		$lang =& DoceboLanguage::createInstance('reservation');
		
		if ($id_course)
		{
			$query = "SELECT e.idEvent, e.idCourse, e.idLaboratory, e.idCategory, e.title, e.description, e.date, e.maxUser, e.deadLine, e.fromTime, e.toTime, c.name, l.name" .
					" FROM ( ".$this->getTableEvents()." as e LEFT JOIN ".$this->getTableCategory()." as c ON (e.idCategory = c.idCategory) )".
					" JOIN ".$this->getTableSubscribed()." as s" .
					" LEFT JOIN ".$this->getTableClassroom()." as l ON l.idClassroom = e.idLaboratory" .
					" WHERE e.idCourse = '".$id_course."'" .
					" AND e.date >= '".date('Y-m-d')."'" .
					" AND s.idEvent = e.idEvent" .
					" AND s.idstUser = '".$id_user."'";
					if ($order_by)
						$query .= "ORDER BY ".$order_by;
					else
						$query .= " ORDER BY c.name, e.title, e.date, e.deadLine";
		}
		else
		{
			$query = "SELECT e.idEvent, e.idCourse, e.idLaboratory, e.idCategory, e.title, e.description, e.date, e.maxUser, e.deadLine, e.fromTime, e.toTime, c.name, l.name" .
					" FROM ( ".$this->getTableEvents()." as e LEFT JOIN  ".$this->getTableCategory()." as c ON (e.idCategory = c.idCategory) ) ".
					" JOIN ".$this->getTableSubscribed()." as s" .
					" LEFT JOIN ".$this->getTableClassroom()." as l ON l.idClassroom = e.idLaboratory" .
					" WHERE e.date >= '".date('Y-m-d')."'" .
					" AND s.idEvent = e.idEvent" .
					" AND s.idstUser = '".$id_user."'" .
					" ORDER BY c.name, e.date, e.deadLine";
		}
		
		$result = sql_query($query);
		$num_rows = sql_num_rows($result);
		
		if ($num_rows)
		{
			$re = array();
			
			while (list($id_event, $id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time, $category_name, $laboratory_name) = sql_fetch_row($result))
			{
				$user_subscribed = $this->getSubscribedUser($id_event);
				$available = $max_user - $user_subscribed;
				
				$re[$id_event][EVENT_ID] = $id_event;
				$re[$id_event][EVENT_ID_LABORATORY] = $id_laboratory;
				$re[$id_event][EVENT_ID_CATEGORY] = $id_category;
				$re[$id_event][EVENT_TITLE] = $title;
				$re[$id_event][EVENT_DESCRIPTION] = $description;
				$re[$id_event][EVENT_DATE] = $date;
				$re[$id_event][EVENT_MAX_USER] = $max_user;
				$re[$id_event][EVENT_DEADLINE] = $deadline;
				$re[$id_event][EVENT_FROM_TIME] = $from_time;
				$re[$id_event][EVENT_TO_TIME] = $to_time;
				if ($id_category)
					$re[$id_event][EVENT_CATEGORY_NAME] = $category_name;
				else
					$re[$id_event][EVENT_CATEGORY_NAME] = $lang->def('_UNDEFINITED');
				$re[$id_event][EVENT_USER_SUBSCRIBED] = $user_subscribed;
				$re[$id_event][EVENT_AVAILABLE_PLACES] = $available;
				$re[$id_event][EVENT_ID_COURSE] = $id_course;
				if ($id_laboratory)
					$re[$id_event][EVENT_LABORATORY_NAME] = $laboratory_name;
				else
					$re[$id_event][EVENT_LABORATORY_NAME] = $lang->def('_UNDEFINITED');
			}
			return $re;
		}
		else
			return false;
	}
	
	/**
	 * Function that add a new subscription to an event
	 * 
	 * @param int $idst_user Idst of the user
	 * @param int $id_event Id of the event
	 * 
	 * @return bool True if done else false
	 */
	function addSubscription($idst_user, $id_event)
	{
		if($this->controlEventFull($id_event))
			return false;
		
		if ($this->controlUserSubscription($idst_user, $id_event))
			return true;
		
		$query = "INSERT INTO ".$this->getTableSubscribed()."" .
				" (idstUser, idEvent)" .
				" VALUES ('".$idst_user."', '".$id_event."')";
		
		return $result = sql_query($query);
	}
	
	/**
	 * Function that delete a subscription to an event
	 * 
	 * @param int $idst_user Idst of the user
	 * @param int $id_event Id of the event
	 * 
	 * @return bool True if done else false
	 */
	function delSubscription($idst_user, $id_event)
	{
		$query = "DELETE" .
				" FROM ".$this->getTableSubscribed()."" .
				" WHERE idstUser = '".$idst_user."'" .
				" AND idEvent = '".$id_event."'" .
				" LIMIT 1";
		
		return $result = sql_query($query);
	}
	
	/**
	 * Function that switch a subscription
	 * 
	 * @param int $idst_user Idst of the user
	 * @param int $id_old_event Id of the subscription event to delete
	 * @param int $id_new_event Id of the subscription event to insert
	 * 
	 * @return bool True if done else false
	 */
	function switchSubscription($idst_user, $id_old_event, $id_new_event)
	{
		if($this->controlEventFull($id_new_event))
			return false;
		
		$re = $this->addSubscription($idst_user, $id_new_event);
		
		if ($re)
		{
			$re = $this->delSubscription($idst_user, $id_old_event);
			return $re;
		}
		else
			return false;
	}
	
	/**
	 * Function that return the idst of the user subscribed to an event
	 * 
	 * @param int $id_event The id of the event
	 * 
	 * @return array $re[$id_event][] = $idst_user
	 */
	function viewSubscription($id_event)
	{
		$query = "SELECT idstUser" .
				" FROM ".$this->getTableSubscribed()."" .
				" WHERE idEvent = '".$id_event."'";
		
		$result = sql_query($query);
		
		$re = array();
		
		while ($row = sql_fetch_row)
			$re[$id_event][] = $row[0];
	}
	
	function viewCategory($id_course = false)
	{
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$query = "SELECT idCategory, name, idCourse, maxSubscription" .
				" FROM ".$this->getTableCategory()."";
		
		if($id_course)
			$query .= " WHERE idCourse = '".$id_course."'" .
						" OR idCourse = '0'";
		
		$result = sql_query($query);
		$num_rows = sql_num_rows($result);
		
		$re = array();
		$re[0][CATEGORY_ID] = 0;
		$re[0][CATEGORY_NAME] = $lang->def('_UNDEFINITED');
		$re[0][CATEGORY_ID_COURSE] = 0;
		$re[0][CATEGORY_MAX_SUBSCRIPTION] = 0;
		
		if($num_rows)
		{
			while(list($id_category, $name, $id_course_2, $max_subscription) = sql_fetch_row($result))
			{
				$re[$id_category][CATEGORY_ID] = $id_category;
				$re[$id_category][CATEGORY_NAME] = $name;
				$re[$id_category][CATEGORY_ID_COURSE] = $id_course_2;
				$re[$id_category][CATEGORY_MAX_SUBSCRIPTION] = $max_subscription;
			}
		}
		
		return $re;
	}
	
	function getCategoryMaxSubscription($id_category)
	{
		$query = "SELECT maxSubscription" .
				" FROM ".$this->getTableCategory()."" .
				" WHERE idCategory = '".$id_category."'";
		
		$result = sql_query($query);
		
		list($re) = sql_fetch_row($result);
		
		return $re;
	}
	
	/**
	 * Function that add a new category
	 * 
	 * @param string $name Name
	 * 
	 * @return bool True if done else false
	 */
	function addCategory($name, $max_subscription, $id_course = false)
	{
		if ($id_course)
		{
			$query = "INSERT INTO ".$this->getTableCategory()."" .
					" (name, idCourse, maxSubscription)" .
					" VALUES ('".$name."', '".$id_course."', '".$max_subscription."')";
		}
		else
		{
			$query = "INSERT INTO ".$this->getTableCategory()."" .
					" (name, maxSubscription)" .
					" VALUES ('".$name."', '".$max_subscription."')";
		}
		
		return $result = sql_query($query);
	}
	
	/**
	 * Function that delete a category
	 * 
	 * @param int $id_category Id of the category
	 * 
	 * @return bool True if done else false
	 */
	function delCategory($id_category)
	{
		$query = "DELETE" .
				" FROM ".$this->getTableCategory()."" .
				" WHERE idCategory = '".$id_category."'" .
				" LIMIT 1";
		
		$result = sql_query($query);
		
		if($result)
		{
			$query = "UPDATE ".$this->getTableEvents()."" .
					" SET idCategory = '0'" .
					" WHERE idCategory = '".$id_category."'";
			
			return $result = sql_query($query);
		}
		else
			return false;
	}
	
	/**
	 * Function that modify a category
	 * 
	 * @param int $id_category Id of the category
	 * @param string $name New name
	 * 
	 * @return bool True if done else false
	 */
	function modCategory($id_category, $name, $max_subscription)
	{
		$query = "UPDATE ".$this->getTableCategory()."" .
				" SET name = '".$name."'," .
				" maxSubscription = '".$max_subscription."'" .
				" WHERE idCategory = '".$id_category."'" .
				" LIMIT 1";
		
		return $result = sql_query($query);
	}
}

?>