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
 * @version  $Id:$
 */
class FormaCalEvent_core
{
    public $id;

    public $calEventClass;
    public $editable;
    public $create;
    public $start_year;
    public $start_month;
    public $start_day;
    public $start_hour;
    public $start_min;
    public $start_sec;

    public $end_year;
    public $end_month;
    public $end_day;
    public $end_hour;
    public $end_min;
    public $end_sec;

    public $title;
    public $description;
    public $category;
    public $type;
    public $private;
    public $visibility_rules;

    public $_year;
    public $_month;
    public $_day;
    public $_owner;

    public function assignVar()
    {
        $this->id = importVar('id');
        $this->calEventClass = importVar('calEventClass');
        $this->start_year = importVar('start_year');
        $this->start_month = importVar('start_month');
        $this->start_day = importVar('start_day');

        $this->_year = $this->start_year;
        $this->_month = $this->start_month;
        $this->_day = $this->start_day;

        $this->start_hour = importVar('start_hour');
        $this->start_min = importVar('start_min');
        $this->start_sec = importVar('start_sec');
        $this->end_year = importVar('end_year');
        $this->end_month = importVar('end_month');
        $this->end_day = importVar('end_day');
        $this->end_hour = importVar('end_hour');
        $this->end_min = importVar('end_min');
        $this->end_sec = importVar('end_sec');

        $this->title = importVar('title');
        $this->description = importVar('description');

        $this->_owner = importVar('_owner');
        if (!$this->_owner) {
            $this->_owner == Forma::user()->getIdSt();
        }

        $this->category = importVar('category');
        $this->private = importVar('private');
    }

    public function getForm()
    {
        $form_obj = '{
		"form":[
			{"type":"structure","value":"row","permissions":"2"},
			{"type":"structure","value":"cell","field_class":"label","permissions":"2"},
			{"type":"label","value":"_PRIVATE","permissions":"2"},
			{"type":"structure","value":"/cell","permissions":"2"},
			{"type":"structure","value":"cell","field_class":"field","permissions":"2"},
			{"type":"checkbox","id":"private","permissions":"2","defvalue":"on"},
			{"type":"structure","value":"/cell","permissions":"2"},
			{"type":"structure","value":"/row","permissions":"2"},
			{"type":"structure","value":"row"},
			{"type":"structure","value":"cell","field_class":"label"},
			{"type":"label","value":"_START"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"cell","field_class":"field"},
			{"type":"day","id":"start_day"},
			{"type":"string","value":"/"},
			{"type":"month","id":"start_month"},
			{"type":"string","value":"/"},
			{"type":"year","id":"start_year"},
			{"type":"string","value":"&nbsp;"},
			{"type":"hour","id":"start_hour"},
			{"type":"string","value":":"},
			{"type":"min","id":"start_min"},
			{"type":"string","value":":"},
			{"type":"sec","id":"start_sec"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"/row"},
			{"type":"structure","value":"row"},
			{"type":"structure","value":"cell","field_class":"label"},
			{"type":"label","value":"_END"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"cell","field_class":"field"},
			{"type":"day","id":"end_day"},
			{"type":"string","value":"/"},
			{"type":"month","id":"end_month"},
			{"type":"string","value":"/"},
			{"type":"year","id":"end_year"},
			{"type":"string","value":"&nbsp;"},
			{"type":"hour","id":"end_hour"},
			{"type":"string","value":":"},
			{"type":"min","id":"end_min"},
			{"type":"string","value":":"},
			{"type":"sec","id":"end_sec"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"/row"},
			{"type":"structure","value":"row"},
			{"type":"structure","value":"cell","field_class":"label"},
			{"type":"label","value":"_CATEGORY"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"cell","field_class":"field"},	{"type":"select","id":"category","value":["_GENERIC","_VIDEOCONFERENCE","_MEETING","_CHAT","_PUBLISHING","_ASSESSMENT"],"translatevalue":"1"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"/row"},
			{"type":"structure","value":"row"},
			{"type":"structure","value":"cell","field_class":"label"},
			{"type":"label","value":"_SUBJECT"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"cell","field_class":"field"},
			{"type":"text","id":"title","style":"width:300px"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"/row"},
			{"type":"structure","value":"row"},
			{"type":"structure","value":"cell","field_class":"label"},
			{"type":"label","value":"_DESCR"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"cell","field_class":"field"},
			{"type":"textarea","id":"description"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"/row"}
		]

		}';

        return $form_obj;
    }

    public function store()
    {
        if ($this->getPerm()) {
            $start_date = $this->start_year . '-' . $this->start_month . '-' . $this->start_day . ' ' . $this->start_hour . ':' . $this->start_min . ':' . $this->start_sec;

            $end_date = $this->end_year . '-' . $this->end_month . '-' . $this->end_day . ' ' . $this->end_hour . ':' . $this->end_min . ':' . $this->end_sec;

            if (!$this->id) {
                $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_calendar SET create_date=NOW(),';
            } else {
                $query = 'UPDATE ' . $GLOBALS['prefix_fw'] . '_calendar SET ';
            }

            $query .= "class='" . $this->calEventClass . "',";
            $query .= "start_date='" . $start_date . "',";
            $query .= "end_date='" . $end_date . "',";
            $query .= "title='" . $this->title . "',";
            $query .= "description='" . $this->description . "',";
            $query .= "category='" . $this->category . "',";
            $query .= "type='" . $this->type . "',";
            $query .= "private='" . $this->private . "',";
            $query .= "visibility_rules='" . $this->visibility_rules . "',";
            $query .= "_year='" . $this->_year . "',";
            $query .= "_month='" . $this->_month . "',";
            $query .= "_day='" . $this->_day . "',";
            $query .= "_owner='" . $this->_owner . "'";

            if ($this->id) {
                $query .= " WHERE id='" . $this->id . "'";
            }

            $result = sql_query($query);
            if (sql_error()) {
                exit(sql_error() . '<br />' . $query);
            }

            if (!$this->id) {
                $this->id = sql_insert_id();
            }

            return $this->id;
        } else {
            return 0;
        }
    }

    public function del()
    {
        if ($this->getPerm()) {
            $query = 'DELETE FROM ' . $GLOBALS['prefix_fw'] . "_calendar WHERE id='" . $this->id . "'";
            $result = sql_query($query);
        }
    }

    public function getOwner()
    {
        $query = 'SELECT _owner FROM ' . $GLOBALS['prefix_fw'] . "_calendar WHERE id='" . $this->id . "'";
        $result = sql_query($query);
        $row = sql_fetch_array($result);

        return $row['_owner'];
    }

    public function getPerm()
    {
        /* you should override this method according to your class extension criteria */

        return 1;
    }
}
