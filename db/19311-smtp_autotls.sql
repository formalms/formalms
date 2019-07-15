-- Task #19311 - PROBLEMA INVIO EMAIL PARAMETRO AUTO TLS

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_auto_tls', '', 'on_off', 255, 'Smtp Auto TLS', 14, 7, 1, 0, '');

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_debug', '0', 'string', 255, 'Smtp Debug', 14, 8, 1, 0, '');
