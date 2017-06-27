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
-- Struttura della tabella `conference_booking`
--

CREATE TABLE IF NOT EXISTS `conference_booking` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL DEFAULT '0',
  `platform` varchar(255) NOT NULL DEFAULT '',
  `module` varchar(100) NOT NULL DEFAULT '',
  `user_idst` int(11) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `conference_booking`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_chatperm`
--

CREATE TABLE IF NOT EXISTS `conference_chatperm` (
  `room_id` int(11) NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL DEFAULT '',
  `user_idst` int(11) NOT NULL DEFAULT '0',
  `perm` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`room_id`,`module`,`user_idst`,`perm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `conference_chatperm`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_chat_msg`
--

CREATE TABLE IF NOT EXISTS `conference_chat_msg` (
  `msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `id_room` int(11) NOT NULL DEFAULT '0',
  `userid` varchar(255) NOT NULL DEFAULT '',
  `send_to` int(11) DEFAULT NULL,
  `sent_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL,
  PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `conference_chat_msg`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_dimdim`
--

CREATE TABLE IF NOT EXISTS `conference_dimdim` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idConference` bigint(20) NOT NULL DEFAULT '0',
  `confkey` varchar(255) DEFAULT NULL,
  `emailuser` varchar(255) DEFAULT NULL,
  `displayname` varchar(255) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `audiovideosettings` int(11) DEFAULT NULL,
  `maxmikes` int(11) DEFAULT NULL,
  `schedule_info` text NOT NULL,
  `extra_conf` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idConference` (`idConference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `conference_dimdim`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_menu`
--

CREATE TABLE IF NOT EXISTS `conference_menu` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `conference_menu`
--

INSERT INTO `conference_menu` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(1, '_MAIN_CONFERENCE_MANAGMENT', '', 1, 'true');

-- --------------------------------------------------------

--
-- Struttura della tabella `conference_menu_under`
--

CREATE TABLE IF NOT EXISTS `conference_menu_under` (
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
  PRIMARY KEY (`idUnder`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `conference_menu_under`
--

INSERT INTO `conference_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`) VALUES
(2, 1, 'room', '_ROOM', 'room', 'view', NULL, 2, 'class.room.php', 'Module_Room');

-- --------------------------------------------------------

--
-- Struttura della tabella `conference_room`
--

CREATE TABLE IF NOT EXISTS `conference_room` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idCal` bigint(20) NOT NULL DEFAULT '0',
  `idCourse` bigint(20) NOT NULL DEFAULT '0',
  `idSt` bigint(20) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `room_type` varchar(255) DEFAULT NULL,
  `starttime` bigint(20) DEFAULT NULL,
  `endtime` bigint(20) DEFAULT NULL,
  `meetinghours` int(11) DEFAULT NULL,
  `maxparticipants` int(11) DEFAULT NULL,
  `bookable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idCourse` (`idCourse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `conference_room`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_rules_admin`
--

CREATE TABLE IF NOT EXISTS `conference_rules_admin` (
  `server_status` enum('yes','no') NOT NULL DEFAULT 'yes',
  `enable_recording_function` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_advice_insert` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_write` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_chat_recording` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_private_subroom` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_public_subroom` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_drawboard_watch` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_drawboard_write` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_audio` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_webcam` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_stream_watch` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_strem_write` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_remote_desktop` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  PRIMARY KEY (`server_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `conference_rules_admin`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_rules_room`
--

CREATE TABLE IF NOT EXISTS `conference_rules_room` (
  `id_room` int(11) NOT NULL AUTO_INCREMENT,
  `enable_recording_function` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_advice_insert` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_write` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_chat_recording` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_private_subroom` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_public_subroom` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_drawboard_watch` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_drawboard_write` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_audio` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_webcam` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_stream_watch` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_strem_write` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `enable_remote_desktop` enum('admin','alluser','noone') NOT NULL DEFAULT 'noone',
  `room_name` varchar(255) NOT NULL DEFAULT '',
  `room_type` enum('course','private','public') NOT NULL DEFAULT 'course',
  `id_source` int(11) NOT NULL DEFAULT '0',
  `room_parent` int(11) NOT NULL DEFAULT '0',
  `advice_one` text,
  `advice_two` text,
  `advice_three` text,
  `room_logo` varchar(255) DEFAULT NULL,
  `room_sponsor` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_room`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `conference_rules_room`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_rules_root`
--

CREATE TABLE IF NOT EXISTS `conference_rules_root` (
  `system_type` enum('p2p','server') NOT NULL DEFAULT 'p2p',
  `server_ip` varchar(255) DEFAULT NULL,
  `server_port` int(5) unsigned DEFAULT NULL,
  `server_path` varchar(255) DEFAULT NULL,
  `max_user_at_time` int(11) unsigned NOT NULL DEFAULT '0',
  `max_room_at_time` int(11) unsigned NOT NULL DEFAULT '0',
  `max_subroom_for_room` int(11) unsigned NOT NULL DEFAULT '0',
  `enable_drawboard` enum('yes','no') NOT NULL DEFAULT 'no',
  `enable_livestream` enum('yes','no') NOT NULL DEFAULT 'no',
  `enable_remote_desktop` enum('yes','no') NOT NULL DEFAULT 'no',
  `enable_webcam` enum('yes','no') NOT NULL DEFAULT 'no',
  `enable_audio` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`system_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `conference_rules_root`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_rules_user`
--

CREATE TABLE IF NOT EXISTS `conference_rules_user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `last_hit` int(11) NOT NULL DEFAULT '0',
  `id_room` int(11) NOT NULL DEFAULT '0',
  `userid` varchar(255) NOT NULL DEFAULT '',
  `user_ip` varchar(15) NOT NULL DEFAULT '',
  `first_name` varchar(255) NOT NULL DEFAULT '',
  `last_name` varchar(255) NOT NULL DEFAULT '',
  `level` int(11) NOT NULL DEFAULT '0',
  `auto_reload` tinyint(1) NOT NULL DEFAULT '0',
  `banned_until` datetime DEFAULT NULL,
  `chat_record` enum('yes','no') NOT NULL DEFAULT 'no',
  `advice_insert` enum('yes','no') NOT NULL DEFAULT 'no',
  `write_in_chat` enum('yes','no') NOT NULL DEFAULT 'no',
  `request_to_chat` enum('yes','no') NOT NULL DEFAULT 'no',
  `create_public_subroom` enum('yes','no') NOT NULL DEFAULT 'no',
  `enable_webcam` enum('yes','no') NOT NULL DEFAULT 'no',
  `enable_audio` enum('yes','no') NOT NULL DEFAULT 'no',
  `enable_drawboard_watch` enum('yes','no') NOT NULL DEFAULT 'no',
  `enable_drawboard_draw` enum('yes','no') NOT NULL DEFAULT 'no',
  `enable_livestream_watch` enum('yes','no') NOT NULL DEFAULT 'no',
  `enable_livestream_publish` enum('yes','no') NOT NULL DEFAULT 'no',
  `accept_private_message` enum('yes','no') NOT NULL DEFAULT 'no',
  `picture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `conference_rules_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_teleskill`
--

CREATE TABLE IF NOT EXISTS `conference_teleskill` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idConference` bigint(20) NOT NULL DEFAULT '0',
  `roomid` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idConference` (`idConference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `conference_teleskill`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_teleskill_log`
--

CREATE TABLE IF NOT EXISTS `conference_teleskill_log` (
  `roomid` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `role` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `duration` int(11) NOT NULL DEFAULT '0',
  `access` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`roomid`,`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `conference_teleskill_log`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `conference_teleskill_room`
--

CREATE TABLE IF NOT EXISTS `conference_teleskill_room` (
  `roomid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `zone` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bookable` tinyint(1) NOT NULL DEFAULT '0',
  `capacity` int(11) DEFAULT NULL,
  PRIMARY KEY (`roomid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `conference_teleskill_room`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_admin_course`
--

CREATE TABLE IF NOT EXISTS `core_admin_course` (
  `idst_user` int(11) NOT NULL DEFAULT '0',
  `type_of_entry` varchar(50) NOT NULL DEFAULT '',
  `id_entry` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idst_user`,`type_of_entry`,`id_entry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_admin_course`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_admin_tree`
--

CREATE TABLE IF NOT EXISTS `core_admin_tree` (
  `idst` varchar(11) NOT NULL DEFAULT '',
  `idstAdmin` varchar(11) NOT NULL DEFAULT '',
  PRIMARY KEY (`idst`,`idstAdmin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_admin_tree`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_calendar`
--

CREATE TABLE IF NOT EXISTS `core_calendar` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `class` varchar(30) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `private` varchar(2) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `type` bigint(20) DEFAULT NULL,
  `visibility_rules` tinytext,
  `_owner` int(11) DEFAULT NULL,
  `_day` smallint(2) DEFAULT NULL,
  `_month` smallint(2) DEFAULT NULL,
  `_year` smallint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_calendar`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_code`
--

CREATE TABLE IF NOT EXISTS `core_code` (
  `code` varchar(255) NOT NULL DEFAULT '',
  `idCodeGroup` int(11) NOT NULL DEFAULT '0',
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `idUser` int(11) DEFAULT NULL,
  `unlimitedUse` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_code`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_code_association`
--

CREATE TABLE IF NOT EXISTS `core_code_association` (
  `code` varchar(255) NOT NULL DEFAULT '',
  `idUser` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`,`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_code_association`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_code_course`
--

CREATE TABLE IF NOT EXISTS `core_code_course` (
  `idCodeGroup` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCodeGroup`,`idCourse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_code_course`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_code_groups`
--

CREATE TABLE IF NOT EXISTS `core_code_groups` (
  `idCodeGroup` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` longtext NOT NULL,
  PRIMARY KEY (`idCodeGroup`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_code_groups`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_code_org`
--

CREATE TABLE IF NOT EXISTS `core_code_org` (
  `idCodeGroup` int(11) NOT NULL DEFAULT '0',
  `idOrg` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idCodeGroup`,`idOrg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_code_org`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_connection`
--

CREATE TABLE IF NOT EXISTS `core_connection` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT '',
  `params` text,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_connection`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_connector`
--

CREATE TABLE IF NOT EXISTS `core_connector` (
  `type` varchar(25) NOT NULL DEFAULT '',
  `file` varchar(255) NOT NULL DEFAULT '',
  `class` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_connector`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_country`
--

CREATE TABLE IF NOT EXISTS `core_country` (
  `id_country` int(11) NOT NULL AUTO_INCREMENT,
  `name_country` varchar(64) NOT NULL DEFAULT '',
  `iso_code_country` varchar(3) NOT NULL DEFAULT '',
  `id_zone` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_country`),
  KEY `IDX_COUNTRIES_NAME` (`name_country`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_country`
--

INSERT INTO `core_country` (`id_country`, `name_country`, `iso_code_country`, `id_zone`) VALUES
(1, 'AFGHANISTAN', 'AF', 0),
(2, 'ALAND ISLANDS', 'AX', 0),
(3, 'ALBANIA', 'AL', 0),
(4, 'ALGERIA', 'DZ', 0),
(5, 'AMERICAN SAMOA', 'AS', 0),
(6, 'ANDORRA', 'AD', 0),
(7, 'ANGOLA', 'AO', 0),
(8, 'ANGUILLA', 'AI', 0),
(9, 'ANTARCTICA', 'AQ', 0),
(10, 'ANTIGUA AND BARBUDA', 'AG', 0),
(11, 'ARGENTINA', 'AR', 0),
(12, 'ARMENIA', 'AM', 0),
(13, 'ARUBA', 'AW', 0),
(14, 'AUSTRALIA', 'AU', 0),
(15, 'AUSTRIA', 'AT', 0),
(16, 'AZERBAIJAN', 'AZ', 0),
(17, 'BAHAMAS', 'BS', 0),
(18, 'BAHRAIN', 'BH', 0),
(19, 'BANGLADESH', 'BD', 0),
(20, 'BARBADOS', 'BB', 0),
(21, 'BELARUS', 'BY', 0),
(22, 'BELGIUM', 'BE', 0),
(23, 'BELIZE', 'BZ', 0),
(24, 'BENIN', 'BJ', 0),
(25, 'BERMUDA', 'BM', 0),
(26, 'BHUTAN', 'BT', 0),
(27, 'BOLIVIA', 'BO', 0),
(28, 'BOSNIA AND HERZEGOVINA', 'BA', 0),
(29, 'BOTSWANA', 'BW', 0),
(30, 'BOUVET ISLAND', 'BV', 0),
(31, 'BRAZIL', 'BR', 0),
(32, 'BRITISH INDIAN OCEAN TERRITORY', 'IO', 0),
(33, 'BRUNEI DARUSSALAM', 'BN', 0),
(34, 'BULGARIA', 'BG', 0),
(35, 'BURKINA FASO', 'BF', 0),
(36, 'BURUNDI', 'BI', 0),
(37, 'CAMBODIA', 'KH', 0),
(38, 'CAMEROON', 'CM', 0),
(39, 'CANADA', 'CA', 0),
(40, 'CAPE VERDE', 'CV', 0),
(41, 'CAYMAN ISLANDS', 'KY', 0),
(42, 'CENTRAL AFRICAN REPUBLIC', 'CF', 0),
(43, 'CHAD', 'TD', 0),
(44, 'CHILE', 'CL', 0),
(45, 'CHINA', 'CN', 0),
(46, 'CHRISTMAS ISLAND', 'CX', 0),
(47, 'COCOS (KEELING) ISLANDS', 'CC', 0),
(48, 'COLOMBIA', 'CO', 0),
(49, 'COMOROS', 'KM', 0),
(50, 'CONGO', 'CG', 0),
(51, 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'CD', 0),
(52, 'COOK ISLANDS', 'CK', 0),
(53, 'COSTA RICA', 'CR', 0),
(54, 'IVORY COAST', 'CI', 0),
(55, 'CROATIA', 'HR', 0),
(56, 'CUBA', 'CU', 0),
(57, 'CYPRUS', 'CY', 0),
(58, 'CZECH REPUBLIC', 'CZ', 0),
(59, 'DENMARK', 'DK', 0),
(60, 'DJIBOUTI', 'DJ', 0),
(61, 'DOMINICA', 'DM', 0),
(62, 'DOMINICAN REPUBLIC', 'DO', 0),
(63, 'ECUADOR', 'EC', 0),
(64, 'EGYPT', 'EG', 0),
(65, 'EL SALVADOR', 'SV', 0),
(66, 'EQUATORIAL GUINEA', 'GQ', 0),
(67, 'ERITREA', 'ER', 0),
(68, 'ESTONIA', 'EE', 0),
(69, 'ETHIOPIA', 'ET', 0),
(70, 'FALKLAND ISLANDS (MALVINAS)', 'FK', 0),
(71, 'FAROE ISLANDS', 'FO', 0),
(72, 'FIJI', 'FJ', 0),
(73, 'FINLAND', 'FI', 0),
(74, 'FRANCE', 'FR', 0),
(75, 'FRENCH GUIANA', 'GF', 0),
(76, 'FRENCH POLYNESIA', 'PF', 0),
(77, 'FRENCH SOUTHERN TERRITORIES', 'TF', 0),
(78, 'GABON', 'GA', 0),
(79, 'GAMBIA', 'GM', 0),
(80, 'GEORGIA', 'GE', 0),
(81, 'GERMANY', 'DE', 0),
(82, 'GHANA', 'GH', 0),
(83, 'GIBRALTAR', 'GI', 0),
(84, 'GREECE', 'GR', 0),
(85, 'GREENLAND', 'GL', 0),
(86, 'GRENADA', 'GD', 0),
(87, 'GUADELOUPE', 'GP', 0),
(88, 'GUAM', 'GU', 0),
(89, 'GUATEMALA', 'GT', 0),
(90, 'GUERNSEY', 'GG', 0),
(91, 'GUINEA', 'GN', 0),
(92, 'GUINEA-BISSAU', 'GW', 0),
(93, 'GUYANA', 'GY', 0),
(94, 'HAITI', 'HT', 0),
(95, 'HEARD ISLAND AND MCDONALD ISLANDS', 'HM', 0),
(96, 'HONDURAS', 'HN', 0),
(97, 'HONG KONG', 'HK', 0),
(98, 'HUNGARY', 'HU', 0),
(99, 'ICELAND', 'IS', 0),
(100, 'INDIA', 'IN', 0),
(101, 'INDONESIA', 'ID', 0),
(102, 'IRAN', 'IR', 0),
(103, 'IRAQ', 'IQ', 0),
(104, 'IRELAND', 'IE', 0),
(105, 'ISLE OF MAN', 'IM', 0),
(106, 'ISRAEL', 'IL', 0),
(107, 'ITALY', 'IT', 0),
(108, 'JAMAICA', 'JM', 0),
(109, 'JAPAN', 'JP', 0),
(110, 'JERSEY', 'JE', 0),
(111, 'JORDAN', 'JO', 0),
(112, 'KAZAKHSTAN', 'KZ', 0),
(113, 'KENYA', 'KE', 0),
(114, 'KIRIBATI', 'KI', 0),
(115, 'KOREA, DEMOCRATIC PEOPLE''S REPUBLIC OF', 'KP', 0),
(116, 'KOREA, REPUBLIC OF', 'KR', 0),
(117, 'KUWAIT', 'KW', 0),
(118, 'KYRGYZSTAN', 'KG', 0),
(119, 'LAO PEOPLE''S DEMOCRATIC REPUBLIC', 'LA', 0),
(120, 'LATVIA', 'LV', 0),
(121, 'LEBANON', 'LB', 0),
(122, 'LESOTHO', 'LS', 0),
(123, 'LIBERIA', 'LR', 0),
(124, 'LIBYAN ARAB JAMAHIRIYA', 'LY', 0),
(125, 'LIECHTENSTEIN', 'LI', 0),
(126, 'LITHUANIA', 'LT', 0),
(127, 'LUXEMBOURG', 'LU', 0),
(128, 'MACAO', 'MO', 0),
(129, 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'MK', 0),
(130, 'MADAGASCAR', 'MG', 0),
(131, 'MALAWI', 'MW', 0),
(132, 'MALAYSIA', 'MY', 0),
(133, 'MALDIVES', 'MV', 0),
(134, 'MALI', 'ML', 0),
(135, 'MALTA', 'MT', 0),
(136, 'MARSHALL ISLANDS', 'MH', 0),
(137, 'MARTINIQUE', 'MQ', 0),
(138, 'MAURITANIA', 'MR', 0),
(139, 'MAURITIUS', 'MU', 0),
(140, 'MAYOTTE', 'YT', 0),
(141, 'MEXICO', 'MX', 0),
(142, 'MICRONESIA, FEDERATED STATES OF', 'FM', 0),
(143, 'MOLDOVA, REPUBLIC OF', 'MD', 0),
(144, 'MONACO', 'MC', 0),
(145, 'MONGOLIA', 'MN', 0),
(146, 'MONTENEGRO', 'ME', 0),
(147, 'MONTSERRAT', 'MS', 0),
(148, 'MOROCCO', 'MA', 0),
(149, 'MOZAMBIQUE', 'MZ', 0),
(150, 'MYANMAR', 'MM', 0),
(151, 'NAMIBIA', 'NA', 0),
(152, 'NAURU', 'NR', 0),
(153, 'NEPAL', 'NP', 0),
(154, 'NETHERLANDS', 'NL', 0),
(155, 'NETHERLANDS ANTILLES', 'AN', 0),
(156, 'NEW CALEDONIA', 'NC', 0),
(157, 'NEW ZEALAND', 'NZ', 0),
(158, 'NICARAGUA', 'NI', 0),
(159, 'NIGER', 'NE', 0),
(160, 'NIGERIA', 'NG', 0),
(161, 'NIUE', 'NU', 0),
(162, 'NORFOLK ISLAND', 'NF', 0),
(163, 'NORTHERN MARIANA ISLANDS', 'MP', 0),
(164, 'NORWAY', 'NO', 0),
(165, 'OMAN', 'OM', 0),
(166, 'PAKISTAN', 'PK', 0),
(167, 'PALAU', 'PW', 0),
(168, 'PALESTINIAN TERRITORY, OCCUPIED', 'PS', 0),
(169, 'PANAMA', 'PA', 0),
(170, 'PAPUA NEW GUINEA', 'PG', 0),
(171, 'PARAGUAY', 'PY', 0),
(172, 'PERU', 'PE', 0),
(173, 'PHILIPPINES', 'PH', 0),
(174, 'PITCAIRN', 'PN', 0),
(175, 'POLAND', 'PL', 0),
(176, 'PORTUGAL', 'PT', 0),
(177, 'PUERTO RICO', 'PR', 0),
(178, 'QATAR', 'QA', 0),
(179, 'Reunion', 'RE', 0),
(180, 'ROMANIA', 'RO', 0),
(181, 'RUSSIAN FEDERATION', 'RU', 0),
(182, 'RWANDA', 'RW', 0),
(183, 'SAINT HELENA', 'SH', 0),
(184, 'SAINT KITTS AND NEVIS', 'KN', 0),
(185, 'SAINT LUCIA', 'LC', 0),
(186, 'SAINT PIERRE AND MIQUELON', 'PM', 0),
(187, 'SAINT VINCENT AND THE GRENADINES', 'VC', 0),
(188, 'SAMOA', 'WS', 0),
(189, 'SAN MARINO', 'SM', 0),
(190, 'SAO TOME AND PRINCIPE', 'ST', 0),
(191, 'SAUDI ARABIA', 'SA', 0),
(192, 'SENEGAL', 'SN', 0),
(193, 'SERBIA', 'RS', 0),
(194, 'SEYCHELLES', 'SC', 0),
(195, 'SIERRA LEONE', 'SL', 0),
(196, 'SINGAPORE', 'SG', 0),
(197, 'SLOVAKIA', 'SK', 0),
(198, 'SLOVENIA', 'SI', 0),
(199, 'SOLOMON ISLANDS', 'SB', 0),
(200, 'SOMALIA', 'SO', 0),
(201, 'SOUTH AFRICA', 'ZA', 0),
(202, 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'GS', 0),
(203, 'SPAIN', 'ES', 0),
(204, 'SRI LANKA', 'LK', 0),
(205, 'SUDAN', 'SD', 0),
(206, 'SURINAME', 'SR', 0),
(207, 'SVALBARD AND JAN MAYEN', 'SJ', 0),
(208, 'SWAZILAND', 'SZ', 0),
(209, 'SWEDEN', 'SE', 0),
(210, 'SWITZERLAND', 'CH', 0),
(211, 'SYRIAN ARAB REPUBLIC', 'SY', 0),
(212, 'TAIWAN, PROVINCE OF CHINA', 'TW', 0),
(213, 'TAJIKISTAN', 'TJ', 0),
(214, 'TANZANIA, UNITED REPUBLIC OF', 'TZ', 0),
(215, 'THAILAND', 'TH', 0),
(216, 'TIMOR-LESTE', 'TL', 0),
(217, 'TOGO', 'TG', 0),
(218, 'TOKELAU', 'TK', 0),
(219, 'TONGA', 'TO', 0),
(220, 'TRINIDAD AND TOBAGO', 'TT', 0),
(221, 'TUNISIA', 'TN', 0),
(222, 'TURKEY', 'TR', 0),
(223, 'TURKMENISTAN', 'TM', 0),
(224, 'TURKS AND CAICOS ISLANDS', 'TC', 0),
(225, 'TUVALU', 'TV', 0),
(226, 'UGANDA', 'UG', 0),
(227, 'UKRAINE', 'UA', 0),
(228, 'UNITED ARAB EMIRATES', 'AE', 0),
(229, 'UNITED KINGDOM', 'GB', 0),
(230, 'UNITED STATES', 'US', 0),
(231, 'UNITED STATES MINOR OUTLYING ISLANDS', 'UM', 0),
(232, 'URUGUAY', 'UY', 0),
(233, 'UZBEKISTAN', 'UZ', 0),
(234, 'VANUATU', 'VU', 0),
(235, 'VATICAN CITY STATE', 'VA', 0),
(236, 'VENEZUELA', 'VE', 0),
(237, 'VIET NAM', 'VN', 0),
(238, 'VIRGIN ISLANDS, BRITISH', 'VG', 0),
(239, 'VIRGIN ISLANDS, U.S.', 'VI', 0),
(240, 'WALLIS AND FUTUNA', 'WF', 0),
(241, 'WESTERN SAHARA', 'EH', 0),
(242, 'YEMEN', 'YE', 0),
(243, 'ZAMBIA', 'ZM', 0),
(244, 'ZIMBABWE', 'ZW', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `core_deleted_user`
--

CREATE TABLE IF NOT EXISTS `core_deleted_user` (
  `id_deletion` int(11) NOT NULL AUTO_INCREMENT,
  `idst` int(11) NOT NULL DEFAULT '0',
  `userid` varchar(255) NOT NULL DEFAULT '',
  `firstname` varchar(255) NOT NULL DEFAULT '',
  `lastname` varchar(255) NOT NULL DEFAULT '',
  `pass` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `photo` varchar(255) NOT NULL DEFAULT '',
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `signature` text NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `lastenter` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `valid` tinyint(1) NOT NULL DEFAULT '0',
  `pwd_expire_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `register_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deletion_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_deletion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_deleted_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_event`
--

CREATE TABLE IF NOT EXISTS `core_event` (
  `idEvent` int(11) NOT NULL AUTO_INCREMENT,
  `idClass` int(11) NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL DEFAULT '',
  `section` varchar(50) NOT NULL DEFAULT '',
  `priority` smallint(1) unsigned NOT NULL DEFAULT '1289',
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idEvent`),
  KEY `idClass` (`idClass`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_event`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_event_class`
--

CREATE TABLE IF NOT EXISTS `core_event_class` (
  `idClass` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(50) NOT NULL DEFAULT '',
  `platform` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idClass`),
  UNIQUE KEY `class_2` (`class`),
  KEY `class` (`class`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_event_class`
--

INSERT INTO `core_event_class` (`idClass`, `class`, `platform`, `description`) VALUES
(1, 'UserNew', 'framework', ''),
(2, 'UserMod', 'framework', ''),
(3, 'UserDel', 'framework', ''),
(4, 'UserNewModerated', 'framework', ''),
(5, 'UserGroupModerated', 'framework', ''),
(6, 'UserGroupInsert', 'framework', ''),
(7, 'UserGroupRemove', 'framework', ''),
(8, 'UserCourseInsertModerate', 'lms-a', ''),
(9, 'UserCourseInserted', 'lms-a', ''),
(10, 'UserCourseRemoved', 'lms-a', ''),
(11, 'UserCourseLevelChanged', 'lms-a', ''),
(12, 'UserCourseEnded', 'lms-a', ''),
(13, 'CoursePorpModified', 'lms-a', ''),
(14, 'AdviceNew', 'lms', ''),
(15, 'MsgNewReceived', 'lms', ''),
(16, 'ForumNewCategory', 'lms', ''),
(17, 'ForumNewThread', 'lms', ''),
(18, 'ForumNewResponse', 'lms', ''),
(19, 'UserCourseRemovedModerate', 'lms-a', ''),
(38, 'UserApproved', 'framework', ''),
(39, 'UserCourseBuy', 'lms', ''),
(40, 'SettingUpdate', 'framework', ''),
(41, 'UserNewWaiting', 'framework', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_event_consumer`
--

CREATE TABLE IF NOT EXISTS `core_event_consumer` (
  `idConsumer` int(11) NOT NULL AUTO_INCREMENT,
  `consumer_class` varchar(50) NOT NULL DEFAULT '',
  `consumer_file` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`idConsumer`),
  UNIQUE KEY `consumer_class` (`consumer_class`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Table of consumer with PHP classes and files' ;

--
-- Dump dei dati per la tabella `core_event_consumer`
--

INSERT INTO `core_event_consumer` (`idConsumer`, `consumer_class`, `consumer_file`) VALUES
(1, 'DoceboUserNotifier', '/lib/lib.usernotifier.php'),
(2, 'DoceboCourseNotifier', '/lib/lib.coursenotifier.php'),
(3, 'DoceboOrgchartNotifier', '/lib/lib.orgchartnotifier.php'),
(5, 'DoceboSettingNotifier', '/lib/lib.settingnotifier.php');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_event_consumer_class`
--

CREATE TABLE IF NOT EXISTS `core_event_consumer_class` (
  `idConsumer` int(11) NOT NULL DEFAULT '0',
  `idClass` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idConsumer`,`idClass`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='n:m relation from consumers and event''s classes';

--
-- Dump dei dati per la tabella `core_event_consumer_class`
--

INSERT INTO `core_event_consumer_class` (`idConsumer`, `idClass`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 38),
(1, 39),
(1, 41),
(2, 3),
(3, 3),
(5, 40);

-- --------------------------------------------------------

--
-- Struttura della tabella `core_event_manager`
--

CREATE TABLE IF NOT EXISTS `core_event_manager` (
  `idEventMgr` int(11) NOT NULL AUTO_INCREMENT,
  `idClass` int(11) NOT NULL DEFAULT '0',
  `permission` enum('not_used','mandatory') NOT NULL DEFAULT 'not_used',
  `channel` set('email','sms') NOT NULL DEFAULT 'email',
  `recipients` varchar(255) NOT NULL DEFAULT '',
  `show_level` set('godadmin','admin','user') NOT NULL DEFAULT '',
  PRIMARY KEY (`idEventMgr`),
  UNIQUE KEY `idClass` (`idClass`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_event_manager`
--

INSERT INTO `core_event_manager` (`idEventMgr`, `idClass`, `permission`, `channel`, `recipients`, `show_level`) VALUES
(1, 1, 'mandatory', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(2, 2, 'not_used', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(3, 3, 'not_used', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(4, 4, 'not_used', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin'),
(5, 5, 'not_used', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin'),
(6, 6, 'not_used', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(7, 7, 'not_used', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(8, 8, 'not_used', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin'),
(9, 9, 'not_used', 'email', '_EVENT_RECIPIENTS_TEACHER', 'godadmin,admin,user'),
(10, 10, 'not_used', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(11, 11, 'not_used', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(12, 12, 'mandatory', 'email', '_EVENT_RECIPIENTS_TEACHER', 'godadmin,admin,user'),
(13, 13, 'not_used', 'email', '_EVENT_RECIPIENTS_TEACHER_GOD', 'godadmin,admin,user'),
(14, 14, 'not_used', 'email', '_ALL', 'godadmin,admin,user'),
(15, 15, 'not_used', 'email', '_ALL', 'godadmin,admin,user'),
(16, 16, 'not_used', 'email', '_ALL', 'godadmin,admin,user'),
(17, 17, 'not_used', 'email', '_ALL', 'godadmin,admin,user'),
(18, 18, 'not_used', 'email', '_ALL', 'godadmin,admin,user'),
(19, 19, 'not_used', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin'),
(38, 38, 'not_used', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(39, 39, 'not_used', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(41, 41, 'mandatory', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_event_property`
--

CREATE TABLE IF NOT EXISTS `core_event_property` (
  `idEvent` int(11) NOT NULL DEFAULT '0',
  `property_name` varchar(50) NOT NULL DEFAULT '',
  `property_value` text NOT NULL,
  `property_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`idEvent`,`property_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_event_property`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_event_user`
--

CREATE TABLE IF NOT EXISTS `core_event_user` (
  `idEventMgr` int(11) NOT NULL DEFAULT '0',
  `idst` int(11) NOT NULL DEFAULT '0',
  `channel` set('email','sms') NOT NULL DEFAULT '',
  PRIMARY KEY (`idEventMgr`,`idst`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_event_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_field`
--

CREATE TABLE IF NOT EXISTS `core_field` (
  `idField` int(11) NOT NULL AUTO_INCREMENT,
  `id_common` int(11) NOT NULL DEFAULT '0',
  `type_field` varchar(255) NOT NULL DEFAULT '',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `translation` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(5) NOT NULL DEFAULT '0',
  `show_on_platform` varchar(255) NOT NULL DEFAULT 'framework,',
  `use_multilang` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idField`),
  KEY `id_common` (`id_common`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_field`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_field_son`
--

CREATE TABLE IF NOT EXISTS `core_field_son` (
  `idSon` int(11) NOT NULL AUTO_INCREMENT,
  `idField` int(11) NOT NULL DEFAULT '0',
  `id_common_son` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(50) NOT NULL DEFAULT '',
  `translation` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idSon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_field_son`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_field_type`
--

CREATE TABLE IF NOT EXISTS `core_field_type` (
  `type_field` varchar(255) NOT NULL DEFAULT '',
  `type_file` varchar(255) NOT NULL DEFAULT '',
  `type_class` varchar(255) NOT NULL DEFAULT '',
  `type_category` varchar(255) NOT NULL DEFAULT 'standard',
  PRIMARY KEY (`type_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_field_type`
--

INSERT INTO `core_field_type` (`type_field`, `type_file`, `type_class`, `type_category`) VALUES
('codicefiscale', 'class.cf.php', 'Field_Cf', 'standard'),
('date', 'class.date.php', 'Field_Date', 'standard'),
('dropdown', 'class.dropdown.php', 'Field_Dropdown', 'standard'),
('freetext', 'class.freetext.php', 'Field_Freetext', 'standard'),
('textfield', 'class.textfield.php', 'Field_Textfield', 'standard'),
('upload', 'class.upload.php', 'Field_Upload', 'standard'),
('yesno', 'class.yesno.php', 'Field_Yesno', 'standard'),
('country',  'class.country.php',  'Field_Country',  'standard'),
('textlabel', 'class.label.php', 'Field_Textlabel', 'standard');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_field_userentry`
--

CREATE TABLE IF NOT EXISTS `core_field_userentry` (
  `id_common` varchar(11) NOT NULL DEFAULT '',
  `id_common_son` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `user_entry` text NOT NULL,
  PRIMARY KEY (`id_common`,`id_common_son`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_field_userentry`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_fncrole`
--

CREATE TABLE IF NOT EXISTS `core_fncrole` (
  `id_fncrole` int(10) unsigned NOT NULL DEFAULT '0',
  `id_group` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fncrole`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_fncrole`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_fncrole_competence`
--

CREATE TABLE IF NOT EXISTS `core_fncrole_competence` (
  `id_fncrole` int(10) unsigned NOT NULL DEFAULT '0',
  `id_competence` int(10) unsigned NOT NULL DEFAULT '0',
  `score` int(10) unsigned NOT NULL DEFAULT '0',
  `expiration` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fncrole`,`id_competence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_fncrole_competence`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_fncrole_group`
--

CREATE TABLE IF NOT EXISTS `core_fncrole_group` (
  `id_group` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id_group`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_fncrole_group`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_fncrole_group_lang`
--

CREATE TABLE IF NOT EXISTS `core_fncrole_group_lang` (
  `id_group` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id_group`,`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_fncrole_group_lang`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_fncrole_lang`
--

CREATE TABLE IF NOT EXISTS `core_fncrole_lang` (
  `id_fncrole` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`id_fncrole`,`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_fncrole_lang`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_group`
--

CREATE TABLE IF NOT EXISTS `core_group` (
  `idst` int(11) NOT NULL DEFAULT '0',
  `groupid` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `hidden` enum('true','false') NOT NULL DEFAULT 'false',
  `type` enum('free','moderate','private','invisible','course','company') NOT NULL DEFAULT 'free',
  `show_on_platform` text NOT NULL,
  PRIMARY KEY (`idst`),
  UNIQUE KEY `groupid` (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_group`
--

INSERT INTO `core_group` (`idst`, `groupid`, `description`, `hidden`, `type`, `show_on_platform`) VALUES
(1, '/oc_0', 'Root of organization chart', 'true', 'free', ''),
(2, '/ocd_0', 'Root of organization chart and descendants', 'true', 'free', ''),
(3, '/framework/level/godadmin', 'Group of godadmins', 'true', 'free', ''),
(4, '/framework/level/admin', 'Group of administrators', 'true', 'free', ''),
(5, '/framework/level/publicadmin', 'Group of public admin', 'true', 'free', ''),
(6, '/framework/level/user', 'Group of normal users', 'true', 'free', ''),
(301, '/lms/custom/11/7', 'for custom lms menu', 'true', 'free', ''),
(302, '/lms/custom/11/6', 'for custom lms menu', 'true', 'free', ''),
(303, '/lms/custom/11/5', 'for custom lms menu', 'true', 'free', ''),
(304, '/lms/custom/11/4', 'for custom lms menu', 'true', 'free', ''),
(305, '/lms/custom/11/3', 'for custom lms menu', 'true', 'free', ''),
(306, '/lms/custom/11/2', 'for custom lms menu', 'true', 'free', ''),
(307, '/lms/custom/11/1', 'for custom lms menu', 'true', 'free', ''),
(10893, '/lms/custom/21/7', 'for custom lms menu', 'true', 'free', ''),
(10894, '/lms/custom/21/6', 'for custom lms menu', 'true', 'free', ''),
(10895, '/lms/custom/21/5', 'for custom lms menu', 'true', 'free', ''),
(10896, '/lms/custom/21/4', 'for custom lms menu', 'true', 'free', ''),
(10897, '/lms/custom/21/3', 'for custom lms menu', 'true', 'free', ''),
(10898, '/lms/custom/21/2', 'for custom lms menu', 'true', 'free', ''),
(10899, '/lms/custom/21/1', 'for custom lms menu', 'true', 'free', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_group_fields`
--

CREATE TABLE IF NOT EXISTS `core_group_fields` (
  `idst` int(11) NOT NULL DEFAULT '0',
  `id_field` int(11) NOT NULL DEFAULT '0',
  `mandatory` enum('true','false') NOT NULL DEFAULT 'false',
  `useraccess` enum('noaccess','readonly','readwrite') NOT NULL DEFAULT 'readonly',
  `user_inherit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`idst`,`id_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_group_fields`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_group_members`
--

CREATE TABLE IF NOT EXISTS `core_group_members` (
  `idst` int(11) NOT NULL DEFAULT '0',
  `idstMember` int(11) NOT NULL DEFAULT '0',
  `filter` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`idst`,`idstMember`),
  KEY `idstMember` (`idstMember`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_group_members`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_group_user_waiting`
--

CREATE TABLE IF NOT EXISTS `core_group_user_waiting` (
  `idst_group` int(11) NOT NULL DEFAULT '0',
  `idst_user` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idst_group`,`idst_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_group_user_waiting`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_hteditor`
--

CREATE TABLE IF NOT EXISTS `core_hteditor` (
  `hteditor` varchar(255) NOT NULL DEFAULT '',
  `hteditorname` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`hteditor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_hteditor`
--

INSERT INTO `core_hteditor` (`hteditor`, `hteditorname`) VALUES
('textarea', '_TEXTAREA'),
('tinymce', '_TINYMCE'),
('yui', '_YUI');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_lang_language`
--

CREATE TABLE IF NOT EXISTS `core_lang_language` (
  `lang_code` varchar(50) NOT NULL DEFAULT '',
  `lang_description` varchar(255) NOT NULL DEFAULT '',
  `lang_browsercode` varchar(50) NOT NULL DEFAULT '',
  `lang_direction` enum('ltr','rtl') NOT NULL DEFAULT 'ltr',
  PRIMARY KEY (`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_lang_language`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_lang_text`
--

CREATE TABLE IF NOT EXISTS `core_lang_text` (
  `id_text` int(11) NOT NULL AUTO_INCREMENT,
  `text_key` varchar(50) NOT NULL DEFAULT '',
  `text_module` varchar(50) NOT NULL DEFAULT '',
  `text_attributes` set('accessibility','sms','email') NOT NULL DEFAULT '',
  PRIMARY KEY (`id_text`),
  UNIQUE KEY `text_key` (`text_key`,`text_module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_lang_text`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_lang_translation`
--

CREATE TABLE IF NOT EXISTS `core_lang_translation` (
  `id_text` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(50) NOT NULL DEFAULT '',
  `translation_text` text,
  `save_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_text`,`lang_code`),
  KEY `lang_code` (`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_lang_translation`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_menu`
--

CREATE TABLE IF NOT EXISTS `core_menu` (
  `idMenu` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `collapse` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

--
-- Dump dei dati per la tabella `core_menu`
--

INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `collapse`) VALUES
(1, '', '', 1, 'true'),
(2, '_USER_MANAGMENT', '', 2, 'false'),
(3, '_TRASV_MANAGMENT', '', 3, 'false'),
(4, '_ADMINISTRATORS', '', 4, 'false'),
(5, '_LANGUAGE', '', 5, 'false'),
(6, '', '', 6, 'true');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_menu_under`
--

CREATE TABLE IF NOT EXISTS `core_menu_under` (
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
-- Dump dei dati per la tabella `core_menu_under`
--

INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(1, 1, 'dashboard', '_DASHBOARD', '', 'view', NULL, 1, '', '', 'adm/dashboard/show'),
(3, 2, 'groupmanagement', '_MANAGE_GROUPS', '', 'view', NULL, 2, '', '', 'adm/groupmanagement/show'),
(4, 3, 'field_manager', '_FIELD_MANAGER', 'field_list', 'view', NULL, 3, 'class.field_manager.php', 'Module_Field_Manager', ''),
(5, 3, 'setting', '_CONFIGURATION', '', 'view', NULL, 1, 'class.configuration.php', 'Module_Configuration', 'adm/setting/show'),
(7, 3, 'event_manager', '_EVENTMANAGER', 'display', 'view_event_manager', NULL, 3, 'class.event_manager.php', 'Module_Event_Manager', ''),
(8, 3, 'iotask', '_IOTASK', 'iotask', 'view', NULL, 4, 'class.iotask.php', 'Module_IOTask', ''),
(9, 3, 'pluginmanager', '_PLUGIN_MANAGER', '', 'view', NULL, 7, '', '', 'adm/pluginmanager/show'),
(10, 5, 'lang', '_LANG', '', 'view', NULL, 1, '', '', 'adm/lang/show'),
(13, 6, 'newsletter', '_NEWSLETTER', 'newsletter', 'view', NULL, 1, 'class.newsletter.php', 'Module_Newsletter', ''),
(16, 2, 'usermanagement', '_LISTUSER', '', 'view', NULL, 1, '', '', 'adm/usermanagement/show'),
(18, 4, 'adminrules', '_ADMIN_RULES', '', 'view', NULL, 1, '', '', 'adm/adminrules/show'),
(19, 4, 'publicadminrules', '_PUBLIC_ADMIN_RULES', '', 'view', NULL, 1, '', '', 'adm/publicadminrules/show'),
(20, 4, 'adminmanager', '_ADMIN_MANAGER', '', 'view', NULL, 1, '', '', 'adm/adminmanager/show'),
(21, 4, 'publicadminmanager', '_PUBLIC_ADMIN_MANAGER', '', 'view', NULL, 1, '', '', 'adm/publicadminmanager/show'),
(22, 2, 'functionalroles', '_FUNCTIONAL_ROLE', '', 'view', NULL, 4, '', '', 'adm/functionalroles/show'),
(23, 2, 'competences', '_COMPETENCES', '', 'view', NULL, 3, '', '', 'adm/competences/show'),
(24, 3, 'code', '_CODE', 'list', 'view', NULL, 5, 'class.code.php', 'Module_Code', ''),
(25, 3, 'privacypolicy', '_PRIVACYPOLICIES', '', 'view', NULL, 6, '', '', 'adm/privacypolicy/show');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_message`
--

CREATE TABLE IF NOT EXISTS `core_message` (
  `idMessage` int(11) NOT NULL AUTO_INCREMENT,
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `sender` int(11) NOT NULL DEFAULT '0',
  `posted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(255) NOT NULL DEFAULT '',
  `textof` text NOT NULL,
  `attach` varchar(255) NOT NULL DEFAULT '',
  `priority` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idMessage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_message`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_message_user`
--

CREATE TABLE IF NOT EXISTS `core_message_user` (
  `idMessage` int(11) NOT NULL DEFAULT '0',
  `idUser` int(11) NOT NULL DEFAULT '0',
  `idCourse` int(11) NOT NULL DEFAULT '0',
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idMessage`,`idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_message_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_newsletter`
--

CREATE TABLE IF NOT EXISTS `core_newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_send` int(11) NOT NULL DEFAULT '0',
  `sub` varchar(255) NOT NULL DEFAULT '',
  `msg` text NOT NULL,
  `fromemail` varchar(255) NOT NULL DEFAULT '',
  `language` varchar(255) NOT NULL DEFAULT '',
  `tot` int(11) NOT NULL DEFAULT '0',
  `send_type` enum('email','sms') NOT NULL DEFAULT 'email',
  `stime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `file` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_newsletter`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_newsletter_sendto`
--

CREATE TABLE IF NOT EXISTS `core_newsletter_sendto` (
  `id_send` int(11) NOT NULL DEFAULT '0',
  `idst` int(11) NOT NULL DEFAULT '0',
  `stime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_send`,`idst`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_newsletter_sendto`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_org_chart`
--

CREATE TABLE IF NOT EXISTS `core_org_chart` (
  `id_dir` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(50) NOT NULL DEFAULT '',
  `translation` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_dir`,`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_org_chart`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_org_chart_field`
--

CREATE TABLE IF NOT EXISTS `core_org_chart_field` (
  `idst` int(11) NOT NULL DEFAULT '0',
  `id_field` varchar(11) NOT NULL DEFAULT '0',
  `mandatory` enum('true','false') NOT NULL DEFAULT 'false',
  `useraccess` enum('readonly','readwrite','noaccess') NOT NULL DEFAULT 'readonly',
  PRIMARY KEY (`idst`,`id_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_org_chart_field`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_org_chart_fieldentry`
--

CREATE TABLE IF NOT EXISTS `core_org_chart_fieldentry` (
  `id_common` varchar(11) NOT NULL DEFAULT '',
  `id_common_son` int(11) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `user_entry` text NOT NULL,
  PRIMARY KEY (`id_common`,`id_common_son`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_org_chart_fieldentry`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_org_chart_tree`
--

CREATE TABLE IF NOT EXISTS `core_org_chart_tree` (
  `idOrg` int(11) NOT NULL AUTO_INCREMENT,
  `idParent` int(11) NOT NULL DEFAULT '0',
  `path` text NOT NULL,
  `lev` int(3) NOT NULL DEFAULT '0',
  `iLeft` int(5) NOT NULL DEFAULT '0',
  `iRight` int(5) NOT NULL DEFAULT '0',
  `code` varchar(255) NOT NULL DEFAULT '',
  `idst_oc` int(11) NOT NULL DEFAULT '0',
  `idst_ocd` int(11) NOT NULL DEFAULT '0',
  `associated_policy` int(11) unsigned DEFAULT NULL,
  `associated_template` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idOrg`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_org_chart_tree`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_password_history`
--

CREATE TABLE IF NOT EXISTS `core_password_history` (
  `idst_user` int(11) NOT NULL DEFAULT '0',
  `pwd_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `passw` varchar(100) NOT NULL DEFAULT '',
  `changed_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idst_user`,`pwd_date`),
  KEY `pwd_date` (`pwd_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_password_history`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_platform`
--

CREATE TABLE IF NOT EXISTS `core_platform` (
  `platform` varchar(255) NOT NULL DEFAULT '',
  `class_file` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `class_file_menu` varchar(255) NOT NULL DEFAULT '',
  `class_name_menu` varchar(255) NOT NULL DEFAULT '',
  `class_name_menu_managment` varchar(255) NOT NULL DEFAULT '',
  `file_class_config` varchar(255) NOT NULL DEFAULT '',
  `class_name_config` varchar(255) NOT NULL DEFAULT '',
  `var_default_template` varchar(255) NOT NULL DEFAULT '',
  `class_default_admin` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(3) NOT NULL DEFAULT '0',
  `is_active` enum('true','false') NOT NULL DEFAULT 'true',
  `mandatory` enum('true','false') NOT NULL DEFAULT 'true',
  `dependencies` text NOT NULL,
  `main` enum('true','false') NOT NULL DEFAULT 'true',
  `hidden_in_config` enum('true','false') NOT NULL DEFAULT 'false',
  PRIMARY KEY (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_platform`
--

INSERT INTO `core_platform` (`platform`, `class_file`, `class_name`, `class_file_menu`, `class_name_menu`, `class_name_menu_managment`, `file_class_config`, `class_name_config`, `var_default_template`, `class_default_admin`, `sequence`, `is_active`, `mandatory`, `dependencies`, `main`, `hidden_in_config`) VALUES
('framework', '', '', 'class.admin_menu_fw.php', 'Admin_Framework', 'Admin_Managment_Framework', 'class.conf_fw.php', 'Config_Framework', 'defaultTemplate', 'Module', 1, 'true', 'true', '', 'false', 'false'),
('lms', '', '', 'class.admin_menu_lms.php', 'Admin_Lms', 'Admin_Managment_Lms', 'class.conf_lms.php', 'Config_Lms', 'defaultTemplate', 'LmsAdminModule', 2, 'true', 'false', '', 'true', 'false'),
('scs', '', '', 'class.admin_menu_scs.php', 'Admin_Scs', '', 'class.conf_scs.php', 'Config_Scs', 'defaultTemplate', 'ScsAdminModule', 4, 'true', 'false', '', 'false', 'false');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_plugin`
--


CREATE TABLE IF NOT EXISTS `core_plugin` (
  `plugin_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(64) NOT NULL,
  `code` varchar(32) NOT NULL,
  `category` VARCHAR(255),
  `version` varchar(16) NOT NULL,
  `author` varchar(128) NOT NULL,
  `link` varchar(255) NOT NULL,
  `priority` int(5) NOT NULL,
  `description` text NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`plugin_id`),
  UNIQUE KEY `name` (`name`,`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_plugin`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_privacypolicy`
--

CREATE TABLE IF NOT EXISTS `core_privacypolicy` (
  `id_policy` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_policy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_privacypolicy`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_privacypolicy_lang`
--

CREATE TABLE IF NOT EXISTS `core_privacypolicy_lang` (
  `id_policy` int(11) unsigned NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `translation` text NOT NULL,
  PRIMARY KEY (`id_policy`,`lang_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_privacypolicy_lang`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_pwd_recover`
--

CREATE TABLE IF NOT EXISTS `core_pwd_recover` (
  `idst_user` int(11) NOT NULL DEFAULT '0',
  `random_code` varchar(255) NOT NULL DEFAULT '',
  `request_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`idst_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_pwd_recover`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_reg_list`
--

CREATE TABLE IF NOT EXISTS `core_reg_list` (
  `region_id` varchar(100) NOT NULL DEFAULT '',
  `lang_code` varchar(50) NOT NULL DEFAULT '',
  `region_desc` varchar(255) NOT NULL DEFAULT '',
  `default_region` tinyint(1) NOT NULL DEFAULT '0',
  `browsercode` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_reg_list`
--

INSERT INTO `core_reg_list` (`region_id`, `lang_code`, `region_desc`, `default_region`, `browsercode`) VALUES
('england', 'english', 'england, usa, ...', 0, 'en-EN, en-US'),
('italy', 'italian', 'Italia', 1, 'it');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_reg_setting`
--

CREATE TABLE IF NOT EXISTS `core_reg_setting` (
  `region_id` varchar(100) NOT NULL DEFAULT '',
  `val_name` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`region_id`,`val_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_reg_setting`
--

INSERT INTO `core_reg_setting` (`region_id`, `val_name`, `value`) VALUES
('england', 'custom_date_format', ''),
('england', 'custom_time_format', ''),
('england', 'date_format', 'd_m_Y'),
('england', 'date_sep', '/'),
('england', 'time_format', 'H_i'),
('italy', 'custom_date_format', ''),
('italy', 'custom_time_format', ''),
('italy', 'date_format', 'd_m_Y'),
('italy', 'date_sep', '-'),
('italy', 'time_format', 'H_i');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_rest_authentication`
--

CREATE TABLE IF NOT EXISTS `core_rest_authentication` (
  `id_user` int(11) NOT NULL DEFAULT '0',
  `user_level` int(11) NOT NULL DEFAULT '0',
  `token` varchar(255) NOT NULL DEFAULT '',
  `generation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_enter_date` datetime DEFAULT NULL,
  `expiry_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_rest_authentication`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_revision`
--

CREATE TABLE IF NOT EXISTS `core_revision` (
  `type` enum('wiki','faq') NOT NULL DEFAULT 'faq',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `version` int(11) NOT NULL DEFAULT '0',
  `sub_key` varchar(80) NOT NULL DEFAULT '0',
  `author` int(11) NOT NULL DEFAULT '0',
  `rev_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `content` longtext NOT NULL,
  PRIMARY KEY (`type`,`parent_id`,`version`,`sub_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_revision`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_role`
--

CREATE TABLE IF NOT EXISTS `core_role` (
  `idst` int(11) NOT NULL DEFAULT '0',
  `roleid` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idst`),
  KEY `roleid` (`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0;

--
-- Dump dei dati per la tabella `core_role`
--

INSERT INTO `core_role` (`idst`, `roleid`, `description`) VALUES
(7, '/framework/admin/adminmanager/mod', NULL),
(8, '/framework/admin/adminmanager/view', NULL),
(9, '/framework/admin/adminrules/view', NULL),
(10, '/framework/admin/dashboard/view', NULL),
(11, '/framework/admin/dashboard/view', NULL),
(12, '/framework/admin/directory/approve_waiting_user', NULL),
(13, '/framework/admin/event_manager/view_event_manager', NULL),
(14, '/framework/admin/field_manager/add', NULL),
(15, '/framework/admin/field_manager/del', NULL),
(16, '/framework/admin/field_manager/mod', NULL),
(17, '/framework/admin/field_manager/view', NULL),
(18, '/framework/admin/groupmanagement/add', NULL),
(19, '/framework/admin/groupmanagement/associate_user', NULL),
(20, '/framework/admin/groupmanagement/del', NULL),
(21, '/framework/admin/groupmanagement/mod', NULL),
(22, '/framework/admin/groupmanagement/view', NULL),
(23, '/framework/admin/iotask/view', NULL),
(24, '/framework/admin/kb/mod', NULL),
(25, '/framework/admin/kb/view', NULL),
(26, '/framework/admin/lang/mod', NULL),
(27, '/framework/admin/lang/view', NULL),
(28, '/framework/admin/newsletter/view', NULL),
(29, '/framework/admin/publicadminmanager/mod', NULL),
(30, '/framework/admin/publicadminmanager/view', NULL),
(31, '/framework/admin/usermanagement/add', NULL),
(32, '/framework/admin/usermanagement/approve_waiting_user', NULL),
(33, '/framework/admin/usermanagement/del', NULL),
(34, '/framework/admin/usermanagement/mod', NULL),
(35, '/framework/admin/usermanagement/view', NULL),
(36, '/lms/admin/amanmenu/mod', NULL),
(37, '/lms/admin/amanmenu/view', NULL),
(38, '/lms/admin/catalogue/mod', NULL),
(39, '/lms/admin/catalogue/view', NULL),
(40, '/lms/admin/certificate/mod', NULL),
(41, '/lms/admin/certificate/view', NULL),
(42, '/lms/admin/classroom/view', NULL),
(43, '/lms/admin/communication/mod', NULL),
(44, '/lms/admin/communication/view', NULL),
(45, '/lms/admin/course/add', NULL),
(46, '/lms/admin/course/del', NULL),
(47, '/lms/admin/course/mod', NULL),
(48, '/lms/admin/course/moderate', NULL),
(49, '/lms/admin/course/subscribe', NULL),
(50, '/lms/admin/course/view', NULL),
(51, '/lms/admin/coursepath/mod', NULL),
(52, '/lms/admin/coursepath/moderate', NULL),
(53, '/lms/admin/coursepath/subscribe', NULL),
(54, '/lms/admin/coursepath/view', NULL),
(55, '/lms/admin/enrollrules/view', NULL),
(56, '/lms/admin/games/mod', NULL),
(57, '/lms/admin/games/subscribe', NULL),
(58, '/lms/admin/games/view', NULL),
(59, '/lms/admin/internal_news/mod', NULL),
(60, '/lms/admin/internal_news/view', NULL),
(61, '/lms/admin/kb/view', NULL),
(62, '/lms/admin/label/view', NULL),
(63, '/lms/admin/middlearea/view', NULL),
(64, '/lms/admin/news/mod', NULL),
(65, '/lms/admin/news/view', NULL),
(66, '/lms/admin/preassessment/mod', NULL),
(67, '/lms/admin/preassessment/subscribe', NULL),
(68, '/lms/admin/preassessment/view', NULL),
(69, '/lms/admin/questcategory/mod', NULL),
(70, '/lms/admin/questcategory/view', NULL),
(71, '/lms/admin/report/mod', NULL),
(72, '/lms/admin/report/view', NULL),
(73, '/lms/admin/reservation/mod', NULL),
(74, '/lms/admin/reservation/view', NULL),
(75, '/lms/admin/timeperiods/mod', NULL),
(76, '/lms/admin/timeperiods/view', NULL),
(77, '/lms/admin/transaction/view', NULL),
(78, '/lms/admin/webpages/mod', NULL),
(79, '/lms/admin/webpages/view', NULL),
(80, '/lms/course/public/course/view', NULL),
(81, '/lms/course/public/course_autoregistration/view', NULL),
(82, '/lms/course/public/coursecatalogue/view', NULL),
(83, '/lms/course/public/message/send_all', NULL),
(84, '/lms/course/public/message/view', NULL),
(85, '/lms/course/public/mycertificate/view', NULL),
(86, '/lms/course/public/mycompetences/view', NULL),
(87, '/lms/course/public/mygroup/view', NULL),
(88, '/lms/course/public/profile/mod', NULL),
(89, '/lms/course/public/profile/view', NULL),
(90, '/lms/course/public/tprofile/view', NULL),
(91, '/lms/course/public/public_forum/view', NULL),
(92, '/lms/course/public/public_forum/add', NULL),
(93, '/lms/course/public/public_forum/del', NULL),
(94, '/lms/course/public/public_forum/mod', NULL),
(95, '/lms/course/public/public_forum/moderate', NULL),
(96, '/lms/course/public/public_forum/upload', NULL),
(97, '/lms/course/public/public_forum/write', NULL),
(98, '/lms/course/public/pcourse/add', NULL),
(99, '/lms/course/public/pcourse/del', NULL),
(100, '/lms/course/public/pcourse/mod', NULL),
(101, '/lms/course/public/pcourse/moderate', NULL),
(102, '/lms/course/public/pcourse/subscribe', NULL),
(103, '/lms/course/public/pcourse/view', NULL),
(104, '/lms/course/public/public_newsletter_admin/view', NULL),
(105, '/lms/course/public/public_report_admin/view', NULL),
(106, '/lms/course/public/public_subscribe_admin/approve_waiting_user', NULL),
(107, '/lms/course/public/public_subscribe_admin/createuser_org_chart', NULL),
(108, '/lms/course/public/public_subscribe_admin/deluser_org_chart', NULL),
(109, '/lms/course/public/public_subscribe_admin/edituser_org_chart', NULL),
(110, '/lms/course/public/public_subscribe_admin/view_org_chart', NULL),
(111, '/framework/admin/functionalroles/view', NULL),
(112, '/framework/admin/functionalroles/mod', NULL),
(113, '/framework/admin/functionalroles/associate_user', NULL),
(114, '/framework/admin/competences/view', NULL),
(115, '/framework/admin/competences/mod', NULL),
(116, '/framework/admin/competences/associate_user', NULL),
(117, '/framework/admin/publicadminrules/view', NULL),
(118, '/framework/admin/code/view', NULL),
(119, '/framework/admin/setting/view', NULL),
(120, '/lms/admin/meta_certificate/view', NULL),
(121, '/lms/admin/meta_certificate/mod', NULL),
(122, '/framework/admin/usermanagement/mod_org', NULL),
(123, '/lms/course/public/pusermanagement/view', NULL),
(124, '/lms/course/public/pusermanagement/add', NULL),
(125, '/lms/course/public/pusermanagement/mod', NULL),
(126, '/lms/course/public/pusermanagement/del', NULL),
(127, '/lms/course/public/pusermanagement/approve_waiting_user', NULL),
(175, '/lms/course/private/advice/mod', NULL),
(176, '/lms/course/private/advice/view', NULL),
(177, '/lms/course/private/calendar/mod', NULL),
(178, '/lms/course/private/calendar/personal', NULL),
(179, '/lms/course/private/calendar/view', NULL),
(180, '/lms/course/private/chat/view', NULL),
(181, '/lms/course/private/conference/mod', NULL),
(182, '/lms/course/private/conference/view', NULL),
(183, '/lms/course/private/course/mod', NULL),
(184, '/lms/course/private/course/view', NULL),
(185, '/lms/course/private/course/view_info', NULL),
(186, '/lms/course/private/coursereport/mod', NULL),
(187, '/lms/course/private/coursereport/view', NULL),
(188, '/lms/course/private/forum/add', NULL),
(189, '/lms/course/private/forum/del', NULL),
(190, '/lms/course/private/forum/mod', NULL),
(191, '/lms/course/private/forum/moderate', NULL),
(192, '/lms/course/private/forum/upload', NULL),
(193, '/lms/course/private/forum/view', NULL),
(194, '/lms/course/private/forum/write', NULL),
(195, '/lms/course/private/gradebook/view', NULL),
(196, '/lms/course/private/groups/mod', NULL),
(197, '/lms/course/private/groups/subscribe', NULL),
(198, '/lms/course/private/groups/view', NULL),
(199, '/lms/course/private/htmlfront/mod', NULL),
(200, '/lms/course/private/htmlfront/view', NULL),
(201, '/lms/course/private/light_repo/mod', NULL),
(202, '/lms/course/private/light_repo/view', NULL),
(203, '/lms/course/private/manmenu/mod', NULL),
(204, '/lms/course/private/manmenu/view', NULL),
(205, '/lms/course/private/newsletter/view', NULL),
(206, '/lms/course/private/notes/view', NULL),
(207, '/lms/course/private/organization/view', NULL),
(208, '/lms/course/private/project/add', NULL),
(209, '/lms/course/private/project/del', NULL),
(210, '/lms/course/private/project/mod', NULL),
(211, '/lms/course/private/project/view', NULL),
(212, '/lms/course/private/quest_bank/mod', NULL),
(213, '/lms/course/private/quest_bank/view', NULL),
(214, '/lms/course/private/reservation/mod', NULL),
(215, '/lms/course/private/reservation/view', NULL),
(216, '/lms/course/private/statistic/view', NULL),
(217, '/lms/course/private/stats/view_course', NULL),
(218, '/lms/course/private/stats/view_user', NULL),
(219, '/lms/course/private/storage/home', NULL),
(220, '/lms/course/private/storage/lesson', NULL),
(221, '/lms/course/private/storage/public', NULL),
(222, '/lms/course/private/storage/view', NULL),
(223, '/lms/course/private/wiki/admin', NULL),
(224, '/lms/course/private/wiki/edit', NULL),
(225, '/lms/course/private/wiki/view', NULL),
(226, '/lms/admin/location/view', NULL),
(227, '/lms/admin/location/mod', NULL),
(228, '/lms/admin/coursecategory/add', NULL),
(229, '/lms/admin/coursecategory/mod', NULL),
(230, '/lms/admin/coursecategory/del', NULL),
(272, '/lms/course/private/coursecharts/view', ''),
(280, '/framework/admin/pluginmanager/view', ''),
(11553, '/framework/admin/usermanagement/associate_user', NULL),
(11612, '/lms/course/public/pcertificate/view', NULL),
(11613, '/lms/course/public/pcertificate/mod', NULL),
(11757, '/lms/course/private/coursestats/view', ''),
(11835, '/lms/course/private/presence/view', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_role_members`
--

CREATE TABLE IF NOT EXISTS `core_role_members` (
  `idst` int(11) NOT NULL DEFAULT '0',
  `idstMember` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idst`,`idstMember`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_role_members`
--

INSERT INTO `core_role_members` (`idst`, `idstMember`) VALUES
(7, 3),
(8, 3),
(9, 3),
(10, 3),
(11, 3),
(12, 3),
(13, 3),
(14, 3),
(15, 3),
(16, 3),
(17, 3),
(18, 3),
(19, 3),
(20, 3),
(21, 3),
(22, 3),
(23, 3),
(24, 3),
(25, 3),
(26, 3),
(27, 3),
(28, 3),
(29, 3),
(30, 3),
(31, 3),
(32, 3),
(33, 3),
(34, 3),
(35, 3),
(36, 3),
(37, 3),
(38, 3),
(39, 3),
(40, 3),
(41, 3),
(42, 3),
(43, 3),
(44, 3),
(45, 3),
(46, 3),
(47, 3),
(48, 3),
(49, 3),
(50, 3),
(51, 3),
(52, 3),
(53, 3),
(54, 3),
(55, 3),
(56, 3),
(57, 3),
(58, 3),
(59, 3),
(60, 3),
(61, 3),
(62, 3),
(63, 3),
(64, 3),
(65, 3),
(66, 3),
(67, 3),
(68, 3),
(69, 3),
(70, 3),
(71, 3),
(72, 3),
(73, 3),
(74, 3),
(75, 3),
(76, 3),
(77, 3),
(78, 3),
(79, 3),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 3),
(93, 3),
(94, 3),
(95, 3),
(96, 1),
(96, 3),
(97, 1),
(97, 3),
(111, 3),
(112, 3),
(113, 3),
(114, 3),
(115, 3),
(116, 3),
(117, 3),
(118, 3),
(119, 3),
(120, 3),
(121, 3),
(122, 3),
(175, 301),
(175, 302),
(175, 303),
(175, 304),
(176, 301),
(176, 302),
(176, 303),
(176, 304),
(176, 305),
(176, 306),
(176, 307),
(177, 301),
(177, 302),
(177, 303),
(177, 304),
(178, 301),
(178, 302),
(178, 303),
(178, 304),
(178, 305),
(179, 301),
(179, 302),
(179, 303),
(179, 304),
(179, 305),
(179, 306),
(179, 307),
(180, 301),
(180, 302),
(180, 303),
(180, 304),
(180, 305),
(181, 301),
(181, 302),
(181, 303),
(181, 304),
(182, 301),
(182, 302),
(182, 303),
(182, 304),
(182, 305),
(182, 306),
(182, 307),
(183, 301),
(183, 302),
(183, 303),
(183, 304),
(185, 301),
(185, 302),
(185, 303),
(185, 304),
(185, 305),
(185, 306),
(185, 307),
(186, 301),
(186, 302),
(186, 303),
(186, 304),
(186, 10893),
(186, 10894),
(186, 10895),
(187, 301),
(187, 302),
(187, 303),
(187, 304),
(187, 10893),
(187, 10894),
(187, 10895),
(187, 10896),
(187, 10897),
(187, 10898),
(187, 10899),
(188, 301),
(188, 302),
(188, 303),
(188, 304),
(189, 301),
(189, 302),
(189, 303),
(189, 304),
(190, 301),
(190, 302),
(190, 303),
(190, 304),
(191, 301),
(191, 302),
(191, 303),
(191, 304),
(192, 301),
(192, 302),
(192, 303),
(192, 304),
(192, 305),
(193, 301),
(193, 302),
(193, 303),
(193, 304),
(193, 305),
(193, 306),
(193, 307),
(194, 301),
(194, 302),
(194, 303),
(194, 304),
(194, 305),
(195, 301),
(195, 302),
(195, 303),
(195, 304),
(195, 305),
(195, 10893),
(195, 10894),
(195, 10895),
(195, 10896),
(195, 10897),
(195, 10898),
(195, 10899),
(196, 301),
(196, 302),
(196, 303),
(196, 304),
(197, 301),
(197, 302),
(197, 303),
(197, 304),
(198, 301),
(198, 302),
(198, 303),
(198, 304),
(201, 301),
(201, 302),
(201, 303),
(201, 304),
(202, 301),
(202, 302),
(202, 303),
(202, 304),
(202, 305),
(205, 301),
(205, 302),
(205, 303),
(205, 304),
(206, 301),
(206, 302),
(206, 303),
(206, 304),
(206, 305),
(206, 306),
(206, 307),
(207, 301),
(207, 302),
(207, 303),
(207, 304),
(207, 305),
(207, 306),
(207, 307),
(207, 10893),
(207, 10894),
(207, 10895),
(207, 10896),
(207, 10897),
(207, 10898),
(208, 301),
(208, 302),
(208, 303),
(208, 304),
(209, 301),
(209, 302),
(209, 303),
(209, 304),
(210, 301),
(210, 302),
(210, 303),
(210, 304),
(211, 301),
(211, 302),
(211, 303),
(211, 304),
(211, 305),
(211, 306),
(211, 307),
(216, 301),
(216, 302),
(216, 303),
(216, 304),
(216, 10893),
(216, 10894),
(216, 10895),
(216, 10896),
(217, 301),
(217, 302),
(217, 303),
(217, 304),
(217, 10893),
(217, 10894),
(217, 10895),
(217, 10896),
(218, 301),
(218, 302),
(218, 303),
(218, 304),
(218, 10893),
(218, 10894),
(218, 10895),
(218, 10896),
(219, 301),
(219, 302),
(219, 10893),
(219, 10894),
(220, 301),
(220, 302),
(220, 303),
(220, 304),
(220, 10893),
(220, 10894),
(220, 10895),
(221, 301),
(221, 302),
(221, 10893),
(221, 10894),
(222, 301),
(222, 302),
(222, 303),
(222, 304),
(222, 10893),
(222, 10894),
(222, 10895),
(223, 301),
(223, 302),
(223, 303),
(223, 304),
(224, 301),
(224, 302),
(224, 303),
(224, 304),
(224, 305),
(225, 301),
(225, 302),
(225, 303),
(225, 304),
(225, 305),
(225, 306),
(225, 307),
(226, 3),
(227, 3),
(228, 3),
(229, 3),
(230, 3),
(272, 301),
(272, 302),
(272, 303),
(272, 304),
(272, 307),
(272, 10893),
(272, 10894),
(272, 10895),
(272, 10896),
(280, 3),
(11553, 3),
(11612, 3),
(11613, 3),
(11757, 301),
(11757, 302),
(11757, 303),
(11757, 304),
(11757, 10893),
(11757, 10894),
(11757, 10895),
(11757, 10896),
(11835, 301),
(11835, 302),
(11835, 303),
(11835, 304);

-- --------------------------------------------------------

--
-- Struttura della tabella `core_rules`
--

CREATE TABLE IF NOT EXISTS `core_rules` (
  `id_rule` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `rule_type` varchar(10) NOT NULL DEFAULT '',
  `creation_date` date NOT NULL DEFAULT '0000-00-00',
  `rule_active` tinyint(1) NOT NULL DEFAULT '0',
  `course_list` text NOT NULL,
  PRIMARY KEY (`id_rule`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_rules`
--

INSERT INTO `core_rules` (`id_rule`, `title`, `lang_code`, `rule_type`, `creation_date`, `rule_active`, `course_list`) VALUES
(0, '', 'all', 'base', '0000-00-00', 1, '');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_rules_entity`
--

CREATE TABLE IF NOT EXISTS `core_rules_entity` (
  `id_rule` int(11) NOT NULL DEFAULT '0',
  `id_entity` varchar(50) NOT NULL DEFAULT '',
  `course_list` text NOT NULL,
  PRIMARY KEY (`id_rule`,`id_entity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_rules_entity`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_rules_log`
--

CREATE TABLE IF NOT EXISTS `core_rules_log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `log_action` varchar(255) NOT NULL DEFAULT '',
  `log_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `applied` text NOT NULL,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_rules_log`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_setting`
--

CREATE TABLE IF NOT EXISTS `core_setting` (
  `param_name` varchar(255) NOT NULL DEFAULT '',
  `param_value` text NOT NULL,
  `value_type` varchar(25) NOT NULL DEFAULT 'string',
  `max_size` int(3) NOT NULL DEFAULT '255',
  `pack` varchar(25) NOT NULL DEFAULT 'main',
  `regroup` int(5) NOT NULL DEFAULT '0',
  `sequence` int(5) NOT NULL DEFAULT '0',
  `param_load` tinyint(1) NOT NULL DEFAULT '1',
  `hide_in_modify` tinyint(1) NOT NULL DEFAULT '0',
  `extra_info` text NOT NULL,
  PRIMARY KEY (`param_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_setting`
--

INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
('accessibility', 'off', 'enum', 255, '0', 8, 5, 1, 0, ''),
('bbb_server', 'http://test-install.blindsidenetworks.com/bigbluebutton/', 'string', 255, 'bbb', 6, 1, 1, 0, ''),
('bbb_port', '80', 'string', 255, 'bbb', 6, 2, 1, 0, ''),
('bbb_user', '', 'string', 255, 'bbb', 6, 2, 1, 0, ''),
('bbb_salt', 'to be changed with a complex string', 'string', 255, 'bbb', 6, 3, 1, 0, ''),
('bbb_password_moderator', 'password.moderator', 'string', 255, 'bbb', 6, 4, 1, 0, ''),
('bbb_password_viewer', 'password.viewer', 'string', 255, 'bbb', 6, 5, 1, 0, ''),
('bbb_max_mikes', '2', 'string', 255, 'bbb', 6, 6, 1, 0, ''),
('bbb_max_participant', '300', 'string', 255, 'bbb', 6, 7, 1, 0, ''),
('bbb_max_room', '999', 'string', 255, 'bbb', 6, 8, 1, 0, ''),
('code_teleskill', '', 'string', 255, 'teleskill', 6, 3, 1, 0, ''),
('common_admin_session', 'on', 'enum', 3, 'security', 8, 24, 1, 0, ''),
('conference_creation_limit_per_user', '99999999999', 'string', 255, '0', 6, 0, 1, 0, ''),
('core_version', '1.4.3', 'string', 255, '0', 1, 0, 1, 1, ''),
('course_block', 'off', 'enum', 3, 0, 4, 5, 1, 0, ''),
('course_quota', '0', 'string', 255, '0', 4, 5, 1, 0, ''),
('currency_symbol', '&euro;', 'string', 10, '0', 5, 2, 1, 0, ''),
('customer_help_email', '', 'string', 255, '0', 3, 19, 1, 0, ''),
('customer_help_subj_pfx', '', 'string', 255, '0', 3, 20, 1, 0, ''),
('defaultTemplate', 'standard', 'template', 255, '0', 1, 4, 1, 0, ''),
('default_language', '', 'language', 255, '0', 1, 3, 1, 0, ''),
('dimdim_max_mikes', '2', 'string', 255, 'dimdim', 6, 7, 1, 0, ''),
('dimdim_max_participant', '300', 'string', 255, 'dimdim', 6, 6, 1, 0, ''),
('dimdim_max_room', '99999999999', 'string', 255, 'dimdim', 6, 5, 1, 0, ''),
('dimdim_password', '', 'password', 255, 'dimdim', 6, 2, 1, 0, ''),
('dimdim_port', '80', 'string', 255, 'dimdim', 6, 2, 1, 0, ''),
('dimdim_server', 'webmeeting.dimdim.com', 'string', 255, 'dimdim', 6, 1, 1, 0, ''),
('dimdim_user', '', 'string', 255, 'dimdim', 6, 2, 1, 0, ''),
('do_debug', 'off', 'enum', 3, 'debug', 8, 8, 1, 0, ''),
('first_catalogue', 'off', 'enum', 3, '0', 4, 2, 1, 0, ''),
('forum_as_table', 'on', 'enum', 3, '0', 4, 4, 1, 0, ''),
('google_stat_code', '', 'textarea', 65535, '0', 10, 2, 1, 0, ''),
('google_stat_in_lms', '0', 'check', 1, '0', 10, 1, 1, 0, ''),
('hour_request_limit', '48', 'int', 2, 'register', 3, 13, 0, 0, ''),
('hteditor', 'tinymce', 'hteditor', 255, '0', 1, 7, 1, 0, ''),
('htmledit_image_admin', '1', 'check', 255, '0', 8, 1, 1, 0, ''),
('htmledit_image_godadmin', '1', 'check', 255, '0', 8, 0, 1, 0, ''),
('htmledit_image_user', '1', 'check', 255, '0', 8, 2, 1, 0, ''),
('kb_filter_by_user_access', 'on', 'enum', 3, 'main', 4, 10, 1, 0, ''),
('kb_show_uncategorized', 'on', 'enum', 3, 'main', 4, 11, 1, 0, ''),
('lang_check', 'off', 'enum', 3, 'debug', 8, 7, 1, 0, ''),
('lastfirst_mandatory', 'off', 'enum', 3, 'register', 3, 14, 2, 0, ''),
('ldap_port', '389', 'string', 5, '0', 7, 3, 1, 0, ''),
('ldap_server', '192.168.0.1', 'string', 255, '0', 7, 2, 1, 0, ''),
('ldap_used', 'off', 'enum', 3, '0', 7, 1, 1, 0, ''),
('ldap_user_string', '$user@domain2.domain1', 'string', 255, '0', 7, 4, 1, 0, ''),
('mail_sender', 'sample@localhost', 'string', 255, 'register', 3, 12, 0, 0, ''),
('maintenance', 'off', 'enum', 3, 'security', 8, 25, 0, 0, ''),
('maintenance_pw', 'manutenzione', 'string', 16, 'security', 8, 25, 0, 0, ''),
('mandatory_code', 'off', 'enum', 3, 'register', 3, 18, 1, 0, ''),
('max_log_attempt', '0', 'int', 3, '0', 3, 4, 0, 0, ''),
('nl_sendpause', '20', 'int', 3, 'newsletter', 8, 10, 1, 0, ''),
('nl_sendpercycle', '200', 'int', 4, 'newsletter', 8, 9, 1, 0, ''),
('no_answer_in_poll', 'off', 'enum', 3, '0', 4, 7, 1, 0, ''),
('no_answer_in_test', 'off', 'enum', 3, '0', 4, 6, 1, 0, ''),
('on_catalogue_empty', 'on', 'enum', 3, '0', 4, 3, 1, 0, ''),
('org_name_teleskill', '', 'string', 255, 'teleskill', 6, 4, 1, 0, ''),
('owned_by', 'Copyright (c) forma.lms', 'html', 255, '0', 1, 7, 1, 0, ''),
('page_title', 'Forma E-learning', 'string', 255, '0', 1, 1, 1, 0, ''),
('pass_alfanumeric', 'off', 'enum', 3, 'password', 3, 6, 1, 0, ''),
('pass_change_first_login', 'off', 'enum', 3, 'password', 3, 8, 1, 0, ''),
('pass_max_time_valid', '0', 'int', 4, 'password', 3, 9, 1, 0, ''),
('pass_min_char', '5', 'int', 2, 'password', 3, 7, 0, 0, ''),
('pathchat', 'chat/', 'string', 255, 'path', 8, 21, 1, 0, ''),
('pathcourse', 'course/', 'string', 255, 'path', 8, 11, 1, 0, ''),
('pathfield', 'field/', 'string', 255, 'path', 8, 12, 1, 0, ''),
('pathforum', 'forum/', 'string', 255, 'path', 8, 14, 1, 0, ''),
('pathlesson', 'item/', 'string', 255, 'path', 8, 15, 1, 0, ''),
('pathmessage', 'message/', 'string', 255, 'path', 8, 16, 1, 0, ''),
('pathphoto', 'photo/', 'string', 255, 'path', 8, 13, 1, 0, ''),
('pathprj', 'project/', 'string', 255, 'path', 8, 20, 1, 1, ''),
('pathscorm', 'scorm/', 'string', 255, 'path', 8, 17, 1, 0, ''),
('pathsponsor', 'sponsor/', 'string', 255, 'path', 8, 18, 1, 0, ''),
('pathtest', 'test/', 'string', 255, 'path', 8, 19, 1, 0, ''),
('paypal_currency', 'EUR', 'string', 255, '0', 5, 1, 1, 0, ''),
('paypal_mail', '', 'string', 255, '0', 5, 0, 1, 0, ''),
('paypal_sandbox', 'on', 'enum', 3, '0', 5, 3, 1, 0, ''),
('privacy_policy', 'on', 'enum', 3, 'register', 3, 15, 0, 0, ''),
('profile_only_pwd', 'on', 'enum', 3, '0', 3, 1, 1, 0, ''),
('register_deleted_user', 'off', 'enum', 3, '0', 3, 3, 1, 0, ''),
('register_type', 'admin', 'register_type', 10, 'register', 3, 11, 0, 0, ''),
('registration_code_type', '0', 'registration_code_type', 3, 'register', 3, 17, 1, 0, ''),
('request_mandatory_fields_compilation', 'off', 'enum', 3, '0', 3, 2, 1, 0, ''),
('rest_auth_api_key', '', 'string', 255, 'api', 9, 7, 1, 0, ''),
('rest_auth_api_secret', '', 'string', 255, 'api', 9, 8, 1, 0, ''),
('rest_auth_code', '', 'string', 255, 'api', 9, 4, 1, 0, ''),
('rest_auth_lifetime', '60', 'int', 3, 'api', 9, 5, 1, 0, ''),
('rest_auth_method', '1', 'rest_auth_sel_method', 3, 'api', 9, 3, 1, 0, ''),
('rest_auth_update', 'off', 'enum', 3, 'api', 9, 6, 1, 0, ''),
('save_log_attempt', 'no', 'save_log_attempt', 255, '0', 3, 5, 0, 0, ''),
('sco_direct_play', 'on', 'enum', 3, '0', 8, 3, 1, 0, ''),
('sender_event', 'sample@localhost', 'string', 255, '0', 1, 5, 1, 0, ''),
('send_cc_for_system_emails', '', 'string', 255, '0', 8, 4, 1, 0, ''),
('session_ip_control', 'on', 'enum', 3, 'security', 8, 22, 1, 0, ''),
('sms_cell_num_field', '1', 'field_select', 5, '0', 11, 6, 1, 0, ''),
('sms_credit', '0', 'string', 20, '0', 1, 0, 1, 1, ''),
('sms_gateway', 'smsmarket', 'string', 50, '0', 11, 0, 1, 1, ''),
('sms_gateway_host', '193.254.241.47', 'string', 15, '0', 11, 8, 1, 0, ''),
('sms_gateway_id', '3', 'sel_sms_gateway', 1, '0', 11, 7, 1, 0, ''),
('sms_gateway_pass', '', 'string', 255, '0', 11, 5, 1, 0, ''),
('sms_gateway_port', '26', 'int', 5, '0', 11, 9, 1, 0, ''),
('sms_gateway_user', '', 'string', 50, '0', 11, 4, 1, 0, ''),
('sms_international_prefix', '+39', 'string', 3, '0', 11, 1, 1, 0, ''),
('sms_sent_from', '0', 'string', 25, '0', 11, 2, 1, 0, ''),
('social_fb_active', 'off', 'enum', 3, 'main', 12, 0, 1, 0, ''),
('social_fb_api', '', 'string', 255, 'main', 12, 1, 1, 0, ''),
('social_fb_secret', '', 'string', 255, 'main', 12, 2, 1, 0, ''),
('social_google_active', 'off', 'enum', 3, 'main', 12, 9, 1, 0, ''),
('social_google_client_id', '', 'string', 255, 'main', 12, 10, 1, 0, ''),
('social_google_secret', '', 'string', 255, 'main', 12, 11, 1, 0, ''),
('social_linkedin_access', '', 'string', 255, 'main', 12, 7, 1, 0, ''),
('social_linkedin_active', 'off', 'enum', 3, 'main', 12, 6, 1, 0, ''),
('social_linkedin_secret', '', 'string', 255, 'main', 12, 8, 1, 0, ''),
('social_twitter_active', 'off', 'enum', 3, 'main', 12, 3, 1, 0, ''),
('social_twitter_consumer', '', 'string', 255, 'main', 12, 4, 1, 0, ''),
('social_twitter_secret', '', 'string', 255, 'main', 12, 5, 1, 0, ''),
('sso_secret', '', 'text', 255, '0', 9, 1, 1, 0, ''),
('sso_token', 'off', 'enum', 3, '0', 9, 0, 1, 0, ''),
('stop_concurrent_user', 'on', 'enum', 3, 'security', 8, 23, 1, 0, ''),
('tablist_mycourses', 'name,status', 'tablist_mycourses', 255, '0', 4, 1, 1, 0, ''),
('teleskill_max_participant', '300', 'string', 255, 'teleskill', 6, 6, 1, 0, ''),
('teleskill_max_room', '99999999999', 'string', 255, 'teleskill', 6, 5, 1, 0, ''),
('templ_use_field', '0', 'id_field', 11, '0', 1, 0, 1, 1, ''),
('title_organigram_chart', 'Forma', 'string', 255, '0', 1, 0, 1, 1, ''),
('tracking', 'on', 'enum', 3, '0', 4, 8, 1, 0, ''),
('ttlSession', '4000', 'int', 5, '0', 1, 6, 1, 0, ''),
('url', 'http://localhost/', 'string', 255, '0', 1, 2, 1, 0, ''),
('url_checkin_teleskill', 'http://asp.teleskill.it/tvclive/server-1-1.asp', 'string', 255, 'teleskill', 6, 1, 1, 0, ''),
('url_videoconference_teleskill', '', 'string', 255, 'teleskill', 6, 2, 1, 0, ''),
('user_pwd_history_length', '3', 'int', 3, 'password', 3, 10, 1, 0, ''),
('user_quota', '50', 'string', 255, '0', 8, 6, 1, 0, ''),
('use_advanced_form', 'off', 'enum', 3, 'register', 3, 16, 1, 0, ''),
('use_dimdim_api', 'off', 'enum', 3, 'dimdim', 6, 8, 1, 0, ''),
('use_rest_api', 'off', 'enum', 3, 'api', 9, 2, 1, 0, ''),
('use_tag', 'off', 'enum', 3, '0', 4, 5, 1, 0, ''),
('visuItem', '25', 'int', 3, '0', 2, 1, 1, 1, ''),
('visuNewsHomePage', '3', 'int', 5, '0', 1, 0, 1, 1, ''),
('welcome_use_feed', 'on', 'enum', 3, '0', 1, 0, 1, 1, ''),
('template_domain',  '',  'textarea',  '65535',  '0',  '8',  '8',  '1',  '0',  ''),
('on_usercourse_empty', 'off', 'enum', 3, '0', 4, 4, 1, 0, '');


INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`)
VALUES ('file_upload_whitelist', 'zip,jpg,gif,png,txt,csv,rtf,xml,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,pdf,xps,mp4,mp3,flv,swf,mov,wav,ogg,flac,wma,wmv', 'string', 65535, 'security', 8, 25, 1, 0, '');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_setting_group`
--

CREATE TABLE IF NOT EXISTS `core_setting_group` (
  `path_name` varchar(255) NOT NULL DEFAULT '',
  `idst` int(11) NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (`path_name`,`idst`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_setting_group`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_setting_list`
--

CREATE TABLE IF NOT EXISTS `core_setting_list` (
  `path_name` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) NOT NULL DEFAULT '',
  `default_value` text NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT '',
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `load_at_startup` tinyint(1) NOT NULL DEFAULT '0',
  `sequence` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`path_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_setting_list`
--

INSERT INTO `core_setting_list` (`path_name`, `label`, `default_value`, `type`, `visible`, `load_at_startup`, `sequence`) VALUES
('admin_rules.course_presence_admin', '_COURSE_PRESENCE_ADMIN', 'off', 'enum', 1, 1, 7),
('admin_rules.direct_course_subscribe', '_DIRECT_COURSE_SUBSCRIBE', 'off', 'enum', 1, 1, 6),
('admin_rules.direct_user_insert', '_DIRECT_USER_INSERT', 'off', 'enum', 1, 1, 3),
('admin_rules.limit_course_subscribe', '_LIMIT_COURSE_SUBSCRIBE', 'off', 'enum', 1, 1, 4),
('admin_rules.limit_user_insert', '_LIMIT_USER_INSERT', 'off', 'enum', 1, 1, 1),
('admin_rules.max_course_subscribe', '_MAX_COURSE_SUBSCRIBE', '0', 'integer', 1, 1, 5),
('admin_rules.max_user_insert', '_MAX_USER_INSERT', '0', 'integer', 1, 1, 2),
('admin_rules.user_lang_assigned', '', '', 'string', 0, 1, 0),
('ui.directory.custom_columns', '_CUSTOM_COLUMS', '', 'hidden', 0, 1, 0),
('ui.language', '_LANGUAGE', '', 'language', 1, 1, 0),
('user_rules.field_policy', '', '', 'serialized', 0, 1, 0),
('user_rules.user_quota', '', '-1', 'int', 0, 1, 0),
('user_rules.user_quota_used', '', '0', 'int', 0, 1, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `core_setting_user`
--

CREATE TABLE IF NOT EXISTS `core_setting_user` (
  `path_name` varchar(255) NOT NULL DEFAULT '',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (`path_name`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_setting_user`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_st`
--

CREATE TABLE IF NOT EXISTS `core_st` (
  `idst` int(11) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`idst`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Security Tokens';

--
-- Dump dei dati per la tabella `core_st`
--

INSERT INTO `core_st` (`idst`) VALUES
(11831),
(11832),
(11833),
(11834),
(11835);

-- --------------------------------------------------------

--
-- Struttura della tabella `core_tag`
--

CREATE TABLE IF NOT EXISTS `core_tag` (
  `id_tag` int(11) NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(255) NOT NULL DEFAULT '',
  `id_parent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_tag`),
  KEY `tag_name` (`tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_tag`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_tag_relation`
--

CREATE TABLE IF NOT EXISTS `core_tag_relation` (
  `id_tag` int(11) NOT NULL DEFAULT '0',
  `id_resource` int(11) NOT NULL DEFAULT '0',
  `resource_type` varchar(255) NOT NULL DEFAULT '',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `id_course` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_tag`,`id_resource`,`resource_type`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_tag_relation`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_tag_resource`
--

CREATE TABLE IF NOT EXISTS `core_tag_resource` (
  `id_resource` int(11) NOT NULL DEFAULT '0',
  `resource_type` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `sample_text` text NOT NULL,
  `permalink` text NOT NULL,
  PRIMARY KEY (`id_resource`,`resource_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_tag_resource`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_task`
--

CREATE TABLE IF NOT EXISTS `core_task` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `conn_source` varchar(50) NOT NULL DEFAULT '',
  `conn_destination` varchar(50) NOT NULL DEFAULT '',
  `schedule_type` enum('at','any') NOT NULL DEFAULT 'at',
  `schedule` varchar(50) NOT NULL DEFAULT '',
  `import_type` varchar(50) NOT NULL DEFAULT '',
  `map` text NOT NULL,
  `last_execution` datetime DEFAULT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_task`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_transaction`
--

CREATE TABLE IF NOT EXISTS `core_transaction` (
  `id_trans` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `location` varchar(10) NOT NULL DEFAULT '',
  `date_creation` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_activated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_trans`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_transaction`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_transaction_info`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_transaction_info`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_user`
--

CREATE TABLE IF NOT EXISTS `core_user` (
  `idst` int(11) NOT NULL DEFAULT '0',
  `userid` varchar(255) NOT NULL DEFAULT '',
  `firstname` varchar(255) NOT NULL DEFAULT '',
  `lastname` varchar(255) NOT NULL DEFAULT '',
  `pass` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `signature` text NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `lastenter` datetime DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT '1',
  `pwd_expire_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `force_change` tinyint(1) NOT NULL DEFAULT '0',
  `register_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `facebook_id` varchar(255) DEFAULT NULL,
  `twitter_id` varchar(255) DEFAULT NULL,
  `linkedin_id` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `privacy_policy` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`idst`),
  UNIQUE KEY `userid` (`userid`),
  UNIQUE KEY `facebook_id` (`facebook_id`),
  UNIQUE KEY `twitter_id` (`twitter_id`),
  UNIQUE KEY `linkedin_id` (`linkedin_id`),
  UNIQUE KEY `google_id` (`google_id`),
  UNIQUE KEY `facebook_id_2` (`facebook_id`),
  UNIQUE KEY `google_id_2` (`google_id`),
  UNIQUE KEY `twitter_id_2` (`twitter_id`),
  UNIQUE KEY `linkedin_id_2` (`linkedin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_user`
--

INSERT INTO `core_user` (`idst`, `userid`, `firstname`, `lastname`, `pass`, `email`, `avatar`, `signature`, `level`, `lastenter`, `valid`, `pwd_expire_at`, `force_change`, `register_date`, `facebook_id`, `twitter_id`, `linkedin_id`, `google_id`, `privacy_policy`) VALUES
(270, '/Anonymous', '', '', '', '', '', '', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `core_user_file`
--

CREATE TABLE IF NOT EXISTS `core_user_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_idst` int(11) NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL DEFAULT '',
  `fname` varchar(255) NOT NULL DEFAULT '',
  `real_fname` varchar(255) NOT NULL DEFAULT '',
  `media_url` varchar(255) NOT NULL DEFAULT '',
  `size` int(11) NOT NULL DEFAULT '0',
  `uldate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_user_file`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_user_log_attempt`
--

CREATE TABLE IF NOT EXISTS `core_user_log_attempt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(255) NOT NULL DEFAULT '',
  `attempt_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `attempt_number` int(5) NOT NULL DEFAULT '0',
  `user_ip` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_user_log_attempt`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_user_profileview`
--

CREATE TABLE IF NOT EXISTS `core_user_profileview` (
  `id_owner` int(11) NOT NULL DEFAULT '0',
  `id_viewer` int(11) NOT NULL DEFAULT '0',
  `date_view` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id_owner`,`id_viewer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_user_profileview`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_user_temp`
--

CREATE TABLE IF NOT EXISTS `core_user_temp` (
  `idst` int(11) NOT NULL DEFAULT '0',
  `userid` varchar(255) NOT NULL DEFAULT '',
  `firstname` varchar(100) NOT NULL DEFAULT '',
  `lastname` varchar(100) NOT NULL DEFAULT '',
  `pass` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `language` varchar(50) NOT NULL DEFAULT '',
  `request_on` datetime DEFAULT '0000-00-00 00:00:00',
  `random_code` varchar(255) NOT NULL DEFAULT '',
  `create_by_admin` int(11) NOT NULL DEFAULT '0',
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `facebook_id` varchar(255) DEFAULT NULL,
  `twitter_id` varchar(255) DEFAULT NULL,
  `linkedin_id` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idst`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_user_temp`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_wiki`
--

CREATE TABLE IF NOT EXISTS `core_wiki` (
  `wiki_id` int(11) NOT NULL AUTO_INCREMENT,
  `source_platform` varchar(255) NOT NULL DEFAULT '',
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `language` varchar(50) NOT NULL DEFAULT '',
  `other_lang` text NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `page_count` int(11) NOT NULL DEFAULT '0',
  `revision_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wiki_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_wiki`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_wiki_page`
--

CREATE TABLE IF NOT EXISTS `core_wiki_page` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_code` varchar(60) NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `page_path` varchar(255) NOT NULL DEFAULT '',
  `lev` int(3) NOT NULL DEFAULT '0',
  `wiki_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=1 ;

--
-- Dump dei dati per la tabella `core_wiki_page`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_wiki_page_info`
--

CREATE TABLE IF NOT EXISTS `core_wiki_page_info` (
  `page_id` int(11) NOT NULL DEFAULT '0',
  `language` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `version` int(11) NOT NULL DEFAULT '0',
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wiki_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_wiki_page_info`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_wiki_revision`
--

CREATE TABLE IF NOT EXISTS `core_wiki_revision` (
  `wiki_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `version` int(11) NOT NULL DEFAULT '0',
  `language` varchar(50) NOT NULL DEFAULT '0',
  `author` int(11) NOT NULL DEFAULT '0',
  `rev_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `content` longtext NOT NULL,
  PRIMARY KEY (`wiki_id`,`page_id`,`version`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_wiki_revision`
--


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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
(41, 11, 2, 'Collaborative Area', ''),
(26, 11, 3, 'Teacher Area', ''),
(27, 11, 4, 'Stat Area', ''),
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
(11, 10, 25, 1, ''),
(11, 11, 25, 2, ''),
(11, 12, 26, 1, ''),
(11, 13, 25, 4, ''),
(11, 14, 25, 5, ''),
(11, 15, 25, 8, ''),
(11, 16, 25, 7, ''),
(11, 17, 41, 6, ''),
(11, 18, 25, 6, ''),
(11, 19, 41, 1, ''),
(11, 20, 41, 2, ''),
(11, 21, 41, 5, ''),
(11, 22, 41, 4, ''),
(11, 23, 41, 3, ''),
(11, 24, 26, 2, ''),
(11, 25, 25, 3, ''),
(11, 26, 26, 5, ''),
(11, 27, 26, 3, ''),
(11, 28, 26, 15, ''),
(11, 29, 27, 1, ''),
(11, 30, 27, 2, ''),
(11, 31, 27, 3, ''),
(11, 40, 26, 4, ''),
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
  `method` varchar(255) NOT NULL DEFAULT '',
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

--
-- Limiti per la tabella `core_lang_translation`
--
ALTER TABLE `core_lang_translation`
  ADD CONSTRAINT `core_lang_translation_ibfk_1` FOREIGN KEY (`lang_code`) REFERENCES `core_lang_language` (`lang_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `core_lang_translation_ibfk_2` FOREIGN KEY (`id_text`) REFERENCES `core_lang_text` (`id_text`) ON DELETE CASCADE ON UPDATE CASCADE;










-- --------------------------------------------------------


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
