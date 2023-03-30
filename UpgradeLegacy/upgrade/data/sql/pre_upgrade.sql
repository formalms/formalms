SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- Drop ----------------------------------------------------------------------------------------------------------------

DROP TABLE `cms_area`,`cms_area_block`, `cms_area_block_filter`, `cms_area_block_forum`, `cms_area_block_group`, `cms_area_block_items`, `cms_area_block_simpleprj`, `cms_area_option`, `cms_area_option_text`, `cms_area_perm`, `cms_area_subdivision`, `cms_banner`, `cms_banner_cat`, `cms_banner_raw_stat`, `cms_banner_rules`, `cms_blocktype`, `cms_calendar`, `cms_calendar_item`, `cms_comment_ajax`, `cms_content`, `cms_content_attach`, `cms_content_dir`, `cms_content_titles`, `cms_docs`, `cms_docs_dir`, `cms_docs_info`, `cms_docs_titles`, `cms_form`, `cms_form_items`, `cms_form_map`, `cms_form_sendinfo`, `cms_form_storage`, `cms_form_textarea`, `cms_forum`, `cms_forummessage`, `cms_forumthread`, `cms_forum_access`, `cms_forum_notifier`, `cms_forum_timing`, `cms_links`, `cms_links_dir`, `cms_links_info`, `cms_links_titles`, `cms_media`, `cms_media_dir`, `cms_media_info`, `cms_media_titles`, `cms_menu`, `cms_menu_under`, `cms_news`, `cms_news_attach`, `cms_news_dir`, `cms_news_titles`, `cms_news_topic`, `cms_poll`, `cms_poll_answer`, `cms_poll_vote`, `cms_setting`, `cms_simpleprj`, `cms_simpleprj_file`, `cms_simpleprj_task`, `cms_sysforum`, `cms_text`, `cms_topic`, `cms_tree_perm`,

`crm_contact_history`, `crm_contact_reason`, `crm_contact_user`, `crm_file`, `crm_menu`, `crm_menu_under`, `crm_note`, `crm_project`, `crm_publicmenu_under`, `crm_public_menu`, `crm_setting`, `crm_sysforum`, `crm_task`, `crm_ticket`, `crm_ticket_msg`, `crm_ticket_status`, `crm_todo`,

`ecom_menu`, `ecom_menu_under`, `ecom_paramset_fieldgrp`, `ecom_paramset_grpitem`, `ecom_payaccount`, `ecom_payaccount_setting`, `ecom_product`, `ecom_product_cat`, `ecom_product_cat_info`, `ecom_product_cat_item`, `ecom_product_field`, `ecom_product_field_entry`, `ecom_product_img`, `ecom_product_info`, `ecom_reservation`, `ecom_setting`, `ecom_tax_cat_god`, `ecom_tax_rate`, `ecom_tax_zone`, `ecom_transaction`, `ecom_transaction_product`, `conference_inte_room`, `conference_inte_token`, `conference_inte_user`, `core_company`, `core_company_field`, `core_company_fieldentry`, `core_company_user`, `core_field_template`, `learning_coursepath_slot`, `learning_eportfolio`, `learning_eportfolio_competence`, `learning_eportfolio_competence_invite`, `learning_eportfolio_competence_score`, `learning_eportfolio_curriculum`, `learning_eportfolio_member`, `learning_eportfolio_pdp`, `learning_eportfolio_pdp_answer`, `learning_eportfolio_presentation`, `learning_eportfolio_presentation_attach`, `learning_eportfolio_presentation_invite`, `learning_forum_sema`, `learning_pagel`, `learning_pagel_atvt`, `learning_pagel_sema`, `learning_pagel_sema_items`, `learning_pagel_vote`, `learning_pagel_vote_items`, `learning_public_report_admin`,

`core_companystatus`, `core_companytype`, `core_feed_cache`, `core_feed_out`,

`learning_assessment_rules`, `learning_course_point`,

`core_faq`, `core_faq_cat`, `core_org_chart_user`, `core_pflow_lang`, `core_pflow_list`, `core_pflow_step`, `core_resource`, `core_resource_timetable`, `core_sysforum`, `core_user_friend`, `core_user_myfiles`;

