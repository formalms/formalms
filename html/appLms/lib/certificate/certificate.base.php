<?php defined("IN_FORMA") or die('Direct access is forbidden.');



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
		return [];
	}

	function getSubstitutionTags()
	{
		return [];
	}

}

?>