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
 * @package admin-library
 * @subpackage interaction
 * @author 		Fabio Pirovano <fabio@docebo.com>
 * @version 	$Id: lib.template.php 995 2007-03-09 14:15:07Z fabio $
 */

/**
 * @return string the actual template name
 */
function getTemplate() {
 
	// If saved in session use this one
	if(isset($_SESSION['template']) && $_SESSION['template'] != false) {
		return $_SESSION['template'];
	}
	//search for a template associated to the current host
	$plat_templ = parseTemplateDomain($_SERVER['HTTP_HOST']);
	if($plat_templ != false) {
		$_SESSION['template'] = $plat_templ;
		return $plat_templ;
	}

	// search template according to the org_chart_tree option
	if(!Docebo::user()->isAnonymous()) {

		$qtxt = "SELECT associated_template FROM
			%adm_org_chart_tree
			WHERE associated_template IS NOT NULL AND
			idst_oc IN (".implode(',', Docebo::user()->getArrSt()).")
			ORDER BY iLeft DESC
			LIMIT 0,1";
		
		$re =sql_query($qtxt);
		if (sql_num_rows($re) > 0) {
			list($template_code) = sql_fetch_row($re);

			setTemplate($template_code);
			return $_SESSION['template'];
		}
	}

	// search for the default template
	$_SESSION['template'] = getDefaultTemplate();
	return $_SESSION['template'];
}

/**
 * Search in the settings if the domain given has a template associated
 * @param <type> $curr_domain the current domain
 * @return <mixed> fals eif there isn't a template associated, or the template name
 */
function parseTemplateDomain($curr_domain = false) {

	$association = array();
	
	$domains = Get::sett('template_domain', false);
	if(!$domains) return false;
	$domains = str_replace(array("\r", "\n\n"), "\n", $domains);

	$rows = explode("\n", $domains);
	foreach($rows as $pair) {

		list($domain, $template) = explode(',', $pair);
		if($domain == $curr_domain) return $template;
	}
	return false;
}

/**
 * This function change the template used only in the session
 * @param string 	a valid template name
 */
function setTemplate($new_template) {

	if(is_dir(_base_.'/templates/'.$new_template)) {
		$_SESSION['template'] = $new_template;
	}
	else {
		$_SESSION['template'] = getDefaultTemplate();
	}
}

/**
 * Reset the template to the default
 */
function resetTemplate() {

	unset($_SESSION['template']);
	setTemplate(getTemplate());
}

/**
 * Retrive a list of template
 * @return array an array with the existent templates
 */
function getTemplateList($set_keys = FALSE, $platform = FALSE) {

	$templ = dir(_base_.'/templates/');
	while($elem = $templ->read()) {

		if((is_dir(_base_.'/templates/'.$elem)) && ($elem != ".") && ($elem != "..") && ($elem != ".svn") && $elem{0} != '_' && file_exists(_base_.'/templates/'.$elem."/manifest.xml")) {

			if (!$set_keys) $templArray[] = $elem;
			else $templArray[$elem] = $elem;
		}
	}
	closedir($templ->handle);

	if (!$set_keys) sort($templArray);
	else ksort($templArray);

	reset($templArray);
	return $templArray;
}

/**
 * Search for the default template
 * @return string 	the default template saved in database
 */
function getDefaultTemplate( $platform = false ) {

	$plat_templ = Get::sett('defaultTemplate');
	if(is_dir(_base_.'/templates/'.$plat_templ)) return $plat_templ;
	else return array_pop(getTemplateList());
}

/**
 * @return string the absolute path of templates folder root
 */
function getAbsoluteBasePathTemplate($platform = false) {

	if($platform === false) {
		if(defined("CORE") && isset($_SESSION['current_action_platform'])) $platform = $_SESSION['current_action_platform'];
		else $platform = Get::cur_plat();
	}
	if($platform == 'fw') $platform = 'framework';
	if(!isset($GLOBALS['where_'.$platform])) $platform = 'framework';
	return $GLOBALS['where_'.$platform]
				.( substr($GLOBALS['where_'.$platform], -1) == '/' ? '' : '/').'templates/';
}

