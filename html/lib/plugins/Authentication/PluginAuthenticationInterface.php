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

interface PluginAuthenticationInterface
{
    /*
     * Function creating the login GUI.
     */
    public static function getLoginGUI();

    /*
     * Function returning the user who has logged in.
     */
    public static function getUserFromLogin();
}
