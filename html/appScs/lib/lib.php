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

//require_once(_base_.'/lib/lib.utils.php');
require_once $GLOBALS['where_scs'] . '/lib/lib.utils.php';

require_once _base_ . '/lib/lib.json.php';
require_once $GLOBALS['where_scs'] . '/lib/lib.emoticons.php';

require_once $GLOBALS['where_scs'] . '/modules/video_conference/lib/resource.main.php';
require_once $GLOBALS['where_scs'] . '/modules/video_conference/lib/resource.chat.php';
require_once $GLOBALS['where_scs'] . '/modules/video_conference/lib/resource.user.php';
require_once $GLOBALS['where_scs'] . '/modules/video_conference/lib/resource.room.php';
