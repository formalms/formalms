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

require_once(dirname(__FILE__).'/certificate.base.php');

class CertificateSubs_Misc extends CertificateSubstitution {

	function getSubstitutionTags() {

		$subs = [];
		$subs['[today]'] 			= Lang::t('_COURSE_TODAY','certificate', 'lms');
		$subs['[year]'] 			= Lang::t('_COURSE_YEAR','certificate', 'lms');
		return $subs;
	}
	
	function getSubstitution() {
		
		$subs = [];
		
		$subs['[today]'] 	= Format::date(date("Y-m-d H:i:s"), 'date');
		$subs['[year]'] 	= date("Y");
		
		return $subs;
	}
}

?>