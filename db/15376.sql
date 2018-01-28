-- removing ACTIVATE COURSE MENU
delete from learning_module where default_name = '_COURSE_AUTOREGISTRATION';


-- deleting the language translation key
delete from core_lang_translation where id_text = (select id_text from core_lang_text where text_key='_COURSE_AUTOREGISTRATION');

-- deleting the language  key
delete from core_lang_text where text_key='_COURSE_AUTOREGISTRATION';
