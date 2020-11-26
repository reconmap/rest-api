# Use bash or file wildcards won't work
SHELL = bash
DB_CONTAINER = $(shell docker-compose ps -q mysql)

.PHONY: prepare
prepare: build
	docker-compose run --rm -w /var/www/webapp --entrypoint composer svc install

.PHONY: build
build:
	docker-compose build

.PHONY: tests
tests: start
	docker-compose run --rm -w /var/www/webapp -e WAIT_HOSTS=$(DB_CONTAINER):3306 --entrypoint /usr/local/bin/wait svc
	docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet -e "DROP DATABASE IF EXISTS reconmap_test"
	docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet -e "CREATE DATABASE reconmap_test"
	docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet -e "GRANT ALL PRIVILEGES ON reconmap_test.* TO 'reconmapper'@'%';"
	echo Importing SQL files: $(wildcard docker/mysql/initdb.d/*.sql)
	cat docker/mysql/initdb.d/{01,02,03}*.sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap_test
	docker-compose run --rm -w /var/www/webapp -e CURRENT_PLANET=Moon --entrypoint ./run-tests.sh svc
	docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet -e "DROP DATABASE reconmap_test"

.PHONY: security-tests
security-tests:
	docker-compose run --rm -w /var/www/webapp --entrypoint composer svc require sensiolabs/security-checker
	docker-compose run --rm -w /var/www/webapp --entrypoint ./vendor/bin/security-checker svc security:check

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
	@docker-compose exec -w /var/www/webapp svc bash

# Database targets

.PHONY: db-shell
db-shell:
	@docker-compose exec $(DB_CONTAINER) mysql --silent -uroot -preconmuppet reconmap

.PHONY: db-reset
db-reset:
	cat docker/mysql/initdb.d/{01,02}*.sql | docker container exec -i $(DB_CONTAINER) mysql -uroot -preconmuppet reconmap
	
.PHONY: redis-shell
redis-shell:
	@docker-compose exec redis redis-cli

