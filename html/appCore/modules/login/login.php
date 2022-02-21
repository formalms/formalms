<?php defined("IN_FORMA") or die('Direct access is forbidden.');



function loginDispatch($op) {
    switch($op) {
	    case "login" : {
		    Util::jump_to(Get::rel_path('base').'/index.php?modname=login&amp;op=login');
	    };break;
    }
}
?>