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

/* priority */
define('EVENT_PRIORITY_LOW', 16);
define('EVENT_PRIORITY_NORMAL', 128);
define('EVENT_PRIORITY_HIGH', 240);

/* lifetime of database event property */
define('PROPERTY_LIFETIME', 24 * 7);
define('PROPERTY_CASUALTIES', 50);

/**
 * @version  $Id: lib.event.php 324 2006-05-11 09:17:35Z fabio $
 *
 * @author   Emanuele Sandri <esandri@docebo.com>
 *
 * This is the class for ClassEvents in Docebo
 **/
class DoceboEventClass
{
    /** @var int the unique id of the event class
     *	@internal
     **/
    public $class_id = false;

    /** @var string the name of the event class
     * 	@internal
     **/
    public $class_name;

    /**
     * DoceboEventClass constructor.
     *
     * @param mixed $class_ref the reference to the class, the id or the name
     *                         - if is int $class_ref is used as class_id
     *                         - if is string is used as name
     **/
    public function __construct($class_ref)
    {
        if (is_int($class_ref)) {
            $this->class_id = $class_ref;
            $this->class_name = $this->getClassName($class_ref);
        } else {
            $this->class_name = $class_ref;
            $this->class_id = $this->getClassId($class_ref);
        }
    }

    /**
     * get the id of an event class from name.
     *
     * @param string $class_name the name of event class
     * @param mixed the id of the class or FALSE if it's was not found
     * @static
     **/
    public function getClassId($class_name)
    {
        $query = 'SELECT idClass FROM ' . $GLOBALS['prefix_fw'] . '_event_class'
                . " WHERE class = '" . $class_name . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 0) {
            return false;
        }
        list($class_id) = sql_fetch_row($rs);

        return $class_id;
    }

    /**
     * get the name of an event class from id.
     *
     * @param int $class_id the name of event class
     * @param mixed the name of the class or FALSE if it's was not found
     * @static
     **/
    public function getClassName($class_id)
    {
        $query = 'SELECT class FROM ' . $GLOBALS['prefix_fw'] . '_event_class'
                . " WHERE idClass = '" . $class_id . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 0) {
            return false;
        }
        list($class_name) = sql_fetch_row($rs);

        return $class_name;
    }

    /**
     * Create a new event based on this class.
     *
     * @param int    $class_id    the class of the event
     * @param string $module      the module of the event
     * @param string $section     the section of the event
     * @param int    $priority    the priority of the event
     * @param string $description the description of the event
     *
     * @return DoceboEvent $event the event object
     **/
    public function &createEvent($module, $section, $priority, $description)
    {
        $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_event'
                . ' (idClass,module,section,priority,description)'
                . ' VALUES ('
                . "'" . (int) $this->class_id . "',"
                . "'" . $module . "',"
                . "'" . $section . "',"
                . "'" . (int) $priority . "',"
                . "'" . $description . "'"
                . ')';
        $result = sql_query($query);
        if ($result) {
            $event_istance = new DoceboEvent(sql_insert_id());

            return $event_istance;
        } else {
            $false_var = false;

            return $false_var;
        }
    }
}

/**
 * @version  $Id: lib.event.php 324 2006-05-11 09:17:35Z fabio $
 *
 * @category Event
 *
 * @author   Emanuele Sandri <esandri@docebo.com>
 *
 * This is the base class for Events in Docebo.
 */
class DoceboEvent
{
    /** @var int the unique id of the event
     **/
    public $event_id = false;

    /** @var int the unique id of the class of the event
     **/
    public $class_id = false;

    /** @var string the module of the event
     **/
    public $module = false;

    /** @var string the section of the event
     **/
    public $section = false;

    /** @var int the priority of the event
     **/
    public $priority = false;

    /** @var string the priority of the event
     **/
    public $description = false;

