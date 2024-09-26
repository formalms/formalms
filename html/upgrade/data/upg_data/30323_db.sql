DELETE FROM learning_coursereport
                            WHERE id_report NOT IN (
                                SELECT * FROM (SELECT MIN(id_report) 
                                FROM learning_coursereport 
                                GROUP BY source_of, id_course, id_source
                                ) as t);
DELETE FROM learning_coursereport
                            WHERE id_course = 0;
DELETE FROM learning_coursereport_score
                            WHERE id_report NOT IN (
                                SELECT id_report
                                FROM learning_coursereport 
                              
                                );

-- Variables for table and constraint
SET @table_name = DATABASE();
SET @table_name = 'learning_coursereport';
SET @constraint_name = 'unique_coursereport';

-- Check if the constraint already exists
SET @constraint_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_TYPE = 'UNIQUE'
      AND TABLE_NAME = @table_name
      AND CONSTRAINT_NAME = @constraint_name
);

-- Conditionally add the constraint if it does not exist
SET @sql = IF(
    @constraint_exists = 0,
    CONCAT('ALTER TABLE ', @table_name, ' ADD CONSTRAINT ', @constraint_name, ' UNIQUE (source_of, id_course, id_source);'),
    'SELECT "Constraint already exists.";'
);

-- Execute the SQL
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
                        ('force_scorm_finish', 'on', 'enum', 3, '0', 4, 17, 1, 0, '');

/*repeated from former version for errors*/
CREATE TABLE IF NOT EXISTS learning_communication_lang (
    id_comm int,
    lang_code varchar(255),
    title varchar(255),
    description text
);

UPDATE `core_reg_setting` SET `value` = '-' WHERE `val_name` = 'date_sep';

UPDATE core_menu_under SET module_name = 'dashboard' WHERE default_name = '_DASHBOARD' and associated_token = 'view' and of_platform = 'lms';
INSERT IGNORE INTO core_role ( idst, roleId )
SELECT max(idst)+1, '/lms/course/public/dashboard/view' FROM core_st LIMIT 1;

SET @idParent = ( SELECT `idMenu` FROM `core_menu` WHERE `name` = '_CONTENTS' LIMIT 1 );
INSERT INTO `core_menu` ( `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform` )
SELECT * FROM (
    SELECT '_MANAGEMENT_COMMUNICATION', '', '4', TRUE AS `true1`, TRUE AS `true2`, @idParent, NULL, 'framework' 
 ) AS tmp
WHERE NOT EXISTS (
    SELECT 'idParent' FROM `core_menu`
        WHERE `name` = '_MANAGEMENT_COMMUNICATION'
) LIMIT 1;

SET @idParent = ( SELECT `idMenu` FROM `core_menu` WHERE `name` = '_MANAGEMENT_COMMUNICATION' LIMIT 1 );
INSERT INTO `core_menu` ( `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform` )
SELECT * FROM (
    SELECT '_CATEGORIES', '', '1', TRUE AS `true1`, TRUE AS `true2`, @idParent, NULL, 'framework' 
 ) AS tmp
WHERE NOT EXISTS (
    SELECT 'idParent' FROM `core_menu`
        WHERE `name` = '_CATEGORIES'
) LIMIT 1;

UPDATE `core_menu` SET `idParent` =  (SELECT `idMenu` FROM ( SELECT `idMenu` FROM `core_menu` WHERE `name` = '_MANAGEMENT_COMMUNICATION' LIMIT 1) tbl) WHERE `name` = '_COMMUNICATION_MAN';

SET @idMenu = ( SELECT `idMenu` FROM `core_menu` WHERE `name` = '_CATEGORIES' LIMIT 1 );
INSERT INTO `core_menu_under` ( `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path` )
SELECT * FROM (SELECT @idMenu,
    'communication',
    '_CATEGORIES',
    NULL AS `null1`,
    'view',
    'framework',
    1,
    NULL AS `null2`,
    NULL AS `null3`,
    'alms/communication/showCategories'
    ) AS tmp
WHERE NOT EXISTS (
    SELECT `idMenu` FROM `core_menu_under`
        WHERE `idMenu` = @idMenu
) LIMIT 1;

UPDATE `core_menu_under` SET `default_name` = '_CATEGORIES' WHERE `module_name` = 'reservation' AND `default_name` = '_CATEGORY';

UPDATE `core_menu` SET `name` = '_CATEGORIES' WHERE `idMenu` = ( SELECT `idMenu` FROM `core_menu_under` WHERE `module_name` = 'reservation' AND `default_name` = '_CATEGORIES' LIMIT 1 );
