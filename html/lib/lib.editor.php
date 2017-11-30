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
 * @package 	admin-library
 * @category 	interaction
 */

/**
 * Called in loadHeader method of Block class include .js files if required
 *
 * @return string the code to be include(think to directly use PageWriter->append($string, 'head'))
 **/
function loadHeaderHTMLEditor() {
	$res="";

	if(getAccessibilityStatus() === false)
		$ht = Get::sett('hteditor');
	else
		$ht = 'accesseditor';

	$relative_path =$GLOBALS['where_framework_relative'];
	$relative_path.=( substr($GLOBALS['where_framework_relative'], -1) == '/' ? '' : '/' );

	switch($ht) {
		//using tiny_mce
		case 'tinymce':
			Util::get_js(Get::rel_path('base').'/addons/tiny_mce/tinymce.min.js', true, true);
			Util::get_js(Get::rel_path('base').'/addons/tiny_mce/forma.js', true, true); 
		break;

		case 'yui':
			$js = array(	'element'	=> 'element-beta-min.js',
							'container'	=> 'container_core-min.js',
							'menu'		=> 'menu-min.js',
							'button'	=> 'button-min.js',
							'editor'	=> 'editor-beta-min.js');

			YuiLib::load($js);

			$GLOBALS['page']->add('<link rel="stylesheet" type="text/css" href="'.$GLOBALS['where_framework_relative'].'/addons/yui/assets/skins/sam/skin.css">', 'page_head');
		break;

		//using normal textarea
		case 'accesseditor' :
		default :
		break;
	}


   
	if(isset($GLOBALS['page'])) {
		$GLOBALS['page']->add($res, 'page_head');
	} else {
		echo $res;
	}
}


/**
 * Create an instance of HTML Editor.
 *
 * @param string	$formid 		the id of the container form
 * @param string	$textarea_name 	id of textarea to use
 * @param string	$value initial 	content of text area
 *
 * @return string 	html for include the htmleditor
 **/
function loadHtmlEditor($id_form, $id, $name, $value, $css_text, $extra_param_for = false, $simple = false) {

	
    if (!isset($GLOBALS["HTML_EDITOR_SETUP"])) {
		loadHeaderHTMLEditor();
		$GLOBALS["HTML_EDITOR_SETUP"]=TRUE;
	}  else {
        loadHeaderHTMLEditor();        
    }
      
	if(getAccessibilityStatus() === false)
		$ht = Get::sett('hteditor');
	else
		$ht = 'accesseditor';

	$value=htmlspecialchars($value, ENT_COMPAT);

	switch($ht) {
		//using tiny_mce
		case 'tinymce':
			$txt =	'<textarea id="'.$id.'" name="'.$name.'" cols="52" rows="7" class="'.($simple ? 'tinymce_simple' : 'tinymce_complex').'">'."\n"
//			$txt =	'<textarea id="'.$id.'" name="'.$name.'" cols="52" rows="7" class="'.'tinymce_simple'.'">'."\n"
				.$value."\n"
				.'</textarea>'."\n";

			return $txt;
		break;

		case 'yui':
			$txt = '<textarea class="'.$css_text.'" id="'.$id.'" name="'.$name.'" cols="52" rows="7">'
				.htmlspecialchars ( $value, ENT_NOQUOTES )
				.'</textarea>';

			$txt .= '<script>var yuiEditor'.$id.' = new YAHOO.widget.Editor(\''.$id.'\', {
				     height: \'250px\',
				     width: \'100%\',
				     dompath: true,
				     animate: true
				     });
				     yuiEditor'.$id.'.render();';

			$txt .= 'YAHOO.util.Event.on(yuiEditor'.$id.'.get(\'element\').form, \'submit\', function onSubmitOperation'.$id.'()'
					.'{'
					.'yuiEditor'.$id.'.saveHTML();'
					.'}'
					.', yuiEditor'.$id.', true);</script>';

			return $txt;
		break;

		//using normal textarea
		case 'accesseditor' :
		default : {

			if(!$css_text) $css_text = 'textarea';
			return '<textarea class="'.$css_text.'" id="'.$id.'" name="'.$name.'" cols="52" rows="7">'
				.htmlspecialchars ( $value, ENT_NOQUOTES )
				.'</textarea>';
		};break;
	}
}


function getEditorExtra() {
	$res="";

	if(Get::accessibilty() === false)
		$ht = Get::sett('hteditor');
	else
		$ht = 'accesseditor';

	switch($ht) {

		case "xstandard": { // ---------------------------------- xstandard ---------
			$res="onsubmit=\"xstandardEventHandler();\"";
		} break;

	}

	return $res;
}


function getHTMLEditorList() {
	//EFFECTS: return an array that contain the list of html editor

	$reHt = sql_query("
	SELECT hteditor, hteditorname
	FROM ".$GLOBALS['prefix_fw']."_hteditor
	ORDER BY hteditorname");
	while(list($hteditor_db, $hteditorname_db) = sql_fetch_row($reHt)) {
		if(defined($hteditorname_db)) {
			$ht_array[$hteditor_db] = constant($hteditorname_db);
		}
		else {
			$ht_array[$hteditor_db] = strtolower(substr($hteditorname_db, 1));
		}
	}
	sql_free_result($reHt);
	return $ht_array;
}


?>
