--- bug #491
INSERT IGNORE INTO core_event_class (idClass, class, platform, description) VALUES ('19', 'UserCourseRemovedModerate', 'lms-a', '');
INSERT IGNORE INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', '19');
INSERT IGNORE INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES ('19', '19', 'not_used', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin');

-- User waiting to be unsubscribed from a course
INSERT IGNORE INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserCourseRemovedModerate', 'event_manager', '');
INSERT IGNORE INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES
( (SELECT lt.id_text from core_lang_text lt where t.text_key = '_EVENT_CLASS_UserCourseRemovedModerate' AND lt.text_module = 'event_manager'), 'english', 'User waiting to be unsubscribed from a course', now());

-- User is waiting for course unsubscription approval
INSERT IGNORE INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_SUBJECT', 'email', '');
INSERT IGNORE INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES
( (SELECT lt.id_text from core_lang_text lt where t.text_key = '_NEW_USER_UNSUBS_WAITING_SUBJECT' AND lt.text_module = 'email'), 'english','User is waiting for course unsubscription approval', now());

-- New user unsubscribed to the '[course]' course and is waiting to be approved. <a href="[url]">[url]</a>
INSERT IGNORE INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_TEXT', 'email', '');
INSERT IGNORE INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES
((SELECT lt.id_text from core_lang_text lt where t.text_key = '_NEW_USER_UNSUBS_WAITING_TEXT' AND lt.text_module = 'email'), 'english','New user unsubscribed to the \'[course]\' course and is waiting to be approved.

<a href="[url]">[url]</a>', now());

INSERT IGNORE INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_SUBJECT_SMS', 'email', '');
INSERT IGNORE INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES
( (SELECT lt.id_text from core_lang_text lt where t.text_key = '_NEW_USER_UNSUBS_WAITING_SUBJECT_SMS' AND lt.text_module = 'email'), 'english','', now());

INSERT IGNORE INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_TEXT_SMS', 'email', '');
INSERT IGNORE INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES
( (SELECT lt.id_text from core_lang_text lt where t.text_key = '_NEW_USER_UNSUBS_WAITING_TEXT_SMS' AND lt.text_module = 'email'), 'english','', now() );


-- bug #2555
INSERT IGNORE INTO `learning_middlearea` (`obj_index`, `disabled`, `idst_list`, `sequence`) VALUES ('tb_kb', 0, 'a:0:{}', 0);

-- bug #1075
INSERT IGNORE INTO `core_event_manager` (`idEventMgr`, `idClass`, `permission`, `channel`, `recipients`, `show_level`) VALUES (41, 41, 'mandatory', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin');
INSERT IGNORE INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES (1, 41);
INSERT IGNORE INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES (41, 'UserNewWaiting', 'framework', '');

-- bug #1104
INSERT IGNORE INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_PROGRESS_COMPLETED', 'course', '');
INSERT IGNORE INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
FROM core_lang_text lt , core_lang_language l ,
( SELECT lt.id_text, t.lang_code, t.translation_text
  FROM   core_lang_text lt, core_lang_translation t
  WHERE t.id_text = lt.id_text AND lt.text_key ='_COMPLETED'  AND lt.text_module = 'standard'  ) t1
WHERE lt.text_key = '_PROGRESS_COMPLETED' AND lt.text_module = 'course' AND t1.lang_code = l.lang_code
;

INSERT IGNORE INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_PROGRESS_INCOMPLETE', 'course', '');
INSERT IGNORE INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
FROM core_lang_text lt , core_lang_language l ,
( SELECT lt.id_text, t.lang_code, t.translation_text
  FROM   core_lang_text lt, core_lang_translation t
  WHERE t.id_text = lt.id_text AND lt.text_key ='incomplete'  AND lt.text_module = 'standard'  ) t1
WHERE lt.text_key = '_PROGRESS_INCOMPLETE' AND lt.text_module = 'course' AND t1.lang_code = l.lang_code
;

INSERT IGNORE INTO `core_lang_text` (`id_text` ,`text_key` ,`text_module` ,`text_attributes`) VALUES (NULL , '_PROGRESS_PASSED', 'course', '');
INSERT IGNORE INTO `core_lang_translation` ( `id_text`, `lang_code`,  `translation_text`, `save_date` )
SELECT lt.id_text, l.lang_code, t1.translation_text as translation_text, now() AS save_date
FROM core_lang_text lt , core_lang_language l ,
( SELECT lt.id_text, t.lang_code, t.translation_text
  FROM   core_lang_text lt, core_lang_translation t
  WHERE t.id_text = lt.id_text AND lt.text_key ='_PASSED'  AND lt.text_module = 'coursereport'  ) t1
WHERE lt.text_key = '_PROGRESS_PASSED' AND lt.text_module = 'course' AND t1.lang_code = l.lang_code
;




