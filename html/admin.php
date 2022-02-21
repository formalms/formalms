<?php



Header('Location: http://'.$_SERVER['HTTP_HOST']
    		.( strlen(dirname($_SERVER['PHP_SELF'])) != 1 ? dirname($_SERVER['PHP_SELF']) : '' )
			.'/appCore/');

?>