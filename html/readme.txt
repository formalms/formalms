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
FORMALMS 1.3
Relase date: 2014 November 10
-----------------------------------------------------------------------------


Index
-----

0.   New in these release
1.   Licence
2.   Requirements
3.   Installation procedure
4.   Upgrade procedure
4.1  Upgrade from docebo ce 3.x to FormaLMS 1.x
5.   Loading a new language
6.   Other


------------------------------------------------------------------------------

0. New in these release

For all bugfix and new features included in this release, please read changelog.txt
Here are some notes:

In this release we introduced, in a "experimental status", the following new features:

a) Plugin manager:
   you can build your own plugins and automatically execute it.
   Plugins will be official distributed or provided by third parties.
b) BigBlueButton and Teleskill conference as plugin
   we release BBB and Teleskill as plugin . The plugin version is used if plugin manager is enabled
   otherwise the standard module version is used. When plugin will be stable, module version will be
   deprecated and dropped
c) Alternate template engine TWIG:
   you can build views with the TWIG engine superseeding standard views in custom engine (php).
d) Custom scripts overlay:
   you can now build your modified version of some files on a "custom scripts" folders structure.
   When a custom script is found, the standard execution will be overlayed by the "custom" version.
e) PHP 5.5 and 5.6:
   forma.lms can now be installed with php 5.5 and 5.6 version,
   NO tests were made with these versions of php, so please provide feedback on any issue you may find.

The new features in experimental status can be used for test and development works: they are provided
"as is" in a "beta stage" and can be used to test and prepare your installation.
These features may be reworked or modified in "core execution" to meet tests results and usage feedbacks,
to be stabilized in the next releases.

All these new features are disabled by defaults. the features must be enebled in the config file
if you want use it

0.2 Discontinued and deprecated features

In these release we put in "DEPRECATED" status :
a) DimDim support
b) docebo405ce template
c) all "CMS" components

in a next release, without any furher advice, deprecated features will be discontinued and dropped
from the main stream

------------------------------------------------------------------------------

1. LICENCE

This software is released with GPL v2.0 license, please refer to the provided file license.txt for details.
You are welcome to add your credits to the page footer using the provided configuration option.

Please do not remove the hardcoded "Powered by forma.lms CE" credit
in respect to the work of the project partners and community


2. REQUIREMENTS

Server specs: Linux, Windows, MacOs, Unix, Sun with
- Apache 2.0.x or above
- PHP 5.2.x or 5.3.x 5.4.x ( 5.5.x and 5.6.x in beta status)
- Mysql 5.0 or higher with transaction support
- Doesn't matter if safe mode or register global are on or off
- If you need to test on your windows home pc we suggest easyphp, wamp or xampp



3. INSTALL PROCEDURE

- Be sure you have your database parameters (host, user, password, dbname) available
- Be sure you have your ftp parameters (host, user, password), if you want use FTP upload
- Before install, create the database on your dbserver (you can use a db admin tool as phpmyadmin)
- If you are on your home pc with your easyphp/wamp/xampp create a database trought
  http://localhost/mysql/ or http://localhost/phpmyadmin/
- Upload all the files in your root directory or a subfolder
- Launch http://www.yoursite.com/install/
- Follow installation instructions

Note:
At the end step of install procedure, the system will load XML file languages,
Depending on number of languagse chosen, this operation can take some time in order to complete


4. UPGRADE PROCEDURE

- Make a full backup of db and files
- Make sure that you have a full backup that you can trust and recover if needed
- Export the language files (if you did any customization), to import them again after the upgrade
- Delete all the files and dir excluding
  * the folder files/
  * your own template in templates/<yourowntemplate> (if you have any)
  * the config.php file in root folder,
- Upload the new files
- Launch www.yourwebsite.com/upgrade
- Follow instructions.
  The procedure may change for you the config.php file (if writeble, or require you to download it
  and upload to the web root folder)

Note:
At the end step of install procedure, the system will load XML file languages for all languages
you loaded in the system.
Depending on number of languagse, this operation can take some time in order to complete

4.1 Upgrade from docebo ce 3.x and docebo ce 4.x  to FormaLMS 1.x

You can directly upgrade your old docebo (either series 3.6.x and 4.x) installations to forma.lms 1.x
The config.php file is completly changed from D36 and with more config options since D4.x
The upgrade procedure change for you the config.php and write (if writeble, or require you to download it
and upload to the web root folder).
Coming from D36 review the config.php and add your own settings, if needed


5. LOADING A NEW LANGUAGE

- Make sure that you have the required xml file on your PC (get it from installation tarball)
- Go in the administration area
- Go in language import/export
- Select the xml file
- Choose if you want to overwrite previous translation or not
- Click Import

6. OTHER
