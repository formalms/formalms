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
require_once(_base_.'/addons/password_compact/password.php');

define("PASSWORD_INCORRECT", 0);
define("PASSWORD_CORRECT", 1);
define("PASSWORD_UPDATE", 2);

define("PASSWORD_MD5", 0);

class Password {
    private $policies;
    private $algorithm_default;
    private $algorithm_options;
    private $password;

    /**
     * Password constructor.
     * @param $password String containing unencrypted password
     * @return Password
     */
    function Password($password) {
        $this->policies=PasswordPolicies::check($password);
        $this->password=$password;
        $this->algorithm_default=(int)Get::sett('pass_algorithm', PASSWORD_MD5);
        $this->algorithm_options = array(
            PASSWORD_BCRYPT=>array(
                'cost'=>10
            )
        );
        $this->algorithm_functions = array(
            PASSWORD_BCRYPT=>"password_verify_php",
            PASSWORD_MD5=>"password_verify_md5"
        );
    }

    /**
     * Returns the password's informations
     * @param $password
     * @return array
     */
    public function info($password) {
        $result=array(
            'algorithm'=>null,
            'options'=>null
        );
        $info=password_get_info($password);
        if ($info['algo']==0){
            if (preg_match('/^[a-f0-9]{32}$/', $password)){
                $result['algorithm']=PASSWORD_MD5;
            } else {
                $result['algorithm']=false;
            }
        } else {
            $result['algorithm']=$info['algo'];
            $result['options']=$info['options'];
        }
        return $result;
    }
    private function generate_hash($algorithm){
        $options=@$this->algorithm_options[$algorithm];
        if ($algorithm==PASSWORD_MD5) {
            return md5($this->password);
        } else {
            if (isset($options)){
                return password_hash($this->password, $algorithm, $options);
            } else {
                return password_hash($this->password, $algorithm);
            }
        }
    }

    /**
     * Returns the hash of the password
     * @param bool $algorithm
     * @return bool|false|string
     */
    public function hash($algorithm=false){
        if (!$algorithm){
            $algorithm=$this->algorithm_default;
        }
        return $this->generate_hash($algorithm);
    }

    private function password_verify_php($text){
        return password_verify($this->password, $text);
    }

    private function password_verify_md5($text){
        return $this->generate_hash(PASSWORD_MD5)==$text;
    }

    private function verify_password_by_algorithm($hash,$algorithm=false){
        if ($algorithm){
            $algorithm=$this->algorithm_functions[$algorithm];
            return $this->$algorithm($hash);
        } else {
            foreach ($this->algorithm_functions as $function){
                if ($this->$function($hash)){
                    return true;
                }
            }
        }
    }

    /**
     * Verify the password
     * @param $text
     * @return int
     */
    public function verify($text){
        $info=$this->info($text);
        $default_alg=$this->algorithm_default;
        // IF VERIFIED WITH DEFAULT ALGORITHM
        if ($info['algorithm']==$default_alg && $this->verify_password_by_algorithm($text,$default_alg)){
            return PASSWORD_CORRECT;
        }
        // IF VERIFIED WITH OTHER ALGORITHM
        else if ($this->verify_password_by_algorithm($text)){
            return PASSWORD_UPDATE;
        }
        // IF NOT VERIFIED
        else {
            return PASSWORD_INCORRECT;
        }
    }

    /**
     * Returns the password policies
     * @return PasswordPolicies
     */
    public function policies(){
        return $this->policies;
    }
}

class PasswordPolicies {
    private $valid;
    private $messages=array();
    function __construct($valid,$messages=array()) {
        $this->valid=$valid;
        $this->messages=$messages;
    }

    static public function check($password){
        $policies = array(
            'pass_min_char'=>Get::sett('pass_min_char'),
            'pass_alfanumeric'=>Get::sett('pass_alfanumeric'),
            'pass_min_digits'=>Get::sett('pass_min_digits'),
            'pass_min_lower'=>Get::sett('pass_min_lower'),
            'pass_min_upper'=>Get::sett('pass_min_upper'),
            'pass_min_nonalphanum'=>Get::sett('pass_min_nonalphanum')
        );
        $result=true;
        $messages=array();
        foreach ($policies as $policy=>$value){
            $message=self::$policy($password,$value);
            if (isset($value) && $message){
                $result=false;
                $messages[]=$message;
            }
        }
        return new self($result,$messages);
    }

    public function messages() {
        return $this->messages;
    }

    public function valid() {
        return $this->valid;
    }

    private function pass_min_char($password,$policy){
        if(strlen($password) < $policy) {
            return Lang::t('_PASSWORD_TOO_SHORT', 'configuration');
        } else {
            return false;
        }
    }
    private function pass_alfanumeric($password,$policy){
        if ($policy=="on"){
            if( !preg_match('/[a-z]/i', $password) || !preg_match('/[0-9]/', $password) ) {
                return Lang::t('_ERR_PASSWORD_MUSTBE_ALPHA', 'configuration');
            }
        }
        return false;
    }
    private function pass_min_digits($password,$policy){
        if (!preg_match('/[[:digit:]]/u', $password) < $policy) {
            return Lang::t('_ERR_PASSWORD_MIN_DIGITS', 'configuration');
        } else {
            return false;
        }
    }
    private function pass_min_lower($password,$policy){
        if (!preg_match('/[[:lower:]]/u', $password) < $policy) {
            return Lang::t('_ERR_PASSWORD_MIN_LOWER', 'configuration');
        } else {
            return false;
        }
    }
    private function pass_min_upper($password,$policy){
        if (!preg_match('/[[:upper:]]/u', $password) < $policy) {
            return Lang::t('_ERR_PASSWORD_MIN_UPPER', 'configuration');
        } else {
            return false;
        }
    }
    private function pass_min_nonalphanum($password,$policy){
        if (!preg_match('/[^[:upper:][:lower:][:digit:]]/u', $password) < $policy) {
            return Lang::t('_ERR_PASSWORD_MIN_NONALPHANUM', 'configuration');
        } else {
            return false;
        }
    }

}