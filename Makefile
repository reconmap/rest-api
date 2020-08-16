
DOCKER_IMAGE=reconmap

.PHONY: prepare
prepare: build
	docker-compose run --rm -w /var/www/webapp --entrypoint composer svc install

.PHONY: build
build:
	docker-compose build

.PHONY: run
run: 
	docker-compose up -d

.PHONY: tests
tests:
	docker-compose run --rm -w /var/www/webapp --entrypoint ./vendor/bin/phpunit svc tests

.PHONY: conn_svc
conn_svc:
	docker-compose exec -w /var/www/webapp svc bash

.PHONY: conn_db
conn_db:
	docker-compose exec db mysql -uroot -preconmuppet reconmap

.PHONY: stop
stop:
	docker-compose stop

.PHONY: clean
clean: stop
	docker-compose down -v
	rm -rf db_data
	rm -rf vendor
