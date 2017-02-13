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
 * Regional settings management classes
 *
 * @package admin-library
 * @subpackage utility
 * @author   Giovanni Derks <virtualdarkness[AT]gmail-com>
 */

class RegionalSettings {
	/** RegionalSettingsManager object */
	var $regset_manager=NULL;

	/** DoceboDate object */
	var $ddate=NULL;

	var $full_token="";
	var $date_token="";
	var $time_token="";
	var $time_offset=0;
	var $time_sep=":";
	var $date_sep="";
	var $region_id=NULL;


	/**
	 * RegionalSettings constructor
	 * @param string $param_prefix the prefix for the tables names
	 *			if not given global $prefix variable is used
	 * @param resource $dbconn the connection to the database
	 * 			if not given last connection will be used.
	 */
	function RegionalSettings($region_id=FALSE, $param_prefix=FALSE, $dbconn=NULL) {

		$this->ddate=new DoceboDate();

		$this->regset_manager=new RegionalSettingsManager($param_prefix, $dbconn);

		$this->loadRegion($region_id);

	}


	function loadRegion($region_id) {

		$this->region_id=$this->regset_manager->checkRegion($region_id);

		$settings=$this->regset_manager->getRegionSettings($this->region_id);
		$this->date_sep=$settings["date_sep"];

		if ($settings["date_format"] != "custom")
			$this->date_token=$this->getToken($settings["date_format"], $this->date_sep);
		else
			$this->date_token=$settings["custom_date_format"];

		if ($settings["time_format"] != "custom")
			$this->time_token=$this->getToken($settings["time_format"], $this->time_sep);
		else
			$this->time_token=$settings["custom_time_format"];

		if ((isset($settings["time_offset"])) && (!empty($settings["time_offset"]))) {
			$this->time_offset=(int)$settings["time_offset"];
		}

		$this->full_token=$this->date_token." ".$this->time_token;

	}


	function setToken($date_token=FALSE, $time_token=FALSE) {

		if ($date_token !== FALSE)
			$this->date_token=$date_token;

		if ($time_token !== FALSE)
			$this->time_token=$time_token;

		$this->full_token=$this->date_token." ".$this->time_token;
	}


	/**
	 * @param string $format_str the original format string
	 * @param string $sep the separator to use in the token
	 *
	 * @return string the token string corresponding to the given format string;
	 * 	we are using the same format of the jscalendar script available at
	 * 	http://www.dynarch.com/projects/calendar/
	 */
	function getToken($format_str, $sep) {
		$res="";
		$from_arr=array("d", "m", "Y", "y", "H", "h", "i", "s", "a", "_", ".");
		$to_arr=array("%d", "%m", "%Y", "%y", "%H", "%I", "%M", "%S", "%P", $sep, " ");
		$res=str_replace($from_arr, $to_arr, $format_str);

		return $res;
	}


	/**
	 * @param string $token the token corresponding to our date
	 *
	 * @return string the regoular expresion used to parse the date
	 */
	function _getDateRegExp($token) {
		$res="";
		$from_arr=array("%d", "%m", "%Y", "%y", "%H", "%I", "%M", "%S", "%P");
		$to_arr=$this->getRegExpArray();
		$res=str_replace($from_arr, $to_arr, $token);

		return "/".str_replace('/', '\/', $res)."/";
	}


	function _getFormatRegExp($token) {
		$res="";
		$from_arr=array("%d", "%m", "%Y", "%y", "%H", "%I", "%M", "%S", "%P");
		$to_arr=array_fill(0, count($from_arr), "(.*)");
		$res=str_replace($from_arr, $to_arr, $token);

		return "/".str_replace('/', '\/', $res)."/";
	}


	function getRegExpArray() {
		$res=array(
			'%d' => '(\\d{1,2})',		// day
			'%m' => '(\\d{1,2})',		// month
			'%Y' => '(\\d{4})',			// year long
			'%y' => '(\\d{1,2})',		// year short
			'%H' => '(\\d{1,2})',		// hour
			'%I' => '(\\d{1,2})',		// hour
			'%M' => '(\\d{1,2})',		// minute
			'%S' => '(\\d{1,2})',		// second
			'%P' => '(\\w{2})'			  // AM/PM
		);

		return $res;
	}


