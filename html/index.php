<?php

/* ========================================================================	\
|	DOCEBO - The E-Learning Suite											|
| 																			|
|	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
| =========================================================================	|
| 																			|
| Docebo is free software. You can redistribute it and/or modify			|
| it under the terms of the GNU General Public License as published by		|
| the Free Software Foundation, either version 2 of the License, or			|
| (at your option) any later version.										|
| 																			|
| Docebo is distributed in the hope that it will be useful,					|
| but WITHOUT ANY WARRANTY; without even the implied warranty of			|
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the				|
| GNU General Public License for more details.								|
| 																			|
| You should have received a copy of the GNU General Public License			|
| along with Docebo. If not, see: http://www.gnu.org/licenses/.				|
\ ======================================================================== */

define("IN_DOCEBO", true);
define("_deeppath_", '');
require(dirname(__FILE__).'/base.php');

// start buffer
ob_start();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_PAGE_WR);

if(!Docebo::user()->isAnonymous() && (!isset($_GET['modname']) || $_GET['modname'] != 'login')) {

	require_once(_base_.'/lib/lib.platform.php');
	$pm = PlatformManager::createInstance();
	if($pm->getHomePlatform() == 'cms') Util::jump_to(_folder_cms_.'/index.php');
	Util::jump_to(_folder_lms_.'/index.php');
}

// instanciate the page-writer that we want (maybe we can rewrite it in a
// different way with the introduction of the layout manager)
emptyPageWriter::createInstance();
$db =& DbConn::getInstance();

// redirect if the main page is the cms
$query_platform = "SELECT platform
FROM ".$GLOBALS['prefix_fw']."_platform
WHERE main = 'true'
LIMIT 0, 1";
list($sel) = $db->fetch_row($db->query($query_platform));
if($sel == 'cms') {
	Util::jump_to(_folder_cms_);
}

// load the requested module
$module_cfg = false;
$GLOBALS['modname'] = Get::req('modname', DOTY_ALPHANUM, '');
$GLOBALS['op']		= Get::req('op', DOTY_ALPHANUM, '');
$r					= Get::req('r', DOTY_MIXED, '');
$GLOBALS['mvc']		= $r;

if(!empty($GLOBALS['modname'])) {
	require_once(_lms_.'/lib/lib.istance.php');
	$module_cfg =& createModule($GLOBALS['modname']);
}
if($r !== '')
{
	$GLOBALS['page']->add(Util::get_css(Layout::path().'style/base.css', true), 'page_head');
	$GLOBALS['page']->add(Util::get_css(Layout::path().'style/base-old-treeview.css', true), 'page_head');
	$GLOBALS['page']->add(Util::get_css(Layout::path().'style/lms.css', true), 'page_head');
	$GLOBALS['page']->add(Util::get_css(Layout::path().'style/lms-to-review.css', true), 'page_head');
	$GLOBALS['page']->add(Util::get_css(Layout::path().'style/lms-menu.css', true), 'page_head');
	$GLOBALS['page']->add(Util::get_css(Layout::path().'style/print.css', true), 'page_head');

	$r = preg_replace('/[^a-zA-Z0-9\-\_\/]+/', '', $r);
	$r = explode('/', $r);
	if(count($r) == 3) {
		// Position, class and method defined in the path requested
		$mvc_class = ucfirst(strtolower($r[1])). ucfirst(strtolower($r[0])).'Controller';
		$mvc_name = $r[1];
		$task = $r[2];
	} else {
		// Only class and method defined in the path requested
		$mvc_class = ''.ucfirst(strtolower($r[0])).'LmsController';
		$mvc_name = $r[0];
		$task = $r[1];
	}
	ob_clean();
	$controller = new $mvc_class( $mvc_name );
	$controller->request($task);

	$GLOBALS['page']->add(ob_get_contents(), 'content');

	if($r[0] === 'homecatalogue')
		$layout = 'home_catalogue';
	else
		$layout = 'home';

	ob_clean();
}
else
{
	// layout selection
	if($op == '') $op = 'login';
	switch ($op) {
		case 'login': {
			$layout = 'home_login';
		};break;
		default: {
			if ($module_cfg) {
				$layout = 'home';
				$module_cfg->loadBody();
			}
			else { die(); }
		};break;
	}
}

// -----------------------------------------------------------------------------

// finalize
Boot::finalize();

// remove all the echo and put them in the debug zone
$GLOBALS['page']->add(ob_get_contents(), 'debug');
ob_clean();

// layout
Layout::render($layout);

// flush buffer
ob_end_flush();

?>