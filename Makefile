# Default shell
SHELL := /bin/bash

# Variables
MAKE_PHP_8_2_BIN ?= php8.2
MAKE_COMPOSER_2_BIN ?= /usr/local/bin/composer2

MAKE_PHP ?= ${MAKE_PHP_8_2_BIN}
MAKE_COMPOSER ?= ${MAKE_PHP} ${MAKE_COMPOSER_2_BIN}

# Default goal
.DEFAULT_GOAL := panic

# Goals
.PHONY: check
check: stan lint audit

.PHONY: audit
audit: vendor
	${MAKE_COMPOSER} audit

.PHONY: stan
stan: vendor tools
	${MAKE_PHP} ./tools/phpstan/vendor/bin/phpstan analyse

.PHONY: lint
lint: vendor tools
	${MAKE_COMPOSER} validate --strict
	./tools/prettier/node_modules/.bin/prettier --plugin=./tools/prettier/node_modules/@prettier/plugin-xml/src/plugin.js --xml-quote-attributes=double --xml-whitespace-sensitivity=ignore -c .
	${MAKE_PHP} ./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run --diff

.PHONY: fix
fix: vendor tools
	./tools/prettier/node_modules/.bin/prettier --plugin=./tools/prettier/node_modules/@prettier/plugin-xml/src/plugin.js --xml-quote-attributes=double --xml-whitespace-sensitivity=ignore --plugin=./tools/prettier/node_modules/@prettier/plugin-php/src/index.js --php-version=8.2 -w .
	${MAKE_PHP} ./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix

.PHONY: composer
composer:
	${MAKE_COMPOSER} install

.PHONY: composer-no-dev
composer-no-dev:
	${MAKE_COMPOSER} install --no-dev -a

.PHONY: clean-composer
clean-composer:
	rm -rf ./vendor
	rm -rf ./composer.lock

.PHONY: clean-tools
clean-tools:
	rm -rf ./tools/*/vendor
	rm -rf ./tools/*/node_modules
	rm -rf ./tools/*/composer.lock
	rm -rf ./tools/*/package-lock.json
	rm -rf ./tools/*/yarn.lock

.PHONY: clean-node
clean-node:
	rm -rf ./node_modules
	rm -rf ./package-lock.json
	rm -rf ./yarn.lock

.PHONY: clean
clean: clean-tools clean-composer clean-node

# Aliases
.PHONY: ci
ci: check

# Dependencies
tools: ./tools/prettier/node_modules/.bin/prettier ./tools/phpstan/vendor/bin/phpstan ./tools/php-cs-fixer/vendor/bin/php-cs-fixer

vendor:
	${MAKE_COMPOSER} install

./tools/prettier/node_modules/.bin/prettier:
	npm --prefix=./tools/prettier install

./tools/phpstan/vendor/bin/phpstan:
	${MAKE_COMPOSER} --working-dir=./tools/phpstan install

./tools/php-cs-fixer/vendor/bin/php-cs-fixer:
	${MAKE_COMPOSER} --working-dir=./tools/php-cs-fixer install
