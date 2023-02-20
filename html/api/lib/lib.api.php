<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

define('_API_DEBUG', false);

//use single user-code authentication
define('_AUTH_UCODE', 0);
//use generated token authentication
define('_AUTH_TOKEN', 1);
//use secret key // ---- new auth method (alpha) 20110610
define('_AUTH_SECRET_KEY', 2);

define('_AUTH_UCODE_DESC', 'SINGLE_CODE');
define('_AUTH_TOKEN_DESC', 'GENERATED_TOKEN');
define('_AUTH_SECRET_KEY_DESC', 'SECRET_KEY');

define('_REST_OUTPUT_XML', 'xml');
define('_REST_OUTPUT_JSON', 'json');

define('_MIME_TYPE_XML', 'application/xml');
define('_MIME_TYPE_JSON', 'application/json');

// xml export function
define('_XML_VERSION', '1.0');
define('_XML_ENCODING', 'UTF-8');
define('_GENERIC_ELEMENT', 'element');

class API
{
    protected $db;
    protected $aclManager;
    protected $session;

    protected $needAuthentication = true;
    protected $authenticated = false;
    protected $buffer = '';
    protected \Symfony\Component\HttpFoundation\Request $request;

    public function __construct()
    {
        $this->db = DbConn::getInstance();
        $this->aclManager = Docebo::user()->getAclManager();
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $this->request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();
    }

    /**
     * Returns the buffer.
     *
     * @return <return>
     */
    public function get()
    {
        return $this->_buffer;
    }

    /**
     * Writes in the buffer.
     *
     * @param <string> $string
     */
    protected function _write($string)
    {
        $this->_buffer .= $string;
    }

    /**
     * Empty the buffer.
     */
    public function flush()
    {
        $this->_buffer = '';
    }

    public function authenticateUser($username, $password)
    {
        $acl_man = Docebo::user()->getAclManager();
        $query = 'SELECT * FROM %adm_user '
            . "WHERE userid='" . $this->aclManager->absoluteId($username) . "' AND pass='" . $this->aclManager->encrypt($password) . "'";
        $res = $this->db->query($query);

        return $this->db->num_rows($res) > 0;
    }

    /*
     * Check user authentication
     */
    public function checkAuthentication($code)
    {
        $result = ['success' => false];
        //eliminates old token
        $query = 'DELETE FROM %adm_rest_authentication WHERE expiry_date < NOW()';
        $this->db->query($query);

        if (!$this->needAuthentication) {
            $result = ['success' => true];
            //no authentication needed for this module
            return $result;
        }

        // ---- new auth method (alpha) 20110610 ---- [

        $auth_method = FormaLms\lib\Get::sett('rest_auth_method', 'none');

        // ]----

        switch ($auth_method) {
            // use application's pre-set authentication code
            case _AUTH_UCODE:
                    $auth_code = FormaLms\lib\Get::sett('rest_auth_code', false);

                    $headerAuth = str_replace(['FormaLMS', ' '], '', $_SERVER['HTTP_X_AUTHORIZATION']);

                    if ($code !== $auth_code && $headerAuth !== $auth_code) {
                        $result['message'] = 'Autentication code is not valid';
                    } else {
                        $result['success'] = true;
                    }

                break;
            // search the token in  authentications DB table
            case _AUTH_TOKEN:
                    $query = "SELECT * FROM %adm_rest_authentication WHERE token='$code'";
                    $res = $this->db->query($query);
                    if ($this->db->num_rows($res) > 0) {
                        $now = time();
                        $result = true;
                        $query = "UPDATE %adm_rest_authentication SET last_enter_date='" . date('Y-m-d H:i:s', $now) . "' ";
                        if (FormaLms\lib\Get::sett('rest_auth_update', false)) {
                            $lifetime = FormaLms\lib\Get::sett('rest_auth_lifetime', 1);
                            $query .= " , expiry_date='" . date('Y-m-d H:i:s', $now + $lifetime) . "' ";
                        }
                        $query .= " WHERE token='$code'";
                        $this->db->query($query);
                        $result['success'] = true;
                    } else {
                        $result['message'] = 'Autentication Token is not valid';
                    }

                break;
            case _AUTH_SECRET_KEY:
                 // ---- new auth method (alpha) 20110610 ---- [
                    $result = $this->checkKeys($GLOBALS['UNFILTERED_POST']);

                break; // ]----
            default:
        }

        return $result;
    }

    /**
     * Check if the request is valid.
     *
     * @param array $params the parameters recived by the api
     *
     * @return array
     */
    public function checkKeys($params)
    { // ---- new auth method (alpha) 20110610 ---- [
        // retive the hash recived with the api call
        $result = ['success' => true];
        if (!isset($_SERVER['HTTP_X_AUTHORIZATION'])) {
            return false;
        }

        // calculate the same hash locally
        $auth = base64_decode(str_replace('FormaLMS ', '', $_SERVER['HTTP_X_AUTHORIZATION']));
        $params_to_check = $this->getValuesFromParams($params);

        $hash_sha1 = sha1(implode(',', $params_to_check) . ',' . FormaLms\lib\Get::sett('rest_auth_api_secret', ''));

        // check if the two hashes match each other
        if (strtolower($auth) != strtolower(FormaLms\lib\Get::sett('rest_auth_api_key', '') . ':' . $hash_sha1)) {
            $result['success'] = false;
            $result['message'] = 'Api Key or Secret not valid';
        }

        return $result;
    }

