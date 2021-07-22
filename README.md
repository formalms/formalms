# INSTALLATION

#### XAMPP User
* you are required to enable php_openssl.dll in your php settings:
  1) check if you have php_openssl.dll in \<xampp install dir>\php\ext\
  2) in php.ini, add the following line under Windows Extensions section: `extension=php_openssl.dll`

#### To setup the first time the project for development:

copy and rename `/test/behat/behat_config.yml.dist` and change `base_url` to your needs, then:

    # --ignore-platform-reqs use this when you don't have php >= 5.5 (tests will not run)
    cd html && php ../composer.phar install [--ignore-platform-reqs]

#### To update the project libraries during development (after an update):

    bin/phing project:setup

#### To execute behat test suites:

    bin/behat --config test/behat/behat.yml

#### To execute phpunit test suites:

    bin/phpunit --stderr --verbose -c test/phpunit/phpunit.xml

#### To build the dist package run:

    bin/phing project:build


# EVENTS SYSTEM
#### CORE
* ConfigGetRegroupUnitsEvent
  * Dispatched before the admin configuration menu is displayed
  * Event object contains the array of the names of the groups. Useful to modify the order or add elements.
* OnlineUserEvent
  * WIP
* UsersManagementEditMultipleEvent
  * TODO - Plugin e-commerce
* UsersManagementShowDetailsEvent
  * TODO - Plugin e-commerce
* UsersManagementShowEvent
  * TODO - Plugin e-commerce  

#### CORE - Filesystem
* CopyEvent
  * Dispatched when a file copy operation is requested.
  * Event object contains src and dest paths. It must return true or false if operation succeeded or not.
* DownloadEvent
  * Dispatched when a file download operation is requested.
  * Event object contains info about requested file. Useful to modify the path returned to download.
* UnlinkEvent  
  * Dispatched when a file remove operation is requested.
  * Event object contains path of the file. It must return true or false if operation succeeded or not.
* UploadEvent
  * Dispatched when a file upload operation is requested.
  * Event object contains src and dest paths. It must return true or false if operation succeeded or not.

#### CORE - User
* RegisterUserEvent
  * WIP

#### API
* ApiUserRegistrationEvent
  * Dispatched when a new user is registered by Api
  * Event object contains the idst of the new user

#### LMS  
* UserListEvent
  * Dispatched in user export of event
  * Used from UserDataExport Plugin
  * Event object contains Event id and Lang.
* TestCompletedEvent  
  * Dispatched when a test status is completed
  * Used from Test360 plugin to send message to user.
  * Event object contains object_test, user Id and ACL manager instance.
* TestUpdateModalityEvent
  * Dispatched when UpdateModality of test il called.
  * Used from Test360 Plugin to save more configuration showed.
  * Event object contains POST variables passed in action.
* TestCousereportEvent
  * Dispatched when cousereport overview is opened
  * Used from Test360 Plugin to override chartlink endpoint
  * Event object contains an Learning_Test object.
* TestConfigurationTabsRenderEvent
  * Dispatched when the edit page of a Test object is opened.
  * Event object contains an array of the tabs rendered (key is the name of the tab and value is the html). Useful to hide or add tabs.
* TestGetTypesEvent
  * Dispatched when the list of the Test types are requested. 
  * Event object contains an array of the Test types. Useful when a new kind of Test LO is added to the platform.
* TestConfigurationMethodOfUseRenderEvent
  * Dispatched when Mode of use of test is Showed.
  * Used from Test360 to add more configurations for test.
  * Event object contains Learning_Test object and Lang.
* TestCreateEvent
  * Dispatched when a new Learning_Test object is created.
  * Event object contains the brand new Learning_Test object.
* TestUpdateEvent
  * Dispatched when a Learning_Test object is updated.
  * Event object contains the brand new Learning_Test object.
* UserProfileShowEvent
  * Dispatched when the user profile is showed up.
  * Event object contains the LmsUserProfile instance. Useful to modify some user's profile property before the rendering.
* UserSelectorBeforeRenderEvent
  * Dispatched before the user-selector widget is rendered.
  * Event object contains the columns and field values. useful to manipulate them before render.
* UserSelectorRenderJSScriptEvent
  * Dispatched before the user-selector widget is rendered.
  * Event object can contains JS scripts to prepend to the default one. Useful to introduce custom logic into the user-selector widget.

