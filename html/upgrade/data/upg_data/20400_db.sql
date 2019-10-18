INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('report_persistence_days', '30', 'int', '4', 'report_settings', '1', '990', '1', '0', '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('report_max_email_size_MB', '0', 'int', '4', 'report_settings', '1', '991', '1', '0', '');

INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('report_storage_folder', '/files/common/report/', 'string', '255', 'report_settings', '1', '992', '1', '0', '');

DELETE FROM `core_setting` WHERE `param_name` = 'profile_only_pwd';
INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('profile_modify', 'allow', 'profile_modify', '16', '0', '3', '1', '1', '0', '');
INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('profile_modify_url', '', 'string', '255', '0', '3', '2', '1', '0', '');

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
-- Dump dei dati per la tabella `learning_aggregated_cert_coursepath`
--



ALTER TABLE `learning_report_schedule`
	ADD COLUMN last_execution DATETIME NULL DEFAULT NULL;