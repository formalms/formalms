/* ======================================================================== \
|   forma.lms- The E-Learning Suite                                         |
|                                                                           |
|   Copyright (c) 2013 (forma.lms)                                          |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

-----------------------------------------------------------------------------
forma.lms 2.0
Release date: 2018 June 19
-----------------------------------------------------------------------------


Index
-----

0.   Release Notes
1.   Licence
2.   Requirements
3.   Installation procedure
4.   Upgrade procedure
4.1  Upgrade from version 1.xx
4.2  Upgrade from docebo ce 3.x and 4.x
5.   Loading a new language
6.   Other


------------------------------------------------------------------------------

0   RELEASE NOTES (2.0)

This is a new major release  with new features, redesign of the UI , many improvements

For a list of all new features included in this release, please read changelog.txt

Here are important notes:

a) PHP support
   Full support for PHP from 5.4.x to 7.0.x 
   Know issues for PHP 7.1.x and 7.2.x  will be addressed in next releases
   Dropped support for PHP < 5.4
   
b) Template System
   Refactored front-end (users) UI with native responsive system based on bootstrap,
   Enhanced administrator templates .
   forma.lms 1.x.x  templates are NOT supported 
   During upgrade and running a 2.x version forma.lms check the template defined and if not
   supported  switch to the standard template. A template labeled “standard” must always be exists.
   A compliant 2.0 template must have a manifest file declaring the supported version 
   (for now 2.0)
   
   As a suggestion, never change the standard template, if you need customisation, copy in a new one 
   and change it
   
c) Privacy and GDPR compliance
   forma.lms is released with a compliant set of privacy features. The default settings meet GDPR 
   requirements. You can change these settings as your requirements.
   Acceptance of privacy policy records who, when and witch privacy policy has been acknowledged.
   Go to the privacy section of the administration section to set privacy messages. For default
   forma.lms use    privacy messages from language translation, but any modifications must be made 
   in the privacy settings section. 
   Note that privacy language translation defaults might be dropped in future releases.
   Each orgchart node can have its own privacy policy, Node without policy will use the policy 
   marked as "default"
   
   After upgrade from 1.xx release  all users must be re-accept privacy policy at first login.

d) Public admin removed
   The "public admin" feature has been dropped.
   The "admin" feature remains and has been enhanced with new permissions
   
   During upgrade from 1.xx, users with "public admin" permissions will be reverted to standard 
   users, however courses and orgchart node assigned to such users are retained. 
   After upgrade, you must update interested users to the admin role and assign them an 
   administration profile (create new one if needed).

e) direct SSO link.
   The SSO direct link from external site to forma.lms is changed .   
   v1.xx url:  http://yourformalms.domain.com/appLms/index.php
   v2.xx url:  http://yourformalms.domain.com/index.php
   All other parameters (token included) are non changed

f) Assessment
   Feature dropped
   
g) Plugin system
   The plugin system has been enhanced in the administration and extended use into the system.
   Each plugin must have a manifest file to describe its version, forma.lms version and 
   dependencies.
   Many components has been converted in plugin, and distributed with the core system:
   -- all login module
   -- base video conferencing
   -- some reports
   With next releases, other components will be converted.

h) Back-end administration menu
   DB tables and contents used by "admin menu components" are changed. During upgrade any 
   customization of these will be lost
 

------------------------------------------------------------------------------

1. LICENCE

This software is released with GPL v2.0 license, please refer to the provided file license.txt for 
details.

The hardcoded "Powered by forma.lms CE" credit must NOT be removed, in respect to the work of the 
project and community. 
You are welcome to add your credits to the page footer using the provided configuration option.


2. REQUIREMENTS

Server specs: Linux, Windows, MacOs, Unix, Sun with
- Apache 2.2.x or above  (on Windows also IIS can be used)
- PHP  5.4.x  5.5.x 5.6.x  7.0.x
- Mysql 5.0 or higher, mariadb  5.5 or higher
  db server engine must have transaction support (innodb recommended)
- Doesn't matter if safe mode or register global are on or off

If you need to test on your personal computer we suggest a prepackaged web server environment as 
easyphp, wamp/mamp or xampp.


3. INSTALL PROCEDURE

- Be sure you have your database parameters (host, user, password, dbname) available
- If you want use at runtime FTP upload features, be sure you have your ftp parameters (host, user, 
  password), 
- Before install, create the database on your dbserver. You can use any db admin tool as phpmyadmin
  If you use a prepackaged webserver on your PC , the default db admin link is 
  http://localhost/mysql/ or http://localhost/phpmyadmin/
  If the db user have permissions, the installer can create database for you
  The database must be defined with UTF8 encoding
- Upload all the files in your root directory or subfolder
  - forma.lms web is protected with apache .htaccess files from unauthorized use. Often dot-files
    are hidden from view. Check your environment and be sure that also these files are uploaded
- Launch http://www.yoursite.com/install/
- Follow installation instructions
- To secure you installation, at the end of install, remove or rename the folders install and 
  upgrade


Note:
At the end step of install procedure, the system will load XML file languages.
Depending on number of language chosen, this operation can take some time in order to complete


4. UPGRADE PROCEDURE

- Make a full backup of db and files
- Make sure that you have a full backup that you can trust and recover if needed
- Export the language files (if you did any customization), to import them again after the upgrade
- Delete all files and directories excluding
  * the folder files/  and all subfolders and files
  * the folder customscripts/
  * your own templates in templates/<yourowntemplate> (if you have any)
  * the config.php file in root folder
- Upload the new files
  - The upgrade procedure needs resources under install folder, so make sure to upload also this one
  - forma.lms web is protected with apache .htaccess files from unauthorized use. Often dot-files
    are hidden from view. Check your environment and be sure that also these files are uploaded
- Launch http://www.yourwebsite.com/upgrade
- Follow upgrade instructions.
- As final step of the upgrade, the procedure imports and updates all languages previously defined
  with the standard translations.
- At the end of upgrade, go to the "Administration panel/Language" to import your own language files
- To secure you installation, at the end of upgrade, remove or rename the folders install and 
  upgrade. 

  The procedure may change for you the config.php file (if writable, or require you to download it
  and upload to the web root folder, if not)

4.2 Upgrade from version 1.xx

During upgrade from forma.lms 1.x  to forma.lms 2.xx, pay attention to
a) Database
   after upgrade the DB are not full compatible with forma.lms 1.xx, and there is no path to go back.
   Made a backup before upgrade 
b) Template
   Template 1.xx are not compatible with 2.xx 
c) Dropped feature
d) Customscripts
   All customization made through customscripts/ must be reviewed and ported to the 2.0 current 
   version. forma.lms does not check base version file with your customized version in customscripts 
   folder. There is no version check support for customscripts files

At the end of the upgrade process, check all the system configuration settings to validate your 
needs. The update procedure tries to keep the settings, but new options have been added and others 
have been removed

Please, double check above release notes before upgrading

 
4.2 Upgrade from docebo ce 3.x, docebo ce 4.x 

You can directly upgrade your old docebo (either series 3.6.x and 4.x) installations to forma.lms 2.x
The config.php file is completely changed from D36 and with more config options since D4.x
The upgrade procedure change for you the config.php and write (if writable), or require you to 
download it and upload to the web root folder.
Coming from D36 review the config.php and add your own settings, if needed
  

5. LOADING A NEW LANGUAGE

- Make sure that you have the required xml file on your PC (get it from installation tarball)
- Go in the administration area
- Go in language import/export
- Select the xml file
- Choose if you want to overwrite previous translation or not
- Click Import

6. OTHER
