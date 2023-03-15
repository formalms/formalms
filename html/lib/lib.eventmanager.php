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
 * @version  $Id: lib.eventmanager.php 955 2007-02-03 15:19:40Z fabio $
 *
 * @author   Emanuele Sandri <esandri@docebo.com>
 */
require_once _base_ . '/lib/lib.event.php';

class DoceboEventManager
{
    /**
     * Register a new event class.
     *
     * @param string $class_name the name of the class
     *
     * @return int idClass of the registered class, FALSE otherwise
     * @static
     **/
    public function registerEventClass($class_name)
    {
        $class_id = DoceboEventClass::getClassId($class_name);
        if ($class_id !== false) {
            return $class_id;
        } else {
            $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_event_class'
                    . " (name) VALUES ('" . $class_name . "')";
            $result = sql_query($query);
            if ($result) {
                return sql_insert_id();
            } else {
                return false;
            }
        }
    }

    /**
     * Create a new event based on this class.
     *
     * @param string $class_name  the class of the event
     * @param string $module      the module of the event
     * @param string $section     the section of the event
     * @param int    $priority    the priority of the event
     * @param string $description the description of the event
     *
     * @return DoceboEvent $event the event object
     * @static
     *
     * @internal if you pass an int $class_id in the first parameter you can
     *				create a new event from the class_id specified
     **/
    public function &newEvent($class_name, $module, $section, $priority, $description)
    {
        $class = new DoceboEventClass($class_name);
        $istance = $class->createEvent($module, $section, $priority, $description);

        return $istance;
    }

