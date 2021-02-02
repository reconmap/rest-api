# Use bash or file wildcards won't work
SHELL := bash
.SHELLFLAGS := -eu -o pipefail -c
.ONESHELL:
.DELETE_ON_ERROR:
MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

DB_CONTAINER=rmap-mysql

DOCKER_IMAGE_NAME = quay.io/reconmap/rest-api
DOCKER_DEFAULT_TAG = $(DOCKER_IMAGE_NAME)

.PHONY: prepare
prepare: build
	docker-compose run --rm -w /var/www/webapp --entrypoint composer api install

.PHONY: build
build:
	docker-compose build --no-cache

.PHONY: tests
tests: start
	docker-compose run --rm -w /var/www/webapp -e WAIT_HOSTS=$(DB_CONTAINER):3306 --entrypoint /usr/local/bin/wait api
	docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet -e "DROP DATABASE IF EXISTS reconmap_test"
	docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet -e "CREATE DATABASE reconmap_test"
	docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet -e "GRANT ALL PRIVILEGES ON reconmap_test.* TO 'reconmapper'@'%';"
	echo Importing SQL files: $(wildcard docker/mysql/initdb.d/*.sql)
	cat docker/mysql/initdb.d/{01,02,03}*.sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap_test
	docker-compose run --rm -w /var/www/webapp -e CURRENT_PLANET=Moon --entrypoint ./run-tests.sh api
	docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet -e "DROP DATABASE reconmap_test"

.PHONY: security-tests
security-tests:
	docker-compose run --rm -w /var/www/webapp --entrypoint bash api -c "wget https://github.com/fabpot/local-php-security-checker/releases/download/v1.0.0/local-php-security-checker_1.0.0_linux_amd64 -O security-checker && chmod +x security-checker && ./security-checker"

.PHONY: start
start:
	docker-compose up -d

.PHONY: stop
stop:
	docker-compose stop

.PHONY: clean
clean: stop
	docker-compose down -v --remove-orphans
	docker-compose rm
	rm -rf {data-mysql,data-redis} 

.PHONY: api-shell
api-shell:
	@docker-compose exec -w /var/www/webapp api bash

.PHONY: push
push:
	docker push $(DOCKER_IMAGE_NAME):$(GIT_BRANCH_NAME)
	docker push $(DOCKER_IMAGE_NAME):latest

# Database targets

.PHONY: db-shell
db-shell:
	@docker-compose exec mysql mysql --silent -uroot -preconmuppet reconmap

.PHONY: db-reset
db-reset:
	cat docker/mysql/initdb.d/{01,02}*.sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap

.PHONY: db-import
db-import:
	cat docker/mysql/initdb.d/{01,02,03}*.sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap
	
.PHONY: db-migrate
db-migrate:
	cat database/migrations/changes$(MIGRATE_FROM_VERSION)-$(MIGRATE_TO_VERSION).sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap
	
.PHONY: redis-shell
redis-shell:
	@docker-compose exec redis redis-cli

