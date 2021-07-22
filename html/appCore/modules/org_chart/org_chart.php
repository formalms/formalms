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
 * @package  DoceboCore
 * @version  $Id: org_chart.php 113 2006-03-08 18:08:42Z ema $
 * @category Organization chart
 * @author   Fabio Pirovano <fabio@docebo.com>
 */

function saveOrgChartState($data) {
	require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php');
	$s_o = new Session_Save();
	$s_o->save('org_chart', $data);
}

function existOrgChartState() {
	require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php');
	$s_o = new Session_Save();
	return $s_o->nameExists('org_chart');
}

function &loadOrgChartState() {
	require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php');
	$s_o = new Session_Save();
	return $s_o ->load('org_chart');
}

function orgChart() {
	
}

function assign_field($id_folder) {
	
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.field.php');	
	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$lang =& DoceboLanguage::createInstance('organization_chart', 'framework');

	$out->add(
		getTitleArea($lang->def('_ORG_CHART'), 'org_chart' )
		.'<div class="std_block">'
		.Form::openForm('org_chart', 'index.php?modname=org_chart&amp;op=org_chart'));
	
	$fl = new FieldList();
	
	
	$out->add( 	Form::closeForm().'</div>');

}

function loadFormAction( &$tree, $op ) {
	require_once(_base_.'/lib/lib.form.php');
	require_once(dirname(__FILE__).'/tree.org_chart.php');
	
	$lang =& DoceboLanguage::createInstance('organization_chart', 'framework');
	
	$out =& $GLOBALS['page'];
	$out->add('<link href="templates/standard/style/base-old-treeview.css" rel="stylesheet" type="text/css">', 'page_head');
	$out->setWorkingZone('content');
	
	$out->add(
		getTitleArea($lang->def('_ORG_CHART'), 'org_chart' )
		.'<div class="std_block">'
		.Form::openForm('org_chart', 'index.php?modname=org_chart&amp;op=org_chart'));
	
	switch( $op ) {
		case 'newfolder':
			$out->add($tree->loadNewFolder());
		break;
		case 'deletefolder':
			$out->add($tree->loadDeleteFolder());
		break;
		case 'renamefolder':
			$out->add($tree->loadRenameFolder());
		break;
		case 'movefolder':
			$out->add($tree->loadMoveFolder());
		break;
		case 'assign_field':
			$out->add($tree->loadAssignField());
		break;
			
	}
	$out->add( Form::closeForm()
				.'</div>');
}


function orgDispatch($op, $id_folder = false, &$tree) {
	switch($op) {
		case "org_chart" : {
			orgChart();
		};break;
		case "newfolder":
		case "deletefolder":
		case "renamefolder":
		case "movefolder": 
		case "assign_field": {
			loadFormAction( $tree, $op );
		};break;
	}
}
?>