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

class DashboardBlockForm
{
    public const FORM_TYPE_TEXT = 'text';
    public const FORM_TYPE_NUMBER = 'number';
    public const FORM_TYPE_IMAGE = 'image';
    public const FORM_TYPE_FILE = 'file';
    public const FORM_TYPE_TEXTAREA = 'textarea';
    public const FORM_TYPE_CHECKBOX = 'checkbox';
    public const FORM_TYPE_RADIO = 'radio';
    public const FORM_TYPE_URL = 'url';
    public const FORM_TYPE_SELECT = 'select';

    public const ALLOWED_FORM_TYPES = [self::FORM_TYPE_TEXT, self::FORM_TYPE_NUMBER, self::FORM_TYPE_IMAGE, self::FORM_TYPE_FILE, self::FORM_TYPE_TEXTAREA, self::FORM_TYPE_CHECKBOX, self::FORM_TYPE_RADIO, self::FORM_TYPE_URL, self::FORM_TYPE_SELECT];

    public static function getFormItem($block, $name, $type, $required = false, $values = [], $attr = [])
    {
        if (!in_array($type, self::ALLOWED_FORM_TYPES)) {
            return false;
        }

        return new DashboardBlockFormItem($name, get_class($block), $required, $type, $values, $attr);
    }

    public static function validate($block, $data)
    {
        $errors = [];
        /** @var DashboardBlockLms $blockObj */
        $blockObj = new $block($data);

        if ($blockObj->isFirstInsert()) {
            return $errors;
        }

        $blockForm = $blockObj->getForm();

        /** @var DashboardBlockFormItem $formItem */
        foreach ($blockForm as $formItem) {
            if (!array_key_exists($formItem->getName(), $blockObj->getData()) && $formItem->isRequired()) {
                $errors[] = [
                    'field' => $formItem->getField(),
                    'error' => sprintf(Lang::t('_VALUE_IS_REQUIRED', 'dashboardsetting'), $formItem->getField()),
                ];
            } elseif (array_key_exists($formItem->getName(), $blockObj->getData())) {
                $value = $blockObj->getData()[$formItem->getName()];

                switch ($formItem->getType()) {
                    case DashboardBlockForm::FORM_TYPE_SELECT:
                    case DashboardBlockForm::FORM_TYPE_RADIO:
                        if (!in_array($value, array_keys($formItem->getValues()))) {
                            $errors[] = [
                                'field' => $formItem->getField(),
                                'error' => sprintf(Lang::t('_VALUE_IS_NOT_VALID', 'dashboardsetting'), $value),
                            ];
                        }
                        break;
                    case DashboardBlockForm::FORM_TYPE_URL:
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            $errors[] = [
                                'field' => $formItem->getField(),
                                'error' => sprintf(Lang::t('_VALUE_IS_NOT_VALID', 'dashboardsetting'), $value),
                            ];
                        }
                        break;
                    case DashboardBlockForm::FORM_TYPE_CHECKBOX:
                    case DashboardBlockForm::FORM_TYPE_FILE:
                    case DashboardBlockForm::FORM_TYPE_IMAGE:
                        break;
                    case DashboardBlockForm::FORM_TYPE_NUMBER:
                        if (!is_numeric($value)) {
                            $errors[] = [
                                'field' => $formItem->getField(),
                                'error' => sprintf(Lang::t('_VALUE_IS_NOT_VALID', 'dashboardsetting'), $value),
                            ];
                        }
                        break;
                    case DashboardBlockForm::FORM_TYPE_TEXT:
                    case DashboardBlockForm::FORM_TYPE_TEXTAREA:
                    default:
                        break;
                }
            }
        }

        return $errors;
    }

    public static function fieldExist($block, $field)
    {
        /** @var DashboardBlockLms $blockObj */
        $blockObj = new $block([]);

        $blockForm = $blockObj->getForm();

        /** @var DashboardBlockFormItem $formItem */
        foreach ($blockForm as $formItem) {
            if ($formItem->getField() === $field) {
                return true;
            }
        }

        return false;
    }

    public static function getFieldName($block, $field)
    {
        /** @var DashboardBlockLms $blockObj */
        $blockObj = new $block([]);

        $blockForm = $blockObj->getForm();

        /** @var DashboardBlockFormItem $formItem */
        foreach ($blockForm as $formItem) {
            if ($formItem->getField() === $field) {
                return $formItem->getName();
            }
        }

        return false;
    }
}
