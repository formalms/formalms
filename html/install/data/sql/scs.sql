-- MySQL dump 10.9
--
-- Host: localhost    Database: formalms
-- ------------------------------------------------------
-- Server version

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
