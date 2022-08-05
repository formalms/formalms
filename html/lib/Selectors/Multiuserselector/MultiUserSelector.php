<?php
namespace FormaLms\lib\Selectors\Multiuserselector;

use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\UserDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\RoleDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\GroupDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\OrgDataSelector;
use FormaLms\lib\Selectors\Multiuserselector\DataSelectors\DataSelector;
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

class MultiUserSelector { 

    protected $dataSelectors =  array();

    const NAMESPACE = 'FormaLms\lib\Selectors\Multiuserselector\DataSelectors\\';

    public function setDataSelectors(string $dataSelector, string $key) : self{

        $className = self::NAMESPACE . $dataSelector;
        try {
            $this->dataSelectors[$key] = new $className();
        } catch(Exception $e) {
            //
        }
        

        return $this;
    }

    public function getDataSelectors() : array{

        return $this->dataSelectors;
    }

    public function retrieveDataSelector($key) : ?DataSelector{

        return $this->dataSelectors[$key];
    }
    
}
