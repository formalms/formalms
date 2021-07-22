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

if(Docebo::user()->isAnonymous()) die('You can\'t access');

//load calendar core classes - extend'em for other type of events
require_once($GLOBALS['where_framework']."/lib/lib.calendar_core.php");
require_once($GLOBALS['where_framework']."/lib/lib.calevent_core.php");

$op = Get::req('op', DOTY_ALPHANUM, '');
$calClass = Get::req('calClass', DOTY_MIXED, '');

switch ($op) {
	case "get": {
		$month=Get::req('month', DOTY_INT);
		$year=Get::req('year', DOTY_INT);

		if (!$month and !$year) {
			$today=getdate();
			$year=$today['year'];
 			$month=$today['mon'];
			$day=$today['mday'];
		};

		$m0=$month-2;
		$y0=$year;
		if ($m0<1) {
			$y0--;
			$m0=10;
		};

		$start_date="$y0-$m0-01 00:00:00";
		$end_date="$year-$month-31 23:59:59";

		if ($calClass<>"core")
		require_once($GLOBALS['where_framework']."/lib/lib.calendar_".$calClass.".php");

		$class="DoceboCal_".$calClass;
		$cal = new $class();
		
		if ($calClass=="lms_classroom") {
			$classroom=Get::req('classroom');
			$eventlist=$cal->getEvents(0,0,0,$start_date,$end_date,$classroom);
		} else {
			$eventlist=$cal->getEvents(0,0,0,$start_date,$end_date);
		};
		
		$json=new Services_JSON();
		$calEvents=$json->encode($eventlist);
		aout($calEvents);
	};break;

	case "set": {
		
		$index=Get::req("index");
		$calEventClass=Get::req("calEventClass");

		if ($calEventClass<>"core")
		require_once($GLOBALS['where_framework']."/lib/lib.calevent_".$calEventClass.".php");

		$class="DoceboCalEvent_".$calEventClass;
		$event=new $class();

		$event->assignVar();
		if($event->store()) {

			$id=$event->id;
	
			aout("{\"index\":\"$index\",\"id\":\"$id\"}");
		} else {
		
			$result = array();
			$result['index'] = $index;
			$result['error'] = 1;
			$result['errormsg'] = Lang::t('_NOT_FREE', 'standard');
			$json = new Services_JSON();
			$result_coded = $json->encode($result);
		
			aout($result_coded);
		}
		
	};break;

	case "del": {
		$id=Get::req("id", DOTY_INT);
		$calEventClass=Get::req("calEventClass");

		if ($calEventClass<>"core")
		require_once($GLOBALS['where_framework']."/lib/lib.calevent_".$calEventClass.".php");

		$class="DoceboCalEvent_".$calEventClass;
		$event=new $class();

		//$event->id=$id;
		//$event->_owner=$event->getOwner();

		$event->assignVar();
		
		$event->del();

		aout("{\"result\":\"1\"}");
	};break;

	case "getForm": {
		$calEventClass=Get::req("calEventClass");

		if ($calEventClass<>"core")
		require_once($GLOBALS['where_framework']."/lib/lib.calevent_".$calEventClass.".php");

		$class="DoceboCalEvent_".$calEventClass;
		$event=new $class();

		$form=$event->getForm();

		aout($form);
	};break;


	case "getLang": {
		$lang =& DoceboLanguage::createInstance( 'calendar', 'lms');

		$lang_obj='{
		"_DN":["'.$lang->def('_SUNDAY').'","'.$lang->def('_MONDAY').'","'.$lang->def('_TUESDAY').'","'.$lang->def('_WEDNESDAY').'","'.$lang->def('_THURSDAY').'","'.$lang->def('_FRIDAY').'","'.$lang->def('_SATURDAY').'","'.$lang->def('_SUNDAY').'"],
		"_SDN":["'.$lang->def('_SUN').'","'.$lang->def('_MON').'","'.$lang->def('_TUE').'","'.$lang->def('_WED').'","'.$lang->def('_THU').'","'.$lang->def('_FRI').'","'.$lang->def('_SAT').'","'.$lang->def('_SUN').'"],
		"_MN":["'.$lang->def('_JANUARY').'","'.$lang->def('_FEBRUARY').'","'.$lang->def('_MARCH').'","'.$lang->def('_APRIL').'","'.$lang->def('_MAY').'","'.$lang->def('_JUNE').'","'.$lang->def('_JULY').'","'.$lang->def('_AUGUST').'","'.$lang->def('_SEPTEMBER').'","'.$lang->def('_OCTOBER').'","'.$lang->def('_NOVEMBER').'","'.$lang->def('_DECEMBER').'"],
		"_SMN":["'.$lang->def('_JAN').'","'.$lang->def('_FEB').'","'.$lang->def('_MAR').'","'.$lang->def('_APR').'","'.$lang->def('_MAY').'","'.$lang->def('_JUN').'","'.$lang->def('_JUL').'","'.$lang->def('_AUG').'","'.$lang->def('_SEP').'","'.$lang->def('_OCT').'","'.$lang->def('_NOV').'","'.$lang->def('_DEC').'"],
		"_PREV_YEAR":"'.$lang->def('_PREV_YEAR').'",
		"_PREV_MONTH":"'.$lang->def('_PREV_MONTH').'",
		"_GO_TODAY":"'.$lang->def('_GO_TODAY').'",
		"_NEXT_MONTH":"'.$lang->def('_NEXT_MONTH').'",
		"_NEXT_YEAR":"'.$lang->def('_NEXT_YEAR').'",
		"_CAL_TITLE":"'.$lang->def('_CAL_TITLE').'",
		"_PART_TODAY":"'.$lang->def('_PART_TODAY').'",
		"_DAY_FIRST":"'.$lang->def('_DAY_FIRST').'",
		"_WEEKEND":"'.$lang->def('_WEEKEND').'",
		"_TODAY":"'.$lang->def('_TODAY').'",
		"_DEF_DATE_FORMAT":"'.$lang->def('_DEF_DATE_FORMAT').'",
		"_TT_DATE_FORMAT":"'.$lang->def('_TT_DATE_FORMAT').'",
		"_WK":"'.$lang->def('_WK').'",
		"_TIME":"'.$lang->def('_TIME').'",
		"_CLOSE":"'.$lang->def('_CLOSE').'",
		"_START":"'.$lang->def('_START').'",
		"_END":"'.$lang->def('_END').'",
		"_SUBJECT":"'.$lang->def('_TITLE').'",
		"_DESCR":"'.$lang->def('_DESCRIPTION').'",
		"_SAVE":"'.$lang->def('_SAVE').'",
		"_DEL":"'.$lang->def('_DEL').'",
		"_NOTITLE":"'.$lang->def('_NOTITLE').'",
		"_NEW_EVENT":"'.$lang->def('_NEW_EVENT').'",
		"_MOD":"'.$lang->def('_MOD').'",
		"_OPERATION_FAILURE":"'.$lang->def('_OPERATION_FAILURE').'",
		"_PLS_WAIT":"'.$lang->def('_PLS_WAIT').'",
		"_DEL_EVENT":"'.$lang->def('_DEL').'",
		"_AREYOUSURE":"'.$lang->def('_AREYOUSURE').'",
		"_CATEGORY":"'.$lang->def('_CATEGORY').'",
		"_EVENT":"'.$lang->def('_EVENT').'",
		"_YES":"'.$lang->def('_YES').'",
		"_NO":"'.$lang->def('_NO').'",

		"_PRIVATE":"'.$lang->def('_PRIVATE').'",
		"_GENERIC":"'.$lang->def('_GENERIC').'",
		"_VIDEOCONFERENCE":"'.$lang->def('_VIDEOCONFERENCE').'",
		"_MEETING":"'.$lang->def('_MEETING').'",
		"_CHAT":"'.$lang->def('_CHAT').'",
		"_PUBLISHING":"'.$lang->def('_PUBLISHING').'",
		"_ASSESSMENT":"'.$lang->def('_ASSESSMENT').'"
		}';

		aout($lang_obj);
	};break;
}

?>