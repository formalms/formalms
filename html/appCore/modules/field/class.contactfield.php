<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

/**
 * @package  DoceboCore
 * @version  $Id: class.cf.php 601 2006-09-01 10:50:52Z giovanni $
 * @category Field
 * @author   Claudio Cherubino <claudio.cherubino@docebo.com>
 */

require_once(Forma::inc(_adm_.'/modules/field/class.field.php'));

/**
* class for IM fields
*/
class Field_Contact extends Field {

/**
* this function is useful for field recognize
*
* @return string	return the identifier of the field
*
* @access public
*/
function getFieldType() {
return 'contact_field';
}

function getIMBrowserHref($id_user, $field_value) {

return '';
}

function getIMBrowserImageSrc($id_user, $field_value) {

return '';
}
}