-- Add translation for Super-Administrators --
INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_RECIPIENTS_GOD', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','Super-Administrators', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','Super-Administrators', now());

-- User change his data --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserModSuperAdmin', 'framework', '');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_GOD', 'godadmin');

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserModSuperAdmin', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been modified', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User has been modified', now());


INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_MOD_USER_SBJ', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been modified', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User has been modified', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_MOD_USER_TEXT', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been modified in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User with name and surname : [firstname] [lastname] and userId : [username] has been modified in platform : [url]', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_MOD_USER_TEXT_SMS', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been modified in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User with name and surname : [firstname] [lastname] and userId : [username] has been modified in platform : [url]', now());


-- User suspended in forma --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserSuspendedSuperAdmin', 'framework', '');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_GOD', 'godadmin');

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserSuspendedSuperAdmin', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been suspended', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User has been suspended', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_SUSPENDED_USER_SBJ', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been suspended', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User has been suspended', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_SUSPENDED_USER_TEXT', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended in platform : [url]', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_SUSPENDED_USER_TEXT_SMS', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended in platform : [url]', now());



-- User changes node --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserModNodeSuperAdmin', 'framework', '');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_GOD', 'godadmin');

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserModNodeSuperAdmin', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User Node is changes', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User Node is changes', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CHANGE_NODE_USER_SBJ', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been change node', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User has been change node', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CHANGE_NODE_USER_TEXT', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been change node in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User with name and surname : [firstname] [lastname] and userId : [username] has been change node in platform : [url]', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CHANGE_NODE_USER_SBJ_SMS', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been change node in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User with name and surname : [firstname] [lastname] and userId : [username] has been change node in platform : [url]', now());


-- User suspended from a course --
SET @max = (SELECT MAX(idClass)+1 FROM `core_event_class`);

INSERT INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (@max, 'UserCourseSuspendedSuperAdmin', 'framework', '');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', @max);

INSERT INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES (@max, @max, 'not_used', 'email', '_EVENT_RECIPIENTS_TEACHER_GOD', 'godadmin,admin,user');

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserCourseSuspendedSuperAdmin', 'event_manager', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been suspended from a course', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User has been suspended from a courseexit', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_COURSE_EVENT_SUSPENDED_USER_SBJ', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User has been suspended', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User has been suspended', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_COURSE_EVENT_SUSPENDED_USER_TEXT', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended from course : [course] in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended from course : [course] in platform : [url]', now());


INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_COURSE_EVENT_SUSPENDED_USER_TEXT_SMS', 'email', '');
SET @lastID = LAST_INSERT_ID();

INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'english','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended from course : [course] in platform : [url]', now());
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (@lastID, 'italian','User with name and surname : [firstname] [lastname] and userId : [username] has been suspended from course : [course] in platform : [url]', now());