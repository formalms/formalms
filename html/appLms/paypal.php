<?php

define("IS_PAYPAL", true);
define("IN_FORMA", true);
define("_deeppath_", '../');
require(dirname(__FILE__).'/../base.php');

// start buffer
ob_start();

$GLOBALS['orig_post']=$_POST; // not filtered

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_PAGE_WR);


// -----------------------------------------------------------------------------


if (!empty($_GET['op'])) {
	switch ($_GET['op']) {
		case 'ok': { // redirect to the success message
			$link =Get::rel_path('lms').'/index.php?r=cart/show&ok=1';
			Util::jump_to($link);
		} break;
	}
	die();
}
else { // Default action: paypal notify
	$cart =new CartLmsController();
	$cart->paypalNotifyTask();
}


// -----------------------------------------------------------------------------

// finalize
Boot::finalize();
?>
