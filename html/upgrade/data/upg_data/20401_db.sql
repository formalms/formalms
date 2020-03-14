INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
VALUES ('smtp_host', '', 'string', 255, 'Smtp Host', 14, 1, 1, 0, '');

INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
VALUES ('smtp_port', '', 'string', 255, 'Smtp Port', 14, 2, 1, 0, '');

INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
VALUES ('smtp_secure', '', 'string', 255, 'Smtp Secure', 14, 3, 1, 0, '');

INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
VALUES ('smtp_user', '', 'string', 255, 'Smtp User', 14, 4, 1, 0, '');

INSERT IGNORE INTO core_setting (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
VALUES ('smtp_pwd', '', 'string', 255, 'Smtp Password', 14, 5, 1, 0, '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_auto_tls', '', 'on_off', 255, 'Smtp Auto TLS', 14, 7, 1, 0, '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_debug', '0', 'string', 255, 'Smtp Debug', 14, 8, 1, 0, '');