    /**
     * Constructor of DoceboEvent object.
     *
     * @param int $event_id the unique id of the event
     **/
    public function DoceboEvent($event_id)
    {
        $this->event_id = $event_id;

        $query = 'SELECT idClass, module, section, priority, description '
                . '  FROM ' . $GLOBALS['prefix_fw'] . '_event'
                . " WHERE idEvent = '" . $event_id . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 0) {
            return false;
        }
        list($this->class_id, $this->module, $this->section,
                $this->priority, $this->description) = sql_fetch_row($rs);
    }

    /**
     * Return the id of the event.
     *
     * @return int the id of the event
     **/
    public function getEventId()
    {
        return $this->event_id;
    }

    /**
     * Return the class of the event.
     *
     * @return string The class of the event
     **/
    public function getClassId()
    {
        return $this->class_id;
    }

    /**
     * Return the class of the event.
     *
     * @return string The class of the event
     **/
    public function getClassName()
    {
        return DoceboEventClass::getClassName($this->class_id);
    }

    /**
     * Return module of the event.
     *
     * @return string module of the event
     **/
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Return section of the event.
     *
     * @return string section of the event
     **/
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Return priority of the event.
     *
     * @return int priority of the event
     **/
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Return description of the event.
     *
     * @return string description of the event
     **/
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Verify if a property exist.
     *
     * @param string $prop_name the unique name of the property in the event
     *
     * @return bool TRUE if exist, FALSE otherwise
     **/
    public function getProperty($prop_name)
    {
        $query = 'SELECT property_value FROM ' . $GLOBALS['prefix_fw'] . '_event_property'
                . " WHERE property_name = '" . $prop_name . "'"
                . "   AND idEvent = '" . (int) $this->event_id . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) > 0) {
            list($result) = sql_fetch_row($rs);

            return $result;
        } else {
            return false;
        }
    }

    /**
     * Get a property value for an event.
     *
     * @param string $prop_name the unique name of the property in the event
     *
     * @return string the value of the property or FALSE if not found or error
     **/
    public function existProperty($prop_name)
    {
        $query = 'SELECT property_name FROM ' . $GLOBALS['prefix_fw'] . '_event_property'
                . " WHERE property_name = '" . $prop_name . "'"
                . "   AND idEvent = '" . (int) $this->event_id . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set a property for an event.
     *
     * @param string $prop_name  the unique name of the property in the event
     * @param string $prop_value the value of the property
     *
     * @return bool TRUE for success, FALSE otherwise
     **/
    public function setProperty($prop_name, $prop_value)
    {
        if ($this->existProperty($prop_name)) {
            $query = 'UPDATE ' . $GLOBALS['prefix_fw'] . '_event_property'
                    . "   SET property_value = '" . $prop_value . "'"
                    . " WHERE property_name = '" . $prop_name . "'"
                    . "   AND idEvent = '" . (int) $this->event_id . "'";
        } else {
            $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_event_property'
                    . ' (idEvent, property_name, property_value, property_date) VALUES ('
                    . "'" . (int) $this->event_id . "','" . $prop_name . "','" . $prop_value . "', '" . date('Y-m-d') . "')";
        }
        $result = sql_query($query);

        return $result;
    }

    public function deleteOldProperty($force = false)
    {
        if (mt_rand(1, PROPERTY_CASUALTIES) == 1 || $force === true) {
            $query = 'DELETE FROM ' . $GLOBALS['prefix_fw'] . '_event_property ' .
                    "WHERE property_date < '" . date('Y-m-d', time() - PROPERTY_LIFETIME * 3600) . "'";
            sql_query($query);
        }
    }
}

/**
 * @version  $Id: lib.event.php 324 2006-05-11 09:17:35Z fabio $
 *
 * @category Event
 *
 * @author   Emanuele Sandri <esandri@docebo.com>
 *
 * This is the base class for Consumer Events in Docebo.
 **/
class DoceboEventConsumer
{
    /** @var int the unique id of the consumer
     *	@internal
     **/
    public $consumer_id = null;

    /** @var string the PHP class of the consumer
     *	@internal
     **/
    public $consumer_class = false;

    /** @var string the PHP file that contains the declaration
     *								of the consumer class
     *
     *	@internal
     **/
    public $consumer_file = false;

