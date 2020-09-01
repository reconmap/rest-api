
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
tests: run
	docker-compose run --rm -w /var/www/webapp --entrypoint ./run-tests.sh svc

.PHONY: conn_svc
conn_svc:
	docker-compose exec -w /var/www/webapp svc bash

.PHONY: conn_db
conn_db:
	docker-compose exec db mysql -uroot -preconmuppet reconmap

.PHONY: reset_db
reset_db:
	cat $(wildcard docker/database/initdb.d/*.sql) | docker container exec -i $(shell docker-compose ps -q db) mysql -uroot -preconmuppet reconmap
	
.PHONY: stop
stop:
	docker-compose stop

.PHONY: clean
clean: stop
	docker-compose down -v
	rm -rf db_data

.PHONY: runswagger
runswagger:
	docker run -p 80:8080 -e SWAGGER_JSON=/local/openapi.yaml -v $(PWD):/local swaggerapi/swagger-ui

.PHONY: generateapidocs
generateapidocs:
	docker run --rm -v $(PWD):/local swaggerapi/swagger-codegen-cli-v3 generate -i /local/openapi.yaml -l dynamic-html -o /local/apidocs
