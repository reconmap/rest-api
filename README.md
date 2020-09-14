
[![Build Status](https://travis-ci.org/reconmap/api-backend.svg?branch=master)](https://travis-ci.org/reconmap/api-backend) [![Maintainability](https://api.codeclimate.com/v1/badges/a54653799e277bab4e77/maintainability)](https://codeclimate.com/github/Reconmap/api-backend/maintainability)


![Reconmap devices](https://pasteall.org/media/2/0/204759e8714dc1def4209d10b3370c4d.png)

![Reconmap logo](https://pasteall.org/media/4/7/4780c30723f90cfd56ec0d056555b7e6.png) 


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

The first thing you need to do is build the containers and prepare the app. This can be achieved by invoking the default make target:

```sh
$ make
```

Once the containers are built, and the app prepared, you can run the docker services with the following command:

```sh
$ make start
```

If everything went ok you should be able to use curl or any other HTTP client (eg your browser) to call the API:

```sh
$ curl http://localhost:8080
```

## How to contribute

**We are glad you are thinking about contributing to this project.** All help is hugely appreciated.

Before you jump to make any changes make sure you have read the [contributing guidelines](CONTRIBUTING.md). This would save us all time. Thanks!

## How to report bugs or feature requests

If you have bugs or feature requests to report please use the [issues](https://github.com/reconmap/application/issues) tab on Github.

If you want to chat to somebody on the development team head to our [Gitter](https://gitter.im/reconmap/community) community.
