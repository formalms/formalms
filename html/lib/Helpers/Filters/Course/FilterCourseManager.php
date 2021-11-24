<?php

require_once Forma::inc(_lib_ .'/Helpers/Filters/FilterManager.php');

class FilterCourseManager extends FilterManager
{

    public function getCookieIndex(string $param, string $prefix = null) : string{

        $arrayPartitions = [];
        $cookieIndex = '';
        if ($prefix) {
            $arrayPartitions[] = $prefix;
        }

        switch($param) {
            case "filter_cat":
                    $arrayPartitions[] = 'my_course';
                    $arrayPartitions[] = 'category';
                break;

            default:
            //not defined behaviour
                $arrayPartitions = [];
                break;
        }

        if (count($arrayPartitions)) {
            $cookieIndex = implode(".", $arrayPartitions);
        }

        return $cookieIndex;
    }


    
}