	/**
	 * @param string $date the date in the regional format
	 * @param string $type the content of the date (datetime, date, time)
	 *
	 * @return int the Internal date format (unix timestamp)
	 */
	function regionalToInternal($date, $type=FALSE) {

		if ($type === FALSE)
			$type="datetime";

		switch ($type) {

			case "datetime": {
				$this->_decodeDate($date, $this->full_token);
			} break;

			case "date": {
				$this->ddate->setInternalDate(FALSE, FALSE, FALSE, "00", "00", "00");
				$this->_decodeDate($date, $this->date_token);
			} break;

			case "time": {
				$this->ddate->setInternalDate("0000", "00", "00", FALSE, FALSE, FALSE);
				$this->_decodeDate($date, $this->time_token);
			} break;

		}

		$internal=$this->ddate->getInternalDate();

		// ..decodeDate has set the ddate timestamp var.
		//--NO-MKTIME--//
		$this->ddate->setOffset($this->time_offset * 60, "sub");
		//--NO-MKTIME--// return $this->ddate->getTimeStamp();

	}


	/**
	 * @param int $internal_date the Internal date format (unix timestamp)
	 * @param string $type the content of the date (datetime, date, time)
	 * @param boolean $seconds if false will remove the seconds; if true will use the
	 *	default settings according to the token
	 *
	 * @return string the date formatted in the regional format
	 */
	function internalToRegional($type=FALSE, $seconds=TRUE) {

		$res="";

		if ($type === FALSE)
			$type="datetime";

		$full_token=$this->full_token;
		$date_token=$this->date_token;
		$time_token=$this->time_token;

		if ( (($type == "time") || ($type == "datetime")) && (!$seconds)) {
			$time_token=preg_replace("/%M.*%S/", "%M", $time_token);
			$full_token=preg_replace("/%M.*%S/", "%M", $full_token);
		}

		//--NO-MKTIME--// $this->ddate->setTimeStamp($internal_date);
		//--NO-MKTIME--//
		$this->ddate->setOffset($this->time_offset * 60, "add");
		//--NO-MKTIME--// $internal_date=$this->ddate->getTimeStamp();

		switch ($type) {

			case "datetime": {
				$res=$this->_encodeDate($full_token);
			} break;

			case "date": {
				$res=$this->_encodeDate($date_token);
			} break;

			case "time": {
				$res=$this->_encodeDate($time_token);
			} break;

		}

		return $res;
	}


	/**
	 * @param string $dbtyoe the type of database that we are using
	 * @param string $type the content of the date (datetime, date, time)
	 *
	 * @return string the token with the format of the database field
	 */
	function _getDbToken($dbtype=FALSE, $type=FALSE) {

		if ($dbtype === FALSE)
			$dbtype="mysql"; // <- for now it is the only one we support!

		if ($type === FALSE)
			$type="datetime";

		$token="";

		switch ($dbtype) {

			case "mysql": {
				$token["datetime"]="%Y-%m-%d %H:%M:%S";
				$token["date"]="%Y-%m-%d";
				$token["time"]="%H:%M:%S";
			} break;

		}

		return $token[$type];
	}


	/**
	 * @param string $internal_date the Internal date format (unix timestamp)
	 *
	 * @return string the date formatted in the database field format
	 */
	function internalToDatabase() {

		$token=$this->_getDbToken();
		return $this->_encodeDate($token);

	}


	/**
	 * @param string $date the date formatted in the database field format
	 *
	 */
	function databaseToInternal($date, $type=FALSE) {

		$this->_decodeDate($date, $this->_getDbToken(FALSE, $type));

	}


