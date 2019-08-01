-- Fix Event recipients in event management for UserCourseInserted
UPDATE `core_event_manager` SET `recipients`='_EVENT_RECIPIENTS_USER' WHERE idEventMgr = (SELECT core_event_class.idClass FROM core_event_class WHERE core_event_class.class = 'UserCourseInserted');

-- User change his data --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT IGNORE INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserModSuperAdmin', 'framework', '');

INSERT IGNORE INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT IGNORE INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_GOD', 'godadmin');

INSERT IGNORE INTO `core_event_class` (`class`, `platform`, `description`) VALUES ('UserCourseInsertedModerators', 'lms-a', '');
set @lastID=LAST_INSERT_ID();

INSERT IGNORE INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, @lastID);

INSERT IGNORE INTO `core_event_manager` (`idClass`, `permission`, `channel`, `recipients`, `show_level`) 
VALUES (@lastID, 'mandatory', 'email', '_EVENT_RECIPIENTS_TEACHER', 'admin');

-- User suspended in forma --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT IGNORE INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserSuspendedSuperAdmin', 'framework', '');

INSERT IGNORE INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT IGNORE INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_GOD', 'godadmin');

-- User changes node --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT IGNORE INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserModNodeSuperAdmin', 'framework', '');

INSERT IGNORE INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT IGNORE INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_GOD', 'godadmin');

-- User suspended from a course --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT IGNORE INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserCourseSuspendedSuperAdmin', 'framework', '');

INSERT IGNORE INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT IGNORE INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_TEACHER_GOD', 'godadmin,admin,user');

-- User registration SuperAdmins --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT IGNORE INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserRegistrationSuperadmins', 'lms-a', '');

INSERT IGNORE INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT IGNORE INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'mandatory', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'admin');

-- Certificate Assign permissions --
UPDATE core_menu_under SET associated_token = 'mod' WHERE default_name = '_CERTIFICATE' AND of_platform = 'alms';

SET @max = (SELECT MAX(idst)+1 FROM `core_role`);
INSERT IGNORE INTO `core_role` (idst, roleid) VALUES (@max, '/lms/admin/certificate/assign');

SET @max = (SELECT MAX(idst)+2 FROM `core_role`);
INSERT IGNORE INTO `core_role` (idst, roleid) VALUES (@max, '/lms/admin/certificate/release');