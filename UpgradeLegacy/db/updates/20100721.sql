INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
	('kb_filter_by_user_access', 'on', 'enum', '3', 'main', '4', '20', '1', '0', '');

ALTER TABLE `learning_kb_res`
	ADD `sub_categorize` tinyint(1) NOT NULL DEFAULT -1 AFTER `force_visible`;

ALTER TABLE  `learning_coursepath_user` ADD  `date_assign` DATETIME NOT NULL AFTER  `waiting`;

ALTER TABLE `learning_games` ADD COLUMN `play_chance` VARCHAR(45) NOT NULL AFTER `id_resource`;