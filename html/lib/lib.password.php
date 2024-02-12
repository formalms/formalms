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

define('PASSWORD_INCORRECT', 0);
define('PASSWORD_CORRECT', 1);
define('PASSWORD_UPDATE', 2);

define('PASSWORD_MD5', 0);

class Password
{
    private $policies;
    private $algorithm_default;
    private $algorithm_options;
    private $password;
    /**
     * @var string[]
     */
    public array $algorithm_functions;

    /**
     * Password constructor.
     *
     * @param $password String containing unencrypted password
     *
     * @return Password
     */
    public function __construct($password)
    {
        $this->password = $password;
        $this->algorithm_default = ((int)FormaLms\lib\Get::sett('pass_algorithm', PASSWORD_BCRYPT) ===1 ? '2y': FormaLms\lib\Get::sett('pass_algorithm', PASSWORD_BCRYPT));
        $this->algorithm_options = [
            PASSWORD_BCRYPT => [
                'cost' => 10,
            ],
        ];
        $this->algorithm_functions = [
            PASSWORD_BCRYPT => 'password_verify_php',
            PASSWORD_MD5 => 'password_verify_md5',
        ];
    }

    /**
     * Returns the password's informations.
     *
     * @param $password
     *
     * @return array
     */
    public function info($password)
    {
        $result = [
            'algorithm' => null,
            'options' => null,
        ];
        $info = password_get_info($password);
        if (is_null($info['algo'])) {
            if (preg_match('/^[a-f0-9]{32}$/', $password)) {
                $result['algorithm'] = PASSWORD_MD5;
            } else {
                $result['algorithm'] = false;
            }
        } else {
            $result['algorithm'] = $info['algo'];
            $result['options'] = $info['options'];
        }

        return $result;
    }

    private function generate_hash($algorithm)
    {
        $options = @$this->algorithm_options[$algorithm];
        if ($algorithm == PASSWORD_MD5) {
            return md5($this->password);
        } else {
            if (isset($options)) {
                return password_hash($this->password, $algorithm, $options);
            } else {
                return password_hash($this->password, $algorithm);
            }
        }
    }

    /**
     * Returns the hash of the password.
     *
     * @param bool $algorithm
     *
     * @return bool|false|string
     */
    public function hash($algorithm = false)
    {
        if (!$algorithm) {
            $algorithm = $this->algorithm_default;
        }

        return $this->generate_hash($algorithm);
    }

    private function password_verify_php($text)
    {
        return password_verify($this->password, $text);
    }

    private function password_verify_md5($text)
    {
        return $this->generate_hash(PASSWORD_MD5) == $text;
    }

    private function verify_password_by_algorithm($hash, $algorithm = false)
    {
        if ($algorithm) {
            $algorithm = $this->algorithm_functions[$algorithm];

            return $this->$algorithm($hash);
        } else {
            foreach ($this->algorithm_functions as $function) {
                if ($this->$function($hash)) {
                    return true;
                }
            }
        }
    }

    /**
     * Verify the password.
     *
     * @param $text
     *
     * @return int
     */
    public function verify($text)
    {
        $info = $this->info($text);
        $default_alg = $this->algorithm_default;
        // IF VERIFIED WITH DEFAULT ALGORITHM
        if ($info['algorithm'] == $default_alg && $this->verify_password_by_algorithm($text, $default_alg)) {
            return PASSWORD_CORRECT;
        }
        // IF VERIFIED WITH OTHER ALGORITHM
        elseif ($this->verify_password_by_algorithm($text)) {
            return PASSWORD_UPDATE;
        }
        // IF NOT VERIFIED
        else {
            return PASSWORD_INCORRECT;
        }
    }

    /**
     * Returns the password policies.
     *
     * @return PasswordPolicies
     */
    public function policies()
    {
        return $this->policies;
    }
}

class PasswordPolicies
{
    private $valid;
    private $messages = [];

    public function __construct($valid, $messages = [])
    {
        $this->valid = $valid;
        $this->messages = $messages;
    }

