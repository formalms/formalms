/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

-----------------------------------------------------------------------------
forma.lms 3.0.0
Release date: 2021 november
-----------------------------------------------------------------------------


Index
-----

0.   Release Notes
1.   Licence
2.   Requirements
2.1  Platform internal requirements
3.   Installation procedure
4.   Upgrade procedure
4.1  Upgrade from version 2.xx
4.2  Upgrade from version 1.xx
4.3  Upgrade from docebo ce 3.x and 4.x
5.   Loading a new language
6.   Other


------------------------------------------------------------------------------
0    RELEASE NOTES (3.0.0)

This is a new major release  with new features, redesign of the UI , many improvements

For all new features and bugfixes included in this release, please read changelog.txt
Here are some notes from base release.

Attention
From release 2.2.0 the templates system checks the version of the template.
check and update your templates manifest as described in par. 3.0.0


Here are some important notes:

a) PHP support
   Full support for PHP from 7.4.x
   Dropped support for PHP < 7.4
   
b) Template System
   Refactored front-end (users) UI with native responsive system based on bootstrap
   Enhanced administrator templates .
   forma.lms 1.x.x templates are NOT supported 
   forma.lms 2.x.x templates are NOT supported
   While upgrading and running a version >=2.2.x or 3.x forma.lms will check the defined template and
   - if not supported - it will switch to the standard template. 
   A template labeled "standard" (the distributed one in the package) must always exist.
   A compliant template must have a manifest file that declares the version 
   If there is a need for improvements or bug fixes, the minimum supported version of the template supported
   will be changed also in minor release

   As a tip, never change the standard template, if you need customization copy it to a new one and change it
   
c) Privacy and GDPR compliance
   forma.lms is released with a compliant set of privacy features. The default settings comply with the GDPR 
   requirements. You can change these settings to suit your needs.
   Acceptance of privacy policy records who, when and which privacy policy has been acknowledged.
   Go to the privacy section of the administration section to setup privacy messages. 
   By default, forma.lms uses the privacy messages taken from the language translation. 
   Any modification must be made in the privacy settings section. 
   Please note that the default language translation privacy settings may be dropped in future releases.
   Each Organization chart node can have its own privacy policy. A node without a specific policy will used 
   the policy marked as "default"
   
   After upgrading from version 1.xx, all users must re-accept the privacy policy at the first login.

d) Public admin removed
   The "public admin" feature has been dropped since the 2.x.x versions.
   The "admin" feature remains and has been enhanced with new permissions
   
   During the upgrade from 1.xx, users with the "public admin" permissions will be reverted to standard 
   users. However, the courses and Organization chart node assigned to these users are preserved. 
   After the upgrade, you need to upgrade the affected users to the administrator role and assign them an
   administration profile (create a new one if necessary).

e) direct SSO link.
   The SSO direct link from an external site to forma.lms has changed since 2.x.x:
   v1.xx url:  http://yourformalms.domain.com/appLms/index.php
   v2.xx url:  http://yourformalms.domain.com/index.php
   All other parameters (token included) have not changed

f) Assessment
   Feature dropped in 2.x.x version
   
g) Plugin system
   The plugin system has been enhanced in the administration section and its use has been extended 
   into the system.
   Each plugin must have a manifest file to describe its version, forma.lms version and dependencies.
   Many components has been converted to plugins, and distributed with the core system:
   -- all the autorizations module
   -- the base video conferencing systems
   -- some reports
   With next releases, other components will be converted.

h) Back-end administration menu
   DB tables and contents used by "admin menu components" have changed since 2.x.x. 
   During the upgrade any customization of these will be lost
 

---------------------------------------------------------------------------------------------------
1. LICENCE

This software is released with GPL v2.0 license, please refer to the provided file license.txt for 
details.

The hardcoded "Powered by forma.lms CE" credit must NOT be removed, in respect to the work of the 
project and community. 
You are welcome to add your credits to the page footer using the provided configuration option.