    private function getValuesFromParams($params)
    {
        $params_to_check = [];
        foreach ($params as $val) {
            if (is_array($val)) {
                $values = $this->getValuesFromParams($val);
                foreach ($values as $value) {
                    $params_to_check[] = $value;
                }
            } else {
                $params_to_check[] = $val;
            }
        }

        return $params_to_check;
    }

    public function call($name, $params)
    {
        // Loads user information according to the external user data provided:
        $params = $this->populateParams($params);

        return $this->$name($params);
    }

    private function populateParams($params)
    {
        $params = $this->fillParamsFrom($params, $_POST);
        $params = $this->checkExternalUser($params, $_POST);

        return $params;
    }

    public static function Execute($auth_code, $module, $function, $params)
    {
        $result = ['success' => true, 'message' => ''];
        $class_name = $module . '_API';
        $file_name = Forma::inc(_base_ . '/api/lib/api.' . $module . '.php');

        if (!file_exists($file_name)) {
            $result['success'] = false;
            $result['message'] = sprintf('File not found : %s', $file_name);
        }
        if ($result['success']) {
            require_once $file_name;

            if (!class_exists($class_name)) {
                $result['success'] = false;
                $result['message'] = sprintf('Class not found : %s', $class_name);
            }
            if ($result['success']) {
                /** @var API $api_obj */
                $api_obj = new $class_name();

                $result = $api_obj->checkAuthentication($auth_code);

                if ($result['success']) {
                    $params = $api_obj->populateParams($params);
                    $result = $api_obj->call($function, $params);
                }
            }
        }

        return $result;
    }

    /**
     * Return the array of nested element into an xml format.
     *
     * @param array $arr
     *
     * @return string the xml formatted output
     */
    public static function getXML($arr)
    {
        $output = '';
        if (is_array($arr)) {
            $output .= '<?xml version="' . _XML_VERSION . '" encoding="' . _XML_ENCODING . '"?>';
            $output .= '<XMLoutput>';
            self::convert($output, $arr);
            $output .= '</XMLoutput>';
        }

        return $output;
    }

    protected static function getopentag($tagkey)
    {
        $output = '<';
        if (is_numeric($tagkey)) {
            $output .= _GENERIC_ELEMENT;
        } else {
            $output .= $tagkey;
        }
        $output .= '>';

        return $output;
    }

    protected static function getclosetag($tagkey)
    {
        $output = '</';
        if (is_numeric($tagkey)) {
            $output .= _GENERIC_ELEMENT;
        } else {
            $output .= $tagkey;
        }
        $output .= '>';

        return $output;
    }

    protected static function getstringval(&$value)
    {
        $output = '';
        if (is_bool($value)) {
            switch ($value) {
                case true:
                    $output .= 'true';
                    break;
                case false:
                    $output .= 'false';
                    break;
            }
        } else {
            $output .= $value;
        }

        return $output;
    }

    protected static function convert(&$out, &$data)
    {
        if (!is_array($data)) {
            return;
        }
        foreach ($data as $key => $val) {
            $out .= self::getopentag($key);
            if (is_array($val)) {
                self::convert($out, $val);
            } else {
                $out .= self::getstringval($val);
            }
            $out .= self::getclosetag($key);
        }
    }

    /**
     * Check if the params array contains information about the external user;
     * if found the idst value of the array will be overwritten with the
     * data found.
     *
     * @param <array> $params
     *
     * @return <array>
     */
    public function checkExternalUser($params, $data)
    {
        if (defined('_API_DEBUG') && _API_DEBUG) {
            file_put_contents('check_ext_user.txt', "\n\n----------------\n\n" . print_r($params, true) . ' || ' . print_r($data, true), FILE_APPEND);
        }

        if (!empty($data['ext_user']) && !empty($data['ext_user_type'])) {
            $pref_path = 'ext.user.' . $data['ext_user_type'];
            $pref_val = 'ext_user_' . $data['ext_user_type'] . '_' . (int) $data['ext_user'];

            $res = $this->aclManager->getUsersBySetting($pref_path, $pref_val);

            if (defined('_API_DEBUG') && _API_DEBUG) {
                file_put_contents('check_ext_user.txt', print_r($res, true), FILE_APPEND);
            }

            if (count($res) > 0) {
                $params[0] = $res[0];
                $params['idst'] = $res[0];
            } else {
                $params[0] = 0;
                $params['ext_not_found'] = true;

                // this will be useful for example for the createUser method..
                $params['ext_user'] = $data['ext_user'];
                $params['ext_user_type'] = $data['ext_user_type'];
            }
        }

        if (defined('_API_DEBUG') && _API_DEBUG) {
            file_put_contents('check_ext_user.txt', print_r($params, true), FILE_APPEND);
        }

        return $params;
    }

    public function fillParamsFrom($params, $data, $overwrite = false)
    {
        foreach ($data as $k => $val) {
            if (!isset($params[$k]) || $overwrite) {
                $params[$k] = $val;
            }
        }

        return $params;
    }
}
