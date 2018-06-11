
-- Add lastedit and validity
ALTER TABLE `core_privacypolicy` ADD `lastedit_date` DATETIME NOT NULL AFTER `name`, ADD `validity_date` DATETIME NOT NULL AFTER `lastedit_date`;

-- Add is_default
ALTER TABLE `core_privacypolicy` ADD `is_default` INT(1) NOT NULL DEFAULT '0' AFTER `name`;

-- Label Reset Policy
INSERT INTO `core_lang_text` (`id_text`, `text_key`, `text_module`, `text_attributes`) VALUES (NULL, '_RESET_POLICY', 'standard', '');
INSERT INTO core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_RESET_POLICY' and text_module = 'standard'), 'italian', 'Invalida le policy precedentemente accettate', now());
INSERT INTO core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_RESET_POLICY' and text_module = 'standard'), 'english', 'Reset Policy', now());

-- Label Set as default Policy
INSERT INTO `core_lang_text` (`id_text`, `text_key`, `text_module`, `text_attributes`) VALUES (NULL, '_SET_AS_DEFAULT', 'standard', '');
INSERT INTO core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_SET_AS_DEFAULT' and text_module = 'standard'), 'italian', 'Setta come predefinito', now());
INSERT INTO core_lang_translation (id_text, lang_code, translation_text, save_date) 
VALUES ((SELECT id_text FROM core_lang_text where text_key = '_SET_AS_DEFAULT' and text_module = 'standard'), 'english', 'Set as Default', now());


INSERT INTO `core_privacypolicy` (`name`, `is_default`, `lastedit_date`, `validity_date`) VALUES( 'Default Privacy Policy', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00');
SET @v_idst=LAST_INSERT_ID();
INSERT INTO `core_privacypolicy_lang` (`id_policy`, `lang_code`, `translation`) VALUES( @v_idst, 'english', 'Default Privacy Policy ENGLISH');
INSERT INTO `core_privacypolicy_lang` (`id_policy`, `lang_code`, `translation`) VALUES( @v_idst, 'italian', 'Default Privacy Policy ITALIAN');

CREATE TABLE `core_privacypolicy_user` (
  `id_policy` int(11) NOT NULL,
  `idst` int(11)  NOT NULL,
  `accept_date` datetime NOT NULL
);