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

use FormaLms\lib\Session\SessionManager;

class Learning_Cart
{
    public function __construct()
    {
        Learning_Cart::init();
    }

    public static function init()
    {
        if (!SessionManager::getInstance()->getSession()->has('lms_cart')) {
            SessionManager::getInstance()->getSession()->set('lms_cart', []);
            SessionManager::getInstance()->getSession()->save();
        }
    }

    public static function cartItemCount()
    {
        $count = 0;
        $cart = SessionManager::getInstance()->getSession()->get('lms_cart');
        $i = 0;

        foreach ($cart as $id_course => $extra) {
            if (is_array($extra)) {
                if (isset($extra['classroom'])) {
                    $count += count($extra['classroom']);
                } else {
                    $count += count($extra['edition']);
                }
            } else {
                ++$count;
            }
        }

        if ($count == 0) {
            Learning_Cart::emptyCart();
        }

        return $count;
    }

    public static function emptyCart()
    {
        SessionManager::getInstance()->getSession()->set('lms_cart', []);
        SessionManager::getInstance()->getSession()->save();
    }
}
