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
 * @author 		Fabio Pirovano <fabio@docebo.com>
 *
 * @version 	$Id: lib.template.php 995 2007-03-09 14:15:07Z fabio $
 */

/**
 * @return string the actual template name
 */
function getTemplate()
{
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    // If saved in session use this one
    if ($session->has('template') && $session->get('template') != false) {
        if (!checkTemplateVersion($session->get('template'))) {
            return 'standard';
        }

        return $session->get('template');
    }

    // force_standard mode
    if ((array_key_exists('notuse_template', $_REQUEST) && isset($_REQUEST['notuse_template'])) || (array_key_exists('notuse_template', $GLOBALS) && $GLOBALS['notuse_template'] == true)) {
        $session->set('template', 'standard');
        $session->save();

        return $session->get('template');
    }

    //search for a template associated to the current host
    $plat_templ = parseTemplateDomain($_SERVER['HTTP_HOST']);
    if ($plat_templ != false) {
        $session->set('template', $plat_templ);
        $session->save();
        if (!checkTemplateVersion($session->get('template'))) {
            return 'standard';
        }

        return $plat_templ;
    }

    // search template according to the org_chart_tree option
    if (!Docebo::user()->isAnonymous()) {
        $qtxt = 'SELECT associated_template FROM
			%adm_org_chart_tree
			WHERE associated_template IS NOT NULL AND
			idst_oc IN (' . implode(',', Docebo::user()->getArrSt()) . ')
			ORDER BY iLeft DESC
			LIMIT 0,1';

        $re = sql_query($qtxt);
        if (sql_num_rows($re) > 0) {
            list($template_code) = sql_fetch_row($re);

            setTemplate($template_code);
            if (!checkTemplateVersion($session->get('template'))) {
                return 'standard';
            }

            return $session->get('template');
        }
    }

    // search for the default template
    $session->set('template', getDefaultTemplate());

    return $session->get('template');
}

/**
 * Search in the settings if the domain given has a template associated.
 *
 * @param <type> $curr_domain the current domain
 *
 * @return <mixed> fals eif there isn't a template associated, or the template name
 */
function parseTemplateDomain($curr_domain = false)
{
    if (!$domains = FormaLms\lib\Get::sett('template_domain', false)) {
        return false;
    }

    $domains = json_decode($domains, true) ?: [];

    foreach ($domains as $item) {
        if ($item['domain'] == $curr_domain) {
            return $item['template'];
        }
    }

    return false;
}

function getCurrentDomain($idOrg = null, $baseUrl = false)
{
    $domain = FormaLms\lib\Get::site_url();
    if (!($domains = FormaLms\lib\Get::sett('template_domain', false)) || $baseUrl) {
        return $domain;
    }

    $domains_tmp = json_decode($domains, true) ?: [];
    $domains = [];

    foreach ($domains_tmp as $item) {
        $domains[$item['node']] = $item;
    }

    if ($idOrg && isset($domains[$idOrg]) && $domains[$idOrg]['domain']) {
        return 'https://' . $domains[$idOrg]['domain'] . '/';
    } else {
        $sql = "SELECT idParent FROM core_org_chart_tree WHERE idOrg = $idOrg";
        $query = sql_query($sql);
        $node = sql_fetch_object($query);
        if ($node && $node->idParent) {
            return getCurrentDomain($node->idParent);
        }
    }

    return $domain;
}

/**
 * This function change the template used only in the session.
 *
 * @param string 	a valid template name
 */
function setTemplate($new_template)
{
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    if (is_dir(_templates_ . '/' . $new_template)) {
        $session->set('template', $new_template);
    } else {
        $session->set('template', getDefaultTemplate());
    }
    $session->save();
}

/**
 * Reset the template to the default.
 */
function resetTemplate()
{
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $session->remove('template');
    $session->save();
    setTemplate(getTemplate());
}