    /**
     * DoceboEventConsumer constructor.
     *
     * @param mixed $consumer_ref the reference to the consumer, the id or the name
     *                            - if is int $consumer_ref is used as consumer_id
     *                            - if is string is used as consumer_class
     **/
    public function DoceboEventConsumer($class_ref)
    {
        $query = 'SELECT idConsumer, consumer_class, consumer_file '
                . '  FROM ' . $GLOBALS['prefix_fw'] . '_event_consumer';
        if (is_int($class_ref)) {
            $query .= " WHERE idConsumer = '" . (int) $class_ref . "'";
        } else {
            $query .= " WHERE consumer_class = '" . $class_ref . "'";
        }
        $rs = sql_query($query);
        if (sql_num_rows($rs) > 0) {
            list($this->consumer_id,
                    $this->consumer_class,
                    $this->consumer_file) = sql_fetch_row($rs);
        }
    }

    /**
     * get the id of a consumer from PHP class name.
     *
     * @param string $consumer_class (optional) the PHP class name of the consumer
     *
     * @return mixed the id of the consumer or FALSE if it wasn't found
     *               This method is static if $consumer_class is passed
     **/
    public function getConsumerId($consumer_class = false)
    {
        if ($consumer_class == false) {
            return $this->consumer_id;
        }
        $query = 'SELECT idConsumer FROM ' . $GLOBALS['prefix_fw'] . '_event_consumer'
                . " WHERE consumer_class = '" . $consumer_class . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 0) {
            return false;
        }
        list($consumer_id) = sql_fetch_row($rs);

        return $consumer_id;
    }

    /**
     * get the PHP class of a consumer from id.
     *
     * @param int $consumer_id (optional) the id of the consumer
     *
     * @return mixed the PHP class of the consumer or FALSE if it wasn't found
     *               This method is static if $consumer_id is passed
     **/
    public function getConsumerName($consumer_id)
    {
        if ($consumer_id == false) {
            return $this->consumer_class;
        }
        $query = 'SELECT consumer_class FROM ' . $GLOBALS['prefix_fw'] . '_event_consumer'
                . " WHERE idConsumer = '" . $consumer_id . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 0) {
            return false;
        }
        list($consumer_class) = sql_fetch_row($rs);

        return $consumer_class;
    }

    /**
     * get the PHP file of a consumer from id.
     *
     * @param int $consumer_id (optional) the id of the consumer
     *
     * @return mixed the PHP file of the consumer or FALSE if it wasn't found
     *               This method is static if $consumer_id is passed
     **/
    public function getConsumerFile($consumer_id)
    {
        if ($consumer_id == false) {
            return $this->consumer_file;
        }
        $query = 'SELECT consumer_file FROM ' . $GLOBALS['prefix_fw'] . '_event_consumer'
                . " WHERE idConsumer = '" . $consumer_id . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 0) {
            return false;
        }
        list($consumer_file) = sql_fetch_row($rs);

        return $consumer_file;
    }

    /** get the PHP class and file of the consumer from id.
     * @param int $consumer_id (optional) the id of the consumer
     *
     * @return mixed an array with 0 => class and 1 => file of the consumer
     *               or FALSE if it wasn't found
     *
     *	This method is static if $consumer_id is passed
     **/
    public function getConsumerClassFile($consumer_id)
    {
        if ($consumer_id == false) {
            return [$this->consumer_class, $this->consumer_file];
        }
        $query = 'SELECT consumer_class, consumer_file FROM ' . $GLOBALS['prefix_fw'] . '_event_consumer'
                . " WHERE idConsumer = '" . $consumer_id . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 0) {
            return false;
        }

        return sql_fetch_row($rs);
    }

    /**
     * return the name of the consumer.
     *
     * @return string the name of the consumer
     * @abstract
     **/
    public function _getCunsumerName()
    {
        return 'DoceboEventConsumer';
    }

    /**
     * put an event to the consumer.
     *
     * @param DoceboEvent &$event the event to be consumed
     *
     * @return true inform the events manager to continue the dispatch of this
     *              events to all others consumer
     *              FALSE say that the dispatch of this event must be interrupted
     * @abstract
     **/
    public function actionEvent(&$event)
    {
        if (Get::sett('do_debug') == 'on') {
            $log_event = $event->getProperty('_event_log');
            if ($log_event === false) {
                $log_event = '';
            } else {
                $log_event .= '; ';
            }
            $log_event .= 'processed - ' . $this->_getCunsumerName()
                        . ' - ' . date('Y-m-d');
            $event->setProperty('_event_log', $log_event);
        }
    }
}
