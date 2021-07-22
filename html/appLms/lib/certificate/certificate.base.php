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

class CertificateSubstitution
{
	var $id_user;
	var $id_course;
	var $id_meta;

	function CertificateSubstitution($id_user, $id_course, $id_meta = 0)
	{
		$this->id_user = $id_user;
		$this->id_course = $id_course;
		$this->id_meta = $id_meta;
	}

	function getSubstitution()
	{
		return array();
	}

	function getSubstitutionTags()
	{
		return array();
	}

}

?>