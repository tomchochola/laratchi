# Default shell
SHELL := /bin/bash

# Variables
MAKE_PHP_8_1_BIN ?= php8.1
MAKE_COMPOSER_2_BIN ?= /usr/local/bin/composer2

MAKE_PHP ?= ${MAKE_PHP_8_1_BIN}
MAKE_COMPOSER ?= ${MAKE_PHP} ${MAKE_COMPOSER_2_BIN}

# Default goal
.DEFAULT_GOAL := check

# Goals
.PHONY: local
local: update

.PHONY: check
check: audit lint

.PHONY: audit
audit: vendor tools
	tools/local-php-security-checker/vendor/bin/local-php-security-checker
	${MAKE_COMPOSER} audit

.PHONY: lint
lint: vendor tools
	tools/prettier/node_modules/.bin/prettier --ignore-path .gitignore -c . '!**/*.svg'
	${MAKE_COMPOSER} validate --strict
	${MAKE_PHP} tools/phpstan/vendor/bin/phpstan analyse
	${MAKE_PHP} tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run --diff

.PHONY: fix
fix: tools
	tools/prettier/node_modules/.bin/prettier --ignore-path .gitignore -w . '!**/*.svg'
	${MAKE_PHP} tools/php-cs-fixer/vendor/bin/php-cs-fixer fix

.PHONY: clean
clean:
	git clean -Xfd

.PHONY: cold
cold:
	git clean -xfd tools composer.lock vendor package-lock.json node_modules

.PHONY: minify
minify:
	svgo -r -f resources --multipass --final-newline

.PHONY: build
build:
	npx tailwindcss -c resources/exceptions/css/tailwind.config.js  -i resources/exceptions/css/index.css -o resources/exceptions/views/css.blade.php -m

.PHONY: dev
dev:
	npx tailwindcss -c resources/exceptions/css/tailwind.config.js -i resources/exceptions/css/index.css -o resources/exceptions/views/css.css

.PHONY: clean-tools
clean-tools:
	git clean -xfd tools

.PHONY: update-tools
update-tools: clean-tools tools

.PHONY: update
update:
	${MAKE_COMPOSER} update -o

# Aliases
.PHONY: ci
ci: check

# Dependencies
tools: tools/prettier/node_modules/.bin/prettier tools/phpstan/vendor/bin/phpstan tools/php-cs-fixer/vendor/bin/php-cs-fixer tools/local-php-security-checker/vendor/bin/local-php-security-checker

tools/prettier/node_modules/.bin/prettier:
	npm --prefix=tools/prettier update

vendor:
	${MAKE_COMPOSER} update -o

tools/phpstan/vendor/bin/phpstan:
	${MAKE_COMPOSER} --working-dir=tools/phpstan update -o

tools/php-cs-fixer/vendor/bin/php-cs-fixer:
	${MAKE_COMPOSER} --working-dir=tools/php-cs-fixer update -o

tools/local-php-security-checker/vendor/bin/local-php-security-checker:
	${MAKE_COMPOSER} --working-dir=tools/local-php-security-checker update -o
