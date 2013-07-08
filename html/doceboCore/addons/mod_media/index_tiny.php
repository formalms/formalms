<?php

/*************************************************************************/
/* DOCEBO FRAMEWORK                                                      */
/* ============================================                          */
/*                                                                       */
/* Copyright (c) 2005 by Giovanni Derks <giovanni[AT]docebo-com>         */
/* http://www.docebo.com                                                 */
/*                                                                       */
/* This program is free software. You can redistribute it and/or modify  */
/* it under the terms of the GNU General Public License as published by  */
/* the Free Software Foundation; either version 2 of the License.        */
/*************************************************************************/

if($_GET['type'] == 'file') define("POPUP_MOD_NAME", "mod_link");
else define("POPUP_MOD_NAME", "mod_media");


// ----------- Popup Options ---------------
$GLOBALS["popup"]["editor"]="tiny";
// -----------------------------------------

require_once("../mod_index/index.php");


?>