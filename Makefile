
DOCKER_IMAGE=reconmap

.PHONY: run
run: build
	docker-compose up -d

.PHONY: prepare
prepare:
	docker-compose exec -w /var/www/webapp fe composer install

.PHONY: build
build:
	docker-compose build

.PHONY: shell
shell:
	docker-compose exec fe bash

.PHONY: mysqlclient
mysqlclient:
	docker-compose exec db mysql -uroot -preconmuppet reconmap

.PHONY: stop
stop:
	docker-compose stop