/**
 * @return string the absolute path of templates folder
 */
function getAbsolutePathTemplate($platform = false) {

	return getAbsoluteBasePathTemplate($platform).getTemplate().'/';
}

/**
 * @return string the relative url of templates folder root
 */
function getRelativeBasePathTemplate($platform = false) {


	if($platform === false) {
		if(defined("CORE") && isset($_SESSION['current_action_platform'])) $platform = $_SESSION['current_action_platform'];
		else $platform = Get::cur_plat();
	}
	if($platform == 'fw') $platform = 'framework';
	if(!isset($GLOBALS['where_'.$platform.'_relative'])) $platform = 'framework';
	return $GLOBALS['where_'.$platform.'_relative']
				.( substr($GLOBALS['where_'.$platform.'_relative'], -1) == '/' ? '' : '/').'templates/';
}

/**
 * @return string the relative url of templates folder
 */
function getPathTemplate($platform = false) {

	return Get::tmpl_path($platform);
	//return getRelativeBasePathTemplate($platform).getTemplate().'/';
}

/**
 * @return string 	the relative address of the images directory
 */
function getPathImage($platform = false) {

	return getPathTemplate($platform).'images/';
}

/**
 * @return string 	this function is added in 31/05/2017 and it returns the relative address of the restyling images directory
 */
function getPathRestylingImage($platform = false) {

    return getPathTemplate($platform).'static/';
}

/**
 * @param string	$text		The title of the area
 * @param string	$image		the name of the gif in tampltes/xxx/images/area_title/
 * @param string	$alt_image	The alt for the image [deprecated, not used]
 * @param bool		$ignore_glob	ignore global value of the title
 *
 * @return string 	the code for a graceful title area
 */
function getTitleArea($text, $image = '', $alt_image = '', $ignore_glob = false) {

	$is_first = true;
	if(!is_array($text))
		$text = array($text);

	// $html = '<div class="title_block">'."\n";
	$html = '<div class="page-header">'."\n";
	foreach($text as $link => $title) {

		if($is_first) {

			$is_first = false;
			// Retrive, if exists, name customized by the user for the module
			/*if(!$ignore_glob && isset($GLOBALS['module_assigned_name'][$GLOBALS['modname']]) && $GLOBALS['module_assigned_name'][$GLOBALS['modname']] != '') {
				$title = $GLOBALS['module_assigned_name'][$GLOBALS['modname']];
			}*/
			// Area title
			$html .= '<h1>'
				.(!is_int($link) ? '<a href="'.$link.'">' : '' )
				.$title
				.(!is_int($link) ? '</a>' : '' )
				.'</h1>'."\n";

			$GLOBALS['page']->add('<li><a href="#main_area_title">'. Lang::t('_JUMP_TO', 'standard').' '.$title.'</a></li>', 'blind_navigation');

			if($title) $GLOBALS['page_title'] = Get::sett('page_title', '').' &rsaquo; '.$title;

			// Init navigation
			if(count($text) > 1) {
				// $html .= '<ul class="navigation">';
				$html .= '<ul class="breadcrumb">';
			//	if(!is_int($link)) {
			//		$html .= '<li><a href="'.$link.'">'. Lang::t('_START_PAGE', 'standard').' '.strtolower($title).'</a></li>';
			//	} else $html .= '<li>'. Lang::t('_START_PAGE', 'standard').' '.strtolower($title).'</li>';
			}
		} else {

			// if(is_int($link)) $html .= '<li> &rsaquo; '.$title.'</li>';
			// else $html .= ' <li> &rsaquo; <a href="'.$link.'">'.$title.'</a></li>';

			if(is_int($link)) $html .= '<li>'.$title.'</li>';
			else $html .= ' <li><a href="'.$link.'">'.$title.'</a></li>';
		}
	}
	if(count($text) > 1) $html .= '</ul>'."\n";
	$html .= '</div>'."\n";
	return $html;
}

/**
 * @param string	$message	the error message
 * @param bool		$with_image	add the standard error image or not
 *
 * @return string 	the code for a graceful error user interface
 */
