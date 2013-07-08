/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */




Index
-----

1. Requirements

2. Installation procedure

3. Upgrade procedure

3.1 Upgrade from 3.x to 4.x

4. Loading a new language

5. Others




------------------------------------------------------------------------------

1. Requirements

Server specs: Linux, Windows, MacOs, Unix, Sun with
- Apache 2.0.x or above
- PHP 5.2.x or above
- Mysql 4.1 or higher with transaction support
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

- Make a full backup db and files
- Make sure that you have a full backup that you can trust and recover if needed
- Overwrite all the old files, do not overwrite the config.php file (or if you prefer delete all the docebo files and dir excluding the files/ dir and the config.php file and upload the new files)
- Launch www.yourwebsite.com/upgrade
- Follow instructions




3.1 Upgrade from 3.x to 4.x

The config.php file is completly changed from the previous release to the new one, in this case you will need to manually compile the new config.php file (you can found the new one inside the folder of the new version)



4. Loading a new language

- Make sure that you have the required xml file
- Go in the administration area
- Go in language import/export
- Select the xml file
- Choose if you want to overwrite previous translation or not
- Click Import




5. Others

More info on installation on manuals:

http://www.docebolms.org
http://www.docebocms.org
http://www.docebokms.org

If you need to be a developer please go on

http://www.docebo.org

