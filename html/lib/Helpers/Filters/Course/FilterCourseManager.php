<?php

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

require_once \FormaLms\lib\Forma::inc(_lib_ . '/Helpers/Filters/FilterManager.php');

class FilterCourseManager extends FilterManager
{
    public function getCookieIndex(string $param, string $prefix = null): string
    {
        $arrayPartitions = [];
        $cookieIndex = '';
        if ($prefix) {
            $arrayPartitions[] = $prefix;
        }

        switch ($param) {
            case 'filter_cat':
                    $arrayPartitions[] = 'my_course';
                    $arrayPartitions[] = 'category';
                break;

            default:
            //not defined behaviour
                $arrayPartitions = [];
                break;
        }

        if (count($arrayPartitions)) {
            $cookieIndex = implode('.', $arrayPartitions);
        }

        return $cookieIndex;
    }
}
