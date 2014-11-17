
-- ------------------------------------------------------
-- MySQL dump
-- MySQL Version: 5.x.xx
-- PHP   Version: 5.x.x
--
-- Host: localhost    Database: formalms
-- ------------------------------------------------------

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_advice`
--

CREATE TABLE IF NOT EXISTS `learning_advice` (
  `idAdvice` int(11) NOT NULL AUTO_INCREMENT,
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `posted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `author` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `important` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idAdvice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_advice`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_adviceuser`
--

CREATE TABLE IF NOT EXISTS `learning_adviceuser` (
  `idAdvice` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `archivied` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idAdvice`,`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_adviceuser`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_assessment_rule`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_assessment_rule`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_assessment_user`
--

CREATE TABLE IF NOT EXISTS `learning_assessment_user` (
  `id_assessment` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `type_of` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_assessment`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_assessment_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_calendar`
--

CREATE TABLE IF NOT EXISTS `learning_calendar` (
  `id` bigint(20) NOT NULL DEFAULT '0',
  `idCourse` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_calendar`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_catalogue`
--

CREATE TABLE IF NOT EXISTS `learning_catalogue` (
  `idCatalogue` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`idCatalogue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_catalogue`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_catalogue_entry`
--

CREATE TABLE IF NOT EXISTS `learning_catalogue_entry` (
  `idCatalogue` int(11) NOT NULL DEFAULT '0',
  `idEntry` int(11) NOT NULL DEFAULT '0',
  `type_of_entry` enum('course','coursepath') NOT NULL DEFAULT 'course',
  PRIMARY KEY (`idCatalogue`,`idEntry`,`type_of_entry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_catalogue_entry`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_catalogue_member`
--

CREATE TABLE IF NOT EXISTS `learning_catalogue_member` (
  `idCatalogue` int(11) NOT NULL DEFAULT '0',
  `idst_member` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCatalogue`,`idst_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_catalogue_member`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_category`
--

CREATE TABLE IF NOT EXISTS `learning_category` (
  `idCategory` int(11) NOT NULL AUTO_INCREMENT,
  `idParent` int(11) DEFAULT '0',
  `lev` int(11) NOT NULL DEFAULT '0',
  `path` text NOT NULL,
  `description` text NOT NULL,
  `iLeft` int(5) NOT NULL DEFAULT '0',
  `iRight` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_category`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_certificate`
--

CREATE TABLE IF NOT EXISTS `learning_certificate` (
  `id_certificate` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `base_language` varchar(255) NOT NULL DEFAULT '',
  `cert_structure` text NOT NULL,
  `orientation` enum('P','L') NOT NULL DEFAULT 'P',
  `bgimage` varchar(255) NOT NULL DEFAULT '',
  `meta` tinyint(1) NOT NULL DEFAULT '0',
  `user_release` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_certificate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_certificate`
--

INSERT INTO `learning_certificate` (`id_certificate`, `code`, `name`, `description`, `base_language`, `cert_structure`, `orientation`, `bgimage`, `meta`, `user_release`) VALUES
(2, '0000', 'Certificate sample', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.<br />', 'english', '<table style="margin-left: auto; margin-right: auto;" border="0">\n<tbody>\n<tr>\n<td>&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</td>\n<td style="text-align: center;" colspan="2"><span style="font-size: 65px;"><br /><br /><br /><br /><br /><br /><br />This certificate is awarded to<strong><strong><br />[display_name]<br /></strong><br /><br /><br /></strong>In recognition of your completion of the training course<strong><br />[course_name]<br /><br /><br /><br /><br /><br /></strong></span></td>\n<td>&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</td>\n</tr>\n<tr>\n<td>&nbsp;</td>\n<td>&nbsp;</td>\n<td style="text-align: right;"><span style="line-height: 19px; font-size: small;"><strong><br /></strong></span></td>\n<td><span style="font-size: x-large;"><strong>[theacher_list]</strong></span><br /><span style="font-size: x-large;">The Instructor/s</span></td>\n</tr>\n</tbody>\n</table>\n<br />', 'L', 'certificate_sample.jpg', 0, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_certificate_assign`
--

CREATE TABLE IF NOT EXISTS `learning_certificate_assign` (
  `id_certificate` int(11) NOT NULL DEFAULT '0',
  `id_course` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `on_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cert_file` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_certificate`,`id_course`,`id_user`),
  KEY `id_course` (`id_course`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_certificate_assign`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_certificate_course`
--

CREATE TABLE IF NOT EXISTS `learning_certificate_course` (
  `id_certificate` int(11) NOT NULL DEFAULT '0',
  `id_course` int(11) NOT NULL DEFAULT '0',
  `available_for_status` tinyint(1) NOT NULL DEFAULT '0',
  `point_required` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_certificate`,`id_course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_certificate_course`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_certificate_meta`
--

CREATE TABLE IF NOT EXISTS `learning_certificate_meta` (
  `idMetaCertificate` int(11) NOT NULL AUTO_INCREMENT,
  `idCertificate` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  PRIMARY KEY (`idMetaCertificate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_certificate_meta`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_certificate_meta_assign`
--

CREATE TABLE IF NOT EXISTS `learning_certificate_meta_assign` (
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idMetaCertificate` int(11) NOT NULL DEFAULT '0',
  `idCertificate` int(11) NOT NULL DEFAULT '0',
  `on_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cert_file` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idUser`,`idMetaCertificate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_certificate_meta_assign`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_certificate_meta_course`
--

CREATE TABLE IF NOT EXISTS `learning_certificate_meta_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMetaCertificate` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `idCourseEdition` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_certificate_meta_course`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_certificate_tags`
--

CREATE TABLE IF NOT EXISTS `learning_certificate_tags` (
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`file_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_certificate_tags`
--

INSERT INTO `learning_certificate_tags` (`file_name`, `class_name`) VALUES
('certificate.course.php', 'CertificateSubs_Course'),
('certificate.misc.php', 'CertificateSubs_Misc'),
('certificate.user.php', 'CertificateSubs_User'),
('certificate.userstat.php', 'CertificateSubs_UserStat');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_classroom`
--

CREATE TABLE IF NOT EXISTS `learning_classroom` (
  `idClassroom` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `location_id` int(11) NOT NULL DEFAULT '0',
  `room` varchar(255) NOT NULL DEFAULT '',
  `street` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `state` varchar(255) NOT NULL DEFAULT '',
  `zip_code` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `fax` varchar(255) NOT NULL DEFAULT '',
  `capacity` varchar(255) NOT NULL DEFAULT '',
  `disposition` text NOT NULL,
  `instrument` text NOT NULL,
  `available_instrument` text NOT NULL,
  `note` text NOT NULL,
  `responsable` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idClassroom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_classroom`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_classroom_calendar`
--

CREATE TABLE IF NOT EXISTS `learning_classroom_calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classroom_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `owner` int(11) NOT NULL DEFAULT '0',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_classroom_calendar`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_class_location`
--

CREATE TABLE IF NOT EXISTS `learning_class_location` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_class_location`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_comment_ajax`
--

CREATE TABLE IF NOT EXISTS `learning_comment_ajax` (
  `id_comment` int(11) NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(50) NOT NULL DEFAULT '',
  `external_key` varchar(200) NOT NULL DEFAULT '',
  `id_author` int(11) NOT NULL DEFAULT '0',
  `posted_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `textof` text NOT NULL,
  `history_tree` varchar(255) NOT NULL DEFAULT '',
  `id_parent` int(11) NOT NULL DEFAULT '0',
  `moderated` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_comment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_comment_ajax`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_commontrack`
--

CREATE TABLE IF NOT EXISTS `learning_commontrack` (
  `idReference` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idTrack` int(11) NOT NULL DEFAULT '0',
  `objectType` varchar(20) NOT NULL DEFAULT '',
  `dateAttempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(20) NOT NULL DEFAULT '',
  `firstAttempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `first_complete` datetime DEFAULT NULL,
  `last_complete` datetime DEFAULT NULL,
  PRIMARY KEY (`idTrack`,`objectType`),
  KEY `idReference` (`idReference`),
  KEY `idUser` (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_commontrack`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_communication`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_communication`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_communication_access`
--

CREATE TABLE IF NOT EXISTS `learning_communication_access` (
  `id_comm` int(11) NOT NULL DEFAULT '0',
  `idst` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_comm`,`idst`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_communication_access`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_communication_category`
--

CREATE TABLE IF NOT EXISTS `learning_communication_category` (
  `id_category` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int(11) unsigned NOT NULL DEFAULT '0',
  `level` int(11) unsigned NOT NULL DEFAULT '0',
  `iLeft` int(11) unsigned NOT NULL DEFAULT '0',
  `iRight` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_communication_category`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_communication_category_lang`
--

CREATE TABLE IF NOT EXISTS `learning_communication_category_lang` (
  `id_category` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `translation` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_category`,`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_communication_category_lang`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_communication_track`
--

CREATE TABLE IF NOT EXISTS `learning_communication_track` (
  `idReference` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idTrack` int(11) NOT NULL DEFAULT '0',
  `objectType` varchar(20) NOT NULL DEFAULT '',
  `dateAttempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(20) NOT NULL DEFAULT '',
  `firstAttempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`idReference`,`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_communication_track`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_competence`
--

CREATE TABLE IF NOT EXISTS `learning_competence` (
  `id_competence` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_category` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('score','flag') NOT NULL DEFAULT 'score',
  `score` float NOT NULL DEFAULT '0',
  `typology` enum('skill','attitude','knowledge') NOT NULL DEFAULT 'skill',
  `expiration` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_competence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_competence`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_competence_category`
--

CREATE TABLE IF NOT EXISTS `learning_competence_category` (
  `id_category` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_parent` int(10) unsigned NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `iLeft` int(10) unsigned NOT NULL DEFAULT '0',
  `iRight` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_competence_category`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_competence_category_lang`
--

CREATE TABLE IF NOT EXISTS `learning_competence_category_lang` (
  `id_category` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id_category`,`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_competence_category_lang`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_competence_course`
--

CREATE TABLE IF NOT EXISTS `learning_competence_course` (
  `id_competence` int(10) unsigned NOT NULL DEFAULT '0',
  `id_course` int(10) unsigned NOT NULL DEFAULT '0',
  `score` float NOT NULL DEFAULT '0',
  `retraining` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_competence`,`id_course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_competence_course`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_competence_lang`
--

CREATE TABLE IF NOT EXISTS `learning_competence_lang` (
  `id_competence` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id_competence`,`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_competence_lang`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_competence_required`
--

CREATE TABLE IF NOT EXISTS `learning_competence_required` (
  `id_competence` int(10) unsigned NOT NULL DEFAULT '0',
  `idst` int(10) unsigned NOT NULL DEFAULT '0',
  `type_of` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_competence`,`idst`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_competence_required`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_competence_track`
--

CREATE TABLE IF NOT EXISTS `learning_competence_track` (
  `id_track` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_competence` int(10) unsigned NOT NULL DEFAULT '0',
  `id_user` int(10) unsigned NOT NULL DEFAULT '0',
  `operation` varchar(255) NOT NULL DEFAULT '',
  `id_course` int(11) NOT NULL DEFAULT '0',
  `assigned_by` int(11) NOT NULL DEFAULT '0',
  `date_assignment` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `score_assigned` float NOT NULL DEFAULT '0',
  `score_total` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_track`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_competence_track`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_competence_user`
--

CREATE TABLE IF NOT EXISTS `learning_competence_user` (
  `id_competence` int(10) unsigned NOT NULL DEFAULT '0',
  `id_user` int(10) unsigned NOT NULL DEFAULT '0',
  `score_got` float NOT NULL DEFAULT '0',
  `last_assign_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_competence`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_competence_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_course`
--

CREATE TABLE IF NOT EXISTS `learning_course` (
  `idCourse` int(11) NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) NOT NULL DEFAULT '0',
  `code` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `lang_code` varchar(100) NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT '0',
  `level_show_user` int(11) NOT NULL DEFAULT '0',
  `subscribe_method` tinyint(1) NOT NULL DEFAULT '0',
  `linkSponsor` varchar(255) NOT NULL DEFAULT '',
  `imgSponsor` varchar(255) NOT NULL DEFAULT '',
  `img_course` varchar(255) NOT NULL DEFAULT '',
  `img_material` varchar(255) NOT NULL DEFAULT '',
  `img_othermaterial` varchar(255) NOT NULL DEFAULT '',
  `course_demo` varchar(255) NOT NULL DEFAULT '',
  `mediumTime` int(10) unsigned NOT NULL DEFAULT '0',
  `permCloseLO` tinyint(1) NOT NULL DEFAULT '0',
  `userStatusOp` int(11) NOT NULL DEFAULT '0',
  `difficult` enum('veryeasy','easy','medium','difficult','verydifficult') NOT NULL DEFAULT 'medium',
  `show_progress` tinyint(1) NOT NULL DEFAULT '1',
  `show_time` tinyint(1) NOT NULL DEFAULT '0',
  `show_who_online` tinyint(1) NOT NULL DEFAULT '0',
  `show_extra_info` tinyint(1) NOT NULL DEFAULT '0',
  `show_rules` tinyint(1) NOT NULL DEFAULT '0',
  `date_begin` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `hour_begin` varchar(5) NOT NULL DEFAULT '',
  `hour_end` varchar(5) NOT NULL DEFAULT '',
  `valid_time` int(10) NOT NULL DEFAULT '0',
  `max_num_subscribe` int(11) NOT NULL DEFAULT '0',
  `min_num_subscribe` int(11) NOT NULL DEFAULT '0',
  `max_sms_budget` double NOT NULL DEFAULT '0',
  `selling` tinyint(1) NOT NULL DEFAULT '0',
  `prize` varchar(255) NOT NULL DEFAULT '',
  `course_type` varchar(255) NOT NULL DEFAULT 'elearning',
  `policy_point` varchar(255) NOT NULL DEFAULT '',
  `point_to_all` int(10) NOT NULL DEFAULT '0',
  `course_edition` tinyint(1) NOT NULL DEFAULT '0',
  `classrooms` varchar(255) NOT NULL DEFAULT '',
  `certificates` varchar(255) NOT NULL DEFAULT '',
  `create_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `security_code` varchar(255) NOT NULL DEFAULT '',
  `imported_from_connection` varchar(255) DEFAULT NULL,
  `course_quota` varchar(255) NOT NULL DEFAULT '-1',
  `used_space` varchar(255) NOT NULL DEFAULT '0',
  `course_vote` double NOT NULL DEFAULT '0',
  `allow_overbooking` tinyint(1) NOT NULL DEFAULT '0',
  `can_subscribe` tinyint(1) NOT NULL DEFAULT '0',
  `sub_start_date` datetime DEFAULT NULL,
  `sub_end_date` datetime DEFAULT NULL,
  `advance` varchar(255) NOT NULL DEFAULT '',
  `autoregistration_code` varchar(255) NOT NULL DEFAULT '',
  `direct_play` tinyint(1) NOT NULL DEFAULT '0',
  `use_logo_in_courselist` tinyint(1) NOT NULL DEFAULT '0',
  `show_result` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `credits` int(11) NOT NULL DEFAULT '0',
  `auto_unsubscribe` tinyint(1) NOT NULL DEFAULT '0',
  `unsubscribe_date_limit` datetime DEFAULT NULL,
  PRIMARY KEY (`idCourse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_course`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_coursepath`
--

CREATE TABLE IF NOT EXISTS `learning_coursepath` (
  `id_path` int(11) NOT NULL AUTO_INCREMENT,
  `path_code` varchar(255) NOT NULL DEFAULT '',
  `path_name` varchar(255) NOT NULL DEFAULT '',
  `path_descr` text NOT NULL,
  `subscribe_method` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_coursepath`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_coursepath_courses`
--

CREATE TABLE IF NOT EXISTS `learning_coursepath_courses` (
  `id_path` int(11) NOT NULL DEFAULT '0',
  `id_item` int(11) NOT NULL DEFAULT '0',
  `in_slot` int(11) NOT NULL DEFAULT '0',
  `prerequisites` text NOT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_path`,`id_item`,`in_slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_coursepath_courses`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_coursepath_user`
--

CREATE TABLE IF NOT EXISTS `learning_coursepath_user` (
  `id_path` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `waiting` tinyint(1) NOT NULL DEFAULT '0',
  `course_completed` int(3) NOT NULL DEFAULT '0',
  `date_assign` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `subscribed_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_path`,`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_coursepath_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_coursereport`
--

CREATE TABLE IF NOT EXISTS `learning_coursereport` (
  `id_report` int(11) NOT NULL AUTO_INCREMENT,
  `id_course` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `max_score` float NOT NULL DEFAULT '0',
  `required_score` float NOT NULL DEFAULT '0',
  `weight` int(3) NOT NULL DEFAULT '0',
  `show_to_user` enum('true','false') NOT NULL DEFAULT 'true',
  `use_for_final` enum('true','false') NOT NULL DEFAULT 'true',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `source_of` enum('test','activity','scorm','final_vote','scoitem') NOT NULL DEFAULT 'test',
  `id_source` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_report`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_coursereport`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_coursereport_score`
--

CREATE TABLE IF NOT EXISTS `learning_coursereport_score` (
  `id_report` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `date_attempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `score` double(5,2) NOT NULL DEFAULT '0.00',
  `score_status` enum('valid','not_checked','not_passed','passed') NOT NULL DEFAULT 'valid',
  `comment` text NOT NULL,
  PRIMARY KEY (`id_report`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_coursereport_score`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_courseuser`
--

CREATE TABLE IF NOT EXISTS `learning_courseuser` (
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `edition_id` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  `date_inscr` datetime DEFAULT NULL,
  `date_first_access` datetime DEFAULT NULL,
  `date_complete` datetime DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `waiting` tinyint(1) NOT NULL DEFAULT '0',
  `subscribed_by` int(11) NOT NULL DEFAULT '0',
  `rule_log` int(11) DEFAULT NULL,
  `score_given` int(4) DEFAULT NULL,
  `imported_from_connection` varchar(255) DEFAULT NULL,
  `absent` tinyint(1) NOT NULL DEFAULT '0',
  `cancelled_by` int(11) NOT NULL DEFAULT '0',
  `new_forum_post` int(11) NOT NULL DEFAULT '0',
  `date_begin_validity` datetime DEFAULT NULL,
  `date_expire_validity` datetime DEFAULT NULL,
  `requesting_unsubscribe` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `requesting_unsubscribe_date` datetime DEFAULT NULL,
  PRIMARY KEY (`idUser`,`idCourse`,`edition_id`),
  KEY `idCourse` (`idCourse`),
  KEY `idUser` (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_courseuser`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_course_date`
--

CREATE TABLE IF NOT EXISTS `learning_course_date` (
  `id_date` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_course` int(10) unsigned NOT NULL DEFAULT '0',
  `code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `max_par` int(11) NOT NULL DEFAULT '0',
  `price` varchar(255) NOT NULL DEFAULT '0',
  `overbooking` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `test_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  `medium_time` int(11) NOT NULL DEFAULT '0',
  `sub_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sub_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `unsubscribe_date_limit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_date`),
  KEY `id_course` (`id_course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_course_date`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_course_date_day`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_course_date_day`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_course_date_presence`
--

CREATE TABLE IF NOT EXISTS `learning_course_date_presence` (
  `day` date NOT NULL DEFAULT '0000-00-00',
  `id_date` int(11) unsigned NOT NULL DEFAULT '0',
  `id_user` int(11) unsigned NOT NULL DEFAULT '0',
  `id_day` int(11) unsigned NOT NULL DEFAULT '0',
  `presence` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `score` varchar(255) DEFAULT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`day`,`id_date`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_course_date_presence`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_course_date_user`
--

CREATE TABLE IF NOT EXISTS `learning_course_date_user` (
  `id_date` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `date_subscription` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_complete` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `presence` mediumtext,
  `subscribed_by` int(10) unsigned NOT NULL DEFAULT '0',
  `overbooking` int(10) DEFAULT '0',
  `requesting_unsubscribe` tinyint(1) unsigned DEFAULT NULL,
  `requesting_unsubscribe_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_date`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_course_date_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_course_editions`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_course_editions`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_course_editions_user`
--

CREATE TABLE IF NOT EXISTS `learning_course_editions_user` (
  `id_edition` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `date_subscription` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_complete` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `subscribed_by` int(10) unsigned NOT NULL DEFAULT '0',
  `requesting_unsubscribe` tinyint(1) unsigned DEFAULT NULL,
  `requesting_unsubscribe_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_edition`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_course_editions_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_course_file`
--

CREATE TABLE IF NOT EXISTS `learning_course_file` (
  `id_file` int(11) NOT NULL AUTO_INCREMENT,
  `id_course` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_course_file`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_faq`
--

CREATE TABLE IF NOT EXISTS `learning_faq` (
  `idFaq` int(11) NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) NOT NULL DEFAULT '0',
  `question` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `keyword` text NOT NULL,
  `answer` text NOT NULL,
  `sequence` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idFaq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_faq`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_faq_cat`
--

CREATE TABLE IF NOT EXISTS `learning_faq_cat` (
  `idCategory` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `author` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_faq_cat`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_forum`
--

CREATE TABLE IF NOT EXISTS `learning_forum` (
  `idForum` int(11) NOT NULL AUTO_INCREMENT,
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `num_thread` int(11) NOT NULL DEFAULT '0',
  `num_post` int(11) NOT NULL DEFAULT '0',
  `last_post` int(11) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `sequence` int(5) NOT NULL DEFAULT '0',
  `emoticons` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idForum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_forum`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_forummessage`
--

CREATE TABLE IF NOT EXISTS `learning_forummessage` (
  `idMessage` int(11) NOT NULL AUTO_INCREMENT,
  `idThread` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `answer_tree` text NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `textof` text NOT NULL,
  `posted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `author` int(11) NOT NULL DEFAULT '0',
  `generator` tinyint(1) NOT NULL DEFAULT '0',
  `attach` varchar(255) NOT NULL DEFAULT '',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `modified_by_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`idMessage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_forummessage`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_forumthread`
--

CREATE TABLE IF NOT EXISTS `learning_forumthread` (
  `idThread` int(11) NOT NULL AUTO_INCREMENT,
  `id_edition` int(11) NOT NULL DEFAULT '0',
  `idForum` int(11) NOT NULL DEFAULT '0',
  `posted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(255) NOT NULL DEFAULT '',
  `author` int(11) NOT NULL DEFAULT '0',
  `num_post` int(11) NOT NULL DEFAULT '0',
  `num_view` int(5) NOT NULL DEFAULT '0',
  `last_post` int(11) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `erased` tinyint(1) NOT NULL DEFAULT '0',
  `emoticons` varchar(255) NOT NULL DEFAULT '',
  `rilevantForum` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idThread`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_forumthread`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_forum_access`
--

CREATE TABLE IF NOT EXISTS `learning_forum_access` (
  `idForum` int(11) NOT NULL DEFAULT '0',
  `idMember` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idForum`,`idMember`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_forum_access`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_forum_notifier`
--

CREATE TABLE IF NOT EXISTS `learning_forum_notifier` (
  `id_notify` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `notify_is_a` enum('forum','thread') NOT NULL DEFAULT 'forum',
  PRIMARY KEY (`id_notify`,`id_user`,`notify_is_a`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_forum_notifier`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_forum_timing`
--

CREATE TABLE IF NOT EXISTS `learning_forum_timing` (
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `last_access` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`idUser`,`idCourse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_forum_timing`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_games`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_games`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_games_access`
--

CREATE TABLE IF NOT EXISTS `learning_games_access` (
  `id_game` int(11) NOT NULL DEFAULT '0',
  `idst` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_game`,`idst`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_games_access`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_games_track`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_games_track`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_glossary`
--

CREATE TABLE IF NOT EXISTS `learning_glossary` (
  `idGlossary` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `author` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idGlossary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_glossary`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_glossaryterm`
--

CREATE TABLE IF NOT EXISTS `learning_glossaryterm` (
  `idTerm` int(11) NOT NULL AUTO_INCREMENT,
  `idGlossary` int(11) NOT NULL DEFAULT '0',
  `term` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`idTerm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_glossaryterm`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_homerepo`
--

CREATE TABLE IF NOT EXISTS `learning_homerepo` (
  `idRepo` int(11) NOT NULL AUTO_INCREMENT,
  `idParent` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `lev` int(10) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `objectType` varchar(20) NOT NULL DEFAULT '',
  `idResource` int(11) NOT NULL DEFAULT '0',
  `idCategory` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idAuthor` int(11) NOT NULL DEFAULT '0',
  `version` varchar(8) NOT NULL DEFAULT '',
  `difficult` enum('_VERYEASY','_EASY','_MEDIUM','_DIFFICULT','_VERYDIFFICULT') NOT NULL DEFAULT '_VERYEASY',
  `description` text NOT NULL,
  `language` varchar(50) NOT NULL DEFAULT '',
  `resource` varchar(255) NOT NULL DEFAULT '',
  `objective` text NOT NULL,
  `dateInsert` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `idOwner` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idRepo`),
  KEY `idParent` (`idParent`),
  KEY `path` (`path`),
  KEY `idOwner` (`idOwner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_homerepo`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_htmlfront`
--

CREATE TABLE IF NOT EXISTS `learning_htmlfront` (
  `id_course` int(11) NOT NULL DEFAULT '0',
  `textof` text NOT NULL,
  PRIMARY KEY (`id_course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_htmlfront`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_htmlpage`
--

CREATE TABLE IF NOT EXISTS `learning_htmlpage` (
  `idPage` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL DEFAULT '',
  `textof` text NOT NULL,
  `author` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idPage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_htmlpage`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_htmlpage_attachment`
--

CREATE TABLE IF NOT EXISTS `learning_htmlpage_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idpage` int(11) unsigned NOT NULL,
  `file` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Dump dei dati per la tabella `learning_htmlpage_attachment`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_instmsg`
--

CREATE TABLE IF NOT EXISTS `learning_instmsg` (
  `id_msg` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_sender` int(11) NOT NULL DEFAULT '0',
  `id_receiver` int(11) NOT NULL DEFAULT '0',
  `msg` text,
  `status` smallint(2) NOT NULL DEFAULT '0',
  `data` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_msg`),
  KEY `id_sender` (`id_sender`,`id_receiver`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_instmsg`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_kb_rel`
--

CREATE TABLE IF NOT EXISTS `learning_kb_rel` (
  `res_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` varchar(45) NOT NULL DEFAULT '',
  `rel_type` enum('tag','folder') NOT NULL DEFAULT 'tag',
  PRIMARY KEY (`res_id`,`parent_id`,`rel_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_kb_rel`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_kb_res`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_kb_res`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_kb_tag`
--

CREATE TABLE IF NOT EXISTS `learning_kb_tag` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_kb_tag`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_kb_tree`
--

CREATE TABLE IF NOT EXISTS `learning_kb_tree` (
  `node_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `lev` int(11) NOT NULL DEFAULT '0',
  `iLeft` int(11) NOT NULL DEFAULT '0',
  `iRight` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_kb_tree`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_kb_tree_info`
--

CREATE TABLE IF NOT EXISTS `learning_kb_tree_info` (
  `id_dir` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(50) NOT NULL DEFAULT '',
  `node_title` varchar(255) NOT NULL DEFAULT '',
  `node_desc` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_kb_tree_info`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_label`
--

CREATE TABLE IF NOT EXISTS `learning_label` (
  `id_common_label` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_common_label`,`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_label`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_label_course`
--

CREATE TABLE IF NOT EXISTS `learning_label_course` (
  `id_common_label` int(11) NOT NULL DEFAULT '0',
  `id_course` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_common_label`,`id_course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_label_course`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_light_repo`
--

CREATE TABLE IF NOT EXISTS `learning_light_repo` (
  `id_repository` int(11) NOT NULL AUTO_INCREMENT,
  `id_course` int(11) NOT NULL DEFAULT '0',
  `repo_title` varchar(255) NOT NULL DEFAULT '',
  `repo_descr` text NOT NULL,
  PRIMARY KEY (`id_repository`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_light_repo`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_light_repo_files`
--

CREATE TABLE IF NOT EXISTS `learning_light_repo_files` (
  `id_file` int(11) NOT NULL AUTO_INCREMENT,
  `id_repository` int(11) NOT NULL DEFAULT '0',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `file_descr` text NOT NULL,
  `id_author` int(11) NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_light_repo_files`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_light_repo_user`
--

CREATE TABLE IF NOT EXISTS `learning_light_repo_user` (
  `id_repo` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `last_enter` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `repo_lock` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_repo`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_light_repo_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_link`
--

CREATE TABLE IF NOT EXISTS `learning_link` (
  `idLink` int(11) NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) NOT NULL DEFAULT '0',
  `title` varchar(150) NOT NULL DEFAULT '',
  `link_address` varchar(255) NOT NULL DEFAULT '',
  `keyword` text NOT NULL,
  `description` text NOT NULL,
  `sequence` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idLink`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_link`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_link_cat`
--

CREATE TABLE IF NOT EXISTS `learning_link_cat` (
  `idCategory` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `author` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_link_cat`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_lo_param`
--

CREATE TABLE IF NOT EXISTS `learning_lo_param` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idParam` int(11) NOT NULL DEFAULT '0',
  `param_name` varchar(20) NOT NULL DEFAULT '',
  `param_value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idParam_name` (`idParam`,`param_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_lo_param`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_lo_types`
--

CREATE TABLE IF NOT EXISTS `learning_lo_types` (
  `objectType` varchar(20) NOT NULL DEFAULT '',
  `className` varchar(20) NOT NULL DEFAULT '',
  `fileName` varchar(50) NOT NULL DEFAULT '',
  `classNameTrack` varchar(255) NOT NULL DEFAULT '',
  `fileNameTrack` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`objectType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_lo_types`
--

INSERT INTO `learning_lo_types` (`objectType`, `className`, `fileName`, `classNameTrack`, `fileNameTrack`) VALUES
('faq', 'Learning_Faq', 'learning.faq.php', 'Track_Faq', 'track.faq.php'),
('glossary', 'Learning_Glossary', 'learning.glossary.php', 'Track_Glossary', 'track.glossary.php'),
('htmlpage', 'Learning_Htmlpage', 'learning.htmlpage.php', 'Track_Htmlpage', 'track.htmlpage.php'),
('item', 'Learning_Item', 'learning.item.php', 'Track_Item', 'track.item.php'),
('link', 'Learning_Link', 'learning.link.php', 'Track_Link', 'track.link.php'),
('poll', 'Learning_Poll', 'learning.poll.php', 'Track_Poll', 'track.poll.php'),
('scormorg', 'Learning_ScormOrg', 'learning.scorm.php', 'Track_Scormorg', 'track.scorm.php'),
('test', 'Learning_Test', 'learning.test.php', 'Track_Test', 'track.test.php');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_materials_lesson`
--

CREATE TABLE IF NOT EXISTS `learning_materials_lesson` (
  `idLesson` int(11) NOT NULL AUTO_INCREMENT,
  `author` int(11) NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idLesson`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_materials_lesson`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_materials_track`
--

CREATE TABLE IF NOT EXISTS `learning_materials_track` (
  `idTrack` int(11) NOT NULL AUTO_INCREMENT,
  `idResource` int(11) NOT NULL DEFAULT '0',
  `idReference` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idTrack`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_materials_track`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_menu`
--

CREATE TABLE IF NOT EXISTS `learning_menu` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_menu`
--

INSERT INTO `learning_menu` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(1, '_MANAGEMENT_COURSE', '', 1, 'false'),
(2, '', '', 2, 'true'),
(3, '_ASSESSMENT', '', 3, 'false'),
(4, '', '', 4, 'true'),
(5, '', '', 5, 'true'),
(6, '_CONTENTS', '', 6, 'false'),
(7, '_MAN_CERTIFICATE', '', 7, 'false'),
(8, '_MANAGEMENT_RESERVATION', '', 8, 'false'),
(9, '_CONFIGURATION', '', 9, 'false'),
(10, '', '', 10, 'true'),
(11, '', '', 11, 'true'),
(12, '', '', 12, 'true'),
(13, '', '', 13, 'true'),
(14, '', '', 14, 'true');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_menucourse_main`
--

CREATE TABLE IF NOT EXISTS `learning_menucourse_main` (
  `idMain` int(11) NOT NULL AUTO_INCREMENT,
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idMain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_menucourse_main`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_menucourse_under`
--

CREATE TABLE IF NOT EXISTS `learning_menucourse_under` (
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `idModule` int(11) NOT NULL DEFAULT '0',
  `idMain` int(11) NOT NULL DEFAULT '0',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `my_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idCourse`,`idModule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_menucourse_under`
--

INSERT INTO `learning_menucourse_under` (`idCourse`, `idModule`, `idMain`, `sequence`, `my_name`) VALUES
(0, 1, 0, 1, ''),
(0, 2, 0, 2, ''),
(0, 3, 0, 3, ''),
(0, 4, 0, 4, ''),
(0, 5, 0, 5, ''),
(0, 6, 0, 6, ''),
(0, 7, 0, 2, ''),
(0, 8, 0, 8, ''),
(0, 9, 0, 9, ''),
(0, 32, 0, 4, ''),
(0, 33, 5, 11, ''),
(0, 34, 0, 3, ''),
(0, 35, 1, 1, ''),
(0, 36, 1, 2, ''),
(0, 37, 1, 3, ''),
(0, 38, 1, 4, ''),
(0, 39, 1, 5, '');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_menucustom`
--

CREATE TABLE IF NOT EXISTS `learning_menucustom` (
  `idCustom` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`idCustom`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_menucustom`
--

INSERT INTO `learning_menucustom` (`idCustom`, `title`, `description`) VALUES
(11, 'Collaboration','Menu with standard collaboration features'),
(21, 'Self - Training', 'Training based on multimedia content');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_menucustom_main`
--

CREATE TABLE IF NOT EXISTS `learning_menucustom_main` (
  `idMain` int(11) NOT NULL AUTO_INCREMENT,
  `idCustom` int(11) NOT NULL DEFAULT '0',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idMain`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_menucustom_main`
--

INSERT INTO `learning_menucustom_main` (`idMain`, `idCustom`, `sequence`, `name`, `image`) VALUES
(25, 11, 1, 'Student Area', ''),
(26, 11, 3, 'Teacher Area', ''),
(27, 11, 4, 'Stat Area', ''),
(41, 11, 2, 'Collaborative Area', ''),
(42, 21, 1, 'Student Area', ''),
(43, 21, 2, 'Teacher', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_menucustom_under`
--

CREATE TABLE IF NOT EXISTS `learning_menucustom_under` (
  `idCustom` int(11) NOT NULL DEFAULT '0',
  `idModule` int(11) NOT NULL DEFAULT '0',
  `idMain` int(11) NOT NULL DEFAULT '0',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `my_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idCustom`,`idModule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_menucustom_under`
--

INSERT INTO `learning_menucustom_under` (`idCustom`, `idModule`, `idMain`, `sequence`, `my_name`) VALUES
(0, 1, 0, 1, ''),
(0, 2, 0, 2, ''),
(0, 3, 0, 3, ''),
(0, 4, 0, 4, ''),
(0, 5, 0, 5, ''),
(0, 6, 0, 6, ''),
(0, 7, 0, 7, ''),
(0, 8, 0, 8, ''),
(0, 9, 0, 9, ''),
(0, 32, 0, 10, ''),
(0, 33, 0, 11, ''),
(11, 11, 25, 2, ''),
(11, 12, 26, 1, ''),
(11, 13, 25, 4, ''),
(11, 14, 25, 5, ''),
(11, 15, 25, 8, ''),
(11, 17, 41, 6, ''),
(11, 19, 41, 1, ''),
(11, 20, 41, 2, ''),
(11, 21, 41, 5, ''),
(11, 22, 41, 4, ''),
(11, 23, 41, 3, ''),
(11, 24, 26, 2, ''),
(11, 25, 25, 3, ''),
(11, 26, 26, 5, ''),
(11, 27, 26, 3, ''),
(11, 29, 27, 1, ''),
(11, 30, 27, 2, ''),
(11, 31, 27, 3, ''),
(11, 41, 27, 17, ''),
(11, 42, 27, 18, ''),
(11, 43, 26, 16, ''),
(21, 12, 43, 1, ''),
(21, 14, 42, 2, ''),
(21, 25, 42, 1, ''),
(21, 26, 43, 2, ''),
(21, 29, 43, 6, ''),
(21, 30, 43, 7, ''),
(21, 31, 43, 5, ''),
(21, 41, 43, 3, ''),
(21, 42, 43, 4, '');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_menu_under`
--

CREATE TABLE IF NOT EXISTS `learning_menu_under` (
  `idUnder` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `module_name` varchar(255) NOT NULL DEFAULT '',
  `default_name` varchar(255) NOT NULL DEFAULT '',
  `default_op` varchar(255) NOT NULL DEFAULT '',
  `associated_token` varchar(255) NOT NULL DEFAULT '',
  `of_platform` varchar(255) DEFAULT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  `class_file` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `mvc_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idUnder`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_menu_under`
--

INSERT INTO `learning_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(1, 1, 'course', '_COURSE', '', 'view', NULL, 1, '', '', 'alms/course/show'),
(2, 9, 'amanmenu', '_MAN_MENU', 'mancustom', 'view', NULL, 1, 'class.amanmenu.php', 'Module_AManmenu', ''),
(3, 1, 'coursepath', '_COURSEPATH', 'pathlist', 'view', NULL, 2, 'class.coursepath.php', 'Module_Coursepath', ''),
(4, 1, 'catalogue', '_CATALOGUE', 'catlist', 'view', NULL, 3, 'class.catalogue.php', 'Module_Catalogue', ''),
(5, 6, 'webpages', '_WEBPAGES', 'webpages', 'view', NULL, 1, 'class.webpages.php', 'Module_Webpages', ''),
(6, 6, 'news', '_NEWS', 'news', 'view', NULL, 2, 'class.news.php', 'Module_News', ''),
(7, 9, 'questcategory', '_QUESTCATEGORY', '', 'view', NULL, 4, '', '', 'alms/questcategory/show'),
(8, 1, 'coursecategory', '_COURSECATEGORY', '', 'view', NULL, 4, '', '', 'alms/coursecategory/show'),
(9, 11, 'report', '_REPORT', 'reportlist', 'view', NULL, 1, 'class.report.php', 'Module_Report', ''),
(10, 3, 'preassessment', '_ASSESSMENT', 'assesmentlist', 'view', NULL, 1, 'class.preassessment.php', 'Module_PreAssessment', ''),
(14, 7, 'certificate', '_CERTIFICATE', 'certificate', 'view', NULL, 1, 'class.certificate.php', 'Module_Certificate', ''),
(17, 8, 'reservation', '_EVENTS', 'view_event', 'view', NULL, 1, 'class.reservation.php', 'Module_Reservation', ''),
(18, 8, 'reservation', '_CATEGORY', 'view_category', 'view', NULL, 2, 'class.reservation.php', 'Module_Reservation', ''),
(20, 8, 'reservation', '_RESERVATION', 'view_registration', 'view', NULL, 3, 'class.reservation.php', 'Module_Reservation', ''),
(21, 9, 'middlearea', '_MIDDLE_AREA', 'view_area', 'view', NULL, 2, 'class.middlearea.php', 'Module_MiddleArea', ''),
(22, 6, 'internal_news', '_NEWS_INTERNAL', 'news', 'view', NULL, 3, 'class.internal_news.php', 'Module_Internal_News', ''),
(23, 7, 'meta_certificate', '_META_CERTIFICATE', 'meta_certificate', 'view', NULL, 3, 'class.meta_certificate.php', 'Module_Meta_Certificate', ''),
(26, 14, 'transaction', '_TRANSACTION', '', 'view', NULL, 1, '', '', 'alms/transaction/show'),
(27, 2, 'location', '_LOCATION', '', 'view', NULL, 1, '', '', 'alms/location/show'),
(28, 4, 'games', '_CONTEST', '', 'view', NULL, 1, '', '', 'alms/games/show'),
(29, 5, 'communication', '_COMMUNICATION_MAN', '', 'view', NULL, 1, '', '', 'alms/communication/show'),
(30, 12, 'kb', '_CONTENT_LIBRARY', '', 'view', NULL, 1, '', '', 'alms/kb/show'),
(31, 9, 'timeperiods', '_TIME_PERIODS', '', 'view', NULL, 5, '', '', 'alms/timeperiods/show'),
(32, 13, 'enrollrules', '_ENROLLRULES', '', 'view', NULL, 1, '', '', 'alms/enrollrules/show'),
(33, 9, 'label', '_LABEL', '', 'view', NULL, 5, '', '', 'alms/label/show');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_middlearea`
--

CREATE TABLE IF NOT EXISTS `learning_middlearea` (
  `obj_index` varchar(255) NOT NULL DEFAULT '',
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `idst_list` text NOT NULL,
  `sequence` INT( 5 ) NOT NULL,
  PRIMARY KEY (`obj_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_middlearea`
--

INSERT INTO `learning_middlearea` (`obj_index`, `disabled`, `idst_list`, `sequence`) VALUES
('credits', 1, 'a:0:{}',0),
('mo_32', 1, 'a:0:{}',0),
('mo_33', 1, 'a:0:{}',0),
('mo_34', 1, 'a:0:{}',0),
('mo_help', 1, 'a:0:{}',0),
('tb_elearning', 0, 'a:0:{}', 0),
('tb_catalog', 1, 'a:0:{}',0),
('tb_assessment', 1, 'a:0:{}',0),
('tb_classroom', 1, 'a:0:{}',0),
('tb_communication', 1, 'a:0:{}',0),
('tb_coursepath', 1, 'a:0:{}',0),
('tb_games', 1, 'a:0:{}',0),
('tb_label', 1, 'a:0:{}',0),
('tb_videoconference', 1, 'a:0:{}',0),
('tb_kb', 0, 'a:0:{}', 0),
('tb_home', '1', 'a:0:{}', '0');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_module`
--

CREATE TABLE IF NOT EXISTS `learning_module` (
  `idModule` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) NOT NULL DEFAULT '',
  `default_op` varchar(255) NOT NULL DEFAULT '',
  `default_name` varchar(255) NOT NULL DEFAULT '',
  `token_associated` varchar(100) NOT NULL DEFAULT '',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `module_info` varchar(50) NOT NULL DEFAULT '',
  `mvc_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idModule`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_module`
--

INSERT INTO `learning_module` (`idModule`, `module_name`, `default_op`, `default_name`, `token_associated`, `file_name`, `class_name`, `module_info`, `mvc_path`) VALUES
(1, 'course', '', '_MYCOURSES', 'view', '', '', 'all', 'elearning/show'),
(3, 'profile', 'profile', '_PROFILE', 'view', 'class.profile.php', 'Module_Profile', '_user', ''),
(7, 'mycertificate', 'mycertificate', '_MY_CERTIFICATE', 'view', 'class.mycertificate.php', 'Module_MyCertificate', 'all', ''),
(10, 'course', 'infocourse', '_INFCOURSE', 'view_info', 'class.course.php', 'Module_Course', '', ''),
(11, 'advice', 'advice', '_ADVICE', 'view', 'class.advice.php', 'Module_Advice', '', ''),
(12, 'storage', 'display', '_STORAGE', 'view', 'class.storage.php', 'Module_Storage', '', ''),
(13, 'calendar', 'calendar', '_CALENDAR', 'view', 'class.calendar.php', 'Module_Calendar', '', ''),
(14, 'gradebook', 'showgrade', '_GRADEBOOK', 'view', 'class.gradebook.php', 'Module_Gradebook', '', ''),
(15, 'notes', 'notes', '_NOTES', 'view', 'class.notes.php', 'Module_Notes', '', ''),
(16, 'reservation', 'reservation', '_RESERVATION', 'view', 'class.reservation.php', 'Module_Reservation', '', ''),
(17, 'light_repo', 'repolist', '_LIGHT_REPO', 'view', 'class.light_repo.php', 'Module_Light_Repo', '', ''),
(18, 'htmlfront', 'showhtml', '_HTMLFRONT', 'view', 'class.htmlfront.php', 'Module_Htmlfront', '', ''),
(19, 'forum', 'forum', '_FORUM', 'view', 'class.forum.php', 'Module_Forum', '', ''),
(20, 'wiki', 'main', '_WIKI', 'view', 'class.wiki.php', 'Module_Wiki', '', ''),
(21, 'chat', 'chat', '_CHAT', 'view', 'class.chat.php', 'Module_Chat', '', ''),
(22, 'conference', 'list', '_VIDEOCONFERENCE', 'view', 'class.conference.php', 'Module_Conference', '', ''),
(23, 'project', 'project', '_PROJECT', 'view', 'class.project.php', 'Module_Project', '', ''),
(24, 'groups', 'groups', '_GROUPS', 'view', 'class.groups.php', 'Module_Groups', '', ''),
(25, 'organization', 'organization', '_ORGANIZATION', 'view', 'class.organization.php', 'Module_Organization', '', ''),
(26, 'coursereport', 'coursereport', '_COURSEREPORT', 'view', 'class.coursereport.php', 'Module_CourseReport', '', ''),
(27, 'newsletter', 'view', '_NEWSLETTER', 'view', 'class.newsletter.php', 'Module_Newsletter', '', ''),
(28, 'manmenu', 'manmenu', '_MAN_MENU', 'view', 'class.manmenu.php', 'Module_CourseManmenu', '', ''),
(29, 'statistic', 'statistic', '_STAT', 'view', 'class.statistic.php', 'Module_Statistic', '', ''),
(30, 'stats', 'statuser', '_STATUSER', 'view_user', 'class.stats.php', 'Module_Stats', '', ''),
(31, 'stats', 'statcourse', '_STATCOURSE', 'view_course', 'class.stats.php', 'Module_Stats', '', ''),
(32, 'public_forum', 'forum', '_PUBLIC_FORUM', 'view', 'class.public_forum.php', 'Module_Public_Forum', 'all', ''),
(33, 'course_autoregistration', 'course_autoregistration', '_COURSE_AUTOREGISTRATION', 'view', 'class.course_autoregistration.php', 'Module_Course_Autoregistration', 'all', ''),
(34, 'mycompetences', 'mycompetences', '_MYCOMPETENCES', 'view', 'class.mycompetences.php', 'Module_MyCompetences', 'all', ''),
(35, 'pusermanagement', '', '_PUBLIC_USER_ADMIN', 'view', '', '', 'public_admin', 'lms/pusermanagement/show'),
(36, 'pcourse', '', '_PUBLIC_COURSE_ADMIN', 'view', '', '', 'public_admin', 'lms/pcourse/show'),
(38, 'public_report_admin', 'reportlist', '_PUBLIC_REPORT_ADMIN', 'view', 'class.public_report_admin.php', 'Module_Public_Report_Admin', 'public_admin', ''),
(39, 'public_newsletter_admin', 'newsletter', '_PUBLIC_NEWSLETTER_ADMIN', 'view', 'class.public_newsletter_admin.php', 'Module_Public_Newsletter_Admin', 'public_admin', ''),
(40, 'quest_bank', 'main', '_QUEST_BANK', 'view', 'class.quest_bank.php', 'Module_QuestBank', '', ''),
(41, 'coursecharts', 'show', '_COURSECHART', 'view', 'class.coursecharts.php', 'Module_Coursecharts', '', 'coursecharts/show'),
(42, 'coursestats', 'show', '_COURSESTATS', 'view', '', '', '', 'coursestats/show'),
(44, 'pcertificate', 'certificate', '_PUBLIC_CERTIFICATE_ADMIN', 'view', 'class.pcertificate.php', 'Module_Pcertificate', 'public_admin', ''),
(45, 'presence', '', '_PRESENCE', 'view', '', '', '', 'presence/presence');

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_news`
--

CREATE TABLE IF NOT EXISTS `learning_news` (
  `idNews` int(11) NOT NULL AUTO_INCREMENT,
  `publish_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(100) NOT NULL DEFAULT '',
  `short_desc` text NOT NULL,
  `long_desc` text NOT NULL,
  `language` varchar(100) NOT NULL DEFAULT '',
  `important` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idNews`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_news`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_news_internal`
--

CREATE TABLE IF NOT EXISTS `learning_news_internal` (
  `idNews` int(11) NOT NULL AUTO_INCREMENT,
  `publish_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(100) NOT NULL DEFAULT '',
  `short_desc` text NOT NULL,
  `long_desc` text NOT NULL,
  `language` varchar(100) NOT NULL DEFAULT '',
  `important` tinyint(1) NOT NULL DEFAULT '0',
  `viewer` longtext NOT NULL,
  PRIMARY KEY (`idNews`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_news_internal`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_notes`
--

CREATE TABLE IF NOT EXISTS `learning_notes` (
  `idNotes` int(11) NOT NULL AUTO_INCREMENT,
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `owner` int(11) NOT NULL DEFAULT '0',
  `data` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(150) NOT NULL DEFAULT '',
  `textof` text NOT NULL,
  PRIMARY KEY (`idNotes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_notes`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_organization`
--

CREATE TABLE IF NOT EXISTS `learning_organization` (
  `idOrg` int(11) NOT NULL AUTO_INCREMENT,
  `idParent` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `lev` int(10) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `objectType` varchar(20) NOT NULL DEFAULT '',
  `idResource` int(11) NOT NULL DEFAULT '0',
  `idCategory` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idAuthor` int(11) NOT NULL DEFAULT '0',
  `version` varchar(8) NOT NULL DEFAULT '',
  `difficult` enum('_VERYEASY','_EASY','_MEDIUM','_DIFFICULT','_VERYDIFFICULT') NOT NULL DEFAULT '_VERYEASY',
  `description` text NOT NULL,
  `language` varchar(50) NOT NULL DEFAULT '',
  `resource` varchar(255) NOT NULL DEFAULT '',
  `objective` text NOT NULL,
  `dateInsert` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `prerequisites` varchar(255) NOT NULL DEFAULT '',
  `isTerminator` tinyint(4) NOT NULL DEFAULT '0',
  `idParam` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `milestone` enum('start','end','-') NOT NULL DEFAULT '-',
  `width` varchar(4) NOT NULL DEFAULT '',
  `height` varchar(4) NOT NULL DEFAULT '',
  `publish_from` datetime DEFAULT NULL,
  `publish_to` datetime DEFAULT NULL,
  `access` varchar(255) DEFAULT NULL,
  `publish_for` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idOrg`),
  KEY `idParent` (`idParent`),
  KEY `path` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_organization`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_organization_access`
--

CREATE TABLE IF NOT EXISTS `learning_organization_access` (
  `idOrgAccess` int(11) NOT NULL DEFAULT '0',
  `kind` set('user','group') NOT NULL DEFAULT '',
  `value` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idOrgAccess`,`kind`,`value`),
  KEY `idObject` (`idOrgAccess`),
  KEY `kind` (`kind`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Access to items in area lesson (organization)';

--
-- Dump dei dati per la tabella `learning_organization_access`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_poll`
--

CREATE TABLE IF NOT EXISTS `learning_poll` (
  `id_poll` int(11) NOT NULL AUTO_INCREMENT,
  `author` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id_poll`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_poll`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_pollquest`
--

CREATE TABLE IF NOT EXISTS `learning_pollquest` (
  `id_quest` int(11) NOT NULL AUTO_INCREMENT,
  `id_poll` int(11) NOT NULL DEFAULT '0',
  `id_category` int(11) NOT NULL DEFAULT '0',
  `type_quest` varchar(255) NOT NULL DEFAULT '',
  `title_quest` text NOT NULL,
  `sequence` int(5) NOT NULL DEFAULT '0',
  `page` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_quest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_pollquest`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_pollquestanswer`
--

CREATE TABLE IF NOT EXISTS `learning_pollquestanswer` (
  `id_answer` int(11) NOT NULL AUTO_INCREMENT,
  `id_quest` int(11) NOT NULL DEFAULT '0',
  `sequence` int(11) NOT NULL DEFAULT '0',
  `answer` text NOT NULL,
  PRIMARY KEY (`id_answer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_pollquestanswer`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_pollquest_extra`
--

CREATE TABLE IF NOT EXISTS `learning_pollquest_extra` (
  `id_quest` int(11) NOT NULL DEFAULT '0',
  `id_answer` int(11) NOT NULL DEFAULT '0',
  `extra_info` text NOT NULL,
  PRIMARY KEY (`id_quest`,`id_answer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_pollquest_extra`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_polltrack`
--

CREATE TABLE IF NOT EXISTS `learning_polltrack` (
  `id_track` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `id_reference` int(11) NOT NULL DEFAULT '0',
  `id_poll` int(11) NOT NULL DEFAULT '0',
  `date_attempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('valid','not_complete') NOT NULL DEFAULT 'not_complete',
  PRIMARY KEY (`id_track`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_polltrack`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_polltrack_answer`
--

CREATE TABLE IF NOT EXISTS `learning_polltrack_answer` (
  `id_track` int(11) NOT NULL DEFAULT '0',
  `id_quest` int(11) NOT NULL DEFAULT '0',
  `id_answer` int(11) NOT NULL DEFAULT '0',
  `more_info` longtext NOT NULL,
  PRIMARY KEY (`id_track`,`id_quest`,`id_answer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_polltrack_answer`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_prj`
--

CREATE TABLE IF NOT EXISTS `learning_prj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ptitle` varchar(255) NOT NULL DEFAULT '',
  `pgroup` int(11) NOT NULL DEFAULT '0',
  `pprog` tinyint(3) NOT NULL DEFAULT '0',
  `psfiles` tinyint(1) NOT NULL DEFAULT '0',
  `pstasks` tinyint(1) NOT NULL DEFAULT '0',
  `psnews` tinyint(1) NOT NULL DEFAULT '0',
  `pstodo` tinyint(1) NOT NULL DEFAULT '0',
  `psmsg` tinyint(1) NOT NULL DEFAULT '0',
  `cid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_prj`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_prj_files`
--

CREATE TABLE IF NOT EXISTS `learning_prj_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `fname` varchar(255) NOT NULL DEFAULT '',
  `ftitle` varchar(255) NOT NULL DEFAULT '',
  `fver` varchar(255) NOT NULL DEFAULT '',
  `fdesc` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_prj_files`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_prj_news`
--

CREATE TABLE IF NOT EXISTS `learning_prj_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `ntitle` varchar(255) NOT NULL DEFAULT '',
  `ndate` date NOT NULL DEFAULT '0000-00-00',
  `ntxt` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_prj_news`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_prj_tasks`
--

CREATE TABLE IF NOT EXISTS `learning_prj_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tprog` tinyint(3) NOT NULL DEFAULT '0',
  `tname` varchar(255) NOT NULL DEFAULT '',
  `tdesc` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_prj_tasks`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_prj_todo`
--

CREATE TABLE IF NOT EXISTS `learning_prj_todo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `ttitle` varchar(255) NOT NULL DEFAULT '',
  `ttxt` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_prj_todo`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_prj_users`
--

CREATE TABLE IF NOT EXISTS `learning_prj_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `flag` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_prj_users`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_quest_category`
--

CREATE TABLE IF NOT EXISTS `learning_quest_category` (
  `idCategory` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `textof` text NOT NULL,
  `author` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_quest_category`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_quest_type`
--

CREATE TABLE IF NOT EXISTS `learning_quest_type` (
  `type_quest` varchar(255) NOT NULL DEFAULT '',
  `type_file` varchar(255) NOT NULL DEFAULT '',
  `type_class` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`type_quest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_quest_type`
--

INSERT INTO `learning_quest_type` (`type_quest`, `type_file`, `type_class`, `sequence`) VALUES
('associate', 'class.associate.php', 'Associate_Question', 8),
('break_page', 'class.break_page.php', 'BreakPage_Question', 10),
('choice', 'class.choice.php', 'Choice_Question', 1),
('choice_multiple', 'class.choice_multiple.php', 'ChoiceMultiple_Question', 2),
('extended_text', 'class.extended_text.php', 'ExtendedText_Question', 3),
('hot_text', 'class.hot_text.php', 'HotText_Question', 6),
('inline_choice', 'class.inline_choice.php', 'InlineChoice_Question', 5),
('text_entry', 'class.text_entry.php', 'TextEntry_Question', 4),
('title', 'class.title.php', 'Title_Question', 9),
('upload', 'class.upload.php', 'Upload_Question', 7);

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_quest_type_poll`
--

CREATE TABLE IF NOT EXISTS `learning_quest_type_poll` (
  `type_quest` varchar(255) NOT NULL DEFAULT '',
  `type_file` varchar(255) NOT NULL DEFAULT '',
  `type_class` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`type_quest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_quest_type_poll`
--

INSERT INTO `learning_quest_type_poll` (`type_quest`, `type_file`, `type_class`, `sequence`) VALUES
('break_page', 'class.break_page.php', 'BreakPage_QuestionPoll', 7),
('choice', 'class.choice.php', 'Choice_QuestionPoll', 1),
('choice_multiple', 'class.choice_multiple.php', 'ChoiceMultiple_QuestionPoll', 2),
('course_valutation', 'class.course_valutation.php', 'CourseValutation_QuestionPoll', 5),
('doc_valutation', 'class.doc_valutation.php', 'DocValutation_QuestionPoll', 4),
('extended_text', 'class.extended_text.php', 'ExtendedText_QuestionPoll', 3),
('title', 'class.title.php', 'Title_QuestionPoll', 6);

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_repo`
--

CREATE TABLE IF NOT EXISTS `learning_repo` (
  `idRepo` int(11) NOT NULL AUTO_INCREMENT,
  `idParent` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `lev` int(10) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `objectType` varchar(20) NOT NULL DEFAULT '',
  `idResource` int(11) NOT NULL DEFAULT '0',
  `idCategory` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idAuthor` varchar(11) NOT NULL DEFAULT '0',
  `version` varchar(8) NOT NULL DEFAULT '',
  `difficult` enum('_VERYEASY','_EASY','_MEDIUM','_DIFFICULT','_VERYDIFFICULT') NOT NULL DEFAULT '_VERYEASY',
  `description` text NOT NULL,
  `language` varchar(50) NOT NULL DEFAULT '',
  `resource` varchar(255) NOT NULL DEFAULT '',
  `objective` text NOT NULL,
  `dateInsert` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`idRepo`),
  KEY `idParent` (`idParent`),
  KEY `path` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_repo`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_report`
--

CREATE TABLE IF NOT EXISTS `learning_report` (
  `id_report` int(11) NOT NULL AUTO_INCREMENT,
  `report_name` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `use_user_selection` enum('true','false') NOT NULL DEFAULT 'true',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_report`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_report`
--

INSERT INTO `learning_report` (`id_report`, `report_name`, `class_name`, `file_name`, `use_user_selection`, `enabled`) VALUES
(2, 'user_report', 'Report_User', 'class.report_user.php', 'true', 1),
(4, 'courses_report', 'Report_Courses', 'class.report_courses.php', 'true', 1),
(5, 'aggregate_report', 'Report_Aggregate', 'class.report_aggregate.php', 'true', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_report_filter`
--

CREATE TABLE IF NOT EXISTS `learning_report_filter` (
  `id_filter` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_report` int(10) unsigned NOT NULL DEFAULT '0',
  `author` int(10) unsigned NOT NULL DEFAULT '0',
  `creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `filter_name` varchar(255) NOT NULL DEFAULT '',
  `filter_data` text NOT NULL,
  `is_public` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `views` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_filter`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_report_filter`
--

INSERT INTO `learning_report_filter` (`id_filter`, `id_report`, `author`, `creation_date`, `filter_name`, `filter_data`, `is_public`, `views`) VALUES
(13, 4, 270, '0000-00-00 00:00:00', 'Courses - Users', 'a:5:{s:9:"id_report";s:1:"4";s:11:"report_name";s:15:"Courses - Users";s:11:"rows_filter";a:2:{s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}}s:23:"columns_filter_category";s:5:"users";s:14:"columns_filter";a:6:{s:9:"time_belt";a:3:{s:10:"time_range";s:1:"0";s:10:"start_date";s:0:"";s:8:"end_date";s:0:"";}s:21:"org_chart_subdivision";i:0;s:11:"showed_cols";a:7:{i:0;s:12:"_CODE_COURSE";i:1;s:12:"_NAME_COURSE";i:2;s:6:"_INSCR";i:3;s:10:"_MUSTBEGIN";i:4;s:18:"_USER_STATUS_BEGIN";i:5;s:15:"_COMPLETECOURSE";i:6;s:14:"_TOTAL_SESSION";}s:12:"show_percent";b:1;s:9:"all_users";b:1;s:5:"users";a:0:{}}}', 1, 0),
(25, 2, 270, '0000-00-00 00:00:00', 'Users - Courses', 'a:5:{s:9:"id_report";s:1:"2";s:11:"report_name";s:15:"Users - Courses";s:11:"rows_filter";a:2:{s:5:"users";a:0:{}s:9:"all_users";b:1;}s:23:"columns_filter_category";s:7:"courses";s:14:"columns_filter";a:7:{s:21:"org_chart_subdivision";i:0;s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}s:11:"sub_filters";a:0:{}s:16:"filter_exclusive";s:1:"1";s:14:"showed_columns";a:12:{i:0;s:8:"_TH_CODE";i:1;s:25:"_TH_USER_INSCRIPTION_DATE";i:2;s:19:"_TH_USER_START_DATE";i:3;s:17:"_TH_USER_END_DATE";i:4;s:20:"_TH_LAST_ACCESS_DATE";i:5;s:15:"_TH_USER_STATUS";i:6;s:20:"_TH_USER_START_SCORE";i:7;s:20:"_TH_USER_FINAL_SCORE";i:8;s:21:"_TH_USER_COURSE_SCORE";i:9;s:23:"_TH_USER_NUMBER_SESSION";i:10;s:21:"_TH_USER_ELAPSED_TIME";i:11;s:18:"_TH_ESTIMATED_TIME";}s:13:"custom_fields";a:0:{}}}', 1, 0),
(26, 2, 270, '0000-00-00 00:00:00', 'Users - Learning Objects', 'a:5:{s:9:"id_report";s:1:"2";s:11:"report_name";s:24:"Users - Learning Objects";s:11:"rows_filter";a:2:{s:5:"users";a:0:{}s:9:"all_users";b:1;}s:23:"columns_filter_category";s:2:"LO";s:14:"columns_filter";a:6:{s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}s:8:"lo_types";a:8:{s:3:"faq";s:3:"faq";s:8:"glossary";s:8:"glossary";s:8:"htmlpage";s:8:"htmlpage";s:4:"item";s:4:"item";s:4:"link";s:4:"link";s:4:"poll";s:4:"poll";s:8:"scormorg";s:8:"scormorg";s:4:"test";s:4:"test";}s:13:"lo_milestones";a:0:{}s:14:"showed_columns";a:8:{i:0;s:9:"user_name";i:1;s:11:"course_name";i:2;s:13:"course_status";i:3;s:7:"lo_type";i:4;s:7:"lo_name";i:5;s:12:"firstAttempt";i:6;s:11:"lastAttempt";i:7;s:9:"lo_status";}s:13:"custom_fields";a:0:{}}}', 1, 0),
(27, 2, 270, '0000-00-00 00:00:00', 'Users - 30 Days Delay', 'a:5:{s:9:"id_report";s:1:"2";s:11:"report_name";s:21:"Users - 30 Days Delay";s:11:"rows_filter";a:2:{s:5:"users";a:0:{}s:9:"all_users";b:1;}s:23:"columns_filter_category";s:5:"delay";s:14:"columns_filter";a:9:{s:21:"report_type_completed";b:1;s:19:"report_type_started";b:1;s:21:"day_from_subscription";s:2:"30";s:20:"day_until_course_end";s:0:"";s:21:"date_until_course_end";s:0:"";s:21:"org_chart_subdivision";i:0;s:11:"all_courses";b:1;s:16:"selected_courses";a:0:{}s:14:"showed_columns";a:7:{i:0;s:9:"_LASTNAME";i:1;s:5:"_NAME";i:2;s:7:"_STATUS";i:3;s:6:"_EMAIL";i:4;s:11:"_DATE_INSCR";i:5;s:18:"_DATE_FIRST_ACCESS";i:6;s:22:"_DATE_COURSE_COMPLETED";}}}', 1, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `learning_report_schedule`
--

CREATE TABLE IF NOT EXISTS `learning_report_schedule` (
  `id_report_schedule` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_report_filter` int(11) unsigned NOT NULL DEFAULT '0',
  `id_creator` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `period` varchar(255) NOT NULL DEFAULT '',
  `time` time NOT NULL DEFAULT '00:00:00',
  `creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_report_schedule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_report_schedule`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_report_schedule_recipient`
--

CREATE TABLE IF NOT EXISTS `learning_report_schedule_recipient` (
  `id_report_schedule` int(11) unsigned NOT NULL DEFAULT '0',
  `id_user` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_report_schedule`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_report_schedule_recipient`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_reservation_category`
--

CREATE TABLE IF NOT EXISTS `learning_reservation_category` (
  `idCategory` int(11) NOT NULL AUTO_INCREMENT,
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `maxSubscription` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_reservation_category`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_reservation_events`
--

CREATE TABLE IF NOT EXISTS `learning_reservation_events` (
  `idEvent` int(11) NOT NULL AUTO_INCREMENT,
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `idLaboratory` int(11) NOT NULL DEFAULT '0',
  `idCategory` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` longtext,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `maxUser` int(11) NOT NULL DEFAULT '0',
  `deadLine` date NOT NULL DEFAULT '0000-00-00',
  `fromTime` time NOT NULL DEFAULT '00:00:00',
  `toTime` time NOT NULL DEFAULT '00:00:00',
  PRIMARY KEY (`idEvent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_reservation_events`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_reservation_perm`
--

CREATE TABLE IF NOT EXISTS `learning_reservation_perm` (
  `event_id` int(11) NOT NULL DEFAULT '0',
  `user_idst` int(11) NOT NULL DEFAULT '0',
  `perm` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`event_id`,`user_idst`,`perm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_reservation_perm`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_reservation_subscribed`
--

CREATE TABLE IF NOT EXISTS `learning_reservation_subscribed` (
  `idstUser` int(11) NOT NULL DEFAULT '0',
  `idEvent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idstUser`,`idEvent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_reservation_subscribed`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_scorm_items`
--

CREATE TABLE IF NOT EXISTS `learning_scorm_items` (
  `idscorm_item` int(11) NOT NULL AUTO_INCREMENT,
  `idscorm_organization` int(11) NOT NULL DEFAULT '0',
  `idscorm_parentitem` int(11) DEFAULT NULL,
  `adlcp_prerequisites` varchar(200) DEFAULT NULL,
  `adlcp_maxtimeallowed` varchar(24) DEFAULT NULL,
  `adlcp_timelimitaction` varchar(24) DEFAULT NULL,
  `adlcp_datafromlms` varchar(255) DEFAULT NULL,
  `adlcp_masteryscore` varchar(200) DEFAULT NULL,
  `item_identifier` varchar(255) DEFAULT NULL,
  `identifierref` varchar(255) DEFAULT NULL,
  `idscorm_resource` int(11) DEFAULT NULL,
  `isvisible` set('true','false') DEFAULT 'true',
  `parameters` varchar(100) DEFAULT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `nChild` int(11) NOT NULL DEFAULT '0',
  `nDescendant` int(11) NOT NULL DEFAULT '0',
  `adlcp_completionthreshold` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`idscorm_item`),
  UNIQUE KEY `idscorm_organization` (`idscorm_organization`,`item_identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_scorm_items`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_scorm_items_track`
--

CREATE TABLE IF NOT EXISTS `learning_scorm_items_track` (
  `idscorm_item_track` int(11) NOT NULL AUTO_INCREMENT,
  `idscorm_organization` int(11) NOT NULL DEFAULT '0',
  `idscorm_item` int(11) DEFAULT NULL,
  `idReference` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idscorm_tracking` int(11) DEFAULT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'not attempted',
  `nChild` int(11) NOT NULL DEFAULT '0',
  `nChildCompleted` int(11) NOT NULL DEFAULT '0',
  `nDescendant` int(11) NOT NULL DEFAULT '0',
  `nDescendantCompleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idscorm_item_track`),
  KEY `idscorm_organization` (`idscorm_organization`),
  KEY `idscorm_item` (`idscorm_item`),
  KEY `idUser` (`idUser`),
  KEY `idscorm_tracking` (`idscorm_tracking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Join table 3 factor' ;

--
-- Dump dei dati per la tabella `learning_scorm_items_track`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_scorm_organizations`
--

CREATE TABLE IF NOT EXISTS `learning_scorm_organizations` (
  `idscorm_organization` int(11) NOT NULL AUTO_INCREMENT,
  `org_identifier` varchar(255) NOT NULL DEFAULT '',
  `idscorm_package` int(11) NOT NULL DEFAULT '0',
  `title` varchar(100) DEFAULT NULL,
  `nChild` int(11) NOT NULL DEFAULT '0',
  `nDescendant` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idscorm_organization`),
  UNIQUE KEY `idsco_package_unique` (`org_identifier`,`idscorm_package`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_scorm_organizations`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_scorm_package`
--

CREATE TABLE IF NOT EXISTS `learning_scorm_package` (
  `idscorm_package` int(11) NOT NULL AUTO_INCREMENT,
  `idpackage` varchar(255) NOT NULL DEFAULT '',
  `idProg` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `defaultOrg` varchar(255) NOT NULL DEFAULT '',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `scormVersion` varchar(10) NOT NULL DEFAULT '1.2',
  PRIMARY KEY (`idscorm_package`),
  KEY `idUser` (`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_scorm_package`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_scorm_resources`
--

CREATE TABLE IF NOT EXISTS `learning_scorm_resources` (
  `idscorm_resource` int(11) NOT NULL AUTO_INCREMENT,
  `idsco` varchar(255) NOT NULL DEFAULT '',
  `idscorm_package` int(11) NOT NULL DEFAULT '0',
  `scormtype` set('sco','asset') DEFAULT NULL,
  `href` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idscorm_resource`),
  UNIQUE KEY `idsco_package_unique` (`idsco`,`idscorm_package`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_scorm_resources`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_scorm_tracking`
--

CREATE TABLE IF NOT EXISTS `learning_scorm_tracking` (
  `idscorm_tracking` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idReference` int(11) NOT NULL DEFAULT '0',
  `idscorm_item` int(11) NOT NULL DEFAULT '0',
  `user_name` varchar(255) DEFAULT NULL,
  `lesson_location` varchar(255) DEFAULT NULL,
  `credit` varchar(24) DEFAULT NULL,
  `lesson_status` varchar(24) DEFAULT NULL,
  `entry` varchar(24) DEFAULT NULL,
  `score_raw` float DEFAULT NULL,
  `score_max` float DEFAULT NULL,
  `score_min` float DEFAULT NULL,
  `total_time` varchar(15) DEFAULT '0000:00:00.00',
  `lesson_mode` varchar(24) DEFAULT NULL,
  `exit` varchar(24) DEFAULT NULL,
  `session_time` varchar(15) DEFAULT NULL,
  `suspend_data` blob,
  `launch_data` blob,
  `comments` blob,
  `comments_from_lms` blob,
  `xmldata` longblob,
  `first_access` datetime DEFAULT NULL,
  `last_access` datetime DEFAULT NULL,
  PRIMARY KEY (`idscorm_tracking`),
  UNIQUE KEY `Unique_tracking_usersco` (`idUser`,`idReference`,`idscorm_item`),
  KEY `idUser` (`idUser`),
  KEY `idscorm_resource` (`idReference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_scorm_tracking`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_scorm_tracking_history`
--

CREATE TABLE IF NOT EXISTS `learning_scorm_tracking_history` (
  `idscorm_tracking` int(11) NOT NULL DEFAULT '0',
  `date_action` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `score_raw` float DEFAULT NULL,
  `score_max` float DEFAULT NULL,
  `session_time` varchar(15) DEFAULT NULL,
  `lesson_status` varchar(24) NOT NULL DEFAULT '',
  PRIMARY KEY (`idscorm_tracking`,`date_action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_scorm_tracking_history`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_statuschangelog`
--

CREATE TABLE IF NOT EXISTS `learning_statuschangelog` (
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `status_user` tinyint(1) NOT NULL DEFAULT '0',
  `when_do` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`idUser`,`idCourse`,`when_do`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_statuschangelog`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_sysforum`
--

CREATE TABLE IF NOT EXISTS `learning_sysforum` (
  `idMessage` int(11) NOT NULL AUTO_INCREMENT,
  `key1` varchar(255) NOT NULL DEFAULT '',
  `key2` int(11) NOT NULL DEFAULT '0',
  `key3` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `textof` text NOT NULL,
  `posted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `author` int(11) NOT NULL DEFAULT '0',
  `attach` varchar(255) NOT NULL DEFAULT '',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idMessage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_sysforum`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_teacher_profile`
--

CREATE TABLE IF NOT EXISTS `learning_teacher_profile` (
  `id_user` int(11) NOT NULL DEFAULT '0',
  `curriculum` text NOT NULL,
  `publications` text NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_teacher_profile`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_test`
--

CREATE TABLE IF NOT EXISTS `learning_test` (
  `idTest` int(11) NOT NULL AUTO_INCREMENT,
  `author` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `point_type` tinyint(1) NOT NULL DEFAULT '0',
  `point_required` double NOT NULL DEFAULT '0',
  `display_type` tinyint(1) NOT NULL DEFAULT '0',
  `order_type` tinyint(1) NOT NULL DEFAULT '0',
  `shuffle_answer` tinyint(1) NOT NULL DEFAULT '0',
  `question_random_number` int(4) NOT NULL DEFAULT '0',
  `save_keep` tinyint(1) NOT NULL DEFAULT '0',
  `mod_doanswer` tinyint(1) NOT NULL DEFAULT '1',
  `can_travel` tinyint(1) NOT NULL DEFAULT '1',
  `show_only_status` tinyint(1) NOT NULL DEFAULT '0',
  `show_score` tinyint(1) NOT NULL DEFAULT '1',
  `show_score_cat` tinyint(1) NOT NULL DEFAULT '0',
  `show_doanswer` tinyint(1) NOT NULL DEFAULT '0',
  `show_solution` tinyint(1) NOT NULL DEFAULT '0',
  `time_dependent` tinyint(1) NOT NULL DEFAULT '0',
  `time_assigned` int(6) NOT NULL DEFAULT '0',
  `penality_test` tinyint(1) NOT NULL DEFAULT '0',
  `penality_time_test` double NOT NULL DEFAULT '0',
  `penality_quest` tinyint(1) NOT NULL DEFAULT '0',
  `penality_time_quest` double NOT NULL DEFAULT '0',
  `max_attempt` int(11) NOT NULL DEFAULT '0',
  `hide_info` tinyint(1) NOT NULL DEFAULT '0',
  `order_info` text NOT NULL,
  `use_suspension` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `suspension_num_attempts` int(10) unsigned NOT NULL DEFAULT '0',
  `suspension_num_hours` int(10) unsigned NOT NULL DEFAULT '0',
  `suspension_prerequisites` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `chart_options` text NOT NULL,
  `mandatory_answer` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `score_max` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idTest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_test`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_testquest`
--

CREATE TABLE IF NOT EXISTS `learning_testquest` (
  `idQuest` int(11) NOT NULL AUTO_INCREMENT,
  `idTest` int(11) NOT NULL DEFAULT '0',
  `idCategory` int(11) NOT NULL DEFAULT '0',
  `type_quest` varchar(255) NOT NULL DEFAULT '',
  `title_quest` text NOT NULL,
  `difficult` int(1) NOT NULL DEFAULT '3',
  `time_assigned` int(5) NOT NULL DEFAULT '0',
  `sequence` int(5) NOT NULL DEFAULT '0',
  `page` int(11) NOT NULL DEFAULT '0',
  `shuffle` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idQuest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_testquest`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_testquestanswer`
--

CREATE TABLE IF NOT EXISTS `learning_testquestanswer` (
  `idAnswer` int(11) NOT NULL AUTO_INCREMENT,
  `idQuest` int(11) NOT NULL DEFAULT '0',
  `sequence` int(11) NOT NULL DEFAULT '0',
  `is_correct` int(11) NOT NULL DEFAULT '0',
  `answer` text NOT NULL,
  `comment` text NOT NULL,
  `score_correct` double NOT NULL DEFAULT '0',
  `score_incorrect` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`idAnswer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_testquestanswer`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_testquestanswer_associate`
--

CREATE TABLE IF NOT EXISTS `learning_testquestanswer_associate` (
  `idAnswer` int(11) NOT NULL AUTO_INCREMENT,
  `idQuest` int(11) NOT NULL DEFAULT '0',
  `answer` text NOT NULL,
  PRIMARY KEY (`idAnswer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_testquestanswer_associate`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_testquest_extra`
--

CREATE TABLE IF NOT EXISTS `learning_testquest_extra` (
  `idQuest` int(11) NOT NULL DEFAULT '0',
  `idAnswer` int(11) NOT NULL DEFAULT '0',
  `extra_info` text NOT NULL,
  PRIMARY KEY (`idQuest`,`idAnswer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_testquest_extra`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_testtrack`
--

CREATE TABLE IF NOT EXISTS `learning_testtrack` (
  `idTrack` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idReference` int(11) NOT NULL DEFAULT '0',
  `idTest` int(11) NOT NULL DEFAULT '0',
  `date_attempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_attempt_mod` datetime DEFAULT NULL,
  `date_end_attempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_page_seen` int(11) NOT NULL DEFAULT '0',
  `last_page_saved` int(11) NOT NULL DEFAULT '0',
  `number_of_save` int(11) NOT NULL DEFAULT '0',
  `number_of_attempt` int(11) NOT NULL DEFAULT '0',
  `score` double DEFAULT NULL,
  `bonus_score` double NOT NULL DEFAULT '0',
  `score_status` enum('valid','not_checked','not_passed','passed','not_complete','doing') NOT NULL DEFAULT 'not_complete',
  `comment` text NOT NULL,
  `attempts_for_suspension` int(10) unsigned NOT NULL DEFAULT '0',
  `suspended_until` datetime DEFAULT NULL,
  PRIMARY KEY (`idTrack`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_testtrack`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_testtrack_answer`
--

CREATE TABLE IF NOT EXISTS `learning_testtrack_answer` (
  `idTrack` int(11) NOT NULL DEFAULT '0',
  `idQuest` int(11) NOT NULL DEFAULT '0',
  `idAnswer` int(11) NOT NULL DEFAULT '0',
  `score_assigned` double NOT NULL DEFAULT '0',
  `more_info` longtext NOT NULL,
  `manual_assigned` tinyint(1) NOT NULL DEFAULT '0',
  `user_answer` tinyint(1) unsigned NULL DEFAULT '0',
  PRIMARY KEY (`idTrack`,`idQuest`,`idAnswer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_testtrack_answer`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_testtrack_page`
--

CREATE TABLE IF NOT EXISTS `learning_testtrack_page` (
  `idTrack` int(11) NOT NULL DEFAULT '0',
  `page` int(3) NOT NULL DEFAULT '0',
  `display_from` datetime DEFAULT NULL,
  `display_to` datetime DEFAULT NULL,
  `accumulated` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idTrack`,`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_testtrack_page`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_testtrack_quest`
--

CREATE TABLE IF NOT EXISTS `learning_testtrack_quest` (
  `idTrack` int(11) NOT NULL DEFAULT '0',
  `idQuest` int(11) NOT NULL DEFAULT '0',
  `page` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idTrack`,`idQuest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_testtrack_quest`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_testtrack_times`
--

CREATE TABLE IF NOT EXISTS `learning_testtrack_times` (
  `idTrack` int(11) NOT NULL DEFAULT '0',
  `idReference` int(11) NOT NULL DEFAULT '0',
  `idTest` int(11) NOT NULL DEFAULT '0',
  `date_attempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `number_time` tinyint(4) NOT NULL DEFAULT '0',
  `score` double NOT NULL DEFAULT '0',
  `score_status` varchar(50) NOT NULL DEFAULT '',
  `date_begin` DATETIME NOT NULL ,
  `date_end` DATETIME NOT NULL ,
  `time` INT( 11 ) NOT NULL ,
  PRIMARY KEY (`idTrack`,`number_time`,`idTest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_testtrack_times`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_time_period`
--

CREATE TABLE IF NOT EXISTS `learning_time_period` (
  `id_period` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) NOT NULL DEFAULT '',
  `start_date` date NOT NULL DEFAULT '0000-00-00',
  `end_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_time_period`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_trackingeneral`
--

CREATE TABLE IF NOT EXISTS `learning_trackingeneral` (
  `idTrack` int(11) NOT NULL AUTO_INCREMENT,
  `idEnter` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `session_id` varchar(255) NOT NULL DEFAULT '',
  `function` varchar(250) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `timeof` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`idTrack`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_trackingeneral`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_tracksession`
--

CREATE TABLE IF NOT EXISTS `learning_tracksession` (
  `idEnter` int(11) NOT NULL AUTO_INCREMENT,
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `session_id` varchar(255) NOT NULL DEFAULT '',
  `enterTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `numOp` int(5) NOT NULL DEFAULT '0',
  `lastFunction` varchar(50) NOT NULL DEFAULT '',
  `lastOp` varchar(5) NOT NULL DEFAULT '',
  `lastTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip_address` varchar(40) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idEnter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_tracksession`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_transaction`
--

CREATE TABLE IF NOT EXISTS `learning_transaction` (
  `id_transaction` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_confirm` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `price` int(11) NOT NULL DEFAULT '0',
  `payment_status` tinyint(1) NOT NULL DEFAULT '0',
  `course_status` tinyint(1) NOT NULL DEFAULT '0',
  `method` varchar(255) NULL DEFAULT '',
  `payment_note` text NOT NULL,
  `course_note` text NOT NULL,
  PRIMARY KEY (`id_transaction`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_transaction`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_transaction_info`
--

CREATE TABLE IF NOT EXISTS `learning_transaction_info` (
  `id_transaction` int(11) NOT NULL DEFAULT '0',
  `id_course` int(11) NOT NULL DEFAULT '0',
  `id_date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_transaction`,`id_course`,`id_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_transaction_info`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_webpages`
--

CREATE TABLE IF NOT EXISTS `learning_webpages` (
  `idPages` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `language` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(5) NOT NULL DEFAULT '0',
  `publish` tinyint(1) NOT NULL DEFAULT '0',
  `in_home` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idPages`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `learning_webpages`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `learning_wiki_course`
--

CREATE TABLE IF NOT EXISTS `learning_wiki_course` (
  `course_id` int(11) NOT NULL DEFAULT '0',
  `wiki_id` int(11) NOT NULL DEFAULT '0',
  `is_owner` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`course_id`,`wiki_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `learning_wiki_course`
--


--
-- Limiti per le tabelle scaricate
--










-- --------------------------------------------------------


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
