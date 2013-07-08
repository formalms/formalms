<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

require_once(dirname(__FILE__).'/certificate.base.php');

class CertificateSubs_Misc extends CertificateSubstitution {

	function getSubstitutionTags() {
		
		$lang =& DoceboLanguage::createInstance('certificate', 'lms');
		
		$subs = array();
		$subs['[today]'] 			= $lang->def('_COURSE_TODAY');
		$subs['[year]'] 			= $lang->def('_COURSE_YEAR');
		return $subs;
	}
	
	function getSubstitution() {
		
		$subs = array();
		
		$subs['[today]'] 	= Format::date(date("Y-m-d H:i:s"), 'date');
		$subs['[year]'] 	= date("Y");
		
		return $subs;
	}
}

?>