    public static function check($password)
    {
        $policies = [
            'pass_min_char' => FormaLms\lib\Get::sett('pass_min_char'),
            'pass_alfanumeric' => FormaLms\lib\Get::sett('pass_alfanumeric'),
            'pass_min_digit' => FormaLms\lib\Get::sett('pass_min_digit'),
            'pass_min_lowercase' => FormaLms\lib\Get::sett('pass_min_lowercase'),
            'pass_min_uppercase' => FormaLms\lib\Get::sett('pass_min_uppercase'),
            'pass_special_char' => FormaLms\lib\Get::sett('pass_special_char'),
            'pass_history' => FormaLms\lib\Get::sett('user_pwd_history_length'),
            'pass_different_from_user' => 1
        ];



        $result = true;
        $messages = [];
        foreach ($policies as $policy => $value) {
            $message = self::$policy($password, $value);
            if (isset($value) && $message) {
                $result = false;
                $messages[] = $message;
            }
        }

        return new self($result, $messages);
    }

    public function messages()
    {
        return $this->messages;
    }

    public function valid()
    {
        return $this->valid;
    }

    private static function pass_min_char($password, $policy)
    {
        if (strlen($password) < $policy) {
            return Lang::t('_REG_PASS_MIN_CHAR','register',['[min_char]'=>$policy]);
        } else {
            return false;
        }
    }

    private static function pass_alfanumeric($password, $policy)
    {
        if ($policy == 'on') {
            if (!preg_match('/[a-z]/i', $password) || !preg_match('/[0-9]/', $password)) {
                return Lang::t('_REG_PASS_MUST_BE_ALPNUM', 'register');
            }
        }

        return false;
    }

    private static function pass_min_digit($password, $policy)
    {
        preg_match_all('/\d/', $password, $matches);
        $total_digit = count($matches[0]);
        if ( $policy > 0 && $total_digit < $policy) {
            if ( $policy === 1 )
                return Lang::t('_REG_PASS_MIN_DIGITS_1','register',['[min_char]'=>$policy]);
            else {
                return Lang::t('_REG_PASS_MIN_DIGITS','register',['[min_char]'=>$policy]);
            }
        } else {
            return false;
        }
    }

    private static function pass_min_lowercase($password, $policy)
    {
        preg_match_all('/[[:lower:]]/', $password, $matches);
        $total_lower = count($matches[0]);
        if ($policy > 0 && $total_lower < $policy) {
            if ( $policy === 1 )
                return Lang::t('_REG_PASS_MIN_LOWER_1','register',['[min_char]'=>$policy]);
            else {
                return Lang::t('_REG_PASS_MIN_LOWER','register',['[min_char]'=>$policy]);
            }
        } else {
            return false;
        }
    }

    private static function pass_min_uppercase($password, $policy)
    {
        preg_match_all('/[[:upper:]]/', $password, $matches);
        $total_upper = count($matches[0]);
        if ($policy > 0 && $total_upper < $policy) {
            if ( $policy === 1 )
                return Lang::t('_REG_PASS_MIN_UPPER_1','register',['[min_char]'=>$policy]);
            else {
                return Lang::t('_REG_PASS_MIN_UPPER','register',['[min_char]'=>$policy]);
            }
        } else {
            return false;
        }
    }

    private static function pass_special_char($password, $policy)
    {

        preg_match_all('/[^[:upper:][:lower:][:digit:]]/u', $password, $matches);
        $total_special = count($matches[0]);
        if ($policy > 0 && $total_special < $policy) {
            if ( $policy === 1 )
                return Lang::t('_REG_PASS_MIN_NONALPHANUM_1','register',['[min_char]'=>$policy]);
            else {
                return Lang::t('_REG_PASS_MIN_NONALPHANUM','register',['[min_char]'=>$policy]);
            }
        } else {
            return false;
        }
    }

    private static function pass_different_from_user($password, $policy) {
        $a = \FormaLms\lib\FormaUser::getCurrentUser()->getUserId();
        if ($policy == 1 && $password === \FormaLms\lib\FormaUser::getCurrentUser()->getUserId()) {
            return Lang::t('_PASS_DIFFERENT_USERNAME', 'register');
        } else {
            return false;
        }
    }

    private static function pass_history($password, $policy) {
        if ($policy != 0) {
            $idst = \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt();
            $acl_man = \FormaLms\lib\Forma::getAclManager();
            $user_info = $acl_man->getUser($idst, false);

            $q = "SELECT passw from core_password_history where idst_user=$idst order by PWD_DATE DESC limit $policy";
            $p = new Password($password);
            $result = sql_query($q);
            while ($row = sql_fetch_assoc($result)) {
                $used = $p->verify($row['passw']);
                if ($used) {
                    return Lang::t('_REG_PASS_MUST_DIFF', 'register', ['[min_char]' => $policy]);
                }
            }
            return false;
        }
        return false;
    }

}