---------------------------------------------------------------------------------------------------
2. REQUIREMENTS

Server specs: Linux, Windows, MacOs, Unix, Sun with
- Apache 2.2.x or above, Nginx or IIS on MS WIndows (other web server with php processing can be used)
- PHP  7.4.x
- Mysql 5.7 or higher, mariadb 10.1 or higher
  db server engine must have transaction support (innodb recommended)
- Doesn't matter if safe mode or register global are on or off

If you need to test on your personal computer we suggest a prepackaged web server environment as 
easyphp, wamp/mamp or xampp.

2.1 Platform internal requirements

Minimum template version: 2.2

---------------------------------------------------------------------------------------------------
3. INSTALL PROCEDURE

- Be sure you have your database parameters (host, user, password, dbname) available
- If you want use at runtime FTP upload features, be sure you have your ftp parameters (host, user, 
  password), 
- If you want use at runtime SMTP mailfeatures, be sure you have your smtp parameters (host, port, user, 
  password, security protocol), 

- Before install, create the database on your dbserver. You can use any db admin tool as phpmyadmin
  If you use a prepackaged webserver on your computer, the default db admin link is 
  http://localhost/mysql/ or http://localhost/phpmyadmin/   , check your webserver configuration 
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


---------------------------------------------------------------------------------------------------
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
  with the standard translations. Thee language import procedure has been enanched and can now not overwrite the customization
- At the end of upgrade, go to the "Administration panel/Language" check and import if needed your own language files; 
  you can import only modified labels
- To secure you installation, at the end of upgrade, remove or rename the folders
  install and upgrade. 

  The procedure may change for you the config.php file (if writable), or requires you to download it
  and upload to the web root folder (if not writable)

4.1 Upgrade from version 2.xx

During upgrade from forma.lms 2.x  to forma.lms 2.2 and later, pay attention to
a) Template
   Template 2.0 and 2.1 are not compatible with 2.2 and newer
   During the upgrade the site template is updated to "standard".
   Minimum template version required (see release notes)
d) Customscripts
   All customization made through customscripts/ must be reviewed and ported to the current 
   version. forma.lms does not check base version file with your customized version in customscripts 
   folder. There is no version check support for customscripts files

4.2 Upgrade from version 1.xx

During upgrade from forma.lms 1.x  to forma.lms 2.xx, pay attention to
a) Database
   after upgrade the DB are not full compatible with forma.lms 1.xx, and there is no path to go back.
   Made a backup before upgrade 
b) Template
   Template 1.xx are not compatible with 2.xx 
   During the upgrade the site template is updated to "standard"
c) Dropped feature
d) Customscripts
   All customization made through customscripts/ must be reviewed and ported to the 2.0 current 
   version. forma.lms does not check base version file with your customized version in customscripts 
   folder. There is no version check support for customscripts files

e) The update detects the use of the mysql / mysqli driver and changes the configuration appropriately

At the end of the upgrade process, check all the system configuration settings to validate your 
needs. The update procedure tries to keep the settings, but new options have been added and others 
have been removed

Please, double check above release notes before upgrading

 
4.3 Upgrade from docebo ce 3.x, docebo ce 4.x 

You can directly upgrade your old docebo (either series 3.6.x and 4.x) installations to forma.lms 2.x
The config.php file is completely changed from D36 and with more config options since D4.x
The upgrade procedure change for you the config.php and write (if writable), or require you to 
download it and upload to the web root folder.
Coming from D36 review the config.php and add your own settings, if needed
  

5. LOADING A NEW LANGUAGE

- Make sure that you have the required xml file on your computer. 
  get it from installation tarball . 
  The import procedure for the 3.x version can read the xml file directly from the web server folders
- Go in the administration area
- Go in language import/export
- Select the xml file
- Choose if you want to overwrite previous translation or not
- Click Import

6. OTHER
