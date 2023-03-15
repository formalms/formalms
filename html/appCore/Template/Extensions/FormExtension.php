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

namespace FormaLms\appCore\Template\Extensions;

require_once _base_ . '/lib/lib.form.php';

use Twig\TwigFunction;

class FormExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('Form_openForm', [\Form::class, 'openForm'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getFormHeader', [\Form::class, 'getFormHeader'], ['is_safe' => ['html']]),
            new TwigFunction('Form_openElementSpace', [\Form::class, 'openElementSpace'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getTextLabel', [\Form::class, 'getTextLabel'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getTextBox', [\Form::class, 'getTextBox'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLineBox', [\Form::class, 'getLineBox'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getHidden', [\Form::class, 'getHidden'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputTimeSelectorField', [\Form::class, 'getInputTimeSelectorField'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLineTimeSelectorField', [\Form::class, 'getLineTimeSelectorField'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputTextfield', [\Form::class, 'getInputTextfield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getSearchInputTextfield', [\Form::class, 'getSearchInputTextfield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLineTextfield', [\Form::class, 'getLineTextfield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getTextfield', [\Form::class, 'getTextfield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_loadDatefieldScript', [\Form::class, 'loadDatefieldScript'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputDatetimefield', [\Form::class, 'getInputDatetimefield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLineDatetimefield', [\Form::class, 'getLineDatetimefield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getDatetimefield', [\Form::class, 'getDatetimefield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputDatefield', [\Form::class, 'getInputDatefield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLineDatefield', [\Form::class, 'getLineDatefield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getDatefield', [\Form::class, 'getDatefield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputPassword', [\Form::class, 'getInputPassword'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLinePassword', [\Form::class, 'getLinePassword'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getPassword', [\Form::class, 'getPassword'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputFilefield', [\Form::class, 'getInputFilefield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLineFilefield', [\Form::class, 'getLineFilefield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getFilefield', [\Form::class, 'getFilefield'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getExtendedFileField', [\Form::class, 'getExtendedFileField'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputDropdown', [\Form::class, 'getInputDropdown'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputLabelDropdown', [\Form::class, 'getInputLabelDropdown'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLineDropdown', [\Form::class, 'getLineDropdown'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getDropdown', [\Form::class, 'getDropdown'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputListbox', [\Form::class, 'getInputListbox'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLineListbox', [\Form::class, 'getLineListbox'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getListbox', [\Form::class, 'getListbox'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputCheckbox', [\Form::class, 'getInputCheckbox'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLineCheckbox', [\Form::class, 'getLineCheckbox'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getCheckbox', [\Form::class, 'getCheckbox'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getCheckboxSet', [\Form::class, 'getCheckboxSet'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputRadio', [\Form::class, 'getInputRadio'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLineRadio', [\Form::class, 'getLineRadio'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getRadio', [\Form::class, 'getRadio'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getRadioSet', [\Form::class, 'getRadioSet'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getRadioHoriz', [\Form::class, 'getRadioHoriz'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getOpenCombo', [\Form::class, 'getOpenCombo'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getCloseCombo', [\Form::class, 'getCloseCombo'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getOpenFieldset', [\Form::class, 'getOpenFieldset'], ['is_safe' => ['html']]),
            new TwigFunction('Form_openCollasableFieldset', [\Form::class, 'openCollasableFieldset'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getCloseFieldset', [\Form::class, 'getCloseFieldset'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getTextarea', [\Form::class, 'getTextarea'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputTextarea', [\Form::class, 'getInputTextarea'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getSimpleTextarea', [\Form::class, 'getSimpleTextarea'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getSimpleText', [\Form::class, 'getSimpleText'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getBreakRow', [\Form::class, 'getBreakRow'], ['is_safe' => ['html']]),
            new TwigFunction('Form_openFormLine', [\Form::class, 'openFormLine'], ['is_safe' => ['html']]),
            new TwigFunction('Form_closeFormLine', [\Form::class, 'closeFormLine'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getLabel', [\Form::class, 'getLabel'], ['is_safe' => ['html']]),
            new TwigFunction('Form_closeElementSpace', [\Form::class, 'closeElementSpace'], ['is_safe' => ['html']]),
            new TwigFunction('Form_openButtonSpace', [\Form::class, 'openButtonSpace'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getReset', [\Form::class, 'getReset'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getButton', [\Form::class, 'getButton'], ['is_safe' => ['html']]),
            new TwigFunction('Form_getInputButton', [\Form::class, 'getInputButton'], ['is_safe' => ['html']]),
            new TwigFunction('Form_closeButtonSpace', [\Form::class, 'closeButtonSpace'], ['is_safe' => ['html']]),
            new TwigFunction('Form_closeForm', [\Form::class, 'closeForm'], ['is_safe' => ['html']]),
        ];
    }
}
