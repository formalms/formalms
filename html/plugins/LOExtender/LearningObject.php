<?php

namespace Plugin\LOExtender;

defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class LearningObject {

    public function getParamInfo($lo, &$params) {
                
        $params = array_merge($params, array(
            array('label' => 'Prova Tutti 1', 'param_name' => 'prova_tutti_1')
          , array('label' => 'Prova Tutti 2', 'param_name' => 'prova_tutti_2')
        ));

        switch($lo->getObjectType()) {
            case 'scormorg':
                $params = array_merge($params, array(
                    array('label' => 'Prova ScormOrg 1', 'param_name' => 'prova_scormorg_1')
                  , array('label' => 'Prova ScormOrg 2', 'param_name' => 'prova_scormorg_2')
                ));
                break;
            case 'htmlpage':
                $params = array_merge($params, array(
                    array('label' => 'Prova HtmlPage 1', 'param_name' => 'prova_htmlpage_1')
                  , array('label' => 'Prova HtmlPage 2', 'param_name' => 'prova_htmlpage_2')
                ));
                break;
        }
    }
    
    public function renderCustomSettings($lo, $values, &$out) {

        $out .= \Form::getTextfield('Prova Tutti 1', 'prova_tutti_1', 'prova_tutti_1', 255, isset($values['prova_tutti_1']) ? $values['prova_tutti_1'] : '');
        $out .= \Form::getTextfield('Prova Tutti 2', 'prova_tutti_2', 'prova_tutti_2', 255, isset($values['prova_tutti_2']) ? $values['prova_tutti_2'] : '');

        switch($lo->getObjectType()) {
            case 'scormorg':
                $out .= \Form::getTextfield('Prova ScormOrg 1', 'prova_tutti_1', 'prova_scormorg_1', 255, isset($values['prova_scormorg_1']) ? $values['prova_scormorg_1'] : '');
                $out .= \Form::getTextfield('Prova ScormOrg 2', 'prova_tutti_2', 'prova_scormorg_2', 255, isset($values['prova_scormorg_2']) ? $values['prova_scormorg_2'] : '');
                break;
            case 'htmlpage':
                $out .= \Form::getTextfield('Prova HtmlPage 1', 'prova_htmlpage_1', 'prova_htmlpage_1', 255, isset($values['prova_htmlpage_1']) ? $values['prova_htmlpage_1'] : '');
                $out .= \Form::getTextfield('Prova HtmlPage 2', 'prova_htmlpage_2', 'prova_htmlpage_2', 255, isset($values['prova_htmlpage_2']) ? $values['prova_htmlpage_2'] : '');
                break;
        }
    }
}