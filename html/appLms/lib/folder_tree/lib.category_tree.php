<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

function getNodeOptions($id_category, $is_leaf)
{
    $lang = &DoceboLanguage::CreateInstance('course', 'lms');
    $node_options = [];

    $node_options[] = [
        'id' => 'mod_' . $id_category,
        'command' => 'modify',
        //'content' => '<img src="'.FormaLms\lib\Get::tmpl_path().'images/standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'" />'
        'icon' => 'standard/edit.png',
        'alt' => $lang->def('_MOD'),
    ];

    if ($is_leaf) {
        $node_options[] = [
            'id' => 'del_' . $id_category,
            'command' => 'delete',
            //'content' => '<img src="'.FormaLms\lib\Get::tmpl_path().'images/standard/delete.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'" />'
            'icon' => 'standard/delete.png',
            'alt' => $lang->def('_DEL'),
        ];
    } else {
        $node_options[] = [
            'id' => 'del_' . $id_category,
            'command' => false,
            //'content' => '<img src="'.FormaLms\lib\Get::tmpl_path().'images/blank.png" />'
            'icon' => 'blank.png',
        ];
    }

    return $node_options;
}
