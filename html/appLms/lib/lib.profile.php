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

function getprofile($id_user)
{
    require_once _base_ . '/lib/lib.form.php';
    require_once _adm_ . '/lib/lib.field.php';

    $acl_man = \FormaLms\lib\Forma::getAcl()->getACLManager();

    $user_info = $acl_man->getUser($id_user, false);

    $txt = '<div>';

    $txt .= '<div class="boxinfo_title">' . Lang::t('_USERPARAM', 'profile', 'framework') . '</div>'
        . Form::getLineBox(Lang::t('_USERNAME', 'profile', 'framework'), $acl_man->relativeId($user_info[ACL_INFO_USERID]))
        . Form::getLineBox(Lang::t('_LASTNAME', 'profile', 'framework'), $user_info[ACL_INFO_LASTNAME])
        . Form::getLineBox(Lang::t('_NAME', 'profile', 'framework'), $user_info[ACL_INFO_FIRSTNAME])
        . Form::getLineBox(Lang::t('_EMAIL', 'profile', 'framework'), $user_info[ACL_INFO_EMAIL])
        . Form::getBreakRow()
        . '<div class="boxinfo_title">' . Lang::t('_USERFORUMPARAM') . '</div>'
        . '<table class="profile_images">'
        . '<tr><td>';
    // NOTE: avatar
    if ($user_info[ACL_INFO_AVATAR] != '') {
        $img_size = getimagesize($path . $user_info[ACL_INFO_AVATAR]);
        $txt .= '<img class="profile_image'
            . ($img_size[0] > 150 || $img_size[1] > 150 ? ' image_limit' : '')
            . '" src="' . $path . $user_info[ACL_INFO_AVATAR] . '" alt="' . Lang::t('_AVATAR', 'profile', 'framework') . '" /><br />';
    } else {
        $txt .= '<div class="text_italic">' . Lang::t('_NOAVATAR', 'profile', 'framework') . '</div>';
    }
    // NOTE: signature
    $txt .= '</td></tr></table>'
        . '<div class="title">' . Lang::t('_SIGNATURE', 'profile', 'framework') . '</div>'
        . '<div class="profile_signature">' . $user_info[ACL_INFO_SIGNATURE] . '</div><br />' . "\n";

    $txt .= '</div>';

    return $txt;
}
