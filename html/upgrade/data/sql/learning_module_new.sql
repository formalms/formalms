
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `learning_module_new` (
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

INSERT INTO `learning_module_new` (`idModule`, `module_name`, `default_op`, `default_name`, `token_associated`, `file_name`, `class_name`, `module_info`, `mvc_path`) VALUES
(1, 'course', '', '_MYCOURSES', 'view', '', '', 'all', 'elearning/show'),
(3, 'profile', 'profile', '_PROFILE', 'view', 'class.profile.php', 'Module_Profile', '_user', ''),
(5, 'mygroup', 'group', '_MYGROUP', 'view', 'class.mygroup.php', 'Module_MyGroup', '_user', ''),
(7, 'mycertificate', 'mycertificate', '_MY_CERTIFICATE', 'view', 'class.mycertificate.php', 'Module_MyCertificate', 'all', ''),
(9, 'userevent', 'user_display', '_MYEVENTS', 'view', 'class.userevent.php', 'Module_UserEvent', '_user', ''),
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
(43, 'presence', 'main', '_ATTENDANCE', 'view', 'class.presence.php', 'Module_Presence', '', ''),
(44, 'pcertificate', 'certificate', '_PUBLIC_CERTIFICATE_ADMIN', 'view', 'class.pcertificate.php', 'Module_Pcertificate', 'public_admin', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
