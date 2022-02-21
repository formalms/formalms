<?php defined("IN_FORMA") or die('Direct access is forbidden.');



require_once($GLOBALS['where_framework'].'/addons/htmlpurifier/HTMLPurifier.auto.php');

/**
 * Extension of HTMLPurifier for a couple of reason such as easy mantainance and commutation to singleton class
 */
class DbPurifier extends HTMLPurifier {

	/**
	 * constructor, this is a singleton class please don't use this but make a call like this : $var =& DBPurifier::getInstance()
	 * @access private
	 */
	function DbPurifier() {

		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core', 'Encoding', 'UTF-8');
		$config->set('Core', 'XHTML', true);
        $config->set('Cache.SerializerPath', _files_ . '/cache/twig');
		parent::HTMLPurifier($config);
	}

	/**
	 * return the instance of the DbPurifier
	 * @access public
	 */
	function &getInstance() {

		if(!isset($GLOBALS['html_purifier'])) {

			$GLOBALS['html_purifier'] = new DBPurifier();
		}
		return $GLOBALS['html_purifier'];
	}

	/**
	 * remove all the html from the string
	 */
	function text($string) {

		return strip_tags($this->purify($string));
	}

}

?>
