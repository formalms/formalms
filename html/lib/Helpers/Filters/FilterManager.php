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

class FilterManager
{
    /** @var string string */
    protected $cookiePath;

    /** @var string string */
    protected $cookieTime;

    public function __construct(string $cookiePath = null, string $cookieTime = null)
    {
        $this->cookiePath = $cookiePath ?? '/';
        $this->cookieTime = $time ?? (time() + (365 * 24 * 3600));
    }

    public function setFilterByCookie(string $cookieIndex, string $cookieValue, string $cookiePath = null, string $cookieTime = null): void
    {
        if ((int) $cookieValue === 0) {
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
