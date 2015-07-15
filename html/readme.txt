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
FORMALMS 1.4.1
Relase date: 2015 June 25
-----------------------------------------------------------------------------


Index
-----

0.   New in these release
0.2  Discontinued and deprecated features
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

a) Google login (social login):
   Google will discontinued openid login feature upon 2015 april 20, allowing only oauth2 protocol
   for authentication. With this release, forma.lms switch google authentication to oauth2.
   To use google oauth2 you must register at google an application and  generate an application ID and a secret key
   thoe value must be enteter in the configuration screen to enable google authentication.

   FOr those that have google authentication enabled and perfom forma.lms upgrade before 20 april 2015,
   for backward compatibility, the upgrade enable in config.php (for the transition period) the old openid protocol
   After the switchoff date you must disable openid in your config.php file

   The openid protocol are "DEPRECATED" from this release and will be DISCONTINUED

b) Plugin manager: the plugin feature still remain in "experimental" status
c) Custom scripts overlay: the feature is fully supported

0.2 Discontinued and deprecated features

In these release we put in "DEPRECATED" status :
a) openid protocol ( for google login)
b) php 5.2  support

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
  * the folder customscripts/
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
The upgrade procedure change for you the config.php and write (if writeble), or require you to download it
and upload to the web root folder.
Coming from D36 review the config.php and add your own settings, if needed


5. LOADING A NEW LANGUAGE

- Make sure that you have the required xml file on your PC (get it from installation tarball)
- Go in the administration area
- Go in language import/export
- Select the xml file
- Choose if you want to overwrite previous translation or not
- Click Import

6. OTHER
