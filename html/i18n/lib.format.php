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

class Format {

	private static $_regset = false;

	private function  __construct() {}

	private function init() {

		require_once(_i18n_.'/lib.regset.php');
		self::$_regset = new RegionalSettings();
	}

	/**
	 * Return the current istance of the format file
	 */
	public static function instance() {

		$classname = __CLASS__;
		if(!self::$_regset) self::init();
		return self::$_regset;
	}

	/**
	 * Convert a date from the iso format to the current regional format
	 * @param <string> $date the date to convert
	 * @param <string> $type 'date' or 'datetime'
	 * @return <string> the date in the current format
	 */
	public static function date($date, $type = FALSE, $seconds = FALSE) {

		if(!self::$_regset) self::istance();
		return self::$_regset->databaseToRegional($date, $type, $seconds);
	}

	/**
	 * Convert a date from the current regional format to a iso format
	 * @param <string> $date the date to convert
	 * @param <string> $type 'date' or 'datetime'
	 * @return <string> the date in iso
	 */
	public static function dateDb($date, $type = FALSE) {

		if(!self::$_regset) self::istance();
		return self::$_regset->regionalToDatabase($date, $type);
	}


	/**
	 * Convert a date from the ISO format into timestamp
	 * @param <string> $date the date to convert
	 * @return <string> the timestamp
	 */
	public static function toTimestamp($date) {

		if(!self::$_regset) self::istance();
		return self::$_regset->databaseToTimestamp($date);
	}


	public function dateDistance( $date ) {

		// yyyy-mm-dd hh:mm:ss
		// 0123456789012345678
		$year 	= substr($date, 0, 4);
		$month 	= substr($date, 5, 2);
		$day	= substr($date, 8, 2);

		$hour 	= substr($date, 11, 2);
		$minute = substr($date, 14, 2);
		$second	= substr($date, 17 , 2);

		$distance = time() - mktime($hour, $minute, $second, $month, $day, $year);
		//second -> minutes
		$distance = (int)($distance / 60);
		// < 1 hour print minutes
		if( ($distance >= 0 ) && ($distance < 60) ) return $distance.' '.Lang::t('_MINUTES', 'standard');

		//minutes -> hour
		$distance = (int)($distance / 60);
		if( ($distance >= 0 ) && ($distance < 48) ) return $distance.' '.Lang::t('_HOURS', 'standard');

		//hour -> day
		$distance = (int)($distance / 24);
		if( ($distance >= 0 ) && ($distance < 30 ) ) return $distance.' '.Lang::t('_DAYS', 'standard');

		//echo > 1 month
		return Format::date($date, 'date');
	}
}
