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
 * This package define classes for XML DOM compatibility with PHP < 5
 * In Docebo we use DOM API from PHP5 but you can use the platform also
 *	in PHP4. So in this package we redefine all the DOM API used in Docebo.
 * For do that we use domxml.
 *
 * @package admin-library
 * @subpackage domxml
 * @version 	$Id: lib.domxml.php 113 2006-03-08 18:08:42Z ema $
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
**/

if( version_compare(phpversion(), "5.0.0") == -1 ) {
	require_once( dirname(__FILE__).'/lib.domxml4.php' );
} else {
	require_once( dirname(__FILE__).'/lib.domxml5.php' );
}
?>
