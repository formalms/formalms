/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

-----------------------------------------------------------------------------
FORMALMS 1.1
Relase date: 2014 February 18
-----------------------------------------------------------------------------


Index
-----

1. Requirements

2. Installation procedure

3. Upgrade procedure

3.1 Upgrade from docebo ce 3.x to 4.x

4. Loading a new language

5. Others




------------------------------------------------------------------------------

1. Requirements

Server specs: Linux, Windows, MacOs, Unix, Sun with
- Apache 2.0.x or above
- PHP 5.2.x or 5.3.x 5.4.x (known issues with 5.4)
- Mysql 5.0 or higher with transaction support
- Doesn't matter if safe mode or register global are on or off
- If you need to test on your windows home pc we suggest easyphp or xampp




2. Installation procedure

- Be sure you have your ftp parameters (host, user, password) and database parameters (user, password dbname) available
- If you are on your home pc with your easyphp/xampp create a database trought http://localhost/mysql/ or http://localhost/phpmyadmin/
- Upload all the files in your root directory
- Launch http://www.yoursite.com/install/
- Follow installation instructions

Note: The system will load XML file languages, this operation can take some time in order to complete




3. Upgrade procedure

- Make a full backup of db and files
- Make sure that you have a full backup that you can trust and recover if needed
- Export the language files (if you did any customization), to import them again after the upgrade
- Delete all the files and dir excluding the files/ dir and the config.php file, and upload the new files
- Launch www.yourwebsite.com/upgrade
- Follow instructions



3.1 Upgrade from docebo ce 3.x and docebo ce 4.x  to FormaLMS 1.x

You can directly upgrade your old docebo installations to Forma 1.x
The config.php file is completly changed from the previous release to the new one, in this case you will need to manually compile the new config.php file (you can found the new one inside the folder of the new version)



4. Loading a new language

- Make sure that you have the required xml file
- Go in the administration area
- Go in language import/export
- Select the xml file
- Choose if you want to overwrite previous translation or not
- Click Import


---------------------------------------------------------------------------------------
CHANGELOG
---------------------------------------------------------------------------------------

---------------------------------------------------------------------------------------
FORMALMS 1.1
---------------------------------------------------------------------------------------
498 | Bug | Improved PHP5.4 compatibility
1456 | Bug | Removed empty tabs from middlearea (authoring, biblioteca, calendar)
1517 | Bug | Bug with SNAP scorm lo tracking
1518 | Bug | Italic text shown as normal
1548 | Bug | Inverted column titles in session details table
1736 | Bug | Menu manager: Question Bank "Edit" Option not working
1932 | Bug | Missing grouptype img in advanced user registration
1965 | Bug | Subscription to entire catalog not setting user group
1976 | Bug | Editions don't inherit subscription mode setting
2037 | Bug | Advanced Registration - user non added to selected groups
2084 | Bug | Can't edit default enrollment policy
1101 | Bug | Missing translation key in Privacypolicies
1440 | Bug | Fixed course "Days of validity" feature
971 | Bug | Admins can see all the orgchart nodes during user import from csv (not only the assigned ones)
1139 | Bug | Date not displayed on catalog subscribe to course editions (00/00/0000)
1059 | Bug | Allow edit of scorm objects info in gradebook
1499 | Bug | Missing images in catalog pagination
1737 | Bug | Question Bank - "Select All" option ignores filters on export
1738 | Bug | Back button in report card now takes user to the right page
1323 | Bug | Communications were saved even with non-allowed files attached
2210 | Bug | Template - Login box breaks is social functions are active
2211 | Bug | Catalog - Wrong div class and inline styling for labels view
2242 | Bug | My courses - Elearning Tab - Courses assigned to a curricula were hidden under "All open courses" filter
2209 | Bug | Page footer position error in html page edit
1188 | Bug | Question Bank - Added page autorefresh on question delete
2182 | Bug | Wrong/missing label for course status in infocourse module
1405 | Change Request | Shared objects - Edit/delete actions only for owners
1407 | Change Request | Show teacher name in course list
1408 | Change Request | Added course category permissions for administrator profiles
1428 | Change Request | Close button on organization js alerts
1790 | Change Request | Forum - Threads answers inherit discussion title
1909 | Change Request | Performance and code optimization for user orgchart
1974 | Change Request | "My courses" pointing to elearning/class tab if default page set to catalog
2005 | Change Request | Added orgchart nodes create/edit permission for admins
2045 | Change Request | Updated Htmlpurifier to v. 4.6 (Major Security fixes)
2046 | Change Request | Updated phpmailer addon (v 5.2.7)
2047 | Change Request | Updated tcpdf addon (v. 6.0)
2085 | Change Request | Roles - Added dynamic column in gap analysis
2168 | Change Request | Roles - Added XLS export in gap analysis
1432 | Change Request | Added percentage progress bar in gradebook stats
1406 | Change Request | Added link to test results in gradebook
474 | Change Request | User Profile - better visualization for uploaded files
476 | Change Request | New Copy/Move subscription function for courses
488 | Change Request | Added drag'n'drop ordering for frontend tabs in middlearea configuration
492 | Change Request | Curricula - Added filter and search in curricula tab
485 | Change Request | Forum - Relevant Threads dropdown select changed into checkbox
809 | Change Request | Catalog buttons autorefresh after subscribing
810 | Change Request | Added alt text description for locked courses
1429 | Change Request | Template - Added styles for title and attach box in html pages