function getErrorUi($message, $with_image = true) {
	return UIFeedback::error($message);

	return '<p class="error_container">'
		.'<strong>'.$message.'</strong>'
		.'</p>';
}

/**
 * @param string $name the name of the result
 *
 * @return string 	the code for a graceful result confirmer
 **/
function getResultUi( $name ) {
	return UIFeedback::info($name);

	return "\n".'<p class="result_container">'."\n\t"
		.'<strong>'.$name.'</strong>'."\n"
		.'</p>'."\n";
}

/**
 * @param string $message the information message
 *
 * @return string 	the code for a graceful information user interface
 */
function getInfoUi($message, $return = false) {
	return UIFeedback::info($message, $return);

	return '<p class="information_container">'
		.'<strong>'.$message.'</strong>'
		.'</p>';
}

/**
 * @param 	string 	$link 	the link related with the back operation
 * @param 	string 	$name 	the name of the link
 * @param 	string 	$type 	the type of back ('link','button','submit')
 * 							if is selected button as type the link will be ignored
 *
 * @return  string 	the code for a graceful back purpose
 **/
function getBackUi( $link, $name, $type = 'link' ) {

	switch($type) {
		case "button" : {
			return '<div class="container-back_button">'
				.'<input class="button" type="button" value="'.$name.'" /></div>';
		};break;
		case "submit" : {
			return '<div class="container-back_button">'
				.'<input class="button" type="submit" value="'.$name.'" /></div>';
		};break;
		default : {
			return '<div class="container-back">'."\n\t".'<a href="'.$link.'" '
					.( Get::sett('use_accesskey') == 'on' ? 'accesskey="b">'.$name.' (b)' : '>'.$name ).'</a>'."\n"
					.'</div>'."\n";
		}
	}
}

/**
 * @param 	string	$are_you_sure 		the text to display in the title
 * @param 	string	$central_text 		the text in the central part
 * @param 	string	$command_is_link 	if the undo and confirm command is link or button,
 										if is true, the other
 * @param 	string	$confirm_ref 		if $command_is_link is true, this is the confirm link, else the button name and id
 *										if the name contains "[" "]" they change it in this way "[" => "_", "]" => ""
 * @param 	string	$undo_ref 			if $command_is_link is true, this is the undo link, else the button name and id
 *										if the name contains "[" "]" they change it in this way "[" => "_", "]" => ""
 * @param 	string	$confirm_text 		the text of the confirm action (optional)
 * @param 	string	$undo_text 			the text of the undo action (optional
 *
 * @return string the html code for the requested interface
 */
function getDeleteUi($are_you_sure, $central_text, $command_is_link,
			$confirm_ref, $undo_ref, $confirm_text = false, $undo_text = false) {

	require_once(_base_.'/lib/lib.form.php');

	$txt = '<h2>'.$are_you_sure.'</h2>'
		.'<p class="spacer">'
		.$central_text
		.'</p>'
		.'<p>';
	if($command_is_link) {

		$txt .= '<a href="'.$confirm_ref.'">'
				.'<img src="'.getPathImage().'standard/delete.png" alt="'.( $confirm_text == false ? Lang::t('_CONFIRM') : $confirm_text ).'" />'
				.'&nbsp;'.( $confirm_text == false ? Lang::t('_CONFIRM') : $confirm_text ).'</a>&nbsp;&nbsp;'
				.'<a href="'.$undo_ref.'">'
				.'<img src="'.getPathImage().'standard/cancel.png" alt="'.( $undo_text == false ? Lang::t('_UNDO') : $undo_text ).'" />'
				.'&nbsp;'.( $undo_text == false ? Lang::t('_UNDO') : $undo_text ).' </a>';
	} else {

		$confirm_ref_id = str_replace(']', '', str_replace('[', '_', $confirm_ref));
		$undo_ref_id	= str_replace(']', '', str_replace('[', '_', $undo_ref));
		$txt .= Form::getButton($confirm_ref_id, $confirm_ref, Lang::t('_CONFIRM'), 'transparent_del_button')
			.'&nbsp;'
			.Form::getButton($undo_ref_id, $undo_ref, Lang::t('_UNDO'), 'transparent_undo_button');
	}
	$txt .= '</p>';
	return $txt;
}

