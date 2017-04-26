<?php

/* 
 * Interface to implement for auth-type plugins.
 */
interface PluginAuthenticationInterface {
    
    /*
     * Function creating the login GUI.
     */
    static function getLoginGUI();
    
    /*
     * Function returning the user who has logged in.
     */
    static function getUserFromLogin();
}

use appCore\Events;

/*
 * Base class of auth plugins.
 * Plugins must implement PluginAuthInterface.
 */
abstract class PluginAuthentication extends FormaPlugin {
    
    public function __construct() {
        
    }
}