
-- task #14746
UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'google_stat_in_lms';
UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'google_stat_code';

-- task #14734
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'paypal_mail';
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'paypal_currency';
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'currency_symbol';
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'paypal_sandbox';

UPDATE `core_setting` SET `sequence` = '14' WHERE `core_setting`.`param_name` = 'paypal_mail';
UPDATE `core_setting` SET `sequence` = '15' WHERE `core_setting`.`param_name` = 'paypal_currency';
UPDATE `core_setting` SET `sequence` = '16' WHERE `core_setting`.`param_name` = 'currency_symbol';
UPDATE `core_setting` SET `sequence` = '17' WHERE `core_setting`.`param_name` = 'paypal_sandbox';

-- task #14736
DELETE FROM `core_lang_text` WHERE `core_lang_text`.`id_text` = (SELECT clt.`id_text` FROM (SELECT * FROM `core_lang_text`) AS clt WHERE clt.`text_key` = "_ASK_FOR_TREE_COURSE_CODE");

-- task #14734
UPDATE `core_setting` SET `regroup` = '1' WHERE `core_setting`.`param_name` = 'mail_sender';
UPDATE `core_setting` SET `regroup` = '1' WHERE `core_setting`.`param_name` = 'customer_help_email';
UPDATE `core_setting` SET `regroup` = '1' WHERE `core_setting`.`param_name` = 'customer_help_subj_pfx';

UPDATE `core_setting` SET `sequence` = '9' WHERE `core_setting`.`param_name` = 'mail_sender';
UPDATE `core_setting` SET `sequence` = '10' WHERE `core_setting`.`param_name` = 'customer_help_email';
UPDATE `core_setting` SET `param_load` = '11' WHERE `core_setting`.`param_name` = 'customer_help_subj_pfx';

-- task #14733
UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'ttlSession';

-- task #14747
UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'Clear_Twig_Cache';
UPDATE `core_setting` SET `sequence` = '27' WHERE `core_setting`.`param_name` = 'Clear_Twig_Cache';
UPDATE `core_setting` SET `pack` = 'twig_cache' WHERE `core_setting`.`param_name` = 'Clear_Twig_Cache';
INSERT INTO `core_lang_text` (`id_text`, `text_key`, `text_module`, `text_attributes`) VALUES (NULL, '_CLEAR_TWIG_CACHE', 'configuration', '');

INSERT IGNORE INTO `core_lang_translation` (`id_text`, `lang_code`, `translation_text`, `save_date`) VALUES ((SELECT `core_lang_text`.`id_text` FROM `core_lang_text` WHERE `core_lang_text`.`text_key` = "_CLEAR_TWIG_CACHE" ), 'english', 'Clear twig cache', '2017-10-16 17:49:29'), ((SELECT `core_lang_text`.`id_text` FROM `core_lang_text` WHERE `core_lang_text`.`text_key` = "_CLEAR_TWIG_CACHE" ), 'italian', 'Elimina twig cache', '2017-10-16 17:49:29');

-- task #14745
UPDATE `core_setting` SET `regroup` = '9' WHERE `core_setting`.`param_name` = 'ldap_server';
UPDATE `core_setting` SET `regroup` = '9' WHERE `core_setting`.`param_name` = 'ldap_user_string';
UPDATE `core_setting` SET `regroup` = '9' WHERE `core_setting`.`param_name` = 'ldap_used';
UPDATE `core_setting` SET `regroup` = '9' WHERE `core_setting`.`param_name` = 'ldap_port';

UPDATE `core_setting` SET `sequence` = '1' WHERE `core_setting`.`param_name` = 'ldap_port';
UPDATE `core_setting` SET `sequence` = '2' WHERE `core_setting`.`param_name` = 'ldap_used';
UPDATE `core_setting` SET `sequence` = '3' WHERE `core_setting`.`param_name` = 'ldap_server';
UPDATE `core_setting` SET `sequence` = '5' WHERE `core_setting`.`param_name` = 'sso_token';
UPDATE `core_setting` SET `sequence` = '6' WHERE `core_setting`.`param_name` = 'sso_secret';
UPDATE `core_setting` SET `sequence` = '7' WHERE `core_setting`.`param_name` = 'use_rest_api';
UPDATE `core_setting` SET `sequence` = '8' WHERE `core_setting`.`param_name` = 'rest_auth_code';
UPDATE `core_setting` SET `sequence` = '9' WHERE `core_setting`.`param_name` = 'rest_auth_method';
UPDATE `core_setting` SET `sequence` = '10' WHERE `core_setting`.`param_name` = 'rest_auth_update';
UPDATE `core_setting` SET `sequence` = '11' WHERE `core_setting`.`param_name` = 'rest_auth_api_key';
UPDATE `core_setting` SET `sequence` = '12' WHERE `core_setting`.`param_name` = 'rest_auth_lifetime';
UPDATE `core_setting` SET `sequence` = '13' WHERE `core_setting`.`param_name` = 'rest_auth_api_secret';

UPDATE `core_lang_translation` SET `translation_text` = 'API e Autenticazione' WHERE `core_lang_translation`.`id_text` IN (SELECT `core_lang_text`.`id_text` FROM `core_lang_text` WHERE `core_lang_text`.`text_key` = "_API_SSO") AND `core_lang_translation`.`lang_code` = 'english';
UPDATE `core_lang_translation` SET `translation_text` = 'API and Authentication' WHERE `core_lang_translation`.`id_text` IN (SELECT `core_lang_text`.`id_text` FROM `core_lang_text` WHERE `core_lang_text`.`text_key` = "_API_SSO") AND `core_lang_translation`.`lang_code` = 'italian';