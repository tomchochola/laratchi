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
check: stan lint audit test

.PHONY: audit
audit: ./vendor ./node_modules ./package-lock.json
	${MAKE_COMPOSER} audit
	${MAKE_COMPOSER} check-platform-reqs
	${MAKE_COMPOSER} validate --strict --no-check-all
	npm audit --audit-level info

.PHONY: stan
stan: ./vendor ./vendor/bin/phpstan
	${MAKE_PHP} ./vendor/bin/phpstan analyse

.PHONY: lint
lint: ./vendor ./node_modules/.bin/prettier ./vendor/bin/php-cs-fixer
	./node_modules/.bin/prettier --plugin=@prettier/plugin-xml --xml-quote-attributes=double --xml-whitespace-sensitivity=ignore -c .
	${MAKE_PHP} ./vendor/bin/php-cs-fixer fix --dry-run --diff

.PHONY: fix
fix: ./vendor ./node_modules/.bin/prettier ./vendor/bin/php-cs-fixer
	./node_modules/.bin/prettier --plugin=@prettier/plugin-xml --xml-quote-attributes=double --xml-whitespace-sensitivity=ignore --plugin=@prettier/plugin-php --php-version=8.2 -w .
	${MAKE_PHP} ./vendor/bin/php-cs-fixer fix

.PHONY: test
test: ./vendor ./vendor/bin/phpunit
	${MAKE_PHP} ./vendor/bin/phpunit

.PHONY: clean
clean:
	rm -rf ./node_modules
	rm -rf ./package-lock.json
	rm -rf ./vendor
	rm -rf ./composer.lock
	rm -rf ./.php-cs-fixer.cache
	rm -rf ./.phpunit.result.cache

# Deploy / Release
.PHONY: local
local:
	${MAKE_COMPOSER} update
	npm update --install-links

.PHONY: testing
testing: local

.PHONY: development
development: local

.PHONY: production
production:
	${MAKE_COMPOSER} install -a --no-dev
	npm install --omit dev --install-links

.PHONY: staging
staging: production

# Dependencies
./vendor ./vendor/bin/phpstan ./vendor/bin/php-cs-fixer ./vendor/bin/phpunit:
	${MAKE_COMPOSER} update

./node_modules ./package-lock.json ./node_modules/.bin/prettier:
	npm update --install-links
