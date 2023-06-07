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

/**
 * @author        Fabio Pirovano <fabio@docebo.com>
 *
 * @version    $Id: lib.istance.php 573 2006-08-23 09:38:54Z fabio $
 */

/**
 * create a istance of a specified class of a module
 * automaticaly include the file that contains the class of the module.
 *
 * @param string $module_name the name og the module to istance
 * @param string $class_name the name of the class relative to the module, if not passed is
 *                            extracted from the $module_name
 *
 * @return mixed the class istance
 */
function createModule($module_name, $class_name = null)
{
    $module_name = preg_replace('/[^a-zA-Z0-9\-\_]+/', '', $module_name);
    $dirPath = dirname(__DIR__, 1) . '/class.module/';
    $fileName = 'class.' . $module_name . '.php';
    if (file_exists($dirPath . $fileName)) {
        include_once \FormaLms\lib\Forma::include($dirPath, $fileName);
        if ($class_name === null) {
            $class_name = 'Module_' . ucfirst($module_name);
        }
    } else {
        include_once \FormaLms\lib\Forma::include(dirname(__DIR__, 1) . '/class.module/', 'class.definition.php');
        $class_name = 'LmsModule';
    }

    if (checkIfPlugin($module_name) == 'plugin') {
        include_once FormaLms\lib\Get::rel_path('plugins') . '/' . $module_name . '/class/class.' . $module_name . '.php';
        $class_name = 'Module_' . ucfirst($module_name);
    }

    if (class_exists($class_name)) {
        $module_cfg = new $class_name();

        return $module_cfg;
    }

    return null;
}

function checkIfPlugin($module_name)
{
    [$module_info] = sql_fetch_row(sql_query('SELECT module_info'
        . ' FROM learning_module'
        . " WHERE module_name = '" . $module_name . "'"));

    return $module_info;
}
