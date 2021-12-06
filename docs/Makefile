# Use bash or file wildcards won't work
SHELL = bash

.PHONY: runswagger
runswagger:
	docker run -p 8080:8080 -e SWAGGER_JSON=/local/openapi.yaml -v $(PWD):/local swaggerapi/swagger-ui

.PHONY: generateapidocs
generateapidocs:
	docker run --rm -v $(PWD):/local openapitools/openapi-generator-cli generate -i /local/openapi.yaml -g dynamic-html -o /local/apidocs

.PHONY: servedocs
servedocs:
	cd apidocs/docs && python3 -m http.server
