#!/usr/bin/env bash

pushd /var/www/webapp
WAIT_HOSTS=db:3306 /usr/local/bin/wait && ./vendor/bin/phpunit
popd
