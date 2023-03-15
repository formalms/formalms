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

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * @version 	$Id: lib.userselection.php 113 2006-03-08 18:08:42Z ema $
 */
class userSelection
{
    public function userSelection($title = '')
    {
        if ($title != '') {
            echo '<div class="title">' . $title . '</div>';
        }
    }

    public function leftColum($title, $element)
    {
        //EFFECTS : construct the left colum with the array passed

        echo '<div class="user-colum-50">' . "\n"
            . (($title != '' ? $title : '')) . "\n"
            . '<div class="user-selection-colum">' . "\n";
        if (is_array($element)) {
            $i = 0;
            foreach ($element as $key => $val) {
                echo '<div class="color-line' . (!($i++ % 2) ? '' : '-alt') . '">' . $val . '</div>' . "\n";
            }
        }
        echo '</div>' . "\n"
            . '</div>' . "\n";
    }

    public function rightColum($title, $element)
    {
        //EFFECTS : construct the right colum with the array passed

        echo '<div class="user-colum-50">' . "\n"
            . (($title != '' ? $title : '')) . "\n"
            . '<div class="user-selection-colum">' . "\n";
        if (is_array($element)) {
            $i = 0;
            foreach ($element as $key => $val) {
                echo '<div class="color-line' . (($i++ % 2) ? '' : '-alt') . '">' . $val . '</div>' . "\n";
            }
        }
        echo '</div>' . "\n"
            . '</div>' . "\n";
    }

    public function noFloat()
    {
        echo '<div class="nofloat"></div>' . "\n";
    }
}
