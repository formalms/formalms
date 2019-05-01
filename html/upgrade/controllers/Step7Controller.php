<?php

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

require_once(dirname(__FILE__).'/StepController.php');

Class Step7Controller extends StepController {

	var $step=7;

	private function clearTwigCache(){

		$twigCacheDir =  _files_ . '/cache/twig';//		\appCore\Template\TwigManager::getCacheDir();
	
		$this->rrmdir($twigCacheDir);
	
	}
	
	private function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (is_dir($dir."/".$object))
						$this->rrmdir($dir."/".$object);
					else
						unlink($dir."/".$object);
				}
			}
			rmdir($dir);
		}
	}
	public function render()
	{
		$this->clearTwigCache();
		parent::render();
	}

	public function validate() {
		return true;
	}

}

?>