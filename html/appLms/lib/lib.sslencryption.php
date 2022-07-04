<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class SSLEncryption {

    protected static $ciphering = "AES-128-CTR";

    protected static $options = 0;

    protected static $encryption_iv = '1234567891011121';

    protected static $encryption_key = 'forma-lms';


    public static function encrpytString($string) {

        return openssl_encrypt($string, self::$ciphering, self::$encryption_key, self::$options, self::$encryption_iv);
    }

    public static function decrpytString($string) {

        return openssl_decrypt($string, self::$ciphering, self::$encryption_key, self::$options, self::$encryption_iv);
    }

    function base64url_encode($data) {
        return rtrim(strtr($data, '+/', '-_'), '=');
      }
      function base64_url_decode($input) {
       return strtr($input, '._-', '+/+');
      }

}