select if (
               exists(
                       select distinct index_name from information_schema.statistics
                       where table_schema = DATABASE()
                         and table_name = 'learning_courseuser' and index_name like 'courseuser_course_idx'
                   )
           ,'select ''index courseuser_course_idx exists'' _______;'
           ,'ALTER TABLE learning_courseuser ADD INDEX courseuser_course_idx (idCourse)') into @a;
PREPARE stmt1 FROM @a;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;