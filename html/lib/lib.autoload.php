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

/**
 * Definition of php magic __autoload() method
 * @param <string> $classname the classname that php are tring to istanciate
 * @return not used
 */
function docebo_autoload($classname) {

	// purify the request
	$classname = preg_replace('/[^a-zA-Z0-9\-\_]+/', '', $classname);

	// fixed bases classes
	$fixed = array(
		// Layout
		'Layout'			=> _lib_.'/layout/lib.layout.php',
		'LoginLayout'		=> _lib_.'/layout/lib.loginlayout.php',

		// mvc
		'Model'				=> _lib_.'/mvc/lib.model.php',
		'TreeModel'			=> _lib_.'/mvc/lib.treemodel.php',

		'Controller'		=> _lib_.'/mvc/lib.controller.php',
		'LmsController'		=> _lib_.'/mvc/lib.lmscontroller.php',
		'AdmController'		=> _lib_.'/mvc/lib.admcontroller.php',
		'AlmsController'	=> _lib_.'/mvc/lib.almscontroller.php',
		'MobileController'	=> _lib_.'/mvc/lib.mobilecontroller.php',
		'LobjLmsController'	=> _lms_.'/controllers/LobjLmsController.php',

		// db
		'DbConn'			=> _base_.'/db/lib.docebodb.php',
		'Mysql_DbConn'		=> _base_.'/db/drivers/docebodb.mysql.php',
		'Mysqli_DbConn'		=> _base_.'/db/drivers/docebodb.mysqli.php',
        
		// i18n
		'Lang'				=> _i18n_.'/lib.lang.php',
		'DoceboLanguage'	=> _i18n_.'/lib.lang.php',
		'Format'			=> _i18n_.'/lib.format.php',

		// Cache
		'ICache'			=> _lib_.'/cache/icache.php',
		'DCache'			=> _lib_.'/cache/dcache.php',
		'DApcCache'			=> _lib_.'/cache/dapccache.php',
		'DDummyCache'		=> _lib_.'/cache/ddummycache.php',
		'DFileCache'		=> _lib_.'/cache/dfilecache.php',
		'DMemcache'			=> _lib_.'/cache/dmemcache.php',
		
		// form file
		'Form'				=> _lib_.'/lib.form.php',
		'DForm'				=> _lib_.'/forms/lib.dform.php',
		
		// lib files
		'DoceboACL'			=> _lib_.'/lib.acl.php',
		'DoceboACLManager'	=> _lib_.'/lib.aclmanager.php',

		// widget
		'Widget'			=> _base_.'/widget/lib.widget.php',
		
		// exception
		'DoceboException'	=> _lib_.'/error/doceboexception.php',
		'MvcException'		=> _lib_.'/error/mvcexception.php',
		
		//aws
		'Plugin'			=> _lib_.'/lib.plugin.php',
		'PluginManager'		=> _lib_.'/lib.pluginmanager.php',
         // lib jquery
        'jquerylib'         => _lib_.'/lib.jquerylib.php',
        

	);

	//search for a base class and include the file if found
	if(isset($fixed[$classname])) {
		if(file_exists($fixed[$classname])) include_once( $fixed[$classname] );
		return;
	}

	//possibile path for autoloading classes
	$path = array(
		'adm' => array(
			_adm_.'/models',
			_adm_.'/controllers'
		),
		'alms' => array(
			_lms_.'/admin/models',
			_lms_.'/admin/controllers'
		),
		'lms' => array(
			_lms_.'/models',
			_lms_.'/controllers'
		),
		'lobj' => array(
			_lms_.'/models',
			_lms_.'/controllers'
		)
	);


	//possibile path for autoloading classes custom
	$pathCustomscripts = array(
		'adm' => array(
			_base_.'/customscripts'.'/'._folder_adm_.'/models',
			_base_.'/customscripts'.'/'._folder_adm_.'/controllers'
#			_adm_.'/customscripts/models',
#			_adm_.'/customscripts/controllers'
		),
		'alms' => array(
			_base_.'/customscripts'.'/'._folder_lms_.'/admin/models',
			_base_.'/customscripts'.'/'._folder_lms_.'/admin/controllers'
#			_lms_.'/customscripts/admin/models',
#			_lms_.'/customscripts/admin/controllers'
		),
		'lms' => array(
			_base_.'/customscripts'.'/'._folder_lms_.'/models',
			_base_.'/customscripts'.'/'._folder_lms_.'/controllers'
#			_lms_.'/customscripts/models',
#			_lms_.'/customscripts/controllers'
		),
		'lobj' => array(
			_base_.'/customscripts'.'/'._folder_lms_.'/models',
			_base_.'/customscripts'.'/'._folder_lms_.'/controllers'
#			_lms_.'/customscripts/models',
#			_lms_.'/customscripts/controllers'
		)
	);
        
	//parse classname for info and path
	$location = array();
	if(preg_match('/(Mobile|Adm|Alms|Lms|Acms|Cms|Lobj)Controller$/', $classname, $location)) {
		// include controller file
		$loc = ( isset($location[1]) ? strtolower($location[1]) : 'adm' );
                if (file_exists($pathCustomscripts[$loc][1].'/'.$classname.'.php') && Get::cfg('enable_customscripts', false) == true ){
                    $c_file = $pathCustomscripts[$loc][1].'/'.$classname.'.php';
                } else {
                    $c_file = $path[$loc][1].'/'.$classname.'.php';
                }
		//if(file_exists($c_file))
			include_once(Forma::inc($c_file));
		return;
	} else if(preg_match('/(Mobile|Adm|Alms|Lms|Acms|Cms|Lobj)$/', $classname, $location)) {
		// include model file
		$loc = ( isset($location[1]) ? strtolower($location[1]) : 'adm' );
                if (file_exists($pathCustomscripts[$loc][0].'/'.$classname.'.php') && Get::cfg('enable_customscripts', false) == true ){
                    $c_file = $pathCustomscripts[$loc][0].'/'.$classname.'.php';
                } else {
                    $c_file = $path[$loc][0].'/'.$classname.'.php';
                }
		//if(file_exists($c_file))
			include_once(Forma::inc($c_file));
		return;
	}

	// manage widgets classnames
	if(preg_match ('/(Widget)/', $classname, $location)) {
		$loc = _base_.'/widget/'.strtolower(str_replace(array('WidgetController', 'Widget'), array('', ''), $classname));
		if(strpos($classname, 'Controller') !== false) {
			// include controller file
			$c_file = $loc.'/controller/'.$classname.'.php';
			if(file_exists($c_file)) include_once(Forma::inc($c_file));
			return;
		} else { //if(strpos($classname, 'Model') !== false) {
			// include model file
			$c_file = $loc.'/model/'.$classname.'.php';
			if(file_exists($c_file)) include_once(Forma::inc($c_file));
			return;
		}
	}
	// search for a standard filename in the library
	if(file_exists(_lib_.'/lib.'.strtolower($classname).'.php')) {
		
		if(!class_exists('Forma', false)) include_once( _lib_.'/lib.'.strtolower($classname).'.php' );
		else include_once( Forma::inc(_lib_.'/lib.'.strtolower($classname).'.php') );
		return;
	}

	// unable to autoload
}

spl_autoload_register('docebo_autoload');
