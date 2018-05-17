
-- Add lastedit and validity
ALTER TABLE `core_privacypolicy` ADD `lastedit_date` DATETIME NOT NULL AFTER `name`, ADD `validity_date` DATETIME NOT NULL AFTER `lastedit_date`;

-- Label Reset Policy
INSERT INTO `core_lang_text` (`id_text`, `text_key`, `text_module`, `text_attributes`) VALUES (NULL, '_RESET_POLICY', 'standard', '');
INSERT INTO core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_RESET_POLICY' and text_module = 'standard'), 'italian', 'Invalida le policy precedentemente accettate', now());
INSERT INTO core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_RESET_POLICY' and text_module = 'standard'), 'english', 'Reset Policy', now());


INSERT INTO `core_privacypolicy` (`id_policy`, `name`, `lastedit_date`, `validity_date`) VALUES(0, 'Default Privacy Policy', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `core_privacypolicy_lang` (`id_policy`, `lang_code`, `translation`) VALUES(0, 'english', 'Default Privacy Policy ENG');
INSERT INTO `core_privacypolicy_lang` (`id_policy`, `lang_code`, `translation`) VALUES(0, 'italian', 'Default Privacy Policy ITA');

CREATE TABLE `core_privacypolicy_user` (
  `id_policy` int(11) NOT NULL,
  `idst` int(11)  NOT NULL,
  `policy_date` datetime NOT NULL
);