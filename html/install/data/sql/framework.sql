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


--
-- Struttura della tabella `core_customfield`
--

CREATE TABLE IF NOT EXISTS `core_customfield` (
  `id_field` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL DEFAULT '',
  `type_field` varchar(255) NOT NULL DEFAULT '',
  `sequence` int(5) NOT NULL DEFAULT '0',
  `show_on_platform` varchar(255) NOT NULL DEFAULT 'framework,',
  `use_multilang` tinyint(1) NOT NULL DEFAULT '0',
  `area_code` varchar(255) NOT NULL,
  PRIMARY KEY (`id_field`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_customfield`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_customfield_area`
--

CREATE TABLE IF NOT EXISTS `core_customfield_area` (
  `area_code` varchar(255) NOT NULL DEFAULT '',
  `area_name` varchar(255) NOT NULL DEFAULT '',
  `area_table` varchar(255) NOT NULL DEFAULT '',
  `area_field` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_customfield_area`
--

INSERT INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('LO_TEST', 'Learning Object Test', '%lms_testquest', 'idQuest');
INSERT INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('COURSE', 'Course', '%lms_course', 'idCourse');
INSERT INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('COURSE_EDITION', 'Course Edition', '%lms_course_editions', 'id_edition');
INSERT INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('ORG_CHART', 'Org Chart Tree', 'core_org_chart_tree', 'idOrg');
INSERT INTO `core_customfield_area` (`area_code`, `area_name`, `area_table`, `area_field`) VALUES('LO_OBJECT', 'Learning Object', 'learning_organization', 'idOrg');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_customfield_entry`
--

CREATE TABLE IF NOT EXISTS `core_customfield_entry` (
  `id_field` varchar(11) NOT NULL DEFAULT '',
  `id_obj` int(11) NOT NULL DEFAULT '0',
  `obj_entry` text NOT NULL,
  PRIMARY KEY (`id_field`,`id_obj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_customfield_entry`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_customfield_lang`
--

CREATE TABLE IF NOT EXISTS `core_customfield_lang` (
  `id_field` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(255) NOT NULL DEFAULT '',
  `translation` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_customfield_lang`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_customfield_son`
--

CREATE TABLE IF NOT EXISTS `core_customfield_son` (
  `id_field_son` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL DEFAULT '',
  `id_field` int(11) NOT NULL DEFAULT '0',
  `sequence` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_field_son`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_customfield_son`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_customfield_son_lang`
--

CREATE TABLE IF NOT EXISTS `core_customfield_son_lang` (
  `id_field_son` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(50) NOT NULL DEFAULT '',
  `translation` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_customfield_son_lang`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `core_customfield_type`
--

CREATE TABLE IF NOT EXISTS `core_customfield_type` (
  `type_field` varchar(255) NOT NULL DEFAULT '',
  `type_file` varchar(255) NOT NULL DEFAULT '',
  `type_class` varchar(255) NOT NULL DEFAULT '',
  `type_category` varchar(255) NOT NULL DEFAULT 'standard',
  PRIMARY KEY (`type_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_customfield_type`
--

INSERT INTO `core_customfield_type` (`type_field`, `type_file`, `type_class`, `type_category`) VALUES('dropdown', 'class.dropdown.php', 'Field_Dropdown', 'standard');
INSERT INTO `core_customfield_type` (`type_field`, `type_file`, `type_class`, `type_category`) VALUES('textfield', 'class.textfield.php', 'Field_Textfield', 'standard');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_db_upgrades`
--

CREATE TABLE IF NOT EXISTS `core_db_upgrades` (
  `script_id` int(11) NOT NULL AUTO_INCREMENT,
  `script_name` varchar(255) NOT NULL,
  `script_description` text,
  `script_version` varchar(255) DEFAULT NULL,
  `core_version` varchar(255) DEFAULT NULL,
  `creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `execution_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`script_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


--
-- Dump dei dati per la tabella `core_db_upgrades`
--

INSERT INTO `core_db_upgrades` (`script_id`, `script_name`, `script_description`, `script_version`, `core_version`, `creation_date`, `execution_date`) VALUES(1, 'add_log_db_upgrades.sql', 'Creazione tabella di log per script update db', '1.0', '2.0', '2016-10-06 08:47:53', '2016-10-06 08:47:53');


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
(41, 'UserNewWaiting', 'framework', ''),
(42, 'UserNewApi', 'framework', ''),
(43, 'UserCourseInsertedApi', 'lms-a', ''),
(44, 'UserCourseInsertedModerators', 'lms-a', ''),
(45, 'UserCourseSuspendedSuperAdmin', 'framework', ''),
(46, 'UserRegistrationSuperadmins', 'lms-a', '');

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
(5, 40),
(1, 42),
(1, 43),
(1, 44),
(1, 45),
(1, 46);

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
(41, 41, 'mandatory', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'godadmin,admin'),
(42, 42, 'mandatory', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(43, 43, 'mandatory', 'email', '_EVENT_RECIPIENTS_USER', 'godadmin,admin,user'),
(44, 44, 'mandatory', 'email', '_EVENT_RECIPIENTS_TEACHER', 'admin'),
(45, 45, 'not_used', 'email', '_EVENT_RECIPIENTS_TEACHER_GOD', 'godadmin,admin,user'),
(46, 46, 'mandatory', 'email', '_EVENT_RECIPIENTS_MODERATORS_GOD', 'admin');

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
(6, '/framework/level/user', 'Group of normal users', 'true', 'free', ''),
(301, '/lms/custom/1/7', 'for custom lms menu', 'true', 'free', ''),
(302, '/lms/custom/1/6', 'for custom lms menu', 'true', 'free', ''),
(303, '/lms/custom/1/5', 'for custom lms menu', 'true', 'free', ''),
(304, '/lms/custom/1/4', 'for custom lms menu', 'true', 'free', ''),
(305, '/lms/custom/1/3', 'for custom lms menu', 'true', 'free', ''),
(306, '/lms/custom/1/2', 'for custom lms menu', 'true', 'free', ''),
(307, '/lms/custom/1/1', 'for custom lms menu', 'true', 'free', '');
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
  `plugin_id` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_text`),
  UNIQUE KEY `text_key` (`text_key`,`text_module`, `plugin_id`)
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
  `is_active` enum('true','false') NOT NULL DEFAULT 'true',
  `collapse` enum('true','false') NOT NULL DEFAULT 'true',
  `idParent` int(11) DEFAULT NULL,
  `idPlugin` int(11) DEFAULT NULL,
  `of_platform` varchar(255) NOT NULL DEFAULT 'framework',
  PRIMARY KEY (`idMenu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_menu`
--

INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(1, '_USER_MANAGMENT', '<i class="fa fa-users fa-fw"></i>', 1, 'true', 'true', NULL, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(2, '_FIRST_LINE_lms', ' <i class="fa fa-graduation-cap" aria-hidden="true"></i>', 2, 'true', 'true', NULL, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(3, '_CONTENTS', '<i class="fa fa-clipboard fa-fw"></i>', 3, 'true', 'true', NULL, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(4, '_REPORT', '<i class="fa fa-bar-chart-o fa-fw"></i>', 4, 'true', 'true', NULL, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(5, '_CONFIGURATION', '<i class="fa fa-cogs fa-fw"></i>', 5, 'true', 'true', NULL, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(11, '_LISTUSER', '', 1, 'true', 'true', 1, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(12, '_MANAGE_GROUPS', '', 2, 'true', 'true', 1, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(13, '_COMPETENCES', '', 3, 'true', 'true', 1, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(14, '_FUNCTIONAL_ROLE', '', 4, 'true', 'true', 1, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(15, '_ADMINISTRATORS', '', 5, 'true', 'true', 1, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(21, '_COURSES', '', 1, 'true', 'true', 2, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(22, '_LOCATION', '', 2, 'true', 'true', 2, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(23, '_CONTEST', '', 3, 'true', 'true', 2, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(24, '_MAN_CERTIFICATE', '', 4, 'true', 'true', 2, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(25, '_MANAGEMENT_RESERVATION', '', 5, 'true', 'true', 2, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(26, '_CONTENT_LIBRARY', '', 6, 'true', 'true', 2, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(27, '_ENROLLRULES', '', 7, 'true', 'true', 2, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(28, '_TRANSACTION', '', 8, 'true', 'true', 2, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(31, '_WEBPAGES', '', 1, 'true', 'true', 3, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(33, '_NEWS_INTERNAL', '', 3, 'true', 'true', 3, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(34, '_COMMUNICATION_MAN', '', 4, 'true', 'true', 3, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(35, '_NEWSLETTER', '', 5, 'true', 'true', 3, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(40, '_REPORT', '', 1, 'true', 'true', 4, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(50, '_FIELD_MANAGER', '', 4, 'true', 'true', 5, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(51, '_DASHBOARD', '', 1, 'true', 'true', 5, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(52, '_CONFIG_SYS', '', 2, 'true', 'true', 5, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(54, '_PLUGIN_MANAGER', '', 4, 'true', 'true', 5, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(55, '_LANG', '', 5, 'true', 'true', 5, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(59, '_CONFIG_ELEARNING', '', 3, 'true', 'true', 5, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(151, '_ADMIN_RULES', '', 1, 'true', 'true', 15, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(152, '_ADMIN_MANAGER', '', 2, 'true', 'true', 15, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(211, '_COURSES', '', 1, 'true', 'true', 21, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(212, '_COURSEPATH', '', 2, 'true', 'true', 21, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(213, '_CATALOGUE', '', 3, 'true', 'true', 21, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(241, '_CERTIFICATE', '', 1, 'true', 'true', 24, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(242, '_META_CERTIFICATE', '', 2, 'true', 'true', 24, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(251, '_EVENTS', '', 1, 'true', 'true', 25, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(252, '_CATEGORY', '', 2, 'true', 'true', 25, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(253, '_RESERVATION', '', 3, 'true', 'true', 25, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(501, '_FIELD_MANAGER', '', 1, 'true', 'true', 50, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(502, '_CUSTOMFIELD_MANAGER', '', 2, 'true', 'true', 50, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(521, '_CONFIGURATION', '', 1, 'true', 'true', 52, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(523, '_EVENTMANAGER', '', 3, 'true', 'true', 52, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(524, '_IOTASK', '', 4, 'true', 'true', 52, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(526, '_PRIVACYPOLICIES', '', 6, 'true', 'true', 52, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(528, '_CODE', '', 8, 'true', 'true', 52, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(591, '_MAN_MENU', '', 1, 'true', 'true', 59, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(592, '_MIDDLE_AREA', '', 2, 'true', 'true', 59, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(593, '_QUESTCATEGORY', '', 3, 'true', 'true', 59, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(594, '_TIME_PERIODS', '', 4, 'true', 'true', 59, NULL, 'framework');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(595, '_LABEL', '', 5, 'true', 'true', 59, NULL, 'framework');

INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(596, '_MYCOURSES', '', 1, 'true', 'true', NULL, NULL, 'lms');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(597, '_CATALOGUE', '', 2, 'false', 'true', NULL, NULL, 'lms');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(598, '_PUBLIC_FORUM', '', 3, 'true', 'true', NULL, NULL, 'lms');
INSERT INTO `core_menu` (`idMenu`, `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES(599, '_HELPDESK', '<span class="glyphicon glyphicon-question-sign top-menu__label"></span>', 1000, 'false', 'true', NULL, NULL, 'lms');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_menu_under`
--

CREATE TABLE IF NOT EXISTS `core_menu_under` (
  `idUnder` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `module_name` varchar(255) DEFAULT NULL,
  `default_name` varchar(255) NOT NULL DEFAULT '',
  `default_op` varchar(255) DEFAULT NULL,
  `associated_token` varchar(255) DEFAULT NULL,
  `of_platform` varchar(255) DEFAULT NULL,
  `sequence` int(3) NOT NULL DEFAULT '0',
  `class_file` varchar(255) DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `mvc_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idUnder`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_menu_under`
--

INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(11, 11, 'usermanagement', '_LISTUSER', '', 'view', 'framework', 1, '', '', 'adm/usermanagement/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(12, 12, 'groupmanagement', '_MANAGE_GROUPS', '', 'view', 'framework', 1, '', '', 'adm/groupmanagement/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(13, 13, 'competences', '_COMPETENCES', '', 'view', 'framework', 1, '', '', 'adm/competences/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(14, 14, 'functionalroles', '_FUNCTIONAL_ROLE', '', 'view', 'framework', 4, '', '', 'adm/functionalroles/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(22, 22, 'location', '_LOCATION', '', 'view', 'alms', 2, '', '', 'alms/location/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(23, 23, 'games', '_CONTEST', '', 'view', 'alms', 3, '', '', 'alms/games/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(26, 26, 'kb', '_CONTENT_LIBRARY', '', 'view', 'alms', 6, '', '', 'alms/kb/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(27, 27, 'enrollrules', '_ENROLLRULES', '', 'view', 'alms', 7, '', '', 'alms/enrollrules/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(28, 28, 'transaction', '_TRANSACTION', '', 'view', 'alms', 8, '', '', 'alms/transaction/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(31, 31, 'webpages', '_WEBPAGES', 'webpages', 'view', 'alms', 1, 'class.webpages.php', 'Module_Webpages', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(32, 32, 'news', '_NEWS', 'news', 'view', 'alms', 2, 'class.news.php', 'Module_News', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(33, 33, 'internal_news', '_NEWS_INTERNAL', 'news', 'view', 'alms', 3, 'class.internal_news.php', 'Module_Internal_News', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(34, 34, 'communication', '_COMMUNICATION_MAN', '', 'view', 'alms', 1, '', '', 'alms/communication/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(35, 35, 'newsletter', '_NEWSLETTER', 'newsletter', 'view', 'framework', 1, 'class.newsletter.php', 'Module_Newsletter', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(40, 40, 'report', '_REPORT', 'reportlist', 'view', 'alms', 1, 'class.report.php', 'Module_Report', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(51, 51, 'dashboard', '_DASHBOARD', '', 'view', 'framework', 1, '', '', 'adm/dashboard/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(54, 54, 'pluginmanager', '_PLUGIN_MANAGER', '', 'view', 'framework', 4, '', '', 'adm/pluginmanager/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(55, 55, 'lang', '_LANG', '', 'view', 'framework', 5, '', '', 'adm/lang/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(151, 151, 'adminrules', '_ADMIN_RULES', '', 'view', 'framework', 1, '', '', 'adm/adminrules/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(152, 152, 'adminmanager', '_ADMIN_MANAGER', '', 'view', 'framework', 1, '', '', 'adm/adminmanager/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(211, 211, 'course', '_COURSES', '', 'view', 'alms', 1, '', '', 'alms/course/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(212, 212, 'coursepath', '_COURSEPATH', 'pathlist', 'view', 'alms', 2, 'class.coursepath.php', 'Module_Coursepath', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(213, 213, 'catalogue', '_CATALOGUE', 'catlist', 'view', 'alms', 3, 'class.catalogue.php', 'Module_Catalogue', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(241, 241, 'certificate', '_CERTIFICATE', 'certificate', 'mod', 'alms', 1, 'class.certificate.php', 'Module_Certificate', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(242, 242, 'meta_certificate', '_META_CERTIFICATE', 'meta_certificate', 'view', 'alms', 2, 'class.meta_certificate.php', 'Module_Meta_Certificate', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(251, 251, 'reservation', '_EVENTS', 'view_event', 'view', 'alms', 1, 'class.reservation.php', 'Module_Reservation', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(252, 252, 'reservation', '_CATEGORY', 'view_category', 'view', 'alms', 2, 'class.reservation.php', 'Module_Reservation', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(253, 253, 'reservation', '_RESERVATION', 'view_registration', 'view', 'alms', 3, 'class.reservation.php', 'Module_Reservation', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(501, 501, 'field_manager', '_FIELD_MANAGER', 'field_list', 'view', 'framework', 1, 'class.field_manager.php', 'Module_Field_Manager', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(502, 502, 'customfield_manager', '_CUSTOMFIELD_MANAGER', 'field_list', 'view', 'framework', 2, 'class.customfield_manager.php', 'Module_Customfield_Manager', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(521, 521, 'setting', '_CONFIGURATION', '', 'view', 'framework', 1, 'class.configuration.php', 'Module_Configuration', 'adm/setting/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(523, 523, 'event_manager', '_EVENTMANAGER', 'display', 'view_event_manager', 'framework', 3, 'class.event_manager.php', 'Module_Event_Manager', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(524, 524, 'iotask', '_IOTASK', 'iotask', 'view', 'framework', 4, 'class.iotask.php', 'Module_IOTask', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(526, 526, 'privacypolicy', '_PRIVACYPOLICIES', '', 'view', 'framework', 6, '', '', 'adm/privacypolicy/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(528, 528, 'code', '_CODE', 'list', 'view', 'framework', 8, 'class.code.php', 'Module_Code', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(591, 591, 'amanmenu', '_MAN_MENU', 'mancustom', 'view', 'alms', 1, 'class.amanmenu.php', 'Module_AManmenu', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(592, 592, 'middlearea', '_MIDDLE_AREA', 'view_area', 'view', 'alms', 2, 'class.middlearea.php', 'Module_MiddleArea', '');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(593, 593, 'questcategory', '_QUESTCATEGORY', '', 'view', 'alms', 3, '', '', 'alms/questcategory/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(594, 594, 'timeperiods', '_TIME_PERIODS', '', 'view', 'alms', 4, '', '', 'alms/timeperiods/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(595, 595, 'label', '_LABEL', '', 'view', 'alms', 5, '', '', 'alms/label/show');

INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(596, 596, 'course', '_MYCOURSES', NULL, 'view', 'lms', 1, NULL, NULL, 'lms/mycourses/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(597, 597, 'coursecatalogue', '_CATALOGUE', NULL, 'view', 'lms', 2, NULL, NULL, 'lms/catalog/show');
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(598, 598, 'public_forum', '_PUBLIC_FORUM', 'forum', 'view', 'lms', 3, NULL, NULL, NULL);
INSERT INTO `core_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES(599, 599, 'helpdesk', '_HELPDESK', 'popup', 'view', 'lms', 1000, NULL, NULL, NULL);

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

INSERT INTO `core_platform` (`platform`, `class_file`, `class_name`, `class_file_menu`, `class_name_menu`, `class_name_menu_managment`, `file_class_config`, `class_name_config`, `var_default_template`, `class_default_admin`, `sequence`, `is_active`, `mandatory`, `dependencies`, `main`, `hidden_in_config`) VALUES('framework', '', '', 'class.admin_menu_fw.php', 'Admin_Framework', 'Admin_Managment_Framework', 'class.conf_fw.php', 'Config_Framework', 'defaultTemplate', 'Module', 1, 'true', 'true', '', 'false', 'false');
INSERT INTO `core_platform` (`platform`, `class_file`, `class_name`, `class_file_menu`, `class_name_menu`, `class_name_menu_managment`, `file_class_config`, `class_name_config`, `var_default_template`, `class_default_admin`, `sequence`, `is_active`, `mandatory`, `dependencies`, `main`, `hidden_in_config`) VALUES('lms', '', '', 'class.admin_menu_lms.php', 'Admin_Lms', 'Admin_Managment_Lms', 'class.conf_lms.php', 'Config_Lms', 'defaultTemplate', 'LmsAdminModule', 2, 'true', 'false', '', 'true', 'false');
INSERT INTO `core_platform` (`platform`, `class_file`, `class_name`, `class_file_menu`, `class_name_menu`, `class_name_menu_managment`, `file_class_config`, `class_name_config`, `var_default_template`, `class_default_admin`, `sequence`, `is_active`, `mandatory`, `dependencies`, `main`, `hidden_in_config`) VALUES('scs', '', '', 'class.admin_menu_scs.php', 'Admin_Scs', '', 'class.conf_scs.php', 'Config_Scs', 'defaultTemplate', 'ScsAdminModule', 4, 'true', 'false', '', 'false', 'false');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_plugin`
--

CREATE TABLE IF NOT EXISTS `core_plugin` (
  `plugin_id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(64) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `version` varchar(16) NOT NULL,
  `author` varchar(128) NOT NULL,
  `link` varchar(255) NOT NULL,
  `priority` int(5) NOT NULL,
  `description` text NOT NULL,
  `regroup` int(11) NOT NULL,
  `active` int(1) NOT NULL,
  `core` int(1) NOT NULL,
  PRIMARY KEY (`plugin_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_plugin`
--

INSERT IGNORE INTO `core_plugin` (`name`, `title`, `category`, `version`, `author`, `link`, `priority`, `description`, `active`, `core`) VALUES ('FormaAuth', 'Forma Auth', '', '1.0', 'Joint Technologies', '', '0', 'forma auth', '1', b'1');

-- --------------------------------------------------------

--
-- Struttura della tabella `core_privacypolicy`
--

CREATE TABLE IF NOT EXISTS `core_privacypolicy` (
  `id_policy` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `is_default` int(1) NOT NULL DEFAULT '0',
  `lastedit_date` datetime NOT NULL,
  `validity_date` datetime NOT NULL,
  PRIMARY KEY (`id_policy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

--
-- Dump dei dati per la tabella `core_privacypolicy`
--
INSERT INTO `core_privacypolicy` (`name`, `is_default`, `lastedit_date`, `validity_date`) VALUES( 'Default Privacy Policy', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

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
-- Struttura della tabella `core_privacypolicy_user`
--

CREATE TABLE IF NOT EXISTS `core_privacypolicy_user` (
  `id_policy` int(11) NOT NULL,
  `idst` int(11)  NOT NULL,
  `accept_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_privacypolicy_user`
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
-- Struttura della tabella `core_requests`
--

CREATE TABLE IF NOT EXISTS `core_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `controller` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `plugin` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


--
-- Dump dei dati per la tabella `core_requests`
--

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
  `idPlugin` INT(10) NULL,
  PRIMARY KEY (`idst`),
  KEY `roleid` (`roleid`),
  CONSTRAINT FOREIGN KEY (`idPlugin`) REFERENCES `core_plugin`(`plugin_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
(111, '/framework/admin/functionalroles/view', NULL),
(112, '/framework/admin/functionalroles/mod', NULL),
(113, '/framework/admin/functionalroles/associate_user', NULL),
(114, '/framework/admin/competences/view', NULL),
(115, '/framework/admin/competences/mod', NULL),
(116, '/framework/admin/competences/associate_user', NULL),
(118, '/framework/admin/code/view', NULL),
(119, '/framework/admin/setting/view', NULL),
(120, '/lms/admin/meta_certificate/view', NULL),
(121, '/lms/admin/meta_certificate/mod', NULL),
(122, '/framework/admin/usermanagement/mod_org', NULL),
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
(231, '/framework/admin/usermanagement/add_org', NULL),
(232, '/framework/admin/usermanagement/mod_org', NULL),
(233, '/framework/admin/usermanagement/del_org', NULL),
(234, '/lms/course/private/light_repo/view_all', NULL),
(235, '/framework/admin/customfield_manager/view', NULL),
(236, '/framework/admin/customfield_manager/add', NULL),
(237, '/framework/admin/customfield_manager/mod', NULL),
(238, '/framework/admin/customfield_manager/del', NULL),
(280, '/framework/admin/pluginmanager/view', NULL),
(295,'/lms/course/private/statistic/view_all', NULL),
(296,'/lms/course/private/stats/view_all_statuser', NULL),
(297,'/lms/course/private/stats/view_all_statcourse', NULL),
(298,'/lms/course/private/coursestats/view_all', NULL),
(299,'/lms/course/private/coursereport/view_all', NULL),
(300, '/lms/course/public/helpdesk/view', NULL),
(11553, '/framework/admin/usermanagement/associate_user', NULL),
(11757, '/lms/course/private/coursestats/view', ''),
(11835, '/lms/course/private/presence/view', ''),
(11836, '/lms/admin/certificate/assign', NULL),
(11837, '/lms/admin/certificate/release', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `core_role_members`
--

CREATE TABLE IF NOT EXISTS `core_role_members` (
  `idst` int(11) NOT NULL DEFAULT '0',
  `idstMember` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idst`,`idstMember`),
  CONSTRAINT FOREIGN KEY (idst) REFERENCES core_role(idst) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `core_role_members`
--

INSERT INTO `core_role_members` (`idst`, `idstMember`) VALUES
(7,3),
(8,3),
(9,3),
(10,3),
(11,3),
(12,3),
(13,3),
(14,3),
(15,3),
(16,3),
(17,3),
(18,3),
(19,3),
(20,3),
(21,3),
(22,3),
(23,3),
(24,3),
(25,3),
(26,3),
(27,3),
(28,3),
(31,3),
(32,3),
(33,3),
(34,3),
(35,3),
(36,3),
(37,3),
(38,3),
(39,3),
(40,3),
(41,3),
(42,3),
(43,3),
(44,3),
(45,3),
(46,3),
(47,3),
(48,3),
(49,3),
(50,3),
(51,3),
(52,3),
(53,3),
(54,3),
(55,3),
(56,3),
(57,3),
(58,3),
(59,3),
(60,3),
(61,3),
(62,3),
(63,3),
(64,3),
(65,3),
(66,3),
(67,3),
(68,3),
(69,3),
(70,3),
(71,3),
(72,3),
(73,3),
(74,3),
(75,3),
(76,3),
(77,3),
(78,3),
(79,3),
(80,1),
(81,1),
(82,1),
(83,1),
(84,1),
(85,1),
(86,1),
(87,1),
(88,1),
(89,1),
(90,1),
(91,1),
(92,3),
(93,3),
(94,3),
(95,3),
(96,1),
(96,3),
(97,1),
(97,3),
(111,3),
(112,3),
(113,3),
(114,3),
(115,3),
(116,3),
(118,3),
(119,3),
(120,3),
(121,3),
(122,3),
(186,10893),
(186,10894),
(186,10895),
(187,10893),
(187,10894),
(187,10895),
(187,10896),
(187,10897),
(187,10898),
(187,10899),
(195,10893),
(195,10894),
(195,10895),
(195,10896),
(195,10897),
(195,10898),
(195,10899),
(207,10893),
(207,10894),
(207,10895),
(207,10896),
(207,10897),
(207,10898),
(216,10893),
(216,10894),
(216,10895),
(216,10896),
(217,10893),
(217,10894),
(217,10895),
(217,10896),
(218,10893),
(218,10894),
(218,10895),
(218,10896),
(219,10893),
(219,10894),
(220,10893),
(220,10894),
(220,10895),
(221,10893),
(221,10894),
(222,10893),
(222,10894),
(222,10895),
(226,3),
(227,3),
(228,3),
(229,3),
(230,3),
(235,3),
(236,3),
(237,3),
(238,3),
(280,3),
(11553,3),
(11757,10893),
(11757,10894),
(11757,10895),
(11757,10896),
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
(187, 301),
(187, 302),
(187, 303),
(187, 304),
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
(195, 306),
(195, 307),
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
(198, 305),
(198, 306),
(198, 307),
(199, 301),
(199, 302),
(199, 303),
(200, 301),
(200, 302),
(200, 303),
(200, 304),
(200, 305),
(200, 306),
(200, 307),
(201, 301),
(201, 302),
(201, 303),
(201, 304),
(202, 301),
(202, 302),
(202, 303),
(202, 304),
(202, 305),
(203, 301),
(204, 301),
(205, 301),
(205, 302),
(205, 303),
(205, 304),
(207, 301),
(207, 302),
(207, 303),
(207, 304),
(207, 305),
(207, 306),
(207, 307),
(216, 301),
(216, 302),
(216, 303),
(216, 304),
(217, 301),
(217, 302),
(217, 303),
(217, 304),
(218, 301),
(218, 302),
(218, 303),
(218, 304),
(219, 301),
(219, 302),
(220, 301),
(220, 302),
(220, 303),
(220, 304),
(221, 301),
(221, 302),
(222, 301),
(222, 302),
(222, 303),
(222, 304),
(296, 301),
(297, 301),
(297, 302),
(297, 303),
(297, 304),
(11757, 301),
(11757, 302),
(11757, 303),
(11757, 304),
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
  `regroup` int(11) NOT NULL DEFAULT '0',
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
('Clear_Twig_Cache', 'index.php?r=adm/setting/clearTwigCache', 'button', 2, 'twig_cache', 8, 27, 0, 0, ''),
('common_admin_session', 'on', 'enum', 3, 'security', 8, 24, 1, 0, ''),
('conference_creation_limit_per_user', '99999999999', 'string', 255, '0', 6, 0, 1, 0, ''),
('course_block', 'off', 'enum', 3, '0', 4, 15, 1, 0, ''),
('course_quota', '500', 'string', 255, '0', 4, 9, 1, 0, ''),
('currency_symbol', '€', 'string', 10, 'ecommerce', 4, 18, 1, 0, ''),
('customer_help_email', '', 'string', 255, 'email_settings', 1, 9, 1, 0, ''),
('customer_help_subj_pfx', '', 'string', 255, 'email_settings', 1, 10, 1, 0, ''),
('custom_fields_mandatory_for_admin', 'off', 'enum', 3, 'register', 3, 21, 1, 0, ''),
('defaultTemplate', 'standard', 'template', 255, '0', 1, 4, 1, 0, ''),
('default_language', 'italian', 'language', 255, '0', 1, 3, 1, 0, ''),
('do_debug', 'off', 'enum', 3, 'debug', 8, 8, 1, 0, ''),
('file_upload_whitelist', 'exe,rar,zip,jpg,gif,png,txt,csv,rtf,xml,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,pdf,xps,mp4,mp3,flv,swf,mov,wav,ogg,flac,wma,wmv', 'string', 65535, 'security', 8, 27, 1, 0, ''),
('google_stat_code', '', 'textarea', 65535, 'google', 8, 28, 1, 0, ''),
('google_stat_in_lms', '0', 'check', 1, 'google', 8, 29, 1, 0, ''),
('hide_empty_category', 'on', 'enum', 3, '0', 4, 6, 1, 0, ''),
('home_page_option', 'my_courses', 'home_page_option', 255, '0', 4, 1, 1, 0, ''),
('hour_request_limit', '48', 'int', 2, 'register', 3, 13, 0, 0, ''),
('hteditor', 'tinymce', 'hteditor', 255, '0', 1, 6, 1, 0, ''),
('htmledit_image_admin', '1', 'check', 255, '0', 8, 1, 1, 0, ''),
('htmledit_image_godadmin', '1', 'check', 255, '0', 8, 0, 1, 0, ''),
('htmledit_image_user', '1', 'check', 255, '0', 8, 2, 1, 0, ''),
('kb_filter_by_user_access', 'on', 'enum', 3, '0', 4, 14, 1, 0, ''),
('kb_show_uncategorized', 'on', 'enum', 3, '0', 4, 13, 1, 0, ''),
('lang_check', 'off', 'enum', 3, 'debug', 8, 7, 1, 0, ''),
('lastfirst_mandatory', 'off', 'enum', 3, 'register', 3, 14, 2, 0, ''),
('ldap_port', '389', 'string', 5, '0', 9, 1, 1, 0, ''),
('ldap_server', '192.168.0.1', 'string', 255, '0', 9, 3, 1, 0, ''),
('ldap_used', 'off', 'enum', 3, '0', 9, 2, 1, 0, ''),
('ldap_user_string', '$user@domain2.domain1', 'string', 255, '0', 9, 4, 1, 0, ''),
('mail_sender', 'sample@localhost.localdomain', 'string', 255, 'email_settings', 1, 8, 0, 0, ''),
('maintenance', 'off', 'enum', 3, 'security', 8, 25, 0, 0, ''),
('maintenance_pw', 'manutenzione', 'string', 16, 'security', 8, 26, 0, 0, ''),
('mandatory_code', 'off', 'enum', 3, 'register', 3, 18, 1, 0, ''),
('max_log_attempt', '0', 'int', 3, '0', 3, 4, 0, 0, ''),
('nl_sendpause', '20', 'int', 3, 'newsletter', 8, 10, 1, 0, ''),
('nl_sendpercycle', '200', 'int', 4, 'newsletter', 8, 9, 1, 0, ''),
('no_answer_in_poll', 'off', 'enum', 3, '0', 4, 11, 1, 0, ''),
('no_answer_in_test', 'off', 'enum', 3, '0', 4, 10, 1, 0, ''),
('on_catalogue_empty', 'on', 'enum', 3, '0', 4, 5, 1, 0, ''),
('on_path_in_mycourses', 'off', 'enum', 3, '0', 4, 3, 1, 0, ''),
('on_usercourse_empty', 'off', 'on_usercourse_empty', 3, '0', 4, 2, 1, 0, ''),
('orgchart_singlenode', 'off', 'enum', 3, 'register', 3, 21, 1, 0, ''),
('owned_by', 'Copyright (c) forma.lms', 'html', 255, '0', 1, 7, 1, 0, ''),
('page_title', 'Forma E-learning', 'string', 255, '0', 1, 1, 1, 0, ''),
('pass_alfanumeric', 'off', 'enum', 3, 'password', 3, 6, 1, 0, ''),
('pass_algorithm', '1', 'password_algorithms', 255, 'password', 3, 6, 1, 0, ''),
('pass_change_first_login', 'on', 'enum', 3, 'password', 3, 8, 1, 0, ''),
('pass_max_time_valid', '0', 'int', 4, 'password', 3, 9, 1, 0, ''),
('pass_min_char', '8', 'int', 2, 'password', 3, 7, 0, 0, ''),
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
('paypal_currency', 'EUR', 'string', 255, 'ecommerce', 4, 17, 1, 0, ''),
('paypal_mail', '', 'string', 255, 'ecommerce', 4, 16, 1, 0, ''),
('paypal_sandbox', 'on', 'enum', 3, 'ecommerce', 4, 19, 1, 0, ''),
('privacy_policy', 'on', 'enum', 3, 'register', 3, 15, 0, 0, ''),
('profile_only_pwd', 'off', 'enum', 3, '0', 3, 1, 1, 0, ''),
('register_deleted_user', 'off', 'enum', 3, '0', 3, 3, 1, 0, ''),
('register_type', 'admin', 'register_type', 10, 'register', 3, 11, 0, 0, ''),
('registration_code_type', '0', 'registration_code_type', 3, 'register', 3, 17, 1, 0, ''),
('request_mandatory_fields_compilation', 'on', 'enum', 3, '0', 3, 2, 1, 0, ''),
('rest_auth_api_key', '', 'string', 255, 'api', 9, 11, 1, 0, ''),
('rest_auth_api_secret', '', 'string', 255, 'api', 9, 13, 1, 0, ''),
('rest_auth_code', '', 'string', 255, 'api', 9, 8, 1, 0, ''),
('rest_auth_lifetime', '60', 'int', 3, 'api', 9, 12, 1, 0, ''),
('rest_auth_method', '1', 'rest_auth_sel_method', 3, 'api', 9, 9, 1, 0, ''),
('rest_auth_update', 'off', 'enum', 3, 'api', 9, 10, 1, 0, ''),
('save_log_attempt', 'no', 'save_log_attempt', 255, '0', 3, 5, 0, 0, ''),
('sco_direct_play', 'on', 'enum', 3, '0', 8, 3, 1, 0, ''),
('sender_event', 'sample@localhost.localdomain', 'string', 255, '0', 1, 5, 1, 0, ''),
('send_cc_for_system_emails', '', 'string', 255, 'email_settings', 1, 11, 1, 0, ''),
('session_ip_control', 'off', 'enum', 3, 'security', 8, 22, 1, 0, ''),
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
('sso_secret', '', 'text', 255, '0', 9, 6, 1, 0, ''),
('sso_token', 'off', 'enum', 3, '0', 9, 5, 1, 0, ''),
('stop_concurrent_user', 'on', 'enum', 3, 'security', 8, 23, 1, 0, ''),
('tablist_mycourses', 'name,code,status', 'tablist_mycourses', 255, '0', 4, 4, 1, 0, ''),
('template_domain', '', 'textarea', 65535, '0', 8, 9, 1, 0, ''),
('templ_use_field', '0', 'id_field', 11, '0', 1, 0, 1, 1, ''),
('title_organigram_chart', 'Forma', 'string', 255, '0', 1, 0, 1, 1, ''),
('tracking', 'off', 'enum', 3, '0', 4, 12, 1, 0, ''),
('ttlSession', '4000', 'int', 5, '0', 8, 6, 1, 0, ''),
('url', 'http://localhost/forma-dev/html/', 'string', 255, '0', 1, 2, 1, 0, ''),
('user_pwd_history_length', '3', 'int', 3, 'password', 3, 10, 1, 0, ''),
('user_quota', '50', 'string', 255, '0', 8, 6, 1, 0, ''),
('use_advanced_form', 'off', 'enum', 3, 'register', 3, 16, 1, 0, ''),
('use_course_label', 'off', 'enum', 3, 'main', 4, 7, 1, 0, ''),
('use_rest_api', 'off', 'enum', 3, 'api', 9, 7, 1, 0, ''),
('use_tag', 'on', 'enum', 3, '0', 4, 8, 1, 0, ''),
('visuItem', '25', 'int', 3, '0', 2, 1, 1, 1, ''),
('visuNewsHomePage', '3', 'int', 5, '0', 1, 0, 1, 1, ''),
('welcome_use_feed', 'on', 'enum', 3, '0', 1, 0, 1, 1, '');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Security Tokens' ;

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
  `sequence` int(3) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`sequence`)
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
  `pass` varchar(255) NOT NULL DEFAULT '',
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
  `pass` varchar(255) NOT NULL DEFAULT '',
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
  `avatar` varchar(255) NOT NULL,
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



--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `core_lang_translation`
--
ALTER TABLE `core_lang_translation`
  ADD CONSTRAINT `core_lang_translation_ibfk_1` FOREIGN KEY (`lang_code`) REFERENCES `core_lang_language` (`lang_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `core_lang_translation_ibfk_2` FOREIGN KEY (`id_text`) REFERENCES `core_lang_text` (`id_text`) ON DELETE CASCADE ON UPDATE CASCADE;



-- task 14732
UPDATE `core_setting` SET `sequence` = '9' WHERE `core_setting`.`param_name` = 'template_domain';

UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'google_stat_in_lms';
UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'google_stat_code';
UPDATE `core_setting` SET `sequence` = '26' WHERE `core_setting`.`param_name` = 'maintenance_pw';
UPDATE `core_setting` SET `sequence` = '27' WHERE `core_setting`.`param_name` = 'file_upload_whitelist';
UPDATE `core_setting` SET `sequence` = '28' WHERE `core_setting`.`param_name` = 'google_stat_code';
UPDATE `core_setting` SET `sequence` = '29' WHERE `core_setting`.`param_name` = 'google_stat_in_lms';
UPDATE `core_setting` SET `pack` = 'google' WHERE `core_setting`.`param_name` = 'google_stat_code';
UPDATE `core_setting` SET `pack` = 'google' WHERE `core_setting`.`param_name` = 'google_stat_in_lms';
UPDATE `core_setting` SET `sequence` = '30' WHERE `core_setting`.`param_name` = 'Clear_Twig_Cache';

UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'paypal_mail';
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'paypal_currency';
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'currency_symbol';
UPDATE `core_setting` SET `regroup` = '4' WHERE `core_setting`.`param_name` = 'paypal_sandbox';
UPDATE `core_setting` SET `pack` = '0' WHERE `core_setting`.`param_name` = 'kb_filter_by_user_access';
UPDATE `core_setting` SET `pack` = '0' WHERE `core_setting`.`param_name` = 'kb_show_uncategorized';
UPDATE `core_setting` SET `sequence` = '14' WHERE `core_setting`.`param_name` = 'paypal_mail';
UPDATE `core_setting` SET `sequence` = '15' WHERE `core_setting`.`param_name` = 'paypal_currency';
UPDATE `core_setting` SET `sequence` = '16' WHERE `core_setting`.`param_name` = 'currency_symbol';
UPDATE `core_setting` SET `sequence` = '17' WHERE `core_setting`.`param_name` = 'paypal_sandbox';

UPDATE `core_setting` SET `pack` = 'ecommerce' WHERE `core_setting`.`param_name` = 'paypal_mail';
UPDATE `core_setting` SET `pack` = 'ecommerce' WHERE `core_setting`.`param_name` = 'paypal_currency';
UPDATE `core_setting` SET `pack` = 'ecommerce' WHERE `core_setting`.`param_name` = 'currency_symbol';
UPDATE `core_setting` SET `pack` = 'ecommerce' WHERE `core_setting`.`param_name` = 'paypal_sandbox';

DELETE FROM `core_lang_text` WHERE `core_lang_text`.`id_text` = (SELECT clt.`id_text` FROM (SELECT * FROM `core_lang_text`) AS clt WHERE clt.`text_key` = "_ASK_FOR_TREE_COURSE_CODE");

UPDATE `core_setting` SET `regroup` = '1' WHERE `core_setting`.`param_name` = 'mail_sender';
UPDATE `core_setting` SET `regroup` = '1' WHERE `core_setting`.`param_name` = 'customer_help_email';
UPDATE `core_setting` SET `regroup` = '1' WHERE `core_setting`.`param_name` = 'customer_help_subj_pfx';
UPDATE `core_setting` SET `regroup` = '1' WHERE `core_setting`.`param_name` = 'send_cc_for_system_emails';

UPDATE `core_setting` SET `pack` = 'email_settings' WHERE `core_setting`.`param_name` = 'mail_sender';
UPDATE `core_setting` SET `pack` = 'email_settings' WHERE `core_setting`.`param_name` = 'customer_help_email';
UPDATE `core_setting` SET `pack` = 'email_settings' WHERE `core_setting`.`param_name` = 'customer_help_subj_pfx';
UPDATE `core_setting` SET `pack` = 'email_settings' WHERE `core_setting`.`param_name` = 'send_cc_for_system_emails';

UPDATE `core_setting` SET `sequence` = '1' WHERE `core_setting`.`param_name` = 'page_title';
UPDATE `core_setting` SET `sequence` = '2' WHERE `core_setting`.`param_name` = 'url';
UPDATE `core_setting` SET `sequence` = '3' WHERE `core_setting`.`param_name` = 'default_language';
UPDATE `core_setting` SET `sequence` = '4' WHERE `core_setting`.`param_name` = 'defaultTemplate';
UPDATE `core_setting` SET `sequence` = '5' WHERE `core_setting`.`param_name` = 'sender_event';
UPDATE `core_setting` SET `sequence` = '6' WHERE `core_setting`.`param_name` = 'hteditor';
UPDATE `core_setting` SET `sequence` = '7' WHERE `core_setting`.`param_name` = 'owned_by';
UPDATE `core_setting` SET `sequence` = '8' WHERE `core_setting`.`param_name` = 'mail_sender';
UPDATE `core_setting` SET `sequence` = '9' WHERE `core_setting`.`param_name` = 'customer_help_email';
UPDATE `core_setting` SET `sequence` = '10' WHERE `core_setting`.`param_name` = 'customer_help_subj_pfx';
UPDATE `core_setting` SET `sequence` = '11' WHERE `core_setting`.`param_name` = 'send_cc_for_system_emails';

UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'ttlSession';


UPDATE `core_setting` SET `regroup` = '8' WHERE `core_setting`.`param_name` = 'Clear_Twig_Cache';
UPDATE `core_setting` SET `sequence` = '27' WHERE `core_setting`.`param_name` = 'Clear_Twig_Cache';
UPDATE `core_setting` SET `pack` = 'twig_cache' WHERE `core_setting`.`param_name` = 'Clear_Twig_Cache';
UPDATE `core_setting` SET `sequence` = '6' WHERE `core_setting`.`param_name` = 'pass_algorithm';
UPDATE `core_setting` SET `pack` = 'register' WHERE `core_setting`.`param_name` = 'orgchart_singlenode';
UPDATE `core_setting` SET `pack` = 'register' WHERE `core_setting`.`param_name` = 'custom_fields_mandatory_for_admin';


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


-- --------------------------------------------------------


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