	/**
	 * @param string $date the date in the regional format
	 * @param string $type the content of the date (datetime, date, time)
	 *
	 * @return string the date formatted in the database field format
	 */
	function regionalToDatabase($date, $type=FALSE) {

		if($date == '') return '';
		$internal=$this->regionalToInternal($date, $type);
		return $this->internalToDatabase($internal);

	}


	/**
	 * @param string $date the date formatted in the database field format
	 * @param string $type the content of the date (datetime, date, time)
	 * @param boolean $seconds if false will remove the seconds; if true will use the
	 *	default settings according to the token
	 *
	 * @return string the date formatted in the regional format
	 */
	function databaseToRegional($date, $type=FALSE, $seconds=FALSE) {

		if($date == '') return '';
		$this->databaseToInternal($date, $type);
		return $this->internalToRegional($type, $seconds);

	}


	/**
	 * @param string $date the date formatted in the database field format
	 * As a convention, the internal format corresponds to the unix timestamp
	 * but if you need to get it you'd better use this function cause
	 * databaseToInternal could change in the future.
	 *
	 * @return string The unix timestamp
	 */
	function databaseToTimestamp($date) {

		$this->databaseToRegional($date);
		// ..databaseToRegional has set the ddate timestamp var.
		return $this->ddate->getTimeStamp();

	}


	function databaseToCustom($date, $type=FALSE, $seconds=FALSE, $date_token=FALSE, $time_token=FALSE) {

		$old_date_token=$this->date_token;
		$old_time_token=$this->time_token;

		$this->setToken($date_token, $time_token);

		$res=$this->databaseToRegional($date, $type, $seconds);

		$this->setToken($old_date_token, $old_time_token);
		return $res;
	}


	function customToDatabase($date, $type=FALSE, $date_token=FALSE, $time_token=FALSE) {

		$old_date_token=$this->date_token;
		$old_time_token=$this->time_token;

		$this->setToken($date_token, $time_token);

		$res=$this->regionalToDatabase($date, $type);

		$this->setToken($old_date_token, $old_time_token);
		return $res;
	}


	/**
	 * @param string $date the date in the regional format
	 * @param string $token the token with the date format
	 *
	 * This function will fill the DoceboDate object with the date elements
	 * decoded from the given date/token
	 */
	function _decodeDate($date, $token) {
		$found_val = $found_key ='';

		$pattern=$this->_getDateRegExp($token);
		preg_match ($pattern, $date, $found_val);
		unset($found_val[0]);

		$pattern=$this->_getFormatRegExp($token);
		preg_match ($pattern, $token, $found_key);
		unset($found_key[0]);

		// print_r($found_val); echo("<br />\n"); //debug
		// print_r($found_key); echo("<br />\n"); //debug
		$this->ddate->setFromToken($found_val, $found_key);
		// print_r($this->ddate); //debug
	}


	/**
	 * @param string $internal_date the Internal date format (unix timestamp)
	 * @param string $token the token with the date format
	 *
	 * @return string the date formatted according to the token format
	 */
	function _encodeDate($token) {

		$find=array();
		$replace=array();

		$date_arr=$this->ddate->getDateArray();

		if ($date_arr["hour"] > 12) {
			$hour_12=$this->ddate->leadingZero(12-$date_arr["hour"], 2);
			$date_arr["ampm"]="pm";
		}
		else {
			$hour_12=$date_arr["hour"];
			$date_arr["ampm"]="am";
		}

		$find[]="%d";		$replace[]=$date_arr["day"];
		$find[]="%m";		$replace[]=$date_arr["month"];
		$find[]="%Y";		$replace[]=$date_arr["year"];
		$find[]="%y";		$replace[]=substr($date_arr["year"], 2);
		$find[]="%H";		$replace[]=$date_arr["hour"]; // 24 h
		$find[]="%I";		$replace[]=$hour_12; // 12 h
		$find[]="%M";		$replace[]=$date_arr["min"];
		$find[]="%S";		$replace[]=$date_arr["sec"];
		$find[]="%P";		$replace[]=$date_arr["ampm"];

		$res=str_replace($find, $replace, $token);

		return $res;
	}


