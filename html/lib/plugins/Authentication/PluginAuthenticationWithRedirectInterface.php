<?php

/*
 * Interface to implement for auth-type plugins.
 */
interface PluginAuthenticationWithRedirectInterface {

    /*
     * Function creating the login GUI.
     */
    static function getLoginGUI($redirect = '');

    /*
     * Function returning the user who has logged in.
     */
    static function getUserFromLogin();
}