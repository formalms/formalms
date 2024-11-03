ALTER TABLE learning_light_repo ADD repo_teacher_alert boolean default false;
INSERT IGNORE INTO `dashboard_blocks`(`block_class`, `created_at`) VALUES ('DashboardBlockCourseAttendanceGraphLms', CURRENT_TIMESTAMP);
select if (
               exists(
                       select distinct index_name from information_schema.statistics
                       where table_schema = DATABASE()
                         and table_name = 'learning_testquestanswer' and index_name like 'idQuest_idx'
                   )
           ,'select ''index idQuest_idx exists'' _______;'
           ,'ALTER TABLE learning_testquestanswer ADD INDEX idQuest_idx (idQuest) USING BTREE') into @a;
PREPARE stmt1 FROM @a;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;


select if (
               exists(
                       select distinct index_name from information_schema.statistics
                       where table_schema = DATABASE()
                         and table_name = 'learning_coursereport' and index_name like 'idCourse_idReport_idx'
                   )
           ,'select ''index idCourse_idReport_idx exists'' _______;'
           ,'ALTER TABLE learning_coursereport ADD INDEX idCourse_idReport_idx (id_course,id_report) USING BTREE') into @a;
PREPARE stmt1 FROM @a;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

select if (
               exists(
                       select distinct index_name from information_schema.statistics
                       where table_schema = DATABASE()
                         and table_name = 'core_field_userentry' and index_name like 'idUser_idCommon_idx'
                   )
           ,'select ''index idUser_idCommon_idx exists'' _______;'
           ,'ALTER TABLE core_field_userentry ADD INDEX idUser_idCommon_idx (id_user,id_common) USING BTREE') into @a;
PREPARE stmt1 FROM @a;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @dbname = DATABASE();
SET @tablename = "learning_forum";

SET @columnname = "max_threads";

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " INT(11) DEFAULT 0")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = "threads_are_private";

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " TINYINT(1) DEFAULT 0")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;