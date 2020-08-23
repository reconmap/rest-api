
[![Build Status](https://travis-ci.org/reconmap/api-backend.svg?branch=master)](https://travis-ci.org/reconmap/api-backend)

![Reconmap logo](https://reconmap.org/images/logo.png)

# Reconmap

Project planning, implementation and documentation for security professionals and teams. From reconnaissance to intelligence gathering step by step.

## Demo

A running demo is available for you to try here: https://demo.reconmap.org

## Requirements

- Docker
- Docker compose
- Make

## Documentation

The API specs have been documented using [OpenAPI](openapi.yaml). You can use the interactive [Swagger UI](https://petstore.swagger.io/?url=https://raw.githubusercontent.com/Reconmap/api-backend/master/openapi.yaml) to play with it.

## Build instructions

The first thing you need to do is build the containers and prepare the app. This can be achieved by invoking the default make target.
you need to build and start the containers:

```sh
$ make
```

Once the containers are built and the app prepared you can run the docker services.

```sh
$ make run
```

If everything went ok you should be able to use curl or any other HTTP client (eg your browser) to call the API:

```sh
$ curl http://localhost:8080
Reconmap API
```

## How to contribute

**We are glad you are thinking about contributing to this project.** All help is hugely appreciated.

Before you jump to make any changes make sure you have read the [contributing guidelines](CONTRIBUTING.md). This would save us all time. Thanks!

## How to report bugs or feature requests

If you have bugs or feature requests to report please use the [issues](https://github.com/reconmap/application/issues) tab on Github.

If you want to chat to somebody on the development team head to our [Gitter](https://gitter.im/reconmap/community) community.
