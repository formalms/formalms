-- Label _SEND_LINK_RESET_PASSWORD
INSERT INTO `core_lang_text` (`id_text`, `text_key`, `text_module`, `text_attributes`) VALUES (NULL, '_SEND_LINK_RESET_PASSWORD', 'register', '');

INSERT INTO core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_SEND_LINK_RESET_PASSWORD' and text_module = 'register'), 'italian', 'Invia una email con il link per resettare la password', now());

INSERT INTO core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_SEND_LINK_RESET_PASSWORD' and text_module = 'register'), 'english', 'Send an email with the link to reset password', now());