	/**
	 *
	 * getLanguageFromBrowser returns the language code of the auto-detected region
	 * of the user.
	 *
	 * @return string a valid lang_code
	 */
	function getLanguageFromBrowser() {

		$res="";

		if ((isset($this->region_id)) && (!empty($this->region_id))) {
			$res=$this->regset_manager->getRegionInfo($this->region_id, "lang_code");
		}

		if ($res == "") {
			$res=$GLOBALS['platform_manager']->getLanguageForPlatform();
		}

		return $res;
	}
}

class RegionalSettingsManager {
	/** db connection */
	var $dbconn;
	/** prefix for the database */
	var $prefix;
	var $default_region=NULL;
	var $region_info=array();
	var $region_settings=array();
	var $setting_list=array();


	/**
	 * RegionalSettingsManager constructor
	 * @param string $param_prefix the prefix for the tables names
	 *			if not given global $prefix variable is used
	 * @param resource $dbconn the connection to the database
	 * 			if not given last connection will be used.
	 */
	function RegionalSettingsManager( $param_prefix = FALSE, $dbconn = NULL ) {
		if( $param_prefix === FALSE ) {
			$this->prefix=$GLOBALS["prefix_fw"];
		} else {
			$this->prefix=$param_prefix;
		}
		$this->dbConn=$dbconn;

		$this->setting_list=$this->_loadSettingList();
	}


	/**
	 * @return string table name for the list of regions
	 **/
	function _getListTable() {
		return $this->prefix."_reg_list";
	}

	/**
	 * @return string table name for the list of regions
	 **/
	function _getSettingTable() {
		return $this->prefix."_reg_setting";
	}


	function _executeQuery( $query ) {
		if( $this->dbconn === NULL )
			$rs = sql_query( $query );
		else
			$rs = sql_query( $query, $this->dbconn );
		return $rs;
	}


	function _executeInsert( $query ) {
		if( $this->dbconn === NULL ) {
			if( !sql_query( $query ) )
				return FALSE;
		} else {
			if( !sql_query( $query, $this->dbconn ) )
				return FALSE;
		}
		if( $this->dbconn === NULL )
			return sql_insert_id();
		else
			return sql_insert_id($this->dbconn);
	}


