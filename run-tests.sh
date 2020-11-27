#!/usr/bin/env bash
set -e

pushd /var/www/webapp
./vendor/bin/phpunit
popd
