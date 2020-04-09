INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('report_persistence_days', '30', 'int', '4', 'report_settings', '8', '990', '1', '0', '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('report_max_email_size_MB', '0', 'int', '4', 'report_settings', '8', '991', '1', '0', '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('report_storage_folder', '/files/common/report/', 'string', '255', 'report_settings', '8', '992', '1', '0', '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('use_immediate_report', 'off', 'enum', '3', 'report_settings', '8', '993', '1', '0', '');

DELETE FROM `core_setting` WHERE `param_name` = 'profile_only_pwd';
INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('profile_modify', 'allow', 'profile_modify', '16', '0', '3', '10', '1', '0', '');
INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('profile_modify_url', '', 'string', '255', '0', '3', '11', '1', '0', '');

ALTER TABLE `learning_report_schedule`
	ADD COLUMN last_execution DATETIME NULL DEFAULT NULL;

INSERT IGNORE INTO core_st(idst) values(null);
set @v_idst=LAST_INSERT_ID();
INSERT IGNORE INTO core_role(idst, roleid, description) VALUES
(@v_idst, concat('/lms/admin/report/schedule'), NULL);

ALTER TABLE `learning_courseuser` ADD INDEX `courseuser_course_idx` (`idCourse`);

ALTER TABLE `core_field_userentry` CHANGE COLUMN `id_common` `id_common` INT(11) NOT NULL DEFAULT '0' ;
