-include Makefile.local

export XDEBUG_MODE=coverage

MAKEFLAGS += --warn-undefined-variables
SHELL := bash
CURRENT_PATH := $(CURDIR)

.PHONY: help
help:
	@echo 'Available targets'
	@echo '  clean               Removes temporary build artifacts like'
	@echo '  dependencies        Installs composer dependencies'
	@echo '  fix                 Fixes composer.json and code style'
	@echo '  test                Execute all tests'
	@echo '  fix-code-style      Fix code style'
	@echo '  export-changelog    Export changelog'

.PHONY: test
test: clean fix

.PHONY: dependencies
dependencies:
	chmod +x composer.phar
	chmod +x composer-normalize.phar
	php composer.phar --working-dir=$(CURRENT_PATH)/html install --no-interaction
	php composer.phar --working-dir=$(CURRENT_PATH)/tools/php-cs-fixer install --no-interaction

.PHONY: fix
fix: fix-code-style fix-composer

.PHONY: fix-code-style
fix-code-style: dependencies
fix-code-style:
	$(CURRENT_PATH)/tools/php-cs-fixer/vendor/bin/php-cs-fixer --config=$(CURRENT_PATH)/tools/php-cs-fixer/.php-cs-fixer.php fix

.PHONY: fix-composer
fix-composer: dependencies
fix-composer:
	php composer-normalize.phar --no-update-lock $(CURRENT_PATH)/html/composer.json
	php composer.phar --working-dir=$(CURRENT_PATH)/html update nothing

.PHONY: clean
clean:
	rm -rf $(CURRENT_PATH)/html/vendor $(CURRENT_PATH)/html/files/cache $(CURRENT_PATH)/tools/php-cs-fixer/vendor

.PHONY: export-changelog
export-changelog:
	php $(CURRENT_PATH)/tools/php-changelog/changelog-upgrade.php