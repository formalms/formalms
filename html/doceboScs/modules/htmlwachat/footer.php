<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

/*Save user info*/
if( Docebo::user()->isLoggedIn() )
	Docebo::user()->SaveInSession();

/*End database connection*************************************************/

DbConn::getInstance()->close();

/*Flush buffer************************************************************/



/* output all */
$GLOBALS['page']->add(ob_get_contents(), 'debug');
ob_clean();

echo $GLOBALS['page']->getContent();
ob_end_flush();

?>
