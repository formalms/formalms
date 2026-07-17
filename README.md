# INSTALLATION

## System Requirements

### PHP Requirements
- PHP Version: 8.0.0 - 8.1.x
- Required PHP Extensions:
  - OpenSSL
  - PDO
  - MySQL/MariaDB
  - GD
  - cURL
  - XML
  - Zip

### Database Requirements
- MySQL Version: 5.7 - 8.x
- MariaDB Version: 10.0 - 11.0

### Web Server
- Apache (recommended) with mod_rewrite enabled
- Nginx (with proper rewrite rules)

## Installation Steps

### 1. Server Setup
- Ensure your web server is properly configured
- Make sure PHP and required extensions are installed
- Create a database for FormaLMS

### 2. Application Setup
- Download and extract the FormaLMS package to your web server directory
- Set proper permissions:
  - Make sure the web server has write permissions to:
    - `/html/files/` directory and subdirectories
    - `/html/config.php` file

### 3. Web-based Installation
- Navigate to your FormaLMS URL in a web browser
- Follow the installation wizard which will guide you through:
  - Language selection
  - System requirements check
  - Database configuration
  - Administrator account setup
  - SMTP configuration (optional)

### 4. Post-Installation
- Configure cron jobs if needed for scheduled tasks
- Review and adjust system settings in the admin panel

## Development Setup

#### To setup the first time the project for development:

Copy and rename `/test/behat/behat_config.yml.dist` and change `base_url` to your needs, then:

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


# MAKE COMMANDS
FormaLMS provides several make commands to help with development and maintenance tasks. These commands can be executed from the project root directory using `make <command>`.

## Available Commands

### `make help`
Displays a list of all available make commands with brief descriptions.

### `make clean`
Removes temporary build artifacts, including:
- `/html/vendor` directory
- `/html/files/cache` directory
- `/tools/php-cs-fixer/vendor` directory

### `make dependencies`
Installs composer dependencies:
- Sets executable permissions for composer.phar and composer-normalize.phar
- Installs dependencies in the `/html` directory
- Installs dependencies for PHP CS Fixer in the `/tools/php-cs-fixer` directory

### `make fix`
Combines `fix-code-style` and `fix-composer` commands to:
- Fix code style issues
- Normalize composer.json file

### `make fix-code-style`
Fixes code style issues using PHP CS Fixer with the configuration from `/tools/php-cs-fixer/.php-cs-fixer.php`.

### `make fix-composer`
Normalizes the composer.json file and updates dependencies:
- Normalizes `/html/composer.json` without updating the lock file
- Updates composer dependencies without making changes

### `make test`
Executes all tests after cleaning build artifacts and fixing code style issues.

### `make export-changelog`
Exports the changelog using the PHP script at `/tools/php-changelog/changelog-upgrade.php`.

### `make export-events`
Extracts and displays all `Events::trigger` calls from the codebase using the PHP script at `/tools/php-events-extractor/extract-events.php`.

### `make export-events-readme`
Exports all events to the `EVENTS.md` file using the PHP script at `/tools/php-events-extractor/extract-events.php`.



# DOCTRINE MIGRATIONS
### Command is available from the project root folder `html`

#### `php bin/doctrine-migrations`

* Available commands:
  * `completion`                        Dump the shell completion script
  * `help`                              Display help for a command
  * `list`                              List commands
* migrations
  * `migrations:current`                [current] Outputs the current version
  * `migrations:diff`                   [diff] Generate a migration by comparing your current database to your mapping information.
  * `migrations:dump-schema`            [dump-schema] Dump the schema for your database to a migration.
  * `migrations:execute`                [execute] Execute one or more migration versions up or down manually.
  * `migrations:generate`               [generate] Generate a blank migration class.
  * `migrations:latest`                 [latest] Outputs the latest version
  * `migrations:list`                   [list-migrations] Display a list of all available migrations and their status.
  * `migrations:migrate`                [migrate] Execute a migration to a specified version or the latest available version.
  * `migrations:rollup`                 [rollup] Rollup migrations by deleting all tracked versions and insert the one version that exists.
  * `migrations:status`                 [status] View the status of a set of migrations.
  * `migrations:sync-metadata-storage`  [sync-metadata-storage] Ensures that the metadata storage is at the latest version.
  * `migrations:up-to-date`             [up-to-date] Tells you if your schema is up-to-date.
  * `migrations:version`                [version] Manually add and delete migration versions from the version table.




# UPGRADE
* in the up method of the migration before or after the queries insert
* `\Events::trigger('platform.upgrade', [_upgradeclass_ => formatUpgradeClass(__CLASS__, "pre")]);`
* `\Events::trigger('platform.upgrade', [_upgradeclass_ => formatUpgradeClass(__CLASS__, "post")]);`
* To set params for the event
* `\Events::trigger('platform.upgrade', [_upgradeclass_ => formatUpgradeClass(__CLASS__, "pre"), "params" =>["arg1"=> "val1",...."argN" => "valN"]]);`

# EVENTS SYSTEM
For a complete list of all available events, see [EVENTS.md](EVENTS.md).


# BRANCHING RULES
FormaLMS follows specific branching rules for version control. For detailed information about the Git workflow, branching rules, and command examples, please refer to the [GIT-WORKFLOW.md](GIT-WORKFLOW.md) file.