<?php

namespace FormaLms\lib\Helpers;

use FormaLms\lib\Get;
use FormaLms\lib\Event\Events;
use FormaLms\lib\Helpers\FormaJson;
use FormaLms\lib\App\TemplateManager;
use FormaLms\lib\System\PlatformManager;
use FormaLms\lib\Language\LanguageService;

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

/*
 * Utils file, basic function for the nowadays use
 *
 * In this file you can find some usefull functions fot the html generation
 * and for recovering some common ifnormation about the user, the script and
 * so on.
 * @author Fabio Pirovano <gishell@tiscali.it>
 * @version 1.0
 * @package main
 */

/*
 * docebo constant for signature
 */
define('MAX_SIGN_GAP', 3600 * 8);

class Util
{

    public static function putElementInArrayAfterIndex(array $data, string $indexToFind, string $valueToFind, array $newBlock) : array {

        foreach ($data as $index => $item) {
            if ($item[$indexToFind] === $valueToFind) {
                // Usa array_splice per inserire il nuovo blocco subito dopo
                array_splice($data, $index + 1, 0, [$newBlock]);
                break;
            }
        }

        return $data;

    }

}