function readTemplateManifest($template_name, $key = false)
{
    $template_file = _templates_ . '/' . $template_name . '/manifest.xml';
    if (!file_exists($template_file)) {
        return false;
    }
    if ($xml = simplexml_load_file($template_file)) {
        $man_json = json_encode($xml);
        $man_array = json_decode($man_json, true);
        if (key_exists($key, $man_array)) {
            return $man_array[$key];
        }

        return $man_array;
    } else {
        return false;
    }
}
/**
 * Check the template version.
 *
 * @return bool false if template is not compatible, true if it is compatible
 */
function checkTemplateVersion($template_name)
{
    require_once Forma::inc(_adm_ . '/versions.php');
    $template_forma_version = readTemplateManifest($template_name, 'forma_version');
    $check = [];
    if ($template_forma_version) {
        if (version_compare(_template_min_version_, $template_forma_version) <= 0) {
            return true;
        }
    }

    return false;
}

function getTemplateVersion($template_name)
{
    require_once Forma::inc(_adm_ . '/versions.php');

    return readTemplateManifest($template_name, 'forma_version');
}

/**
 * Retrive a list of template.
 *
 * @return array an array with the existent templates
 */
function getTemplateList($set_keys = false, $platform = false)
{
    $templ = dir(_templates_ . '/');
    while ($elem = $templ->read()) {
        if ((is_dir(_templates_ . '/' . $elem)) && ($elem != '.') && ($elem != '..') && ($elem != '.svn') && $elem[0] != '_' && checkTemplateVersion($elem)) {
            if (!$set_keys) {
                $templArray[] = $elem;
            } else {
                $templArray[$elem] = $elem;
            }
        }
    }
    closedir($templ->handle);

    if (!$set_keys) {
        sort($templArray);
    } else {
        ksort($templArray);
    }

    reset($templArray);

    return $templArray;
}
/**
 * Search for the default template.
 *
 * @return string the default template saved in database
 */
function getDefaultTemplate($platform = false)
{
    $plat_templ = FormaLms\lib\Get::sett('defaultTemplate');
    if (is_dir(_templates_ . '/' . $plat_templ)) {
        return $plat_templ;
    } else {
        $array = getTemplateList();

        return array_pop($array);
    }
}

/**
 * @return string the absolute path of templates folder root
 */
function getAbsoluteBasePathTemplate($platform = false)
{
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    if ($platform === false) {
        if (defined('CORE') && $session->has('current_action_platform') && !empty($session->get('current_action_platform'))) {
            $platform = $session->get('current_action_platform');
        } else {
            $platform = FormaLms\lib\Get::cur_plat();
        }
    }
    if ($platform == 'fw') {
        $platform = 'framework';
    }
    if (!isset($GLOBALS['where_' . $platform])) {
        $platform = 'framework';
    }

    return $GLOBALS['where_' . $platform]
        . (substr($GLOBALS['where_' . $platform], -1) == '/' ? '' : '/') . 'templates/';
}

/**
 * @return string the absolute path of templates folder
 */
function getAbsolutePathTemplate($platform = false)
{
    return getAbsoluteBasePathTemplate($platform) . getTemplate() . '/';
}

/**
 * @return string the relative url of templates folder root
 */
function getRelativeBasePathTemplate($platform = false)
{
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    if ($platform === false) {
        if (defined('CORE') && $session->has('current_action_platform') && !empty($session->get('current_action_platform'))) {
            $platform = $session->get('current_action_platform');
        } else {
            $platform = FormaLms\lib\Get::cur_plat();
        }
    }
    if ($platform == 'fw') {
        $platform = 'framework';
    }
    if (!isset($GLOBALS['where_' . $platform . '_relative'])) {
        $platform = 'framework';
    }

    return $GLOBALS['where_' . $platform . '_relative']
        . (substr($GLOBALS['where_' . $platform . '_relative'], -1) == '/' ? '' : '/') . 'templates/';
}

