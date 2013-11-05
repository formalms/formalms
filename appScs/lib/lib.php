<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

//require_once(_base_.'/lib/lib.utils.php');
require_once($GLOBALS['where_scs'].'/lib/lib.utils.php');
require_once($GLOBALS['where_scs'].'/lib/lib.check.php');

require_once(_base_.'/lib/lib.json.php');
require_once($GLOBALS['where_scs'].'/lib/lib.emoticons.php');

require_once($GLOBALS['where_scs'].'/modules/video_conference/lib/resource.main.php');
require_once($GLOBALS['where_scs'].'/modules/video_conference/lib/resource.chat.php');
require_once($GLOBALS['where_scs'].'/modules/video_conference/lib/resource.user.php');
require_once($GLOBALS['where_scs'].'/modules/video_conference/lib/resource.room.php');

require_once($GLOBALS['where_scs'].'/lib/lib.htmlpurifier.php');

?>