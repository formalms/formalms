<?php

namespace FormaLms\lib\Services\Courses;

use FormaLms\lib\Services\BaseService;



require_once _lms_.'/lib/lib.course.php';


class CourseService extends BaseService
{

    public function getCourseFromMenu(int $idMenu) : array {

        $queryText = 'SELECT c.* FROM %lms_course c
                JOIN %lms_menucourse_under mu ON mu.idCourse = c.idCourse AND mu.idMain = "'.$idMenu.'"
                LIMIT 1';

        $queryResult = sql_query($queryText);
    
        $row = sql_fetch_assoc($queryResult);

        return $row;
    }
}