/**
 * @return string the relative url of templates folder
 */
function getPathTemplate($platform = false)
{
    return FormaLms\lib\Get::tmpl_path($platform);
    //return getRelativeBasePathTemplate($platform).getTemplate().'/';
}

/**
 * @return string the relative address of the images directory
 */
function getPathImage($platform = false)
{
    return getPathTemplate($platform) . 'images/';
}

/**
 * @return string this function is added in 31/05/2017 and it returns the relative address of the restyling images directory
 */
function getPathRestylingImage($platform = false)
{
    return getPathTemplate($platform) . 'static/';
}

/**
 * @param string $text        The title of the area
 * @param string $image       the name of the gif in tampltes/xxx/images/area_title/
 * @param string $alt_image   The alt for the image [deprecated, not used]
 * @param bool   $ignore_glob ignore global value of the title
 *
 * @return string the code for a graceful title area
 */
function getTitleArea($text, $image = '', $alt_image = '', $ignore_glob = false)
{
    $is_first = true;
    if (!is_array($text)) {
        $text = [$text];
    }

    // $html = '<div class="title_block">'."\n";
    $html = '<div class="page-header">' . "\n";
    foreach ($text as $link => $title) {
        if ($is_first) {
            $is_first = false;
            // Retrive, if exists, name customized by the user for the module
            /*if(!$ignore_glob && isset($GLOBALS['module_assigned_name'][$GLOBALS['modname']]) && $GLOBALS['module_assigned_name'][$GLOBALS['modname']] != '') {
                $title = $GLOBALS['module_assigned_name'][$GLOBALS['modname']];
            }*/
            // Area title
            $html .= '<h1>'
                . (!is_int($link) ? '<a href="' . $link . '">' : '')
                . $title
                . (!is_int($link) ? '</a>' : '')
                . '</h1>' . "\n";

            $GLOBALS['page']->add('<li><a href="#main_area_title">' . Lang::t('_JUMP_TO', 'standard') . ' ' . $title . '</a></li>', 'blind_navigation');

            if ($title) {
                $GLOBALS['page_title'] = FormaLms\lib\Get::sett('page_title', '') . ' &rsaquo; ' . $title;
            }

            // Init navigation
            if (count($text) > 1) {
                // $html .= '<ul class="navigation">';
                $html .= '<ul class="breadcrumb">';
                //	if(!is_int($link)) {
                //		$html .= '<li><a href="'.$link.'">'. Lang::t('_START_PAGE', 'standard').' '.strtolower($title).'</a></li>';
                //	} else $html .= '<li>'. Lang::t('_START_PAGE', 'standard').' '.strtolower($title).'</li>';
            }
        } else {
            // if(is_int($link)) $html .= '<li> &rsaquo; '.$title.'</li>';
            // else $html .= ' <li> &rsaquo; <a href="'.$link.'">'.$title.'</a></li>';

            if (is_int($link)) {
                $html .= '<li>' . $title . '</li>';
            } else {
                $html .= ' <li><a href="' . $link . '">' . $title . '</a></li>';
            }
        }
    }
    if (count($text) > 1) {
        $html .= '</ul>' . "\n";
    }
    $html .= '</div>' . "\n";

    return $html;
}

/**
 * @param string $message    the error message
 * @param bool   $with_image add the standard error image or not
 *
 * @return string the code for a graceful error user interface
 */
function getErrorUi($message, $with_image = true)
{
    return UIFeedback::error($message);

    return '<p class="error_container">'
        . '<strong>' . $message . '</strong>'
        . '</p>';
}

/**
 * @param string $name the name of the result
 *
 * @return string the code for a graceful result confirmer
 **/
function getResultUi($name)
{
    return UIFeedback::info($name);

    return "\n" . '<p class="result_container">' . "\n\t"
        . '<strong>' . $name . '</strong>' . "\n"
        . '</p>' . "\n";
}

