
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
$ curl http://localhost:8080/projects
[{"id":"1","insert_ts":"2020-08-15 13:46:05","update_ts":null,"is_template":"0","name":"Web server pentest project","description":"Test project to show pentest tasks and reports"}]
```

## Contributions

Contributions are more than welcome! Fork the repo, make changes, then open a pull request against our repo and we will review it and merge it promptly.

If you have bugs or feature requests to report please use the [issues](https://github.com/reconmap/application/issues) tab on Github.

If you want to chat to one of us join our IRC channel on [Freenode](https://webchat.freenode.net/) using the channel #reconmap.
