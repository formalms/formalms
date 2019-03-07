<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

Util::get_js(Get::rel_path('lib') . '/formatable/formatable.js', true, true);
Util::get_css(Get::rel_path('lib') . '/formatable/formatable.css', true, true);