	/**
	 * return an array with all the region_id presents on system
	 * @return array with all the region_id in system (index in array is numeric
	 *			starting from 0, value is region_id)
	 */
	function getAllRegions() {

		$res=array();

		$qtxt="SELECT region_id, lang_code, region_desc, default_region, browsercode FROM ".$this->_getListTable()." ORDER BY region_id";
		$q=$this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_array($q)) {
				$res[]=$row["region_id"];

				if ($row["default_region"])
					$this->default_region=$row["region_id"];

				$this->region_info[$row["region_id"]]["lang_code"]=$row["lang_code"];
				$this->region_info[$row["region_id"]]["description"]=$row["region_desc"];
				$this->region_info[$row["region_id"]]["default"]=$row["default_region"];
				$this->region_info[$row["region_id"]]["browsercode"]=$row["browsercode"];
			}
		}

		return $res;
	}


	/**
	 * @return string with the default region_id in system
	 */
	function getDefaultRegion() {


		require_once(_base_.'/lib/lib.platform.php');
		$plt_man =& PlatformManager::createInstance();
		$def_lang =  $plt_man->getLanguageForPlatform();

		if (($this->default_region == NULL) || ($this->default_region == "")) {
			$qtxt="SELECT region_id FROM ".$this->_getListTable()." WHERE lang_code='".$def_lang."'";
			$q=$this->_executeQuery($qtxt);

			if (($q) && (sql_num_rows($q) > 0)) {
				$row=sql_fetch_array($q);
				$this->default_region=$row["region_id"];
			} else {
				$this->default_region='english';
			}
		}

		return $this->default_region;
	}


	/**
	 * @param string $region_id
	 * @param string $req_info is the requested information
	 */
	function getRegionInfo($region_id, $req_info) {

		if ((!is_array($this->region_info)) || (count($this->region_info) == 0)) {
			$this->region_info=$this->loadRegionInfo();
		}

		return $this->region_info[$region_id][$req_info];
	}


	/**
	 * return an array with all the information about a region in system
	 * @return array
	 */
	function loadRegionInfo() {

		// lang_code is also a reference key with the core_lang_language table [N:1]

		$res=array();

		$qtxt="SELECT region_id, lang_code, region_desc, default_region, browsercode FROM ".$this->_getListTable();
		$q=$this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_array($q)) {
				$res[$row["region_id"]]["lang_code"]=$row["lang_code"];
				$res[$row["region_id"]]["description"]=$row["region_desc"];
				$res[$row["region_id"]]["default"]=$row["default_region"];
				$res[$row["region_id"]]["browsercode"]=$row["browsercode"];
			}
		}

		return $res;
	}


	/**
	 * @param string $region_id
	 * @param string $req_info is the requested information
	 */
	function getRegionSettings($region_id) {

		if ((!isset($this->region_settings[$region_id])) ||
			(!is_array($this->region_settings[$region_id])) ||
			(count($this->region_settings[$region_id]) == 0)) {

			$this->region_settings=$this->loadRegionSettings($region_id);
		}
		return $this->region_settings[$region_id];
	}


	/**
	 * return an array with all the information about a region in system
	 * @param string $region_id
	 * @return array
	 */
	function loadRegionSettings($region_id) {

		$res=array();

		$qtxt="SELECT val_name, value FROM ".$this->_getSettingTable()." WHERE region_id='$region_id'";
		$q=$this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_array($q)) {
				$res[$region_id][$row["val_name"]]=$row["value"];
			}
		}

		return $res;
	}


	/**
	 * @return array with all the val_name of the setting fields
	 */
	function _loadSettingList() {

		$setting_list=array();
		$setting_list[]="date_format";
		$setting_list[]="date_sep";
		$setting_list[]="time_format";
		$setting_list[]="custom_date_format";
		$setting_list[]="custom_time_format";

		return $setting_list;
	}


	/**
	 * return an array with all the information about a region in system
	 * @param string $region_id
	 * @param array $data an array that contains the settings data, usually taken from $_POST
	 */
	function saveRegionSettings($region_id, $data) {

		if ($region_id == "")
			return false;

		if ((isset($data["old_region_id"])) && ($data["old_region_id"] != "")) {
			$qtxt="DELETE FROM ".$this->_getSettingTable()." WHERE region_id='".$data["old_region_id"]."'";
		}
		else {
			$qtxt="DELETE FROM ".$this->_getSettingTable()." WHERE region_id='$region_id'";
		}
		$q=$this->_executeQuery($qtxt);

		foreach($this->setting_list as $key=>$val) {
			$qtxt ="INSERT INTO ".$this->_getSettingTable()." (region_id, val_name, value) ";
			$qtxt.="VALUES('".$region_id."', '".$val."', '".$data[$val]."')";
			$q=$this->_executeQuery($qtxt);
		}

		return true;
	}


	/**
	 * @param array $raw_data is the array with the information passed by the user; usually $_POST
	 * @return array with the data in the correct format
	 */
	function checkData($raw_data) {

		$data["region_id"]=substr($raw_data["region_id"], 0, 255);
		$data["region_desc"]=substr($raw_data["region_desc"], 0, 255);
		if (isset($raw_data["old_region_id"]))
			$data["old_region_id"]=substr($raw_data["old_region_id"], 0, 255);
		$data["browsercode"]=substr($raw_data["browsercode"], 0, 255);

		foreach($this->setting_list as $key=>$val) {
			if (isset($raw_data[$val]))
				$data[$val]=substr($raw_data[$val], 0, 255);
		}

		return $data;
	}


	/**
	 * @param array $raw_data is the array with the information passed by the user; usually $_POST
	 * @return boolean with the result of the insert
	 */
	function addNewRegion($raw_data) {

		$data=$this->checkData($raw_data);

		$qtxt ="INSERT INTO ".$this->_getListTable()." ";
		$qtxt.="(region_id, region_desc, browsercode)";
		$qtxt.="VALUES ('".$data["region_id"]."', '".$data["region_desc"]."', '".$data["browsercode"]."')";
		$q=$this->_executeQuery($qtxt);

		if ($q) {
			$res=true;
			$this->saveRegionSettings($data["region_id"], $data);
		}
		else
			$res=false;

		return $res;
	}


	/**
	 * @param array $raw_data is the array with the information passed by the user; usually $_POST
	 * @return boolean with the result of the insert
	 */
	function updateRegion($raw_data) {

		$data=$this->checkData($raw_data);

		$qtxt ="UPDATE ".$this->_getListTable()." SET ";
		$qtxt.="region_id='".$data["region_id"]."', region_desc='".$data["region_desc"]."', ";
		$qtxt.="browsercode='".$data["browsercode"]."' ";
		$qtxt.="WHERE region_id='".$data["old_region_id"]."'";
		$q=$this->_executeQuery($qtxt);

		if ($q) {
			$res=true;
			$this->saveRegionSettings($data["region_id"], $data);
		}
		else
			$res=false;

		return $res;
	}


	/**
	 * @return string the auto detected region_id or 0 if it fail
	 */
	function autoDetectRegion() {

		if(!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
			$res=0;
			return $res;
		}
		$accept_language=$_SERVER["HTTP_ACCEPT_LANGUAGE"];
		// [TODO] move the code that makes the accept language array to lib.utils

		$al_arr=explode(",", $accept_language);

		$i=0;
		$res="";
		while(($res == "") && ($i < count($al_arr))) {

			$bl_arr=explode(";", $al_arr[$i]);
			$browser_language=$bl_arr[0];
			$browser_language =sql_escape_string(substr($browser_language, 0, 5));

			$qtxt="SELECT region_id FROM ".$this->_getListTable()." WHERE browsercode LIKE '%".$browser_language."%'";
			$q=$this->_executeQuery($qtxt);

			if (($q) && (sql_num_rows($q) > 0)) {
				$row=sql_fetch_array($q);
				$res=$row["region_id"];
			}

			$i++;
		}

		if ($res == "")
			$res=0; // Not Found

		return $res;
	}



	/**
	 *
	 * checkRegion will check if the parameter region_id is empty.. if it is then
	 * it will try to fill it using the auto detection, else will use the one passed.
	 * then it will check if the region id is available in the database; if it is not
	 * it will use the default region.
	 *
	 * @param string $region_id the region_id to check
	 *
	 * @return string a valid region_id
	 */
	function checkRegion($region_id) {

		if (($region_id === FALSE) || ($region_id == "")) {
			$region_id=$this->autoDetectRegion();
		}

		$all_regions=$this->getAllRegions();

		if (!in_array($region_id, $all_regions, true))
			$region_id=$this->getDefaultRegion();

		return $region_id;
	}

}

