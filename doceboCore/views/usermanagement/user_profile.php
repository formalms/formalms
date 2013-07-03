<?php

/*
 * View parameters:
 * - $id_user : idst of the profile's user;
 * - $title : profile's form title;
 * - $profile : instance of the profile class;
 * - $model : instance of the usermanagement model;
 * - $json : instance of the Services_JSON class;
 *
 */

$body = "";

$body .= $profile->getHead();
$body .= $profile->performAction();
//$body .= $this->_profileBackUrl();
$body .= $profile->getFooter();

$tabs = '<div id="profile_dialog_tabview" class="yui-navset">'
	.'<ul class="yui-nav">'
	.'<li class="selected"><a href="#profile_tab1"><em>'.Lang::t('_PROFILE', 'profile').'</em></a></li>'
	.'<li><a href="#profile_tab2"><em>'.Lang::t('_USERCOURSE_STATS_TITLE', 'profile').'</em></a></li>'
	.'<li><a href="#profile_tab3"><em>'.Lang::t('_USERCOMPETENCES_CAPTION', 'profile').'</em></a></li>'
	.'<li><a href="#profile_tab4"><em>'.Lang::t('_FUNCTIONAL_ROLE', 'menu').'</em></a></li>'
	.'<li><a href="#profile_tab4"><em>'.Lang::t('_GROUPS', 'admin_directory').'</em></a></li>'
	.'</ul>'
	.'<div class="yui-content">'
	.'<div id="profile_tab1">'.$body.'</div>'
	.'<div id="profile_tab2" class="little_table">'.$profile->getUserLmsStat($id_user).'</div>'
	.'<div id="profile_tab3" class="little_table">'.$profile->getUserCompetencesList($id_user).'</div>'
	.'<div id="profile_tab4" class="little_table">'.$profile->getUserFunctionalRolesList($id_user).'</div>'
	.'<div id="profile_tab4" class="little_table">'.$profile->getUserGroupsList($id_user).'</div>'
	.'</div></div>';

if (isset($json)) {
	$output = array(
		'success' => true,
		'header' => $title,
		'body' => $tabs
	);
	echo $this->json->encode($output);
} else {
	echo getTitleArea($title);
	echo '<div class="std_block">';
	echo $body;
	echo '</div>';
}
?>