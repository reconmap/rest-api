[![Build Status](https://travis-ci.com/reconmap/rest-api.svg?branch=master)](https://travis-ci.com/Reconmap/rest-api)
[![Maintainability](https://api.codeclimate.com/v1/badges/7bebfcc41dce2610de78/maintainability)](https://codeclimate.com/github/reconmap/rest-api/maintainability)
[![codecov](https://codecov.io/gh/reconmap/rest-api/branch/master/graph/badge.svg?token=VTC6RAM41Q)](https://codecov.io/gh/reconmap/rest-api)
[![Gitter](https://badges.gitter.im/reconmap/community.svg)](https://gitter.im/reconmap/community?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

# Reconmap Rest API

The Reconmap API is a RESTful API that allows any of the clients (Web, CLI, Mobile) to manipulate any of the Reconmap's
entities: projects, tasks, commands, reports, users, etc. With the API you can extend Reconmap in any way you can
imagine.

This is a component of many in the [Reconmap's architecture](https://reconmap.org/development/architecture.html).

## Runtime requirements

- Docker
- Docker compose
- Make

## Documentation

The API specs have been documented using the [OpenAPI](docs/openapi.yaml) specification. You can use the
interactive [OpenAPI UI](https://api.reconmap.org/docs/) to play with it.

## Build instructions

The first thing you need to do is build the containers and prepare the app. This can be achieved by invoking the default
make target:

```sh
make
```

Once the containers are built, and the app prepared, you can run the docker services with the following command:

```sh
make start
```

If everything went ok you should be able to use curl or any other HTTP client (eg your browser) to call the API:

```sh
curl http://localhost:8080
```

Alternatively, you could run the Reconmap [frontend](https://github.com/Reconmap/web-client) against your local API with
the following commands:

```sh
cat <<EOF > environment.js
window.env = {
	// URL to the Reconmap API including protocol and port but not trailing slash
    REACT_APP_API_ENDPOINT: 'http://localhost:8080',

	// URL to the Reconmap WebSocket API including protocol and port but not trailing slash
    REACT_APP_WS_ENDPOINT: 'ws://localhost:8765'
};
EOF
docker run --rm -d -p 3001:80 \
	-v "$PWD/environment.js:/usr/share/nginx/html/environment.js" \
	--name reconmap-web-client quay.io/reconmap/web-client:master
```

Then opening your browser at http://localhost:3001

## How to contribute

**We are glad you are thinking about contributing to this project.** All help is hugely appreciated.

Before you jump to make any changes make sure you have read the [contributing guidelines](CONTRIBUTING.md). This would
save us all time. Thanks!

## How to report bugs or feature requests

If you have bugs or feature requests to report please use the [issues](https://github.com/reconmap/application/issues)
tab on Github.

If you want to chat to somebody on the development team head to our [Gitter](https://gitter.im/reconmap/community)
community.
