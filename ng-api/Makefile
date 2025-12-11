PROJECT := rest-api
DC := docker compose -p $(PROJECT)

.PHONY: start
start:
	$(DC) start ngapi

.PHONY: stop
stop:
	$(DC) stop ngapi

.PHONY: push
push:
	$(DC) push ngapi
