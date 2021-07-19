-include Makefile.local

export XDEBUG_MODE=coverage

MAKEFLAGS += --warn-undefined-variables
SHELL := bash
PATH := $(CURDIR)/vendor/bin:$(PATH)
CURRENT_PATH := $(CURDIR)

.PHONY: help
help:
	@echo 'Available targets'
	@echo '  clean               Removes temporary build artifacts like'
	@echo '  dependencies        Installs composer dependencies'
	@echo '  fix                 Fixes composer.json and code style'
	@echo '  test                Execute all tests'

.PHONY: test
test: clean fix

.PHONY: dependencies
dependencies:
	composer install --no-interaction

.PHONY: fix
fix: fix-code-style fix-composer

.PHONY: fix-code-style
fix-code-style: dependencies
fix-code-style:
	$(CURRENT_PATH)/tools/php-cs-fixer/vendor/bin/php-cs-fixer --config=$(CURRENT_PATH)/tools/php-cs-fixer/.php-cs-fixer.php fix

.PHONY: fix-composer
fix-composer: dependencies
fix-composer:
	composer normalize --no-update-lock
	composer update nothing

.PHONY: clean
clean:
	rm -rf vendor files/cache
