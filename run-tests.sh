#!/usr/bin/env bash
set -e

export XDEBUG_MODE=coverage
export XDEBUG_MODE=off
export PHPUNIT_ARGS="--coverage-clover=\"/var/www/webapp/tests/clover.xml\""
export PHPUNIT_ARGS="--no-coverage --display-warnings"

pushd /var/www/webapp
./vendor/bin/phpunit $PHPUNIT_ARGS
pushd packages/command-parsers-lib
./vendor/bin/phpunit $PHPUNIT_ARGS
popd
popd

export XDEBUG_MODE=
