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
/*
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
*/
$active_tab = Get::req('active_tab', DOTY_STRING, 'profile_tab1');

if ($active_tab != 'profile_tab1' && $active_tab != 'profile_tab2' && $active_tab != 'profile_tab3' && $active_tab != 'profile_tab4' && $active_tab != 'profile_tab5') {

    $active_tab = 'profile_tab1';

}
$tabs = '<div id="tab_message">'
    . '<ul class="nav nav-tabs">'
    . '<li' . ($active_tab == 'profile_tab1' ? ' class="active"' : '') . '><a data-toggle="tab" href="#profile_tab1"><em>' . Lang::t('_PROFILE', 'profile') . '</em></a></li>'
    . '<li' . ($active_tab == 'profile_tab2' ? ' class="active"' : '') . '><a data-toggle="tab" href="#profile_tab2"><em>' . Lang::t('_USERCOURSE_STATS_TITLE', 'profile') . '</em></a></li>'
    . '<li' . ($active_tab == 'profile_tab3' ? ' class="active"' : '') . '><a data-toggle="tab" href="#profile_tab3"><em>' . Lang::t('_USERCOMPETENCES_CAPTION', 'profile') . '</em></a></li>'
    . '<li' . ($active_tab == 'profile_tab4' ? ' class="active"' : '') . '><a data-toggle="tab" href="#profile_tab4"><em>' . Lang::t('_FUNCTIONAL_ROLE', 'menu') . '</em></a></li>'
    . '<li' . ($active_tab == 'profile_tab5' ? ' class="active"' : '') . '><a data-toggle="tab" href="#profile_tab5"><em>' . Lang::t('_GROUPS', 'admin_directory') . '</em></a></li>'
    . '</ul>'
    . '<div class="tab-content">'
    . '<div class="tab-pane' . ($active_tab == 'profile_tab1' ? ' active' : '') . '" id="profile_tab1">' . $body . '</div>'
    . '<div class="tab-pane' . ($active_tab == 'profile_tab2' ? ' active' : '') . '" id="profile_tab2">' . $profile->getUserLmsStat($id_user) . '</div>'
    . '<div class="tab-pane' . ($active_tab == 'profile_tab3' ? ' active' : '') . '" id="profile_tab3">' . $profile->getUserCompetencesList($id_user) . '</div>'
    . '<div class="tab-pane' . ($active_tab == 'profile_tab4' ? ' active' : '') . '" id="profile_tab4">' . $profile->getUserFunctionalRolesList($id_user) . '</div>'
    . '<div class="tab-pane' . ($active_tab == 'profile_tab5' ? ' active' : '') . '" id="profile_tab5">' . $profile->getUserGroupsList($id_user) . '</div>'
    . '</div>'
    . '</div>';

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