class DoceboDate {

	var $day="00";
	var $month="00";
	var $year="0000";
	var $hour="00";
	var $min="00";
	var $sec="00";
	var $ampm="";
	var $day_name=NULL;
	var $timestamp=0;


	/**
	 * InternalTime constructor
	 */
	function InternalTime() {
	}


	/**
	 * @param array $token_val array with the token values
	 * @param array $token_key array with the token keywords
	 *
	 * set the object's properties reading them from the $token_val array and
	 * mapping them using the $token_key array
	 */
	function setFromToken($token_val, $token_key) {

		$token_arr=array();

		foreach($token_key as $key => $val) {
			if(isset($token_val[$key])) $token_arr[$val] = $token_val[$key];
			else $token_arr[$val] = '';
		}


		foreach($token_arr as $key=>$val) {
//$from_arr=array("%d", "%m", "%Y", "%y", "%H", "%I", "%M", "%S", "%P");
			switch($key) {
				case "%d": { // Day
					$this->day=$val;
				} break;
				case "%m": { // Month
					$this->month=$val;
				} break;
				case "%Y": { // 4 digits Year
					$this->year=$val;
				} break;
				case "%y": { // 2 digits Year
					$this->year=$val;
					if ($this->year > 70) // Guess the first part of the year; not sure that is the better way though
						$this->year="19".$this->year;
					else
						$this->year="20".$this->year;
				} break;
				case "%H": { // 24h Hour
					$this->hour=$val;
				} break;
				case "%I": { // 12h Hour
					$this->hour=$val;
/*					if (isset($token_arr["%P"])) {
						if (strtolower($token_arr["%P"]) == "pm") {
							$this->hour=(int)$this->hour+12;
							if ($this->hour == 24)
								$this->hour="12";
							$this->hour=$this->leadingZero($this->hour, 2);
						}
						if (strtolower($token_arr["%P"]) == "am") {
							if ($this->hour == 12)
								$this->hour="24";
						}
					} */

				} break;
				case "%M": { // Minutes
					$this->min=$val;
				} break;
				case "%S": { // Seconds
					$this->sec=$val;
				} break;
				case "%P": { // AM/PM
					$this->ampm=$val;
				} break;
			}

		}

		// Reset the timestamp so it will be re-generated when getTimeStamp is called
		$this->timestamp=0;
	}


