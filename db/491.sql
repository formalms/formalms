
INSERT INTO core_event_class (idClass, class, platform, description) VALUES ('19', 'UserCourseRemovedModerate', 'lms-a', '');

INSERT INTO core_event_consumer_class (idConsumer, idClass) VALUES ('1', '19');

INSERT INTO core_event_manager (idEventMgr, idClass, permission, channel, recipients, show_level) VALUES ('19', '19', 'not_used', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin');

-- User waiting to be unsubscribed from a course
INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_EVENT_CLASS_UserCourseRemovedModerate', 'event_manager', '');
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (LAST_INSERT_ID(), 'english','User waiting to be unsubscribed from a course', now());

-- User is waiting for course unsubscription approval
INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_SUBJECT', 'email', '');
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (LAST_INSERT_ID(), 'english','User is waiting for course unsubscription approval', now());

-- New user unsubscribed to the '[course]' course and is waiting to be approved. <a href="[url]">[url]</a>
INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_TEXT', 'email', '');
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (LAST_INSERT_ID(), 'english','New user unsubscribed to the \'[course]\' course and is waiting to be approved.

<a href="[url]">[url]</a>', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_SUBJECT_SMS', 'email', '');
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (LAST_INSERT_ID(), 'english','', now());

INSERT INTO core_lang_text (id_text, text_key, text_module, text_attributes) VALUES (NULL, '_NEW_USER_UNSUBS_WAITING_TEXT_SMS', 'email', '');
INSERT INTO core_lang_translation ( id_text,lang_code,  translation_text, save_date ) VALUES  (LAST_INSERT_ID(), 'english','', now());
