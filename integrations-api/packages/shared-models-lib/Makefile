IMAGE_NAME=reconmap/shared-models-lib

all:
	composer install

test:
	composer validate --strict --no-check-version

code-analysis:
	vendor/bin/psalm --report=results.sarif

image:
	docker build -t $(IMAGE_NAME) .

container:
	docker run -it --rm -v $(PWD):/app -w /app --entrypoint /bin/bash $(IMAGE_NAME)

