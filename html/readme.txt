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
FORMALMS 1.2
Relase date: 2014 May 30
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


