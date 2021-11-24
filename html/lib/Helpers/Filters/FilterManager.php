<?php

class FilterManager
{

    /** @var string string */
    protected $cookiePath;

    /** @var string string */
    protected $cookieTime;

    public function __construct(string $cookiePath = null , String $cookieTime = null)
    {
        $this->cookiePath = $cookiePath ?? "/";
        $this->cookieTime = $time ?? (time() + (365 * 24 * 3600));
    }

    public function setFilterByCookie(string $cookieIndex, string $cookieValue, string $cookiePath = null, String $cookieTime = null) : void {

        if((int) $cookieValue === 0) {
            //if a 0 value is passed a cookie reset happens
            $cookieValue = '';
        }
        setcookie($cookieIndex,
                    $cookieValue,
                    $cookieTime ?? $this->cookieTime,    // for an entire year);
                    $cookiePath ?? $this->cookiePath
                    );
    }

    
}
