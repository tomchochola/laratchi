# Default shell
SHELL := /bin/bash

# Variables
MAKE_PHP_8_2_BIN ?= php8.2
MAKE_COMPOSER_2_BIN ?= /usr/local/bin/composer2

MAKE_PHP ?= ${MAKE_PHP_8_2_BIN}
MAKE_COMPOSER ?= ${MAKE_PHP} ${MAKE_COMPOSER_2_BIN}

# Default goal
.DEFAULT_GOAL := assert-never

# Goals
.PHONY: check
check: stan lint audit

.PHONY: audit
audit: vendor tools
	${MAKE_COMPOSER} audit

.PHONY: stan
stan: vendor tools
	${MAKE_PHP} tools/phpstan/vendor/bin/phpstan analyse

.PHONY: lint
lint: vendor tools
	${MAKE_COMPOSER} validate --strict
	"tools/prettier-lint/node_modules/.bin/prettier" -c .
	${MAKE_PHP} tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run --diff

.PHONY: fix
fix: vendor tools
	"tools/prettier-fix/node_modules/.bin/prettier" -w .
	${MAKE_PHP} tools/php-cs-fixer/vendor/bin/php-cs-fixer fix

.PHONY: composer
composer:
	${MAKE_COMPOSER} install

.PHONY: composer-no-dev
composer-no-dev:
	${MAKE_COMPOSER} install --no-dev -a

.PHONY: clean-composer
clean-composer:
	git clean -xfd vendor composer.lock

.PHONY: update-composer
update-composer: clean-composer
	${MAKE_COMPOSER} update

.PHONY: clean-tools
clean-tools:
	git clean -xfd tools

.PHONY: update-tools
update-tools: clean-tools tools

.PHONY: update
update: update-tools update-composer

.PHONY: clean
clean: clean-tools clean-composer

# Aliases
.PHONY: ci
ci: check

.PHONY: update-full
update-full: update

# Dependencies
tools: tools/prettier-lint/node_modules/.bin/prettier tools/prettier-fix/node_modules/.bin/prettier tools/phpstan/vendor/bin/phpstan tools/php-cs-fixer/vendor/bin/php-cs-fixer

tools/prettier-lint/node_modules/.bin/prettier:
	npm --prefix=tools/prettier-lint update

tools/prettier-fix/node_modules/.bin/prettier:
	npm --prefix=tools/prettier-fix update

vendor:
	${MAKE_COMPOSER} update

tools/phpstan/vendor/bin/phpstan:
	${MAKE_COMPOSER} --working-dir=tools/phpstan update

tools/php-cs-fixer/vendor/bin/php-cs-fixer:
	${MAKE_COMPOSER} --working-dir=tools/php-cs-fixer update
