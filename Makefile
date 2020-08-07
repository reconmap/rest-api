
DOCKER_IMAGE=reconmap

.PHONY: run
run:
	docker-compose up -d

.PHONY: shell
shell:
	docker-compose exec fe bash

.PHONY: mysqlclient
mysqlclient:
	docker-compose exec db mysql -uroot -preconmuppet reconmap

.PHONY: stop
stop:
	docker-compose stop
