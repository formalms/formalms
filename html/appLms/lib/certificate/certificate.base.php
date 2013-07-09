<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
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