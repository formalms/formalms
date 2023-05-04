INSERT IGNORE INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES
	('purchase_user', '', 'string', 255, 'ecommerce', 4, 18, 1, 0, '');


INSERT IGNORE INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) 
VALUES
	(51, 'PurchaseCourse', 'lms', NULL);

INSERT IGNORE INTO `core_event_manager` (`idEventMgr`, `idClass`, `permission`, `channel`, `recipients`, `show_level`) 
VALUES
    ( 51, 51, 'mandatory', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user');

INSERT IGNORE INTO `core_event_consumer_class` (`idConsumer`, `idClass`) 
VALUES
	(1,51);


