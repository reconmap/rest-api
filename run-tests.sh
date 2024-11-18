#!/usr/bin/env bash
set -e

pushd /var/www/webapp
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-clover="/var/www/webapp/tests/clover.xml" --display-warnings
pushd packages/command-parsers-lib
XDEBUG_MODE=coverage ./vendor/bin/phpunit --display-warnings
popd
popd
