TRUNCATE TABLE `learning_menu`;
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
(13, '', '', 13, 'true');

TRUNCATE TABLE `learning_menu_under`;
INSERT INTO `learning_menu_under` (`idUnder`, `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path`) VALUES
(NULL, 1, 'course', '_COURSE', '', 'view', NULL, 1, '', '', 'alms/course/show'),
(NULL, 1, 'coursepath', '_COURSEPATH', 'pathlist', 'view', NULL, 2, 'class.coursepath.php', 'Module_Coursepath', ''),
(NULL, 1, 'catalogue', '_CATALOGUE', 'catlist', 'view', NULL, 3, 'class.catalogue.php', 'Module_Catalogue', ''),

(NULL, 2, 'location', '_LOCATION', '', 'view', NULL, 1, '', '', 'alms/location/show'),

(NULL, 3, 'preassessment', '_ASSESSMENT', 'assesmentlist', 'view', NULL, 1, 'class.preassessment.php', 'Module_PreAssessment', ''),

(NULL, 4, 'games', '_CONTEST', '', 'view', NULL, 1, '', '', 'alms/games/show'),

(NULL, 5, 'communication', '_COMMUNICATION_MAN', '', 'view', NULL, 1, '', '', 'alms/communication/show'),

(NULL, 6, 'webpages', '_WEBPAGES', 'webpages', 'view', NULL, 1, 'class.webpages.php', 'Module_Webpages', ''),
(NULL, 6, 'news', '_NEWS', 'news', 'view', NULL, 2, 'class.news.php', 'Module_News', ''),
(NULL, 6, 'internal_news', '_NEWS_INTERNAL', 'news', 'view', NULL, 3, 'class.internal_news.php', 'Module_Internal_News', ''),

(NULL, 7, 'certificate', '_CERTIFICATE', 'certificate', 'view', NULL, 1, 'class.certificate.php', 'Module_Certificate', ''),
(NULL, 7, 'meta_certificate', '_META_CERTIFICATE', 'meta_certificate', 'view', NULL, 2, 'class.meta_certificate.php', 'Module_Meta_Certificate', ''),

(NULL, 8, 'reservation', '_EVENTS', 'view_event', 'view', NULL, 1, 'class.reservation.php', 'Module_Reservation', ''),
(NULL, 8, 'reservation', '_CATEGORY', 'view_category', 'view', NULL, 2, 'class.reservation.php', 'Module_Reservation', ''),
(NULL, 8, 'reservation', '_RESERVATION', 'view_registration', 'view', NULL, 3, 'class.reservation.php', 'Module_Reservation', ''),

(NULL, 9, 'amanmenu', '_MAN_MENU', 'mancustom', 'view', NULL, 1, 'class.amanmenu.php', 'Module_AManmenu', ''),
(NULL, 9, 'middlearea', '_MIDDLE_AREA', 'view_area', 'view', NULL, 2, 'class.middlearea.php', 'Module_MiddleArea', ''),
(NULL, 9, 'questcategory', '_QUESTCATEGORY', '', 'view', NULL, 3, '', '', 'alms/questcategory/show'),
(NULL, 9, 'label', '_LABEL', '', 'view', NULL, 4, '', '', 'alms/label/show'),
(NULL, 9, 'timeperiods', '_TIME_PERIODS', '', 'view', NULL, 5, '', '', 'alms/timeperiods/show'),

(NULL, 10, 'report', '_REPORT', 'reportlist', 'view', NULL, 1, 'class.report.php', 'Module_Report', ''),

(NULL, 11, 'kb', '_CONTENT_LIBRARY', '', 'view', NULL, 1, '', '', 'alms/kb/show'),

(NULL, 12, 'enrollrules', '_ENROLLRULES', '', 'view', NULL, 1, '', '', 'alms/enrollrules/show'),

(NULL, 13, 'transaction', '_TRANSACTION', '', 'view', NULL, 1, '', '', 'alms/transaction/show');