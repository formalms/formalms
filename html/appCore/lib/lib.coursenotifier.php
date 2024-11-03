<?php

use FormaLms\lib\Forma;

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

require_once _base_ . '/lib/lib.event.php';

/**
 * This is the class for ClassEvents in Forma.
 *
 * @version  $Id: lib.coursenotifier.php 113 2006-03-08 18:08:42Z ema $
 */
class FormaCourseNotifier extends FormaEventConsumer
{
    public function _getConsumerName()
    {
        return 'FormaUserNotifier';
    }

    public function actionEvent(&$event)
    {
        require_once Forma::include(_lms_ . '/lib/', 'lib.subscribe.php');

        parent::actionEvent($event);

        $acl_man = \FormaLms\lib\Forma::getAclManager();;

        // recover event information
        $id_user = $event->getProperty('userdeleted');

        $man_subs = new CourseSubscribe_Management();
        $man_subs->unsubscribeUserFromAllCourses($id_user);

        return true;
    }
}
