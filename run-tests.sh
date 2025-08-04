#!/usr/bin/env bash
set -e

export XDEBUG_MODE=coverage
export PHPUNIT_ARGS=" --display-warnings --coverage-clover=\"/var/www/webapp/tests/clover.xml\""

pushd /var/www/webapp
./vendor/bin/phpunit $PHPUNIT_ARGS
pushd packages/command-parsers-lib
./vendor/bin/phpunit $PHPUNIT_ARGS
popd
popd

export XDEBUG_MODE=
