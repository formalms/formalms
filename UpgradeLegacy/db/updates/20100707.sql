
ALTER TABLE  `learning_courseuser` ADD  `rule_log` INT( 11 ) NULL AFTER  `subscribed_by`;

ALTER TABLE  `learning_rules_log` ADD  `log_action` VARCHAR( 255 ) NOT NULL AFTER  `id_log`;

RENAME TABLE `learning_rules` TO `core_rules` ;
RENAME TABLE `learning_rules_entity` TO `core_rules_entity` ;
RENAME TABLE `learning_rules_log` TO `core_rules_log` ;

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES ('common_admin_session', 'on', 'enum', '3', 'main', '8', '30', '1', '0', '');