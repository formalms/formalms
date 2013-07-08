<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

Header('Location: http://'.$_SERVER['HTTP_HOST']
    		.( strlen(dirname($_SERVER['PHP_SELF'])) != 1 ? dirname($_SERVER['PHP_SELF']) : '' )
			.'/doceboCore/');

?>