	/**
	 * @return int the timestamp resulting from the object's properties
	 */
	function getTimeStamp() {

		$this->timestamp = mktime(
			( ($this->hour <= 0) 	? 1		:  $this->hour ),
			( ($this->min <= 0) 	? 0		: $this->min ),
			( ($this->sec <= 0) 	? 0		: $this->sec ),
			( ($this->month <= 0) 	? 1		: $this->month ),
			( ($this->day <= 0) 	? 1		: $this->day ),
			( ($this->year <= 0) 	? 1970	: $this->year )
		);

		return $this->timestamp;
	}


	/**
	 * @param string $time_stamp unix timestamp
	 */
	function setTimeStamp($time_stamp) {
		$this->timestamp = $time_stamp;
	}


	/**
	 * set the timestamp from the object's properties
	 */
	function setFromTimeStamp() {
		$time_stamp = $this->getTimeStamp();
		if($time_stamp == -1) {

			$this->hour 	= '00';
			$this->min		= '00';
			$this->sec		= '00';
			$this->month	= '00';
			$this->day		= '00';
			$this->year		= '0000';
			$this->ampm		= "";
			$this->day_name	= NULL;
		} else {

			$this->hour 	= date("H", $time_stamp);
			$this->min		= date("i", $time_stamp);
			$this->sec		= date("s", $time_stamp);
			$this->month	= date("m", $time_stamp);
			$this->day		= date("d", $time_stamp);
			$this->year		= date("Y", $time_stamp);
			$this->ampm		= "";
			$this->day_name	= date("D", $time_stamp);
		}
	}


	function getDateArray() {

		$res=array();
		$res["sec"]=$this->sec;
		$res["min"]=$this->min;
		$res["hour"]=$this->hour;
		$res["day"]=$this->day;
		$res["month"]=$this->month;
		$res["year"]=$this->year;
		$res["ampm"]=$this->ampm;

		return $res;
	}


	function setFromDateArray($date) {

		$res=array();
		$this->sec=$this->leadingZero($date["sec"], 2);
		$this->min=$this->leadingZero($date["min"], 2);
		$this->hour=$this->leadingZero($date["hour"], 2);
		$this->day=$this->leadingZero($date["day"], 2);
		$this->month=$this->leadingZero($date["month"], 2);
		$this->year=$this->leadingZero($date["year"], 2);
		$this->ampm=$date["ampm"];

	}



