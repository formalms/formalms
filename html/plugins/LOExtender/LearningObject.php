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
            array('label' => 'Prova Tutti lo', 'param_name' => 'prova_tutti_lo')
        ));

        switch($lo->getObjectType()) {
            case 'faq':
                $params = array_merge($params, array(
                    array('label' => 'Prova Faq', 'param_name' => 'prova_faq')
                ));
                break;
            case 'scormorg':
                $params = array_merge($params, array(
                    array('label' => 'Prova ScormOrg 1', 'param_name' => 'prova_scormorg_1')
                ));
                break;
            case 'htmlpage':
                $params = array_merge($params, array(
                    array('label' => \Lang::t('_DESCRIPTION'), 'param_name' => 'desc2')
                ));
                break;
        }
    }
    
    public function renderCustomSettings($lo, $values, &$out) {

        $out .= \Form::getTextfield('Prova Tutti LO', 'prova_tutti_lo', 'prova_tutti_lo', 255, isset($values['prova_tutti_lo']) ? $values['prova_tutti_lo'] : '');

        switch($lo->getObjectType()) {
            case 'faq':
                $out .= \Form::getTextfield('Prova faq', 'prova_faq', 'prova_faq', 255, isset($values['prova_faq']) ? $values['prova_faq'] : '');
                //$out .= \Form::getTextfield('Prova ScormOrg 2', 'prova_tutti_2', 'prova_scormorg_2', 255, isset($values['prova_scormorg_2']) ? $values['prova_scormorg_2'] : '');
                break;
            case 'scormorg':
                $out .= \Form::getTextfield('Prova ScormOrg 1', 'prova_tutti_1', 'prova_scormorg_1', 255, isset($values['prova_scormorg_1']) ? $values['prova_scormorg_1'] : '');
                //$out .= \Form::getTextfield('Prova ScormOrg 2', 'prova_tutti_2', 'prova_scormorg_2', 255, isset($values['prova_scormorg_2']) ? $values['prova_scormorg_2'] : '');
                break;
            case 'htmlpage':
                $out .= \Form::getTextfield(\Lang::t('_DESCRIPTION'), 'desc2', 'desc2', 255, isset($values['desc2']) ? $values['desc2'] : '');
                //$out .= \Form::getTextfield('Prova HtmlPage 2', 'prova_htmlpage_2', 'prova_htmlpage_2', 255, isset($values['prova_htmlpage_2']) ? $values['prova_htmlpage_2'] : '');
                break;
        }
    }
}