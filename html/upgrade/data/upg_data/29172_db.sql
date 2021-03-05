INSERT IGNORE INTO `dashboard_blocks` (`id`, `block_class`, `created_at`)
VALUES (9, 'DashboardBlockWelcomeLms',CURRENT_TIMESTAMP);

-- delete menu chat
delete from learning_module where module_name like 'chat';
DELETE FROM `learning_quest_type` WHERE `type_quest` = 'hot_text';

-- add property ignorescore in scorm
ALTER TABLE `learning_organization` ADD `ignoreScore` TINYINT( 4 ) NOT NULL DEFAULT '0';

-- setting ignore_score
INSERT INTO `core_setting` 
(`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) 
VALUES
('ignore_score', 'off', 'enum', 3, '0', 4, 16, 1, 0, '');