	/**
	 * @param string $offset the offset in seconds
	 * @param string $todo what has to be done ("add" or "sub")
	 *
	 * changes the date/time adding or subtracting the passed value
	 */
	function setOffset($offset, $todo, $work_on=0) {
		$debug="";

		if ($work_on > 4)
			return 0;

		if (($work_on == 0) && ($offset < 0)) {
			if ($todo == "add") {
				$todo="sub";
			}
			else if ($todo == "sub") {
				$todo="add";
			}
			$offset=$offset*-1;
			$debug.="switched from -".$offset." to ".$offset."<br />\n";
		}

		$work_arr=array("sec", "min", "hour", "day", "month");
		$date_arr=$this->getDateArray();

		$cur=$work_arr[$work_on];
		$debug.="cur = ".$cur.": ".$date_arr[$cur];

		switch($cur) {

			case "min":
			case "sec": {
				$max=59;
				$min=0;
				$tot=60;
			} break;

			case "hour": {
				$max=23;
				$min=0;
				$tot=24;
			} break;

			case "day": {
				$max=$this->getMaxDaysForMonth($this->month, $this->year);
				$min=1;
				$tot=$max;
			} break;

			case "month": {
				$max=12;
				$min=1;
				$tot=$max;
			} break;

		}

		if ($todo == "add") {
			$new_temp_val=$date_arr[$cur]+$offset;
			$exceed=($new_temp_val > $max ? TRUE : FALSE);
		}
		else if ($todo == "sub") {
			$new_temp_val=$date_arr[$cur]-$offset;
			//$new_temp_val=$date_arr[$cur]-($offset % $tot);
			$exceed=($new_temp_val < $min ? TRUE : FALSE);
		}

		$debug.=" -&gt; : ".$new_temp_val;

		if ($exceed) {

			$new_temp_val=abs($new_temp_val);

			$new_offset=(int)($offset / $tot);
			$new_val=$date_arr[$cur]-($offset % $tot);

			$date_arr[$cur]=$new_val;
			$this->setFromDateArray($date_arr);

			$debug.=" -&gt; : ".$new_val."<br />";
			$debug.="new offset: ".$new_offset." - ";
			$debug.="todo: ".$todo;

			$this->setOffset($new_offset, $todo, $work_on+1);
		}
		else {
			$date_arr[$cur]=$new_temp_val;
			$this->setFromDateArray($date_arr);
		}

		//-debug-// echo "<hr />".$debug."</hr><br />";
	}


	function getMaxDaysForMonth($month, $year) {
		$res=0;

		if ( (($year % 4 == 0) && ($year % 100 > 0)) || ($year % 400 == 0))
			$leap=TRUE;
		else
			$leap=FALSE;

		switch($month) {

			case 2: {  // feb
				$res=($leap ? 29 : 28);
			} break;

			case 1:    // jan
			case 3:    // mar
			case 5:    // may
			case 7:    // jul
			case 8:    // aug
			case 10:   // aug
			case 12: { // aug
				$res=31;
			} break;

			case 4:    // jan
			case 6:    // mar
			case 9:    // may
			case 11: { // jul
				$res=30;
			} break;

		}

		return $res;
	}


	/**
	 * @param string $str our original string
	 * @param string $len the lenght of the resulting string
	 *
	 * @return string example: leadingZero("2", 3) => "002"
	 */
	function leadingZero($str, $len) {
		// Moved to lib.utils.php
		//require_once(_base_.'/lib/lib.utils.php');
		return leadingZero($str, $len);
	}


	function getInternalDate() {
		$res="";
		$res.=$this->year."-";
		$res.=$this->month."-";
		$res.=$this->day."-";
		$res.=$this->hour."-";
		$res.=$this->min."-";
		$res.=$this->sec;

		return $res;
	}


	function setInternalDate($year=FALSE, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE) {
		$this->year =($year !== FALSE ? $year : $this->year);
		$this->month =($month !== FALSE ? $month : $this->month);
		$this->day =($day !== FALSE ? $day : $this->day);
		$this->hour =($hour !== FALSE ? $hour : $this->hour);
		$this->min =($min !== FALSE ? $min : $this->min);
		$this->sec =($sec !== FALSE ? $sec : $this->year);
	}


}

?>