---------------------------------------------------------------------------------------
FORMALMS 1.0
---------------------------------------------------------------------------------------

Bug #391: Force IE9 compatibility
Bug #392: Deleted users remain in course subscription count
Bug #393: Javascript Errors on report publishing
Bug #394: Missing or wrong dates in " My Certificates" Area
Bug #395: Report publicadmin con filtro sui corsi (405)
Bug #396: Advanced search in course subscription
Bug #397: Content library tags
Bug #398: Export report in admin welcome page
Bug #399: Broken subscription confirmation link
Bug #400: Error 500 for administrators and publicadmin
Bug #401: Missing mime types
Bug #402: Bugs in course report
Bug #403: Password non salvata in login
Bug #404: Bugs e mod in catalogo corsi
Bug #405: Fix communications admin page
Bug #406: End Course date not working
Bug #407: Installer/Upgrader
Bug #408: Question Bank
Bug #409: Missing subscribe button for curricula in catalog
Bug #412: Text Label fields hidden in usertable dropdown
Bug #413: course subscription - notification event not working
Bug #414: Announcements - email notification sent only to admin
Bug #415: Wrong question timing using randomization in temporized tests
Bug #416: File upload error in communications
Bug #511: fixed IM not working
Bug #516: Upgrader - Missing db inserts upgrading from docebo 3.6
Bug #523: Charset error in report export
Bug #525: Editing existing csv connection
Bug #537: Rebranding - Logo e favicon
Bug #538: Rebranding - Set e controllo versione Forma
Bug #539: Verifiche controllo versione PHP
Bug #803: JSON Error when closing subscribe dialog
Bug #806: Blank page on course stats filtering with editions
Bug #808: Enrollment Rules - user folder assignement bug
Bug #967: wrong xls icon in gradebook
Bug #1010: Code error on course copy (generates problems on IE)
Bug #1033: Updated mime types list
Bug #1083: Course projects - missing download icon for files
Bug #1088: 404 error in chat window
Bug #1106: New styles for install/upgrade wizard
Bug #1143: Announcements - missing title and description in sent email
Bug #1235: Commenti purple da levare
Bug #1253: Communications - error on downloaded files extensions
Change Request #470: Course Copy - single LO selection
Change Request #471: Reservation Management - delete and email single registered users
Change Request #472: Reservation Management - Simplifieduser registration
Change Request #473: Reservation Management - Email notification on user un-subscription
Change Request #475: Custom Fields - Html Label
Change Request #477: Course Catalogue - Vertical category list & various fixes
Change Request #478: Added new maintenance mode feature
Change Request #480: Dynamic dropdown list in gradebook user table
Change Request #481: Attendance list - xls export function
Change Request #483: Reintroduced missing career box module
Change Request #490: New horizontal menu in courses
Change Request #494: New code subscription module (side)
Change Request #495: Scorm stats for admins
Change Request #502: TinyMCE - Updated to ver. 3.5.8
Change Request #503: Code Rebranding
Change Request #504: File System: doceboLms/doceboCore folders renamed
Change Request #505: New Forma Credits
Change Request #507: Removed hidden call to old docebo servers
Change Request #513: Added and fixed many translation keys (IT & EN)
Change Request #518: Updated platform upgrade wizard
Change Request #521: Updated YUI library (2.9)
Change Request #524: Set autocomplete=off for fill-in (TE) questions in tests
Change Request #526: error in event translation
Change Request #527: Message priority (completed missing features)
Change Request #575: Restyling Template
Change Request #805: New paypal procedures
Change Request #1032: Updated file extension whitelist
Change Request #1076: New links to Forma sites in admin dashboard