    /**
     * Register a new consumer for a class of events.
     *
     * @param mixed  $class_name     the name of the class to be associated to this consumer
     *                               if this parameter is an array the consumer will be
     *                               related to all event class identified by the array items
     * @param string $consumer_class the PHP class of the consumer
     * @param string $consumer_file  the PHP file to be included before the $consumer_class
     *                               instantiation
     *
     * @return bool TRUE on success, FALSE otherwise
     * @static
     *
     * @internal if you pass an int $class_id or an array of int in the first parameter
     *				you can relate the consumer to these class ids
     **/
    public function registerEventConsumer($class_name, $consumer_class, $consumer_file)
    {
        $idConsumer = DoceboEventManager::_registerConsumer($consumer_class, $consumer_file);
        if ($idConsumer === false) {
            return false;
        }
        if (is_array($class_name)) {
            foreach ($class_name as $cn) {
                $class_id = DoceboEventClass::getClassId($cn);
                if ($class_id !== false) {
                    DoceboEventManager::_makeConsumerClassRelation($idConsumer, $class_id);
                } else {
                    return false;
                }
            }
        } else {
            $class_id = DoceboEventClass::getClassId($class_name);
            if ($class_id !== false) {
                DoceboEventManager::_makeConsumerClassRelation($idConsumer, $class_id);
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Make association from consumer and class.
     *
     * @param int $consumer_id the id of the consumer
     * @param int $class_id    the id of the event class
     * @static
     **/
    public function _makeConsumerClassRelation($consumer_id, $class_id)
    {
        $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_event_consumer_class'
                . ' (idConsumer, idClass) VALUES '
                . " ('" . (int) $consumer_id . "','" . (int) $class_id . "')";
        sql_query($query);
    }

    /**
     * Register a new consumer.
     *
     * @param string $consumer_class
     * @param string $consumer_file
     *
     * @return int the id of the registered consumer, FALSE if error
     * @static
     **/
    public function _registerConsumer($consumer_class, $consumer_file)
    {
        $consumer_id = DoceboEventConsumer::getConsumerId($consumer_class);
        if ($consumer_id !== false) {
            return $consumer_id;
        } else {
            $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_event_consumer'
                    . ' (consumer_class, consumer_file) VALUES '
                    . " ('" . $consumer_class . "','" . $consumer_file . "')";
            $result = sql_query($query);
            if ($result) {
                return sql_insert_id();
            } else {
                return false;
            }
        }
    }

    /**
     * return the array of consumers realted to a given event class.
     *
     * @param int $class_id the id of the event class
     *
     * @return array an array of the consumer related to $class_id
     *               any element of the array is
     *               consumer_id => array( consumer_class, consumer_file )
     * @static
     **/
    public function listConsumerFromClassId($class_id)
    {
        $query = 'SELECT DISTINCT ev.idConsumer, ev.consumer_class, ev.consumer_file '
                . '  FROM ' . $GLOBALS['prefix_fw'] . '_event_consumer AS ev'
                . '  JOIN ' . $GLOBALS['prefix_fw'] . '_event_consumer_class AS ecc'
                . " WHERE ecc.idClass = '" . $class_id . "'"
                . '   AND ev.idConsumer = ecc.idConsumer';
        $rs = sql_query($query);
        $result = [];
        if ($rs === false) {
            echo $query;
            echo '  Errore: ' . sql_error();

            return false;
        }
        if (sql_num_rows($rs) > 0) {
            while (list($id, $class, $file) = sql_fetch_row($rs)) {
                $result[$id] = [$class, $file];
            }
        }

        return $result;
    }

    /**
     * This method execute the dispatch of the event on all the consumer related
     *	to the class of the event.
     *
     * @param DoceboEvent $event the event to be dispatched
     * @static
     **/
    public function dispatch(&$event)
    {
        $arr_consumer = DoceboEventManager::listConsumerFromClassId($event->getClassId());

        foreach ($arr_consumer as $consumer_id => $consumer_param) {
            require_once Forma::inc($GLOBALS['where_framework'] . $consumer_param[1]);

            $consumer = eval('return new ' . $consumer_param[0] . '(' . $consumer_id . ');');
            $consumer->actionEvent($event);
        }
    }
}

class EventMessageComposer
{
    protected $module;

    protected $platform;

    protected $lang;

    protected $arr_subject;

    protected $arr_body;

    protected $subject_composed;

    protected $body_composed;

    protected $attachments;

    /**
     * @param string $module      the module name
     * @param string $platform    the platform name
     * @param string $arr_subject the array with the subject info
     * @param string $arr_body    the array with the object info
     *                            array(
     *                            array( 	['lang_text'] => '_ALERT_TEXT',
     *                            ['lang_substtution'] =>  array( text_find => text_replace , ... )
     *                            ), ...
     *                            )
     */
    public function __construct($module = false, $platform = false, $arr_subject = false, $arr_body = false, $attachments = [])
    {
        $this->module = 'email';
        $this->platform = false;
        $this->arr_subject = $arr_subject;
        $this->arr_body = $arr_body;
        $this->subject_composed = false;
        $this->body_composed = false;
        $this->attachments = [];
    }

    public function setSubject($array_info, $media = false)
    {
        $this->subject_composed = false;
        if ($media === false) {
            $this->arr_subject = $array_info;
        } else {
            $this->arr_subject[$media] = $array_info;
        }
    }

    public function setBody($array_info, $media = false)
    {
        $this->body_composed = false;
        if ($media === false) {
            $this->arr_body = $array_info;
        } else {
            $this->arr_body[$media] = $array_info;
        }
    }

    public function setSubjectLangText($media, $lang_text, $arr_substitution, $simple_text = false)
    {
        $this->subject_composed = false;
        $this->arr_subject[$media][] = [
            'lang_text' => $lang_text,
            'lang_substtution' => $arr_substitution,
            'simple_text' => $simple_text, ];
    }

    public function setBodyLangText($media, $lang_text, $arr_substitution, $simple_text = false)
    {
        $this->body_composed = false;
        $this->arr_body[$media][] = [
            'lang_text' => $lang_text,
            'lang_substtution' => $arr_substitution,
            'simple_text' => $simple_text, ];
    }

    public function getSubject($media, $language)
    {
        if ($this->subject_composed !== false && isset($this->subject_composed[$media][$language])) {
            return $this->subject_composed[$media][$language];
        }

        if (isset($this->arr_subject[$media])) {
            $this->subject_composed[$media][$language] = $this->_composeElement($this->arr_subject[$media], $language);
        } else {
            return '';
        }

        return $this->subject_composed[$media][$language];
    }

    public function getBody($media, $language)
    {
        if ($this->body_composed !== false && isset($this->body_composed[$media][$language])) {
            return $this->body_composed[$media][$language];
        }

        if (isset($this->arr_body[$media])) {
            $this->body_composed[$media][$language] = $this->_composeElement($this->arr_body[$media], $language, $media);
        } else {
            return '';
        }

        return $this->body_composed[$media][$language];
    }

    public function _composeElement($arr_element, $language, $media = 'email')
    {
        $compose = '';
        Lang::init('email', false, $language);
        Lang::init('sms', false, $language);
        foreach ($arr_element as $arr_text) {
            if (isset($arr_text['simple_text']) && $arr_text['simple_text'] === true) {
                $compose .= $arr_text['lang_text'];
            } else {
                $compose .= Lang::t($arr_text['lang_text'], $media, $arr_text['lang_substtution'], $language);
            }
        }

        return $compose;
    }

    public function setAttachments(array $attachments): EventMessageComposer
    {
        $this->attachments = $attachments;

        return $this;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function prepare_serialize()
    {
        unset($this->lang);
    }

    public function after_unserialize()
    {
    }
}

/**
 * This function encapsulate a set of common instruction for event notification generation.
 *
 * @param string               $class            The class name ho event (eg. UserMod)
 * @param string               $module           The module generator (eg. directory)
 * @param string               $section          The section in module that generate event (eg. edit)
 * @param int                  $priority         The priority level of event
 * @param string               $description      The description of the event
 * @param array                $recipients       An array of userid that should be notified
 * @param EventMessageComposer $msg_composer     a class for message composition
 * @param bool                 $force_email_send if true the message is sent to all the user in $recipients ignoring their settings for email
 **/
function createNewAlert($class,$module,$section,$priority,$description,
                            $recipients, $msg_composer, $force_email_send = false)
{
    if (!getEnabledEvent($class, $module, $section)) {
        return;
    }

    $event = &DoceboEventManager::newEvent($class, $module, $section, $priority, $description);

    $event->deleteOldProperty();

    if (is_array($recipients['to']) && is_array($recipients['cc']) && is_array($recipients['bcc'])) {
        $event->setProperty('recipientid', implode(',', $recipients['to']));
        $event->setProperty('recipientcc', implode(',', $recipients['cc']));
        $event->setProperty('recipientbcc', implode(',', $recipients['bcc']));
    } else {
        $event->setProperty('recipientid', implode(',', $recipients));
    }
    $event->setProperty('subject', addslashes($msg_composer->getSubject('email', getLanguage())));
    $event->setProperty('body', addslashes($msg_composer->getBody('email', getLanguage())));
    $msg_composer->prepare_serialize(); // __sleep is preferred but i preferr this method
    $event->setProperty('MessageComposer', addslashes(rawurlencode(serialize($msg_composer))));
    $event->setProperty('force_email_send', ($force_email_send === false ? 'false' : 'true'));
    $event->setProperty('attachments', $msg_composer->getAttachments());
    DoceboEventManager::dispatch($event);
}

function getEnabledEvent($class)
{
    $query = 'SELECT COUNT(*) AS count FROM ' . $GLOBALS['prefix_fw'] . '_event_manager AS em'
        . ' INNER JOIN ' . $GLOBALS['prefix_fw'] . '_event_class AS ec ON em.idClass = ec.idClass'
        . " WHERE ec.class = '$class' AND em.permission <> 'not_used'";

    if ($res = sql_query($query)) {
        if ($row = sql_fetch_object($res)) {
            return (bool) $row->count;
        }
    }
}

/*
 * This function encapsulate a set of common instruction for event notification generation
 * @param string $class 	The class name ho event (eg. UserMod)
 * @param string $module	The module generator (eg. directory)
 * @param string $section 	The section in module that generate event (eg. edit)
 * @param int	 $priority	The priority level of event
 * @param string $description The description of the event
 * @param array	 $recipients An array of userid that should be notified
 * @param string $subject 	The subject of notification
 * @param string $body		The body of the notification
 **/
/*function createNewAlert(	$class,$module,$section,$priority,$description,
                            $recipients,$subject,$body ) {
    $event =& DoceboEventManager::newEvent($class, $module, $section, $priority, $description);
    $event->setProperty('recipientid',implode(',',$recipients));
    $event->setProperty('subject',$subject);
    $event->setProperty('body',$body);
    DoceboEventManager::dispatch($event);
}
*/
