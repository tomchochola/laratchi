# Default shell
SHELL := /bin/bash

# Variables
MAKE_PHP_8_1_BIN ?= php8.1
MAKE_COMPOSER_2_BIN ?= /usr/local/bin/composer2

MAKE_PHP ?= ${MAKE_PHP_8_1_BIN}
MAKE_COMPOSER ?= ${MAKE_PHP} ${MAKE_COMPOSER_2_BIN} -n

# Default goal
.DEFAULT_GOAL := assert-never

# Goals
.PHONY: check
check: audit lint

.PHONY: audit
audit: vendor tools
	${MAKE_COMPOSER} audit

.PHONY: lint
lint: vendor tools
	tools/prettier-lint/node_modules/.bin/prettier -c .
	${MAKE_COMPOSER} validate --strict
	${MAKE_PHP} tools/phpstan/vendor/bin/phpstan analyse --no-progress -n
	${MAKE_PHP} tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run --diff -n

.PHONY: fix
fix: vendor tools
	tools/prettier-fix/node_modules/.bin/prettier -w .
	${MAKE_PHP} tools/php-cs-fixer/vendor/bin/php-cs-fixer fix -n

.PHONY: clean-composer
clean-composer:
	git clean -xfd vendor composer.lock

.PHONY: update-composer
update-composer: clean-composer
	${MAKE_COMPOSER} update -o --no-progress

.PHONY: clean-tools
clean-tools:
	git clean -xfd tools

.PHONY: update-tools
update-tools: clean-tools tools

.PHONY: clean-npm
clean-npm:
	git clean -xfd package-lock.json node_modules

.PHONY: update-full
update-full: update-tools update-composer

.PHONY: clean
clean: clean-tools clean-composer clean-npm

# Dependencies
tools: tools/prettier-lint/node_modules/.bin/prettier tools/prettier-fix/node_modules/.bin/prettier tools/phpstan/vendor/bin/phpstan tools/php-cs-fixer/vendor/bin/php-cs-fixer

tools/prettier-lint/node_modules/.bin/prettier:
	npm --prefix=tools/prettier-lint update --no-progress

tools/prettier-fix/node_modules/.bin/prettier:
	npm --prefix=tools/prettier-fix update --no-progress

vendor:
	${MAKE_COMPOSER} update -o --no-progress

tools/phpstan/vendor/bin/phpstan:
	${MAKE_COMPOSER} --working-dir=tools/phpstan update -o --no-progress

tools/php-cs-fixer/vendor/bin/php-cs-fixer:
	${MAKE_COMPOSER} --working-dir=tools/php-cs-fixer update -o --no-progress