/**
 * @param string $message the information message
 *
 * @return string the code for a graceful information user interface
 */
function getInfoUi($message, $return = false)
{
    return UIFeedback::info($message, $return);

    return '<p class="information_container">'
        . '<strong>' . $message . '</strong>'
        . '</p>';
}

/**
 * @param string $link the link related with the back operation
 * @param string $name the name of the link
 * @param string $type the type of back ('link','button','submit')
 *                     if is selected button as type the link will be ignored
 *
 * @return string the code for a graceful back purpose
 **/
function getBackUi($link, $name, $type = 'link')
{
    switch ($type) {
        case 'button':
                return '<div class="container-back_button">'
                    . '<input class="button" type="button" value="' . $name . '" /></div>';

            break;
        case 'submit':
                return '<div class="container-back_button">'
                    . '<input class="button" type="submit" value="' . $name . '" /></div>';

            break;
        default:
                return '<div class="container-back">' . "\n\t" . '<a href="' . $link . '" '
                    . (FormaLms\lib\Get::sett('use_accesskey') == 'on' ? 'accesskey="b">' . $name . ' (b)' : '>' . $name) . '</a>' . "\n"
                    . '</div>' . "\n";
    }
}

/**
 * @param string $are_you_sure    the text to display in the title
 * @param string $central_text    the text in the central part
 * @param string $command_is_link if the undo and confirm command is link or button,
 * @param string $confirm_ref     if $command_is_link is true, this is the confirm link, else the button name and id
 *                                if the name contains "[" "]" they change it in this way "[" => "_", "]" => ""
 * @param string $undo_ref        if $command_is_link is true, this is the undo link, else the button name and id
 *                                if the name contains "[" "]" they change it in this way "[" => "_", "]" => ""
 * @param string $confirm_text    the text of the confirm action (optional)
 * @param string $undo_text       the text of the undo action (optional
 *
 * @return string the html code for the requested interface
 */
function getDeleteUi(
    $are_you_sure,
    $central_text,
    $command_is_link,
    $confirm_ref,
    $undo_ref,
    $confirm_text = false,
    $undo_text = false
) {
    require_once _base_ . '/lib/lib.form.php';

    $txt = '<h2>' . $are_you_sure . '</h2>'
        . '<p class="spacer">'
        . $central_text
        . '</p>'
        . '<p>';
    if ($command_is_link) {
        $txt .= '<a href="' . $confirm_ref . '">'
            . '<img src="' . getPathImage() . 'standard/delete.png" alt="' . ($confirm_text == false ? Lang::t('_CONFIRM') : $confirm_text) . '" />'
            . '&nbsp;' . ($confirm_text == false ? Lang::t('_CONFIRM') : $confirm_text) . '</a>&nbsp;&nbsp;'
            . '<a href="' . $undo_ref . '">'
            . '<img src="' . getPathImage() . 'standard/cancel.png" alt="' . ($undo_text == false ? Lang::t('_UNDO') : $undo_text) . '" />'
            . '&nbsp;' . ($undo_text == false ? Lang::t('_UNDO') : $undo_text) . ' </a>';
    } else {
        $confirm_ref_id = str_replace(']', '', str_replace('[', '_', $confirm_ref));
        $undo_ref_id = str_replace(']', '', str_replace('[', '_', $undo_ref));
        $txt .= Form::getButton($confirm_ref_id, $confirm_ref, Lang::t('_CONFIRM'), 'transparent_del_button')
            . '&nbsp;'
            . Form::getButton($undo_ref_id, $undo_ref, Lang::t('_UNDO'), 'transparent_undo_button');
    }
    $txt .= '</p>';

    return $txt;
}

