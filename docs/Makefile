# Use bash or file wildcards won't work
SHELL = bash

.PHONY: runswagger
runswagger:
	docker run -p 80:8080 -e SWAGGER_JSON=/local/openapi.yaml -v $(PWD):/local swaggerapi/swagger-ui

.PHONY: generateapidocs
generateapidocs:
	docker run --rm -v $(PWD):/local swaggerapi/swagger-codegen-cli-v3 generate -i /local/openapi.yaml -l dynamic-html -o /local/apidocs

