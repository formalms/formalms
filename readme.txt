/*****************
* XAMPP User
* you are required to enable php_openssl.dll in your php settings:
* 1) check if you have php_openssl.dll in \<xampp install dir>\php\ext\
* 2) in php.ini, add the following line under Windows Extensions section
*		 extension=php_openssl.dll
***************************/

-) To setup for the first time the project for development:

    copy and rename /test/behat/behat_cofnig.yml.dist and change 'base_url' to your needs, then:

    php composer.phar install

-) To update the project libraries during development (after an update):

    bin/phing project:setup

-) To execute behat test suites:

    bin/behat --config test/behat/behat.yml

-) To execute phpunit test suites:

    bin/phpunit --stderr --verbose -c test/phpunit/phpunit.xml

-) To build the dist package run:

    bin/phing project:build


