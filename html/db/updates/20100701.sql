ALTER TABLE `core_event_manager` MODIFY COLUMN `permission` ENUM('not_used','mandatory') NOT NULL DEFAULT 'not_used';

ALTER TABLE `learning_label` ADD COLUMN `sequence` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `file_name`;

INSERT INTO core_setting (`param_name`,`param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('send_cc_for_system_emails','','string', 255, 0, 8, 18, 1, 0, '')
