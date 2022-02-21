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

/**
 * Extend Selector class.
 *
 * @author   Giovanni Derks <virtualdarkness[AT]gmail-com>
 *
 * @version  $Id: lib.selextend.php 113 2006-03-08 18:08:42Z ema $
 */
class ExtendSelector
{
    /** extra selectors */
    public $extra_sel = [];

    /** selected items */
    public $selected_items = [];

    /** selected items */
    public $database_items = [];

    /** printed items */
    public $printed_items = [];

    /**
     * ExtendSelector constructor.
     */
    public function ExtendSelector()
    {
    }

    /**
     * @param $arr array with the extra field information
     * Sets the extra_sel array with the one provided by the user
     */
    public function setExtraSel($arr)
    {
        $this->extra_sel = $arr;
    }

    /**
     * @param $arr array with the printed items information
     * Sets the printed_items array with the one provided by the user
     */
    public function setPrintedItems($arr)
    {
        $this->printed_items = $arr;
    }

    /**
     * @param $arr array
     * @param $pfx string
     */
    public function grabSelectedItems($arr = false, $pfx = 'selector')
    {
        if ($arr === false) {
            $arr = $_POST;
        }

        $valid_keys = array_keys($this->extra_sel);
        //foreach($arr as $key=>$val) {
        //	if (in_array($key, $valid_keys)) {
        foreach ($valid_keys as $key => $val) {
            if (isset($arr[$val])) {
                $this->selected_items[$val] = array_keys($arr[$val]);
            }

            //}
        }

        if (isset($_POST[$pfx . '_selected_items'])) {
            $old_sel = Util::unserialize(urldecode($_POST[$pfx . '_selected_items']));
        } else {
            $old_sel = false;
        }
        foreach ($valid_keys as $key => $val) {
            if ((!isset($this->printed_items)) || (!is_array($this->printed_items))) {
                $this->printed_items = [];
            }
            if ((isset($old_sel[$val])) && (is_array($old_sel[$val]))) {
                $old_sel[$val] = array_diff($old_sel[$val], $this->printed_items);
            }
        }

        if (is_array($old_sel)) {
            foreach ($valid_keys as $key => $val) {
                if ((!isset($this->selected_items[$val])) || (!is_array($this->selected_items[$val]))) {
                    $this->selected_items[$val] = [];
                }
                if ((!isset($old_sel[$val])) || (!is_array($old_sel[$val]))) {
                    $old_sel[$val] = [];
                }
                $this->selected_items[$val] = array_unique(array_merge($this->selected_items[$val], $old_sel[$val]));
            }
            //$arr=$old_sel;
        }

        if (isset($_POST[$pfx . '_database_items'])) {
            $this->database_items = $this->getDatabaseItemsFromVar($_POST[$pfx . '_database_items']);
        } else {
            $this->database_items = [];
        }
    }

    public function setSelectedItems($arr = false)
    {
        if ($arr === false) {
            $arr = $_POST;
        }

        $valid_keys = array_keys($this->extra_sel);

        foreach ($valid_keys as $key => $val) {
            if (isset($arr[$val])) {
                $this->selected_items[$val] = array_keys($arr[$val]);
            }
        }
    }

    /**
     * @return array
     */
    public function getSelectedItems()
    {
        //print_r($this->selected_items);
        if (count($this->selected_items) <= 0) {
            $this->grabSelectedItems();
        }
        //print_r($this->selected_items);
        return $this->selected_items;
    }

    /**
     * @param $arr array
     */
    public function setDatabaseItems($arr)
    {
        $this->database_items = $arr;
    }

    public function getDatabaseItemsFromVar($serialized_var = false, $pfx = 'selector')
    {
        if ($serialized_var === false) {
            $serialized_var = $_POST[$pfx . '_database_items'];
        }

        if (isset($serialized_var)) {
            return Util::unserialize(urldecode($serialized_var));
        } else {
            return [];
        }
    }

    public function extendListRow($row_id)
    {
        $res = [];
        foreach ($this->extra_sel as $key => $val) {
            if ((isset($this->selected_items[$key])) && (is_array($this->selected_items[$key]))) {
                $chk = (in_array($row_id, $this->selected_items[$key]) ? true : false);
            } else {
                $chk = false;
            }
            $res['col_' . $key] = Form::getCheckbox('', $key . '_' . $row_id . '_', $key . '[' . $row_id . ']', 1, $chk);
        }

        return $res;
    }

    public function extendListHeader()
    {
        $res = [];
        foreach ($this->extra_sel as $key => $val) {
            $alt = $val['alt'];
            if (isset($val['title'])) {
                $title = $val['title'];
            } else {
                $title = $alt;
            }

            $res['col_' . $key] = '<img src="' . $val['img'] . '" alt="' . $alt . '" title="' . $title . '" />';
        }

        return $res;
    }

    public function extendGroupListRow()
    {
    }

    public function extendOrgChartListRow()
    {
    }

    /**
     * @param $pfx string the prefix for the id/name of the hidden field
     *
     * @return string the html code of the hidden field containing the serialized and urlencoded values
     *                of the selected items array. This function does what the printState does for normal selector
     */
    public function writeSelectedInfo($pfx = 'selector')
    { //serialize
        require_once _base_ . '/lib/lib.form.php';
        $form = new Form();
        $res = '';
        $res .= $form->getHidden($pfx . '_selected_items', $pfx . '_selected_items', urlencode(Util::serialize($this->selected_items)));
        $res .= $form->getHidden($pfx . '_database_items', $pfx . '_database_items', urlencode(Util::serialize($this->database_items)));

        return $res;
    }
}
