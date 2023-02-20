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

define('IS_PAYPAL', true);
define('IN_FORMA', true);
define('_deeppath_', '../');
require dirname(__FILE__) . '/../base.php';

// start buffer
ob_start();

$GLOBALS['orig_post'] = $_POST; // not filtered

// initialize
require _base_ . '/lib/lib.bootstrap.php';
Boot::init(BOOT_PAGE_WR);

// -----------------------------------------------------------------------------

if (!empty($_GET['op'])) {
    switch ($_GET['op']) {
        case 'ok':  // redirect to the success message
            $link = FormaLms\lib\Get::rel_path('lms') . '/index.php?r=cart/show&ok=1';
            Util::jump_to($link);
         break;
    }
    exit();
} else { // Default action: paypal notify
    $cart = new CartLmsController();
    $cart->paypalNotifyTask();
}

// -----------------------------------------------------------------------------

// finalize
Boot::finalize();
