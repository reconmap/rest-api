IMAGE_NAME=reconmap/php-output-command-parsers

all:
	composer install

test:
	composer validate --strict --no-check-version
	composer exec -- phpunit --no-coverage

code-analysis:
	vendor/bin/psalm --report=results.sarif

image:
	docker build -t $(IMAGE_NAME) .

container:
	docker run -it --rm -v $(PWD):/app -w /app --entrypoint /bin/bash $(IMAGE_NAME)