/**
 * @param string $are_you_sure    the text to display in the title
 * @param string $central_text    the text in the central part
 * @param string $command_is_link if the undo and confirm command is link or button,
 * @param string $confirm_ref     if $command_is_link is true, this is the confirm link, else the button name and id
 *                                if the name contains "[" "]" they change it in this way "[" => "_", "]" => ""
 * @param string $undo_ref        if $command_is_link is true, this is the undo link, else the button name and id
 *                                if the name contains "[" "]" they change it in this way "[" => "_", "]" => ""
 * @param string $confirm_text    the text of the confirm action (optional)
 * @param string $undo_text       the text of the undo action (optional
 *
 * @return string the html code for the requested interface
 */
function getModifyUi(
    $are_you_sure,
    $central_text,
    $command_is_link,
    $confirm_ref,
    $undo_ref,
    $confirm_text = false,
    $undo_text = false
) {
    require_once _base_ . '/lib/lib.form.php';

    $txt = '<h2>' . $are_you_sure . '</h2>'
        . '<p class="spacer">'
        . $central_text
        . '</p>'
        . '<p>';
    if ($command_is_link) {
        $txt .= '<a class="ico-wt-sprite subs_confirm" href="' . $confirm_ref . '">'
            . '<span>' . ($confirm_text == false ? Lang::t('_CONFIRM') : $confirm_text) . '</span></a>&nbsp;&nbsp;'
            . '<a class="ico-wt-sprite subs_cancel" href="' . $undo_ref . '">'
            . '<span>' . ($undo_text == false ? Lang::t('_UNDO') : $undo_text) . '</span></a>';
    } else {
        $confirm_ref_id = str_replace(']', '', str_replace('[', '_', $confirm_ref));
        $undo_ref_id = str_replace(']', '', str_replace('[', '_', $undo_ref));
        $txt .= Form::getButton($confirm_ref_id, $confirm_ref, Lang::t('_CONFIRM'), 'transparent_del_button')
            . '&nbsp;'
            . Form::getButton($undo_ref_id, $undo_ref, Lang::t('_UNDO'), 'transparent_undo_button');
    }
    $txt .= '</p>';

    return $txt;
}

/**
 * @param string $entry the text that you want to add to the legenda
 *
 * @return string the text added
 */
function addLegendaEntry($entry)
{
    if (!isset($GLOBALS['_legenda'])) {
        $GLOBALS['_legenda'] = [];
    }

    return $GLOBALS['_legenda'][] = $entry;
}

/**
 * Destroy the entry in the legenda.
 */
function emptyLegenda()
{
    if (!isset($GLOBALS['_legenda'])) {
        $GLOBALS['_legenda'] = [];
    }
}

/**
 * @return string the legenda, if it has at least one entry
 */
function getLegenda()
{
    $text = '';
    if (!isset($GLOBALS['_legenda'])) {
        $GLOBALS['_legenda'] = [];
    }
    if (is_array($GLOBALS['_legenda']) && count($GLOBALS['_legenda'])) {
        $text = '<div id="legend" class="layout_legenda">
				<div class="title">Legenda</div>' . "\n";
        foreach ($GLOBALS['_legenda'] as $key => $value) {
            $text .= '<div class="legenda_line">' . "\n"
                . "\t" . $value . "\n"
                . '</div>' . "\n";
        }
        $text .= '</div>';
    }

    return $text;
}

function setAccessibilityStatus($new_status)
{
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    if (FormaLms\lib\Get::sett('accessibility', 'off') !== 'off') {
        $session->set('high_accessibility', $new_status);
    } else {
        $session->set('high_accessibility', false);
    }
    $session->save();
}

function getAccessibilityStatus()
{
    if (FormaLms\lib\Get::sett('accessibility') == 'off') {
        return false;
    }
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    if ($session->has('high_accessibility')) {
        return $session->get('high_accessibility') == 1;
    } else {
        return true;
    }
}

function getTemplateFromIdOrg(int $id_org)
{
    list($template_name) = sql_fetch_row(sql_query("select associated_template from core_org_chart_tree where idOrg=$id_org"));

    return $template_name;
}
