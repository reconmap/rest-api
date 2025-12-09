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

ifndef GIT_BRANCH_NAME
GIT_BRANCH_NAME = $(shell git rev-parse --abbrev-ref HEAD)
endif

.PHONY: build
build:
	docker compose build --no-cache --build-arg HOST_UID=$(HOST_UID) --build-arg HOST_GID=$(HOST_GID)

.PHONY: start
start:
	docker compose --profile testing up -d

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
	@docker compose exec -u reconmapper -w /var/www/webapp api bash

.PHONY: api-rootshell
api-rootshell:
	@docker compose exec -u root -w /var/www/webapp api bash

.PHONY: cache-clear
cache-clear:
	git clean -fdx data/cache

.PHONY: push
push:
	docker compose push mysql

# Database targets

.PHONY: db-shell
db-shell:
	@docker compose exec mysql mysql --silent -uroot -preconmuppet reconmap

.PHONY: db-reset
db-reset:
	cat database/{01,02}*.sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap

.PHONY: db-import
db-import:
	cat database/{01,02,03}*.sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap

.PHONY: redis-shell
redis-shell:
	@docker compose exec redis redis-cli