-- Create --------------------------------------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `conference_teleskill_log` (
  `roomid` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `role` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `duration` int(11) NOT NULL DEFAULT '0',
  `access` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`roomid`,`idUser`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_code` (
  `code` varchar(255) NOT NULL DEFAULT '',
  `idCodeGroup` int(11) NOT NULL DEFAULT '0',
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `idUser` int(11) DEFAULT NULL,
  `unlimitedUse` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_code_association` (
  `code` varchar(255) NOT NULL DEFAULT '',
  `idUser` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`,`idUser`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_code_course` (
  `idCodeGroup` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCodeGroup`,`idCourse`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_code_groups` (
  `idCodeGroup` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  PRIMARY KEY (`idCodeGroup`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_code_org` (
  `idCodeGroup` int(11) NOT NULL DEFAULT '0',
  `idOrg` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCodeGroup`,`idOrg`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_rules` (
  `id_rule` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `rule_type` varchar(10) NOT NULL DEFAULT '',
  `creation_date` date NOT NULL DEFAULT '0000-00-00',
  `rule_active` tinyint(1) NOT NULL DEFAULT '0',
  `course_list` text NOT NULL,
  PRIMARY KEY (`id_rule`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

INSERT INTO `core_rules` (`id_rule`, `title`, `lang_code`, `rule_type`, `creation_date`, `rule_active`, `course_list`) VALUES
(1, '', 'all', 'base', '0000-00-00', 1, '');

CREATE TABLE IF NOT EXISTS `core_rules_entity` (
  `id_rule` int(11) NOT NULL DEFAULT '0',
  `id_entity` varchar(50) NOT NULL DEFAULT '',
  `course_list` text NOT NULL,
  PRIMARY KEY (`id_rule`,`id_entity`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_rules_log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `log_action` varchar(255) NOT NULL DEFAULT '',
  `log_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `applied` text NOT NULL,
  PRIMARY KEY (`id_log`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_certificate_meta` (
  `idMetaCertificate` int(11) NOT NULL AUTO_INCREMENT,
  `idCertificate` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`idMetaCertificate`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_certificate_meta_assign` (
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idMetaCertificate` int(11) NOT NULL DEFAULT '0',
  `idCertificate` int(11) NOT NULL DEFAULT '0',
  `on_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cert_file` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`idUser`,`idMetaCertificate`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_certificate_meta_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMetaCertificate` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `idCourseEdition` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_communication_category` (
  `id_category` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int(11) unsigned NOT NULL DEFAULT '0',
  `level` int(11) unsigned NOT NULL DEFAULT '0',
  `iLeft` int(11) unsigned NOT NULL DEFAULT '0',
  `iRight` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_category`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_communication_category_lang` (
  `id_category` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `translation` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_category`,`lang_code`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_label` (
  `id_common_label` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_common_label`,`lang_code`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_label_course` (
  `id_common_label` int(11) NOT NULL DEFAULT '0',
  `id_course` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_common_label`,`id_course`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_privacypolicy` (
  `id_policy` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_policy`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_privacypolicy_lang` (
  `id_policy` int(11) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `translation` text NOT NULL,
  PRIMARY KEY (`id_policy`,`lang_code`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_fncrole` (
  `id_fncrole` int(10) unsigned NOT NULL DEFAULT '0',
  `id_group` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fncrole`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_fncrole_competence` (
  `id_fncrole` int(10) unsigned NOT NULL DEFAULT '0',
  `id_competence` int(10) unsigned NOT NULL DEFAULT '0',
  `score` int(10) unsigned NOT NULL DEFAULT '0',
  `expiration` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fncrole`,`id_competence`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_fncrole_group` (
  `id_group` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id_group`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_fncrole_group_lang` (
  `id_group` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id_group`,`lang_code`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_fncrole_lang` (
  `id_fncrole` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id_fncrole`,`lang_code`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_password_history` (
  `idst_user` int(11) NOT NULL DEFAULT '0',
  `pwd_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `passw` varchar(100) NOT NULL DEFAULT '',
  `changed_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idst_user`,`pwd_date`),
  KEY `pwd_date` (`pwd_date`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_setting_group` (
  `path_name` varchar(255) NOT NULL DEFAULT '',
  `idst` int(11) NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (`path_name`,`idst`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_transaction` (
  `id_trans` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `location` varchar(10) NOT NULL DEFAULT '',
  `date_creation` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_activated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_trans`),
  KEY `id_user` (`id_user`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `core_transaction_info` (
  `id_trans` int(11) NOT NULL DEFAULT '0',
  `id_course` int(11) NOT NULL DEFAULT '0',
  `id_date` int(11) NOT NULL DEFAULT '0',
  `id_edition` int(11) NOT NULL DEFAULT '0',
  `code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `price` varchar(255) NOT NULL DEFAULT '',
  `activated` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_trans`,`id_course`,`id_date`,`id_edition`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_assessment_rule` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `from_score` double NOT NULL DEFAULT '0',
  `to_score` double NOT NULL DEFAULT '0',
  `competences_list` text,
  `courses_list` text,
  `feedback_txt` text,
  PRIMARY KEY (`rule_id`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_communication` (
  `id_comm` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `publish_date` date NOT NULL DEFAULT '0000-00-00',
  `type_of` varchar(15) NOT NULL DEFAULT '',
  `id_resource` int(11) NOT NULL DEFAULT '0',
  `id_category` int(11) unsigned NOT NULL DEFAULT '0',
  `id_course` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_comm`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_communication_access` (
  `id_comm` int(11) NOT NULL DEFAULT '0',
  `idst` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_comm`,`idst`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_communication_track` (
  `idReference` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idTrack` int(11) NOT NULL DEFAULT '0',
  `objectType` varchar(20) NOT NULL DEFAULT '',
  `dateAttempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(20) NOT NULL DEFAULT '',
  `firstAttempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`idReference`,`idUser`),
  KEY `idReference` (`idReference`),
  KEY `idUser` (`idUser`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_competence_category_lang` (
  `id_category` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id_category`,`lang_code`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_competence_lang` (
  `id_competence` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id_competence`,`lang_code`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_course_date` (
  `id_date` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_course` int(10) unsigned NOT NULL DEFAULT '0',
  `code` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `max_par` int(11) NOT NULL DEFAULT '0',
  `price` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `overbooking` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `test_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  `medium_time` int(11) NOT NULL DEFAULT '0',
  `sub_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sub_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `unsubscribe_date_limit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_date`),
  KEY `id_course` (`id_course`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

INSERT INTO `learning_course_date` (`id_date`, `id_course`, `code`, `name`, `description`, `status`, `max_par`, `price`, `overbooking`, `sub_start_date`, `sub_end_date`)
SELECT `idCourseEdition`, `idCourse`, `code`, `name`, `description`, `status`, `max_num_subscribe`, `price`, `allow_overbooking`, `sub_start_date`, `sub_end_date`
FROM learning_course_edition
WHERE edition_type = 'blended' OR edition_type = 'classroom';

CREATE TABLE IF NOT EXISTS `learning_course_date_day` (
  `id_day` int(11) NOT NULL DEFAULT '0',
  `id_date` int(11) NOT NULL DEFAULT '0',
  `classroom` int(11) unsigned NOT NULL DEFAULT '0',
  `date_begin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pause_begin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pause_end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_day`,`id_date`),
  KEY `id_date` (`id_date`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

UPDATE learning_course_edition
SET hour_begin = '00:00:00'
WHERE hour_begin = '-1';

UPDATE learning_course_edition
SET hour_end = '00:00:00'
WHERE hour_end = '-1';

INSERT INTO `learning_course_date_day` (`id_day`, `id_date`, `classroom`, `date_begin`, `date_end`)
SELECT 0, `idCourseEdition`, `classrooms`, CONCAT( date_begin, ' ', hour_begin ), CONCAT(date_begin, ' ', hour_end)
FROM learning_course_edition
WHERE edition_type = 'blended' OR edition_type = 'classroom';


INSERT INTO `learning_course_date_day` (`id_day`, `id_date`, `classroom`, `date_begin`, `date_end`)
SELECT 1, `idCourseEdition`, `classrooms`, CONCAT( date_end, ' ', hour_begin ), CONCAT(date_end, ' ', hour_end)
FROM learning_course_edition
WHERE edition_type = 'blended' OR edition_type = 'classroom';

CREATE TABLE IF NOT EXISTS `learning_course_date_presence` (
  `day` date NOT NULL DEFAULT '0000-00-00',
  `id_date` int(11) unsigned NOT NULL DEFAULT '0',
  `id_user` int(11) unsigned NOT NULL DEFAULT '0',
  `id_day` int(11) unsigned NOT NULL DEFAULT '0',
  `presence` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `score` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`day`,`id_date`,`id_user`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_course_date_user` (
  `id_date` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `date_subscription` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_complete` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `presence` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `subscribed_by` int(10) unsigned NOT NULL DEFAULT '0',
  `overbooking` int(10) DEFAULT '0',
  `requesting_unsubscribe` tinyint(1) unsigned DEFAULT NULL,
  `requesting_unsubscribe_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_date`,`id_user`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

INSERT INTO `learning_course_date_user` (`id_date`, `id_user`, `date_subscription`, `date_complete`, `subscribed_by`)
SELECT cu.`edition_id`, cu.`idUser`, `date_inscr`, `date_complete`, `subscribed_by`
FROM learning_courseuser AS cu JOIN learning_course_date AS cd
	ON (cu.idCourse = cd.id_course AND cu.edition_id = cd.id_date);

CREATE TABLE IF NOT EXISTS `learning_course_editions` (
  `id_edition` int(11) NOT NULL AUTO_INCREMENT,
  `id_course` int(11) NOT NULL DEFAULT '0',
  `code` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `date_begin` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `max_num_subscribe` int(11) NOT NULL DEFAULT '0',
  `min_num_subscribe` int(11) NOT NULL DEFAULT '0',
  `price` varchar(255) NOT NULL DEFAULT '',
  `overbooking` tinyint(1) NOT NULL DEFAULT '0',
  `can_subscribe` tinyint(1) NOT NULL DEFAULT '0',
  `sub_date_begin` date NOT NULL DEFAULT '0000-00-00',
  `sub_date_end` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id_edition`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

INSERT INTO `learning_course_editions` (`id_edition`, `id_course`, `code`, `name`, `description`, `status`, `date_begin`, `date_end`, `max_num_subscribe`, `min_num_subscribe`, `price`, `overbooking`, `can_subscribe`, `sub_date_begin`, `sub_date_end`)
SELECT `idCourseEdition`, `idCourse`, `code`, `name`, `description`, `status`, `date_begin`, `date_end`, `max_num_subscribe`, `min_num_subscribe`, `price`, `allow_overbooking`, `can_subscribe`, `sub_start_date`, `sub_end_date`
FROM learning_course_edition
WHERE edition_type = 'elearning';

CREATE TABLE IF NOT EXISTS `learning_course_editions_user` (
  `id_edition` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `date_subscription` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_complete` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `subscribed_by` int(10) unsigned NOT NULL DEFAULT '0',
  `requesting_unsubscribe` tinyint(1) unsigned DEFAULT NULL,
  `requesting_unsubscribe_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_edition`,`id_user`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

INSERT INTO `learning_course_editions_user` (`id_edition`, `id_user`, `date_subscription`, `date_complete`, `subscribed_by`)
SELECT cu.`edition_id`, cu.`idUser`, `date_inscr`, `date_complete`, `subscribed_by`
FROM learning_courseuser AS cu JOIN learning_course_editions AS ce
	ON (cu.idCourse = ce.id_course AND cu.edition_id = ce.id_edition);

CREATE TABLE IF NOT EXISTS `learning_games` (
  `id_game` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `start_date` date NOT NULL DEFAULT '0000-00-00',
  `end_date` date NOT NULL DEFAULT '0000-00-00',
  `type_of` varchar(15) NOT NULL DEFAULT '',
  `id_resource` int(11) NOT NULL DEFAULT '0',
  `play_chance` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_game`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_games_access` (
  `id_game` int(11) NOT NULL DEFAULT '0',
  `idst` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_game`,`idst`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_games_track` (
  `idReference` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idTrack` int(11) NOT NULL DEFAULT '0',
  `objectType` varchar(20) NOT NULL DEFAULT '',
  `dateAttempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(20) NOT NULL DEFAULT '',
  `firstAttempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `current_score` int(11) DEFAULT NULL,
  `max_score` int(11) DEFAULT NULL,
  `num_attempts` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idTrack`,`objectType`),
  KEY `idReference` (`idReference`),
  KEY `idUser` (`idUser`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_kb_rel` (
  `res_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` varchar(45) NOT NULL DEFAULT '',
  `rel_type` enum('tag','folder') NOT NULL DEFAULT 'tag',
  PRIMARY KEY (`res_id`,`parent_id`,`rel_type`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_kb_res` (
  `res_id` int(11) NOT NULL AUTO_INCREMENT,
  `r_name` varchar(255) NOT NULL DEFAULT '',
  `original_name` varchar(255) DEFAULT NULL,
  `r_desc` text,
  `r_item_id` int(11) NOT NULL DEFAULT '0',
  `r_type` varchar(45) NOT NULL DEFAULT '',
  `r_env` varchar(45) NOT NULL DEFAULT '',
  `r_env_parent_id` int(11) DEFAULT NULL,
  `r_param` varchar(255) DEFAULT NULL,
  `r_alt_desc` varchar(255) DEFAULT NULL,
  `r_lang` varchar(50) NOT NULL DEFAULT '',
  `force_visible` tinyint(1) NOT NULL DEFAULT '0',
  `is_mobile` tinyint(1) NOT NULL DEFAULT '0',
  `sub_categorize` tinyint(1) NOT NULL DEFAULT '-1',
  `is_categorized` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`res_id`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_kb_tag` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tag_id`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_kb_tree` (
  `node_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `lev` int(11) NOT NULL DEFAULT '0',
  `iLeft` int(11) NOT NULL DEFAULT '0',
  `iRight` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`node_id`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_kb_tree_info` (
  `id_dir` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(50) NOT NULL DEFAULT '',
  `node_title` varchar(255) NOT NULL DEFAULT '',
  `node_desc` text
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_time_period` (
  `id_period` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) NOT NULL DEFAULT '',
  `start_date` date NOT NULL DEFAULT '0000-00-00',
  `end_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id_period`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_transaction` (
  `id_transaction` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_confirm` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `price` int(11) NOT NULL DEFAULT '0',
  `payment_status` tinyint(1) NOT NULL DEFAULT '0',
  `course_status` tinyint(1) NOT NULL DEFAULT '0',
  `method` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `payment_note` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `course_note` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_transaction`),
  KEY `id_user` (`id_user`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE TABLE IF NOT EXISTS `learning_transaction_info` (
  `id_transaction` int(11) NOT NULL DEFAULT '0',
  `id_course` int(11) NOT NULL DEFAULT '0',
  `id_date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_transaction`,`id_course`,`id_date`)
) ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

-- Structure -----------------------------------------------------------------------------------------------------------


ALTER TABLE  `conference_dimdim` ADD  `schedule_info` TEXT NOT NULL, ADD  `extra_conf` TEXT NOT NULL;

ALTER TABLE  `conference_room` ADD  `bookable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE  `core_deleted_user` CHANGE  `lasteneter`  `lastenter` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00', CHANGE  `deleted_bt`  `deleted_by` INT( 11 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `core_event_manager` CHANGE  `permission`  `permission` ENUM(  'not_used',  'mandatory' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'not_used';

DELETE FROM `core_field_type` WHERE `type_field` = 'gmail';
DELETE FROM `core_field_type` WHERE `type_field` = 'icq';
DELETE FROM `core_field_type` WHERE `type_field` = 'msn';
DELETE FROM `core_field_type` WHERE `type_field` = 'skype';
DELETE FROM `core_field_type` WHERE `type_field` = 'yahoo';

INSERT INTO  `core_field_type` (`type_field` ,`type_file` ,`type_class` ,`type_category`)
VALUES ('country',  'class.country.php',  'Field_Country',  'standard');

ALTER TABLE  `core_field_son` CHANGE  `sequence`  `sequence` INT( 11 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `core_lang_language` DROP  `lang_charset`;

DROP TABLE `core_lang_text` ;
DROP TABLE `core_lang_translation` ;

CREATE TABLE IF NOT EXISTS `core_lang_text` (
  `id_text` int(11) NOT NULL AUTO_INCREMENT,
  `text_key` varchar(50) NOT NULL DEFAULT '',
  `text_module` varchar(50) NOT NULL DEFAULT '',
  `text_attributes` set('accessibility','sms','email') NOT NULL DEFAULT '',
  PRIMARY KEY (`id_text`),
  UNIQUE KEY `text_key` (`text_module`,`text_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `core_lang_translation` (
  `id_text` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(50) NOT NULL DEFAULT '',
  `translation_text` text,
  `save_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_text`,`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE  `core_task` CHANGE  `sequence`  `sequence` INT( 3 ) NOT NULL DEFAULT '0';

ALTER TABLE `core_menu_under` ADD COLUMN `mvc_path` VARCHAR(255) NOT NULL DEFAULT ''  AFTER `class_name` ;

ALTER TABLE `core_org_chart_tree` ADD COLUMN `code` VARCHAR(255) NOT NULL DEFAULT ''  AFTER `lev` , ADD COLUMN `iLeft` INT(5) NOT NULL DEFAULT '0'  AFTER `lev` , ADD COLUMN `iRight` INT(5) NOT NULL DEFAULT '0'  AFTER `iLeft` , ADD COLUMN `idst_oc` INT(11) NOT NULL DEFAULT '0' , ADD COLUMN `idst_ocd` INT(11) NOT NULL DEFAULT '0' , ADD COLUMN `associated_policy` INT(11) UNSIGNED NULL ;

ALTER TABLE `core_rest_authentication` COLLATE = utf8_general_ci , CHANGE COLUMN `id_user` `id_user` INT(11) NOT NULL DEFAULT '0'  , CHANGE COLUMN `user_level` `user_level` INT(11) NOT NULL DEFAULT '0'  , CHANGE COLUMN `token` `token` VARCHAR(255) NOT NULL DEFAULT ''  ;

ALTER TABLE  `core_setting` CHANGE  `value_type`  `value_type` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'string', CHANGE  `pack`  `pack` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'main';

ALTER TABLE  `core_tag` CHANGE  `id_parent`  `id_parent` INT( 11 ) NOT NULL DEFAULT  '0';

ALTER TABLE `core_tag_relation` CHANGE COLUMN `id_tag` `id_tag` INT(11) NOT NULL DEFAULT '0'  , CHANGE COLUMN `id_resource` `id_resource` INT(11) NOT NULL DEFAULT '0'  , CHANGE COLUMN `id_user` `id_user` INT(11) NOT NULL DEFAULT '0'  , CHANGE COLUMN `private` `private` TINYINT(1) NOT NULL DEFAULT '0'  , CHANGE COLUMN `id_course` `id_course` INT(11) NOT NULL DEFAULT '0'  ;

ALTER TABLE `core_tag_resource` CHANGE COLUMN `id_resource` `id_resource` INT(11) NOT NULL DEFAULT '0'  ;

ALTER TABLE `core_user` ADD COLUMN `force_change` TINYINT(1) NOT NULL DEFAULT '0' AFTER `pwd_expire_at`, ADD COLUMN `privacy_policy` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `pwd_expire_at`;

ALTER TABLE `core_user` ADD `facebook_id` VARCHAR( 255 ) NULL , ADD `twitter_id` VARCHAR( 255 ) NULL , ADD `linkedin_id` VARCHAR( 255 ) NULL , ADD `google_id` VARCHAR( 255 ) NULL ;

ALTER TABLE `core_user_temp` ADD `facebook_id` VARCHAR( 255 ) NULL , ADD `twitter_id` VARCHAR( 255 ) NULL , ADD `linkedin_id` VARCHAR( 255 ) NULL , ADD `google_id` VARCHAR( 255 ) NULL ;

ALTER TABLE  `core_user` ADD UNIQUE (`facebook_id`);

ALTER TABLE  `core_user` ADD UNIQUE (`google_id`);

ALTER TABLE  `core_user` ADD UNIQUE (`twitter_id`);

ALTER TABLE  `core_user` ADD UNIQUE (`linkedin_id`);

ALTER TABLE  `core_wiki_page` CHANGE  `page_code`  `page_code` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `learning_category` ADD COLUMN `iLeft` INT(5) NOT NULL DEFAULT '0'  AFTER `description` , ADD COLUMN `iRight` INT(5) NOT NULL DEFAULT '0'  AFTER `iLeft` , CHANGE COLUMN `idParent` `idParent` INT(11) NULL DEFAULT '0'  ;

ALTER TABLE `learning_certificate` ADD COLUMN `user_release` TINYINT(1) NOT NULL DEFAULT '0'  AFTER `meta`, CHANGE  `meta` `meta` TINYINT( 1 ) NOT NULL DEFAULT  '0';
UPDATE `learning_certificate` SET `user_release`=1 WHERE 1;

ALTER TABLE `learning_certificate_course` ADD `point_required` INT(11) NOT NULL DEFAULT '0';

ALTER TABLE `learning_commontrack` ADD COLUMN `first_complete` DATETIME AFTER `firstAttempt`, ADD COLUMN `last_complete` DATETIME AFTER `first_complete`;

ALTER TABLE `learning_competence` CHANGE COLUMN `competence_type` `typology` ENUM('skill','attitude','knowledge') NOT NULL DEFAULT 'skill', CHANGE COLUMN `score_min` `expiration` INT(10) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `learning_competence_category` CHANGE COLUMN `id_competence_category` `id_category` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, ADD COLUMN `id_parent` INT(10) UNSIGNED NOT NULL DEFAULT '0'  AFTER `id_category` , ADD COLUMN `iLeft` INT(10) UNSIGNED NOT NULL DEFAULT '0'  AFTER `id_parent` , ADD COLUMN `iRight` INT(10) UNSIGNED NOT NULL DEFAULT '0'  AFTER `iLeft` , ADD COLUMN `level` INT(10) UNSIGNED NOT NULL DEFAULT '0'  AFTER `id_parent` , DROP PRIMARY KEY , ADD PRIMARY KEY (`id_category`) ;

ALTER TABLE `learning_competence_course` ADD COLUMN `retraining` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'  AFTER `score` ;

ALTER TABLE `learning_competence_track` CHANGE COLUMN `score` `score_total` FLOAT NOT NULL DEFAULT '0', CHANGE COLUMN `source` `operation` VARCHAR(255) NOT NULL DEFAULT '', ADD COLUMN `assigned_by` INT(11) NOT NULL DEFAULT '0'  AFTER `id_user` , ADD COLUMN `id_course` INT(11) NOT NULL DEFAULT '0'  AFTER `id_user` , ADD COLUMN `score_assigned` FLOAT NOT NULL DEFAULT '0'  AFTER `date_assignment`;

ALTER TABLE `learning_competence_user` DROP COLUMN `score_init` , ADD COLUMN `last_assign_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'  AFTER `score_got` ;

ALTER TABLE `learning_course`
ADD COLUMN `credits` INT(11) NOT NULL DEFAULT '0'  AFTER `show_result`,
ADD COLUMN `auto_unsubscribe` TINYINT(1) NOT NULL DEFAULT 0 AFTER `credits`,
ADD COLUMN `unsubscribe_date_limit` DATETIME NULL DEFAULT NULL AFTER `auto_unsubscribe`,
CHANGE COLUMN `direct_play` `direct_play` TINYINT(1) NOT NULL DEFAULT '0' AFTER `autoregistration_code`,
CHANGE COLUMN `show_who_online` `show_who_online` TINYINT(1) NOT NULL DEFAULT '0'  AFTER `show_time`,
CHANGE COLUMN `use_logo_in_courselist` `use_logo_in_courselist` TINYINT(1) NOT NULL DEFAULT '0',
CHANGE COLUMN `show_result` `show_result` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' ;

ALTER TABLE  `learning_coursereport` CHANGE  `source_of`  `source_of` ENUM(  'test',  'activity',  'scorm',  'final_vote',  'scoitem' ) NOT NULL DEFAULT  'test';

ALTER TABLE  `learning_coursepath_user`
ADD COLUMN `course_completed` INT( 3 ) NOT NULL DEFAULT '0',
ADD COLUMN `date_assign` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `learning_courseuser` ADD COLUMN `date_begin_validity` DATETIME AFTER `new_forum_post`, ADD COLUMN `date_expire_validity` DATETIME AFTER `date_begin_validity`;

ALTER TABLE `learning_courseuser` ADD  `rule_log` INT( 11 ) NULL AFTER  `subscribed_by`;

ALTER TABLE `learning_courseuser` ADD COLUMN `requesting_unsubscribe` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `date_expire_validity`, ADD COLUMN `requesting_unsubscribe_date` DATETIME AFTER `requesting_unsubscribe`;

ALTER TABLE `learning_menu_under` ADD COLUMN `mvc_path` VARCHAR(255) NOT NULL DEFAULT ''  AFTER `class_name` ;

ALTER TABLE `learning_module` ADD COLUMN `mvc_path` VARCHAR(255) NOT NULL DEFAULT ''  AFTER `module_info` , CHANGE COLUMN `module_info` `module_info` VARCHAR(50) NOT NULL DEFAULT ''  ;

ALTER TABLE `learning_organization` ADD COLUMN `publish_for` INT(1) NOT NULL DEFAULT 0  AFTER `publish_to`, ADD COLUMN `access` VARCHAR(255) NULL DEFAULT NULL  AFTER `publish_to` ;

ALTER TABLE `learning_test` ADD `mandatory_answer` tinyint(1) unsigned NOT NULL DEFAULT '0', ADD `score_max` int(11) NOT NULL DEFAULT '0';

ALTER TABLE  `learning_scorm_tracking` ADD `first_access` DATETIME NULL DEFAULT NULL , ADD  `last_access` DATETIME NULL DEFAULT NULL;

ALTER TABLE `learning_scorm_tracking_history` CHANGE COLUMN `idscorm_tracking` `idscorm_tracking` INT(11) NOT NULL DEFAULT '0'  , CHANGE COLUMN `date_action` `date_action` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'  ;

ALTER TABLE `learning_statuschangelog` DROP PRIMARY KEY , ADD PRIMARY KEY (`idUser`, `idCourse`, `when_do`) ;

ALTER TABLE `learning_test` ADD COLUMN `chart_options` TEXT NOT NULL  AFTER `hide_info` , ADD COLUMN `order_info` TEXT NOT NULL  AFTER `hide_info` , ADD COLUMN `suspension_num_attempts` INT(10) UNSIGNED NOT NULL DEFAULT '0'  AFTER `order_info` , ADD COLUMN `suspension_num_hours` INT(10) UNSIGNED NOT NULL DEFAULT '0'  AFTER `suspension_num_attempts` , ADD COLUMN `suspension_prerequisites` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'  AFTER `suspension_num_hours` , ADD COLUMN `use_suspension` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'  AFTER `order_info` ;

ALTER TABLE `learning_testtrack` ADD COLUMN `attempts_for_suspension` INT(10) UNSIGNED NOT NULL DEFAULT '0'  AFTER `comment` , ADD COLUMN `suspended_until` DATETIME NULL DEFAULT NULL  AFTER `attempts_for_suspension`;

ALTER TABLE `learning_testtrack_answer` ADD COLUMN `user_answer` TINYINT(1) UNSIGNED NULL DEFAULT '0';

UPDATE `learning_testtrack_answer` SET `user_answer` = NULL;

ALTER TABLE  `learning_testtrack_times` ADD  `date_begin` DATETIME NOT NULL ,
ADD  `date_end` DATETIME NOT NULL ,
ADD  `time` INT( 11 ) NOT NULL ;

ALTER TABLE `learning_report_filter` ADD COLUMN `views` INT(5) NOT NULL DEFAULT '0';

ALTER TABLE `learning_report_schedule` CHANGE  `id_report_filter` `id_report_filter` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0', CHANGE  `id_creator` `id_creator` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0', CHANGE  `time` `time` TIME NOT NULL DEFAULT  '00:00:00', CHANGE  `creation_date` `creation_date` DATETIME NOT NULL DEFAULT  '0000-00-00 00:00:00';

ALTER TABLE `learning_report_schedule_recipient` CHANGE  `id_report_schedule` `id_report_schedule` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0', CHANGE  `id_user` `id_user` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0';

ALTER TABLE `core_group_fields` ADD COLUMN `user_inherit` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `useraccess`;

ALTER TABLE `core_org_chart_tree` ADD `associated_template` VARCHAR( 255 ) NULL;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;