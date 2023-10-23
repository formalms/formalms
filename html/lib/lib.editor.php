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
 * Create an instance of HTML Editor.
 *
 * @param string $formid        the id of the container form
 * @param string $textarea_name id of textarea to use
 * @param string $value         initial 	content of text area
 *
 * @return string html for include the htmleditor
 **/
function loadHtmlEditor($id_form, $id, $name, $value, $css_text, $extra_param_for = false, $simple = false)
{

    $ht = FormaLms\lib\Get::sett('hteditor');
    $value = htmlspecialchars($value, ENT_COMPAT);

    switch ($ht) {
        //using tiny_mce
        case 'tinymce':
            $txt = '<textarea id="' . $id . '" name="' . $name . '" cols="52" rows="7" class="' . ($simple ? 'tinymce_simple' : 'tinymce_complex') . '">' . "\n"
                . $value . "\n"
                . '</textarea>' . "\n";

            return $txt;
        break;
        //using normal textarea
        case 'accesseditor':
        default:
            if (!$css_text) {
                $css_text = 'textarea';
            }
            return '<textarea class="' . $css_text . '" id="' . $id . '" name="' . $name . '" cols="52" rows="7">'
                . htmlspecialchars($value, ENT_NOQUOTES)
                . '</textarea>';
         break;
    }
}

function getEditorExtra()
{
    $res = '';

    if (FormaLms\lib\Get::accessibilty() === false) {
        $ht = FormaLms\lib\Get::sett('hteditor');
    } else {
        $ht = 'accesseditor';
    }

    switch ($ht) {
        case 'xstandard':  // ---------------------------------- xstandard ---------
            $res = 'onsubmit="xstandardEventHandler();"';
         break;
    }

    return $res;
}

function getHTMLEditorList()
{
    //EFFECTS: return an array that contain the list of html editor

    $reHt = sql_query('
	SELECT hteditor, hteditorname
	FROM ' . $GLOBALS['prefix_fw'] . '_hteditor
	ORDER BY hteditorname');
    while (list($hteditor_db, $hteditorname_db) = sql_fetch_row($reHt)) {
        if (defined($hteditorname_db)) {
            $ht_array[$hteditor_db] = constant($hteditorname_db);
        } else {
            $ht_array[$hteditor_db] = strtolower(substr($hteditorname_db, 1));
        }
    }
    sql_free_result($reHt);

    return $ht_array;
}