/**
 * @param 	string	$are_you_sure 		the text to display in the title
 * @param 	string	$central_text 		the text in the central part
 * @param 	string	$command_is_link 	if the undo and confirm command is link or button,
 										if is true, the other
 * @param 	string	$confirm_ref 		if $command_is_link is true, this is the confirm link, else the button name and id
 *										if the name contains "[" "]" they change it in this way "[" => "_", "]" => ""
 * @param 	string	$undo_ref 			if $command_is_link is true, this is the undo link, else the button name and id
 *										if the name contains "[" "]" they change it in this way "[" => "_", "]" => ""
 * @param 	string	$confirm_text 		the text of the confirm action (optional)
 * @param 	string	$undo_text 			the text of the undo action (optional
 *
 * @return string the html code for the requested interface
 */
function getModifyUi($are_you_sure, $central_text, $command_is_link,
			$confirm_ref, $undo_ref, $confirm_text = false, $undo_text = false) {

	require_once(_base_.'/lib/lib.form.php');

	$txt = '<h2>'.$are_you_sure.'</h2>'
		.'<p class="spacer">'
		.$central_text
		.'</p>'
		.'<p>';
	if($command_is_link) {

		$txt .= '<a class="ico-wt-sprite subs_confirm" href="'.$confirm_ref.'">'
				.'<span>'.( $confirm_text == false ? Lang::t('_CONFIRM') : $confirm_text ).'</span></a>&nbsp;&nbsp;'
				.'<a class="ico-wt-sprite subs_cancel" href="'.$undo_ref.'">'
				.'<span>'.( $undo_text == false ? Lang::t('_UNDO') : $undo_text ).'</span></a>';
	} else {

		$confirm_ref_id = str_replace(']', '', str_replace('[', '_', $confirm_ref));
		$undo_ref_id	= str_replace(']', '', str_replace('[', '_', $undo_ref));
		$txt .= Form::getButton($confirm_ref_id, $confirm_ref, Lang::t('_CONFIRM'), 'transparent_del_button')
			.'&nbsp;'
			.Form::getButton($undo_ref_id, $undo_ref, Lang::t('_UNDO'), 'transparent_undo_button');
	}
	$txt .= '</p>';
	return $txt;
}

/**
 * @param string	$entry	the text that you want to add to the legenda
 *
 * @return string 	the text added
 */
function addLegendaEntry($entry) {

	if(!isset($GLOBALS['_legenda'])) $GLOBALS['_legenda'] = array();
	return $GLOBALS['_legenda'][] = $entry;
}

/**
 * Destroy the entry in the legenda
 */
function emptyLegenda() {

	if(!isset($GLOBALS['_legenda'])) $GLOBALS['_legenda'] = array();
}

/**
 * @return string 	the legenda, if it has at least one entry
 */
function getLegenda() {

	$text = '';
	if(!isset($GLOBALS['_legenda'])) $GLOBALS['_legenda'] = array();
	if(is_array($GLOBALS['_legenda']) && count($GLOBALS['_legenda'])) {
		$text = '<div id="legend" class="layout_legenda">
				<div class="title">Legenda</div>'."\n";
		foreach($GLOBALS['_legenda'] as $key => $value) {
			$text .= '<div class="legenda_line">'."\n"
				."\t".$value."\n"
				.'</div>'."\n";
		}
		$text .= '</div>';
	}
	return $text;
}


function setAccessibilityStatus($new_status) {

	if(Get::sett('accessibility', 'off') != 'off') {

		$_SESSION['high_accessibility'] = $new_status;
	} else {
		$_SESSION['high_accessibility'] = false;
	}
}

function getAccessibilityStatus() {

	if(Get::sett('accessibility') == 'off')
		return false;

	if(isset($_SESSION['high_accessibility']))
		return ($_SESSION['high_accessibility'] == 1);

	else return true;
}

