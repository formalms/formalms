-- # 14043

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
('home_page_option', 'catalogue', 'home_page_option', 255, '0', 4, 1, 1, 0, '');

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
('hide_empty_category', 'on', 'enum', 3, '0', 4, 5, 1, 0, '');

DELETE from core_setting where param_name = 'first_catalogue';

update core_setting set sequence = 2 where param_name = 'on_usercourse_empty'; 
update core_setting set sequence = 3 where param_name = 'tablist_mycourses'; 
update core_setting set sequence = 4 where param_name = 'on_catalogue_empty'; 
update core_setting set sequence = 6 where param_name = 'use_tag'; 
update core_setting set sequence = 7 where param_name = 'course_quota';
update core_setting set sequence = 8 where param_name = 'no_answer_in_test';
update core_setting set sequence = 9 where param_name = 'no_answer_in_poll';
update core_setting set sequence = 10 where param_name = 'tracking';
update core_setting set sequence = 11 where param_name = 'kb_filter_by_user_access';
update core_setting set sequence = 12 where param_name = 'kb_show_uncategorized';
update core_setting set sequence = 13 where param_name = 'course_block';




insert into core_lang_text (text_key, text_module, text_attributes) VALUES ('_HOME_PAGE', 'configuration', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_HOME_PAGE' and text_module = 'configuration'), 'italian', 'Home page', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_HOME_PAGE' and text_module = 'configuration'), 'english', 'Home page', now());

--

insert into core_lang_text (text_key, text_module, text_attributes) VALUES ('_MY_COURSES', 'configuration', '');

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_MY_COURSES' and text_module = 'configuration'), 'italian', 'I miei corsi', now());

insert into core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_MY_COURSES' and text_module = 'configuration'), 'english', 'My courses', now());


update core_lang_translation set translation_text = 'Attiva elenco corsi in pagina di login' where translation_text = 'Attiva elenco corsi in home page';
update core_lang_translation set translation_text = 'Show catalogue in login page' where translation_text = 'Show catalogue in home page';