<?php

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

class CourseLevel
{
    public const COURSE_LEVEL_ADMIN = 7;
    public const COURSE_LEVEL_TEACHER = 6;
    public const COURSE_LEVEL_MENTOR = 5;
    public const COURSE_LEVEL_TUTOR = 4;
    public const COURSE_LEVEL_STUDENT = 3;
    public const COURSE_LEVEL_GHOST = 2;
    public const COURSE_LEVEL_GUEST = 1;

    public const COURSE_LEVELS = [self::COURSE_LEVEL_ADMIN, self::COURSE_LEVEL_TEACHER, self::COURSE_LEVEL_MENTOR, self::COURSE_LEVEL_TUTOR, self::COURSE_LEVEL_STUDENT, self::COURSE_LEVEL_GHOST, self::COURSE_LEVEL_GUEST];

    public static function getTranslatedLevels($op = '')
    {


        return [
            7 => Lang::t('_LEVEL_7', 'levels'),        //'Admin'
            6 => Lang::t('_LEVEL_6', 'levels'),        //'Prof'
            5 => Lang::t('_LEVEL_5', 'levels'),        //'Mentor'
            4 => Lang::t('_LEVEL_4', 'levels'),        //'Tutor'
            3 => Lang::t('_LEVEL_3', 'levels'),        //'Studente'
            2 => Lang::t('_LEVEL_2', 'levels'),        //'Ghost' (no track)
            1 => Lang::t('_LEVEL_1', 'levels'),        //'Guest'
        ];
    }

    public static function getLevels()
    {
        return [
            self::COURSE_LEVEL_ADMIN => 'Administrator',
            self::COURSE_LEVEL_TEACHER => 'Instructor',
            self::COURSE_LEVEL_MENTOR => 'Mentor',
            self::COURSE_LEVEL_TUTOR => 'Tutor',
            self::COURSE_LEVEL_STUDENT => 'Student',
            self::COURSE_LEVEL_GHOST => 'Ghost',
            self::COURSE_LEVEL_GUEST => 'Guest',
        ];
    }

    public function isTeacher($level)
    {
        $res = ((int)$level === self::COURSE_LEVEL_TEACHER ? true : false);

        return $res;
    }
}
