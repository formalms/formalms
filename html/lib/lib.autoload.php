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

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * Definition of php magic __autoload() method.
 *
 * @param string $classname the classname that php are tring to istanciate
 *
 * @return void used
 */
function forma_autoload($classname)
{
    // purify the request
    $classname = (string)preg_replace('/[^a-zA-Z0-9\-\_]+/', '', $classname);

    // fixed bases classes
    $fixed = [
        // Layout
        'Layout' => _lib_ . '/layout/lib.layout.php',
        'LoginLayout' => _lib_ . '/layout/lib.loginlayout.php',

        // mvc
        'Model' => _lib_ . '/mvc/lib.model.php',
        'TreeModel' => _lib_ . '/mvc/lib.treemodel.php',

        'Controller' => _lib_ . '/mvc/lib.controller.php',
        'LmsController' => _lib_ . '/mvc/lib.lmscontroller.php',
        'AdmController' => _lib_ . '/mvc/lib.admcontroller.php',
        'AlmsController' => _lib_ . '/mvc/lib.almscontroller.php',
        'MobileController' => _lib_ . '/mvc/lib.mobilecontroller.php',
        'LobjLmsController' => _lms_ . '/controllers/LobjLmsController.php',

        // db
        'DbHelper' => _base_ . '/db/DbHelper.php',

        'Services_JSON' => _lib_ . '/lib.json.php',

        // i18n
        'Lang' => _i18n_ . '/lib.lang.php',
        'FormaLanguage' => _i18n_ . '/lib.lang.php',
        'Format' => _i18n_ . '/lib.format.php',

        // form file
        'Form' => _lib_ . '/lib.form.php',
        'DForm' => _lib_ . '/forms/lib.dform.php',

        // lib files
        'FormaACL' => _lib_ . '/lib.acl.php',
        'FormaACLManager' => _lib_ . '/lib.aclmanager.php',

        // widget
        'Widget' => _base_ . '/widget/lib.widget.php',

        //aws
        'Plugin' => _lib_ . '/lib.plugin.php',
        'PluginManager' => _lib_ . '/lib.pluginmanager.php',
        // lib jquery
        'JQueryLib' => _lib_ . '/lib.jquerylib.php',
    ];

    //search for a base class and include the file if found
    if (isset($fixed[$classname])) {
        if (file_exists($fixed[$classname])) {
            include_once $fixed[$classname];
        }

        return;
    }

    //possibile path for autoloading classes
    $path = [
        'adm' => [
            _adm_ . '/models',
            _adm_ . '/controllers',
        ],
        'alms' => [
            _lms_ . '/admin/models',
            _lms_ . '/admin/controllers',
        ],
        'lms' => [
            _lms_ . '/models',
            _lms_ . '/controllers',
        ],
        'lobj' => [
            _lms_ . '/models',
            _lms_ . '/controllers',
        ],
    ];

    //parse classname for info and path
    $location = [];
    if (preg_match('/(Mobile|Adm|Alms|Lms|Acms|Cms|Lobj)Controller$/', $classname, $location)) {
        // include controller file
        $loc = (isset($location[1]) ? strtolower($location[1]) : 'adm');
        $c_file = $path[$loc][1] . '/' . $classname . '.php';
        //if(file_exists($c_file))
        include_once \FormaLms\lib\Forma::inc($c_file);

        return;
    } elseif (preg_match('/(Mobile|Adm|Alms|Lms|Acms|Cms|Lobj)$/', $classname, $location)) {
        // include model file
        $loc = (isset($location[1]) ? strtolower($location[1]) : 'adm');
        $c_file = $path[$loc][0] . '/' . $classname . '.php';
        //if(file_exists($c_file))
        include_once \FormaLms\lib\Forma::inc($c_file);

        return;
    }

    // manage widgets classnames
    if (preg_match('/(Widget)/', $classname, $location)) {
        $loc = _base_ . '/widget/' . strtolower(str_replace(['WidgetController', 'Widget'], ['', ''], $classname));
        if (strpos($classname, 'Controller') !== false) {
            // include controller file
            $c_file = $loc . '/controller/' . $classname . '.php';
            if (file_exists($c_file)) {
                include_once \FormaLms\lib\Forma::inc($c_file);
            }

            return;
        } else { //if(strpos($classname, 'Model') !== false) {
            // include model file
            $c_file = $loc . '/model/' . $classname . '.php';
            if (file_exists($c_file)) {
                include_once \FormaLms\lib\Forma::inc($c_file);
            }

            return;
        }
    }
    // search for a standard filename in the library
    if (file_exists(_lib_ . '/lib.' . strtolower($classname) . '.php')) {
        if (!class_exists('Forma', false)) {
            include_once _lib_ . '/lib.' . strtolower($classname) . '.php';
        } else {
            include_once \FormaLms\lib\Forma::inc(_lib_ . '/lib.' . strtolower($classname) . '.php');
        }

        return;
    }

    // unable to autoload
}

spl_autoload_register('forma_autoload');
