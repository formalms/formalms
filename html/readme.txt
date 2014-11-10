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

0. New in these release

1. Requirements

2. Installation procedure

3. Upgrade procedure

3.1 Upgrade from docebo ce 3.x to 4.x

4. Loading a new language

5. Others


------------------------------------------------------------------------------

0. New in these release

For all bugfix and new feature included in this release, please read changelog.txt
Here some notes

In this release we have introduced, in a "experimental status", the following new features:
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

All these new features are disabled by defaultsm the features must be enebled in the config file
if you want use it

0.2 Discontinued and deprecated features

In these release we put in "DEPRECATED" status :
a) DimDim support
b) docebo405ce template
c) all "CMS" components

in a next release, without any furher advice, deprecated features will be discontinued and dropped
from the main stream


------------------------------------------------------------------------------

1. Requirements

Server specs: Linux, Windows, MacOs, Unix, Sun with
- Apache 2.0.x or above
- PHP 5.2.x or 5.3.x 5.4.x
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


