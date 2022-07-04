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

    public static function encrpytDownloadUrl($string) {
    
        $computedString = self::encrpytString($string);

        //return rtrim(strtr($computedString, '+/', '-_'), '=');
        return str_replace('=', '@' ,base64_encode($computedString));
    }

    public static function decrpytDownloadUrl($string) {
    
        $computedString = base64_decode(str_replace('@', '=' ,$string));

        //return rtrim(strtr($computedString, '+/', '-_'), '=');
        return self::decrpytString($computedString);
    }

}