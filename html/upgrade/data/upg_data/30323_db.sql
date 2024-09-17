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

-- DROP INDEX IF EXISTS
SELECT
    COUNT(*)
INTO
    @INDEX_my_index_ON_TABLE_my_table_EXISTS
FROM
    `information_schema`.`statistics`
WHERE
    `table_schema` = 'my_database'
    AND `index_name` = 'unique_coursereport'
    AND `table_name` = 'learning_coursereport'
;
SET @statement := IF(
    @INDEX_my_index_ON_TABLE_my_table_EXISTS > 0,
    -- 'SELECT "info: index exists."',
    'DROP INDEX `unique_coursereport` ON `learning_coursereport`',
    'SELECT "info: index does not exist."'
);
PREPARE statement FROM @statement;
EXECUTE statement;
DEALLOCATE PREPARE statement;

ALTER TABLE learning_coursereport
        ADD CONSTRAINT unique_coursereport UNIQUE (source_of, id_course, id_source);

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

INSERT IGNORE INTO `core_menu` ( `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform` )
VALUES
    ( '_MANAGEMENT_COMMUNICATION', '', '4', TRUE, TRUE, (SELECT `idMenu` FROM ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_CONTENTS' LIMIT 1 ) tbl), NULL, 'framework' );

INSERT IGNORE INTO `core_menu` ( `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform` )
VALUES
    ( '_CATEGORIES', '', '1', TRUE, TRUE, (SELECT `idMenu` FROM ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_MANAGEMENT_COMMUNICATION' ) tbl), NULL, 'framework' );

UPDATE `core_menu` SET `idParent` =  (SELECT `idMenu` FROM ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_MANAGEMENT_COMMUNICATION' LIMIT 1) tbl) WHERE name = '_COMMUNICATION_MAN';

INSERT IGNORE INTO `core_menu_under` ( `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path` )
VALUES
    ( ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_CATEGORIES' LIMIT 1 ) ,
    'communication',
    '_CATEGORIES',
    NULL,
    'view',
    'framework',
    1,
    NULL,
    NULL,
    'alms/communication/showCategories'
    );
