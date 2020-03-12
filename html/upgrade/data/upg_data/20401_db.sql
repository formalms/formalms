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

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_auto_tls', '', 'on_off', 255, 'Smtp Auto TLS', 14, 7, 1, 0, '');

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('smtp_debug', '0', 'string', 255, 'Smtp Debug', 14, 8, 1, 0, '');


--- Aggregated certificate refactoring MVC
UPDATE core_menu_under
SET  	default_op = '',
       class_file = '',
       class_name = '',
       mvc_path = 'alms/aggregatedcertificate/show'
WHERE idUnder = 242 ;



ALTER TABLE learning_certificate_meta
    RENAME TO learning_aggregated_cert_metadata;
ALTER TABLE learning_aggregated_cert_metadata
    CHANGE idMetaCertificate idAssociation INT(11) NOT NULL AUTO_INCREMENT;




ALTER TABLE learning_certificate_meta_assign
    RENAME TO learning_aggregated_cert_assign;
ALTER TABLE learning_aggregated_cert_assign
    CHANGE idMetaCertificate idAssociation INT(11) NOT NULL;
ALTER TABLE `learning_aggregated_cert_assign` DROP PRIMARY KEY, ADD PRIMARY KEY (`idUser`, `idCertificate`) USING BTREE;



ALTER TABLE learning_certificate_meta_course
    RENAME TO learning_aggregated_cert_course;
ALTER TABLE learning_aggregated_cert_course
    CHANGE idMetaCertificate idAssociation INT(11) NOT NULL ;


--
-- Struttura della tabella `learning_aggregated_cert_coursepath`
--

CREATE TABLE IF NOT EXISTS `learning_aggregated_cert_coursepath` (
                                                                     `id` int(11) NOT NULL AUTO_INCREMENT,
                                                                     `idAssociation` int(11) NOT NULL DEFAULT '0',
                                                                     `idUser` int(11) NOT NULL DEFAULT '0',
                                                                     `idCoursePath` int(11) NOT NULL DEFAULT '0',
                                                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Struttura della tabella `learning_aggregated_cert_coursepath`
--
