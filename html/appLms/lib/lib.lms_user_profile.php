<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once _base_ . '/lib/lib.user_profile.php';

/**
 * @category library
 */
class LmsUserProfile extends UserProfile
{
    /**
     * class constructor.
     */
    public function __construct($id_user, $edit_mode = false)
    {
        parent::__construct($id_user, $edit_mode);
    }

    // initialize functions ===========================================================

    /**
     * instance the viewer class of the profile.
     */
    public function initViewer($varname_action, $platform = null)
    {
        $this->_up_viewer = new LmsUserProfileViewer($this, $varname_action);
    }
}

// ========================================================================================================== //
// ========================================================================================================== //
// ========================================================================================================== //

/**
 * @category library
 */
class LmsUserProfileViewer extends UserProfileViewer
{
    /**
     * class constructor.
     */
    public function __construct(&$user_profile, $varname_action)
    {
        parent::UserProfileViewer($user_profile, $varname_action);
    }

    /**
     * print the title of the page.
     *
     * @param mixed  $text  the title of the area, or the array with zone path and name
     * @param string $image the image to load before the title
     *
     * @return string the html code for space open
     */
    public function getTitleArea()
    {
        return '';
    }

    /**
     * Print the head of the module space after the getTitle area.
     *
     * @return string the html code for space open
     */
    public function getHead()
    {
        return '<div class="up_main">' . "\n";
    }

    /**
     * Print the footer of the module space.
     *
     * @return string the html code for space close
     */
    public function getFooter()
    {
        return '</div>' . "\n";
    }
}
