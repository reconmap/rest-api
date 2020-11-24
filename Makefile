# Use bash or file wildcards won't work
SHELL = bash

.PHONY: prepare
prepare: build
	docker-compose run --rm -w /var/www/webapp --entrypoint composer svc install

.PHONY: build
build:
	docker-compose build

.PHONY: tests
tests: start
	docker-compose run --rm -w /var/www/webapp -e WAIT_HOSTS=db:3306 --entrypoint /usr/local/bin/wait svc
	docker container exec -i $(shell docker-compose ps -q db) mysql -uroot -preconmuppet -e "DROP DATABASE IF EXISTS reconmap_test"
	docker container exec -i $(shell docker-compose ps -q db) mysql -uroot -preconmuppet -e "CREATE DATABASE reconmap_test"
	docker container exec -i $(shell docker-compose ps -q db) mysql -uroot -preconmuppet -e "GRANT ALL PRIVILEGES ON reconmap_test.* TO 'reconmapper'@'%';"
	echo Importing SQL files: $(wildcard docker/database/initdb.d/*.sql)
	cat docker/database/initdb.d/{01,02,03}*.sql | docker container exec -i $(shell docker-compose ps -q db) mysql -uroot -preconmuppet reconmap_test
	docker-compose run --rm -w /var/www/webapp -e CURRENT_PLANET=Moon --entrypoint ./run-tests.sh svc
	docker container exec -i $(shell docker-compose ps -q db) mysql -uroot -preconmuppet -e "DROP DATABASE reconmap_test"

.PHONY: security-tests
security-tests:
	docker-compose run --rm -w /var/www/webapp --entrypoint composer svc require sensiolabs/security-checker
	docker-compose run --rm -w /var/www/webapp --entrypoint ./vendor/bin/security-checker svc security:check

.PHONY: db-reset
db-reset:
	cat docker/database/initdb.d/{01,02}*.sql | docker container exec -i $(shell docker-compose ps -q db) mysql -uroot -preconmuppet reconmap
	
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

.PHONY: db-shell
db-shell:
	@docker-compose exec db mysql --silent -uroot -preconmuppet reconmap

.PHONY: redis-shell
redis-shell:
	@docker-compose exec redis redis-cli

