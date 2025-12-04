# Use bash or file wildcards won't work
SHELL := bash
.SHELLFLAGS := -eu -o pipefail -c
.ONESHELL:
.DELETE_ON_ERROR:
.DEFAULT_GOAL := prepare
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

DB_CONTAINER=rmap-mysql

HOST_UID=$(shell id -u)
HOST_GID=$(shell id -g)

DOCKER_IMAGE_NAME = ghcr.io/reconmap/rest-api
DOCKER_DEFAULT_TAG = $(DOCKER_IMAGE_NAME)

ifndef GIT_BRANCH_NAME
GIT_BRANCH_NAME = $(shell git rev-parse --abbrev-ref HEAD)
endif

define composer_install
	echo
	echo Running composer install on $(1)
	docker compose run --rm --user reconmapper -w $(1) --entrypoint composer api install
endef

.PHONY: prepare-config
prepare-config:
	[ -f config.json ] || cp config-template.json config.json

.PHONY: prepare-dirs
prepare-dirs:
	mkdir -p vendor logs

.SILENT: install-deps
.PHONY: install-deps
install-deps:
	$(call composer_install, /var/www/webapp)
	$(call composer_install, /var/www/webapp/packages/command-parsers-lib)

.PHONY: prepare
prepare: prepare-config prepare-dirs build install-deps

.PHONY: build
build:
	docker compose build --no-cache --build-arg HOST_UID=$(HOST_UID) --build-arg HOST_GID=$(HOST_GID)

.PHONY: tests
tests: start validate
	echo Importing SQL files: $(wildcard database/0*.sql)
	cat tests/database.sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet
	cat ../docker/mysql/initdb/0*.sql | sed "s/USE reconmap;/USE reconmap_test;/" | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap_test
	docker compose -p rest-api run --rm -w /var/www/webapp --entrypoint php api src/Cli/app.php test:generate-data --use-test-database
	docker compose -p rest-api run --rm -w /var/www/webapp -e CURRENT_PLANET=Moon --entrypoint ./run-tests.sh api
	docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet -e "DROP DATABASE reconmap_test"

.PHONY: security-tests
security-tests:
	docker compose run --rm -w /var/www/webapp --entrypoint bash api -c "wget https://github.com/fabpot/local-php-security-checker/releases/download/v2.0.5/local-php-security-checker_2.0.5_linux_amd64 -O security-checker && chmod +x security-checker && ./security-checker"

.PHONY: start
start: prepare-config prepare-dirs
	docker compose -p rest-api up -d

.PHONY: validate
validate:
	docker compose run --rm -w /var/www/webapp --entrypoint composer api validate --strict

.PHONY: stop
stop:
	docker compose stop

.PHONY: clean
clean: stop
	docker compose down -v --remove-orphans
	docker compose rm
	rm -rf {data-mysql,data-redis}

.PHONY: api-shell
api-shell:
	@docker compose -p rest-api exec -u reconmapper -w /var/www/webapp api bash

.PHONY: api-rootshell
api-rootshell:
	@docker compose exec -u root -w /var/www/webapp api bash

.PHONY: cache-clear
cache-clear:
	git clean -fdx data/cache

.PHONY: push
push:
	docker tag $(DOCKER_IMAGE_NAME):latest $(DOCKER_IMAGE_NAME):$(GIT_BRANCH_NAME)
	docker push $(DOCKER_IMAGE_NAME):$(GIT_BRANCH_NAME)
	docker push $(DOCKER_IMAGE_NAME):latest
	docker compose push mysql

# Database targets

.PHONY: db-reset
db-reset:
	cat database/{01,02}*.sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap

.PHONY: db-import
db-import:
	cat database/{01,02,03}*.sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap


