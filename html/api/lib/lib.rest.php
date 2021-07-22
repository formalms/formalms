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

define('_REST_PARAM_NAME', 'q');
define('_REST_VALIDATOR_PARAM', 'api');
define('_REST_MINIMUM_PARAMS', 3);
define('_REST_APINAME_INDEX', 2);
define('_REST_APIMETHOD_INDEX', 3);

if (!defined("_REST_AUTH_CODE")) define("_REST_AUTH_CODE", 0);
if (!defined("_REST_AUTH_TOKEN")) define("_REST_AUTH_TOKEN", 1);


class RestAPI {

	/**
	 * Error handling
	 * @param <string> $error error message to print
	 * @param <type> $type error output type
	 * @return <type>
	 */
	static public function HandleError($error=_REST_STANDARD_ERROR, $type=_REST_OUTPUT_XML) {

		$output = '';
		$temp = array('error'=>$error);
		switch ($type) {
			case _REST_OUTPUT_XML:  {	$output .= API::getXML($temp); } break;
			case _REST_OUTPUT_JSON: {
				$json = new Services_JSON();
				$output .= $json->encode($temp);
			} break;
			default: {
				// handler doesn't know how to format the output, so send raw string
				$output .= $error;
			} break;
		}
		return $output;
	}

	/**
	 * Debug information handling, it's used only in developement context
	 * @param <string> $message debug message
	 * @param <string> $type output type to use
	 * @return <string> formatted error message
	 */
	static public function HandleDebugInfo($message, $type=_REST_OUTPUT_XML) {

		$output = '';
		$f_msg = $message;
		$temp = array('debug'=>$f_msg);
		switch ($type) {
			case _REST_OUTPUT_XML: { $output .= API::getXML($temp); } break;
			case _REST_OUTPUT_JSON: {
				$json = new Services_JSON();
				$output .= $json->encode($temp);
			} break;
			default: $output .= $message; break; //handler doesn't know how to format the output, so send raw string
		}
		return $output;
	}

	/**
	 * Handle the output in the correct format
	 * @param <type> $arr
	 * @param <type> $type
	 * @return string
	 */
	public static function HandleOutput(&$arr, $type=_REST_OUTPUT_XML) {
		$output = '';
		switch ($type) {
			case _REST_OUTPUT_XML: {
				$output .= API::getXML($arr);
			} break;
			case _REST_OUTPUT_JSON: {
				$json = new Services_JSON();
				$output .= $json->encode($arr);
			} break;
			default: {
				$output .= '<error>Invalid type setting.</error>';
			}
		}
		return $output;
	}
	
	/**
	 * Retrieve an user id by token, if authenticated
	 * @param <type> $token auth token
	 * @return <type> the user associated to the token
	 */
	static public function getUserIdByToken($token) {

		$output = false;
		$query = "SELECT * FROM %adm_rest_authentication WHERE token='$token'";
		$res = sql_query($query);
		if(sql_num_rows($res) > 0) {
			$row = sql_fetch_assoc($res);
			$output = $row['id_user'];
		}
		return $output;
	}

}
