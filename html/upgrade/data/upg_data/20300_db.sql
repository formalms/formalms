-- Fix Event recipients in event management for UserCourseInserted
UPDATE `core_event_manager` SET `recipients`='_EVENT_RECIPIENTS_USER' WHERE idEventMgr = (SELECT core_event_class.idClass FROM core_event_class WHERE core_event_class.class = 'UserCourseInserted');

-- Add translation for Super-Administrators --
INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_RECIPIENTS_GOD', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','Super-Administrators', now());

-- User change his data --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserModSuperAdmin', 'framework', '');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_GOD', 'godadmin');

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserModSuperAdmin', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been modified', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_MOD_USER_SBJ', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been modified', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_MOD_USER_TEXT', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been modified in platform : [url]', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_MOD_USER_TEXT_SMS', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been modified in platform : [url]', now());

-- User registration from front-office to super admins --
/*INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserRegistrationSuperadmins', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User registered - superadmins', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','Utente registrato - superadmins', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_SUBSCRIBED_SUBJECT_SUPERADMINS', 'email', '');
SET @lastID = LAST_INSERT_ID();
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','A new user has registered', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','Un nuovo utente si è registrato', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_SUBSCRIBED_TEXT_SUPERADMINS', 'email', '');
SET @lastID = LAST_INSERT_ID();
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has registered in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','L\'utente con nome e cognome : [firstname] [lastname] e username : [username] si è registrato sulla piattaforma : [url]', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_SUBSCRIBED_TEXT_SMS_SUPERADMINS', 'email', '');
SET @lastID = LAST_INSERT_ID();
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has registered in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','L\'utente con nome e cognome : [firstname] [lastname] e username : [username] si è registrato sulla piattaforma : [url]', now());

INSERT INTO `core_event_class` (`class`, `platform`, `description`) VALUES ('UserRegistrationSuperadmins', 'lms-a', '');
set @lastID=LAST_INSERT_ID();

INSERT INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, @lastID);

INSERT INTO `core_event_manager` (`idClass`, `permission`, `channel`, `recipients`, `show_level`) 
VALUES (@lastID, 'mandatory', 'email', '_EVENT_RECIPIENTS_TEACHER', 'godadmin');*/

-- User subscribed in a course to moderators --
INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserCourseInsertedModerators', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User subscribed in a course - moderators', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','Utente inserito in un corso - moderatori', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_SUBSCRIBED_SUBJECT_MODERATORS', 'email', '');
SET @lastID = LAST_INSERT_ID();
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','A new user has been subscribed in a course', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','Un nuovo utente si è iscritto ad un corso', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_SUBSCRIBED_TEXT_MODERATORS', 'email', '');
SET @lastID = LAST_INSERT_ID();
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been subscribed in the course : [course] in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','L\'utente con nome e cognome : [firstname] [lastname] e username : [username] è stato iscritto al corso : [course] sulla piattaforma : [url]', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_SUBSCRIBED_TEXT_SMS_MODERATORS', 'email', '');
SET @lastID = LAST_INSERT_ID();
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been subscribed in the course : [course] in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','L\'utente con nome e cognome : [firstname] [lastname] e username : [username] è stato iscritto al corso : [course] sulla piattaforma : [url]', now());

INSERT INTO `core_event_class` (`class`, `platform`, `description`) VALUES ('UserCourseInsertedModerators', 'lms-a', '');
set @lastID=LAST_INSERT_ID();

INSERT INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, @lastID);

INSERT INTO `core_event_manager` (`idClass`, `permission`, `channel`, `recipients`, `show_level`) 
VALUES (@lastID, 'mandatory', 'email', '_EVENT_RECIPIENTS_TEACHER', 'admin');

-- User suspended in forma --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserSuspendedSuperAdmin', 'framework', '');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_GOD', 'godadmin');

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserSuspendedSuperAdmin', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been suspended', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_SUSPENDED_USER_SBJ', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been suspended', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_SUSPENDED_USER_TEXT', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended in platform : [url]', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_SUSPENDED_USER_TEXT_SMS', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended in platform : [url]', now());



-- User changes node --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserModNodeSuperAdmin', 'framework', '');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_GOD', 'godadmin');

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserModNodeSuperAdmin', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User Node is changes', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CHANGE_NODE_USER_SBJ', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been change node', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CHANGE_NODE_USER_TEXT', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been change node in platform : [url]', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CHANGE_NODE_USER_SBJ_SMS', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been change node in platform : [url]', now());


-- User suspended from a course --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserCourseSuspendedSuperAdmin', 'framework', '');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_TEACHER_GOD', 'godadmin,admin,user');

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserCourseSuspendedSuperAdmin', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been suspended from a course', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_COURSE_EVENT_SUSPENDED_USER_SBJ', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been suspended', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_COURSE_EVENT_SUSPENDED_USER_TEXT', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended from course : [course] in platform : [url]', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_COURSE_EVENT_SUSPENDED_USER_TEXT_SMS', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended from course : [course] in platform : [url]', now());

-- Certificate Assign permissions --
UPDATE core_menu_under SET associated_token = 'mod' WHERE default_name = '_CERTIFICATE' AND of_platform = 'alms';

SET @max = (SELECT MAX(idst)+1 FROM `core_role`);
INSERT INTO `core_role` (idst, roleid) VALUES (@max, '/lms/admin/certificate_assign/mod');
SET @idMenu = (SELECT MAX(idMenu)+1 FROM `core_menu`);
SET @manCertId = (SELECT idMenu FROM `core_menu` WHERE name = '_MAN_CERTIFICATE');
INSERT INTO `core_menu` (idMenu, name, image, sequence, is_active, collapse, idParent, of_platform) VALUES (@idMenu, '_CERTIFICATE_ASSIGN_STATUS', '', 3, 1, 1, @manCertId, 'framework');
INSERT INTO `core_menu_under` (idUnder, idMenu, module_name, default_name, default_op, associated_token, of_platform, sequence, class_file, class_name, mvc_path) VALUES (@idMenu, @idMenu, 'certificate_assign', '_CERTIFICATE_ASSIGN_STATUS', 'certificate_assign', 'view', 'alms', 3, 'class.certificate_assign.php', 'Module_Certificate_Assign', '');

SET @max = (SELECT MAX(idst)+1 FROM `core_role`);
INSERT INTO `core_role` (idst, roleid) VALUES (@max, '/lms/admin/certificate_release/mod');
SET @idMenu = (SELECT MAX(idMenu)+1 FROM `core_menu`);
SET @manCertId = (SELECT idMenu FROM `core_menu` WHERE name = '_MAN_CERTIFICATE');
INSERT INTO `core_menu` (idMenu, name, image, sequence, is_active, collapse, idParent, of_platform) VALUES (@idMenu, '_CERTIFICATE_RELEASE', '', 3, 1, 1, @manCertId, 'framework');
INSERT INTO `core_menu_under` (idUnder, idMenu, module_name, default_name, default_op, associated_token, of_platform, sequence, class_file, class_name, mvc_path) VALUES (@idMenu, @idMenu, 'certificate_release', '_CERTIFICATE_RELEASE', 'certificate_release', 'view', 'alms', 3, 'class.certificate_release.php', 'Module_Certificate_Release', '');