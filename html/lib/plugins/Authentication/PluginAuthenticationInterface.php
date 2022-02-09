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