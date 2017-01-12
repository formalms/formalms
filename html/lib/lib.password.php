<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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
        $this->algorithm_default=Get::sett('pass_algorithm');
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


        // CLEAR->DEFAULT_HASH
        if ($info['algorithm']==$this->algorithm_default && $info['options']==$this->algorithm_options[$this->algorithm_default] && password_verify ($this->password, $text)){
            return PASSWORD_CORRECT;
        }
        // CLEAR->NOT_DEFAULT_HASH
        else if (is_int($info['algorithm'] && password_verify($this->password, $text))){
            return PASSWORD_UPDATE;
        }
        // CLEAR->MD5
        else if ($info['algorithm']==PASSWORD_MD5 && $this->generate_hash(PASSWORD_MD5)==$text){
            return PASSWORD_UPDATE;
        }
        // INCORRECT
        else {
            return PASSWORD_INCORRECT;
        }
    }
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