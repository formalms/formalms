
-- Add lastedit and validity
ALTER TABLE `core_privacypolicy` ADD `lastedit_date` DATETIME NOT NULL AFTER `name`, ADD `validity_date` DATETIME NOT NULL AFTER `lastedit_date`;

-- Label Reset Policy
INSERT INTO `core_lang_text` (`id_text`, `text_key`, `text_module`, `text_attributes`) VALUES (NULL, '_RESET_POLICY', 'standard', '');
INSERT INTO core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_RESET_POLICY' and text_module = 'standard'), 'italian', 'Invalida le policy precedentemente accettate', now());
INSERT INTO core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_RESET_POLICY' and text_module = 'standard'), 'english', 'Reset Policy', now());